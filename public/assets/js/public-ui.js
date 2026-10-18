(function () {
 'use strict';
 var menuButton=document.getElementById('nav-toggle');
 var menu=document.getElementById('main-navigation');
 if(menuButton && menu){
  function closeMenu(){menu.classList.remove('is-open');menuButton.setAttribute('aria-expanded','false');document.body.classList.remove('menu-open');}
  menuButton.addEventListener('click',function(){var open=menu.classList.toggle('is-open');menuButton.setAttribute('aria-expanded',String(open));document.body.classList.toggle('menu-open',open);});
  menu.querySelectorAll('a').forEach(function(a){a.addEventListener('click',closeMenu);});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closeMenu();});
  window.addEventListener('resize',function(){if(window.innerWidth>1120)closeMenu();});
 }
 var search=document.getElementById('catalog-search');
 if(search){search.addEventListener('input',function(){var q=search.value.trim().toLocaleLowerCase('id');var matches=0;document.querySelectorAll('.catalog-item').forEach(function(card){var shown=(card.getAttribute('data-search')||'').toLocaleLowerCase('id').includes(q);card.hidden=!shown;if(shown)matches++;});var empty=document.getElementById('catalog-empty');if(empty)empty.hidden=matches!==0;});}
 var display=document.querySelector('[data-currency-display]');var value=document.querySelector('[data-currency-value]');
 if(display && value){var format=function(raw){return raw.replace(/\B(?=(\d{3})+(?!\d))/g,'.');};function sync(){var digits=display.value.replace(/[^0-9]/g,'');digits=digits.replace(/^0+(?=\d)/,'').slice(0,10);value.value=digits||'0';display.value=format(digits);}
  display.addEventListener('input',sync);var form=display.closest('form');if(form)form.addEventListener('submit',sync);
 }
})();
