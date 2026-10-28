@include('layouts.header')

<div class="print-report-header">
    <div>
        <img src="/assets/img/logo-jraudio.png" alt="JR Audio" class="report-print-logo">
        <div>Sistem Manajemen Penyewaan Sound System</div>
        <div>Telp/WhatsApp: 082331745445</div>
    </div>
    <div class="text-end">
        <h4>LAPORAN JR AUDIO</h4>
        <div>Tanggal cetak: <?= date('d-m-Y'); ?></div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Laporan JR Audio</h3>
    <button onclick="window.print()" class="btn btn-success">Print / Simpan PDF</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-header">Filter Laporan</div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($tanggalMulai) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($tanggalSelesai) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary">Tampilkan</button>
                <a href="/admin/laporan" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-2"><div class="card jr-form-card"><div class="card-body"><small>Total Transaksi</small><h4><?= (int) $ringkasan['total_transaksi'] ?></h4></div></div></div>
    <div class="col-md-3 mb-2"><div class="card jr-form-card"><div class="card-body"><small>Total Nilai Transaksi</small><h4>Rp <?= number_format((int) $ringkasan['total_nilai_transaksi'], 0, ',', '.') ?></h4></div></div></div>
    <div class="col-md-3 mb-2"><div class="card jr-form-card"><div class="card-body"><small>Pendapatan Terkonfirmasi</small><h4>Rp <?= number_format((int) $ringkasan['total_pendapatan'], 0, ',', '.') ?></h4></div></div></div>
    <div class="col-md-3 mb-2"><div class="card jr-form-card"><div class="card-body"><small>Transaksi Berjalan</small><h4><?= (int) $ringkasan['total_berjalan'] ?></h4></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header">Laporan Transaksi</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Karyawan</th><th>Status</th><th>Total</th><th>Terbayar</th><th>Sisa</th><th class="no-print">Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporanTransaksi)): ?>
                    <tr><td colspan="9" class="text-center">Data transaksi belum ada.</td></tr>
                <?php endif; ?>
                <?php foreach ($laporanTransaksi as $i => $row): ?>
                    <?php $sisa = max(0, (int) $row['total_harga'] - (int) $row['total_bayar']); ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['tanggal_transaksi']) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status_transaksi']) ?></span></td>
                        <td>Rp <?= number_format((int) $row['total_harga'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format((int) $row['total_bayar'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($sisa, 0, ',', '.') ?></td>
                        <td class="no-print"><a class="btn btn-sm btn-outline-primary" href="/admin/invoice?id=<?= $row['id_transaksi'] ?>">Cetak</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Laporan Pembayaran</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Jenis</th><th>Jumlah</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($laporanPembayaran)): ?>
                    <tr><td colspan="6" class="text-center">Data pembayaran belum ada.</td></tr>
                <?php endif; ?>
                <?php foreach ($laporanPembayaran as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['tanggal_bayar']) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_pembayaran']) ?></td>
                        <td>Rp <?= number_format((int) $row['jumlah_bayar'], 0, ',', '.') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status_konfirmasi']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Laporan Barang</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr><th>No</th><th>Nama Barang</th><th>Kategori</th><th>Harga Sewa</th><th>Stok</th><th>Status</th><th>Total Disewa</th><th>Total Pemasukan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($laporanBarang as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td><?= htmlspecialchars($row['kategori']) ?></td>
                        <td>Rp <?= number_format((int) $row['harga_sewa'], 0, ',', '.') ?></td>
                        <td><?= (int) $row['stok'] ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= (int) $row['total_disewa'] ?></td>
                        <td>Rp <?= number_format((int) $row['total_pemasukan_barang'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .navbar, .no-print, .btn { display: none !important; }
    .container, .container-fluid { max-width: 100% !important; width: 100% !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { font-weight: bold; background: white !important; }
    body { background: white !important; }
}
</style>

@include('layouts.footer')
