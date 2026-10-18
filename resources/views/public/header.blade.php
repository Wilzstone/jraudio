<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111216">
    <meta name="description" content="JR Audio menyediakan penyewaan sound system, paket audio, dan Smart Recommendation untuk kebutuhan berbagai acara.">
    <title>@yield('title', 'JR Audio | Sound System untuk Setiap Acara')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/jr-favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public.css') }}?v=2.0">
    <link rel="stylesheet" href="{{ asset('assets/css/smart.css') }}?v=2.0">
</head>
<body>
<a class="skip-link" href="#main-content">Lewati navigasi</a>
@php
$waNav = 'https://wa.me/6282331745445?text='.rawurlencode('Halo JR Audio, saya ingin konsultasi penyewaan sound system.');
@endphp
<div class="topline"><div class="container topline-inner"><span><span class="live-dot"></span> Sound System & Event Equipment</span><a href="{{ $waNav }}" target="_blank" rel="noopener noreferrer">Butuh bantuan memilih alat? <strong>Chat JR Audio ↗</strong></a></div></div>
<header class="site-header" id="site-header">
    <div class="container navbar-inner">
        <a class="logo-lockup" href="{{ url('/') }}" aria-label="Beranda JR Audio">
            <img src="{{ asset('assets/img/logo-jraudio.png') }}" alt="Logo JR Audio" width="186" height="51">
        </a>
        <button class="nav-toggle" type="button" id="nav-toggle" aria-controls="main-navigation" aria-expanded="false" aria-label="Buka menu navigasi"><span></span><span></span><span></span></button>
        <nav class="main-navigation" id="main-navigation" aria-label="Navigasi utama">
            <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'is-active' : '' }}" @if(request()->is('/')) aria-current="page" @endif>Beranda</a>
            <a href="{{ url('/barang') }}" class="{{ request()->is('barang') ? 'is-active' : '' }}" @if(request()->is('barang')) aria-current="page" @endif>Katalog</a>
            <a href="{{ url('/paket') }}" class="{{ request()->is('paket') ? 'is-active' : '' }}" @if(request()->is('paket')) aria-current="page" @endif>Paket</a>
            <a href="{{ route('rekomendasi.index') }}" class="{{ request()->is('rekomendasi') ? 'is-active' : '' }} nav-smart" @if(request()->is('rekomendasi')) aria-current="page" @endif><span class="spark-icon">✦</span> Smart Recommendation</a>
            <a href="{{ url('/galeri') }}" class="{{ request()->is('galeri') ? 'is-active' : '' }}" @if(request()->is('galeri')) aria-current="page" @endif>Galeri</a>
            <a href="{{ url('/tentang') }}" class="{{ request()->is('tentang') ? 'is-active' : '' }}" @if(request()->is('tentang')) aria-current="page" @endif>Tentang</a>
            <a class="mobile-contact" href="{{ url('/kontak') }}">Kontak</a>
        </nav>
        <a class="nav-cta" href="{{ $waNav }}" target="_blank" rel="noopener noreferrer">Hubungi Kami <span aria-hidden="true">↗</span></a>
    </div>
</header>
