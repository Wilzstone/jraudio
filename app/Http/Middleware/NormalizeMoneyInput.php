<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Terima Rp400.000 dan 400000 sebagai integer 400000; tidak mengubah qty/ID/nomor HP. */
class NormalizeMoneyInput
{
    private const MONEY_FIELDS = [
        'harga_sewa', 'harga_paket_manual', 'harga_final', 'biaya_genset',
        'harga_manual_item', 'jumlah_bayar', 'denda_manual', 'harga_satuan',
        'biaya_transportasi', 'total_harga',
    ];

    /** Normalisasi hanya input rupiah dengan pola grup ribuan yang valid. */
    public static function normalize($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'normalize'], $value);
        }
        if (!is_string($value)) return $value;
        $trim = trim($value);
        // Tidak boleh menafsirkan angka pecahan sebagai nominal rupiah.
        if (preg_match('/^\d{1,3}(?:\.\d{3})+$/D', $trim)) {
            return str_replace('.', '', $trim);
        }
        return $value;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            $input = [];
            foreach (self::MONEY_FIELDS as $key) {
                if ($request->request->has($key)) {
                    $input[$key] = self::normalize($request->input($key));
                }
            }
            if ($input) $request->merge($input);
        }
        return $next($request);
    }
}
