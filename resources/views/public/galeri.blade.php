@include('public.header')
<main id="main-content"><section class="page-hero"><div class="container"><span class="eyebrow eyebrow-light">MOMENTS & EVENTS</span><h1>Setiap acara punya<br><em>ceritanya sendiri.</em></h1><p>Dokumentasi perlengkapan, kegiatan, dan momen dari JR Audio.</p></div></section>
<section class="section section-ivory"><div class="container"><div class="section-heading"><div><span class="eyebrow">EVENT GALLERY</span><h2>Di balik <span>setiap suara.</span></h2><p>Dokumentasi yang tersedia dari arsip JR Audio.</p></div></div><div class="full-gallery">
@foreach([['event1.jpeg','Setup Sound System','Persiapan perlengkapan acara'],['event2.jpeg','Equipment Setup','Penataan peralatan audio'],['event3.jpeg','Event Support','Perlengkapan untuk kegiatan'],['event4.jpg','Night Event','Sound system di malam hari']] as $photo)
<figure class="gallery-photo"><img src="{{ asset('assets/img/galeri/'.$photo[0]) }}" alt="{{ $photo[1] }} JR Audio" loading="lazy"><figcaption><strong>{{ $photo[1] }}</strong><span>{{ $photo[2] }}</span></figcaption></figure>
@endforeach
</div></div></section>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">VIDEO GALLERY</span><h2>Lihat cuplikan <span>acaranya.</span></h2></div></div><div class="video-grid"><div class="video-card"><video controls preload="metadata" playsinline><source src="{{ asset('assets/video/vid1.mp4') }}" type="video/mp4">Browser tidak mendukung video.</video><h3>Dokumentasi Event 01</h3></div><div class="video-card"><video controls preload="metadata" playsinline><source src="{{ asset('assets/video/vid2.mp4') }}" type="video/mp4">Browser tidak mendukung video.</video><h3>Dokumentasi Event 02</h3></div></div></div></section>
</main>@include('public.footer')
