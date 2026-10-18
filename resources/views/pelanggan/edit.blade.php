@include('layouts.header')

<h3>Edit Pelanggan</h3>
<div class="card">
    <div class="card-body">
        <form action="/admin/pelanggan/update" method="post">
    @csrf
            <input type="hidden" name="id_pelanggan" value="<?= htmlspecialchars($data['id_pelanggan']) ?>">
            @include('pelanggan.form')
        </form>
    </div>
</div>

@include('layouts.footer')
