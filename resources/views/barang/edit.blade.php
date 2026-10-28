@include('layouts.header')

<div class="jr-form-heading"><div><span class="jr-form-eyebrow">MANAJEMEN JR AUDIO</span><h3>Edit Barang</h3><p class="jr-form-help">Perbarui informasi kemudian simpan perubahan.</p></div></div>
<form action="/admin/barang/update" method="post" enctype="multipart/form-data" class="mt-3 card jr-form-card p-4">
    @csrf
    <input type="hidden" name="id_barang" value="<?= $data['id_barang']; ?>">
    @include('barang.form')
    <div class="jr-form-actions">
    <button type="submit" class="btn btn-success">Update</button>
    <a href="/admin/barang" class="btn btn-secondary">Kembali</a>
    </div>
</form>

@include('layouts.footer')
