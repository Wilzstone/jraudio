@include('layouts.header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><div class="jr-form-eyebrow">PENYEWAAN / JR AUDIO</div><h3>{{ $pengajuan ? 'Konfirmasi & Buat Transaksi' : 'Tambah Transaksi Penyewaan' }}</h3><p class="jr-form-help">{{ $pengajuan ? 'Data dari pengajuan '. $pengajuan->kode .' sudah diisi. Periksa kembali sebelum disimpan.' : 'Lengkapi data penyewaan dan periksa rincian biaya.' }}</p></div><a href="/admin/transaksi" class="btn btn-secondary">Kembali</a>
</div>
<?php
$today = date('Y-m-d');
$oldJenis = $old['jenis_transaksi'] ?? 'Satuan';
$oldPaket = $old['id_paket'] ?? '';
$idGenset = '';
foreach ($barang as $b) {
    if (stripos($b['nama_barang'], 'Generator set') !== false || stripos($b['kategori'], 'Genset') !== false) {
        $idGenset = $b['id_barang'];
        break;
    }
}
?>
@if($pengajuan)
<div class="alert alert-info">Pengajuan <strong>{{ $pengajuan->kode }}</strong> belum menjadi transaksi. Harga, tanggal, alat, dan lokasi masih bisa diperiksa sebelum penyimpanan.</div>
@endif
<div class="card jr-form-card">
    <div class="card-body">
        <form action="/admin/transaksi/simpan" method="post" id="formTransaksi">
    @csrf
            @if($pengajuan)<input type="hidden" name="kode_pengajuan" value="{{ $pengajuan->kode }}">@endif
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Pelanggan</label><select name="id_pelanggan"
                        class="form-select" required>
                        <option value="">-- Pilih Pelanggan --</option><?php foreach ($pelanggan as $p): ?>
                            <option value="<?= $p['id_pelanggan'] ?>"
                                <?= (($old['id_pelanggan'] ?? '') == $p['id_pelanggan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama_pelanggan']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Tanggal Sewa</label><input type="date"
                        name="tanggal_sewa" id="tanggal_sewa" class="form-control" min="<?= $today ?>"
                        value="<?= htmlspecialchars($old['tanggal_sewa'] ?? $today) ?>" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Tanggal Kembali</label><input type="date"
                        name="tanggal_kembali" id="tanggal_kembali" class="form-control" min="<?= $today ?>"
                        value="<?= htmlspecialchars($old['tanggal_kembali'] ?? $today) ?>" required><small
                        class="text-muted">Maksimal H+7.</small></div>
                <div class="col-md-2 mb-3"><label class="form-label">Status</label><select name="status_transaksi"
                        class="form-select">
                        <option value="Booking">Booking</option>
                        <option value="Berjalan" @disabled($pengajuan)>Berjalan</option>
                    </select></div>
            </div>
            <div class="row g-3 mb-2">
                <div class="col-md-7"><label class="form-label">Alamat lokasi acara <span class="text-muted">(diisi crew)</span></label>
                    <textarea class="form-control" name="alamat_lokasi_acara" rows="2" maxlength="2000" placeholder="Alamat lengkap tempat acara">{{ old('alamat_lokasi_acara') }}</textarea></div>
                <div class="col-md-5"><label class="form-label">Link Google Maps / Shareloc</label>
                    <input class="form-control" type="url" name="link_google_maps" maxlength="2048" placeholder="https://maps.app.goo.gl/..." value="{{ old('link_google_maps') }}"><small class="text-muted">Opsional. Isi dari Google Maps, tidak diminta saat konsultasi.</small></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Jenis Transaksi</label><select
                        name="jenis_transaksi" id="jenis_transaksi" class="form-select">
                        <option value="Satuan" <?= $oldJenis === 'Satuan' ? 'selected' : '' ?>>Satuan</option>
                        <option value="Paketan" <?= $oldJenis === 'Paketan' ? 'selected' : '' ?>>Paketan</option>
                    </select></div>
                <div class="col-md-4 mb-3 paket-field"><label class="form-label">Pilih Paket</label><select
                        name="id_paket" id="id_paket" class="form-select">
                        <option value="">-- Pilih Paket --</option><?php foreach ($paket as $p): ?>
                            <option value="<?= $p['id_paket'] ?>"
                                <?= ((string) $oldPaket === (string) $p['id_paket']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama_paket']) ?> | <?= htmlspecialchars($p['range_harga']) ?>
                            </option><?php endforeach; ?>
                    </select></div>
                <div class="col-md-4 mb-3 paket-field"><label class="form-label">Harga Paket Manual</label><input
                        type="number" name="harga_paket_manual" id="harga_paket_manual" class="form-control" min="0"
                        value="<?= htmlspecialchars($old['harga_paket_manual'] ?? '') ?>" placeholder="Contoh: 800000">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Biaya Genset</label><input type="number"
                        name="biaya_genset" id="biaya_genset" class="form-control" min="0"
                        value="<?= htmlspecialchars($old['biaya_genset'] ?? '0') ?>"><small class="text-muted">{{ $pengajuan ? 'Biaya genset dari rekomendasi sudah terisi dan dapat diedit.' : 'Jika diisi, cek kembali ketersediaan genset.' }}</small></div>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5>Barang / Item yang Disewa</h5>
                <div><button type="button" class="btn btn-sm btn-success" onclick="tambahBarisBarang()">+ Tambah
                        Barang</button> <button type="button" class="btn btn-sm btn-warning"
                        onclick="tambahBarisExternal()">+ Item Luar</button></div>
            </div>
            <div id="barang-wrapper"></div>
            <div class="border rounded p-3 mt-3 bg-light"><strong>Estimasi Total:</strong> <span id="preview_total">Rp
                    0</span><br><small>Paketan: harga paket manual + genset + item luar. Satuan: barang + genset + item
                    luar.</small></div>
            <div class="jr-form-actions">
              <button type="submit" class="btn btn-primary">{{ $pengajuan ? 'Konfirmasi & Simpan Transaksi' : 'Simpan Transaksi' }}</button>
              <a href="{{ $pengajuan ? route('pengajuan.admin.show',['kode'=>$pengajuan->kode]) : url('/admin/transaksi') }}" class="btn btn-outline-secondary">{{ $pengajuan ? 'Kembali ke Pengajuan' : 'Kembali' }}</a>
            </div>
        </form>
    </div>
</div>
<template id="template-barang">
    <div class="row barang-row mb-2 align-items-start">
        <div class="col-md-2"><select name="tipe_item[]" class="form-select tipe-item" onchange="toggleRowType(this)">
                <option value="Barang">Barang</option>
                <option value="External">Item Luar</option>
            </select></div>
        <div class="col-md-4 barang-field"><select name="id_barang[]" class="form-select barang-select">
                <option value="">-- Pilih Barang --</option><?php foreach ($barang as $b): ?>
                    <option value="<?= $b['id_barang'] ?>" data-harga="<?= (int) $b['harga_sewa'] ?>"
                        data-stok="<?= (int) $b['stok'] ?>"><?= htmlspecialchars($b['nama_barang']) ?> | tersedia
                        <?= (int) $b['stok'] ?> | Rp <?= number_format((int) $b['harga_sewa'], 0, ',', '.') ?></option>
                <?php endforeach; ?>
            </select></div>
        <div class="col-md-3 external-field"><input type="text" name="nama_item_external[]" class="form-control"
                placeholder="Nama item luar, contoh: Tenda"><input type="text" name="sumber_external[]"
                class="form-control mt-1" placeholder="Pinjam/sewa dari mana?"></div>
        <div class="col-md-1"><input type="number" name="qty[]" class="form-control qty-input" min="1" value="1"
                required></div>
        <div class="col-md-1 external-field"><input type="number" name="harga_manual_item[]"
                class="form-control harga-manual-item" min="0" value="0" placeholder="Harga"></div>
        <div class="col-md-1"><button type="button" class="btn btn-danger w-100" onclick="hapusBaris(this)">X</button>
        </div>
    </div>
</template>
<script>
    const paketDetail = @json($paketDetail); const idGenset = @json((string)$idGenset);
    const isPengajuan = @json((bool)$pengajuan);
    const prefillItems = @json($prefillItems);
    function rupiah(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') }
    function tambahBarisBarang(id = '', qty = 1) { const c = document.getElementById('template-barang').content.cloneNode(true); const r = c.querySelector('.barang-row'); r.querySelector('.tipe-item').value = 'Barang'; r.querySelector('.barang-select').value = id; r.querySelector('.qty-input').value = qty; document.getElementById('barang-wrapper').appendChild(c); refreshRows(); window.JRMoney?.refresh() }
    function tambahBarisExternal(nama = '', sumber = '', qty = 1, harga = 0) { const c = document.getElementById('template-barang').content.cloneNode(true); const r = c.querySelector('.barang-row'); r.querySelector('.tipe-item').value = 'External'; r.querySelector('input[name="nama_item_external[]"]').value = nama; r.querySelector('input[name="sumber_external[]"]').value = sumber; r.querySelector('.qty-input').value = qty; r.querySelector('.harga-manual-item').value = harga; document.getElementById('barang-wrapper').appendChild(c); refreshRows(); window.JRMoney?.refresh() }
    function hapusBaris(b) { if (document.querySelectorAll('.barang-row').length > 1) b.closest('.barang-row').remove(); hitungPreview() }
    function toggleRowType(s) { const r = s.closest('.barang-row'), ex = s.value === 'External'; r.querySelectorAll('.barang-field').forEach(e => e.style.display = ex ? 'none' : ''); r.querySelectorAll('.external-field').forEach(e => e.style.display = ex ? '' : 'none'); r.querySelector('input[name="nama_item_external[]"]').required = ex; if (ex) r.querySelector('.barang-select').value = ''; hitungPreview() }
    function refreshRows() { document.querySelectorAll('.tipe-item').forEach(toggleRowType); hitungPreview() }
    function clearRows() { document.getElementById('barang-wrapper').innerHTML = '' }
    function loadPaketBarang() { const j = document.getElementById('jenis_transaksi').value, id = document.getElementById('id_paket').value; if (j !== 'Paketan' || !id || !paketDetail[id]) return; clearRows(); paketDetail[id].forEach(i => tambahBarisBarang(i.id_barang, i.qty)); cekGensetOtomatis(); if (!document.querySelector('.barang-row')) tambahBarisBarang(); refreshRows() }
    function cekGensetOtomatis() { if (isPengajuan) return; const biaya = window.JRMoney.parse(document.getElementById('biaya_genset').value); if (biaya <= 0 || !idGenset) return; let ada = false; document.querySelectorAll('.barang-row').forEach(r => { if (r.querySelector('.tipe-item').value === 'Barang' && r.querySelector('.barang-select').value === idGenset) ada = true }); if (!ada) tambahBarisBarang(idGenset, 1) }
    function toggleJenisTransaksi() { const j = document.getElementById('jenis_transaksi').value; document.querySelectorAll('.paket-field').forEach(e => e.style.display = j === 'Paketan' ? '' : 'none'); document.getElementById('harga_paket_manual').required = j === 'Paketan'; document.getElementById('id_paket').required = j === 'Paketan' && !isPengajuan; hitungPreview() }
    function hitungPreview() {
        const j = document.getElementById('jenis_transaksi').value;
        const genset = window.JRMoney.parse(document.getElementById('biaya_genset').value);
        let total = genset;

        if (j === 'Paketan') {
            total += window.JRMoney.parse(document.getElementById('harga_paket_manual').value);
        }

        document.querySelectorAll('.barang-row').forEach(r => {
            const ex = r.querySelector('.tipe-item').value === 'External';
            const qty = Number(r.querySelector('.qty-input').value || 0);

            if (ex) {
                total += qty * window.JRMoney.parse(r.querySelector('.harga-manual-item').value);
            } else if (j === 'Satuan') {
                const s = r.querySelector('.barang-select');
                const harga = Number(s.options[s.selectedIndex]?.dataset.harga || 0);

                // Kalau biaya genset sudah diisi, baris genset tidak dihitung lagi.
                if (idGenset && s.value === idGenset && genset > 0) {
                    total += 0;
                } else {
                    total += qty * harga;
                }
            }
        });

        document.getElementById('preview_total').innerText = rupiah(total);
    }
    function updateTanggalKembaliLimit() { const s = document.getElementById('tanggal_sewa'), k = document.getElementById('tanggal_kembali'); if (!s.value) return; const d = new Date(s.value), m = new Date(d); m.setDate(m.getDate() + 7); const ms = m.toISOString().slice(0, 10); k.min = s.value; k.max = ms; if (!k.value || k.value < s.value) k.value = s.value; if (k.value > ms) k.value = ms }
    document.addEventListener('input', e => { if (e.target.matches('.qty-input,#harga_paket_manual,#biaya_genset,.harga-manual-item')) { if (e.target.id === 'biaya_genset') cekGensetOtomatis(); hitungPreview() } }); document.addEventListener('change', e => { if (e.target.matches('.barang-select,.tipe-item')) hitungPreview() }); document.getElementById('jenis_transaksi').addEventListener('change', function () { toggleJenisTransaksi(); if (this.value === 'Paketan' && !isPengajuan) loadPaketBarang() }); document.getElementById('id_paket').addEventListener('change', () => { if(!isPengajuan) loadPaketBarang(); }); document.getElementById('tanggal_sewa').addEventListener('change', updateTanggalKembaliLimit); document.addEventListener('DOMContentLoaded', () => {
        if (isPengajuan) {
            document.getElementById('jenis_transaksi').value = 'Paketan';
            prefillItems.forEach(i => {
                if (i.tipe_item === 'Barang') tambahBarisBarang(String(i.id_barang || ''), i.qty);
                else tambahBarisExternal(i.nama_item_external, i.sumber_external || '', i.qty, i.harga_satuan || 0);
            });
            // Paket custom memakai id_paket kosong, tanpa membuat stok/barang baru.
            document.getElementById('jenis_transaksi').disabled = true;
            document.getElementById('id_paket').disabled = true;
            // Disabled selects tidak ikut POST; kirim jenis dari hidden.
            const h=document.createElement('input'); h.type='hidden'; h.name='jenis_transaksi'; h.value='Paketan'; document.getElementById('formTransaksi').appendChild(h);
            const paketHidden=document.createElement('input'); paketHidden.type='hidden'; paketHidden.name='id_paket'; paketHidden.value=document.getElementById('id_paket').value; document.getElementById('formTransaksi').appendChild(paketHidden);
        } else tambahBarisBarang();
        toggleJenisTransaksi(); updateTanggalKembaliLimit(); refreshRows();
    });
</script>
@include('layouts.footer')