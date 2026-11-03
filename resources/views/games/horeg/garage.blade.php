<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#0c1420">
<title>Horeg Simulator 3D | JR Audio</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/jr-favicon.svg') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="stylesheet" href="{{ asset('assets/horeg/garage-v080.css') }}?v=080">
{{-- Horeg v0.7. THREE.JS ONLINE, NO NPM. User-supplied crew photos are static public assets. --}}
@if(request()->query('cdn') === 'unpkg')
<script type="importmap">{"imports":{"three":"https://unpkg.com/three@0.180.0/build/three.module.js","three/addons/":"https://unpkg.com/three@0.180.0/examples/jsm/"}}</script>
@else
<script type="importmap">{"imports":{"three":"https://cdn.jsdelivr.net/npm/three@0.180.0/build/three.module.js","three/addons/":"https://cdn.jsdelivr.net/npm/three@0.180.0/examples/jsm/"}}</script>
@endif
<script type="module" src="{{ asset('assets/horeg/boot-v080.js') }}?v=080"></script>
</head>
<body>
<div class="app-shell">
<header class="topbar"><div class="brand"><div class="brand-icon">JR<span>.</span></div><div><strong>HOREG <em>SIMULATOR</em></strong><small>JR AUDIO · WORKSHOP 3D</small></div></div><div class="version">GARAGE + PARADE <span class="ver">v0.8.0</span></div><a class="leave" href="{{ url('/') }}">↖ JR Audio</a></header>
<nav class="steps" aria-label="Tahapan permainan">
 <button type="button" class="step active" data-stage="1"><span>01</span><b>Kendaraan</b></button>
 <button type="button" class="step" data-stage="2"><span>02</span><b>Rakit sound</b></button>
 <button type="button" class="step" data-stage="3"><span>03</span><b>Pilih kru</b></button>
 <button type="button" class="step" data-stage="4"><span>04</span><b>Ikat & periksa</b></button>
 <button type="button" class="step" data-stage="5"><span>05</span><b>Auto Parade</b></button>
