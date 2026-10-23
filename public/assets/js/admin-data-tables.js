/* JR Audio — client-side search/filter/sort for existing rows. Read-only. */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-ja-list]').forEach(root => {
    const body = root.querySelector('[data-ja-body]');
    const search = root.querySelector('[data-ja-search]');
    const filter = root.querySelector('[data-ja-filter]');
    const sort = root.querySelector('[data-ja-sort]');
    const reset = root.querySelector('[data-ja-reset]');
    const counter = root.querySelector('[data-ja-counter]');
    const empty = root.querySelector('[data-ja-empty]');
    const emptyTitle = root.querySelector('[data-ja-empty-title]');
    const emptyDesc = root.querySelector('[data-ja-empty-desc]');
    if (!body || !search || !filter || !sort || !counter || !empty || !reset) return;
    const rows = Array.from(body.querySelectorAll('[data-ja-row]'));
    const noun = root.dataset.jaNoun || 'data';
    const normalize = value => String(value ?? '').trim().toLocaleLowerCase('id-ID');
    const compare = (a, b, order) => {
      switch(order) {
        case 'id-asc': return Number(a.dataset.id) - Number(b.dataset.id);
        case 'name-asc': return normalize(a.dataset.name).localeCompare(normalize(b.dataset.name), 'id');
        case 'name-desc': return normalize(b.dataset.name).localeCompare(normalize(a.dataset.name), 'id');
        case 'date-desc': return String(b.dataset.date || '').localeCompare(String(a.dataset.date || '')) || Number(b.dataset.id)-Number(a.dataset.id);
        case 'date-asc': return String(a.dataset.date || '').localeCompare(String(b.dataset.date || '')) || Number(a.dataset.id)-Number(b.dataset.id);
        case 'amount-desc': return Number(b.dataset.amount) - Number(a.dataset.amount);
        case 'amount-asc': return Number(a.dataset.amount) - Number(b.dataset.amount);
        default: return Number(b.dataset.id) - Number(a.dataset.id);
      }
    };
    const apply = () => {
      const query = normalize(search.value);
      const selected = normalize(filter.value);
      const order = sort.value;
      const sorted = [...rows].sort((a, b) => compare(a, b, order));
      sorted.forEach(row => body.appendChild(row));
      let count = 0;
      rows.forEach(row => {
        const match = (!query || normalize(row.dataset.search).includes(query))
          && (!selected || normalize(row.dataset.filter) === selected);
        row.hidden = !match;
        if (match) count++;
      });
      counter.textContent = `Menampilkan ${count} dari ${rows.length} ${noun}`;
      empty.hidden = count !== 0;
      if (count === 0 && rows.length > 0) {
        if (emptyTitle) emptyTitle.textContent = `${noun.charAt(0).toUpperCase()+noun.slice(1)} tidak ditemukan`;
        if (emptyDesc) emptyDesc.textContent = 'Coba gunakan kata kunci atau filter lain.';
      }
      reset.hidden = !(query || selected || order !== 'id-desc');
    };
    search.addEventListener('input', apply);
    filter.addEventListener('change', apply);
    sort.addEventListener('change', apply);
    reset.addEventListener('click', () => {search.value='';filter.value='';sort.value='id-desc';apply();search.focus();});
    apply();
  });
});
