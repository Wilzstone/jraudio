<?php
namespace App\Support;

use DateTimeInterface;

final class KodePengajuan
{
    /** Kode untuk manusia, bukan pengganti token URL acak / otorisasi. */
    public static function tampil(int $idPengajuan, string|DateTimeInterface $dibuat): string
    {
        $tanggal = $dibuat instanceof DateTimeInterface ? $dibuat->format('ymd') : date('ymd', strtotime($dibuat));
        return 'PGJ-'.$tanggal.'-'.str_pad((string)$idPengajuan, 5, '0', STR_PAD_LEFT);
    }
    public static function dari(object $pengajuan): string
    {
        return self::tampil((int)$pengajuan->id_pengajuan, $pengajuan->created_at);
    }
}
