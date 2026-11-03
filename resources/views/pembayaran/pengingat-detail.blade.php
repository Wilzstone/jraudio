@include('layouts.header')
<section class="container py-3" style="max-width:850px"><h3>Pengingat Pelunasan #{{ $row->id_transaksi }}</h3>
<div class="card jr-form-card"><div class="card-body">
<p>{{ $row->nama_pelanggan }} · {{ $row->no_hp }}</p>
<textarea class="form-control" id="jr-reminder-msg" rows="12" readonly>{{ $pesan }}</textarea>
<div class="jr-form-actions mt-3 d-flex flex-wrap gap-2">
@if($wa)<a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">Buka WhatsApp</a>@endif
<button type="button" class="btn btn-outline-primary" onclick="navigator.clipboard.writeText(document.getElementById('jr-reminder-msg').value)">Salin Pesan</button>
<a href="{{ route('pembayaran.pengingat.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div><small class="text-muted d-block mt-3">Mengakses link tidak berarti pesan terkirim. Pastikan admin menekan Kirim di WhatsApp.</small>
</div></div></section>
@include('layouts.footer')
