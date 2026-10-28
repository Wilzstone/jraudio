@include('layouts.header')

<div class="jr-form-heading"><div><span class="jr-form-eyebrow">MANAJEMEN JR AUDIO</span><h3>Tambah Barang</h3><p class="jr-form-help">Lengkapi data baru lalu simpan.</p></div><a class="btn btn-outline-secondary" href="/admin/barang">Kembali</a></div>
<form action="/admin/barang/simpan" method="post" enctype="multipart/form-data" class="mt-3 card jr-form-card p-4">
    @csrf
    @include('barang.form')
    <div class="jr-form-actions">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="/admin/barang" class="btn btn-secondary">Kembali</a>
    </div>
</form>

@include('layouts.footer')
