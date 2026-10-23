@include('layouts.header')
@php
    $items = collect($transaksi ?? []);
    $total = $items->count();
    $booking = $items->filter(fn ($row) => ($row['status_transaksi'] ?? '') === 'Booking')->count();
    $berjalan = $items->filter(fn ($row) => ($row['status_transaksi'] ?? '') === 'Berjalan')->count();
    $selesai = $items->filter(fn ($row) => ($row['status_transaksi'] ?? '') === 'Selesai')->count();
    $batal = $items->filter(fn ($row) => ($row['status_transaksi'] ?? '') === 'Batal')->count();
@endphp

<section class="ja-inventory ja-management" data-ja-list data-ja-noun="transaksi" aria-labelledby="jaPageTitle">
    <div class="ja-inventory-heading"><div><span class="ja-inventory-eyebrow">PENYEWAAN / JR AUDIO</span><h1 id="jaPageTitle">Data Transaksi</h1><p>Pantau status penyewaan, jadwal, pelanggan, dan nominal transaksi dalam satu tempat.</p></div><a class="btn ja-inventory-add" href="{{ url('/admin/transaksi/tambah') }}"><span aria-hidden="true">＋</span> Tambah Transaksi</a></div>
    <div class="ja-inventory-stats ja-stats-five" aria-label="Ringkasan status transaksi">
        <div class="ja-stat-card"><span class="ja-stat-label">Total Transaksi</span><strong>{{ number_format($total, 0, ',', '.') }}</strong><small>Semua status</small></div>
        <div class="ja-stat-card ja-stat-rented"><span class="ja-stat-label">Booking</span><strong>{{ number_format($booking, 0, ',', '.') }}</strong><small>Belum berjalan</small></div>
        <div class="ja-stat-card ja-stat-service"><span class="ja-stat-label">Berjalan</span><strong>{{ number_format($berjalan, 0, ',', '.') }}</strong><small>Dalam penyewaan</small></div>
        <div class="ja-stat-card ja-stat-available"><span class="ja-stat-label">Selesai</span><strong>{{ number_format($selesai, 0, ',', '.') }}</strong><small>Transaksi rampung</small></div>
        <div class="ja-stat-card ja-stat-canceled"><span class="ja-stat-label">Batal</span><strong>{{ number_format($batal, 0, ',', '.') }}</strong><small>Transaksi dibatalkan</small></div>
    </div>
    <div class="ja-inventory-panel">
        <div class="ja-inventory-toolbar"><div class="ja-toolbar-intro"><h2>Riwayat Penyewaan</h2><p>Temukan transaksi dengan ID, nama pelanggan, tanggal, atau petugas.</p></div>
            <div class="ja-inventory-controls">
                <label class="ja-control-search"><span class="visually-hidden">Cari transaksi</span><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.7"/><path d="m16 16 4.7 4.7"/></svg><input type="search" data-ja-search placeholder="Cari transaksi / pelanggan..." aria-label="Cari transaksi" autocomplete="off"></label>
                <label class="ja-control-select"><span class="visually-hidden">Filter status transaksi</span><select data-ja-filter aria-label="Filter status transaksi"><option value="">Semua status</option><option value="Booking">Booking</option><option value="Berjalan">Berjalan</option><option value="Selesai">Selesai</option><option value="Batal">Batal</option></select></label>
                <label class="ja-control-select"><span class="visually-hidden">Urutkan transaksi</span><select data-ja-sort aria-label="Urutkan transaksi"><option value="id-desc">Terbaru (ID)</option><option value="id-asc">Terlama (ID)</option><option value="date-desc">Sewa terbaru</option><option value="date-asc">Sewa terlama</option><option value="amount-desc">Nominal terbesar</option><option value="amount-asc">Nominal terkecil</option></select></label>
            </div>
        </div>
        <div class="ja-inventory-meta"><p data-ja-counter role="status" aria-live="polite">Menampilkan {{ $total }} dari {{ $total }} transaksi</p><button type="button" class="ja-reset-filter" data-ja-reset hidden>Reset filter</button></div>
        <div class="ja-list-table-wrap"><table class="ja-list-table ja-list-transactions" aria-label="Daftar transaksi penyewaan JR Audio">
            <thead><tr><th scope="col">Transaksi / Pelanggan</th><th scope="col">Jenis</th><th scope="col">Jadwal Sewa</th><th scope="col">Petugas</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col" class="ja-list-right">Aksi</th></tr></thead>
            <tbody data-ja-body>
                @foreach ($items as $row)
                    @php
                        $id = (int) ($row['id_transaksi'] ?? 0);
                        $name = (string) ($row['nama_pelanggan'] ?? '');
                        $staff = (string) ($row['nama_karyawan'] ?? '');
                        $jenis = (string) ($row['jenis_transaksi'] ?? '');
                        $status = (string) ($row['status_transaksi'] ?? '');
                        $start = (string) ($row['tanggal_sewa'] ?? '');
                        $end = (string) ($row['tanggal_kembali'] ?? '');
                        $amount = (int) ($row['total_harga'] ?? 0);
                        $class = match ($status) {'Selesai' => 'is-done', 'Berjalan' => 'is-active', 'Booking' => 'is-booking', 'Batal' => 'is-canceled', default => 'is-neutral'};
                    @endphp
                    <tr class="ja-list-row" data-ja-row data-search="{{ $id.' '.$name.' '.$staff.' '.$jenis.' '.$status.' '.$start.' '.$end }}" data-filter="{{ $status }}" data-name="{{ $name }}" data-id="{{ $id }}" data-date="{{ $start }}" data-amount="{{ $amount }}">
                        <td data-label="Transaksi" class="ja-list-primary"><div class="ja-list-name"><strong>{{ $name }}</strong><small>Transaksi #{{ $id }} <span class="ja-list-separator">·</span> {{ $start !== '' ? date('d/m/Y', strtotime($start)) : '—' }}</small></div></td>
                        <td data-label="Jenis"><span class="ja-list-chip">{{ $jenis !== '' ? $jenis : '—' }}</span></td>
                        <td data-label="Jadwal"><span class="ja-list-dates"><strong>{{ $start !== '' ? date('d/m/Y', strtotime($start)) : '—' }}</strong><small>s.d. {{ $end !== '' ? date('d/m/Y', strtotime($end)) : '—' }}</small></span></td>
                        <td data-label="Petugas"><span class="ja-list-regular">{{ $staff }}</span></td>
                        <td data-label="Status"><span class="ja-list-badge {{ $class }}"><i aria-hidden="true"></i>{{ $status !== '' ? $status : '—' }}</span></td>
                        <td data-label="Total"><strong class="ja-list-money">Rp {{ number_format($amount, 0, ',', '.') }}</strong></td>
                        <td data-label="Aksi" class="ja-list-actions-cell"><div class="ja-action-group"><a class="ja-action-edit ja-list-detail" href="{{ url('/admin/transaksi/detail') }}?id={{ $id }}" aria-label="Detail transaksi nomor {{ $id }}">Lihat Detail <span aria-hidden="true">→</span></a></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="ja-inventory-empty" data-ja-empty @if($total > 0) hidden @endif><div class="ja-empty-icon" aria-hidden="true">⌕</div><strong data-ja-empty-title>{{ $total === 0 ? 'Belum ada transaksi' : 'Transaksi tidak ditemukan' }}</strong><p data-ja-empty-desc>{{ $total === 0 ? 'Buat transaksi penyewaan untuk memulai.' : 'Coba kata kunci atau status yang berbeda.' }}</p></div>
        <div class="ja-inventory-footnote">Ringkasan dan tabel mengikuti status pada database. Filter/pengurutan hanya mengubah tampilan, bukan isi transaksi atau pembayaran.</div>
    </div>
</section>
@include('layouts.footer')
