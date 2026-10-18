@include('layouts.header')

<h3>Tambah Pelanggan</h3>
<div class="card">
    <div class="card-body">
        <form action="/admin/pelanggan/simpan" method="post">
    @csrf
            @include('pelanggan.form')
        </form>
    </div>
</div>

@include('layouts.footer')
