@include('layouts.header')
<div class="jr-form-heading mb-3">
    <div><span class="jr-form-eyebrow">KEUANGAN / JR AUDIO</span><h3>Tambah Pembayaran</h3><p class="jr-form-help">Pilih transaksi, lalu catat pembayaran. Nominal pelunasan dan denda dihitung otomatis.</p></div>
</div>
@if(!$transaksi)
<div class="card jr-form-card"><div class="card-body">
    <form action="/admin/pembayaran/tambah" method="get">
        <label class="form-label">Pilih Transaksi</label>
        <select name="id_transaksi" class="form-select mb-3" required>
            <option value="">-- Pilih Transaksi --</option>
            @foreach($daftarTransaksi as $row)
            <option value="{{ $row['id_transaksi'] }}">#{{ $row['id_transaksi'] }} - {{ $row['nama_pelanggan'] }} · Rp {{ number_format((int)$row['total_harga'],0,',','.') }}</option>
            @endforeach
        </select>
        <div class="jr-form-actions"><button class="btn btn-primary">Lanjut</button><a href="/admin/pembayaran" class="btn btn-outline-secondary">Kembali</a></div>
    </form>
</div></div>
@else
<div class="row g-3 mb-3">
    @foreach([['Total Sewa',$ringkasan['total_sewa']], ['Sisa Sewa',$ringkasan['sisa_sewa']], ['Total Denda',$ringkasan['total_denda']], ['Sisa Denda',$ringkasan['sisa_denda']]] as [$label,$value])
    <div class="col-md-3 col-sm-6"><div class="border rounded p-3 h-100"><div class="text-muted">{{ $label }}</div><strong>Rp {{ number_format($value,0,',','.') }}</strong></div></div>
    @endforeach
</div>
@if($ringkasan['kredit_denda_lama']>0)<div class="alert alert-info">Terdapat pembayaran lama yang melebihi harga sewa. Sebanyak Rp {{ number_format($ringkasan['kredit_denda_lama'],0,',','.') }} sudah diperhitungkan sebagai pembayaran denda lama.</div>@endif
@if($transaksi['status_transaksi']==='Batal')
    <div class="alert alert-warning">Transaksi dibatalkan dan tidak menerima pembayaran.</div>
@elseif($pending)
    <div class="alert alert-warning">Masih ada pembayaran <strong>Menunggu</strong>. Konfirmasi atau tolak terlebih dahulu pada detail transaksi sebelum menambahkan pembayaran lain.</div>
@elseif($sudahLunas)
    <div class="alert alert-success">{{ $jenisOtomatis==='Denda' ? 'Denda sudah lunas.' : 'Biaya sewa sudah lunas.' }} Tidak ada tagihan baru.</div>
@else
    <div class="card jr-form-card"><div class="card-body">
        <div class="alert alert-info">{{ $jenisOtomatis==='Denda' ? 'Pengembalian selesai. Pembayaran baru hanya untuk sisa denda dan nominalnya dikunci oleh sistem.' : 'Pembayaran awal dapat berupa DP. Jika memilih Pelunasan, sistem mengisi sisa sewa otomatis.' }}</div>
        <form action="/admin/pembayaran/simpan" method="post" id="jrPembayaranForm">
            @csrf
            <input type="hidden" name="id_transaksi" value="{{ $transaksi['id_transaksi'] }}">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Transaksi</label><input class="form-control" value="#{{ $transaksi['id_transaksi'] }} - {{ $transaksi['nama_pelanggan'] }}" readonly></div>
                <div class="col-md-6"><label class="form-label">Tanggal Bayar</label><input type="date" name="tanggal_bayar" min="{{ \Carbon\Carbon::today('Asia/Jakarta')->toDateString() }}" max="{{ \Carbon\Carbon::today('Asia/Jakarta')->toDateString() }}" value="{{ \Carbon\Carbon::today('Asia/Jakarta')->toDateString() }}" class="form-control" required><small class="text-muted">Tanggal dicatat sesuai hari pembayaran diterima.</small></div>
                <div class="col-md-6"><label class="form-label">Jenis Pembayaran</label>
                    @if($jenisOtomatis==='Denda')
                        <input class="form-control" value="Denda" readonly><input type="hidden" name="jenis_pembayaran" value="Denda">
                    @else
                        <select name="jenis_pembayaran" id="jenis_pembayaran" class="form-select" required>
                            <option value="DP" @selected(old('jenis_pembayaran')==='DP')>DP</option>
                            <option value="Pelunasan" @selected(old('jenis_pembayaran')==='Pelunasan')>Pelunasan</option>
                        </select>
                    @endif
                </div>
                <div class="col-md-6"><label class="form-label">Jumlah Bayar (Rp)</label>
                    <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" min="1" value="{{ $jenisOtomatis==='Denda' ? $sisaBayar : old('jumlah_bayar','') }}" @if($jenisOtomatis==='Denda') readonly @endif required>
                    <small class="text-muted">{{ $jenisOtomatis==='Denda' ? 'Jumlah mengikuti sisa denda. Tidak bisa diubah.' : 'DP dapat diisi manual; Pelunasan otomatis mengikuti sisa sewa.' }}</small>
                </div>
                <div class="col-md-6"><label class="form-label">Status Konfirmasi</label><select name="status_konfirmasi" class="form-select"><option value="Menunggu">Menunggu</option>@if(auth()->user()?->role==='Owner')<option value="Dikonfirmasi">Dikonfirmasi</option>@endif</select></div>
            </div>
            <div class="jr-form-actions mt-4"><button class="btn btn-primary" type="submit">Simpan Pembayaran</button><a class="btn btn-outline-secondary" href="/admin/transaksi/detail?id={{ $transaksi['id_transaksi'] }}">Kembali</a></div>
        </form>
    </div></div>
@endif
@if($sudahLunas || $pending || $transaksi['status_transaksi']==='Batal')<div class="jr-form-actions mt-3"><a class="btn btn-outline-secondary" href="/admin/transaksi/detail?id={{ $transaksi['id_transaksi'] }}">Kembali</a></div>@endif
<script>
document.addEventListener('DOMContentLoaded', function(){
    const jenis=document.getElementById('jenis_pembayaran');
    const nominal=document.getElementById('jumlah_bayar');
    if(!nominal || !jenis)return;
    const sisa=@json($ringkasan['sisa_sewa']);
    function ubah(){
        if(jenis.value==='Pelunasan'){
            nominal.value=window.JRMoney ? window.JRMoney.format(sisa) : String(sisa);
            nominal.readOnly=true;
        }else{
            nominal.readOnly=false;
            nominal.value='';
        }
    }
    jenis.addEventListener('change',ubah);ubah();
});
</script>
@endif
@include('layouts.footer')
