@include('layouts.header')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Pembayaran</h3>
    <a href="/admin/pembayaran/tambah" class="btn btn-primary">Tambah Pembayaran</a>
</div>

<div class="card jr-form-card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>ID Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Tanggal Bayar</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pembayaran)): ?>
                    <tr><td colspan="8" class="text-center">Belum ada data pembayaran.</td></tr>
                <?php endif; ?>
                <?php foreach ($pembayaran as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>#<?= htmlspecialchars($row['id_transaksi']) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal_bayar']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_pembayaran']) ?></td>
                        <td>Rp <?= number_format((int) $row['jumlah_bayar'], 0, ',', '.') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status_konfirmasi']) ?></span></td>
                        <td>
                            <a href="/admin/transaksi/detail?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-info">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

@include('layouts.footer')
