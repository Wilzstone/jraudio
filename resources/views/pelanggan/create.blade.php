@include('layouts.header')

<div class="jr-form-heading"><div><span class="jr-form-eyebrow">PELANGGAN / JR AUDIO</span><h3>Tambah Pelanggan</h3><p class="jr-form-help">Kelola informasi pelanggan untuk kebutuhan penyewaan.</p></div></div>
<div class="card jr-form-card">
    <div class="card-body">
        <form action="/admin/pelanggan/simpan" method="post">
    @csrf
            @include('pelanggan.form')
        </form>
    </div>
</div>

@include('layouts.footer')
