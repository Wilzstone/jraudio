<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Statistik read-only untuk dashboard Owner.
 * Menggunakan skema JR Audio yang sudah ada, tanpa migration atau modifikasi transaksi.
 */
class DashboardAnalyticsService
{
    public function summarize(string $periode = 'semua'): array
    {
        $today = Carbon::today('Asia/Jakarta');
        $start = match ($periode) {
            '30' => $today->copy()->subDays(29),
            '90' => $today->copy()->subDays(89),
            '365' => $today->copy()->subDays(364),
            default => null,
        };
        $periode = in_array($periode, ['semua', '30', '90', '365'], true) ? $periode : 'semua';
        $labels = [
            'semua' => 'Semua waktu',
            '30' => '30 hari terakhir',
            '90' => '90 hari terakhir',
            '365' => '365 hari terakhir',
        ];

        // Pendapatan = pembayaran terkonfirmasi; bukan omzet kontrak atau laba.
        $pembayaran = DB::table('pembayaran')->where('status_konfirmasi', 'Dikonfirmasi');
        $this->filterTanggal($pembayaran, 'tanggal_bayar', $start, $today);
        $pendapatan = (int) $pembayaran->sum('jumlah_bayar');

        $menunggu = DB::table('pembayaran')->where('status_konfirmasi', 'Menunggu');
        $this->filterTanggal($menunggu, 'tanggal_bayar', $start, $today);
        $jumlahMenunggu = (int) $menunggu->count();

        $transaksi = DB::table('transaksi');
        $this->filterTanggal($transaksi, 'tanggal_transaksi', $start, $today);
        $jumlahTransaksi = (int) (clone $transaksi)->count();
        $statuses = ['Booking' => 0, 'Berjalan' => 0, 'Selesai' => 0, 'Batal' => 0];
        foreach ((clone $transaksi)->select('status_transaksi')->selectRaw('COUNT(*) as total')
            ->groupBy('status_transaksi')->get() as $row) {
            if (array_key_exists($row->status_transaksi, $statuses)) {
                $statuses[$row->status_transaksi] = (int) $row->total;
            }
        }

        // stok = tersedia; stok_disewa & stok_maintenance disimpan terpisah pada skema lama.
        $stok = DB::table('barang')->selectRaw('COUNT(*) as jenis_barang')
            ->selectRaw('COALESCE(SUM(stok), 0) as tersedia')
            ->selectRaw('COALESCE(SUM(stok_disewa), 0) as disewa')
            ->selectRaw('COALESCE(SUM(stok_maintenance), 0) as maintenance')
            ->first();
        $stokRingkas = [
            'jenis' => (int) ($stok->jenis_barang ?? 0),
            'tersedia' => (int) ($stok->tersedia ?? 0),
            'disewa' => (int) ($stok->disewa ?? 0),
            'maintenance' => (int) ($stok->maintenance ?? 0),
        ];
        $stokRingkas['total'] = $stokRingkas['tersedia'] + $stokRingkas['disewa'] + $stokRingkas['maintenance'];

        // Barang terlaris dari qty detail transaksi non-batal, bukan dari stok atau harga.
        $populerQuery = DB::table('detail_transaksi as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('barang as b', 'b.id_barang', '=', 'd.id_barang')
            ->where('t.status_transaksi', '!=', 'Batal')
            ->where('d.tipe_item', 'Barang');
        $this->filterTanggal($populerQuery, 't.tanggal_transaksi', $start, $today);
        $barangTerlaris = $populerQuery->select('b.nama_barang', 'b.kategori')
            ->selectRaw('SUM(d.qty) as total_disewa')
            ->groupBy('b.id_barang', 'b.nama_barang', 'b.kategori')
            ->orderByDesc('total_disewa')->limit(5)->get();

        $recentQuery = DB::table('transaksi as t')
            ->leftJoin('pelanggan as p', 't.id_pelanggan', '=', 'p.id_pelanggan');
        $this->filterTanggal($recentQuery, 't.tanggal_transaksi', $start, $today);
        $transaksiTerbaru = $recentQuery
            ->select('t.id_transaksi', 't.tanggal_transaksi', 't.status_transaksi',
                't.total_harga', 'p.nama_pelanggan')
            ->orderByDesc('t.tanggal_transaksi')->orderByDesc('t.id_transaksi')
            ->limit(6)->get();

        // Untuk periode 'semua', titik akhir grafik mengikuti data terbaru agar dump lama
        // tetap terlihat. Untuk filter relatif, gunakan bulan sekarang.
        $anchor = $today->copy();
        if ($periode === 'semua') {
            $maxBayar = DB::table('pembayaran')->max('tanggal_bayar');
            $maxTransaksi = DB::table('transaksi')->max('tanggal_transaksi');
            $latest = max((string) $maxBayar, (string) $maxTransaksi);
            if ($latest !== '') {
                $anchor = Carbon::parse($latest);
            }
        }
        $bulanTerakhir = $anchor->copy()->startOfMonth();
        $bulanAwal = $bulanTerakhir->copy()->subMonthsNoOverflow(5);
        $grafikQuery = DB::table('pembayaran')->where('status_konfirmasi', 'Dikonfirmasi')
            ->whereBetween('tanggal_bayar', [
                $bulanAwal->toDateString(), $bulanTerakhir->copy()->endOfMonth()->toDateString(),
            ]);
        $this->filterTanggal($grafikQuery, 'tanggal_bayar', $start, $today);
        $grafikRows = $grafikQuery
            ->selectRaw("DATE_FORMAT(tanggal_bayar, '%Y-%m') as bulan, SUM(jumlah_bayar) as total")
            ->groupBy(DB::raw("DATE_FORMAT(tanggal_bayar, '%Y-%m')"))
            ->pluck('total', 'bulan')->all();

        $bulanSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $grafik = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $bulanAwal->copy()->addMonthsNoOverflow($i);
            $grafik[] = [
                'label' => $bulanSingkat[$month->month - 1] . ' ' . $month->format('y'),
                'jumlah' => (int) ($grafikRows[$month->format('Y-m')] ?? 0),
            ];
        }
        $maksimumGrafik = max(array_column($grafik, 'jumlah'));
        foreach ($grafik as &$bar) {
            $bar['tinggi'] = $bar['jumlah'] > 0 && $maksimumGrafik > 0
                ? max(8, (int) round(($bar['jumlah'] / $maksimumGrafik) * 100))
                : 0;
        }
        unset($bar);

        // Biaya mitra mengikuti tanggal transaksi (bukan tanggal kas diterima).
        // Jangan mengurangi total penerimaan dengan biaya mitra dan menyebutnya laba bersih.
        $biayaMitraQ = DB::table('detail_transaksi as d')->join('transaksi as t','t.id_transaksi','=','d.id_transaksi')
            ->where('d.tipe_item','External')->where('t.status_transaksi','<>','Batal');
        $this->filterTanggal($biayaMitraQ, 't.tanggal_transaksi', $start, $today);
        $mi = $biayaMitraQ->selectRaw('COUNT(*) as baris')
            ->selectRaw('COALESCE(SUM(CASE WHEN d.harga_mitra IS NOT NULL THEN d.qty*d.harga_mitra ELSE 0 END),0) as biaya_tercatat')
            ->selectRaw('SUM(CASE WHEN d.harga_mitra IS NULL THEN 1 ELSE 0 END) as belum_tercatat')
            ->selectRaw("COALESCE(SUM(CASE WHEN d.harga_mitra IS NOT NULL AND (t.jenis_transaksi<>'Paketan' OR d.termasuk_paket=0) THEN d.subtotal-d.qty*d.harga_mitra ELSE 0 END),0) as margin_terpisah")
            ->first();
        $mitraRingkas = [
            'baris'=>(int)($mi->baris ?? 0), 'biaya'=>(int)($mi->biaya_tercatat ?? 0),
            'belum_tercatat'=>(int)($mi->belum_tercatat ?? 0),
            'margin_terpisah'=>(int)($mi->margin_terpisah ?? 0),
        ];

        // Insight bersifat deskriptif berbasis aturan, bukan AI atau perkiraan masa depan.
        $insights = [];
        if ($jumlahMenunggu > 0) {
            $insights[] = [
                'tipe' => 'warning',
                'judul' => 'Pembayaran menunggu konfirmasi',
                'pesan' => number_format($jumlahMenunggu, 0, ',', '.') .
                    ' pembayaran pada periode ini belum dikonfirmasi. Periksa sebelum menghitung penerimaan.',
            ];
        }
        if ($stokRingkas['maintenance'] > 0) {
            $insights[] = [
                'tipe' => 'warning',
                'judul' => 'Ada barang dalam perawatan',
                'pesan' => number_format($stokRingkas['maintenance'], 0, ',', '.') .
                    ' unit tercatat maintenance. Evaluasi sebelum menerima penyewaan berikutnya.',
            ];
        }
        if ($barangTerlaris->isNotEmpty()) {
            $top = $barangTerlaris->first();
            $insights[] = [
                'tipe' => 'info',
                'judul' => 'Barang terlaris pada periode ini',
                'pesan' => $top->nama_barang . ' tercatat disewa sebanyak ' .
                    number_format((int) $top->total_disewa, 0, ',', '.') .
                    ' unit dari transaksi non-batal.',
            ];
        }
        if (!$insights) {
            $insights[] = [
                'tipe' => 'info',
                'judul' => 'Belum ada insight untuk periode ini',
                'pesan' => 'Data transaksi atau pembayaran yang memenuhi kriteria belum cukup untuk menghasilkan ringkasan.',
            ];
        }

        return [
            'periodeAktif' => $periode,
            'labelPeriode' => $labels[$periode],
            'pendapatan' => $pendapatan,
            'mitraRingkas'=>$mitraRingkas,
            'jumlahTransaksi' => $jumlahTransaksi,
            'jumlahMenunggu' => $jumlahMenunggu,
            'statuses' => $statuses,
            'stok' => $stokRingkas,
            'barangTerlaris' => $barangTerlaris,
            'transaksiTerbaru' => $transaksiTerbaru,
            'grafik' => $grafik,
            'labelGrafik' => $bulanSingkat[$bulanAwal->month - 1] . ' ' . $bulanAwal->format('Y')
                . ' – ' . $bulanSingkat[$bulanTerakhir->month - 1] . ' ' . $bulanTerakhir->format('Y'),
            'insights' => $insights,
        ];
    }

    private function filterTanggal($query, string $kolom, ?Carbon $start, Carbon $end): void
    {
        if ($start !== null) {
            $query->whereBetween($kolom, [$start->toDateString(), $end->toDateString()]);
        }
    }
}
