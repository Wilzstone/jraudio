<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171920">
    <title>Panel Admin | JR Audio</title>
    <link rel="icon" href="{{ asset('assets/img/jr-favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-shell.css') }}?v=3.0">
    @if (request()->is('admin/barang') || request()->is('cek-ketersediaan') || request()->is('admin/karyawan') || request()->is('admin/pelanggan') || request()->is('admin/transaksi') || request()->is('transaksi'))
        <link rel="stylesheet" href="{{ asset('assets/css/admin-inventory.css') }}?v=4.0">
    @endif
    @if (request()->is('admin/karyawan') || request()->is('admin/pelanggan') || request()->is('admin/transaksi') || request()->is('transaksi'))
        <link rel="stylesheet" href="{{ asset('assets/css/admin-data-tables.css') }}?v=4.0">
        <script src="{{ asset('assets/js/admin-data-tables.js') }}?v=4.0" defer></script>
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">
</head>
<body class="ja-admin-page">
<div class="ja-app" id="jaApp">
    @include('layouts.admin-sidebar')
    <main class="ja-main" id="main-content">
        @include('layouts.admin-topbar')
        <div class="ja-admin-content">
