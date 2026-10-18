<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JR Audio</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/admin-custom.css">
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
</head>
<body>
<?php
$role = session()->get('role') ?? '';
$isOwner = $role === 'Owner';
$isKaryawan = $role === 'Karyawan';
?>
<?php
$currentPathAdmin = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$currentPathAdmin = str_replace('jraudio/public/', '', $currentPathAdmin);
$currentPathAdmin = str_replace('jraudio/', '', $currentPathAdmin);
$currentPathAdmin = $currentPathAdmin === '' ? '/' : '/' . $currentPathAdmin;

function adminNavActive(string $path, string $currentPathAdmin): string
{
    if ($path === '/' && $currentPathAdmin === '/') {
        return 'active';
    }

    return $path !== '/' && str_starts_with($currentPathAdmin, $path) ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 admin-navbar">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="<?= $isKaryawan ? '/transaksi' : '/dashboard' ?>">
            <img src="/assets/img/logo-jraudio.png" alt="JR Audio" class="brand-logo-admin">
        </a>

        <div class="navbar-nav me-auto">
            <?php if ($isOwner): ?>
                <a class="nav-link <?= adminNavActive('/dashboard', $currentPathAdmin) ?>" href="/dashboard">Dashboard</a>
                <a class="nav-link <?= adminNavActive('/admin/barang', $currentPathAdmin) ?>" href="/admin/barang">Barang</a>
                <a class="nav-link <?= adminNavActive('/admin/pelanggan', $currentPathAdmin) ?>" href="/admin/pelanggan">Pelanggan</a>
                <a class="nav-link <?= adminNavActive('/admin/karyawan', $currentPathAdmin) ?>" href="/admin/karyawan">Karyawan</a>
                <a class="nav-link <?= adminNavActive('/admin/transaksi', $currentPathAdmin) ?>" href="/admin/transaksi">Transaksi</a>
                <a class="nav-link <?= adminNavActive('/admin/pembayaran', $currentPathAdmin) ?>" href="/admin/pembayaran">Pembayaran</a>
                <a class="nav-link <?= adminNavActive('/admin/pengembalian', $currentPathAdmin) ?>" href="/admin/pengembalian">Pengembalian</a>
                <a class="nav-link <?= adminNavActive('/admin/laporan', $currentPathAdmin) ?>" href="/admin/laporan">Laporan</a>
            <?php elseif ($isKaryawan): ?>
                <a class="nav-link <?= adminNavActive('/cek-ketersediaan', $currentPathAdmin) ?>" href="/cek-ketersediaan">Cek Ketersediaan</a>
                <a class="nav-link <?= adminNavActive('/transaksi', $currentPathAdmin) ?>" href="/transaksi">Lihat Transaksi</a>
                <a class="nav-link <?= adminNavActive('/transaksi/tambah', $currentPathAdmin) ?>" href="/transaksi/tambah">Buat Transaksi</a>
                <a class="nav-link <?= adminNavActive('/pengembalian', $currentPathAdmin) ?>" href="/pengembalian">Pengembalian</a>
            <?php endif; ?>
        </div>

        <div class="navbar-nav">
            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars(session()->get('nama') ?? '') ?> (<?= htmlspecialchars($role) ?>)
            </span>
            <form action="/logout" method="post" onsubmit="return confirm('Yakin logout?')" class="d-inline">@csrf<button class="nav-link" type="submit" style="background:none;border:0;cursor:pointer">Logout</button></form>
        </div>
    </div>
</nav>
<div class="container-fluid px-4">
