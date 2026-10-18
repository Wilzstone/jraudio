@include('public.header')
<main id="main-content">
<section class="hero" aria-labelledby="hero-heading">
  <div class="hero-glow"></div><div class="hero-lines" aria-hidden="true"></div>
  <div class="container hero-grid">
    <div class="hero-copy">
      <div class="hero-kicker"><span class="hero-kicker-line"></span> SOUND SYSTEM RENTAL · JR AUDIO</div>
      <h1 id="hero-heading">Great events<br>deserve <span>great sound.</span></h1>
      <p>Mulai dari acara kecil sampai panggung yang ramai, temukan perlengkapan audio yang sesuai dengan kebutuhanmu. Tidak tahu harus pilih apa? Kami bantu mulai dari sini.</p>
      <div class="hero-buttons"><a class="button button-red" href="{{ route('rekomendasi.index') }}"><span aria-hidden="true">✦</span> Cari Rekomendasi <span aria-hidden="true">↗</span></a><a class="button button-ghost" href="{{ url('/barang') }}">Jelajahi Katalog <span aria-hidden="true">→</span></a></div>
      <div class="hero-footnote"><span class="hero-footnote-icon">✓</span><span>Rekomendasi kebutuhan acara · Konsultasi via WhatsApp · Tanpa booking otomatis</span></div>
    </div>
    <div class="hero-visual" aria-label="Dokumentasi peralatan dan kegiatan JR Audio">
      <div class="hero-photo-main"><img src="{{ asset('assets/img/galeri/event1.jpeg') }}" alt="Dokumentasi setup perlengkapan audio JR Audio" fetchpriority="high"></div>
      <div class="hero-photo-small"><img src="{{ asset('assets/img/galeri/event4.jpg') }}" alt="Dokumentasi sound system untuk kegiatan malam"></div>
      <div class="hero-floating-card"><span class="float-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></span><div><strong>TURN UP THE MOMENT</strong><small>Audio for every occasion.</small></div></div>
      <span class="hero-corner-label">JR / PRODUCTION <b>2026</b></span>
    </div>
  </div>
  <div class="container hero-bottom"><span>01 / SOUND EXPERIENCE</span><span>SCROLL UNTUK EKSPLORASI <span aria-hidden="true">↓</span></span></div>
</section>
<section class="service-strip" aria-label="Pilihan layanan JR Audio"><div class="container service-strip-inner"><span><b>01</b> Sewa Sound System</span><span><b>02</b> Konsultasi Kebutuhan</span><span><b>03</b> Paket Perlengkapan</span><span><b>04</b> Dukungan Berbagai Acara</span></div></section>
<section class="section" id="smart" aria-labelledby="smart-title">
  <div class="container smart-intro">
    <div class="smart-intro-copy"><span class="eyebrow">✦ &nbsp; FITUR UNGGULAN</span><h2 id="smart-title">Bingung pilih alat?<br><span>Mulai dari kebutuhan acaramu.</span></h2><p>Tidak perlu hafal spesifikasi sound system. Isi jenis acara, jumlah peserta, lokasi, dan anggaran. Sistem akan menyusun saran perlengkapan beserta alasan dan estimasi biayanya.</p><a href="{{ route('rekomendasi.index') }}" class="button button-dark">Coba Smart Recommendation <span aria-hidden="true">↗</span></a><p class="muted-detail">* Rekomendasi awal berbasis aturan; keputusan teknis dan jadwal perlu dikonfirmasi dengan JR Audio.</p></div>
    <div class="smart-demo" aria-label="Gambaran langkah Smart Recommendation"><div class="demo-top"><span><span class="spark-icon">✦</span> JR SMART SYSTEM</span><span>HOW IT WORKS</span></div><div class="demo-line"><span class="demo-num">01</span><div><b>Ceritakan acaramu</b><small>Jenis acara, lokasi, peserta, anggaran</small></div><span>↗</span></div><div class="demo-line"><span class="demo-num">02</span><div><b>Dapatkan rekomendasi</b><small>Daftar barang + jumlah dan alasan</small></div><span>↗</span></div><div class="demo-line"><span class="demo-num">03</span><div><b>Konsultasi lebih lanjut</b><small>Konfirmasi ketersediaan dan kebutuhan</small></div><span>↗</span></div><div class="demo-bottom"><span class="live-dot"></span> SMART RECOMMENDATION IS READY <strong>✦</strong></div></div>
  </div>
