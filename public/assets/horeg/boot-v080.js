// JR AUDIO HOREG CDN LOADER v0.8.0. No npm required.
// Full, versioned game files prevent accidentally retaining v0.4.1 / v0.4.2 JS.
const loading = document.getElementById('garage-loading');
let stage = 'Persiapan';
let failed = false;
const cdn = new URL(location.href).searchParams.get('cdn') === 'unpkg' ? 'unpkg' : 'jsDelivr';
function displayError(error) {
  if (failed || window.__horegGarageReady) return;
  failed = true;
  const msg = error?.message || String(error || 'Kesalahan tidak diketahui');
  const stack = error?.stack || '';
  console.error('[HOREG v0.8.0] gagal pada tahap:', stage, 'CDN:', cdn, error);
  if (!loading) return;
  loading.replaceChildren();
  loading.style.cssText = 'display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:10px;padding:22px;white-space:normal;z-index:11;overflow:auto;';
  const title = document.createElement('strong');
  title.textContent = '3D belum berhasil dimuat — tahap: ' + stage;
  title.style.cssText = 'font-size:17px;line-height:1.4;';
  const detail = document.createElement('pre');
  detail.textContent = [msg, stack].filter(Boolean).join('\n');
  detail.style.cssText = 'font-size:12px;white-space:pre-wrap;overflow-wrap:anywhere;max-width:100%;line-height:1.55;';
  const suggestion = document.createElement('small');
  suggestion.textContent = 'CDN: ' + cdn + ' • Versi: 0.8.0. Tidak perlu npm. Kirim screenshot Console F12 bila masih gagal.';
  const url = new URL(location.href);
  if(cdn === 'unpkg') url.searchParams.delete('cdn'); else url.searchParams.set('cdn','unpkg');
  const link = document.createElement('a');
  link.href = url.toString();
  link.textContent = cdn === 'unpkg' ? 'Uji CDN jsDelivr' : 'Uji CDN unpkg';
  link.style.cssText = 'color:#fff;background:#1761b4;border-radius:9px;padding:9px 13px;text-decoration:none;';
  loading.append(title,detail,suggestion,link);
}
function setStage(next) { stage=next; if(loading && !failed)loading.textContent='Memuat 3D — '+next+'…'; console.info('[HOREG v0.8.0]',next); }
window.addEventListener('error', e => {
  if(!window.__horegGarageReady && e.error) {
    if(e.filename) stage += ' (' + e.filename + ':' + e.lineno + ':' + e.colno + ')';
    displayError(e.error);
  }
});
window.addEventListener('unhandledrejection',e=>{ if(!window.__horegGarageReady) displayError(e.reason); });
const timeout=setTimeout(()=>displayError(new Error('Waktu muat melebihi 20 detik. Periksa internet/CDN melalui tab Network F12.')),20000);
try {
  setStage('Mengambil library Three.js melalui '+cdn);
  await import('three');
  setStage('Memuat OrbitControls');
  await import('three/addons/controls/OrbitControls.js');
  setStage('Memuat aturan rakitan v0.8.0');
  await import('./rules-v080.mjs');
  setStage('Memulai garasi 3D v0.8.0');
  await import('./garage-v080.js');
  if (!window.__horegGarageReady) throw new Error('Kode game selesai diimpor tetapi tidak memberikan tanda siap.');
  clearTimeout(timeout);
  console.info('[HOREG v0.8.0] Garage siap.');
} catch(err) {
  clearTimeout(timeout);
  displayError(err);
}
