@include('layouts.header')

<div class="jr-form-heading"><div><span class="jr-form-eyebrow">PELANGGAN / JR AUDIO</span><h3>Edit Pelanggan</h3><p class="jr-form-help">Kelola informasi pelanggan untuk kebutuhan penyewaan.</p></div><a href="/admin/pelanggan" class="btn btn-outline-secondary">Kembali</a></div>
<div class="card jr-form-card">
    <div class="card-body">
        <form action="/admin/pelanggan/update" method="post">
    @csrf
            <input type="hidden" name="id_pelanggan" value="<?= htmlspecialchars($data['id_pelanggan']) ?>">
            @include('pelanggan.form')
        </form>
    </div>
</div>

@include('layouts.footer')
