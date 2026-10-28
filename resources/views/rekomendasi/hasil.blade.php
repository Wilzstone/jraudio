@include('public.header')
<main id="main-content">
  <section class="page-hero smart-page-hero">
    <div class="container">
      <span class="eyebrow eyebrow-light">✦ &nbsp; REKOMENDASI JR AUDIO</span>
      <h1>Pilihan sound system<br><em>untuk acaramu.</em></h1>
      <p>Berikut rekomendasi awal berdasarkan kebutuhan yang kamu isi. Tim JR Audio siap membantu menyesuaikannya.</p>
    </div>
  </section>

  <section class="section section-ivory">
    <div class="container result-layout">
      <div class="result-main">
        <div class="result-overview">
          <span class="eyebrow">DETAIL ACARA</span>
          <div class="overview-tags">
            <span>Acara: <b>{{ ucwords(str_replace('_', ' ', $input['jenis_acara'])) }}</b></span>
            <span>Tamu: <b>{{ number_format((int) $input['jumlah_peserta'], 0, ',', '.') }} orang</b></span>
            <span>Lokasi: <b>{{ ucfirst($input['lokasi']) }}</b></span>
            <span>Tanggal: <b>{{ date('d/m/Y', strtotime($input['tanggal_acara'])) }}</b></span>
          </div>
        </div>

        <div class="result-auto-pick">
          <span class="eyebrow">REKOMENDASI UNTUKMU</span>
          <h2>{{ $hasil['pilihan_paket'] === 'custom' ? 'Paket Custom' : $hasil['paket_hint'] }}</h2>
          <p>{{ $hasil['alasan_pemilihan'] }}</p>
          <small>Pilihan perlengkapan dapat disesuaikan setelah konsultasi dengan tim JR Audio.</small>
        </div>

        <div class="result-heading">
          <div>
            <span class="eyebrow">RINCIAN REKOMENDASI</span>
            <h2>Perlengkapan acara</h2>
          </div>
          @if(count($hasil['barang']))
            <span class="result-count">{{ count($hasil['barang']) }} jenis perlengkapan</span>
          @endif
        </div>

        @if(count($hasil['barang']))
          <div class="result-table-wrap">
            <table class="result-table result-table-simple">
              <thead>
                <tr><th>Perlengkapan</th><th>Jumlah</th><th>Ketersediaan</th></tr>
              </thead>
              <tbody>
                @foreach($hasil['barang'] as $row)
                  <tr>
                    <td>
                      <strong>{{ $row['nama_barang'] }}</strong>
                      <small>{{ $row['peran'] }}</small>
                    </td>
                    <td><strong>{{ $row['jumlah'] }} unit</strong></td>
                    <td>
                      @if($row['jumlah_vendor'] > 0)
                        <span class="result-availability result-availability-check">Perlu konfirmasi</span>
                      @else
                        <span class="result-availability">Perkiraan tersedia</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="empty-state">
            Tim JR Audio akan membantu menyusun perlengkapan sesuai kebutuhan khusus acaramu.
            Silakan konsultasikan detail acara untuk mendapatkan penawaran.
          </div>
        @endif

        @php
          // Simpan seluruh informasi teknis untuk logika backend, tetapi tampilkan hanya
          // informasi yang diperlukan calon pelanggan.
          $catatanPelanggan = array_values(array_filter(
              array_unique($hasil['kebutuhan_crew'] ?? []),
              fn ($teks) => !str_starts_with($teks, 'Tim kami akan memastikan pilihan peralatan')
          ));
        @endphp
        @if(count($catatanPelanggan))
          <div class="result-notes">
            <span class="eyebrow">SEBELUM PEMESANAN</span>
            <h3>Hal yang perlu dikonfirmasi</h3>
            <ul>
              @foreach(array_slice($catatanPelanggan, 0, 3) as $pesan)
                <li>{{ $pesan }}</li>
              @endforeach
            </ul>
            @if(count($catatanPelanggan) > 3)
              <details class="rules-details">
                <summary>Lihat catatan lainnya</summary>
                <ul>
                  @foreach(array_slice($catatanPelanggan, 3) as $pesan)
                    <li>{{ $pesan }}</li>
                  @endforeach
                </ul>
              </details>
            @endif
          </div>
        @endif
      </div>

      <aside class="result-aside">
        <div class="estimate-card">
          <span>PERKIRAAN HARGA</span>
          @if($hasil['harga_paket'])
            <h3>Rp {{ number_format($hasil['harga_paket']['minimum'], 0, ',', '.') }} – Rp {{ number_format($hasil['harga_paket']['maksimum'], 0, ',', '.') }}</h3>
            <p>Perkiraan biaya {{ $hasil['paket_hint'] }}. Harga akhir menyesuaikan kebutuhan dan kesepakatan dengan JR Audio.</p>
          @else
            <h3 class="estimate-contact">Hubungi JR Audio</h3>
            <p>Untuk kebutuhan acara ini, harga akan diberikan setelah konsultasi.</p>
          @endif
          <div class="estimate-sep"></div>
          <div class="estimate-line">
            <span>Anggaranmu</span>
            <strong>{{ $hasil['anggaran'] > 0 ? 'Rp '.number_format($hasil['anggaran'], 0, ',', '.') : 'Belum ditentukan' }}</strong>
          </div>
          @php
            $pesanAnggaran = match($hasil['status_anggaran']) {
                'di_bawah_minimum' => 'Anggaran perlu disesuaikan. Yuk, konsultasikan.',
                'di_dalam_rentang' => 'Anggaranmu mendekati perkiraan harga.',
                'cukup_untuk_rentang' => 'Anggaranmu sesuai perkiraan harga paket.',
                'belum_diisi' => 'Kamu bisa mendiskusikan anggaran dengan tim kami.',
                default => 'Hubungi tim JR Audio untuk informasi harga.',
            };
          @endphp
          <span class="estimate-status {{ $hasil['status_anggaran'] === 'cukup_untuk_rentang' ? 'status-ok' : 'status-warn' }}">{{ $pesanAnggaran }}</span>
          <p class="estimate-disclaimer">Harga belum termasuk transportasi, genset, dan kebutuhan tambahan. Ketersediaan peralatan akan dikonfirmasi sebelum pemesanan.</p>

          <div class="jr-order-cta" id="ajukan-sewa">
            <h3 style="font-size:1.18rem;margin:0 0 8px">Konsultasikan acaramu</h3>
            <p style="font-size:.95rem">Isi kontakmu agar tim JR Audio bisa menemukan rincian yang sama saat kamu menghubungi kami.</p>
            @if($errors->any())
              <div role="alert" style="color:#b91c1c">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('pengajuan.public.simpan') }}" class="jr-order-contact-form">
              @csrf
              <input type="hidden" name="draft_token" value="{{ $draftToken }}">
              <label style="display:block;font-weight:700;margin-bottom:10px">Nama kamu
                <input required maxlength="100" name="nama_pelanggan" autocomplete="name" placeholder="Nama lengkap" value="{{ old('nama_pelanggan') }}" style="display:block;width:100%;padding:12px;margin-top:5px;border:1px solid #ccc;border-radius:8px">
              </label>
              <label style="display:block;font-weight:700;margin-bottom:10px">Nomor WhatsApp
                <input required name="no_hp" autocomplete="tel" inputmode="tel" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}" style="display:block;width:100%;padding:12px;margin-top:5px;border:1px solid #ccc;border-radius:8px">
              </label>
              <label style="display:flex;gap:8px;align-items:flex-start;font-size:.85rem;margin:8px 0 16px">
                <input required type="checkbox" name="setuju" value="1" style="margin-top:4px">
                <span>Saya setuju data kontak dan kebutuhan acara ini disimpan JR Audio untuk keperluan konsultasi penyewaan.</span>
              </label>
              <button type="submit" class="button button-red" style="width:100%">Simpan &amp; Lanjut ke WhatsApp ↗</button>
            </form>
            <a class="button button-outline" href="{{ route('rekomendasi.index') }}" style="display:block;text-align:center;margin-top:10px">← Ubah Detail Acara</a>
            <p class="estimate-disclaimer">Pengajuan konsultasi belum merupakan booking. Kami akan mengonfirmasi tanggal, perlengkapan, dan harga sebelum transaksi.</p>
          </div>
        </div>
      </aside>
    </div>
  </section>
</main>
@include('public.footer')
