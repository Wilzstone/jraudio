@include('layouts.header')
<section class="container py-3"><h3>Pengingat DP / Belum Lunas</h3>
<p class="text-muted">Daftar transaksi aktif dengan sisa biaya sewa. Total terbayar hanya menghitung pembayaran terkonfirmasi. Pengiriman pesan dilakukan manual melalui WhatsApp.</p>
<div class="card jr-form-card"><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Transaksi</th><th>Pelanggan</th><th>Tanggal Acara</th><th>Terbayar</th><th>Sisa Sewa</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($rows as $row)
@php $sisa=max(0,(int)$row->total_harga-(int)$row->total_dibayar);$dekat=\Carbon\Carbon::parse($row->tanggal_sewa)->diffInDays(\Carbon\Carbon::today('Asia/Jakarta'),false)>=-3; @endphp
<tr><td>#{{ $row->id_transaksi }}</td><td>{{ $row->nama_pelanggan }}</td><td>{{ $row->tanggal_sewa }}</td><td>Rp {{ number_format($row->total_dibayar,0,',','.') }}</td><td>Rp {{ number_format($sisa,0,',','.') }}</td><td><span class="badge {{ $dekat?'bg-warning text-dark':'bg-secondary' }}">{{ $row->total_dibayar>0?'DP':'Belum Bayar' }}</span></td><td><a class="btn btn-sm btn-outline-success" href="{{ route('pembayaran.pengingat.show',['id'=>$row->id_transaksi]) }}">Siapkan Pengingat</a></td></tr>
@empty<tr><td colspan="7" class="text-center">Semua sewa aktif sudah lunas.</td></tr>@endforelse
</tbody></table></div></div><div class="jr-form-actions mt-3"><a href="/admin/pembayaran" class="btn btn-outline-secondary">Kembali</a></div></section>
@include('layouts.footer')
