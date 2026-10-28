@include('layouts.header')

<div class="jr-form-heading"><div><span class="jr-form-eyebrow">MANAJEMEN JR AUDIO</span><h3>Tambah Karyawan</h3><p class="jr-form-help">Lengkapi data baru lalu simpan.</p></div></div>
<form action="/admin/karyawan/simpan" method="post" class="mt-3 card jr-form-card p-4">
    @csrf
    @include('karyawan.form')
    <div class="jr-form-actions">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="/admin/karyawan" class="btn btn-secondary">Kembali</a>
    </div>
</form>

@include('layouts.footer')
