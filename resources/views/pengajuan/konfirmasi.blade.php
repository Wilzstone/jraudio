@include('public.header')
<main id="main-content">
  <section class="page-hero smart-page-hero"><div class="container">
    <span class="eyebrow eyebrow-light">KONSULTASI JR AUDIO</span>
    <h1>Pengajuanmu <em>tersimpan.</em></h1>
    <p>Terima kasih! Lanjutkan percakapan dengan tim JR Audio melalui WhatsApp menggunakan kode pengajuan berikut.</p>
  </div></section>
  <section class="section section-ivory"><div class="container" style="max-width:870px;padding-top:30px;padding-bottom:50px">
    <div style="background:white;border-radius:16px;padding:clamp(18px,4vw,32px);box-shadow:0 8px 30px #0001">
      <span class="eyebrow">KODE PENGAJUAN</span>
      <h2 style="overflow-wrap:anywhere;margin:9px 0">{{ $pengajuan->kode }}</h2>
      <p style="color:#555">Simpan kode ini untuk memudahkan tim kami mencari rincian pengajuanmu.</p>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr));gap:14px;margin:18px 0">
        <div><small>Status</small><p><strong>{{ $pengajuan->status }}</strong></p></div>
        <div><small>Tanggal acara</small><p><strong>{{ date('d/m/Y',strtotime($pengajuan->tanggal_acara)) }}</strong></p></div>
        <div><small>Rekomendasi</small><p><strong>{{ match($pengajuan->rekomendasi_paket) {'paket2500'=>'Paket 2500','paket5000'=>'Paket 5000', default=>'Custom'} }}</strong></p></div>
      </div>
      <h3 style="font-size:1.15rem">Rincian perlengkapan</h3>
      <ul style="line-height:1.75;padding-left:20px">@foreach($detail as $row)<li>{{ $row->nama_barang }} — {{ $row->qty }} unit</li>@endforeach</ul>
      @if($pengajuan->catatan_admin)
        <div style="background:#fff6ed;padding:13px;border-radius:10px;margin:16px 0"><strong>Catatan crew:</strong> {{ $pengajuan->catatan_admin }}</div>
      @endif
      @if($pengajuan->status === 'Disetujui')
        <p>Pengajuan telah disetujui crew. Informasi transaksi dapat ditanyakan dengan menyertakan kode ini.</p>
      @elseif($pengajuan->status === 'Batal')
        <p>Pengajuan ini telah dibatalkan. Jika ingin mencoba lagi, buat rekomendasi baru.</p>
      @else
        @if($wa)
          <a class="button button-red" style="display:inline-block;margin-top:15px" href="{{ $wa }}" target="_blank" rel="noopener noreferrer">Buka WhatsApp JR Audio ↗</a>
        @else
          <p>Nomor WhatsApp belum dikonfigurasi. Hubungi tim kami melalui halaman Kontak sambil menyebutkan kode pengajuan.</p>
        @endif
      @endif
      <p style="font-size:.9rem;margin-top:18px;color:#666">@if($pengajuan->status === 'Disetujui') Pengajuan ini sudah disetujui dan tercatat sebagai transaksi Booking. @else Pengajuan tersimpan di sistem, tetapi pesan WhatsApp hanya terkirim jika kamu benar-benar menekan Kirim di WhatsApp. Pengajuan ini belum menjadi booking. @endif</p>
      <a class="button button-outline" href="{{ url('/') }}">Kembali ke Beranda</a>
    </div>
  </div></section>
</main>
@include('public.footer')
