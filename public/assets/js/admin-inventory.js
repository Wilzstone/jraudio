/* Inventory filters and sorting. Works on existing HTML only; does not send requests or mutate the database. */
document.addEventListener('DOMContentLoaded', () => {
    const body = document.getElementById('jaBarangBody');
    if (!body) return;
    const search = document.getElementById('jaBarangSearch');
    const category = document.getElementById('jaBarangCategory');
    const availability = document.getElementById('jaBarangAvailability');
    const sort = document.getElementById('jaBarangSort');
    const result = document.getElementById('jaBarangCount');
    const empty = document.getElementById('jaBarangEmpty');
    const reset = document.getElementById('jaBarangReset');
    const rows = Array.from(body.querySelectorAll('tr.ja-item-row'));
    const norm = (value) => String(value ?? '').trim().toLocaleLowerCase('id-ID');
    const total = rows.length;

    function apply() {
        const q = norm(search.value);
        const cat = norm(category.value);
        const condition = availability.value;
        const order = sort.value;
        const sorted = [...rows];
        sorted.sort((a, b) => {
            if (order === 'name-asc') return a.dataset.name.localeCompare(b.dataset.name, 'id');
            if (order === 'price-asc') return Number(a.dataset.price) - Number(b.dataset.price);
            if (order === 'price-desc') return Number(b.dataset.price) - Number(a.dataset.price);
            if (order === 'stock-desc') return Number(b.dataset.stock) - Number(a.dataset.stock);
            return Number(b.dataset.id) - Number(a.dataset.id);
        });
        sorted.forEach(row => body.appendChild(row));
        let count = 0;
        rows.forEach(row => {
            const stock = Number(row.dataset.stock);
            const maintenance = Number(row.dataset.maintenance);
            const matches = (!q || norm(row.dataset.search).includes(q))
                && (!cat || norm(row.dataset.category) === cat)
                && (!condition || (condition === 'available' && stock > 0)
                    || (condition === 'out' && stock === 0)
                    || (condition === 'maintenance' && maintenance > 0));
            row.hidden = !matches;
            if (matches) count++;
        });
        result.textContent = `Menampilkan ${count} dari ${total} barang`;
        empty.hidden = count > 0;
        reset.hidden = !(q || cat || condition || order !== 'default');
    }

    [search, category, availability, sort].forEach(control => control.addEventListener(control === search ? 'input' : 'change', apply));
    reset.addEventListener('click', () => {
        search.value = '';
        category.value = '';
        availability.value = '';
        sort.value = 'default';
        apply();
        search.focus();
    });
    apply();
});
