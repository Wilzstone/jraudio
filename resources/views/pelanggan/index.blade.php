@include('layouts.header')
@php
    $items = collect($pelanggan ?? []);
    $total = $items->count();
    $withPhone = $items->filter(fn ($row) => trim((string) ($row['no_hp'] ?? '')) !== '')->count();
    $withAddress = $items->filter(fn ($row) => trim((string) ($row['alamat'] ?? '')) !== '')->count();
@endphp

<section class="ja-inventory ja-management" data-ja-list data-ja-noun="pelanggan" aria-labelledby="jaPageTitle">
    <div class="ja-inventory-heading">
        <div><span class="ja-inventory-eyebrow">RELASI PELANGGAN / JR AUDIO</span><h1 id="jaPageTitle">Data Pelanggan</h1><p>Daftar pelanggan dengan informasi kontak yang lebih mudah dicari dan dikelola.</p></div>
        <a class="btn ja-inventory-add" href="{{ url('/admin/pelanggan/tambah') }}"><span aria-hidden="true">＋</span> Tambah Pelanggan</a>
    </div>
    <div class="ja-inventory-stats ja-stats-three" aria-label="Ringkasan pelanggan">
        <div class="ja-stat-card"><span class="ja-stat-label">Total Pelanggan</span><strong>{{ number_format($total, 0, ',', '.') }}</strong><small>Semua pelanggan tercatat</small></div>
        <div class="ja-stat-card ja-stat-available"><span class="ja-stat-label">Memiliki Nomor HP</span><strong>{{ number_format($withPhone, 0, ',', '.') }}</strong><small>Kontak yang sudah diisi</small></div>
        <div class="ja-stat-card ja-stat-rented"><span class="ja-stat-label">Memiliki Alamat</span><strong>{{ number_format($withAddress, 0, ',', '.') }}</strong><small>Alamat yang sudah diisi</small></div>
    </div>
    <div class="ja-inventory-panel">
        <div class="ja-inventory-toolbar"><div class="ja-toolbar-intro"><h2>Direktori Pelanggan</h2><p>Cari pelanggan menggunakan nama, nomor telepon, atau alamat.</p></div>
            <div class="ja-inventory-controls">
                <label class="ja-control-search"><span class="visually-hidden">Cari pelanggan</span><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.7"/><path d="m16 16 4.7 4.7"/></svg><input type="search" data-ja-search placeholder="Cari pelanggan / kontak..." aria-label="Cari pelanggan" autocomplete="off"></label>
                <label class="ja-control-select"><span class="visually-hidden">Filter kelengkapan kontak</span><select data-ja-filter aria-label="Filter kelengkapan kontak"><option value="">Semua pelanggan</option><option value="with-phone">Ada nomor HP</option><option value="without-phone">Tanpa nomor HP</option></select></label>
                <label class="ja-control-select"><span class="visually-hidden">Urutkan pelanggan</span><select data-ja-sort aria-label="Urutkan pelanggan"><option value="id-desc">Terbaru</option><option value="id-asc">Terlama</option><option value="name-asc">Nama A–Z</option><option value="name-desc">Nama Z–A</option></select></label>
            </div>
        </div>
        <div class="ja-inventory-meta"><p data-ja-counter role="status" aria-live="polite">Menampilkan {{ $total }} dari {{ $total }} pelanggan</p><button type="button" class="ja-reset-filter" data-ja-reset hidden>Reset filter</button></div>
        <div class="ja-list-table-wrap"><table class="ja-list-table ja-list-customers" aria-label="Daftar pelanggan JR Audio">
            <thead><tr><th scope="col">Pelanggan</th><th scope="col">No. Telepon</th><th scope="col">Alamat</th><th scope="col" class="ja-list-right">Aksi</th></tr></thead>
            <tbody data-ja-body>
                @foreach ($items as $row)
                    @php
                        $id = (int) ($row['id_pelanggan'] ?? 0);
                        $name = (string) ($row['nama_pelanggan'] ?? '');
                        $phone = trim((string) ($row['no_hp'] ?? ''));
                        $address = trim((string) ($row['alamat'] ?? ''));
                        $initial = mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1));
                    @endphp
                    <tr class="ja-list-row" data-ja-row data-search="{{ $id.' '.$name.' '.$phone.' '.$address }}" data-filter="{{ $phone !== '' ? 'with-phone' : 'without-phone' }}" data-name="{{ $name }}" data-id="{{ $id }}">
                        <td data-label="Pelanggan" class="ja-list-primary"><div class="ja-list-identity"><span class="ja-list-avatar is-customer" aria-hidden="true">{{ $initial }}</span><span class="ja-list-name"><strong>{{ $name }}</strong><small>ID Pelanggan #{{ $id }}</small></span></div></td>
                        <td data-label="No. Telepon"><span class="ja-list-regular">{{ $phone !== '' ? $phone : 'Belum diisi' }}</span></td>
                        <td data-label="Alamat"><span class="ja-list-address">{{ $address !== '' ? $address : 'Belum diisi' }}</span></td>
                        <td data-label="Aksi" class="ja-list-actions-cell"><div class="ja-action-group">
                            <a class="ja-action-edit" href="{{ url('/admin/pelanggan/edit') }}?id={{ $id }}" aria-label="Edit pelanggan {{ $name }}">Edit</a>
                            <form action="{{ url('/admin/pelanggan/hapus') }}" method="post" onsubmit="return confirm('Yakin hapus pelanggan ini? Pelanggan yang sudah digunakan dalam transaksi tidak dapat dihapus.')">
                                @csrf<input type="hidden" name="id" value="{{ $id }}"><button type="submit" class="ja-action-delete" aria-label="Hapus pelanggan {{ $name }}">Hapus</button>
                            </form>
                        </div></td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="ja-inventory-empty" data-ja-empty @if($total > 0) hidden @endif><div class="ja-empty-icon" aria-hidden="true">⌕</div><strong data-ja-empty-title>{{ $total === 0 ? 'Belum ada pelanggan' : 'Pelanggan tidak ditemukan' }}</strong><p data-ja-empty-desc>{{ $total === 0 ? 'Tambahkan pelanggan pertama untuk memulai.' : 'Coba gunakan kata kunci atau filter lain.' }}</p></div>
        <div class="ja-inventory-footnote">Data pelanggan ditampilkan dari database yang sudah ada. Filter dan pencarian tidak menyimpan perubahan.</div>
    </div>
</section>
@include('layouts.footer')
