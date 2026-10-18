<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#141419">
    <title>Dashboard Analitik Owner | JR Audio</title>
    <link rel="icon" href="{{ asset('assets/img/jr-favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-analytics.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-shell.css') }}">
</head>
<body>
@php
    $rupiah = static fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $angka = static fn ($n) => number_format((int) $n, 0, ',', '.');
    $persen = static fn ($a, $b) => $b > 0 ? round(((int) $a / (int) $b) * 100, 1) : 0;
@endphp

<div class="ja-app" id="jaApp">
    @include('layouts.admin-sidebar')
    <main class="ja-main" id="main-content">
        @include('layouts.admin-topbar', ['pageTitle' => 'Dashboard Analitik'])

        <div class="ja-content">
            <section class="ja-heading">
                <div>
                    <p class="ja-eyebrow">BUSINESS OVERVIEW / JR AUDIO</p>
                    <h1>Dashboard <span>Analitik</span></h1>
                    <p class="ja-muted">Pantau pendapatan, aktivitas penyewaan, dan kondisi peralatan dalam satu tempat.</p>
                </div>
                <form method="get" action="{{ url('/dashboard') }}" class="ja-filter">
                    <label for="periode">Periode analisis</label>
                    <select id="periode" name="periode" onchange="this.form.submit()">
                        <option value="semua" @selected($periodeAktif === 'semua')>Semua waktu</option>
                        <option value="30" @selected($periodeAktif === '30')>30 hari terakhir</option>
                        <option value="90" @selected($periodeAktif === '90')>90 hari terakhir</option>
                        <option value="365" @selected($periodeAktif === '365')>365 hari terakhir</option>
                    </select>
                </form>
            </section>

            <div class="ja-period-info"><span class="ja-period-icon">◷</span> Menampilkan data: <strong>{{ $labelPeriode }}</strong> <span class="ja-period-note">Pendapatan mengikuti tanggal pembayaran · Transaksi mengikuti tanggal transaksi</span></div>

            <section class="ja-metrics" aria-label="Ringkasan statistik">
                <article class="ja-metric ja-metric-primary">
                    <div class="ja-metric-top"><span>Total penerimaan</span><span class="ja-metric-icon">↗</span></div>
                    <div class="ja-metric-value ja-money">{{ $rupiah($pendapatan) }}</div>
                    <div class="ja-metric-foot">Pembayaran berstatus <strong>Dikonfirmasi</strong></div>
                </article>
                <article class="ja-metric">
                    <div class="ja-metric-top"><span>Total transaksi</span><span class="ja-metric-icon">▤</span></div>
                    <div class="ja-metric-value">{{ $angka($jumlahTransaksi) }}</div>
                    <div class="ja-metric-foot">{{ $angka($statuses['Berjalan']) }} berjalan · {{ $angka($statuses['Booking']) }} booking</div>
                </article>
                <article class="ja-metric">
                    <div class="ja-metric-top"><span>Unit sedang disewa</span><span class="ja-metric-icon">▣</span></div>
                    <div class="ja-metric-value">{{ $angka($stok['disewa']) }}</div>
                    <div class="ja-metric-foot">{{ $angka($stok['tersedia']) }} unit tersedia sekarang</div>
                </article>
                <article class="ja-metric">
                    <div class="ja-metric-top"><span>Menunggu konfirmasi</span><span class="ja-metric-icon">!</span></div>
                    <div class="ja-metric-value">{{ $angka($jumlahMenunggu) }}</div>
                    <div class="ja-metric-foot">{{ $angka($stok['maintenance']) }} unit maintenance perlu perhatian</div>
                </article>
            </section>

            <section class="ja-dashboard-grid">
                <article class="ja-panel ja-revenue">
                    <div class="ja-panel-header">
                        <div>
                            <p class="ja-panel-tag">KEUANGAN</p>
                            <h2>Penerimaan bulanan</h2>
                            <p class="ja-muted ja-small">{{ $labelGrafik }} · Nominal pembayaran yang sudah dikonfirmasi</p>
                        </div>
                        <span class="ja-panel-chip">6 bulan</span>
                    </div>
                    <div class="ja-bar-chart" role="img" aria-label="Grafik penerimaan bulanan selama enam bulan. @foreach($grafik as $bulan) {{ $bulan['label'] }}: {{ $rupiah($bulan['jumlah']) }}. @endforeach">
                        @foreach($grafik as $bulan)
                            <div class="ja-bar-column">
                                <div class="ja-bar-area">
                                    <div class="ja-bar {{ $bulan['jumlah'] === 0 ? 'is-empty' : '' }}" style="height: {{ $bulan['jumlah'] > 0 ? $bulan['tinggi'] : 2 }}%;" title="{{ $bulan['label'] }} — {{ $rupiah($bulan['jumlah']) }}"></div>
                                </div>
                                <span class="ja-bar-label">{{ $bulan['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="ja-chart-foot"><span class="ja-legend-dot"></span> Pembayaran terkonfirmasi <span class="ja-muted">Bukan laba bersih atau total kontrak</span></div>
                </article>

                <article class="ja-panel ja-status">
                    <div class="ja-panel-header"><div><p class="ja-panel-tag">OPERASIONAL</p><h2>Status transaksi</h2><p class="ja-muted ja-small">Berdasarkan periode yang dipilih</p></div></div>
                    @foreach ([
                        ['Booking', '#f59e0b'], ['Berjalan', '#4361ee'], ['Selesai', '#16a67a'], ['Batal', '#ef5065']
                    ] as $status)
                        <div class="ja-status-row">
                            <div class="ja-status-line"><span><i class="ja-status-dot" style="background: {{ $status[1] }}"></i>{{ $status[0] }}</span><strong>{{ $angka($statuses[$status[0]]) }}</strong></div>
                            <div class="ja-track"><span style="width: {{ $persen($statuses[$status[0]], $jumlahTransaksi) }}%; background: {{ $status[1] }};"></span></div>
                        </div>
                    @endforeach
                    <div class="ja-status-footer"><span>Total</span><strong>{{ $angka($jumlahTransaksi) }} transaksi</strong></div>
                </article>

                <article class="ja-panel ja-stock">
                    <div class="ja-panel-header"><div><p class="ja-panel-tag">INVENTARIS</p><h2>Kondisi barang</h2><p class="ja-muted ja-small">Posisi stok saat ini, tidak terpengaruh filter tanggal</p></div><span class="ja-panel-chip">{{ $angka($stok['jenis']) }} jenis</span></div>
                    <div class="ja-stock-total"><strong>{{ $angka($stok['total']) }}</strong><span>Total unit fisik</span></div>
                    <div class="ja-stock-track" aria-label="Proporsi stok barang">
                        @if ($stok['total'] > 0)
                            <span style="width: {{ $persen($stok['tersedia'], $stok['total']) }}%; background:#13b88a;"></span>
                            <span style="width: {{ $persen($stok['disewa'], $stok['total']) }}%; background:#556bea;"></span>
                            <span style="width: {{ $persen($stok['maintenance'], $stok['total']) }}%; background:#fa9f42;"></span>
                        @endif
                    </div>
                    <div class="ja-stock-legend">
                        <div><span><i class="ja-status-dot" style="background:#13b88a"></i>Tersedia</span><strong>{{ $angka($stok['tersedia']) }}</strong></div>
                        <div><span><i class="ja-status-dot" style="background:#556bea"></i>Disewa</span><strong>{{ $angka($stok['disewa']) }}</strong></div>
                        <div><span><i class="ja-status-dot" style="background:#fa9f42"></i>Maintenance</span><strong>{{ $angka($stok['maintenance']) }}</strong></div>
                    </div>
                </article>

                <article class="ja-panel ja-popular">
                    <div class="ja-panel-header"><div><p class="ja-panel-tag">PERFORMA PRODUK</p><h2>Barang terlaris</h2><p class="ja-muted ja-small">Jumlah unit disewa dalam transaksi non-batal</p></div><span class="ja-panel-chip">TOP 5</span></div>
                    @forelse ($barangTerlaris as $idx => $item)
                        <div class="ja-popular-row">
                            <span class="ja-rank">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="ja-popular-name"><strong>{{ $item->nama_barang }}</strong><small>{{ $item->kategori }}</small></span>
                            <span class="ja-popular-qty"><strong>{{ $angka($item->total_disewa) }}</strong><small>unit</small></span>
                        </div>
                    @empty
                        <p class="ja-empty">Belum ada penyewaan barang pada periode ini.</p>
                    @endforelse
                </article>
            </section>

            <section class="ja-bottom-grid">
                <article class="ja-panel ja-insights">
                    <div class="ja-panel-header"><div><p class="ja-panel-tag">SMART INSIGHT</p><h2>Catatan untuk Owner</h2><p class="ja-muted ja-small">Ringkasan otomatis berbasis aturan dari database</p></div><span class="ja-spark">✳</span></div>
                    <div class="ja-insight-list">
                        @foreach ($insights as $insight)
                            <div class="ja-insight {{ $insight['tipe'] === 'warning' ? 'ja-insight-warning' : '' }}">
                                <span class="ja-insight-icon" aria-hidden="true">{{ $insight['tipe'] === 'warning' ? '!' : '↗' }}</span>
                                <div><strong>{{ $insight['judul'] }}</strong><p>{{ $insight['pesan'] }}</p></div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="ja-panel ja-recent">
                    <div class="ja-panel-header"><div><p class="ja-panel-tag">AKTIVITAS</p><h2>Transaksi terbaru</h2><p class="ja-muted ja-small">Enam transaksi terbaru pada periode ini</p></div><a class="ja-text-link" href="{{ url('/admin/transaksi') }}">Lihat semua ↗</a></div>
                    <div class="ja-table-scroller">
                        <table class="ja-table">
                            <thead><tr><th>ID / Pelanggan</th><th>Tanggal</th><th>Status</th><th>Nilai kontrak</th></tr></thead>
                            <tbody>
                            @forelse($transaksiTerbaru as $item)
                                <tr>
                                    <td><a href="{{ url('/admin/transaksi/detail') }}?id={{ (int) $item->id_transaksi }}">#{{ $item->id_transaksi }}</a><small>{{ $item->nama_pelanggan ?: 'Pelanggan tidak tersedia' }}</small></td>
                                    <td>{{ date('d/m/Y', strtotime($item->tanggal_transaksi)) }}</td>
                                    <td><span class="ja-badge ja-badge-{{ strtolower($item->status_transaksi) }}">{{ $item->status_transaksi }}</span></td>
                                    <td>{{ $rupiah($item->total_harga) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="ja-empty">Belum ada transaksi pada periode ini.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
            <footer class="ja-footer"><span>© {{ date('Y') }} JR Audio · Dashboard Owner</span><span>Ringkasan operasional JR Audio</span></footer>
        </div>
    </main>
</div>
<script src="{{ asset('assets/js/admin-shell.js') }}" defer></script>
</body>
</html>
