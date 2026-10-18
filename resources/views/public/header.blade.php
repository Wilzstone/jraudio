<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JR Audio - Penyewaan Sound System</title>
    <link rel="stylesheet" href="/assets/css/public.css">
    <link rel="stylesheet" href="/assets/css/smart.css">
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
</head>
<body>

<?php
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$currentPath = str_replace('jraudio/public/', '', $currentPath);
$currentPath = str_replace('jraudio/', '', $currentPath);
$currentPath = $currentPath === '' ? '/' : '/' . $currentPath;

function navActive(string $path, string $currentPath): string
{
    if ($path === '/' && $currentPath === '/') {
        return 'active';
    }

    return $path !== '/' && str_starts_with($currentPath, $path) ? 'active' : '';
}
?>
<nav class="navbar public-navbar">
    <div class="brand">
        <a href="/" class="brand-link <?= navActive('/', $currentPath) ?>">
            <img src="/assets/img/logo-jraudio.png" alt="Logo JR Audio" class="brand-logo">
            <span>JR Audio</span>
        </a>
    </div>
    <ul>
        <li><a class="<?= navActive('/', $currentPath) ?>" href="/">Home</a></li>
        <li><a class="<?= navActive('/tentang', $currentPath) ?>" href="/tentang">Tentang Kami</a></li>
        <li><a class="<?= navActive('/paket', $currentPath) ?>" href="/paket">Paket Harga</a></li>
        <li><a class="<?= navActive('/barang', $currentPath) ?>" href="/barang">Barang</a></li>
        <li><a class="<?= navActive('/rekomendasi', $currentPath) ?>" href="/rekomendasi">Smart Recommendation</a></li>
        <li><a class="<?= navActive('/galeri', $currentPath) ?>" href="/galeri">Galeri</a></li>
        <li><a class="<?= navActive('/partner', $currentPath) ?>" href="/partner">Partner</a></li>
        <li><a class="<?= navActive('/kontak', $currentPath) ?>" href="/kontak">Kontak</a></li>
    </ul>
</nav>
