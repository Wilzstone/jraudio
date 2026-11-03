<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\PelangganNomorService;
use RuntimeException;

class NotifikasiPembayaranService
{
    public function siapkan(int $idPembayaran): void
    {
        $p = DB::table('pembayaran as pb')->join('transaksi as t','t.id_transaksi','=','pb.id_transaksi')
            ->join('pelanggan as pl','pl.id_pelanggan','=','t.id_pelanggan')
            ->where('pb.id_pembayaran',$idPembayaran)
            ->select('pb.*','t.total_harga','pl.nama_pelanggan')->first();
        if (!$p || $p->status_konfirmasi !== 'Dikonfirmasi') throw new RuntimeException('Hanya pembayaran terkonfirmasi yang dapat diberitahukan.');
        $r = app(PembayaranWorkflowService::class)->ringkasan((int)$p->id_transaksi);
        $format = static fn(int $n): string => 'Rp '.number_format($n,0,',','.');
        $jenis = $p->jenis_pembayaran;
        $pesan = implode("\n", [
          'Halo Kak '.$p->nama_pelanggan.',',
          'Terima kasih. Pembayaran JR Audio sudah *dikonfirmasi*.',
          '',
          '*Transaksi:* #'.$p->id_transaksi,
          '*Jenis pembayaran:* '.$jenis,
          '*Nominal pembayaran ini:* '.$format((int)$p->jumlah_bayar),
          '*Total sewa:* '.$format($r['total_sewa']),
          '*Total pembayaran sewa terkonfirmasi:* '.$format($r['dibayar_sewa']),
          '*Sisa sewa:* '.$format($r['sisa_sewa']),
          '*Total denda:* '.$format($r['total_denda']),
          '*Sisa denda:* '.$format($r['sisa_denda']),
          '',
          'Pesan ini adalah ringkasan pembayaran yang sudah diverifikasi JR Audio. Jika ada ketidaksesuaian, silakan hubungi tim kami.',
        ]);
        // Snapshot tidak diubah ketika notifikasi sudah pernah disiapkan atau ditandai terkirim.
        DB::table('notifikasi_pembayaran')->insertOrIgnore([
           'id_pembayaran'=>$idPembayaran, 'id_transaksi'=>$p->id_transaksi,
           'jenis'=>$jenis, 'pesan'=>$pesan, 'status'=>'Disiapkan',
           'created_at'=>now(), 'updated_at'=>now(),
        ]);
    }

    public function linkWhatsapp(?string $nomor, string $pesan): ?string
    {
        if (!$nomor) return null;
        try { $n = PelangganNomorService::normalisasi($nomor); }
        catch (\Throwable $e) { return null; }
        $digits = preg_replace('/\D+/', '', $n);
        return preg_match('/^62[0-9]{8,13}$/', $digits) ? 'https://wa.me/'.$digits.'?text='.rawurlencode($pesan) : null;
    }
}
