<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nama Karyawan</label>
        <input type="text" name="nama_karyawan" class="form-control" required value="<?= htmlspecialchars($data['nama_karyawan'] ?? ''); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($data['username'] ?? ''); ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Password <?= isset($data) ? '(kosongkan jika tidak diganti)' : ''; ?></label>
        <input type="password" name="password" class="form-control" <?= isset($data) ? '' : 'required'; ?>>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Role</label>
        <?php $role = $data['role'] ?? 'Karyawan'; ?>
        <select name="role" class="form-select" required>
            <option value="Owner" <?= $role === 'Owner' ? 'selected' : ''; ?>>Owner</option>
            <option value="Karyawan" <?= $role === 'Karyawan' ? 'selected' : ''; ?>>Karyawan</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">No HP</label>
        <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($data['no_hp'] ?? ''); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Jabatan</label>
        <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Owner, Operator" value="<?= htmlspecialchars($data['jabatan'] ?? ''); ?>">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Alamat</label>
    <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($data['alamat'] ?? ''); ?></textarea>
</div>
