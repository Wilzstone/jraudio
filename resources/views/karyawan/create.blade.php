@include('layouts.header')

<h3>Tambah Karyawan</h3>
<form action="/admin/karyawan/simpan" method="post" class="mt-3">
    @csrf
    @include('karyawan.form')
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="/admin/karyawan" class="btn btn-secondary">Kembali</a>
</form>

@include('layouts.footer')
