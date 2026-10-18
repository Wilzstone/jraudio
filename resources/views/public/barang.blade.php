@include('public.header')

<section class="page-header page-header-gradient">
    <span class="eyebrow">Katalog Barang</span>
    <h1>Daftar Barang</h1>
    <p>Pilih kebutuhan sound system sesuai acara kamu.</p>
</section>

<section class="section">
    <div class="card-container">
        <?php if (!empty($barang)) : ?>
            <?php foreach ($barang as $item) : ?>
                <?php
                    $namaBarang = $item['nama_barang'] ?? 'barang JR Audio';
                    $pesanWa = rawurlencode('Hai, apakah stok barang ' . $namaBarang . ' masih ada? Saya mau pesan ' . $namaBarang . '.');
                    $waBarangUrl = 'https://wa.me/6282331745445?text=' . $pesanWa;
                ?>
                <div class="card">
                    <h3><?= htmlspecialchars($item['nama_barang']); ?></h3>
                    <p>Kategori: <?= htmlspecialchars($item['kategori']); ?></p>
                    <p>Stok tersedia: <?= htmlspecialchars($item['stok']); ?></p>
                    <p>Status: <?= htmlspecialchars($item['status']); ?></p>
                    <p class="price">Rp <?= number_format($item['harga_sewa'], 0, ',', '.'); ?></p>
                    <a class="btn-small" href="<?= htmlspecialchars($waBarangUrl); ?>" target="_blank" rel="noopener">Tanya / Pesan via WhatsApp</a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Belum ada barang tersedia.</p>
        <?php endif; ?>
    </div>
</section>

@include('public.footer')
