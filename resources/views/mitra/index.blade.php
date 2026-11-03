@include('layouts.header')
<div class="mb-3"><div class="jr-form-eyebrow">OPERASIONAL / JR AUDIO</div><h3>Manajemen Mitra</h3>
  <p class="text-muted">Daftar penyedia dan perlengkapan yang dapat dipilih di Pengajuan Sewa maupun Transaksi.</p></div>
@if(session('info'))<div class="alert alert-success">{{ session('info') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card jr-form-card mb-4"><div class="card-header">Tambah Mitra</div><div class="card-body">
<form method="POST" action="{{ route('mitra.simpan') }}" class="row g-3">@csrf
 <div class="col-md-4"><label class="form-label">Nama Mitra *</label><input name="nama_mitra" class="form-control" required maxlength="150"></div>
 <div class="col-md-3"><label class="form-label">Nama Kontak</label><input name="nama_kontak" class="form-control" maxlength="120"></div>
 <div class="col-md-3"><label class="form-label">Nomor WhatsApp</label><input name="no_hp" class="form-control" maxlength="30"></div>
 <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option>Aktif</option><option>Nonaktif</option></select></div>
 <div class="col-md-8"><label class="form-label">Alamat</label><input name="alamat" class="form-control"></div>
 <div class="col-md-4"><label class="form-label">Catatan</label><input name="catatan" class="form-control"></div>
 <div class="col-12"><button class="btn btn-primary">Simpan Mitra</button></div>
</form></div></div>

<div class="card jr-form-card mb-4"><div class="card-header">Daftar Mitra · {{ $mitra->count() }}</div><div class="card-body">
 @forelse($mitra as $m)
 <form method="POST" action="{{ route('mitra.update',['id'=>$m->id_mitra]) }}" class="border rounded p-3 mb-3">@csrf
 <div class="row g-2 align-items-end">
  <div class="col-lg-3"><label class="form-label">Nama Mitra</label><input name="nama_mitra" class="form-control" value="{{ $m->nama_mitra }}" required></div>
  <div class="col-lg-2"><label class="form-label">Kontak</label><input name="nama_kontak" class="form-control" value="{{ $m->nama_kontak }}"></div>
  <div class="col-lg-2"><label class="form-label">WhatsApp</label><input name="no_hp" class="form-control" value="{{ $m->no_hp }}"></div>
  <div class="col-lg-2"><label class="form-label">Status</label><select name="status" class="form-select"><option @selected($m->status==='Aktif')>Aktif</option><option @selected($m->status==='Nonaktif')>Nonaktif</option></select></div>
  <div class="col-lg-3"><button class="btn btn-outline-primary w-100">Update Mitra</button></div>
  <div class="col-lg-8"><label class="form-label">Alamat</label><input name="alamat" class="form-control" value="{{ $m->alamat }}"></div>
  <div class="col-lg-4"><label class="form-label">Catatan</label><input name="catatan" class="form-control" value="{{ $m->catatan }}"></div>
 </div></form>
 @empty<p class="text-muted">Belum ada mitra terdaftar.</p>@endforelse
</div></div>

<div class="card jr-form-card mb-4"><div class="card-header">Tambah Perlengkapan Mitra</div><div class="card-body"><form class="row g-3" method="POST" action="{{ route('mitra.barang.simpan') }}">@csrf
 <div class="col-md-4"><label class="form-label">Mitra *</label><select class="form-select" name="id_mitra" required><option value="">-- Pilih --</option>@foreach($mitra as $m) @if($m->status==='Aktif')<option value="{{ $m->id_mitra }}">{{ $m->nama_mitra }}</option>@endif @endforeach</select></div>
 <div class="col-md-4"><label class="form-label">Nama Barang *</label><input class="form-control" name="nama_barang" maxlength="160" required></div>
 <div class="col-md-2"><label class="form-label">Satuan</label><input class="form-control" name="satuan" value="unit" required></div>
 <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option>Aktif</option><option>Nonaktif</option></select></div>
 <div class="col-md-4"><label class="form-label">Biaya dari Mitra / Unit (Rp)</label><input type="number" name="harga_mitra" class="form-control" min="0" required value="0"></div>
 <div class="col-md-4"><label class="form-label">Tarif ke Pelanggan / Unit (Rp)</label><input type="number" name="harga_pelanggan" class="form-control" min="0" required value="0"></div>
 <div class="col-md-4"><label class="form-label">Catatan / Syarat</label><input class="form-control" name="catatan"></div>
 <div class="col-12"><button class="btn btn-primary">Simpan Barang Mitra</button></div>
</form></div></div>

<div class="card jr-form-card"><div class="card-header">Katalog Perlengkapan Mitra · {{ $barang->count() }}</div><div class="card-body">
 <p class="text-muted small">Harga ini adalah tarif referensi. Saat dibuat transaksi, harga yang disepakati disalin ke rincian transaksi. Katalog tidak menjamin stok tersedia di pihak mitra.</p>
 @forelse($barang as $b)
 <form method="POST" action="{{ route('mitra.barang.update',['id'=>$b->id_barang_mitra]) }}" class="border rounded p-3 mb-3">@csrf
 <div class="row g-2 align-items-end">
  <div class="col-lg-3"><label class="form-label">Mitra</label><select class="form-select" name="id_mitra" required>@foreach($mitra as $m)<option value="{{ $m->id_mitra }}" @selected($b->id_mitra===$m->id_mitra)>{{ $m->nama_mitra }}</option>@endforeach</select></div>
  <div class="col-lg-3"><label class="form-label">Nama Barang</label><input class="form-control" name="nama_barang" value="{{ $b->nama_barang }}" required></div>
  <div class="col-lg-2"><label class="form-label">Satuan</label><input class="form-control" name="satuan" value="{{ $b->satuan }}" required></div>
  <div class="col-lg-2"><label class="form-label">Status</label><select name="status" class="form-select"><option @selected($b->status==='Aktif')>Aktif</option><option @selected($b->status==='Nonaktif')>Nonaktif</option></select></div>
  <div class="col-lg-2"><button class="btn btn-outline-primary w-100">Update Barang</button></div>
  <div class="col-lg-4"><label class="form-label">Biaya Mitra / Unit</label><input class="form-control" type="number" name="harga_mitra" min="0" value="{{ $b->harga_mitra }}" required></div>
  <div class="col-lg-4"><label class="form-label">Tarif Pelanggan / Unit</label><input class="form-control" type="number" name="harga_pelanggan" min="0" value="{{ $b->harga_pelanggan }}" required></div>
  <div class="col-lg-4"><label class="form-label">Catatan</label><input class="form-control" name="catatan" value="{{ $b->catatan }}"></div>
 </div></form>
 @empty<p class="text-muted">Katalog barang mitra masih kosong.</p>@endforelse
 <p class="text-muted small">Barang yang tidak dipakai lagi cukup dinonaktifkan. Jangan hapus riwayat transaksi.</p>
</div></div>
@include('layouts.footer')
