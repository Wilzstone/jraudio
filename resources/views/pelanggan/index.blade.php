@include('layouts.header')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Pelanggan</h3>
    <a href="/admin/pelanggan/tambah" class="btn btn-primary">+ Tambah Pelanggan</a>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="60">No</th>
                    <th>Nama Pelanggan</th>
                    <th>No HP</th>
                    <th>Alamat</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pelanggan)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Data pelanggan belum ada</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pelanggan as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                            <td><?= htmlspecialchars($row['no_hp']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td>
                                <a href="/admin/pelanggan/edit?id=<?= $row['id_pelanggan'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form action="/admin/pelanggan/hapus" method="post" style="display:inline" onsubmit="return confirm('Yakin hapus data ini?')">@csrf
<input type="hidden" name="id" value="<?= $row['id_pelanggan'] ?>">
<button type="submit" class="btn btn-danger btn-sm">Hapus</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

@include('layouts.footer')
