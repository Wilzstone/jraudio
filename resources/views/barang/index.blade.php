@include('layouts.header')
@php
    $isOwner = (auth()->user()?->role ?? session('role')) === 'Owner';
    $items = collect($barang ?? []);
    $totalJenis = $items->count();
    $totalTersedia = $items->sum(fn ($item) => (int) ($item['stok'] ?? 0));
    $totalDisewa = $items->sum(fn ($item) => (int) ($item['stok_disewa'] ?? 0));
    $totalMaintenance = $items->sum(fn ($item) => (int) ($item['stok_maintenance'] ?? 0));
    $kategoriList = $items->pluck('kategori')->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
@endphp

<section class="ja-inventory" aria-labelledby="inventoryTitle">
    <div class="ja-inventory-heading">
        <div>
            <span class="ja-inventory-eyebrow">INVENTARIS / JR AUDIO</span>
            <h1 id="inventoryTitle">{{ $isOwner ? 'Data Barang' : 'Cek Ketersediaan Barang' }}</h1>
            <p>{{ $isOwner ? 'Kelola inventaris dan pantau kondisi peralatan dalam satu halaman.' : 'Periksa jumlah unit yang tersedia, disewa, dan dalam perawatan.' }}</p>
        </div>
        @if ($isOwner)
            <a class="btn ja-inventory-add" href="{{ url('/admin/barang/tambah') }}" aria-label="Tambah barang baru">
                <span aria-hidden="true">＋</span> Tambah Barang
            </a>
        @endif
    </div>

    <div class="ja-inventory-stats" aria-label="Ringkasan inventaris">
        <div class="ja-stat-card">
            <span class="ja-stat-label">Jenis Barang</span>
            <strong>{{ number_format($totalJenis, 0, ',', '.') }}</strong>
            <small>Item di katalog inventaris</small>
        </div>
        <div class="ja-stat-card ja-stat-available">
            <span class="ja-stat-label">Unit Tersedia</span>
            <strong>{{ number_format($totalTersedia, 0, ',', '.') }}</strong>
            <small>Siap untuk disewa</small>
        </div>
        <div class="ja-stat-card ja-stat-rented">
            <span class="ja-stat-label">Unit Disewa</span>
            <strong>{{ number_format($totalDisewa, 0, ',', '.') }}</strong>
            <small>Sedang berada di pelanggan</small>
        </div>
        <div class="ja-stat-card ja-stat-service">
            <span class="ja-stat-label">Maintenance</span>
            <strong>{{ number_format($totalMaintenance, 0, ',', '.') }}</strong>
            <small>Belum dapat disewakan</small>
        </div>
    </div>

    <div class="ja-inventory-panel">
        <div class="ja-inventory-toolbar">
            <div class="ja-toolbar-intro">
                <h2>Daftar Peralatan</h2>
                <p>Temukan barang, lihat stok, dan kelola informasi dengan lebih cepat.</p>
            </div>
            <div class="ja-inventory-controls">
                <label class="ja-control-search">
                    <span class="visually-hidden">Cari nama barang atau kategori</span>
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.7"/><path d="m16 16 4.7 4.7"/></svg>
                    <input id="jaBarangSearch" type="search" placeholder="Cari nama barang atau kategori..." autocomplete="off" aria-controls="jaBarangBody">
                </label>
                <label class="ja-control-select">
                    <span class="visually-hidden">Filter kategori</span>
                    <select id="jaBarangCategory" aria-label="Filter kategori">
                        <option value="">Semua kategori</option>
                        @foreach ($kategoriList as $kategori)
                            <option value="{{ mb_strtolower(trim((string) $kategori)) }}">{{ $kategori }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="ja-control-select">
                    <span class="visually-hidden">Filter ketersediaan</span>
                    <select id="jaBarangAvailability" aria-label="Filter ketersediaan">
                        <option value="">Semua kondisi</option>
                        <option value="available">Stok tersedia</option>
                        <option value="out">Stok habis</option>
                        <option value="maintenance">Ada maintenance</option>
                    </select>
                </label>
                <label class="ja-control-select ja-sort-select">
                    <span class="visually-hidden">Urutkan barang</span>
                    <select id="jaBarangSort" aria-label="Urutkan barang">
                        <option value="default">Terbaru</option>
                        <option value="name-asc">Nama A–Z</option>
                        <option value="price-asc">Harga termurah</option>
                        <option value="price-desc">Harga termahal</option>
                        <option value="stock-desc">Stok terbanyak</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="ja-inventory-meta">
            <p id="jaBarangCount" role="status" aria-live="polite">Menampilkan {{ $totalJenis }} dari {{ $totalJenis }} barang</p>
            <button id="jaBarangReset" class="ja-reset-filter" type="button" hidden>Reset filter</button>
            <span class="ja-mobile-hint">Ketuk Edit untuk mengubah data barang.</span>
        </div>

        <div class="ja-inventory-table-wrap">
            <table class="ja-inventory-table" aria-label="Daftar inventaris JR Audio">
                <thead>
                    <tr>
                        <th scope="col">Barang</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Harga / Hari</th>
                        <th scope="col">Tersedia</th>
                        <th scope="col">Disewa</th>
                        <th scope="col">Maintenance</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        @if ($isOwner)<th scope="col" class="ja-col-actions">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody id="jaBarangBody">
                    @foreach ($items as $row)
                        @php
                            $id = (int) ($row['id_barang'] ?? 0);
                            $stok = (int) ($row['stok'] ?? 0);
                            $disewa = (int) ($row['stok_disewa'] ?? 0);
                            $maintenance = (int) ($row['stok_maintenance'] ?? 0);
                            $fisik = $stok + $disewa + $maintenance;
                            $status = (string) ($row['status'] ?? '-');
                            $statusClass = $status === 'Tersedia' ? 'is-available' : ($status === 'Maintenance' ? 'is-service' : 'is-rented');
                            $name = (string) ($row['nama_barang'] ?? 'Barang');
                            $category = (string) ($row['kategori'] ?? '-');
                            $image = (string) ($row['gambar'] ?? '');
                        @endphp
                        <tr class="ja-item-row"
                            data-search="{{ mb_strtolower($name.' '.$category.' '.$id) }}"
                            data-category="{{ mb_strtolower(trim($category)) }}"
                            data-stock="{{ $stok }}"
                            data-maintenance="{{ $maintenance }}"
                            data-price="{{ (int) ($row['harga_sewa'] ?? 0) }}"
                            data-name="{{ mb_strtolower($name) }}"
                            data-id="{{ $id }}">
                            <td class="ja-item-product" data-label="Barang">
                                <div class="ja-product-cell">
                                    @if ($image !== '')
                                        <img class="ja-product-thumb" src="{{ asset('assets/img/barang/'.basename($image)) }}" alt="Foto {{ $name }}" loading="lazy" width="60" height="60">
                                    @else
                                        <span class="ja-product-placeholder" aria-hidden="true">
                                            <svg width="29" height="29" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path d="m3 7.5 9 4.5 9-4.5M12 12v9"/></svg>
                                        </span>
                                    @endif
                                    <div class="ja-product-text"><strong>{{ $name }}</strong><small>ID #{{ $id }}</small></div>
                                </div>
                            </td>
                            <td data-label="Kategori"><span class="ja-category-chip">{{ $category }}</span></td>
                            <td data-label="Harga / Hari"><strong class="ja-price">Rp {{ number_format((int) ($row['harga_sewa'] ?? 0), 0, ',', '.') }}</strong></td>
                            <td data-label="Tersedia"><span class="ja-stock-pill is-good">{{ number_format($stok, 0, ',', '.') }}</span></td>
                            <td data-label="Disewa"><span class="ja-stock-pill is-warn">{{ number_format($disewa, 0, ',', '.') }}</span></td>
                            <td data-label="Maintenance"><span class="ja-stock-pill is-danger">{{ number_format($maintenance, 0, ',', '.') }}</span></td>
                            <td data-label="Total"><strong>{{ number_format($fisik, 0, ',', '.') }}</strong></td>
                            <td data-label="Status"><span class="ja-inventory-status {{ $statusClass }}"><i aria-hidden="true"></i>{{ $status }}</span></td>
                            @if ($isOwner)
                                <td class="ja-item-actions" data-label="Aksi">
                                    <div class="ja-action-group">
                                        <a href="{{ url('/admin/barang/edit') }}?id={{ $id }}" class="ja-action-edit" aria-label="Edit {{ $name }}">Edit</a>
                                        <form action="{{ url('/admin/barang/hapus') }}" method="post" data-jr-confirm="Hapus barang ini? Data yang sudah dipakai transaksi tidak dapat dihapus.">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $id }}">
                                            <button class="ja-action-delete" type="submit" aria-label="Hapus {{ $name }}">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ja-inventory-empty" id="jaBarangEmpty" @if ($totalJenis > 0) hidden @endif>
            <div class="ja-empty-icon" aria-hidden="true">⌕</div>
            <strong>{{ $totalJenis === 0 ? 'Belum ada barang' : 'Barang tidak ditemukan' }}</strong>
            <p>{{ $totalJenis === 0 ? 'Belum ada barang yang tercatat di inventaris.' : 'Coba kata kunci atau filter yang berbeda.' }}</p>
        </div>
        <div class="ja-inventory-footnote">Jumlah unit dan label status mengikuti data di database. Untuk cek kesiapan sewa, lihat angka <strong>Tersedia</strong>; pencarian/filter tidak mengubah data.</div>
    </div>
</section>
<script src="{{ asset('assets/js/admin-inventory.js') }}?v=3.0" defer></script>
@include('layouts.footer')
