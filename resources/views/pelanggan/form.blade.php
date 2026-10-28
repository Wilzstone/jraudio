<div class="mb-3">
    <label class="form-label">Nama Pelanggan</label>
    <input type="text" name="nama_pelanggan" class="form-control" value="<?= htmlspecialchars($data['nama_pelanggan'] ?? '') ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">No HP</label>
    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($data['no_hp'] ?? '') ?>">
</div>

<div class="mb-3">
    <label class="form-label">Alamat</label>
    <textarea name="alamat" class="form-control" rows="4"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
</div>

<div class="jr-form-actions">
    <button type="submit" class="btn btn-primary">{{ isset($data) ? 'Update Pelanggan' : 'Simpan Pelanggan' }}</button>
    <a href="/admin/pelanggan" class="btn btn-outline-secondary">Kembali</a>
</div>
