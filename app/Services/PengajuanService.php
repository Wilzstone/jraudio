<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Services\PelangganNomorService;
use RuntimeException;

class PengajuanService
{
    /** Satu transaksi database mencakup pengajuan, pelanggan, transaksi, detail, dan mutasi stok. */
    public function simpanDanProses(string $kode, array $data, int $idUser, bool $setujui): ?int
    {
        return DB::transaction(function () use ($kode, $data, $idUser, $setujui) {
            $p = DB::table('pengajuan_sewa')->where('kode', $kode)->lockForUpdate()->first();
            if (!$p) throw new RuntimeException('Pengajuan tidak ditemukan.');
            if ($p->status === 'Disetujui') {
                // Idempotensi: klik ulang tidak menciptakan transaksi ganda.
                return $setujui && $p->id_transaksi ? (int)$p->id_transaksi : throw new RuntimeException('Pengajuan sudah disetujui.');
            }
            if ($p->status === 'Batal') throw new RuntimeException('Pengajuan telah dibatalkan.');
            $rows = DB::table('pengajuan_detail')->where('id_pengajuan', $p->id_pengajuan)->orderBy('id_pengajuan_detail')->get();
            $edited = [];
            if (array_key_exists('form_items', $data)) {
                foreach ($data['form_items'] as $item) {
                    $jenis = ($item['tipe_item'] ?? '') === 'External' ? 'External' : 'Barang';
                    $idBarang = $jenis === 'Barang' ? (int)($item['id_barang'] ?? 0) : null;
                    if ($jenis === 'Barang' && $idBarang < 1) throw new RuntimeException('Barang katalog wajib dipilih.');
                    $edited[] = [
                        'id_pengajuan_detail' => null, 'tipe_item' => $jenis, 'id_barang' => $idBarang,
                        'nama_barang' => trim((string)($item['nama_barang'] ?? '')),
                        'sumber_external' => $jenis === 'External' ? trim((string)($item['sumber_external'] ?? '')) : null,
                        'qty' => (int)($item['qty'] ?? 0),
                        'harga_satuan' => $jenis === 'External' ? max(0,(int)($item['harga_satuan'] ?? 0)) : 0,
                    ];
                }
                if (!$edited) throw new RuntimeException('Tambahkan paling sedikit satu perlengkapan.');
                // Form transaksi menjadi sumber rincian final. Hanya dalam transaksi DB yang sama.
                DB::table('pengajuan_detail')->where('id_pengajuan', $p->id_pengajuan)->delete();
            } else {
            foreach ($rows as $row) {
                $edit = $data['items'][$row->id_pengajuan_detail] ?? null;
                if (!$edit) throw new RuntimeException('Ada detail barang yang tidak lengkap.');
                $jenis = ($edit['tipe_item'] ?? 'Barang') === 'External' ? 'External' : 'Barang';
                $qty = (int)$edit['qty'];
                $idBarang = $jenis === 'Barang' ? (int)($edit['id_barang'] ?? 0) : null;
                if ($jenis === 'Barang' && $idBarang < 1) throw new RuntimeException('Pilih barang katalog atau ubah sumber menjadi Mitra/External.');
                $edited[] = [
                    'id_pengajuan_detail' => $row->id_pengajuan_detail, 'tipe_item' => $jenis,
                    'id_barang' => $idBarang,
                    'nama_barang' => trim((string)$edit['nama_barang']),
                    'sumber_external' => $jenis === 'External' ? trim((string)($edit['sumber_external'] ?? '')) : null,
                    'qty' => $qty,
                ];
            }
            foreach (($data['items_baru'] ?? []) as $new) {
                if (empty($new['nama_barang'])) continue;
                $jenis = ($new['tipe_item'] ?? 'Barang') === 'External' ? 'External' : 'Barang';
                $idBarang = $jenis === 'Barang' ? (int)($new['id_barang'] ?? 0) : null;
                if ($jenis === 'Barang' && $idBarang < 1) throw new RuntimeException('Pilih barang katalog untuk perlengkapan internal tambahan.');
                $edited[] = [
                    'id_pengajuan_detail' => null, 'tipe_item' => $jenis, 'id_barang' => $idBarang,
                    'nama_barang' => trim((string)$new['nama_barang']),
                    'sumber_external' => $jenis === 'External' ? trim((string)($new['sumber_external'] ?? '')) : null,
                    'qty' => (int)$new['qty'],
                ];
            }
            }
            if (!$edited) throw new RuntimeException('Daftar peralatan kosong. Tambahkan perlengkapan sebelum menyetujui.');
            $statusBaru = $setujui ? 'Disetujui' : 'Revisi';
            foreach ($edited as $r) {
                if ($r['tipe_item'] === 'Barang') {
                    $r['nama_barang'] = DB::table('barang')->where('id_barang', $r['id_barang'])->value('nama_barang') ?: $r['nama_barang'];
                }
                $payload = [
                    'tipe_item' => $r['tipe_item'], 'id_barang' => $r['id_barang'], 'nama_barang' => $r['nama_barang'],
                    'sumber_external' => $r['sumber_external'], 'qty' => $r['qty'],
                    'jumlah_vendor' => $r['tipe_item'] === 'External' ? $r['qty'] : 0, 'updated_at' => now(),
                ];
                if ($r['id_pengajuan_detail']) {
                    DB::table('pengajuan_detail')->where('id_pengajuan_detail', $r['id_pengajuan_detail'])->update($payload);
                } else {
                    DB::table('pengajuan_detail')->insert($payload + [
                        'id_pengajuan' => $p->id_pengajuan, 'peran' => 'Tambahan crew', 'created_at' => now(),
                    ]);
                }
            }
            if (!$setujui) {
                DB::table('pengajuan_sewa')->where('id_pengajuan', $p->id_pengajuan)->update([
                    'status' => 'Revisi', 'catatan_admin' => $data['catatan_admin'] ?? null,
                    'diproses_oleh' => $idUser, 'updated_at' => now(),
                ]);
                return null;
            }
            $tanggalSewa = Carbon::parse($data['tanggal_sewa'])->startOfDay();
            $tanggalKembali = Carbon::parse($data['tanggal_kembali'])->startOfDay();
            if ($tanggalSewa->lt(Carbon::today()) || $tanggalKembali->lt($tanggalSewa) || $tanggalKembali->gt($tanggalSewa->copy()->addDays(7))) {
                throw new RuntimeException('Tanggal sewa minimal hari ini dan tanggal kembali maksimal H+7.');
            }
            $karyawan = DB::table('karyawan')->where('id_user', $idUser)->first();
            if (!$karyawan) throw new RuntimeException('Akun ini belum terhubung dengan data karyawan.');
            $hargaFinal = (int)$data['harga_final'];
            $biayaBarangLuar = 0;
            foreach ($edited as $r) if ($r['tipe_item'] === 'External') $biayaBarangLuar += (int)($r['harga_satuan'] ?? 0) * $r['qty'];
            $biayaGenset = (int)($data['biaya_genset'] ?? 0);
            if ($hargaFinal <= 0 || $biayaGenset + $biayaBarangLuar >= $hargaFinal) throw new RuntimeException('Harga final harus positif dan mencakup biaya genset.');
            // Urutkan dan kunci semua barang sebelum memeriksa total qty, termasuk ID berulang.
            $totalPerBarang = [];
            foreach ($edited as $r) {
                if ($r['tipe_item'] === 'Barang') {
                    $totalPerBarang[$r['id_barang']] = ($totalPerBarang[$r['id_barang']] ?? 0) + $r['qty'];
                } else {
                    if (!$r['nama_barang'] || !$r['sumber_external'] || in_array(mb_strtolower(trim($r['sumber_external'])), ['konfirmasi crew', 'perlu ditentukan'], true)) {
                        throw new RuntimeException('Nama barang dan sumber mitra wajib diisi untuk item External.');
                    }
                }
            }
            ksort($totalPerBarang, SORT_NUMERIC);
            $stockRows = [];
            foreach ($totalPerBarang as $idBarang => $jumlah) {
                $b = DB::table('barang')->where('id_barang', $idBarang)->lockForUpdate()->first();
                if (!$b) throw new RuntimeException('Barang dengan ID '.$idBarang.' tidak ditemukan.');
                if ((int)$b->stok < $jumlah) {
                    throw new RuntimeException('Stok '.$b->nama_barang.' kurang: butuh '.$jumlah.', tersedia '.$b->stok.'. Silakan revisi ke mitra atau kurangi qty.');
                }
                $stockRows[$idBarang] = $b;
            }
            $idPelanggan = $p->id_pelanggan ? (int)$p->id_pelanggan : PelangganNomorService::cariAtauBuat($p->nama_pelanggan, $p->no_hp);
            if (isset($data['id_pelanggan']) && (int)$data['id_pelanggan'] !== $idPelanggan) throw new RuntimeException('Pelanggan tidak sesuai dengan pengajuan.');
            $namaPaket = match ($p->rekomendasi_paket) {
                'paket2500' => 'Paket 2500', 'paket5000' => 'Paket 5000', default => 'Custom',
            };
            $paket = DB::table('paket')->where('nama_paket', $namaPaket)->first();
            $idTransaksi = DB::table('transaksi')->insertGetId([
                'id_pelanggan' => $idPelanggan, 'id_karyawan' => $karyawan->id_karyawan,
                'jenis_transaksi' => 'Paketan', 'id_paket' => $paket->id_paket ?? null,
                'nama_paket' => $namaPaket,
                'harga_paket_manual' => $hargaFinal - $biayaGenset - $biayaBarangLuar, 'biaya_genset' => $biayaGenset,
                'tanggal_transaksi' => now()->toDateString(),
                'tanggal_sewa' => $tanggalSewa->toDateString(), 'tanggal_kembali' => $tanggalKembali->toDateString(),
                'status_transaksi' => 'Booking', 'total_harga' => $hargaFinal,
                'alamat_lokasi_acara' => $data['alamat_lokasi_acara'] ?? null,
                'link_google_maps' => $data['link_google_maps'] ?? null,
            ]);
            foreach ($edited as $r) {
                $external = $r['tipe_item'] === 'External';
                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'id_barang' => $external ? null : $r['id_barang'],
                    'nama_item_external' => $external ? $r['nama_barang'] : null,
                    'sumber_external' => $external ? $r['sumber_external'] : null,
                    'qty' => $r['qty'], 'harga_satuan' => $r['harga_satuan'] ?? 0,
                    'subtotal' => ($r['harga_satuan'] ?? 0) * $r['qty'],
                    'tipe_item' => $r['tipe_item'],
                ]);
            }
            foreach ($totalPerBarang as $idBarang => $qty) {
                $b = $stockRows[$idBarang];
                $sisa = (int)$b->stok - $qty;
                DB::table('barang')->where('id_barang', $idBarang)->update([
                    'stok' => $sisa, 'stok_disewa' => (int)$b->stok_disewa + $qty,
                    'status' => $sisa > 0 ? 'Tersedia' : 'Disewa',
                ]);
            }
            DB::table('pengajuan_sewa')->where('id_pengajuan', $p->id_pengajuan)->update([
                'status' => $statusBaru, 'id_transaksi' => $idTransaksi,
                'catatan_admin' => $data['catatan_admin'] ?? null, 'harga_final' => $hargaFinal,
                'biaya_genset' => $biayaGenset, 'diproses_oleh' => $idUser, 'updated_at' => now(),
            ]);
            return (int)$idTransaksi;
        }, 3);
    }

    /** Konfirmasi dari halaman Tambah Transaksi, bukan dari tombol pengajuan langsung. */
    public function konfirmasiFormTransaksi(string $kode, array $data, int $idUser): int
    {
        $jenis = $data['jenis_transaksi'] ?? 'Paketan';
        if ($jenis !== 'Paketan') throw new RuntimeException('Pengajuan rekomendasi harus disimpan sebagai transaksi Paketan/Custom.');
        $items = $data['form_items'] ?? [];
        $biayaLuar = 0;
        foreach ($items as $item) {
            if (($item['tipe_item'] ?? '') === 'External') {
                $biayaLuar += (int)$item['qty'] * (int)($item['harga_satuan'] ?? 0);
            }
        }
        $data['harga_final'] = (int)($data['harga_paket_manual'] ?? 0) + (int)($data['biaya_genset'] ?? 0) + $biayaLuar;
        if ($data['harga_final'] <= 0) throw new RuntimeException('Isi harga paket final terlebih dahulu.');
        $id = $this->simpanDanProses($kode, $data, $idUser, true);
        if (!$id) throw new RuntimeException('Transaksi gagal disimpan.');
        return $id;
    }

    public function batalkan(string $kode, int $idUser, ?string $catatan): void
    {
        DB::transaction(function () use ($kode, $idUser, $catatan) {
            $p = DB::table('pengajuan_sewa')->where('kode', $kode)->lockForUpdate()->first();
            if (!$p) throw new RuntimeException('Pengajuan tidak ditemukan.');
            if ($p->status === 'Disetujui') throw new RuntimeException('Pengajuan sudah menjadi transaksi dan tidak dapat dibatalkan di menu ini.');
            DB::table('pengajuan_sewa')->where('id_pengajuan', $p->id_pengajuan)->update([
                'status' => 'Batal', 'catatan_admin' => $catatan,
                'diproses_oleh' => $idUser, 'updated_at' => now(),
            ]);
        });
    }
}
