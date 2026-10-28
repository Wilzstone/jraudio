<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Paket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rekomendasi awal berbasis wawancara pemilik JR Audio.
 * Tidak mengubah stok/booking; tidak mengklaim kecukupan sistem audio secara teknis.
 */
class RecommendationService
{
    public function rekomendasikan(array $input): array
    {
        $katalog = Barang::query()->orderBy('harga_sewa')->get();
        $tanggal = $input['tanggal_acara'] ?? null;

        if ($tanggal) {
            // Stok fisik perkiraan = tersedia + disewa + maintenance.
            // Booking/berjalan pada hari acara dikurangkan; hasil tetap HARUS diverifikasi.
            $terpesan = DB::table('detail_transaksi as d')
                ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
                ->where('d.tipe_item', 'Barang')
                ->whereNotNull('d.id_barang')
                ->whereIn('t.status_transaksi', ['Booking', 'Berjalan'])
                ->whereDate('t.tanggal_sewa', '<=', $tanggal)
                ->whereDate('t.tanggal_kembali', '>=', $tanggal)
                ->select('d.id_barang', DB::raw('SUM(d.qty) as jumlah_terpesan'))
                ->groupBy('d.id_barang')
                ->pluck('jumlah_terpesan', 'id_barang');

            foreach ($katalog as $barang) {
                $kapasitas = max(0, (int) $barang->stok + (int) $barang->stok_disewa);
                $pesanan = (int) ($terpesan[$barang->id_barang] ?? 0);
                $barang->setAttribute('stok_estimasi_tanggal', max(0, $kapasitas - $pesanan));
            }
        }

        $hasil = $this->dariKatalog($input, $katalog);
        // Harga paket bersumber dari database, tidak berasal dari total harga barang satuan.
        $kode = $hasil['pilihan_paket'];
        $nama = RecommendationRules::PAKET[$kode]['nama'] ?? null;
        $teksHarga = $nama
            ? Paket::query()->where('nama_paket', $nama)->where('status', 'Aktif')->value('range_harga')
            : null;
        $hasil['range_paket'] = $teksHarga;
        $hasil['harga_paket'] = RecommendationRules::bacaRentangHarga($teksHarga);
        // Tarif genset sementara sesuai keputusan Owner; bukan tarif barang satuan paket.
        $biayaGenset = (int)($hasil['biaya_genset'] ?? 0);
        $hasil['harga_total'] = $hasil['harga_paket'] ? [
            'minimum' => $hasil['harga_paket']['minimum'] + $biayaGenset,
            'maksimum' => $hasil['harga_paket']['maksimum'] + $biayaGenset,
        ] : null;
        $hasil['status_anggaran'] = self::statusAnggaran(
            $hasil['harga_total'], (int) $hasil['anggaran']
        );
        $hasil['sesuai_anggaran'] = $hasil['status_anggaran'] === 'cukup_untuk_rentang';
        if ($hasil['status_anggaran'] === 'di_bawah_minimum') {
            $hasil['kebutuhan_crew'][] = 'Anggaran di bawah referensi minimum paket; perlu diskusi penyesuaian, bukan pengurangan alat otomatis.';
        }
        if (!$hasil['harga_paket'] && $kode !== 'custom') {
            $hasil['kebutuhan_crew'][] = 'Tarif paket belum tersedia pada tabel paket. Minta crew memberikan estimasi resmi.';
        }

        return $hasil;
    }

    /**
     * Bandingkan dengan rentang harga resmi yang disimpan pada tabel paket.
     * Perhitungan harga total memasukkan genset Rp400.000 jika diperlukan, tetapi belum termasuk transportasi dan tambahan lain.
     */
    public static function statusAnggaran(?array $harga, int $anggaran): string
    {
        if ($anggaran <= 0) return 'belum_diisi';
        if ($harga === null) return 'menunggu_harga';
        if ($anggaran < $harga['minimum']) return 'di_bawah_minimum';
        if ($anggaran < $harga['maksimum']) return 'di_dalam_rentang';
        return 'cukup_untuk_rentang';
    }

