@include('public.header')
<?php
$noTelp = '082331745445';
$waText = rawurlencode('Hai, saya mau tanya seputar penyewaan sound system JR Audio.');
$whatsappUrl = 'https://wa.me/6282331745445?text=' . $waText;
$tiktokUrl = 'https://www.tiktok.com/@jr_audio_production?_r=1&_t=ZS-96jk2bTEbDD';
$instagramUrl = 'https://www.instagram.com/jr.audioo?igsh=MW9xMTE3aXV2ZjByOQ==';
$mapsUrl = 'https://maps.app.goo.gl/3PbyNjtrfRkZsbPx5';
$tiktokIntent = 'intent://www.tiktok.com/@jr_audio_production#Intent;scheme=https;package=com.zhiliaoapp.musically;S.browser_fallback_url=' . rawurlencode($tiktokUrl) . ';end';
$instagramIntent = 'intent://instagram.com/_u/jr.audioo#Intent;scheme=https;package=com.instagram.android;S.browser_fallback_url=' . rawurlencode($instagramUrl) . ';end';
?>

<section class="page-header page-header-gradient">
    <span class="eyebrow">Kontak</span>
    <h1>Kontak</h1>
    <p>Hubungi JR Audio untuk pemesanan sound system dan informasi ketersediaan barang.</p>
</section>

<section class="section text-section">
    <h2>Hubungi JR Audio</h2>
    <p>Untuk pemesanan atau pertanyaan, pelanggan dapat menghubungi kontak berikut.</p>

    <div class="info-box contact-list">
        <p><strong>No. Telepon / WhatsApp:</strong> <a href="<?= htmlspecialchars($whatsappUrl); ?>" target="_blank" rel="noopener"><?= $noTelp; ?></a></p>
        <p><strong>TikTok:</strong> <a href="<?= htmlspecialchars($tiktokUrl); ?>" data-app-link data-android-intent="<?= htmlspecialchars($tiktokIntent); ?>" data-ios-link="<?= htmlspecialchars($tiktokUrl); ?>" target="_blank" rel="noopener">@jr_audio_production</a></p>
        <p><strong>Instagram:</strong> <a href="<?= htmlspecialchars($instagramUrl); ?>" data-app-link data-android-intent="<?= htmlspecialchars($instagramIntent); ?>" data-ios-link="instagram://user?username=jr.audioo" target="_blank" rel="noopener">@jr_audioo</a></p>
        <p><strong>Lokasi:</strong> <a href="<?= htmlspecialchars($mapsUrl); ?>" target="_blank" rel="noopener">Lihat di Google Maps</a></p>
    </div>

    <div class="contact-actions">
        <a class="btn-primary" href="<?= htmlspecialchars($whatsappUrl); ?>" target="_blank" rel="noopener">Chat via WhatsApp</a>
        <a class="btn-secondary" href="<?= htmlspecialchars($tiktokUrl); ?>" data-app-link data-android-intent="<?= htmlspecialchars($tiktokIntent); ?>" data-ios-link="<?= htmlspecialchars($tiktokUrl); ?>" target="_blank" rel="noopener">Buka TikTok</a>
        <a class="btn-secondary" href="<?= htmlspecialchars($instagramUrl); ?>" data-app-link data-android-intent="<?= htmlspecialchars($instagramIntent); ?>" data-ios-link="instagram://user?username=jr.audioo" target="_blank" rel="noopener">Buka Instagram</a>
    </div>
</section>

@include('public.footer')
