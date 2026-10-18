@include('public.header')
<main id="main-content"><section class="page-hero"><div class="container"><span class="eyebrow eyebrow-light">OUR CONNECTIONS</span><h1>Kolaborasi yang<br><em>membuat acara hidup.</em></h1><p>Beberapa pihak dan rekan produksi yang tercantum dalam dokumentasi JR Audio.</p></div></section>
<section class="section section-ivory"><div class="container"><div class="section-heading"><div><span class="eyebrow">COLLABORATION</span><h2>Bertemu dalam <span>berbagai acara.</span></h2></div></div><div class="partners-grid">
@foreach([['karawitan.jpg','Karawitan Sadhar','Kolaborasi acara makrab'],['komsospepak.jpg','Gereja Klepu','Dukungan kegiatan dan acara keagamaan'],['unisa.png','Kampus UNISA','Dukungan acara kampus']] as $partner)
<div class="partner-tile"><div class="partner-image"><img src="{{ asset('assets/img/partner/'.$partner[0]) }}" alt="{{ $partner[1] }}" loading="lazy"></div><h3>{{ $partner[1] }}</h3><p>{{ $partner[2] }}</p></div>
@endforeach
</div></div></section>
<section class="section"><div class="container"><div class="section-heading"><div><span class="eyebrow">VENDOR & PRODUCTION</span><h2>Rekan <span>produksi.</span></h2></div></div><div class="partners-grid">
@foreach([['assetpro.jpg','Asset Production','Produksi event'],['hmaudio.jpg','HM Audio','Peralatan dan layanan audio'],['kholil.jpg','Kholil Audio','Layanan audio'],['elan.jpg','Elana Audio','Layanan audio'],['lakitenda.jpg','LaKi Tenda','Vendor tenda']] as $partner)
<div class="partner-tile"><div class="partner-image"><img src="{{ asset('assets/img/partner/'.$partner[0]) }}" alt="{{ $partner[1] }}" loading="lazy"></div><h3>{{ $partner[1] }}</h3><p>{{ $partner[2] }}</p></div>
@endforeach
</div></div></section></main>@include('public.footer')
