<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;

/** Cari identitas dengan nomor telepon ternormalisasi, tanpa mengubah nama pelanggan lama. */
final class PelangganNomorService {
    public static function normalisasi(string $hp): string {
        $n = preg_replace('/[^0-9]/', '', $hp);
        if (str_starts_with($n, '0')) return '62'.substr($n, 1);
        if (str_starts_with($n, '8')) return '62'.$n;
        return $n;
    }
    /** Dipanggil di dalam transaksi DB saat membuat pengajuan. */
    public static function cariAtauBuat(string $nama, string $hp): int {
        $nomor = self::normalisasi($hp);
        if (!preg_match('/^62[0-9]{8,13}$/', $nomor)) {
            throw new \InvalidArgumentException('Nomor WhatsApp tidak valid.');
        }
        // Kunci baris saat mencari pelanggan. Cocokkan nomor, bukan nama (hindari duplikasi).
        // Membandingkan di PHP menghindari masalah beda collation pada MySQL lama.
        $existing = DB::table('pelanggan')->select('id_pelanggan','no_hp')
            ->orderBy('id_pelanggan')->lockForUpdate()->get();
        foreach ($existing as $row) {
            if ($row->no_hp && self::normalisasi((string)$row->no_hp) === $nomor) {
                return (int)$row->id_pelanggan;
            }
        }
        return (int) DB::table('pelanggan')->insertGetId([
            'nama_pelanggan' => trim($nama), 'no_hp' => $nomor, 'alamat' => null,
        ]);
    }
}
