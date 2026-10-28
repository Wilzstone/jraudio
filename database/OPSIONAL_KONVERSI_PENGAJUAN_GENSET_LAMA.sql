-- OPSIONAL: hanya untuk pengajuan Menunggu/Revisi yang dibuat sebelum v6,
-- ketika genset dulu tertulis sebagai External. Backup database dulu.
-- Tidak mengubah transaksi, barang, stok atau pembayaran.
-- Hanya dijalankan apabila ingin memperbaiki pengajuan LAMA.
START TRANSACTION;
UPDATE pengajuan_detail AS d
JOIN pengajuan_sewa AS p ON p.id_pengajuan = d.id_pengajuan
JOIN barang AS b ON b.nama_barang = 'Generator set 9 sd 20 kVA'
SET d.id_barang = b.id_barang,
    d.nama_barang = b.nama_barang,
    d.tipe_item = 'Barang',
    d.sumber_external = NULL,
    d.updated_at = NOW()
WHERE p.status IN ('Menunggu', 'Revisi')
  AND d.id_barang IS NULL
  AND d.peran = 'Daya listrik tambahan'
  AND d.nama_barang = 'Genset (sumber dikonfirmasi crew)';
SELECT ROW_COUNT() AS pengajuan_lama_diperbaiki;
COMMIT;
