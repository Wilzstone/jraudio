@include('public.header')
<main id="main-content">
<section class="page-hero"><div class="container"><span class="eyebrow eyebrow-light">OUR EQUIPMENT</span><h1>Temukan gear<br><em>untuk acaramu.</em></h1><p>Daftar perlengkapan JR Audio dengan estimasi harga sewa satuan. Hubungi kami untuk mengonfirmasi ketersediaan sesuai tanggal acara.</p></div></section>
<section class="section section-ivory"><div class="container">
 <div class="listing-toolbar"><div><span class="eyebrow">KATALOG JR AUDIO</span><h2>Daftar barang <small>({{ count($barang ?? []) }})</small></h2></div><div class="catalog-search"><label for="catalog-search">Cari barang</label><input id="catalog-search" type="search" placeholder="Cari nama atau kategori..." autocomplete="off"></div></div>
 @if(!empty($barang))
 <div class="equipment-grid equipment-grid-list" id="catalog-list">
 @foreach($barang as $item)
 @php
   $img = !empty($item['gambar']) ? asset('assets/img/barang/'.basename($item['gambar'])) : null;
   $waItem = 'https://wa.me/6282331745445?text='.rawurlencode('Halo JR Audio, saya ingin menanyakan ketersediaan '.$item['nama_barang'].' untuk tanggal acara saya.');
 @endphp
 <article class="equipment-card catalog-item" data-search="{{ strtolower($item['nama_barang'].' '.$item['kategori']) }}"><div class="equipment-image">@if($img)<img src="{{ $img }}" alt="{{ $item['nama_barang'] }}" loading="lazy">@else<span class="item-fallback">♫</span>@endif</div><div class="equipment-body"><span class="product-category">{{ $item['kategori'] }}</span><h3>{{ $item['nama_barang'] }}</h3><div class="product-meta">Stok tercatat: {{ number_format((int)$item['stok'],0,',','.') }} unit</div><div class="product-bottom"><span>Estimasi harga satuan<br><strong>Rp {{ number_format((int)$item['harga_sewa'],0,',','.') }}</strong></span></div><a class="catalog-order" href="{{ $waItem }}" target="_blank" rel="noopener noreferrer">Tanya via WhatsApp ↗</a></div></article>
 @endforeach
 </div>
 <p id="catalog-empty" class="empty-state" hidden>Tidak ada barang yang sesuai pencarian. Coba kata kunci lain.</p>
 @else <div class="empty-state">Belum ada barang yang ditampilkan. <a href="{{ url('/kontak') }}">Hubungi JR Audio</a> untuk menanyakan ketersediaan.</div>@endif
 <div class="inline-cta"><div><span class="eyebrow">BUTUH SARAN?</span><h3>Belum tahu alat mana yang tepat?</h3><p>Ceritakan kebutuhan acaramu, sistem akan membantu menyusun saran awal.</p></div><a class="button button-red" href="{{ route('rekomendasi.index') }}">✦ Smart Recommendation ↗</a></div>
 </div></section>
</main>
@include('public.footer')
