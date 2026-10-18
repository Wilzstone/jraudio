@include('public.header')

<?php
$wa = '6281234567890';
$pesan2500 = urlencode('Halo JR Audio, saya ingin konsultasi Paket 2500 untuk acara. Boleh minta informasi detail dan estimasi harga?');
$pesan5000 = urlencode('Halo JR Audio, saya ingin konsultasi Paket 5000 untuk acara. Boleh minta informasi detail dan estimasi harga?');
$pesanGenset = urlencode('Halo JR Audio, saya ingin konsultasi sewa genset untuk event. Boleh minta informasi detail?');
?>

<section class="page-header page-header-gradient">
    <span class="eyebrow">Katalog JR Audio</span>
    <h1>Daftar Harga Paket Sound System</h1>
    <p>Paket dibuat supaya pelanggan lebih mudah memilih kebutuhan sound system tanpa harus menentukan alat satu per satu.</p>
</section>

<section class="section">
    <div class="section-title">
        <span class="eyebrow">Paket Utama</span>
        <h2>Pilih Paket Sesuai Acara</h2>
        <p>Harga dapat berubah mengikuti jarak lokasi, durasi acara, request penambahan alat musik, dan kebutuhan teknis di lapangan.</p>
    </div>

    <div class="paket-grid paket-grid-2">
        <div class="paket-card paket-highlight">
            <img src="/assets/img/paket/1.png" alt="Paket 2500 JR Audio">
            <div class="paket-body">
                <span class="badge-soft">Paket 2500</span>
                <h3>Paket 2500</h3>
                <p>Paket yang cocok untuk acara indoor dan kebutuhan sound system skala kecil sampai menengah.</p>
                <ul>
                    <li>Seminar</li>
                    <li>Acara indoor</li>
                    <li>Makrab</li>
                    <li>Live music</li>
                </ul>
                <div class="paket-price">Range Rp 600.000 - Rp 800.000</div>
                <a class="btn-primary" target="_blank" href="https://wa.me/<?= $wa ?>?text=<?= $pesan2500 ?>">Konsultasi Paket 2500</a>
            </div>
        </div>

        <div class="paket-card paket-highlight-red">
            <img src="/assets/img/paket/2.png" alt="Paket 5000 JR Audio">
            <div class="paket-body">
                <span class="badge-soft badge-red">Paket 5000</span>
                <h3>Paket 5000</h3>
                <p>Paket untuk acara besar, outdoor, hiburan, acara umum, dan kebutuhan sound system yang lebih kuat.</p>
                <ul>
                    <li>Wedding</li>
                    <li>Acara 17-an</li>
                    <li>Dangdutan</li>
                    <li>Jathilan</li>
                    <li>Wayang</li>
                    <li>Misa di gereja</li>
                    <li>Takbiran</li>
                    <li>Open ceremony</li>
                    <li>Wisuda / kelulusan</li>
                </ul>
                <div class="paket-price">Range Rp 900.000 - Rp 1.200.000</div>
                <a class="btn-primary" target="_blank" href="https://wa.me/<?= $wa ?>?text=<?= $pesan5000 ?>">Konsultasi Paket 5000</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="section-title">
        <span class="eyebrow">Tambahan</span>
        <h2>Sewa Genset</h2>
        <p>Untuk acara yang membutuhkan sumber listrik tambahan, JR Audio juga menyediakan opsi sewa genset.</p>
    </div>

    <div class="info-card-wide">
        <h3>Harga Sewa Genset</h3>
        <p><strong>Rp 400.000 per event</strong> untuk kapasitas sekitar <strong>9 atau 10 kVA</strong>.</p>
        <p>Jika kebutuhan genset di atas 10 kVA, maka harga dapat berbeda dan perlu dikonsultasikan terlebih dahulu.</p>
        <a class="btn-primary" target="_blank" href="https://wa.me/<?= $wa ?>?text=<?= $pesanGenset ?>">Konsultasi Genset</a>
    </div>
</section>

<section class="section">
    <div class="section-title">
        <span class="eyebrow">Jenis Event</span>
        <h2>Event yang Bisa Ditangani</h2>
        <p>JR Audio dapat mendukung berbagai kebutuhan acara, baik indoor maupun outdoor.</p>
    </div>

    <div class="event-list-grid">
        <span>Seminar</span><span>Acara Indoor</span><span>Makrab</span><span>Live Music</span>
        <span>Wedding</span><span>17-an</span><span>Dangdutan</span><span>Jathilan</span>
        <span>Wayang</span><span>Misa Gereja</span><span>Takbiran</span><span>Open Ceremony</span>
        <span>Wisuda / Kelulusan</span>
    </div>
</section>

<section class="section section-dark">
    <div class="section-title light">
        <span class="eyebrow">Catatan Harga</span>
        <h2>Harga dapat berubah sesuai kebutuhan acara</h2>
        <p>Harga menyesuaikan jarak lokasi, request penambahan alat musik, kapasitas acara, dan kondisi teknis pemasangan di lapangan.</p>
        <a class="btn-light" href="/kontak">Hubungi JR Audio</a>
    </div>
</section>

@include('public.footer')
