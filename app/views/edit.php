<?php include __DIR__ . '/../layouts/header.php'; ?>

<h3>Edit Barang</h3>
<form action="/admin/barang/update" method="post" enctype="multipart/form-data" class="mt-3">
    <input type="hidden" name="id_barang" value="<?= $data['id_barang']; ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button type="submit" class="btn btn-success">Update</button>
    <a href="/admin/barang" class="btn btn-secondary">Kembali</a>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
