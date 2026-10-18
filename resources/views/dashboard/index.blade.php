@include('layouts.header')

<h3>Dashboard JR Audio</h3>
<p class="text-muted">Selamat datang, <?= htmlspecialchars(session()->get('nama') ?? session()->get('username')); ?>.</p>

<?php if (!empty(session()->get('info'))): ?>
    <div class="alert alert-warning">
        <?= htmlspecialchars(session()->get('info')) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Barang</h5>
                <p>Kelola alat sound system yang disewakan.</p>
                <a href="/admin/barang" class="btn btn-dark btn-sm">Buka Barang</a>
            </div>
        </div>
    </div>

    <?php if ((session()->get('role') ?? '') === 'Owner'): ?>
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Karyawan</h5>
                <p>Kelola akun dan data karyawan JR Audio.</p>
                <a href="/admin/karyawan" class="btn btn-dark btn-sm">Buka Karyawan</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

@include('layouts.footer')
