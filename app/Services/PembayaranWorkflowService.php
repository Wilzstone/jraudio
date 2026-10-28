<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Sumber tunggal perhitungan pembayaran sewa dan denda. */
class PembayaranWorkflowService
{
    public static function ringkas(int $hargaSewa, int $denda, int $dibayarSewa, int $dibayarDenda): array
    {
        // Kompatibilitas transaksi lama: dulu denda bisa dibayar lewat jenis Pelunasan.
        // Kelebihan pembayaran sewa lama dianggap sebagai kredit denda (dibatasi nilai dendanya).
        $kreditDendaLama = max(0, $dibayarSewa - $hargaSewa);
        $lunasSewa = max(0, $hargaSewa - $dibayarSewa);
        $lunasDenda = max(0, $denda - $dibayarDenda - $kreditDendaLama);
        return [
            'total_sewa' => $hargaSewa,
            'total_denda' => $denda,
            'dibayar_sewa' => $dibayarSewa,
            'dibayar_denda' => $dibayarDenda,
            'kredit_denda_lama' => min($denda, $kreditDendaLama),
            'sisa_sewa' => $lunasSewa,
            'sisa_denda' => $lunasDenda,
            'sisa_total' => $lunasSewa + $lunasDenda,
        ];
    }

    public function ringkasan(int $idTransaksi): array
    {
        $trx = DB::table('transaksi')->where('id_transaksi', $idTransaksi)->first();
        if (!$trx) throw new RuntimeException('Transaksi tidak ditemukan.');
        $denda = (int) DB::table('pengembalian')->where('id_transaksi', $idTransaksi)
            ->selectRaw('COALESCE(SUM(denda + denda_manual), 0) AS jumlah')->first()->jumlah;
        $bayar = DB::table('pembayaran')->where('id_transaksi', $idTransaksi)
            ->where('status_konfirmasi', 'Dikonfirmasi')
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis_pembayaran IN ('DP','Pelunasan') THEN jumlah_bayar ELSE 0 END),0) AS sewa, COALESCE(SUM(CASE WHEN jenis_pembayaran = 'Denda' THEN jumlah_bayar ELSE 0 END),0) AS denda")
            ->first();
        return self::ringkas((int)$trx->total_harga, $denda, (int)($bayar->sewa ?? 0), (int)($bayar->denda ?? 0));
    }

    /** Semua perubahan pembayaran berseri lewat lock baris transaksi, agar tidak double bayar. */
    public function tambah(array $data): int
    {
        return DB::transaction(function () use ($data) {
            if (($data['tanggal_bayar'] ?? '') !== \Carbon\Carbon::today('Asia/Jakarta')->toDateString()) {
                throw new RuntimeException('Tanggal pembayaran harus hari ini (tanggal pencatatan).');
            }
            $id = (int)$data['id_transaksi'];
            $trx = DB::table('transaksi')->where('id_transaksi', $id)->lockForUpdate()->first();
            if (!$trx || $trx->status_transaksi === 'Batal') throw new RuntimeException('Transaksi tidak ada atau sudah dibatalkan.');
            $r = $this->ringkasan($id);
            $jenis = (string)$data['jenis_pembayaran'];
            $selesai = $trx->status_transaksi === 'Selesai';
            if ($selesai) {
                if ($jenis !== 'Denda' || $r['sisa_denda'] <= 0) throw new RuntimeException('Transaksi selesai hanya menerima pembayaran sisa denda.');
                if ($r['sisa_sewa'] > 0) throw new RuntimeException('Ada sisa sewa, periksa transaksi lama terlebih dahulu.');
                $nominal = $r['sisa_denda'];
            } else {
                if ($jenis === 'Denda') throw new RuntimeException('Denda hanya boleh dibayar setelah pengembalian diproses.');
                if ($r['sisa_sewa'] <= 0) throw new RuntimeException('Biaya sewa sudah lunas.');
                $nominal = $jenis === 'Pelunasan' ? $r['sisa_sewa'] : (int)($data['jumlah_bayar'] ?? 0);
                if ($nominal <= 0 || $nominal > $r['sisa_sewa']) throw new RuntimeException('Nominal pembayaran melebihi sisa sewa atau tidak valid.');
            }
            // Tidak boleh membuat pembayaran berikutnya selama masih ada yang menunggu konfirmasi.
            if (DB::table('pembayaran')->where('id_transaksi', $id)->where('status_konfirmasi', 'Menunggu')->exists()) {
                throw new RuntimeException('Masih ada pembayaran Menunggu. Konfirmasi atau tolak pembayaran itu terlebih dahulu.');
            }
            return (int) DB::table('pembayaran')->insertGetId([
                'id_transaksi' => $id,
                'tanggal_bayar' => $data['tanggal_bayar'],
                'jumlah_bayar' => $nominal,
                'jenis_pembayaran' => $jenis,
                'status_konfirmasi' => $data['status_konfirmasi'],
            ]);
        }, 3);
    }

    public function ubahStatus(int $idPembayaran, string $status): int
    {
        // Ambil id transaksi dahulu; verifikasi ulang di dalam lock sebelum mengubah.
        $row = DB::table('pembayaran')->where('id_pembayaran', $idPembayaran)->first();
        if (!$row) throw new RuntimeException('Pembayaran tidak ditemukan.');
        return DB::transaction(function () use ($idPembayaran, $status, $row) {
            $trx = DB::table('transaksi')->where('id_transaksi', $row->id_transaksi)->lockForUpdate()->first();
            if (!$trx) throw new RuntimeException('Transaksi tidak ditemukan.');
            $bayar = DB::table('pembayaran')->where('id_pembayaran', $idPembayaran)->lockForUpdate()->first();
            if (!$bayar || (int)$bayar->id_transaksi !== (int)$trx->id_transaksi) throw new RuntimeException('Data pembayaran berubah.');
            if ($bayar->status_konfirmasi === $status) return (int)$trx->id_transaksi;
            if ($bayar->status_konfirmasi !== 'Menunggu' || !in_array($status, ['Dikonfirmasi','Ditolak'], true)) {
                throw new RuntimeException('Hanya pembayaran Menunggu yang dapat dikonfirmasi atau ditolak. Pembayaran terkonfirmasi terkunci.');
            }
            if ($status === 'Dikonfirmasi') {
                $r = $this->ringkasan((int)$trx->id_transaksi);
                $jenis = $bayar->jenis_pembayaran;
                if ($trx->status_transaksi === 'Batal') throw new RuntimeException('Transaksi telah dibatalkan.');
                if ($jenis === 'Denda') {
                    if ($trx->status_transaksi !== 'Selesai' || (int)$bayar->jumlah_bayar > $r['sisa_denda']) {
                        throw new RuntimeException('Denda sudah lunas atau pengembalian belum selesai.');
                    }
                } else {
                    if ($trx->status_transaksi === 'Selesai' || (int)$bayar->jumlah_bayar > $r['sisa_sewa']) {
                        throw new RuntimeException('Pembayaran sewa melebihi sisa tagihan atau transaksi telah selesai.');
                    }
                }
            }
            DB::table('pembayaran')->where('id_pembayaran', $idPembayaran)->update(['status_konfirmasi' => $status]);
            return (int)$trx->id_transaksi;
        }, 3);
    }

    public function hapus(int $idPembayaran): int
    {
        $row = DB::table('pembayaran')->where('id_pembayaran', $idPembayaran)->first();
        if (!$row) throw new RuntimeException('Pembayaran tidak ditemukan.');
        return DB::transaction(function () use ($idPembayaran, $row) {
            $trx = DB::table('transaksi')->where('id_transaksi', $row->id_transaksi)->lockForUpdate()->first();
            if (!$trx) throw new RuntimeException('Transaksi tidak ditemukan.');
            $bayar = DB::table('pembayaran')->where('id_pembayaran', $idPembayaran)->lockForUpdate()->first();
            if (!$bayar || $bayar->status_konfirmasi === 'Dikonfirmasi') throw new RuntimeException('Pembayaran terkonfirmasi tidak boleh dihapus.');
            DB::table('pembayaran')->where('id_pembayaran', $idPembayaran)->delete();
            return (int)$trx->id_transaksi;
        }, 3);
    }
}
