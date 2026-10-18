@php
$waFooter = 'https://wa.me/6282331745445?text='.rawurlencode('Halo JR Audio, saya ingin konsultasi kebutuhan sound system untuk acara.');
@endphp
<section class="prefooter" aria-labelledby="cta-heading">
  <div class="container prefooter-layout">
    <div><span class="eyebrow eyebrow-light">SIAP RENCANAKAN ACARAMU?</span><h2 id="cta-heading">Biar urusan audio,<br><em>kami bantu pikirkan.</em></h2><p>Ceritakan kebutuhan acaramu, lalu konsultasikan pilihan perlengkapannya dengan tim JR Audio.</p></div>
    <div class="prefooter-actions"><a class="button button-white" href="{{ route('rekomendasi.index') }}">✦ &nbsp; Coba Smart Recommendation <span aria-hidden="true">↗</span></a><a class="text-link-light" href="{{ $waFooter }}" target="_blank" rel="noopener noreferrer">Atau chat langsung via WhatsApp →</a></div>
  </div>
</section>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-about"><div class="footer-logo-frame"><img src="{{ asset('assets/img/logo-jraudio.png') }}" alt="JR Audio" width="200" height="55"></div><p>Perlengkapan sound system untuk berbagai kebutuhan acara. Lihat katalog, rencanakan kebutuhan, dan konsultasikan detailnya bersama kami.</p></div>
    <div><h3>Jelajahi</h3><a href="{{ url('/') }}">Beranda</a><a href="{{ url('/barang') }}">Katalog Barang</a><a href="{{ url('/paket') }}">Paket Audio</a><a href="{{ route('rekomendasi.index') }}">Smart Recommendation</a></div>
    <div><h3>JR Audio</h3><a href="{{ url('/tentang') }}">Tentang Kami</a><a href="{{ url('/galeri') }}">Galeri Acara</a><a href="{{ url('/partner') }}">Partner</a><a href="{{ url('/lokasi') }}">Lokasi</a></div>
    <div><h3>Hubungi Kami</h3><a href="{{ $waFooter }}" target="_blank" rel="noopener noreferrer">WhatsApp: 0823 3174 5445 ↗</a><a href="https://www.instagram.com/jr.audioo" target="_blank" rel="noopener noreferrer">Instagram @jr.audioo ↗</a><a href="https://www.tiktok.com/@jr_audio_production" target="_blank" rel="noopener noreferrer">TikTok JR Audio ↗</a><a href="{{ url('/kontak') }}">Kontak lengkap →</a></div>
  </div>
  <div class="container footer-bottom"><span>© {{ date('Y') }} JR Audio. All rights reserved.</span><span>Dirancang untuk kebutuhan penyewaan audio & event.</span><a href="{{ url('/login') }}">Login Admin</a></div>
</footer>
<script src="{{ asset('assets/js/public-ui.js') }}?v=2.0" defer></script>
</body>
</html>
