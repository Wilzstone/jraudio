<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Katalog mitra hanya referensi. Harga transaksi disimpan sebagai snapshot. */
class MitraCatalogService {
    public static function item(int $id, bool $requireAktif = true): object {
        $item = DB::table('barang_mitra as b')->join('mitra as m','m.id_mitra','=','b.id_mitra')
            ->where('b.id_barang_mitra',$id)->select('b.*','m.nama_mitra','m.status as status_mitra')->first();
        if (!$item) throw new RuntimeException('Barang mitra tidak ditemukan.');
        if ($requireAktif && ($item->status !== 'Aktif' || $item->status_mitra !== 'Aktif'))
            throw new RuntimeException('Barang atau mitra sudah nonaktif. Pilih barang mitra yang aktif.');
        return $item;
    }
    public static function pilihan(): array {
        return DB::table('barang_mitra as b')->join('mitra as m','m.id_mitra','=','b.id_mitra')
            ->where('b.status','Aktif')->where('m.status','Aktif')
            ->orderBy('m.nama_mitra')->orderBy('b.nama_barang')
            ->get(['b.id_barang_mitra','b.nama_barang','b.harga_mitra','b.harga_pelanggan','m.nama_mitra'])
            ->map(fn($x)=>(array)$x)->all();
    }
}
