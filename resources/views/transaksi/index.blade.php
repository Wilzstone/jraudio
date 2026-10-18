@include('layouts.header')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Transaksi Penyewaan</h3>
    <a href="/admin/transaksi/tambah" class="btn btn-primary">+ Tambah Transaksi</a>
</div>

<table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Pelanggan</th>
            <th>Karyawan</th>
            <th>Tanggal Sewa</th>
            <th>Tanggal Kembali</th>
            <th>Status</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($transaksi)): ?>
            <tr><td colspan="8" class="text-center">Belum ada transaksi.</td></tr>
        <?php endif; ?>
        <?php foreach ($transaksi as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_sewa']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_kembali']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status_transaksi']) ?></span></td>
                <td>Rp <?= number_format((int) $row['total_harga'], 0, ',', '.') ?></td>
                <td>
                    <a href="/admin/transaksi/detail?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-info">Detail</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

@include('layouts.footer')
