@include('layouts.header')

<h3>Tambah Barang</h3>
<form action="/admin/barang/simpan" method="post" enctype="multipart/form-data" class="mt-3">
    @csrf
    @include('barang.form')
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="/admin/barang" class="btn btn-secondary">Kembali</a>
</form>

@include('layouts.footer')
