@include('layouts.header')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Karyawan JR Audio</h3>
    <a href="/admin/karyawan/tambah" class="btn btn-primary btn-sm">+ Tambah Karyawan</a>
</div>

<table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Nama Karyawan</th>
            <th>Username</th>
            <th>Role</th>
            <th>No HP</th>
            <th>Jabatan</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($karyawan as $row): ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['nama_karyawan']); ?></td>
            <td><?= htmlspecialchars($row['username']); ?></td>
            <td><?= htmlspecialchars($row['role']); ?></td>
            <td><?= htmlspecialchars($row['no_hp']); ?></td>
            <td><?= htmlspecialchars($row['jabatan']); ?></td>
            <td><?= htmlspecialchars($row['alamat']); ?></td>
            <td>
                <a class="btn btn-warning btn-sm" href="/admin/karyawan/edit?id=<?= $row['id_karyawan']; ?>">Edit</a>
                <form action="/admin/karyawan/hapus" method="post" style="display:inline" onsubmit="return confirm('Yakin hapus data ini?')">@csrf
<input type="hidden" name="id" value="<?= $row['id_karyawan'] ?>">
<button type="submit" class="btn btn-danger btn-sm">Hapus</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

@include('layouts.footer')
