@php
    $topRole = auth()->user()?->role ?? session('role', 'Karyawan');
    $topName = session('nama') ?: session('username') ?: auth()->user()?->username ?: $topRole;
    // Judul konsisten untuk kedua URL ketersediaan barang.
    $topPath = request()->path();
    $topPage = $pageTitle ?? match (true) {
        str_contains($topPath, 'cek-ketersediaan'), str_contains($topPath, 'barang') => 'Barang',
        str_contains($topPath, 'pengembalian') => 'Pengembalian',
        str_contains($topPath, 'transaksi') => 'Transaksi',
        str_contains($topPath, 'pelanggan') => 'Pelanggan',
        str_contains($topPath, 'karyawan') => 'Karyawan',
        str_contains($topPath, 'pembayaran') => 'Pembayaran',
        str_contains($topPath, 'laporan') => 'Laporan',
        default => 'Dashboard',
    };
@endphp
<header class="ja-topbar">
    <div class="ja-topbar-left">
        <button class="ja-mobile-menu" type="button" data-menu-open aria-label="Buka menu navigasi" aria-controls="jaSidebar" aria-expanded="false">☰</button>
        <span class="ja-breadcrumb">JR AUDIO <span>/</span> {{ strtoupper($topRole) }} <span>/</span> <strong>{{ strtoupper($topPage) }}</strong></span>
    </div>
    <div class="ja-topbar-right">
        <details class="ja-user-menu" id="jaUserMenu">
            <summary class="ja-user-trigger" aria-label="Menu akun {{ $topName }}">
                <span class="ja-topbar-avatar">{{ mb_strtoupper(mb_substr($topName, 0, 1)) }}</span>
                <span class="ja-user-details"><strong>{{ $topName }}</strong><small>{{ $topRole }}</small></span>
                <span class="ja-user-chevron" aria-hidden="true">⌄</span>
            </summary>
            <div class="ja-user-dropdown">
                <div class="ja-user-dropdown-heading"><strong>{{ $topName }}</strong><small>{{ $topRole }} · JR Audio</small></div>
                <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer">Lihat website publik <span aria-hidden="true">↗</span></a>
                <form action="{{ url('/logout') }}" method="post">@csrf
                    <button type="submit" class="ja-signout">Logout <span aria-hidden="true">→</span></button>
                </form>
            </div>
        </details>
    </div>
</header>
