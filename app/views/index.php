<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php $isOwner = (($_SESSION['role'] ?? '') === 'Owner'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?= $isOwner ? 'Data Barang Sound System' : 'Cek Ketersediaan Barang' ?></h3>
    <?php if ($isOwner): ?>
        <a href="/admin/barang/tambah" class="btn btn-primary btn-sm">+ Tambah Barang</a>
    <?php endif; ?>
</div>

<table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Gambar</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Harga Sewa</th>
            <th>Stok Tersedia</th>
            <th>Sedang Disewa</th>
            <th>Maintenance/Rusak</th>
            <th>Total Fisik</th>
            <th>Status</th>
            <?php if ($isOwner): ?><th>Aksi</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($barang)): ?>
            <tr><td colspan="<?= $isOwner ? 11 : 10 ?>" class="text-center">Belum ada data barang.</td></tr>
        <?php endif; ?>

        <?php $no = 1; foreach ($barang as $row): ?>
        <tr>
            <td><?= $no++; ?></td>
            <td>
                <?php if (!empty($row['gambar'])): ?>
                    <img src="/assets/img/barang/<?= htmlspecialchars($row['gambar']); ?>" width="60" height="60" style="object-fit:cover">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['nama_barang']); ?></td>
            <td><?= htmlspecialchars($row['kategori']); ?></td>
            <td>Rp <?= number_format((int) $row['harga_sewa'], 0, ',', '.'); ?></td>
            <td><span class="badge bg-success"><?= (int) $row['stok']; ?></span></td>
            <td><span class="badge bg-warning text-dark"><?= (int) ($row['stok_disewa'] ?? 0); ?></span></td>
            <td><span class="badge bg-danger"><?= (int) ($row['stok_maintenance'] ?? 0); ?></span></td>
            <td><?= (int) (($row['stok'] ?? 0) + ($row['stok_disewa'] ?? 0) + ($row['stok_maintenance'] ?? 0)); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <?php if ($isOwner): ?>
            <td>
                <a class="btn btn-warning btn-sm" href="/admin/barang/edit?id=<?= $row['id_barang']; ?>">Edit</a>
                <a class="btn btn-danger btn-sm" data-confirm="Hapus barang ini?" href="/admin/barang/hapus?id=<?= $row['id_barang']; ?>">Hapus</a>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
