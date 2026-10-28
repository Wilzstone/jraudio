@include('layouts.header')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Kelola Pengembalian</h3>
    <a href="/admin/transaksi" class="btn btn-secondary btn-sm">Lihat Transaksi</a>
</div>

<div class="alert alert-warning">
    Denda keterlambatan dihitung otomatis: <strong>Rp 50.000 per hari</strong> setelah tanggal kembali.
    Denda akan masuk ke <strong>sisa bayar</strong> transaksi.
</div>

<div class="card mb-4">
    <div class="card-header bg-dark text-white">Input Pengembalian Barang</div>
    <div class="card-body">
        <?php if (empty($transaksiBerjalan)): ?>
            <div class="alert alert-warning mb-0">Belum ada transaksi berstatus Berjalan yang perlu dikembalikan.</div>
        <?php else: ?>
            <?php foreach ($transaksiBerjalan as $trx): ?>
                <form action="/admin/pengembalian/simpan" method="post" class="border rounded p-3 mb-4">
    @csrf
                    <input type="hidden" name="id_transaksi" value="<?= $trx['id_transaksi'] ?>">

                    <div class="row mb-3">
                        <div class="col-md-5">
                            <h5 class="mb-1">#<?= $trx['id_transaksi'] ?> - <?= htmlspecialchars($trx['nama_pelanggan']) ?></h5>
                            <div class="text-muted">
                                Sewa: <?= htmlspecialchars($trx['tanggal_sewa']) ?> |
                                Wajib Kembali: <?= htmlspecialchars($trx['tanggal_kembali']) ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Pengembalian</label>
                            <input type="date" name="tanggal_pengembalian" class="form-control" value="<?= htmlspecialchars($trx['tanggal_kembali'] ?? date('Y-m-d')) ?>" required>
                        <small class="text-muted">Otomatis mengikuti tanggal wajib kembali transaksi, tetapi tetap bisa diganti.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kondisi Umum Barang</label>
                            <input type="text" name="kondisi_barang" class="form-control" placeholder="Contoh: sebagian baik, 1 mic rusak" required>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Barang</th>
                                    <th>Qty Disewa</th>
                                    <th>Qty Rusak / Maintenance</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($detailTransaksi[$trx['id_transaksi']] ?? []) as $detail): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($detail['nama_barang']) ?></td>
                                        <td><?= (int) $detail['qty'] ?></td>
                                        <td style="max-width:160px">
                                            <input type="number"
                                                   name="rusak_qty[<?= $detail['id_detail'] ?>]"
                                                   class="form-control"
                                                   min="0"
                                                   max="<?= (int) $detail['qty'] ?>"
                                                   value="0">
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                Qty rusak tidak kembali ke stok tersedia, tapi masuk stok maintenance.
                                                Sisanya dianggap kembali baik.
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Denda Manual Kerusakan / Tambahan</label>
                        <input type="number" name="denda_manual" class="form-control" min="0" value="0" placeholder="Contoh: 100000">
                        <small class="text-muted">Isi jika ada denda kerusakan atau biaya tambahan selain denda telat.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan jika ada."></textarea>
                    </div>

                    <button class="btn btn-primary" data-confirm="Proses pengembalian transaksi ini?">Proses Pengembalian</button>
                </form>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card jr-form-card">
    <div class="card-header bg-dark text-white">Riwayat Pengembalian</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Tanggal Pengembalian</th>
                    <th>Detail Barang</th>
                    <th>Denda</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="7" class="text-center">Belum ada data pengembalian.</td></tr>
                <?php endif; ?>
                <?php foreach ($riwayat as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal_pengembalian']) ?></td>
                        <td>
                            <?php foreach (($detailRiwayat[$row['id_pengembalian']] ?? []) as $d): ?>
                                <div>
                                    <?= htmlspecialchars($d['nama_barang']) ?>:
                                    baik <?= (int) $d['qty_kembali_baik'] ?>,
                                    rusak <?= (int) $d['qty_rusak'] ?>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td>Rp <?= number_format((int) $row['denda'], 0, ',', '.') ?></td>
                        <td><span class="badge bg-success"><?= htmlspecialchars($row['status_transaksi']) ?></span></td>
                        <td>
                            <?= nl2br(htmlspecialchars($row['kondisi_barang'] ?? '-')) ?><br>
                            <small><?= nl2br(htmlspecialchars($row['catatan'] ?? '-')) ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

@include('layouts.footer')
