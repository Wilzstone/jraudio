@include('layouts.header')

<div class="jr-form-heading"><div><span class="jr-form-eyebrow">MANAJEMEN JR AUDIO</span><h3>Edit Karyawan</h3><p class="jr-form-help">Perbarui informasi kemudian simpan perubahan.</p></div><a class="btn btn-outline-secondary" href="/admin/karyawan">Kembali</a></div>
<form action="/admin/karyawan/update" method="post" class="mt-3 card jr-form-card p-4">
    @csrf
    <input type="hidden" name="id_karyawan" value="<?= $data['id_karyawan']; ?>">
    @include('karyawan.form')
    <div class="jr-form-actions">
    <button type="submit" class="btn btn-success">Update</button>
    <a href="/admin/karyawan" class="btn btn-secondary">Kembali</a>
    </div>
</form>

@include('layouts.footer')
