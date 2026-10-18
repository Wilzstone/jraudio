@include('layouts.header')

<h3>Edit Karyawan</h3>
<form action="/admin/karyawan/update" method="post" class="mt-3">
    @csrf
    <input type="hidden" name="id_karyawan" value="<?= $data['id_karyawan']; ?>">
    @include('karyawan.form')
    <button type="submit" class="btn btn-success">Update</button>
    <a href="/admin/karyawan" class="btn btn-secondary">Kembali</a>
</form>

@include('layouts.footer')
