/* JR Audio v6 — pemisah ribuan untuk INPUT RUPIAH saja.
   Tidak menyentuh qty, nomor telepon, tanggal, ID, atau stok.
   Nilai yang dikirim ke server selalu digit biasa: 400.000 -> 400000. */
(() => {
  'use strict';
  const moneyNames = new Set([
    'harga_sewa', 'harga_paket_manual', 'harga_final', 'biaya_genset',
    'harga_manual_item', 'jumlah_bayar', 'denda_manual', 'harga_satuan',
    'biaya_transportasi', 'total_harga'
  ]);
  const digits = value => String(value == null ? '' : value).replace(/[^\d]/g, '');
  const parse = value => Number(digits(value) || '0');
  const format = value => {
    const raw = digits(value).replace(/^0+(?=\d)/, '');
    return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  };
  window.JRMoney = { parse, format, refresh: () => init(document) };

  function isMoney(input) {
    if (!(input instanceof HTMLInputElement)) return false;
    if (input.type !== 'number' && input.type !== 'text') return false; // jangan tampilkan hidden input
    const name = (input.getAttribute('name') || '').replace(/\[\]$/, '');
    return moneyNames.has(name);
  }

  function validate(input) {
    const val = digits(input.value);
    const min = input.dataset.jrMoneyMin;
    const max = input.dataset.jrMoneyMax;
    let msg = '';
    if (input.required && val === '') msg = 'Nominal wajib diisi.';
    else if (val && min !== undefined && Number(val) < Number(min)) msg = 'Nominal minimal Rp ' + format(min) + '.';
    else if (val && max !== undefined && Number(val) > Number(max)) msg = 'Nominal maksimal Rp ' + format(max) + '.';
    input.setCustomValidity(msg);
    return msg === '';
  }
  function decorate(input) {
    if (!isMoney(input) || input.dataset.jrMoneyReady) return;
    input.dataset.jrMoneyReady = '1';
    if (input.hasAttribute('min')) input.dataset.jrMoneyMin = input.getAttribute('min');
    if (input.hasAttribute('max')) input.dataset.jrMoneyMax = input.getAttribute('max');
    input.type = 'text';
    input.inputMode = 'numeric';
    input.autocomplete = 'off';
    input.value = format(input.value);
    if (input.placeholder) input.placeholder = input.placeholder.replace(/\d{4,}/g, s => format(s));
    input.addEventListener('input', () => {
      const leftCount = digits(input.value.slice(0, input.selectionStart ?? input.value.length)).length;
      input.value = format(input.value);
      let pos = 0, seen = 0;
      if (leftCount > 0) {
        while (pos < input.value.length && seen < leftCount) {
          if (/\d/.test(input.value[pos])) seen++;
          pos++;
        }
      }
      input.setSelectionRange(pos, pos);
      validate(input);
    });
    input.addEventListener('blur', () => validate(input));
  }
  function init(node) {
    if (node instanceof HTMLInputElement) decorate(node);
    if (node.querySelectorAll) node.querySelectorAll('input[name]').forEach(decorate);
  }
  document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.ja-admin-content') || document;
    init(root);
    // Input harga item luar dibuat dinamis di form Transaksi.
    const observer = new MutationObserver(mutations => {
      mutations.forEach(m => m.addedNodes.forEach(n => { if (n.nodeType === 1) init(n); }));
    });
    observer.observe(root, { childList: true, subtree: true });

    document.addEventListener('submit', event => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement)) return;
      const fields = Array.from(form.querySelectorAll('input[data-jr-money-ready]'));
      for (const field of fields) {
        if (!validate(field)) { event.preventDefault(); field.reportValidity(); return; }
      }
      // Ubah pemisah ribuan menjadi integer sebelum FormData/validasi Laravel.
      fields.forEach(field => { field.value = digits(field.value); });
      // Jika modal konfirmasi membatalkan submit, tampilkan kembali pemisahnya.
      setTimeout(() => fields.forEach(field => {
        if (field.isConnected) field.value = format(field.value);
      }), 0);
    }, true);
  });
})();
