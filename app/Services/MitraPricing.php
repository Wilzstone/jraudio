<?php
namespace App\Services;

/** Perhitungan barang mitra harus sama untuk Pengajuan dan Transaksi. */
final class MitraPricing
{
    public static function termasukPaket(string $jenis, bool $dicentang): bool
    {
        return $jenis === 'Paketan' && $dicentang;
    }

    public static function subtotalPelanggan(int $hargaPerUnit, int $qty, string $jenis, bool $dicentang): int
    {
        return self::termasukPaket($jenis, $dicentang) ? 0 : max(0, $hargaPerUnit) * max(0, $qty);
    }

    /** Margin per item tidak dapat diukur jika tagihan sudah menyatu dengan harga paket. */
    public static function marginItem(int $subtotal, ?int $modalPerUnit, int $qty, string $jenis, bool $dicentang): ?int
    {
        if ($modalPerUnit === null || self::termasukPaket($jenis, $dicentang)) return null;
        return $subtotal - $modalPerUnit * $qty;
    }
}
