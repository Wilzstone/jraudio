@include('layouts.header')
<section class="jr-pengajuan-page">
  <div class="jr-pengajuan-title"><div><span class="jr-overline">TINJAU PENGAJUAN</span><h1>{{ $pengajuan->kode }}</h1><p>{{ $pengajuan->nama_pelanggan }} · {{ $pengajuan->no_hp }} · Status: <strong>{{ $pengajuan->status }}</strong></p></div>
    <a class="btn btn-outline-secondary" href="{{ route('pengajuan.admin.index') }}">← Semua Pengajuan</a></div>
  <div class="jr-pengajuan-panel">
    <h2>Detail Acara</h2>
    @if($pelangganTertaut)
      <p class="jr-form-help">Pelanggan tersimpan: <strong>{{ $pelangganTertaut->nama_pelanggan }}</strong> (ID #{{ $pelangganTertaut->id_pelanggan }}). Nomor WhatsApp digunakan untuk mencocokkan data yang sudah ada.</p>
      @if(strcasecmp(trim($pelangganTertaut->nama_pelanggan),trim($pengajuan->nama_pelanggan)) !== 0)
        <div class="alert alert-warning">Nama pada pengajuan berbeda dari nama pelanggan yang tersimpan untuk nomor ini. Verifikasi identitas pelanggan sebelum transaksi disetujui.</div>
      @endif
    @endif

    <div class="jr-pengajuan-info">
      <div><small>Jenis acara</small><strong>{{ ucwords(str_replace('_',' ',$pengajuan->jenis_acara)) }}</strong></div>
      <div><small>Tanggal yang diminta</small><strong>{{ $pengajuan->tanggal_acara }}</strong></div>
      <div><small>Lokasi</small><strong>{{ $pengajuan->lokasi_acara ?: ($kebutuhan['lokasi'] ?? 'Belum diisi') }}</strong></div>
      <div><small>Jumlah peserta</small><strong>{{ number_format((int)($kebutuhan['jumlah_peserta'] ?? 0),0,',','.') }} orang</strong></div>
      <div><small>Rekomendasi</small><strong>{{ match($pengajuan->rekomendasi_paket) {'paket2500'=>'Paket 2500','paket5000'=>'Paket 5000',default=>'Custom'} }}</strong></div>
      <div><small>Estimasi awal</small><strong>{{ $pengajuan->estimasi_min !== null ? 'Rp '.number_format((int)$pengajuan->estimasi_min,0,',','.').' – Rp '.number_format((int)$pengajuan->estimasi_max,0,',','.') : 'Dikonfirmasi crew' }}</strong></div>
      <div><small>Listrik</small><strong>{{ str_replace('_',' ',$kebutuhan['kondisi_listrik'] ?? '-') }}</strong></div>
      <div><small>Kegiatan</small><strong>{{ str_replace('_',' ',$kebutuhan['kegiatan'] ?? '-') }}</strong></div>
      <div><small>Kondisi ruangan</small><strong>{{ str_replace('_',' ',$kebutuhan['kondisi_ruangan'] ?? '-') }}</strong></div>
      <div><small>Butuh bass?</small><strong>{{ $kebutuhan['butuh_bass'] ?? '-' }}</strong></div>
      <div><small>Durasi</small><strong>{{ (int)($kebutuhan['durasi_jam'] ?? 0) }} jam</strong></div>
      <div><small>Anggaran pelanggan</small><strong>{{ !empty($kebutuhan['anggaran']) ? 'Rp '.number_format((int)$kebutuhan['anggaran'],0,',','.') : 'Belum ditentukan' }}</strong></div>
    </div>
    @if(!empty($kebutuhan['rundown']))<p><strong>Rundown:</strong> {{ $kebutuhan['rundown'] }}</p>@endif
    @if($pengajuan->id_transaksi)
      <div class="alert alert-success">Pengajuan ini sudah menjadi Transaksi <strong>#{{ $pengajuan->id_transaksi }}</strong>. <a href="{{ url('/admin/transaksi/detail') }}?id={{ $pengajuan->id_transaksi }}">Lihat Transaksi</a>.</div>
    @else
      <div class="alert alert-info">Sebelum menyetujui, pastikan tanggal, stok, jumlah perlengkapan, harga final dan sumber vendor. Jika daftar perlengkapan berubah di halaman ini, tekan Simpan Revisi dahulu. Setelah itu buka Form Transaksi untuk konfirmasi akhir. Stok baru dialokasikan saat form transaksi resmi disimpan.</div>
    @endif
  </div>
  @if(in_array($pengajuan->status, ['Menunggu','Revisi']))
  <form method="POST" action="{{ route('pengajuan.admin.simpan',['kode'=>$pengajuan->kode]) }}" class="jr-pengajuan-panel">@csrf
    <h2>Konfirmasi Perlengkapan</h2>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="table-responsive"><table class="table align-middle">
      <thead><tr><th>Perlengkapan</th><th>Sumber</th><th>Barang katalog</th><th>Qty</th><th>Mitra (untuk External)</th></tr></thead>
      <tbody>
      @foreach($detail as $row)
        @php $field = 'items['.$row->id_pengajuan_detail.']'; @endphp
        <tr>
          <td><input class="form-control" name="{{ $field }}[nama_barang]" maxlength="150" value="{{ old('items.'.$row->id_pengajuan_detail.'.nama_barang', $row->nama_barang) }}" required><small>{{ $row->peran }}</small></td>
          <td><select class="form-select" name="{{ $field }}[tipe_item]">
             <option value="Barang" @selected(old('items.'.$row->id_pengajuan_detail.'.tipe_item',$row->tipe_item)==='Barang')>Internal</option>
             <option value="External" @selected(old('items.'.$row->id_pengajuan_detail.'.tipe_item',$row->tipe_item)==='External')>Mitra / External</option>
          </select></td>
          <td><select class="form-select" name="{{ $field }}[id_barang]"><option value="">— Tidak dipilih —</option>
            @foreach($barang as $b)<option value="{{ $b->id_barang }}" @selected((string)old('items.'.$row->id_pengajuan_detail.'.id_barang',$row->id_barang)===(string)$b->id_barang)>{{ $b->nama_barang }} (stok {{ $b->stok }})</option>@endforeach
          </select></td>
          <td><input class="form-control" type="number" min="1" max="10000" name="{{ $field }}[qty]" value="{{ old('items.'.$row->id_pengajuan_detail.'.qty',$row->qty) }}" required></td>
          <td><input class="form-control" name="{{ $field }}[sumber_external]" maxlength="150" placeholder="Nama mitra / vendor" value="{{ old('items.'.$row->id_pengajuan_detail.'.sumber_external',$row->sumber_external) }}"></td>
        </tr>
      @endforeach
      </tbody>
      <tbody id="jrNewItemsTarget"></tbody>
    </table></div>
    <button class="btn btn-outline-primary" type="button" id="jrAddItem">+ Tambah Perlengkapan</button>
    <template id="jrNewItemTemplate"><tr>
      <td><input required class="form-control" name="items_baru[__INDEX__][nama_barang]" maxlength="150" placeholder="Nama perlengkapan"></td>
      <td><select class="form-select" name="items_baru[__INDEX__][tipe_item]"><option value="Barang">Internal</option><option value="External">Mitra / External</option></select></td>
      <td><select class="form-select" name="items_baru[__INDEX__][id_barang]"><option value="">— Tidak dipilih —</option>
         @foreach($barang as $b)<option value="{{ $b->id_barang }}">{{ $b->nama_barang }} (stok {{ $b->stok }})</option>@endforeach
      </select></td>
      <td><input class="form-control" name="items_baru[__INDEX__][qty]" type="number" min="1" value="1" required></td>
      <td><input class="form-control" name="items_baru[__INDEX__][sumber_external]" placeholder="Nama mitra"></td>
    </tr></template>
    <script>
      document.addEventListener('DOMContentLoaded',()=>{
        let i=0;
        document.getElementById('jrAddItem')?.addEventListener('click',()=>{
          const html=document.getElementById('jrNewItemTemplate').innerHTML.replaceAll('__INDEX__', String(i++));
          document.getElementById('jrNewItemsTarget').insertAdjacentHTML('beforeend',html);
        });
      });
    </script>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Tanggal sewa</label><input type="date" class="form-control" name="tanggal_sewa" min="{{ date('Y-m-d') }}" value="{{ old('tanggal_sewa',max($pengajuan->tanggal_acara,date('Y-m-d'))) }}"></div>
      <div class="col-md-6"><label class="form-label">Tanggal kembali (maksimal H+7)</label><input type="date" class="form-control" name="tanggal_kembali" value="{{ old('tanggal_kembali',max($pengajuan->tanggal_acara,date('Y-m-d'))) }}"></div>
      <div class="col-md-6"><label class="form-label">Harga final total (Rp)</label><input type="number" min="1" max="1000000000" class="form-control" name="harga_final" value="{{ old('harga_final',$pengajuan->harga_final ?? $pengajuan->estimasi_min) }}"><small>Termasuk biaya mitra, transportasi, dan genset jika ada.</small></div>
      <div class="col-md-6"><label class="form-label">Porsi biaya genset (Rp)</label><input type="number" min="0" max="1000000000" class="form-control" name="biaya_genset" value="{{ old('biaya_genset',$pengajuan->biaya_genset) }}"></div>
      <div class="col-12"><label class="form-label">Catatan crew</label><textarea name="catatan_admin" class="form-control" rows="3" maxlength="2000" placeholder="Keterangan revisi atau kesepakatan">{{ old('catatan_admin',$pengajuan->catatan_admin) }}</textarea></div>
    </div>
    <div class="jr-pengajuan-actions">
      <button class="btn btn-outline-secondary" name="aksi" value="revisi" type="submit">Simpan Revisi</button>
      <a class="btn btn-success" href="{{ url('/admin/transaksi/tambah') }}?pengajuan={{ urlencode($pengajuan->kode) }}">Setujui → Isi Form Transaksi</a>
    </div>
  </form>
  <form method="POST" action="{{ route('pengajuan.admin.batal',['kode'=>$pengajuan->kode]) }}" class="jr-pengajuan-panel" data-jr-confirm="Batalkan pengajuan ini? Stok tidak berubah.">@csrf
    <label class="form-label">Alasan pembatalan (opsional)</label><textarea class="form-control" name="catatan_admin" rows="2" maxlength="2000"></textarea>
    <button class="btn btn-outline-danger mt-3" >Batalkan Pengajuan</button>
  </form>
  @endif
</section>
@include('layouts.footer')
