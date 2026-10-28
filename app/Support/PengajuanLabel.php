<?php
namespace App\Support;

/** Label admin untuk nilai pilihan form; tidak mengubah nilai yang tersimpan. */
final class PengajuanLabel
{
    public static function tampil(mixed $value): string
    {
        if ($value === null || trim((string) $value) === '') return 'Belum Diisi';
        $raw = trim((string) $value);
        $map = [
            'belum_tahu' => 'Belum Tahu',
            'belum tahu' => 'Belum Tahu',
            'tidak_cukup' => 'Tidak Cukup',
            'kurang_daya' => 'Daya Tidak Cukup',
            'live_music' => 'Live Music',
            'pidato_mc' => 'Pidato / MC',
            'mic_wireless' => 'Mikrofon Wireless',
            'indoor' => 'Indoor', 'outdoor' => 'Outdoor',
            'ya' => 'Ya', 'tidak' => 'Tidak',
        ];
        $key = strtolower($raw);
        if (isset($map[$key])) return $map[$key];
        $clean = str_replace(['_', '-'], ' ', $raw);
        return ucwords(strtolower($clean));
    }
}
