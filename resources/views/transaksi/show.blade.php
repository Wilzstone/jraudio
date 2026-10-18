@include('layouts.header')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Detail Transaksi #<?= htmlspecialchars($data['id_transaksi']) ?></h3>
    <div>
        <a href="/admin/invoice?id=<?= $data['id_transaksi'] ?>" class="btn btn-success btn-sm">Cetak Invoice</a>
        <a href="/admin/transaksi" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

<?php if ($isSelesai): ?>
    <div class="alert alert-success">
        Transaksi ini sudah selesai. Data status, pembayaran, dan pengembalian tidak bisa diubah lagi.
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header">Informasi Transaksi</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                <table class="table table-borderless">
                    <tr><td width="180">Pelanggan</td><td>: <?= htmlspecialchars($data['nama_pelanggan']) ?></td></tr>
                    <tr><td>No HP</td><td>: <?= htmlspecialchars($data['no_hp']) ?></td></tr>
                    <tr><td>Alamat</td><td>: <?= htmlspecialchars($data['alamat']) ?></td></tr>
                    <tr><td>Karyawan</td><td>: <?= htmlspecialchars($data['nama_karyawan']) ?></td></tr>
                    <tr><td>Jenis</td><td>: <?= htmlspecialchars($data['jenis_transaksi'] ?? 'Satuan') ?></td></tr>
                    <tr><td>Paket</td><td>: <?= htmlspecialchars($data['nama_paket'] ?? '-') ?></td></tr>
                    <tr><td>Tanggal Transaksi</td><td>: <?= htmlspecialchars($data['tanggal_transaksi']) ?></td></tr>
                    <tr><td>Tanggal Sewa</td><td>: <?= htmlspecialchars($data['tanggal_sewa']) ?></td></tr>
                    <tr><td>Tanggal Kembali</td><td>: <?= htmlspecialchars($data['tanggal_kembali']) ?></td></tr>
                </table>
            </div>
            <div class="col-md-5">
                <form action="/admin/transaksi/status" method="post" class="border rounded p-3">
    @csrf
                    <input type="hidden" name="id_transaksi" value="<?= $data['id_transaksi'] ?>">
                    <label class="form-label">Status Transaksi</label>
                    <select name="status_transaksi" class="form-select mb-2" <?= $isSelesai ? 'disabled' : '' ?>>
                        <?php foreach (['Booking','Berjalan','Selesai','Batal'] as $status): ?>
                            <option value="<?= $status ?>" <?= $data['status_transaksi'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" <?= $isSelesai ? 'disabled' : '' ?>>Simpan Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Barang yang Disewa</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Barang</th>
                    <th>Kategori</th>
                    <th>Sumber</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detail as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td><?= htmlspecialchars($row['kategori']) ?></td>
                        <td><?= htmlspecialchars($row['sumber_external'] ?? '-') ?></td>
                        <td><?= (int) $row['qty'] ?></td>
                        <td><?= ((int)$row['harga_satuan'] > 0) ? 'Rp ' . number_format((int) $row['harga_satuan'], 0, ',', '.') : '-' ?></td>
                        <td><?= ((int)$row['subtotal'] > 0) ? 'Rp ' . number_format((int) $row['subtotal'], 0, ',', '.') : '-' ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (($data['jenis_transaksi'] ?? 'Satuan') === 'Paketan'): ?>
                <tr>
                    <th colspan="6" class="text-end">Harga Paket Manual</th>
                    <th>Rp <?= number_format((int) $data['harga_paket_manual'], 0, ',', '.') ?></th>
                </tr>
                <?php endif; ?>

                <tr>
                    <th colspan="6" class="text-end">Biaya Genset</th>
                    <th>Rp <?= number_format((int) ($data['biaya_genset'] ?? 0), 0, ',', '.') ?></th>
                </tr>
                <tr>
                    <th colspan="6" class="text-end">Total Sewa</th>
                    <th>Rp <?= number_format((int) $data['total_harga'], 0, ',', '.') ?></th>
                </tr>
                <tr>
                    <th colspan="6" class="text-end">Denda</th>
                    <th>Rp <?= number_format((int) $totalDenda, 0, ',', '.') ?></th>
                </tr>
                <tr>
                    <th colspan="6" class="text-end">Total Tagihan</th>
                    <th>Rp <?= number_format((int) $totalTagihan, 0, ',', '.') ?></th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Pembayaran</span>
        <?php if (!$isSelesai || $sisaBayar > 0): ?>
            <a href="/admin/pembayaran/tambah?id_transaksi=<?= $data['id_transaksi'] ?>" class="btn btn-sm btn-primary">Tambah Pembayaran</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Total Sewa</div><strong>Rp <?= number_format((int) $data['total_harga'], 0, ',', '.') ?></strong></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Denda</div><strong>Rp <?= number_format((int) $totalDenda, 0, ',', '.') ?></strong></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Total Bayar</div><strong>Rp <?= number_format((int) $totalBayar, 0, ',', '.') ?></strong></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Sisa Bayar</div><strong>Rp <?= number_format((int) $sisaBayar, 0, ',', '.') ?></strong></div></div>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pembayaran)): ?>
                    <tr><td colspan="6" class="text-center">Belum ada pembayaran.</td></tr>
                <?php endif; ?>
                <?php foreach ($pembayaran as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['tanggal_bayar']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_pembayaran']) ?></td>
                        <td>Rp <?= number_format((int) $row['jumlah_bayar'], 0, ',', '.') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status_konfirmasi']) ?></span></td>
                        <td>
                            <?php if (!$isSelesai): ?>
                                <form action="/admin/pembayaran/status" method="post" class="d-inline-flex gap-1">
    @csrf
                                    <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran'] ?>">
                                    <input type="hidden" name="id_transaksi" value="<?= $data['id_transaksi'] ?>">
                                    <select name="status_konfirmasi" class="form-select form-select-sm">
                                        <?php foreach (['Menunggu','Dikonfirmasi','Ditolak'] as $status): ?>
                                            <option value="<?= $status ?>" <?= $row['status_konfirmasi'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-success">Update</button>
                                </form>
                                <form action="/admin/pembayaran/hapus" method="post" class="d-inline" onsubmit="return confirm('Yakin hapus pembayaran ini?')">@csrf<input type="hidden" name="id" value="<?= $row['id_pembayaran'] ?>"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
                            <?php else: ?>
                                <span class="text-muted">Terkunci</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

@include('layouts.footer')