</nav>
<main class="workspace">
  <aside class="stage-panel">
   <section class="stage-section" data-stage-panel="1"><span class="eyebrow">LANGKAH 01 / 04</span><h1>Pilih kendaraan.</h1><p class="help">Pilih bak yang mau dipakai. Mengganti kendaraan akan mereset komponen, bukan data JR Audio.</p><div id="vehicle-list" class="vehicle-list"></div><h2>Warna kabin</h2><div class="swatches" id="paint-picker"><button type="button" class="swatch active" data-color="#e34d52" style="--color:#e34d52" title="Merah" aria-label="Merah"></button><button type="button" class="swatch" data-color="#378cce" style="--color:#378cce" title="Biru" aria-label="Biru"></button><button type="button" class="swatch" data-color="#ebeff1" style="--color:#ebeff1" title="Putih" aria-label="Putih"></button><button type="button" class="swatch" data-color="#292e3b" style="--color:#292e3b" title="Hitam" aria-label="Hitam"></button><button type="button" class="swatch" data-color="#e7b94a" style="--color:#e7b94a" title="Kuning" aria-label="Kuning"></button></div><p class="note">🚚 Pickup · Truk · Towing. Semua kapasitas adalah angka untuk permainan.</p></section>
   <section class="stage-section" data-stage-panel="2" hidden><span class="eyebrow">LANGKAH 02 / 04</span><h1>Rakit sound.</h1><p class="help">Sound di belakang menghadap jalan. Genset depan-tengah. Mixer & operator di area tengah. Rigging menggantung di BELAKANG sound, bukan di atas bak. Sound satu baris kedalaman, boleh tinggi sesuai kendaraan. Tombol contoh akan membuat komposisi simetris khusus kendaraan yang sedang dipilih.</p>
    <div class="tabs" id="catalog-tabs" aria-label="Kategori perangkat">
     <button type="button" class="active" data-tab="sound">🔊 Sound</button><button type="button" data-tab="control">🎚️ Audio</button><button type="button" data-tab="genset">🔌 Genset</button><button type="button" data-tab="rig">▤ Rig</button><button type="button" data-tab="light">💡 Lampu</button><button type="button" data-tab="video">📺 Wall</button>
    </div>
    <div class="speaker-position" id="sound-placement">
      <b>Posisi speaker (tidak acak)</b>
      <div class="position-picker"><button type="button" data-sound-column="left">◀ Kiri</button><button type="button" class="active" data-sound-column="center">● Tengah</button><button type="button" data-sound-column="right">Kanan ▶</button></div>
      <small>Speaker akan dipasang di kolom pilihan. Kalau penuh, ada peringatan tanpa memindahkannya ke tempat lain. Horn Mini bisa ditumpuk sampai 7 lapis jika penopang & batas game memungkinkan.</small>
     </div>
    <div id="catalog" class="catalog"></div><button type="button" id="open-rig-picker" class="secondary-btn rig-quick">▦ Atur lampu di rigging (pop-up)</button><div class="live-health" id="live-health" role="status">Muatan & daya diperiksa otomatis saat merakit.</div>
    <div id="rig-grid-panel" class="rig-grid-panel" hidden><div class="rig-grid-head"><h2>Posisi rigging</h2><small id="rig-grid-caption">4 × 4</small></div><p class="mini-help">Pilih sel dulu, kemudian tambah lampu. Untuk pindah lampu, pilih lampunya lalu klik sel baru. Slot kosong tetap dapat digunakan pada tiang vertikal.</p><div id="rig-grid" class="rig-grid" role="group" aria-label="Slot lampu pada rigging"></div><button id="new-light-slot" type="button" class="secondary-btn new-light">＋ Pilih slot untuk lampu baru</button><small id="rig-hint">Belum memilih slot.</small></div>
    <details class="installed-drawer"><summary>Perangkat terpasang <b id="item-count">0 ITEM</b></summary><div id="item-list" class="installed-list"></div><div id="adjust-panel" class="adjust-panel" hidden><strong id="selected-title">—</strong><div class="nudge-grid"><button type="button" data-nudge="-x">← Kiri</button><button type="button" data-nudge="+z">↑ Depan</button><button type="button" data-nudge="+x">→ Kanan</button><button type="button" data-nudge="-z">↓ Belakang</button></div><div class="edit-actions"><button id="rotate-selected" type="button">↻ Putar</button><button id="duplicate-selected" type="button">＋ Duplikat</button><button id="remove-selected" type="button">Hapus</button></div><small id="edit-hint">Geser 20 cm. Lampu dipindah melalui grid rigging.</small></div><div class="bulk-actions"><button id="undo-last" type="button">↶ Undo</button><button id="clear-build" type="button">⌫ Kosongkan</button></div></details>
    <label for="beam-mode-garage">🎨 Warna moving beam</label>
    <select id="beam-mode-garage" data-beam-mode><option value="rainbow">🌈 Warna-warni</option><option value="white">⚪ Putih</option></select>
    <p id="case-count" class="mini-help">Hardcase mengikuti ruang bak yang kosong.</p>
    <button id="sample-build" type="button" class="sample-build">✦ Muat contoh simetris sesuai kendaraan</button>
   </section>
   <section class="stage-section" data-stage-panel="3" hidden><span class="eyebrow">LANGKAH 03 / 04</span><h1>Pilih tim.</h1><p class="help">Pilih supir dan operator. Wajah pada figur 3D memakai potongan foto yang kamu kirim, dan foto asli ditampilkan di bawah.</p><div id="crew-photos" class="crew-photos"></div><label for="driver">🚚 Supir kendaraan</label><select id="driver"><option value="">— Pilih supir —</option><option>Adit</option><option>Dika</option><option>Kepin</option><option>Galen</option><option>Andra</option><option>Nio</option></select><label for="operator">🎚️ Operator sound</label><select id="operator"><option value="">— Pilih operator —</option><option>Adit</option><option>Dika</option><option>Kepin</option><option>Galen</option><option>Andra</option><option>Nio</option></select><p class="note">Supir dan operator harus orang berbeda. Figur masih low-poly; foto adalah tekstur agar wajah lebih mirip.</p></section>
   <section class="stage-section" data-stage-panel="4" hidden><span class="eyebrow">LANGKAH 04 / 04</span><h1>Amankan rakitan.</h1><p class="help">Muatan dan daya sudah dipantau sejak sesi rakit. Di sini cukup lihat ringkasan, lalu ikat sound DARI SAMPING sebelum berangkat.</p>
    <div class="metric"><div><strong>Muatan</strong><span id="mass-text">0 kg</span></div><div class="progress"><div id="mass-fill" class="fill"></div></div></div>
    <div class="metric"><div><strong>Daya</strong><span id="power-text">0 W</span></div><div class="progress"><div id="power-fill" class="fill blue"></div></div></div>
    <div class="metric-grid"><span>Rakitan tertinggi <b id="height-text">0 m</b></span><span>Keseimbangan kiri/kanan <b id="balance-text">0 m</b></span><span>Keseimbangan depan/belakang <b id="balance-long-text">0 m</b></span></div>
    <div class="ready-box"><span>STATUS</span><b id="ready-label">BELUM SIAP</b></div><div id="checks" class="checks"></div>
    <button type="button" id="parade-check" class="primary-btn">✓ Periksa rakitan</button>
    <button type="button" id="start-straps" class="primary-btn accent">⛓ Krek! Ikat sound dari samping</button><button type="button" id="launch-parade" class="primary-btn parade-btn">🚚 Mulai Auto Parade →</button><div class="strap-progress"><div id="strap-fill"></div></div><p id="strap-status" class="mini-help">Belum diikat.</p>
    <p class="note">Ini animasi game, bukan petunjuk pengikatan muatan di dunia nyata. Tombol parade hanya aktif secara logika jika semua pemeriksaan lulus dan tali sudah dianimasikan.</p>
   </section>
   <section class="stage-section" data-stage-panel="5" hidden><span class="eyebrow">LANGKAH 05 / 05</span><h1>Auto Parade!</h1><p class="help">Kendaraan berjalan sendiri di Kampung Horeg. Putar musik milikmu, lihat sound dari berbagai kamera, atau pause kapan saja.</p>
     <div class="parade-controls"><button id="parade-pause" class="primary-btn" type="button">⏸ Pause</button><button type="button" id="parade-back" class="secondary-btn">↖ Garasi</button></div>
     <label for="parade-speed">Kecepatan <span id="parade-speed-label">8 m/s</span></label><input type="range" id="parade-speed" min="2" max="16" step="1" value="8">
     <h2>Sudut kamera</h2><div class="parade-camera"><button data-parade-camera="follow" class="active">Ikuti</button><button data-parade-camera="side">Samping</button><button data-parade-camera="rear">Sound belakang</button><button data-parade-camera="drone">Atas</button><button data-parade-camera="free">🕹 Bebas</button><button data-parade-camera="audience">👥 Penonton di pinggir jalan</button></div>
     <p class="mini-help">Kamera Bebas: geser dengan mouse kiri untuk orbit 360°, roda mouse untuk zoom, dan mouse kanan untuk menggeser sudut. Kamera mengikuti perpindahan kendaraan tanpa memaksa sudutnya kembali.</p>
     <label for="beam-mode-parade">🎨 Warna moving beam</label>
     <select id="beam-mode-parade" data-beam-mode><option value="rainbow">🌈 Warna-warni</option><option value="white">⚪ Putih</option></select>
     <h2>Rute parade</h2><label for="route-choice">Tujuan kendaraan</label><select id="route-choice"><option value="village">Loop Kampung (otomatis tanpa henti)</option><option value="field">Berhenti di Lapangan (belok saat persimpangan)</option></select>
     <p id="route-progress" class="mini-help">Loop kampung berjalan terus</p><button type="button" id="continue-route" class="primary-btn" hidden>▶ Jalan lagi</button>
     <p class="mini-help">Saat berhenti di lapangan dan musik diputar, operator serta sebagian penonton berjoget dengan tujuh variasi dan jeda masing-masing.</p><h2>Playlist musik</h2><label for="music-track">Pilih demo atau lagu upload</label><select id="music-track"></select>
     <label for="music-repeat">Saat lagu selesai</label><select id="music-repeat"><option value="all">Lagu berikutnya (playlist berulang)</option><option value="one">Ulang lagu ini terus</option></select>
     <label for="music-file">Tambah musik sendiri (bisa banyak file)</label><input id="music-file" type="file" accept="audio/*" multiple><p id="music-name" class="mini-help">Belum memilih lagu. File hanya diputar di browser.</p>
     <audio id="parade-player" controls preload="none" style="width:100%"></audio>
     <p class="note">Dua trek demo instrumental merupakan audio orisinal buatan untuk game. Kamera Bebas mempertahankan target ke kendaraan sambil tetap bisa di-orbit. File upload tetap di browser (tidak tersimpan ke server); pemutaran perlu klik Play pertama kali. Semua lingkungan ini simulasi game.</p>
   </section>
   <div class="nav-actions"><button id="stage-back" class="secondary-btn" type="button">← Kembali</button><button id="stage-next" class="primary-btn" type="button">Lanjut →</button><button id="save-build" class="secondary-btn" type="button">▣ Simpan</button></div>
  </aside>
  <section class="viewer-panel"><div class="viewer-top"><div><span class="eyebrow">JR AUDIO / GARAGE 3D</span><h2 id="viewer-title">Pickup — Garage</h2></div><div class="render-pill">● LIVE 3D</div></div>
   <div class="viewer" id="garage-viewer"><div id="garage-canvas" class="canvas-container"></div><div id="garage-loading" class="loading">Memuat model 3D…</div><div class="overlay-upper"><span id="overlay-mode">JR AUDIO · HOREG LAB</span><span>GARASI: PUTAR · SCROLL: ZOOM</span></div><div class="overlay-lower"><span>Rigging gantung ⇣ belakang · Sound satu dinding ↑</span><span id="viewport-count">0 PERANGKAT</span></div></div>
   <div class="toolbar" id="garage-toolbar"><div class="camera-tools"><button type="button" data-camera="perspective" class="active">◈ 3D</button><button type="button" data-camera="rear">⇣ Belakang sound</button><button type="button" data-camera="side">◧ Samping</button><button type="button" data-camera="front">↑ Depan / supir</button><button type="button" data-camera="top">⊞ Atas</button></div><div class="camera-tools"><button id="toggle-spin" type="button">⟳ Putar</button><button id="toggle-night" class="active" type="button">☾ Malam</button></div></div>
   <div class="bottom-summary"><div><small>KENDARAAN</small><b id="summary-vehicle">Pickup</b></div><div><small>PERANGKAT</small><b id="summary-count">0</b></div><div><small>MUATAN</small><b id="summary-load">0 / 1100 kg</b></div><div><small>KRU</small><b id="summary-crew">Belum dipilih</b></div></div>
   <div class="viewer-footer"><span id="status-line" role="status">Pilih kendaraan untuk mulai.</span><span>FREE BUILD · V0.8.0</span></div>
   <div class="safety-note">⚠ Parameter muatan, ketinggian, pusat massa, listrik, dan kekuatan rigging dibuat untuk <b>game</b>, bukan batas aman kendaraan nyata. Jangan menjalankan genset di ruang tertutup atau di kendaraan berjalan.</div>
  </section>
</main>
<footer class="footer"><span>JR AUDIO × HOREG SIMULATOR v0.8.0</span><span>GAME SIMULASI • TIDAK MENGUBAH DATA SEWA</span></footer>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div><div id="rig-picker" class="rig-popup" hidden><div class="rig-popup-card" role="dialog" aria-modal="true" aria-labelledby="rig-picker-title"><div class="rig-popup-top"><h2 id="rig-picker-title">Posisi lampu rigging</h2><button type="button" id="close-rig-picker" aria-label="Tutup">✕</button></div><p>Pilih slot langsung tanpa scroll. Slot tabrakan akan ditolak.</p><div id="rig-picker-grid" class="rig-popup-grid"></div></div></div>
<div id="result-modal" class="modal-backdrop" hidden><div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-heading"><button type="button" id="close-modal" class="modal-close">×</button><span class="eyebrow">INSPEKSI GAME</span><h2 id="modal-heading">Status rakitan</h2><div id="result-details"></div><button type="button" id="modal-ok" class="primary-btn">Tutup</button></div></div>

</body>
</html>
