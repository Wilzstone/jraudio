@php
    $adminRole = auth()->user()?->role ?? session('role', 'Karyawan');
    $adminOwner = $adminRole === 'Owner';
    $adminNav = $adminOwner
        ? [
            ['Dashboard', '/dashboard', 'dashboard'],
            ['Barang', '/admin/barang', 'barang'],
            ['Pelanggan', '/admin/pelanggan', 'pelanggan'],
            ['Karyawan', '/admin/karyawan', 'karyawan'],
            ['Transaksi', '/admin/transaksi', 'transaksi'],
            ['Pembayaran', '/admin/pembayaran', 'pembayaran'],
            ['Pengembalian', '/admin/pengembalian', 'pengembalian'],
            ['Laporan', '/admin/laporan', 'laporan'],
        ]
        : [
            ['Cek Ketersediaan', '/cek-ketersediaan', 'barang'],
            ['Transaksi', '/transaksi', 'transaksi'],
            ['Buat Transaksi', '/transaksi/tambah', 'plus'],
            ['Pengembalian', '/pengembalian', 'pengembalian'],
        ];
    $adminCurrent = '/'.trim(request()->path(), '/');
    $adminCurrent = $adminCurrent === '//' ? '/' : $adminCurrent;
@endphp
<div class="ja-sidebar-scrim" data-menu-close aria-hidden="true"></div>
<aside class="ja-sidebar" id="jaSidebar" aria-label="Navigasi {{ $adminRole }}">
    <a class="ja-logo" href="{{ url($adminOwner ? '/dashboard' : '/transaksi') }}" aria-label="JR Audio - Halaman utama admin">
        <img src="{{ asset('assets/img/jr-favicon.svg') }}" alt="JR Audio" class="ja-brand-mark">
    </a>
    <nav class="ja-navigation" aria-label="Menu utama">
        @foreach ($adminNav as $adminItem)
            @php
                $adminPath = $adminItem[1];
                // Route yang lebih spesifik dinilai terlebih dahulu supaya menu Tambah tidak mengikuti menu Transaksi.
                $adminActive = $adminPath === '/transaksi'
                    ? ($adminCurrent === '/transaksi')
                    : ($adminCurrent === $adminPath || str_starts_with($adminCurrent, $adminPath.'/'));
                if ($adminOwner && $adminPath === '/admin/transaksi') {
                    $adminActive = str_starts_with($adminCurrent, '/admin/transaksi');
                }
            @endphp
            <a href="{{ url($adminPath) }}" class="ja-nav-link {{ $adminActive ? 'is-active' : '' }}" @if($adminActive) aria-current="page" @endif>
                <span class="ja-nav-symbol" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        @switch($adminItem[2])
                            @case('dashboard') <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/> @break
                            @case('barang') <path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/> @break
                            @case('pelanggan') <circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/> @break
                            @case('karyawan') <circle cx="12" cy="7.5" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2H5Z"/> @break
                            @case('transaksi') <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/> @break
                            @case('pembayaran') <rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18M7 16h4"/> @break
                            @case('pengembalian') <path d="M7 7H4V4M4 7a8 8 0 1 1-1 9M4 7l4-4"/><path d="M12 8v4l3 2"/> @break
                            @case('plus') <circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/> @break
                            @default <path d="M4 20V4h16v16M7 9h10M7 13h10M7 17h7"/>
                        @endswitch
                    </svg>
                </span>
                <span>{{ $adminItem[0] }}</span>
                @if($adminActive)<span class="ja-active-dot" aria-hidden="true"></span>@endif
            </a>
        @endforeach
    </nav>
</aside>
