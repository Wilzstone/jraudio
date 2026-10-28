<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice JR Audio #<?= htmlspecialchars($transaksi['id_transaksi']) ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/admin-custom.css">
    <style>
        body { background: #f5f5f5; }
        .invoice-box { background: white; max-width: 900px; margin: 30px auto; padding: 30px; border-radius: 10px; }
        @media print {
            body { background: white; }
            .invoice-box { margin: 0; max-width: 100%; box-shadow: none !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="invoice-box shadow">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <img src="/assets/img/logo-jraudio.png" alt="JR Audio" class="invoice-logo">
            <div>Sistem Manajemen Penyewaan Sound System</div>
            <div class="text-muted">Telp/WhatsApp: 082331745445</div>
            <div class="text-muted">Invoice / Struk Penyewaan</div>
        </div>
        <div class="text-end">
            <h4>INVOICE</h4>
            <div>No: INV-<?= str_pad((string) $transaksi['id_transaksi'], 4, '0', STR_PAD_LEFT) ?></div>
            <div>Tanggal: <?= date('d-m-Y') ?></div>
        </div>
    </div>

    <hr>

    <div class="row mb-4">
        <div class="col-md-6">
            <h6>Data Pelanggan</h6>
            <table class="table table-sm table-borderless">
                <tr><td width="140">Nama</td><td>: <?= htmlspecialchars($transaksi['nama_pelanggan']) ?></td></tr>
                <tr><td>No HP</td><td>: <?= htmlspecialchars($transaksi['no_hp']) ?></td></tr>
                <tr><td>Alamat pelanggan</td><td>: <?= htmlspecialchars($transaksi['alamat']) ?></td></tr>
                <tr><td>Lokasi acara</td><td>: <?= htmlspecialchars($transaksi['alamat_lokasi_acara'] ?? '-') ?></td></tr>
                <?php if (!empty($transaksi['link_google_maps'])): ?>
                  <tr><td>Google Maps</td><td>: <?= htmlspecialchars($transaksi['link_google_maps']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Data Penyewaan</h6>
            <table class="table table-sm table-borderless">
                <tr><td width="140">Tanggal Sewa</td><td>: <?= htmlspecialchars($transaksi['tanggal_sewa']) ?></td></tr>
                <tr><td>Tanggal Kembali</td><td>: <?= htmlspecialchars($transaksi['tanggal_kembali']) ?></td></tr>
                <tr><td>Status</td><td>: <?= htmlspecialchars($transaksi['status_transaksi']) ?></td></tr>
                <tr><td>Petugas</td><td>: <?= htmlspecialchars($transaksi['nama_karyawan']) ?></td></tr>
            </table>
        </div>
    </div>

    <h6>Detail Barang</h6>
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr><th>No</th><th>Barang</th><th>Kategori</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($detail as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($row['kategori']) ?></td>
                    <td><?= (int) $row['qty'] ?></td>
                    <td>Rp <?= number_format((int) $row['harga_satuan'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format((int) $row['subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row">
        <div class="col-md-6">
            <h6>Riwayat Pembayaran</h6>
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Tanggal</th><th>Jenis</th><th>Jumlah</th><th>Status</th></tr></thead>
                <tbody>
                    <?php if (empty($pembayaran)): ?>
                        <tr><td colspan="4" class="text-center">Belum ada pembayaran</td></tr>
                    <?php endif; ?>
                    <?php foreach ($pembayaran as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['tanggal_bayar']) ?></td>
                            <td><?= htmlspecialchars($row['jenis_pembayaran']) ?></td>
                            <td>Rp <?= number_format((int) $row['jumlah_bayar'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['status_konfirmasi']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr><th>Total Sewa</th><td class="text-end">Rp <?= number_format((int) $transaksi['total_harga'], 0, ',', '.') ?></td></tr>
                <tr><th>Denda</th><td class="text-end">Rp <?= number_format((int) $totalDenda, 0, ',', '.') ?></td></tr>
                <tr><th>Total Tagihan</th><td class="text-end">Rp <?= number_format((int) $totalTagihan, 0, ',', '.') ?></td></tr>
                <tr><th>Total Bayar Terkonfirmasi</th><td class="text-end">Rp <?= number_format((int) $totalBayar, 0, ',', '.') ?></td></tr>
                <tr><th>Sisa Bayar</th><td class="text-end">Rp <?= number_format((int) $sisaBayar, 0, ',', '.') ?></td></tr>
            </table>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-6">
            <p><strong>Catatan:</strong></p>
            <p class="text-muted">Barang wajib dikembalikan sesuai tanggal kembali. Kerusakan atau keterlambatan dapat dikenakan denda.</p>
        </div>
        <div class="col-md-6 text-center">
            <p>Petugas JR Audio</p>
            <br><br><br>
            <p><strong><?= htmlspecialchars($transaksi['nama_karyawan']) ?></strong></p>
        </div>
    </div>

    <div class="no-print mt-4 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-success">Print / Simpan PDF</button>
        <a href="/admin/laporan" class="btn btn-secondary">Kembali ke Laporan</a>
        <a href="/admin/transaksi/detail?id=<?= $transaksi['id_transaksi'] ?>" class="btn btn-primary">Detail Transaksi</a>
    </div>
</div>
</body>
</html>
