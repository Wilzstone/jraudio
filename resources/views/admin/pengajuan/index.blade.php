@include('layouts.header')
<section class="jr-pengajuan-page">
  <div class="jr-pengajuan-title"><div><span class="jr-overline">PENYEWAAN / JR AUDIO</span><h1>Pengajuan Sewa</h1><p>Tinjau permintaan konsultasi dari website. Data ini belum menjadi transaksi.</p></div>
</div>
  <div class="jr-pengajuan-stats">
    @foreach(['Menunggu','Revisi','Disetujui','Batal'] as $nama)
      <a href="{{ route('pengajuan.admin.index', ['status'=>$nama]) }}" class="jr-kpi"><small>{{ $nama }}</small><strong>{{ number_format((int)($counts[$nama] ?? 0),0,',','.') }}</strong></a>
    @endforeach
  </div>
  <div class="jr-pengajuan-panel">
    <div class="jr-pengajuan-controls"><h2>Daftar Pengajuan</h2><a href="{{ route('pengajuan.admin.index') }}">Tampilkan Semua</a></div>
    <div class="table-responsive"><table class="table table-hover align-middle">
      <thead><tr><th>Kode / Pelanggan</th><th>Acara</th><th>Tanggal</th><th>Rekomendasi</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse($pengajuan as $row)
          <tr><td><strong>{{ $row->kode }}</strong><br><small>{{ $row->nama_pelanggan }} · {{ $row->no_hp }}</small></td>
          <td>{{ ucwords(str_replace('_',' ',$row->jenis_acara)) }}</td>
          <td>{{ date('d/m/Y',strtotime($row->tanggal_acara)) }}</td>
          <td>{{ match($row->rekomendasi_paket) {'paket2500'=>'Paket 2500','paket5000'=>'Paket 5000',default=>'Custom'} }}</td>
          <td><span class="jr-status jr-status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td>
          <td><a class="btn btn-sm btn-dark" href="{{ route('pengajuan.admin.show',['kode'=>$row->kode]) }}">Tinjau</a></td></tr>
        @empty
          <tr><td colspan="6" class="text-center py-5">Belum ada pengajuan dengan status ini.</td></tr>
        @endforelse
      </tbody>
    </table></div>
    <div>{{ $pengajuan->links() }}</div>
  </div>
</section>
@include('layouts.footer')
