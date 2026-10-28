@include('layouts.header')
@php
    $items = collect($karyawan ?? []);
    $total = $items->count();
    $owners = $items->filter(fn ($row) => ($row['role'] ?? '') === 'Owner')->count();
    $staff = $items->filter(fn ($row) => ($row['role'] ?? '') === 'Karyawan')->count();
    $withPhone = $items->filter(fn ($row) => trim((string) ($row['no_hp'] ?? '')) !== '')->count();
@endphp

<section class="ja-inventory ja-management" data-ja-list data-ja-noun="karyawan" aria-labelledby="jaPageTitle">
    <div class="ja-inventory-heading">
        <div>
            <span class="ja-inventory-eyebrow">TIM INTERNAL / JR AUDIO</span>
            <h1 id="jaPageTitle">Data Karyawan</h1>
            <p>Kelola akun, peran, dan informasi anggota tim dengan tampilan yang mudah dibaca.</p>
        </div>
        <a class="btn ja-inventory-add" href="{{ url('/admin/karyawan/tambah') }}"><span aria-hidden="true">＋</span> Tambah Karyawan</a>
    </div>

    <div class="ja-inventory-stats" aria-label="Ringkasan karyawan">
        <div class="ja-stat-card"><span class="ja-stat-label">Total Akun Tim</span><strong>{{ number_format($total, 0, ',', '.') }}</strong><small>Semua data karyawan</small></div>
        <div class="ja-stat-card ja-stat-available"><span class="ja-stat-label">Akun Owner</span><strong>{{ number_format($owners, 0, ',', '.') }}</strong><small>Memiliki akses Owner</small></div>
        <div class="ja-stat-card ja-stat-rented"><span class="ja-stat-label">Akun Karyawan</span><strong>{{ number_format($staff, 0, ',', '.') }}</strong><small>Memiliki akses Karyawan</small></div>
        <div class="ja-stat-card ja-stat-service"><span class="ja-stat-label">Nomor HP Terisi</span><strong>{{ number_format($withPhone, 0, ',', '.') }}</strong><small>Dari seluruh anggota tim</small></div>
    </div>

    <div class="ja-inventory-panel">
        <div class="ja-inventory-toolbar">
            <div class="ja-toolbar-intro"><h2>Daftar Anggota Tim</h2><p>Cari nama, username, jabatan, atau nomor telepon.</p></div>
            <div class="ja-inventory-controls">
                <label class="ja-control-search">
                    <span class="visually-hidden">Cari karyawan</span>
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.7"/><path d="m16 16 4.7 4.7"/></svg>
                    <input type="search" data-ja-search placeholder="Cari nama / username..." aria-label="Cari karyawan" autocomplete="off">
                </label>
                <label class="ja-control-select"><span class="visually-hidden">Filter role</span>
                    <select data-ja-filter aria-label="Filter role"><option value="">Semua role</option><option value="Owner">Owner</option><option value="Karyawan">Karyawan</option></select>
                </label>
                <label class="ja-control-select"><span class="visually-hidden">Urutkan karyawan</span>
                    <select data-ja-sort aria-label="Urutkan karyawan"><option value="id-desc">Terbaru</option><option value="id-asc">Terlama</option><option value="name-asc">Nama A–Z</option><option value="name-desc">Nama Z–A</option></select>
                </label>
            </div>
        </div>
        <div class="ja-inventory-meta"><p data-ja-counter role="status" aria-live="polite">Menampilkan {{ $total }} dari {{ $total }} karyawan</p><button type="button" class="ja-reset-filter" data-ja-reset hidden>Reset filter</button></div>
        <div class="ja-list-table-wrap">
            <table class="ja-list-table ja-list-staff" aria-label="Daftar karyawan JR Audio">
                <thead><tr><th scope="col">Anggota Tim</th><th scope="col">Akun</th><th scope="col">Role</th><th scope="col">Kontak</th><th scope="col">Jabatan</th><th scope="col">Alamat</th><th scope="col" class="ja-list-right">Aksi</th></tr></thead>
                <tbody data-ja-body>
                    @foreach ($items as $row)
                        @php
                            $id = (int) ($row['id_karyawan'] ?? 0);
                            $name = (string) ($row['nama_karyawan'] ?? '');
                            $username = (string) ($row['username'] ?? '');
                            $role = (string) ($row['role'] ?? '');
                            $phone = trim((string) ($row['no_hp'] ?? ''));
                            $position = trim((string) ($row['jabatan'] ?? ''));
                            $address = trim((string) ($row['alamat'] ?? ''));
                            $initial = mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1));
                        @endphp
                        <tr class="ja-list-row" data-ja-row data-search="{{ $id.' '.$name.' '.$username.' '.$role.' '.$phone.' '.$position.' '.$address }}" data-filter="{{ $role }}" data-name="{{ $name }}" data-id="{{ $id }}">
                            <td data-label="Karyawan" class="ja-list-primary"><div class="ja-list-identity"><span class="ja-list-avatar" aria-hidden="true">{{ $initial }}</span><span class="ja-list-name"><strong>{{ $name }}</strong><small>ID Karyawan #{{ $id }}</small></span></div></td>
                            <td data-label="Akun"><span class="ja-list-username">{{ '@'.$username }}</span></td>
                            <td data-label="Role"><span class="ja-list-badge {{ $role === 'Owner' ? 'is-owner' : 'is-staff' }}">{{ $role !== '' ? $role : '—' }}</span></td>
                            <td data-label="Kontak"><span class="ja-list-regular">{{ $phone !== '' ? $phone : 'Belum diisi' }}</span></td>
                            <td data-label="Jabatan"><span class="ja-list-regular">{{ $position !== '' ? $position : '—' }}</span></td>
                            <td data-label="Alamat"><span class="ja-list-ellipsis" title="{{ $address }}">{{ $address !== '' ? $address : '—' }}</span></td>
                            <td data-label="Aksi" class="ja-list-actions-cell"><div class="ja-action-group">
                                <a class="ja-action-edit" href="{{ url('/admin/karyawan/edit') }}?id={{ $id }}" aria-label="Edit karyawan {{ $name }}">Edit</a>
                                <form action="{{ url('/admin/karyawan/hapus') }}" method="post" data-jr-confirm="Yakin hapus karyawan ini? Akun yang sudah digunakan pada transaksi tidak dapat dihapus.">
                                    @csrf<input type="hidden" name="id" value="{{ $id }}"><button type="submit" class="ja-action-delete" aria-label="Hapus karyawan {{ $name }}">Hapus</button>
                                </form>
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ja-inventory-empty" data-ja-empty @if($total > 0) hidden @endif><div class="ja-empty-icon" aria-hidden="true">⌕</div><strong data-ja-empty-title>{{ $total === 0 ? 'Belum ada data karyawan' : 'Karyawan tidak ditemukan' }}</strong><p data-ja-empty-desc>{{ $total === 0 ? 'Silakan tambah anggota tim untuk memulai.' : 'Coba gunakan kata kunci atau filter yang berbeda.' }}</p></div>
        <div class="ja-inventory-footnote">Role menentukan hak akses aplikasi. Pencarian dan filter di halaman ini tidak mengubah data akun atau password.</div>
    </div>
</section>
@include('layouts.footer')
