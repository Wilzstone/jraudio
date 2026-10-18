@include('public.header')
<main class="section" style="max-width:920px;margin:0 auto;padding-top:36px">
    <div class="section-title">
        <span class="eyebrow">JR Audio Smart System</span>
        <h2>Rekomendasi Sound System Acara</h2>
        <p>Ceritakan kebutuhan acaramu. Sistem akan menyusun saran barang dari katalog JR Audio dan menghitung estimasi harga satuan.</p>
    </div>
    @if($errors->any())
        <div style="padding:14px;border-radius:10px;background:#fee2e2;color:#7f1d1d;margin-bottom:16px">
            <strong>Periksa input:</strong>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <form action="{{ route('rekomendasi.proses') }}" method="POST" class="smart-form">
        @csrf
        <div class="smart-grid">
            <label>Jenis acara
                <select name="jenis_acara" required>
                    @foreach(['seminar'=>'Seminar / Meeting','pernikahan'=>'Pernikahan','konser'=>'Konser / Pertunjukan','sekolah'=>'Acara Sekolah','lainnya'=>'Lainnya'] as $value=>$nama)
                        <option value="{{ $value }}" @selected(old('jenis_acara','seminar')===$value)>{{ $nama }}</option>
                    @endforeach
                </select>
            </label>
            <label>Jumlah peserta
                <input name="jumlah_peserta" type="number" min="1" max="20000" required value="{{ old('jumlah_peserta',100) }}">
            </label>
            <label>Lokasi acara
                <select name="lokasi" required>
                    <option value="indoor" @selected(old('lokasi')==='indoor')>Indoor</option>
                    <option value="outdoor" @selected(old('lokasi')==='outdoor')>Outdoor</option>
                </select>
            </label>
            <label>Kegiatan utama
                <select name="kegiatan" required>
                    <option value="pidato" @selected(old('kegiatan')==='pidato')>Pidato / MC</option>
                    <option value="musik_live" @selected(old('kegiatan')==='musik_live')>Musik live</option>
                    <option value="musik_rekaman" @selected(old('kegiatan')==='musik_rekaman')>Pemutaran musik</option>
                    <option value="umum" @selected(old('kegiatan')==='umum')>Acara umum</option>
                </select>
            </label>
            <label>Anggaran maksimal (Rp)
                <input name="anggaran" type="number" min="0" required value="{{ old('anggaran',2000000) }}">
            </label>
            <label>Tanggal acara
                <input name="tanggal_acara" type="date" min="{{ date('Y-m-d') }}" required value="{{ old('tanggal_acara',date('Y-m-d')) }}">
            </label>
        </div>
        <label class="smart-check"><input type="checkbox" name="butuh_genset" value="1" @checked(old('butuh_genset'))> Pertimbangkan tambahan genset (jika listrik di lokasi tidak mencukupi)</label>
        <p class="smart-hint">Ini prototipe rekomendasi berbasis aturan, bukan simulasi akustik. Hasil bukan pesanan dan belum menjamin stok pada tanggal acara.</p>
        <button class="btn-primary smart-btn" type="submit">Buat Rekomendasi</button>
    </form>
</main>
@include('public.footer')
