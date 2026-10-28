<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\PelangganNomorService;
use Illuminate\Validation\ValidationException;

class PengajuanPublikController extends Controller
{
    public function simpan(Request $request)
    {
        $data = $request->validate([
            'draft_token' => 'required|string|size:40|alpha_num',
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp' => ['required', 'string', 'regex:/^(?:\+?62|0)[0-9]{8,13}$/'],
            'setuju' => 'accepted',
        ], ['setuju.accepted' => 'Konfirmasi persetujuan diperlukan sebelum melanjutkan.']);

        $key = 'jr_pengajuan_draft.'.$data['draft_token'];
        $draft = $request->session()->get($key);
        if (!is_array($draft) || (int)($draft['expires_at'] ?? 0) < time()) {
            return redirect()->route('rekomendasi.index')->with('error', 'Hasil rekomendasi sudah kedaluwarsa. Silakan isi kembali kebutuhan acara.');
        }
        $input = $draft['input'] ?? [];
        $hasil = $draft['hasil'] ?? [];
        if (empty($input['tanggal_acara']) || empty($hasil['pilihan_paket'])) {
            throw ValidationException::withMessages(['draft_token' => 'Data rekomendasi tidak lengkap. Silakan ulangi formulir.']);
        }
        $hash = hash('sha256', $data['draft_token']);
        $id = DB::table('pengajuan_sewa')->where('token_hash', $hash)->value('kode');
        if (!$id) {
            $kode = 'JR-'.date('ymd').'-'.Str::upper(Str::random(12));
            try {
                DB::transaction(function () use ($data, $input, $hasil, $kode, $hash) {
                    $nomor = PelangganNomorService::normalisasi($data['no_hp']);
                    $idPelanggan = PelangganNomorService::cariAtauBuat($data['nama_pelanggan'], $nomor);
                    $pengajuanId = DB::table('pengajuan_sewa')->insertGetId([
                        'kode' => $kode, 'token_hash' => $hash, 'id_pelanggan' => $idPelanggan,
                        'nama_pelanggan' => trim($data['nama_pelanggan']),
                        'no_hp' => $nomor,
                        'jenis_acara' => $input['jenis_acara'],
                        'lokasi_acara' => $input['lokasi_acara'] ?? null,
                        'tanggal_acara' => $input['tanggal_acara'],
                        'rekomendasi_paket' => $hasil['pilihan_paket'],
                        'estimasi_min' => $hasil['harga_total']['minimum'] ?? null,
                        'estimasi_max' => $hasil['harga_total']['maksimum'] ?? null,
                        'kebutuhan_json' => json_encode($input, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        'biaya_genset' => (int)($hasil['biaya_genset'] ?? 0),
                        'status' => 'Menunggu', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    foreach ($hasil['barang'] ?? [] as $item) {
                        if ((int)($item['jumlah'] ?? 0) < 1) continue;
                        DB::table('pengajuan_detail')->insert([
                            'id_pengajuan' => $pengajuanId,
                            'id_barang' => $item['id_barang'] ?: null,
                            'nama_barang' => mb_substr((string)$item['nama_barang'], 0, 150),
                            'peran' => mb_substr((string)($item['peran'] ?? ''), 0, 100),
                            'qty' => (int)$item['jumlah'],
                            'jumlah_vendor' => (int)($item['jumlah_vendor'] ?? 0),
                            'tipe_item' => $item['id_barang'] ? 'Barang' : 'External',
                            'sumber_external' => $item['sumber_external'] ?? null,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                });
                $id = $kode;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $id = DB::table('pengajuan_sewa')->where('token_hash', $hash)->value('kode');
                if (!$id) throw $e;
            }
        }
        $request->session()->forget($key);
        return redirect()->route('pengajuan.public.show', ['kode' => $id]);
    }

    public function show(string $kode)
    {
        $pengajuan = DB::table('pengajuan_sewa')->where('kode', $kode)->first();
        abort_unless($pengajuan, 404);
        $detail = DB::table('pengajuan_detail')->where('id_pengajuan', $pengajuan->id_pengajuan)->get();
        $input = json_decode($pengajuan->kebutuhan_json, true) ?: [];
        $ringkasan = [
            'Halo JR Audio, saya ingin konsultasi pengajuan sewa.',
            '*Kode pengajuan:* '.$pengajuan->kode,
            '*Nama:* '.$pengajuan->nama_pelanggan,
            '*Acara:* '.str_replace('_', ' ', $pengajuan->jenis_acara),
            '*Tanggal:* '.$pengajuan->tanggal_acara,
            '*Peserta:* '.(int)($input['jumlah_peserta'] ?? 0).' orang',
            '*Kegiatan:* '.str_replace('_',' ',(string)($input['kegiatan'] ?? '-')),
            '*Indoor/Outdoor:* '.($input['lokasi'] ?? '-'),
            '*Kondisi ruangan:* '.str_replace('_',' ',(string)($input['kondisi_ruangan'] ?? '-')),
            '*Sumber listrik:* '.str_replace('_',' ',(string)($input['kondisi_listrik'] ?? '-')),
            '*Durasi:* '.(int)($input['durasi_jam'] ?? 0).' jam',
            '*Anggaran:* '.(!empty($input['anggaran']) ? 'Rp '.number_format((int)$input['anggaran'],0,',','.') : 'Belum ditentukan'),
            '*Lokasi:* '.($pengajuan->lokasi_acara ?: ($input['lokasi'] ?? '-')),
            '*Paket awal:* '.($pengajuan->rekomendasi_paket === 'custom' ? 'Custom' : str_replace('paket', 'Paket ', $pengajuan->rekomendasi_paket)),
            '*Peralatan:*',
        ];
        foreach ($detail as $row) $ringkasan[] = '- '.$row->nama_barang.' : '.$row->qty.' unit';
        $ringkasan[] = '*Rundown:* '.mb_substr(trim((string)($input['rundown'] ?? '')) ?: 'Belum diisi',0,500);
        $ringkasan[] = '*Tambahan genset:* Rp '.number_format((int)$pengajuan->biaya_genset,0,',','.');
        $ringkasan[] = '*Estimasi termasuk genset:* '.($pengajuan->estimasi_min !== null ? 'Rp '.number_format((int)$pengajuan->estimasi_min,0,',','.').' - Rp '.number_format((int)$pengajuan->estimasi_max,0,',','.') : 'Dikonfirmasi crew');
        $ringkasan[] = 'Mohon bantu cek ketersediaan, kebutuhan alat, dan harga final. Terima kasih.';
        $nomor = preg_replace('/\D+/', '', (string)config('jraudio.whatsapp_number'));
        $wa = preg_match('/^[1-9][0-9]{7,14}$/', $nomor)
            ? 'https://wa.me/'.$nomor.'?text='.rawurlencode(implode("\n", $ringkasan)) : null;
        return view('pengajuan.konfirmasi', compact('pengajuan', 'detail', 'wa'));
    }
}
