@include('layouts.header')

<h3>Edit Barang</h3>
<form action="/admin/barang/update" method="post" enctype="multipart/form-data" class="mt-3">
    @csrf
    <input type="hidden" name="id_barang" value="<?= $data['id_barang']; ?>">
    @include('barang.form')
    <button type="submit" class="btn btn-success">Update</button>
    <a href="/admin/barang" class="btn btn-secondary">Kembali</a>
</form>

@include('layouts.footer')
