@include('layouts.header')
<section class="jr-pengajuan-page">
  <div class="jr-pengajuan-title"><div><span class="jr-overline">TINJAU PENGAJUAN</span><h1>{{ $pengajuan->kode }}</h1><p>{{ $pengajuan->nama_pelanggan }} · {{ $pengajuan->no_hp }} · Status: <span class="jr-status jr-status-{{ strtolower($pengajuan->status) }}">{{ $pengajuan->status }}</span></p></div>
    </div>
  <div class="jr-pengajuan-panel">
    <h2>Detail Acara</h2>
    @if($pelangganTertaut)
      <p class="jr-form-help">Pelanggan tersimpan: <strong>{{ $pelangganTertaut->nama_pelanggan }}</strong> (ID #{{ $pelangganTertaut->id_pelanggan }}). Nomor WhatsApp digunakan untuk mencocokkan data yang sudah ada.</p>
      @if(strcasecmp(trim($pelangganTertaut->nama_pelanggan),trim($pengajuan->nama_pelanggan)) !== 0)
        <div class="alert alert-warning">Nama pada pengajuan berbeda dari nama pelanggan yang tersimpan untuk nomor ini. Verifikasi identitas pelanggan sebelum transaksi disetujui.</div>
      @endif
    @endif

    <div class="jr-pengajuan-info">
      <div><small>Jenis acara</small><strong>{{ \App\Support\PengajuanLabel::tampil($pengajuan->jenis_acara) }}</strong></div>
      <div><small>Tanggal yang diminta</small><strong>{{ $pengajuan->tanggal_acara }}</strong></div>
      <div><small>Lokasi</small><strong>{{ \App\Support\PengajuanLabel::tampil($pengajuan->lokasi_acara ?: ($kebutuhan['lokasi'] ?? null)) }}</strong></div>
      <div><small>Jumlah peserta</small><strong>{{ number_format((int)($kebutuhan['jumlah_peserta'] ?? 0),0,',','.') }} orang</strong></div>
      <div><small>Rekomendasi</small><strong>{{ match($pengajuan->rekomendasi_paket) {'paket2500'=>'Paket 2500','paket5000'=>'Paket 5000',default=>'Custom'} }}</strong></div>
      <div><small>Estimasi awal</small><strong>{{ $pengajuan->estimasi_min !== null ? 'Rp '.number_format((int)$pengajuan->estimasi_min,0,',','.').' – Rp '.number_format((int)$pengajuan->estimasi_max,0,',','.') : 'Dikonfirmasi crew' }}</strong></div>
      <div><small>Listrik</small><strong>{{ \App\Support\PengajuanLabel::tampil($kebutuhan['kondisi_listrik'] ?? null) }}</strong></div>
      <div><small>Kegiatan</small><strong>{{ \App\Support\PengajuanLabel::tampil($kebutuhan['kegiatan'] ?? null) }}</strong></div>
      <div><small>Kondisi ruangan</small><strong>{{ \App\Support\PengajuanLabel::tampil($kebutuhan['kondisi_ruangan'] ?? null) }}</strong></div>
      <div><small>Butuh bass?</small><strong>{{ \App\Support\PengajuanLabel::tampil($kebutuhan['butuh_bass'] ?? null) }}</strong></div>
      <div><small>Durasi</small><strong>{{ (int)($kebutuhan['durasi_jam'] ?? 0) }} jam</strong></div>
      <div><small>Anggaran pelanggan</small><strong>{{ !empty($kebutuhan['anggaran']) ? 'Rp '.number_format((int)$kebutuhan['anggaran'],0,',','.') : 'Belum ditentukan' }}</strong></div>
    </div>
    @if(!empty($kebutuhan['rundown']))<p><strong>Rundown:</strong> {{ $kebutuhan['rundown'] }}</p>@endif
    @if($pengajuan->id_transaksi)
      <div class="alert alert-success">Pengajuan ini sudah menjadi Transaksi <strong>#{{ $pengajuan->id_transaksi }}</strong>. <a href="{{ url('/admin/transaksi/detail') }}?id={{ $pengajuan->id_transaksi }}">Lihat Transaksi</a>.</div>
    @else
      <div class="alert alert-info">Sebelum menyetujui, pastikan tanggal, stok, jumlah perlengkapan, harga final dan sumber vendor. Jika pilihan paket atau daftar perlengkapan berubah, klik Simpan Revisi dahulu. Setelah itu buka Form Transaksi untuk konfirmasi akhir. Stok baru dialokasikan saat form transaksi resmi disimpan.</div>
    @endif
  </div>
  @if(in_array($pengajuan->status, ['Menunggu','Revisi']))
  <form method="POST" action="{{ route('pengajuan.admin.simpan',['kode'=>$pengajuan->kode]) }}" class="jr-pengajuan-panel" id="jrReviewForm">@csrf
    <h2>Konfirmasi Perlengkapan</h2>
    <p class="text-muted mb-3">Crew dapat memilih paket yang sesuai atau menggunakan satuan, kemudian menambah dan menyesuaikan perlengkapan sebelum transaksi dibuat.</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label" for="jrPilihanCrew">Jenis penyewaan yang disetujui crew</label>
        <select name="pilihan_crew" id="jrPilihanCrew" class="form-select" required>
          <option value="paket2500" @selected(old('pilihan_crew',$pilihanCrew)==='paket2500')>Paket 2500</option>
          <option value="paket5000" @selected(old('pilihan_crew',$pilihanCrew)==='paket5000')>Paket 5000</option>
          <option value="satuan" @selected(old('pilihan_crew',$pilihanCrew)==='satuan')>Satuan / Custom</option>
        </select>
        <small class="text-muted">Mengganti paket akan memuat ulang komposisi sesuai tabel paket_detail. Perlengkapan tambahan dapat dimasukkan sesudahnya.</small>
      </div>
      <div class="col-md-6 d-flex align-items-end"><div class="alert alert-light border w-100 mb-0" id="jrPaketHint">Perubahan perlengkapan perlu disimpan terlebih dahulu sebelum membuka form transaksi.</div></div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle" id="jrReviewItemsTable">
        <thead><tr><th style="min-width:150px">Sumber</th><th style="min-width:220px">Barang katalog / nama item</th><th style="min-width:90px">Jumlah</th><th style="min-width:180px">Nama mitra (jika External)</th><th>Aksi</th></tr></thead>
        <tbody id="jrReviewItems"></tbody>
      </table>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-3">
      <button class="btn btn-outline-primary" type="button" id="jrAddInternal">+ Tambah Barang</button>
      <button class="btn btn-outline-secondary" type="button" id="jrAddExternal">+ Item Mitra</button>
    </div>
    <div class="row g-3">
      
      
      <div class="col-md-6"><label class="form-label">Estimasi harga final total (Rp)</label><input type="number" min="1" max="1000000000" class="form-control" name="harga_final" value="{{ old('harga_final',$pengajuan->harga_final ?? $pengajuan->estimasi_min) }}"><small>Nilai final diperiksa lagi dalam Form Transaksi. Untuk satuan, harga dihitung per barang.</small></div>
      <div class="col-md-6"><label class="form-label">Biaya genset (Rp)</label><input type="number" min="0" max="1000000000" class="form-control" name="biaya_genset" value="{{ old('biaya_genset',$pengajuan->biaya_genset) }}"></div>
      <div class="col-12"><label class="form-label">Catatan crew</label><textarea name="catatan_admin" class="form-control" rows="3" maxlength="2000" placeholder="Keterangan revisi atau kesepakatan">{{ old('catatan_admin',$pengajuan->catatan_admin) }}</textarea></div>
    </div>
    @if($pengajuan->rekomendasi_paket === 'custom' && !$pengajuan->pilihan_crew)
      <div class="alert alert-warning mt-3">Pengajuan Custom belum memiliki pilihan akhir crew. Pilih Paket 2500, Paket 5000, atau Satuan, lalu <strong>Simpan Revisi</strong> sebelum melanjutkan.</div>
    @endif
    <div id="jrDirtyMessage" class="text-warning small mt-3" hidden>Ada perubahan yang belum disimpan. Klik Simpan Revisi agar pilihan paket dan barang terbawa ke Form Transaksi.</div>
    <div class="jr-pengajuan-actions d-flex flex-wrap gap-2 mt-3">
      <button class="btn btn-primary" name="aksi" value="revisi" type="submit">Simpan Revisi</button>
      <a class="btn btn-success {{ $pengajuan->rekomendasi_paket === 'custom' && !$pengajuan->pilihan_crew ? 'disabled' : '' }}" @if($pengajuan->rekomendasi_paket === 'custom' && !$pengajuan->pilihan_crew) aria-disabled="true" tabindex="-1" @endif id="jrLanjutTransaksi" href="{{ url('/admin/transaksi/tambah') }}?pengajuan={{ urlencode($pengajuan->kode) }}">Lanjut ke Form Transaksi</a>
    </div>
  </form>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const inventory = @json($inventoryOptions);
    const presets = @json($presetPaket);
    const initial = @json($formItemsClient);
    const target = document.getElementById('jrReviewItems');
    const choice = document.getElementById('jrPilihanCrew');
    const next = document.getElementById('jrLanjutTransaksi');
    const dirtyLabel = document.getElementById('jrDirtyMessage');
    let dirty = false;
    function markDirty() {
      dirty = true;
      next.classList.add('disabled'); next.setAttribute('aria-disabled','true');
      next.addEventListener('click', preventDirty); dirtyLabel.hidden = false;
    }
    function preventDirty(e) { if(dirty) e.preventDefault(); }
    function field(type, name, value, options={}) {
      const el = document.createElement(type); el.className = type === 'select' ? 'form-select' : 'form-control';
      el.name = name; if(type==='input') {el.type=options.type || 'text'; el.value=value ?? ''; if(options.min !== undefined)el.min=options.min; el.required=true;}
      if(type==='select') options.choices.forEach(([v,t]) => el.add(new Option(t,v)));
      if(type==='select') el.value=String(value ?? '');
      if(options.placeholder) el.placeholder = options.placeholder;
      return el;
    }
    function addRow(data={}) {
      const idx=target.children.length; const tr=document.createElement('tr');
      const td=(child)=>{const col=document.createElement('td');col.appendChild(child);tr.appendChild(col);return col;};
      const source=field('select',`form_items[${idx}][tipe_item]`, data.tipe_item || 'Barang', {choices:[['Barang','Internal'],['External','Mitra / External']]});
      const col=td(source);
      const product=field('select',`form_items[${idx}][id_barang]`, data.id_barang || '', {choices:[['','-- Pilih barang --'],...inventory.map(b=>[String(b.id_barang), `${b.nama_barang} (stok ${b.stok})`])]});
      const name=field('input',`form_items[${idx}][nama_barang]`,data.nama_barang || '',{placeholder:'Nama item / perlengkapan'});
      const cell=td(product);cell.appendChild(name); name.classList.add('mt-1');
      const qty=field('input',`form_items[${idx}][qty]`,data.qty || 1,{type:'number',min:'1'});qty.max=10000;td(qty);
      const vendor=field('input',`form_items[${idx}][sumber_external]`,data.sumber_external || '', {placeholder:'Nama vendor (jika dari mitra)'});td(vendor);
      const remove=document.createElement('button'); remove.type='button';remove.className='btn btn-outline-danger btn-sm';remove.textContent='Hapus';remove.onclick=()=>{tr.remove(); renumber();markDirty();};td(remove);
      function sync() {
        const external=source.value==='External';product.hidden=external;name.hidden=!external;vendor.disabled=!external;
        product.required=!external;name.required=external;
        if(!external){const b=inventory.find(b=>String(b.id_barang)===product.value); name.value=b?.nama_barang || '';}
      }
      source.addEventListener('change',()=>{sync();markDirty();});
      product.addEventListener('change',()=>{sync();markDirty();});
      tr.addEventListener('input',markDirty);
      target.appendChild(tr);sync();return tr;
    }
    function renumber() {
      Array.from(target.children).forEach((tr,i)=>tr.querySelectorAll('[name]').forEach(el=>el.name=el.name.replace(/form_items\[\d+\]/,`form_items[${i}]`)));
    }
    function render(rows) { target.replaceChildren(); rows.forEach(row=>addRow(row)); renumber(); }
    render(Array.isArray(initial) ? initial : Object.values(initial || {}));
    if(!target.children.length) addRow({tipe_item:'Barang'});
    choice.addEventListener('change',()=>{
      const preset=presets[choice.value];
      if(preset?.length) {
        if(!window.confirm('Muat komposisi paket ini? Rincian barang yang belum disimpan akan diganti.')) {choice.value=choice.dataset.previous || 'satuan';return;}
        render(preset);
      }
      choice.dataset.previous=choice.value;markDirty();
    });
    choice.dataset.previous=choice.value;
    document.getElementById('jrAddInternal').onclick=()=>{addRow({tipe_item:'Barang',qty:1});renumber();markDirty();};
    document.getElementById('jrAddExternal').onclick=()=>{addRow({tipe_item:'External',qty:1});renumber();markDirty();};
    document.querySelectorAll('#jrReviewForm input, #jrReviewForm textarea').forEach(el=>el.addEventListener('input',markDirty));
    document.getElementById('jrReviewForm').addEventListener('submit',e=>{
      if (!target.children.length) { e.preventDefault(); alert('Minimal satu perlengkapan diperlukan.'); return; }
      renumber();
    });
  });
  </script>
  <form method="POST" action="{{ route('pengajuan.admin.batal',['kode'=>$pengajuan->kode]) }}" class="jr-pengajuan-panel" data-jr-confirm="Batalkan pengajuan ini? Stok tidak berubah.">@csrf
    <label class="form-label">Alasan pembatalan (opsional)</label><textarea class="form-control" name="catatan_admin" rows="2" maxlength="2000"></textarea>
    <button class="btn btn-outline-danger mt-3" >Batalkan Pengajuan</button>
  </form>
  @endif
  <div class="jr-form-actions mt-3"><a href="{{ route('pengajuan.admin.index') }}" class="btn btn-outline-secondary">Kembali</a></div>
</section>
@include('layouts.footer')
