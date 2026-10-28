<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class RekomendasiController extends Controller
{
    public function index()
    {
        return view('rekomendasi.form');
    }

    public function hasil(Request $request, string $draftToken)
    {
        $draft = $request->session()->get('jr_pengajuan_draft.'.$draftToken);
        if (!is_array($draft) || (int)($draft['expires_at'] ?? 0) < time()) {
            return redirect()->route('rekomendasi.index')->with('error', 'Hasil rekomendasi telah kedaluwarsa. Silakan isi kembali kebutuhan acara.');
        }
        $input = $draft['input'];
        $hasil = $draft['hasil'];
        return view('rekomendasi.hasil', compact('input', 'hasil', 'draftToken'));
    }

    public function proses(Request $request, RecommendationService $engine)
    {
        $input = $request->validate([
            'jenis_acara' => ['required', Rule::in(['pernikahan', 'acara_kampus', 'live_music', 'seminar', 'konser', 'sekolah', 'lainnya'])],
            'jumlah_peserta' => 'required|integer|min:1|max:20000',
            'lokasi' => 'required|in:indoor,outdoor',
            'lokasi_acara' => 'nullable|string|max:180',
            'luas_panjang' => 'nullable|numeric|min:1|max:5000',
            'luas_lebar' => 'nullable|numeric|min:1|max:5000',
            'kondisi_ruangan' => 'required|in:normal,bergema,belum_tahu',
            'kegiatan' => 'required|in:pidato,musik_live,musik_rekaman,umum',
            'butuh_bass' => 'required|in:ya,tidak,belum_tahu',
            'kondisi_listrik' => 'required|in:cukup,tidak_cukup,belum_tahu',
            'jumlah_mic_wireless' => 'nullable|integer|min:0|max:100',
            'jumlah_mic_kabel' => 'nullable|integer|min:0|max:100',
            'jumlah_monitor' => 'nullable|integer|min:0|max:20',
            'perlu_peralatan_custom' => 'nullable|boolean',
            'rundown' => 'nullable|string|max:1500',
            'durasi_jam' => 'required|integer|min:1|max:96',
            'anggaran' => 'required|integer|min:0|max:1000000000',
            'tanggal_acara' => 'required|date|after_or_equal:today',
            'butuh_genset' => 'nullable|boolean',
        ]);
        $input['butuh_genset'] = $request->boolean('butuh_genset');
        $input['perlu_peralatan_custom'] = $request->boolean('perlu_peralatan_custom');
        $hasil = $engine->rekomendasikan($input);
        // Draft disimpan di session server, bukan dikirim sebagai input harga/qty yang bisa dimanipulasi.
        $draftToken = Str::random(40);
        $request->session()->put('jr_pengajuan_draft.'.$draftToken, [
            'input' => $input, 'hasil' => $hasil, 'expires_at' => time() + 1800,
        ]);

        return redirect()->route('rekomendasi.hasil', ['draftToken' => $draftToken]);
    }
}
