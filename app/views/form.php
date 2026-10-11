<div class="mb-3">
    <label class="form-label">Nama Barang</label>
    <input type="text" name="nama_barang" class="form-control" required value="<?= htmlspecialchars($data['nama_barang'] ?? ''); ?>">
</div>

<div class="mb-3">
    <label class="form-label">Kategori</label>
    <input type="text" name="kategori" class="form-control" required placeholder="Contoh: Speaker, Mixer, Microphone" value="<?= htmlspecialchars($data['kategori'] ?? ''); ?>">
</div>

<div class="mb-3">
    <label class="form-label">Harga Sewa</label>
    <input type="number" name="harga_sewa" class="form-control" required value="<?= htmlspecialchars($data['harga_sewa'] ?? ''); ?>">
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Stok Tersedia</label>
        <input type="number" name="stok" class="form-control" min="0" required value="<?= htmlspecialchars($data['stok'] ?? '0'); ?>">
        <small class="text-muted">Jumlah barang yang bisa dipinjam.</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Sedang Disewa</label>
        <input type="number" class="form-control" readonly value="<?= htmlspecialchars($data['stok_disewa'] ?? '0'); ?>">
        <small class="text-muted">Otomatis bertambah saat transaksi dan berkurang saat pengembalian.</small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Maintenance / Rusak</label>
        <input type="number" name="stok_maintenance" class="form-control" min="0" required value="<?= htmlspecialchars($data['stok_maintenance'] ?? '0'); ?>">
        <small class="text-muted">Barang rusak/maintenance tidak ikut dihitung sebagai stok tersedia.</small>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Gambar</label>
    <input type="file" name="gambar" class="form-control" accept="image/*">
    <?php if (!empty($data['gambar'])): ?>
        <div class="mt-2">
            <img src="/assets/img/barang/<?= htmlspecialchars($data['gambar']); ?>" width="90" height="90" style="object-fit:cover">
        </div>
    <?php endif; ?>
</div>
