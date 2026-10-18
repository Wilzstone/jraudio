(function () {
  const app = document.getElementById('jaApp');
  const open = document.querySelector('[data-menu-open]');
  const closers = document.querySelectorAll('[data-menu-close]');
  const account = document.getElementById('jaUserMenu');
  if (!app) return;
  const closeNav = () => { app.classList.remove('menu-open'); if (open) open.setAttribute('aria-expanded', 'false'); };
  if (open) open.addEventListener('click', () => { const state = !app.classList.contains('menu-open'); app.classList.toggle('menu-open', state); open.setAttribute('aria-expanded', String(state)); });
  closers.forEach(el => el.addEventListener('click', closeNav));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeNav(); if (account) account.open = false; } });
  document.addEventListener('pointerdown', e => { if (account && !account.contains(e.target)) account.open = false; });
  document.querySelectorAll('.ja-sidebar a').forEach(el => el.addEventListener('click', closeNav));
})();
