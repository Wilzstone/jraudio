<?php include __DIR__ . '/../layouts/header.php'; ?>

<h3>Tambah Barang</h3>
<form action="/admin/barang/simpan" method="post" enctype="multipart/form-data" class="mt-3">
    <?php include __DIR__ . '/form.php'; ?>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="/admin/barang" class="btn btn-secondary">Kembali</a>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