</section>
<section class="section section-ivory" aria-labelledby="featured-heading"><div class="container">
  <div class="section-heading"><div><span class="eyebrow">OUR EQUIPMENT</span><h2 id="featured-heading">Perlengkapan <span>unggulan.</span></h2><p>Jelajahi beberapa alat yang terdaftar di katalog JR Audio.</p></div><a class="arrow-link" href="{{ url('/barang') }}">Lihat semua katalog <span aria-hidden="true">↗</span></a></div>
  @if(!empty($barang))
  <div class="equipment-grid">
    @foreach(array_slice($barang, 0, 4) as $item)
      @php $itemImage = !empty($item['gambar']) ? asset('assets/img/barang/'.basename($item['gambar'])) : null; @endphp
      <article class="equipment-card"><a class="equipment-image" href="{{ url('/barang') }}" aria-label="Lihat katalog {{ $item['nama_barang'] }}">@if($itemImage)<img src="{{ $itemImage }}" alt="{{ $item['nama_barang'] }}" loading="lazy">@else<span class="item-fallback">♫</span>@endif <span class="image-arrow" aria-hidden="true">↗</span></a><div class="equipment-body"><span class="product-category">{{ $item['kategori'] }}</span><h3>{{ $item['nama_barang'] }}</h3><div class="product-bottom"><span>Mulai dari<br><strong>Rp {{ number_format((int)$item['harga_sewa'], 0, ',', '.') }}</strong></span><span class="stock-indicator">{{ number_format((int)$item['stok'], 0, ',', '.') }} tersedia*</span></div></div></article>
    @endforeach
  </div>
  <p class="tiny-caption">* Stok ditampilkan berdasarkan catatan saat ini; konfirmasi ketersediaan sesuai tanggal sewa kepada JR Audio.</p>
  @else <div class="empty-state">Katalog sedang diperbarui. <a href="{{ url('/kontak') }}">Hubungi kami</a> untuk informasi barang.</div> @endif
</div></section>
<section class="section" aria-labelledby="package-heading"><div class="container">
  <div class="section-heading"><div><span class="eyebrow">READY-TO-RENT</span><h2 id="package-heading">Paket untuk <span>berbagai acara.</span></h2><p>Ingin lebih praktis? Konsultasikan paket perlengkapan sesuai kebutuhan acara.</p></div><a class="arrow-link" href="{{ url('/paket') }}">Lihat detail paket <span aria-hidden="true">↗</span></a></div>
  <div class="package-preview-grid"><a href="{{ url('/paket') }}" class="package-preview package-a"><img src="{{ asset('assets/img/paket/1.png') }}" alt="Ilustrasi paket audio 2500 watt" loading="lazy"><div><span>01 / PACKAGE</span><h3>Paket 2500 Watt</h3><p>Untuk konsultasi kebutuhan acara indoor dan kegiatan skala kecil–menengah.</p><b>Explore package →</b></div></a><a href="{{ url('/paket') }}" class="package-preview package-b"><img src="{{ asset('assets/img/paket/2.png') }}" alt="Ilustrasi paket audio 5000 watt" loading="lazy"><div><span>02 / PACKAGE</span><h3>Paket 5000 Watt</h3><p>Untuk konsultasi acara outdoor, pertunjukan, dan kebutuhan yang lebih besar.</p><b>Explore package →</b></div></a></div>
</div></section>
<section class="portfolio-band" aria-labelledby="portfolio-heading"><div class="container"><div class="section-heading light"><div><span class="eyebrow eyebrow-light">BEHIND THE SOUND</span><h2 id="portfolio-heading">Bukan cuma alat.<br><span>Ini tentang momen.</span></h2><p>Intip dokumentasi kegiatan dan perlengkapan dari JR Audio.</p></div><a href="{{ url('/galeri') }}" class="arrow-link light-link">Lihat galeri <span aria-hidden="true">↗</span></a></div><div class="portfolio-gallery"><div class="portfolio-item p-wide"><img src="{{ asset('assets/img/galeri/event1.jpeg') }}" alt="Penataan peralatan sound system JR Audio" loading="lazy"><span>Setup & Equipment</span></div><div class="portfolio-item"><img src="{{ asset('assets/img/galeri/event2.jpeg') }}" alt="Dokumentasi acara dan peralatan audio" loading="lazy"><span>Event Production</span></div><div class="portfolio-item"><img src="{{ asset('assets/img/galeri/event4.jpg') }}" alt="Dokumentasi kegiatan malam" loading="lazy"><span>Live Moments</span></div></div></div></section>
</main>
@include('public.footer')
