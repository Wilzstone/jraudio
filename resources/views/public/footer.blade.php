<?php
$footerWaText = rawurlencode('Hai, saya mau tanya seputar penyewaan sound system JR Audio.');
$footerWhatsappUrl = 'https://wa.me/6282331745445?text=' . $footerWaText;
$footerTiktokUrl = 'https://www.tiktok.com/@jr_audio_production?_r=1&_t=ZS-96jk2bTEbDD';
$footerInstagramUrl = 'https://www.instagram.com/jr.audioo?igsh=MW9xMTE3aXV2ZjByOQ==';
$footerMapsUrl = 'https://maps.app.goo.gl/3PbyNjtrfRkZsbPx5';
$footerTiktokIntent = 'intent://www.tiktok.com/@jr_audio_production#Intent;scheme=https;package=com.zhiliaoapp.musically;S.browser_fallback_url=' . rawurlencode($footerTiktokUrl) . ';end';
$footerInstagramIntent = 'intent://instagram.com/_u/jr.audioo#Intent;scheme=https;package=com.instagram.android;S.browser_fallback_url=' . rawurlencode($footerInstagramUrl) . ';end';
?>
<footer>
    <div class="footer-brand">
        <img src="/assets/img/logo-jraudio.png" alt="JR Audio" class="footer-logo">
    </div>
    <p>&copy; <?= date('Y'); ?> JR Audio. Sistem Penyewaan Sound System.</p>
    <div class="footer-links">
        <a href="<?= htmlspecialchars($footerWhatsappUrl); ?>" target="_blank" rel="noopener">082331745445</a>
        <span>|</span>
        <a href="<?= htmlspecialchars($footerTiktokUrl); ?>" data-app-link data-android-intent="<?= htmlspecialchars($footerTiktokIntent); ?>" data-ios-link="<?= htmlspecialchars($footerTiktokUrl); ?>" target="_blank" rel="noopener">TikTok</a>
        <span>|</span>
        <a href="<?= htmlspecialchars($footerInstagramUrl); ?>" data-app-link data-android-intent="<?= htmlspecialchars($footerInstagramIntent); ?>" data-ios-link="instagram://user?username=jr.audioo" target="_blank" rel="noopener">Instagram</a>
        <span>|</span>
        <a href="<?= htmlspecialchars($footerMapsUrl); ?>" target="_blank" rel="noopener">Lokasi</a>
    </div>
</footer>

<script src="/assets/js/sweetalert2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-app-link]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var userAgent = navigator.userAgent || navigator.vendor || window.opera;
            var isAndroid = /android/i.test(userAgent);
            var isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;

            if (isAndroid && link.dataset.androidIntent) {
                link.setAttribute('href', link.dataset.androidIntent);
                return;
            }

            if (isIOS && link.dataset.iosLink && link.dataset.iosLink.indexOf('instagram://') === 0) {
                event.preventDefault();
                var fallbackUrl = link.getAttribute('href');
                window.location.href = link.dataset.iosLink;
                setTimeout(function () {
                    window.location.href = fallbackUrl;
                }, 1200);
            }
        });
    });
});
</script>
</body>
</html>
