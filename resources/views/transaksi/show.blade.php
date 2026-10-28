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
        Barang telah dikembalikan dan transaksi selesai. Jika masih ada denda, pembayaran denda tetap dapat dilakukan hingga lunas.
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
                    <tr><td>Lokasi acara</td><td>: <?= nl2br(htmlspecialchars($data['alamat_lokasi_acara'] ?? '-')) ?></td></tr>
                    <?php if (!empty($data['link_google_maps']) && preg_match('~^https://~i', $data['link_google_maps'])): ?>
                    <tr><td>Google Maps</td><td>: <a href="<?= htmlspecialchars($data['link_google_maps'],ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer">Lihat lokasi acara ↗</a></td></tr>
                    <?php endif; ?>
                    <tr><td>Karyawan</td><td>: <?= htmlspecialchars($data['nama_karyawan']) ?></td></tr>
                    <tr><td>Jenis</td><td>: <?= htmlspecialchars($data['jenis_transaksi'] ?? 'Satuan') ?></td></tr>
                    <tr><td>Paket</td><td>: <?= htmlspecialchars($data['nama_paket'] ?? '-') ?></td></tr>
                    <tr><td>Tanggal Transaksi</td><td>: <?= htmlspecialchars($data['tanggal_transaksi']) ?></td></tr>
                    <tr><td>Tanggal Sewa</td><td>: <?= htmlspecialchars($data['tanggal_sewa']) ?></td></tr>
                    <tr><td>Tanggal Kembali</td><td>: <?= htmlspecialchars($data['tanggal_kembali']) ?></td></tr>
                </table>
            </div>
            <div class="col-md-5">
                <div class="border rounded p-3">
                    <label class="form-label fw-semibold">Status Transaksi</label>
                    <p class="mb-2"><span class="badge <?= match($data['status_transaksi']){'Booking'=>'bg-warning text-dark','Berjalan'=>'bg-primary','Selesai'=>'bg-success',default=>'bg-secondary'} ?>"><?= htmlspecialchars($data['status_transaksi']) ?></span></p>
                    <?php if ($data['status_transaksi']==='Booking'): ?>
                    <form action="/admin/transaksi/status" method="post">
                        @csrf
                        <input type="hidden" name="id_transaksi" value="<?= $data['id_transaksi'] ?>">
                        <select name="status_transaksi" class="form-select mb-3" required>
                            <option value="">-- Pilih perubahan --</option>
                            <option value="Berjalan">Berjalan (penyewaan dimulai)</option>
                            <option value="Batal">Batal (stok dikembalikan)</option>
                        </select>
                        <button type="submit" class="btn btn-primary" data-jr-confirm="Pastikan perubahan status sudah sesuai.">Simpan Status</button>
                    </form>
                    <?php elseif ($data['status_transaksi']==='Berjalan'): ?>
                        <?php if($ringkasan['sisa_sewa']>0): ?>
                        <p class="text-muted mb-2">Lunasi sewa Rp <?= number_format($ringkasan['sisa_sewa'],0,',','.') ?> terlebih dahulu sebelum pengembalian.</p>
                        <a class="btn btn-outline-primary" href="/admin/pembayaran/tambah?id_transaksi=<?= $data['id_transaksi'] ?>">Pelunasan Sewa</a>
                        <?php else: ?>
                        <p class="text-muted mb-2">Sewa lunas. Lanjutkan pengembalian agar barang kembali ke stok dan status otomatis Selesai.</p>
                        <a class="btn btn-primary" href="/admin/pengembalian">Proses Pengembalian</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">Status akhir tidak dapat diubah dari halaman ini.</p>
                    <?php endif; ?>
                </div>
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
        <?php if ($data['status_transaksi'] !== 'Batal' && $sisaBayar > 0): ?>
            <a href="/admin/pembayaran/tambah?id_transaksi=<?= $data['id_transaksi'] ?>" class="btn btn-sm btn-primary">Tambah Pembayaran</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($isSelesai && $ringkasan['sisa_denda'] > 0): ?>
            <div class="alert alert-warning">Barang sudah dikembalikan, tetapi masih ada denda <strong>Rp <?= number_format($ringkasan['sisa_denda'], 0, ',', '.') ?></strong> yang perlu dibayar.</div>
        <?php endif; ?>
        <div class="row mb-3">
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Total Sewa</div><strong>Rp <?= number_format((int) $data['total_harga'], 0, ',', '.') ?></strong></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Denda</div><strong>Rp <?= number_format((int) $totalDenda, 0, ',', '.') ?></strong></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Sisa Sewa</div><strong>Rp <?= number_format((int) $ringkasan['sisa_sewa'], 0, ',', '.') ?></strong></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><div class="text-muted">Sisa Denda</div><strong>Rp <?= number_format((int) $ringkasan['sisa_denda'], 0, ',', '.') ?></strong></div></div>
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
                            <?php if ($row['status_konfirmasi'] === 'Menunggu' && (auth()->user()?->role === 'Owner')): ?>
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
                                <form action="/admin/pembayaran/hapus" method="post" class="d-inline" data-jr-confirm="Yakin hapus pembayaran ini?">@csrf<input type="hidden" name="id" value="<?= $row['id_pembayaran'] ?>"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
                            <?php else: ?>
                                <span class="text-muted"><?= $row['status_konfirmasi'] === 'Menunggu' ? 'Menunggu Owner' : ($row['status_konfirmasi'] === 'Dikonfirmasi' ? 'Terkunci' : 'Ditolak') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

@include('layouts.footer')
