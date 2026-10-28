@include('public.header')
<main id="main-content">
  <section class="page-hero smart-page-hero">
    <div class="container">
      <span class="eyebrow eyebrow-light">✦ &nbsp; REKOMENDASI JR AUDIO</span>
      <h1>Temukan sound system<br><em>untuk acaramu.</em></h1>
      <p>Ceritakan kebutuhan acaramu, lalu lihat pilihan perlengkapan dan perkiraan biaya yang sesuai.</p>
    </div>
  </section>
  <section class="section section-ivory smart-workspace"><div class="container smart-layout">
    <aside class="smart-sidebar">
      <span class="eyebrow">MUDAH & PRAKTIS</span>
      <h2>Rencanakan acara,<br><span>lebih mudah.</span></h2>
      <div class="smart-steps">
        <div><b>01</b><span>Isi informasi dan kebutuhan acaramu</span></div>
        <div><b>02</b><span>Dapatkan saran paket dan perlengkapan</span></div>
        <div><b>03</b><span>Konsultasikan pilihanmu dengan tim JR Audio</span></div>
      </div>
      <div class="smart-notice"><strong>Belum tahu kebutuhan alatnya?</strong>
        <p>Tidak masalah. Isi informasi yang kamu ketahui, lalu tim JR Audio akan membantu memastikan detail perlengkapannya.</p>
      </div>
    </aside>
    <div class="smart-panel">
      <div class="smart-panel-heading"><span>DETAIL ACARA</span><span class="form-step">Isi sesuai rencana acaramu</span></div>
      @if($errors->any())<div class="form-errors" role="alert"><strong>Ada informasi yang perlu diperbaiki:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
      <form action="{{ route('rekomendasi.proses') }}" method="POST" class="smart-form">@csrf
        <h3 class="smart-fieldset-title">1. Acaranya seperti apa?</h3>
        <div class="smart-grid">
          <label>Jenis acara
            <select name="jenis_acara" required>
              @foreach(['pernikahan'=>'Pernikahan','acara_kampus'=>'Acara kampus / Makrab','live_music'=>'Live music','seminar'=>'Seminar / Meeting','konser'=>'Konser / Pertunjukan','sekolah'=>'Acara sekolah','lainnya'=>'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('jenis_acara','acara_kampus') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </label>
          <label>Perkiraan jumlah peserta
            <input name="jumlah_peserta" type="number" min="1" max="20000" required value="{{ old('jumlah_peserta','') }}" placeholder="Contoh: 200">
          </label>
          <label>Kegiatan utama
            <select name="kegiatan" required>
              @foreach(['pidato'=>'Pidato / MC','musik_live'=>'Musik live / Band','musik_rekaman'=>'Pemutaran musik','umum'=>'Campuran / Umum'] as $value => $label)
                <option value="{{ $value }}" @selected(old('kegiatan','umum') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </label>
          <label>Durasi acara (jam)
            <input name="durasi_jam" type="number" min="1" max="96" required value="{{ old('durasi_jam',12) }}">
          </label>
          <label>Tanggal acara
            <input name="tanggal_acara" type="date" min="{{ date('Y-m-d') }}" required value="{{ old('tanggal_acara','') }}">
          </label>
          <label>Nama tempat acara <small>(opsional)</small>
            <input name="lokasi_acara" type="text" maxlength="180" value="{{ old('lokasi_acara','') }}" placeholder="Contoh: Gedung Mrican">
          </label>
        </div>
        <h3 class="smart-fieldset-title">2. Bagaimana kondisi tempatnya?</h3>
        <div class="smart-grid">
          <label>Jenis area
            <select name="lokasi" required><option value="indoor" @selected(old('lokasi','indoor') === 'indoor')>Indoor / Dalam ruangan</option><option value="outdoor" @selected(old('lokasi') === 'outdoor')>Outdoor / Terbuka</option></select>
          </label>
          <label>Kondisi suara ruangan
            <select name="kondisi_ruangan" required><option value="belum_tahu" @selected(old('kondisi_ruangan','belum_tahu') === 'belum_tahu')>Belum tahu</option><option value="normal" @selected(old('kondisi_ruangan') === 'normal')>Normal</option><option value="bergema" @selected(old('kondisi_ruangan') === 'bergema')>Bergema</option></select>
          </label>
          <label>Panjang lokasi <small>(meter, opsional)</small>
            <input name="luas_panjang" type="number" min="1" max="5000" step="0.1" value="{{ old('luas_panjang','') }}" placeholder="Contoh: 20">
          </label>
          <label>Lebar lokasi <small>(meter, opsional)</small>
            <input name="luas_lebar" type="number" min="1" max="5000" step="0.1" value="{{ old('luas_lebar','') }}" placeholder="Contoh: 30">
          </label>
          <label>Sumber listrik
            <select name="kondisi_listrik" id="jrKondisiListrik" required><option value="belum_tahu" @selected(old('kondisi_listrik','belum_tahu') === 'belum_tahu')>Belum tahu</option><option value="cukup" @selected(old('kondisi_listrik') === 'cukup')>Diperkirakan cukup</option><option value="tidak_cukup" @selected(old('kondisi_listrik') === 'tidak_cukup')>Daya kurang / tidak cukup</option></select>
          </label>
          <label>Ingin suara bass yang kuat?
            <select name="butuh_bass" required><option value="belum_tahu" @selected(old('butuh_bass','belum_tahu') === 'belum_tahu')>Belum tahu</option><option value="ya" @selected(old('butuh_bass') === 'ya')>Ya</option><option value="tidak" @selected(old('butuh_bass') === 'tidak')>Tidak, fokus vokal</option></select>
          </label>
        </div>
        <h3 class="smart-fieldset-title">3. Perlengkapan dan anggaran</h3>
        <p class="smart-section-intro">Jika belum tahu jumlah alat yang dibutuhkan, biarkan angka 0. Tim kami akan membantu menyesuaikannya.</p>
        <div class="smart-grid">
          <label>Jumlah mikrofon wireless yang dibutuhkan
            <input name="jumlah_mic_wireless" type="number" min="0" max="100" value="{{ old('jumlah_mic_wireless',0) }}">
            <small class="smart-help">Isi 0 jika belum tahu.</small>
          </label>
          <label>Jumlah monitor panggung yang dibutuhkan
            <input name="jumlah_monitor" type="number" min="0" max="20" value="{{ old('jumlah_monitor',0) }}">
            <small class="smart-help">Isi 0 jika belum tahu.</small>
          </label>
          <label>Jumlah mikrofon kabel
            <input name="jumlah_mic_kabel" type="number" min="0" max="100" value="{{ old('jumlah_mic_kabel',0) }}">
            <small class="smart-help">Isi 0 jika tidak perlu atau belum tahu.</small>
          </label>
          <label>Perkiraan anggaran (Rp)
            <div class="currency-control"><span>Rp</span><input type="text" inputmode="numeric" autocomplete="off" data-currency-display aria-label="Anggaran dalam rupiah" value="{{ old('anggaran') !== null ? number_format((int) old('anggaran'),0,',','.') : '' }}" placeholder="Contoh: 1.000.000"><input type="hidden" name="anggaran" data-currency-value value="{{ (int) old('anggaran',0) }}"></div>
            <small class="smart-help">Isi jika sudah menentukan anggaran.</small>
          </label>
        </div>
        <label class="smart-rundown">Rundown / kebutuhan lain <small>(opsional)</small>
          <textarea name="rundown" rows="3" maxlength="1500" placeholder="Contoh: MC pembukaan, sambutan, band 5 orang, penutupan">{{ old('rundown') }}</textarea>
          <small>Membantu kami memahami urutan kegiatanmu.</small>
        </label>
        <label class="smart-check"><input type="checkbox" name="butuh_genset" id="jrButuhGenset" value="1" @checked(old('butuh_genset'))><span>Saya membutuhkan bantuan penyediaan genset <small>Biaya awal Rp400.000, dapat disesuaikan saat konfirmasi.</small></span></label>
        <label class="smart-check"><input type="checkbox" name="perlu_peralatan_custom" value="1" @checked(old('perlu_peralatan_custom'))><span>Saya memiliki kebutuhan peralatan khusus <small>Ceritakan kebutuhannya pada kolom rundown di atas.</small></span></label>
        <div class="smart-form-bottom"><p>Perkiraan harga dan ketersediaan akan dikonfirmasi sebelum pemesanan.</p><button class="button button-red" type="submit">Lihat Rekomendasi <span aria-hidden="true">↗</span></button></div>
      </form>
    </div>
  </div></section>
</main>
<script>
// Daya kurang selalu memerlukan genset; checkbox menampilkan keputusan tersebut.
document.addEventListener('DOMContentLoaded', () => {
  const listrik = document.getElementById('jrKondisiListrik');
  const genset = document.getElementById('jrButuhGenset');
  if (!listrik || !genset) return;
  const sync = () => {
    const wajib = listrik.value === 'tidak_cukup';
    if (wajib) { genset.checked = true; genset.disabled = true; }
    else { genset.disabled = false; if (genset.dataset.jrForced === 'yes') genset.checked = false; }
    genset.dataset.jrForced = wajib ? 'yes' : 'no';
  };
  listrik.addEventListener('change', sync);
  sync();
});
</script>
@include('public.footer')
