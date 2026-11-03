@include('layouts.header')
<section class="container py-3" style="max-width:850px">
 <h3>Informasi Pembayaran Terkonfirmasi</h3><p class="text-muted">Teks ini adalah ringkasan pada saat pembayaran dikonfirmasi. Admin membuka WhatsApp dan mengirim sendiri.</p>
 <div class="card jr-form-card"><div class="card-body">
  <p><strong>{{ $row->nama_pelanggan }}</strong> · {{ $row->no_hp }} · Transaksi #{{ $row->id_transaksi }}</p>
  <p>Status notifikasi: <span class="badge {{ $row->status==='Ditandai Terkirim'?'bg-success':'bg-secondary' }}">{{ $row->status }}</span></p>
  <textarea readonly class="form-control" rows="15" id="pesan-notifikasi">{{ $row->pesan }}</textarea>
  <div class="jr-form-actions mt-3 d-flex flex-wrap gap-2">
   @if($wa)<a class="btn btn-success" target="_blank" rel="noopener noreferrer" href="{{ $wa }}">Buka WhatsApp</a>@else <span class="text-danger">Nomor HP tidak valid. Periksa data pelanggan.</span>@endif
   <button type="button" class="btn btn-outline-primary" onclick="navigator.clipboard.writeText(document.getElementById('pesan-notifikasi').value)">Salin Pesan</button>
   <a class="btn btn-outline-secondary" href="/admin/pembayaran">Kembali</a>
  </div>
  @if($row->status==='Disiapkan')
  <form action="{{ route('pembayaran.notifikasi.tandai',['id'=>$row->id_pembayaran]) }}" method="post" class="mt-4">@csrf
   <label><input type="checkbox" name="konfirmasi" value="1" required> Saya sudah menekan Kirim di WhatsApp dan memeriksa percakapan pelanggan.</label>
   <button class="btn btn-sm btn-outline-success mt-2" type="submit">Tandai Telah Dikirim</button>
  </form>
  @endif
  <small class="d-block mt-3 text-muted">Status Ditandai Terkirim adalah catatan manual admin, bukan bukti pengantaran otomatis dari WhatsApp.</small>
 </div></div>
</section>
@include('layouts.footer')
