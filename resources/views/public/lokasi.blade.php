@include('public.header')
<?php
$mapsUrl = 'https://maps.app.goo.gl/3PbyNjtrfRkZsbPx5';
$waText = rawurlencode('Hai, saya mau tanya lokasi JR Audio dan penyewaan sound system.');
$whatsappUrl = 'https://wa.me/6282331745445?text=' . $waText;
?>

<section class="page-header">
    <h1>Lokasi</h1>
    <p>Temukan lokasi JR Audio melalui Google Maps.</p>
</section>

<section class="section text-section">
    <h2>Lokasi JR Audio</h2>
    <p>Silakan buka link Google Maps di bawah ini untuk melihat rute menuju lokasi JR Audio.</p>

    <div class="info-box">
        <p><strong>Lokasi:</strong> <a href="<?= htmlspecialchars($mapsUrl); ?>" target="_blank" rel="noopener">Buka Google Maps JR Audio</a></p>
        <p><strong>No. Telepon / WhatsApp:</strong> <a href="<?= htmlspecialchars($whatsappUrl); ?>" target="_blank" rel="noopener">082331745445</a></p>
    </div>

    <div class="map-placeholder map-card">
        <div>
            <h3>JR Audio</h3>
            <p>Klik tombol di bawah untuk membuka rute di Google Maps.</p>
            <a class="btn-primary" href="<?= htmlspecialchars($mapsUrl); ?>" target="_blank" rel="noopener">Buka Lokasi</a>
        </div>
    </div>
</section>

@include('public.footer')
