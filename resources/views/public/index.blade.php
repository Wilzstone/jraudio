@include('public.header')
<?php
$waHomeText = rawurlencode('Hai, saya mau tanya seputar penyewaan sound system JR Audio.');
$waHomeUrl = 'https://wa.me/6282331745445?text=' . $waHomeText;
?>

<section class="hero">
    <div class="hero-content">
        <img src="/assets/img/logo-jraudio.png" alt="JR Audio" class="hero-logo">
        <h1>Sewa Sound System Mudah dan Terpercaya</h1>
        <p>JR Audio menyediakan berbagai kebutuhan audio untuk acara, event, seminar, hajatan, dan kegiatan lainnya.</p>
        <div class="hero-actions">
            <a href="/barang" class="btn-primary">Lihat Barang</a>
            <a href="/rekomendasi" class="btn-primary">Cari Kebutuhan Acara</a>
            <a href="<?= htmlspecialchars($waHomeUrl); ?>" class="btn-secondary" target="_blank" rel="noopener">Hubungi WhatsApp</a>
        </div>
    </div>
</section>

<section class="section">
    <h2>Barang Tersedia</h2>
    <div class="card-container">
        <?php if (!empty($barang)) : ?>
            <?php foreach (array_slice($barang, 0, 6) as $item) : ?>
                <div class="card">
                    <h3><?= htmlspecialchars($item['nama_barang']); ?></h3>
                    <p>Kategori: <?= htmlspecialchars($item['kategori']); ?></p>
                    <p>Stok: <?= htmlspecialchars($item['stok']); ?></p>
                    <p class="price">Rp <?= number_format($item['harga_sewa'], 0, ',', '.'); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Belum ada barang tersedia.</p>
        <?php endif; ?>
    </div>
</section>


<section class="section">
    <div class="section-title">
        <span class="eyebrow">Paket Praktis</span>
        <h2>Paket Sound System</h2>
        <p>Pelanggan bisa memilih paket yang sudah disiapkan tanpa harus memilih alat satu per satu.</p>
    </div>
    <div class="paket-mini-grid">
        <a class="paket-mini-card" href="/paket">
            <img src="/assets/img/paket/1.png" alt="Paket 2500">
            <h3>Paket 2500</h3>
            <p>Seminar, indoor, makrab, dan live music.</p>
        </a>
        <a class="paket-mini-card" href="/paket">
            <img src="/assets/img/paket/2.png" alt="Paket 5000">
            <h3>Paket 5000</h3>
            <p>Wedding, 17-an, dangdutan, jathilan, dan acara besar.</p>
        </a>
        <a class="paket-mini-card" href="/paket">
            <img src="/assets/img/paket/genset.png" alt="Sewa Genset">
            <h3>Sewa Genset</h3>
            <p>Tambahan genset 9/10 kVA untuk kebutuhan listrik event.</p>
        </a>
    </div>
</section>

<section class="section section-soft">
    <div class="section-title">
        <span class="eyebrow">Dokumentasi</span>
        <h2>Galeri Acara</h2>
        <p>Lihat contoh dokumentasi dan suasana acara yang didukung oleh JR Audio.</p>
    </div>
    <div class="gallery-preview">
        <img src="/assets/img/galeri/event1.jpeg" alt="Galeri JR Audio 1">
        <img src="/assets/img/galeri/event2.jpeg" alt="Galeri JR Audio 2">
        <img src="/assets/img/galeri/event3.jpeg" alt="Galeri JR Audio 3">
    </div>
    <div class="center">
        <a class="btn-primary" href="/galeri">Lihat Galeri</a>
    </div>
</section>


@include('public.footer')