    /**
     * Pure rule evaluation untuk tes tanpa DB. Katalog berisi model Barang.
     */
    public function dariKatalog(array $input, Collection $katalog): array
    {
        $jenis = (string) ($input['jenis_acara'] ?? 'lainnya');
        $kegiatan = (string) ($input['kegiatan'] ?? 'umum');
        $lokasi = (string) ($input['lokasi'] ?? 'indoor');
        $peserta = max(0, (int) ($input['jumlah_peserta'] ?? 0));
        $anggaran = max(0, (int) ($input['anggaran'] ?? 0));
        $pemilihan = RecommendationRules::pilihOtomatis($input);
        $pilihan = $pemilihan['kode'];
        $paket = RecommendationRules::PAKET[$pilihan] ?? null;
        $bass = (string) ($input['butuh_bass'] ?? 'belum_tahu');
        $listrik = (string) ($input['kondisi_listrik'] ?? 'belum_tahu');
        $ruangan = (string) ($input['kondisi_ruangan'] ?? 'belum_tahu');
        $preferensi = 'otomatis'; // Pelanggan tidak lagi diminta memilih jenis speaker.
        $live = $jenis === 'live_music' || $kegiatan === 'musik_live';
        $perluGenset = (bool) ($input['butuh_genset'] ?? false) || $listrik === 'tidak_cukup';
        $micKabel = max(0, (int) ($input['jumlah_mic_kabel'] ?? 0));
        $micWireless = max(0, (int) ($input['jumlah_mic_wireless'] ?? 0));
        $monitorDiminta = max(0, (int) ($input['jumlah_monitor'] ?? 0));
        $durasi = max(1, (int) ($input['durasi_jam'] ?? 12));

        $items = [];
        $catatan = [];
        $aturan = [];
        $kebutuhanCrew = [];
        $alokasi = [];
        $butuhVendor = false;
        $estimasiLengkap = true;
        $totalAcuan = 0;
        $aturan[] = 'AUTO: ' . $pemilihan['alasan'];
        $kebutuhanCrew[] = 'Tim kami akan memastikan pilihan peralatan sesuai ukuran tempat, jumlah tamu, dan kebutuhan acaramu.';

        $catatan[] = 'Jumlah peserta (' . number_format($peserta, 0, ',', '.') . ') dan area ' . $lokasi . ' dipakai untuk diskusi cakupan suara. Pemilik belum menetapkan rumus jumlah speaker per peserta; sistem tidak menebak jumlah tambahan.';
        $aturan[] = 'R01: jumlah peserta/luas lokasi bukan rumus otomatis';

        if ($ruangan === 'bergema' && $lokasi === 'indoor') {
            $kebutuhanCrew[] = 'Ruangan bergema: penempatan dan pengaturan speaker perlu diperiksa lebih lanjut.';
            $aturan[] = 'R02: indoor bergema => review akustik';
        }
        if (empty($input['luas_panjang']) || empty($input['luas_lebar'])) {
            $kebutuhanCrew[] = 'Ukuran lokasi perlu dikonfirmasi agar penempatan speaker tepat.';
        }
        if ($durasi > 12) {
            $kebutuhanCrew[] = 'Durasi acara lebih dari 12 jam: tarif akan disesuaikan bersama tim JR Audio.';
            $aturan[] = 'R03: durasi > 12 jam => review tarif';
        }
        if ($durasi > 24) {
            $kebutuhanCrew[] = 'Acara berlangsung lebih dari satu hari: ketersediaan alat perlu dikonfirmasi.';
        }
        if ($listrik === 'belum_tahu') {
            $kebutuhanCrew[] = 'Pastikan kapasitas listrik di lokasi acara sebelum pemesanan.';
        }
        if ($perluGenset) {
            $kebutuhanCrew[] = 'Kebutuhan genset dan biaya tambahannya akan dibantu oleh tim JR Audio.';
            $aturan[] = 'R04: listrik tidak cukup/permintaan genset => review daya';
        }
        if ($bass === 'ya') {
            $aturan[] = 'R05: subwoofer diperlukan untuk kebutuhan bass';
        } elseif ($bass === 'tidak' && $paket) {
            $kebutuhanCrew[] = 'Jika tidak membutuhkan bass, tim kami dapat membantu menyesuaikan pilihan perlengkapan.';
        }
        if ($live) {
            $kebutuhanCrew[] = 'Untuk live music, tim kami akan memastikan kebutuhan monitor panggung, amplifier, dan DI Box.';
            $aturan[] = 'R06: live music => monitor + amplifier + DI Box (jumlah dikonfirmasi)';
            if (!$katalog->contains(fn ($b) => stripos((string) $b->nama_barang, 'di box') !== false || stripos((string) $b->nama_barang, 'di-box') !== false)) {
                $kebutuhanCrew[] = 'Ketersediaan DI Box akan dikonfirmasi oleh tim JR Audio.';
            }
        }
        if ($micKabel > 0) {
            $kebutuhanCrew[] = 'Permintaan ' . $micKabel . ' mikrofon kabel akan dikonfirmasi jenis, ketersediaan, dan biayanya.';
            $aturan[] = 'R07: mikrofon kabel sesuai permintaan';
        }

        // Tambahkan peralatan berdasarkan peran, bukan hanya kategori.
        // Pemakaian satu tipe barang pada dua peran (FOH dan monitor) berbagi stok.
        $tambahkan = function (string $peran, int $qty, callable $cocok, string $alasan) use (
            $katalog, &$items, &$catatan, &$alokasi, &$butuhVendor, &$estimasiLengkap, &$totalAcuan
        ): ?object {
            if ($qty <= 0) {
                return null;
            }
            $barang = $katalog->first($cocok);
            if (!$barang) {
                $items[] = [
                    'id_barang' => null, 'nama_barang' => $peran . ' (belum ada di katalog)',
                    'kategori' => $peran, 'peran' => $peran, 'jumlah' => $qty,
                    'jumlah_dianjurkan' => $qty, 'jumlah_internal' => 0,
                    'jumlah_vendor' => $qty, 'stok_saat_ini' => 0,
                    'harga_satuan' => null, 'subtotal' => null, 'alasan' => $alasan,
                ];
                $catatan[] = $peran . ' belum tersedia sebagai item katalog; perlu verifikasi crew/vendor.';
                $butuhVendor = true;
                $estimasiLengkap = false;
                return null;
            }
            $id = (int) $barang->id_barang;
            $tersedia = max(0, (int) ($barang->stok_estimasi_tanggal ?? $barang->stok));
            $tersisa = max(0, $tersedia - ($alokasi[$id] ?? 0));
            $internal = min($qty, $tersisa);
            $vendor = $qty - $internal;
            $alokasi[$id] = ($alokasi[$id] ?? 0) + $internal;
            if ($vendor > 0) {
                $butuhVendor = true;
                $catatan[] = $peran . ': dibutuhkan ' . $qty . ' unit, estimasi stok internal yang dapat dialokasikan ' . $internal . '. Kekurangan ' . $vendor . ' unit perlu konfirmasi/pengadaan mitra.';
            }
            $harga = (int) $barang->harga_sewa;
            $totalAcuan += $qty * $harga;
            $items[] = [
                'id_barang' => $id, 'nama_barang' => (string) $barang->nama_barang,
                'kategori' => (string) $barang->kategori, 'peran' => $peran,
                'jumlah' => $qty, 'jumlah_dianjurkan' => $qty, 'jumlah_internal' => $internal,
                'jumlah_vendor' => $vendor, 'stok_saat_ini' => $tersedia,
                'harga_satuan' => $harga, 'subtotal' => $qty * $harga,
                'alasan' => $alasan,
            ];
            return $barang;
        };

        $speaker = fn ($b) => strtolower((string) $b->kategori) === 'speaker';
        $isArray = fn ($b) => $speaker($b) && stripos((string) $b->nama_barang, 'array') !== false;
        $isHuper = fn ($b) => $speaker($b) && stripos((string) $b->nama_barang, 'huper') !== false;

        if ($paket) {
            $aturan[] = 'R08: komposisi ' . $paket['nama'] . ' sesuai wawancara Owner';
            $speakerTipe = 'line_array';
            if ($pilihan === 'paket2500') {
                $array = $katalog->first($isArray);
                $stokArray = $array ? (int) ($array->stok_estimasi_tanggal ?? $array->stok) : 0;
                if ($preferensi === 'huper' || ($preferensi === 'otomatis' && $stokArray < 2)) {
                    $speakerTipe = 'huper';
                }
            } elseif ($preferensi === 'huper') {
                $catatan[] = 'Paket 5000 menurut Owner menggunakan line array; pilihan Huper tidak diterapkan otomatis.';
            }
            if ($speakerTipe === 'huper') {
                $catatan[] = 'Speaker aktif Huper dipakai sebagai opsi utama Paket 2500. Penggunaan tanpa mixer dimungkinkan, tetapi kualitas/pengaturan suara berbeda dan perlu penilaian crew.';
            }

            $tambahkan('Speaker utama', $paket['speaker_utama'], $speakerTipe === 'huper' ? $isHuper : $isArray,
                'Susunan speaker utama paket menurut Owner. Luas/akustik lokasi tetap harus ditinjau.');
            $tambahkan('Subwoofer', $paket['subwoofer'],
                fn ($b) => strtolower((string) $b->kategori) === 'subwoofer',
                'Jumlah subwoofer berasal dari komposisi paket Owner; kebutuhan bass sebenarnya mengikuti acara.');
            // Pada paket_detail SQL lama, Huper dipakai sebagai speaker monitor.
            // Ini asumsi mapping katalog (bukan sertifikasi kesesuaian teknis).
            $monitorKhusus = $katalog->first(fn ($b) => $speaker($b) && stripos((string) $b->nama_barang, 'monitor') !== false);
            $monitor = $tambahkan('Speaker monitor', $paket['monitor'], $monitorKhusus
                ? fn ($b) => $speaker($b) && stripos((string) $b->nama_barang, 'monitor') !== false
                : $isHuper,
                'Untuk monitoring di panggung. Model/penempatan final harus disetujui crew.');
            if (!$monitorKhusus && $monitor) {
                $catatan[] = 'Speaker Huper 15 inci digunakan sementara sebagai kandidat monitor berdasarkan isi paket_detail SQL lama. Kecocokan teknis tetap wajib dikonfirmasi crew.';
            }
            $tambahkan('Mikrofon wireless', max($paket['mic_wireless'], $micWireless),
                fn ($b) => strtolower((string) $b->kategori) === 'microphone' && stripos((string) $b->nama_barang, 'wireless') !== false,
                'Jumlah dasar sesuai paket; tambahan mengikuti kebutuhan mikrofon yang diisi pelanggan.');
            if ($speakerTipe === 'line_array' || $live) {
                $tambahkan('Mixer', 1,
                    fn ($b) => strtolower((string) $b->kategori) === 'mixer',
                    'Umumnya diperlukan untuk pengaturan sumber suara. Jumlah channel/perangkat pelengkap diverifikasi crew.');
                $kebutuhanCrew[] = 'Tim kami akan menyiapkan aksesori dan pengaturan audio yang sesuai.';
            }
            if ($paket['speaker_utama'] > 0) {
                $catatan[] = $paket['catatan'];
            }
        } else {
            $aturan[] = 'R09: sewa satuan/custom => konfirmasi jumlah teknis oleh crew';
            $kebutuhanCrew[] = 'Untuk kebutuhan khusus, tim JR Audio akan membantu menentukan jumlah alat dan harganya.';
            if ($micWireless > 0) {
                $tambahkan('Mikrofon wireless', $micWireless,
                    fn ($b) => strtolower((string) $b->kategori) === 'microphone' && stripos((string) $b->nama_barang, 'wireless') !== false,
                    'Jumlah mikrofon sesuai kebutuhan yang diisi penyewa.');
            }
            $estimasiLengkap = false;
        }

        if ($perluGenset) {
            // Genset JR Audio adalah barang INTERNAL. Jangan membuat item External
            // otomatis: id_barang diperlukan agar pengajuan -> transaksi -> stok konsisten.
            $genset = $katalog->first(fn ($b) =>
                strtolower(trim((string) $b->nama_barang)) === 'generator set 9 sd 20 kva'
            );
            if ($genset) {
                $tambahkan('Daya listrik tambahan', 1,
                    fn ($b) => (int) $b->id_barang === (int) $genset->id_barang,
                    'Generator set 9 sd 20 kVA dari inventaris JR Audio. Kapasitas daya perlu dikonfirmasi crew.');
                $aturan[] = 'R11: listrik kurang => Generator set 9 sd 20 kVA internal';
                if (max(0, (int) ($genset->stok_estimasi_tanggal ?? $genset->stok)) < 1) {
                    $kebutuhanCrew[] = 'Generator set 9 sd 20 kVA belum tersedia pada perkiraan stok. Crew harus menyelesaikan ketersediaan sebelum transaksi disimpan.';
                }
            } else {
                // Tidak mengarang ID inventaris dan tidak mengubahnya menjadi External.
                $estimasiLengkap = false;
                $kebutuhanCrew[] = 'Generator set 9 sd 20 kVA belum tercatat di inventaris. Crew perlu memeriksa data barang sebelum transaksi.';
                $catatan[] = 'Barang genset yang diminta tidak ditemukan di katalog; tidak dibuat item luar otomatis.';
            }
            $kebutuhanCrew[] = 'Biaya genset awal Rp400.000 dan dapat disesuaikan oleh crew saat transaksi.';
        }
        if ($butuhVendor) {
            $kebutuhanCrew[] = 'Sebagian perlengkapan perlu dicek ketersediaannya. Tim JR Audio akan membantu mencari alternatif.';
            $aturan[] = 'R10: kekurangan stok => opsi mitra, bukan otomatis booking';
        }
        if ($kegiatan === 'musik_rekaman' && $bass === 'belum_tahu') {
            $kebutuhanCrew[] = 'Apakah kamu memerlukan suara bass tambahan? Tim kami akan membantu menentukannya.';
        }
        if (!empty($input['rundown'])) {
            $kebutuhanCrew[] = 'Tim kami akan meninjau rundown untuk memastikan kebutuhan perlengkapan.';
        } else {
            $kebutuhanCrew[] = 'Silakan siapkan rundown acara saat berkonsultasi dengan tim kami.';
        }

        $catatan[] = 'Data tanggal menggunakan catatan booking aktif untuk estimasi konflik; tidak menjamin ketersediaan final dan tidak mengunci stok.';
        $catatan[] = 'Harga pada tabel adalah acuan harga barang SATUAN dari katalog, bukan harga resmi paket. Biaya transportasi di luar harga paket; genset menggunakan biaya awal Rp400.000 bila dibutuhkan.';
        $catatan[] = 'Tidak ada booking/pengurangan stok yang dilakukan. Rekomendasi ini dapat dijadikan bahan penawaran, tetapi hanya final setelah persetujuan crew.';
        // Evaluasi anggaran baru dilakukan dengan RENTANG HARGA PAKET dari tabel paket,
        // bukan total harga satuan yang tidak merepresentasikan tarif bundling.

        return [
            'barang' => $items,
            'biaya_genset' => $perluGenset ? 400000 : 0,
            'total' => $totalAcuan, // acuan harga SATUAN, tidak dipakai sebagai tarif paket
            'alasan_pemilihan' => $pemilihan['alasan'],
            'tingkat_kepastian' => $pemilihan['keyakinan'],
            'pemilihan_otomatis' => true,
            'estimasi_lengkap' => $estimasiLengkap && !$butuhVendor,
            'anggaran' => $anggaran,
            'sesuai_anggaran' => null, // dihitung berdasarkan rentang paket di rekomendasikan()
            'catatan' => array_values(array_unique($catatan)),
            'kebutuhan_crew' => array_values(array_unique($kebutuhanCrew)),
            'paket_hint' => $paket['nama'] ?? 'Sewa satuan / custom',
            'pilihan_paket' => $pilihan,
            'range_paket' => null,
            'harga_paket' => null,
            'status_anggaran' => 'menunggu_harga',
            'aturan_aktif' => array_values(array_unique($aturan)),
            'aturan' => RecommendationRules::VERSION,
            'perlu_konsultasi' => true,
        ];
    }
}
