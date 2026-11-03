-- JR AUDIO v11: DIAGNOSTIK DATABASE (READ-ONLY)
-- Jalankan di DATABASE CADANGAN/CLONE bila masih ragu. Query ini SELECT/SHOW saja.
-- Sesuaikan database name jika bukan jr_audio_dev.
-- JANGAN jalankan migrate:fresh, DROP TABLE, TRUNCATE, atau IMPORT SQL lama ke produksi.

SELECT DATABASE() AS database_aktif, VERSION() AS versi_mysql;
SELECT TABLE_NAME, ENGINE, TABLE_ROWS FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN
 ('barang','pelanggan','karyawan','users','paket','paket_detail','transaksi','detail_transaksi','pembayaran','pengembalian','pengembalian_detail','pengajuan_sewa','pengajuan_detail','mitra','barang_mitra','notifikasi_pembayaran','migrations')
 ORDER BY TABLE_NAME;

-- Sinkronisasi catatan migration dengan struktur database.
SELECT migration,batch FROM migrations ORDER BY batch,migration;
SELECT TABLE_NAME,COLUMN_NAME,DATA_TYPE,COLUMN_TYPE,IS_NULLABLE
 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
 AND ((TABLE_NAME='detail_transaksi' AND COLUMN_NAME IN ('harga_mitra','id_barang_mitra','termasuk_paket','dikembalikan_ke_mitra_at'))
 OR (TABLE_NAME='pengajuan_sewa' AND COLUMN_NAME IN ('id_pelanggan','pilihan_crew','kode'))
 OR (TABLE_NAME='transaksi' AND COLUMN_NAME IN ('alamat_lokasi_acara','link_google_maps')))
 ORDER BY TABLE_NAME,COLUMN_NAME;

-- Transaksi selesai tanpa pengembalian: berisiko stok tidak kembali.
SELECT t.id_transaksi,t.status_transaksi,t.tanggal_sewa FROM transaksi t
 LEFT JOIN pengembalian pg ON pg.id_transaksi=t.id_transaksi
 WHERE t.status_transaksi='Selesai' AND pg.id_pengembalian IS NULL;

-- Pengembalian ganda: harus nol sebelum unique index v7.
SELECT id_transaksi,COUNT(*) AS jumlah_pengembalian FROM pengembalian
 GROUP BY id_transaksi HAVING COUNT(*)>1;

-- Stok negatif / anomali.
SELECT id_barang,nama_barang,stok,stok_disewa,stok_maintenance,status
 FROM barang WHERE stok<0 OR stok_disewa<0 OR stok_maintenance<0;

-- Alokasi stok berbeda dari detail transaksi yang belum selesai.
SELECT b.id_barang,b.nama_barang,b.stok_disewa AS stok_disewa_tersimpan,
 COALESCE(x.total_aktif,0) AS jumlah_tercatat_di_transaksi
 FROM barang b LEFT JOIN (
 SELECT d.id_barang,SUM(d.qty) AS total_aktif FROM detail_transaksi d
 JOIN transaksi t ON t.id_transaksi=d.id_transaksi
 WHERE d.tipe_item='Barang' AND t.status_transaksi IN ('Booking','Berjalan')
 GROUP BY d.id_barang) x ON x.id_barang=b.id_barang
 WHERE b.stok_disewa<>COALESCE(x.total_aktif,0);

-- Pelunasan sewa tidak terkonfirmasi tetapi pengembalian sudah tercatat.
SELECT pg.id_pengembalian,t.id_transaksi,t.total_harga,
 COALESCE(x.dibayar,0) AS sewa_terkonfirmasi
 FROM pengembalian pg JOIN transaksi t ON t.id_transaksi=pg.id_transaksi
 LEFT JOIN (SELECT id_transaksi,SUM(jumlah_bayar) AS dibayar FROM pembayaran
   WHERE status_konfirmasi='Dikonfirmasi' AND jenis_pembayaran IN ('DP','Pelunasan')
   GROUP BY id_transaksi) x ON x.id_transaksi=t.id_transaksi
 WHERE COALESCE(x.dibayar,0) < t.total_harga;

-- Status pesanan disetujui tetapi tidak terhubung ke transaksi.
SELECT id_pengajuan,kode,id_transaksi,status FROM pengajuan_sewa
 WHERE status='Disetujui' AND id_transaksi IS NULL;

-- Pengajuan yang mengacu transaksi tidak ada.
SELECT p.id_pengajuan,p.kode,p.id_transaksi FROM pengajuan_sewa p
 LEFT JOIN transaksi t ON t.id_transaksi=p.id_transaksi
 WHERE p.id_transaksi IS NOT NULL AND t.id_transaksi IS NULL;

-- Item mitra yang biaya pokoknya belum dicatat (NULL BUKAN 0).
SELECT d.id_detail,d.id_transaksi,d.nama_item_external,d.qty,d.harga_satuan,d.harga_mitra
 FROM detail_transaksi d WHERE d.tipe_item='External' AND d.harga_mitra IS NULL;

-- Data pembayaran denda terkonfirmasi tetapi belum ada pengembalian.
SELECT pb.id_pembayaran,pb.id_transaksi,pb.jumlah_bayar FROM pembayaran pb
 LEFT JOIN pengembalian pg ON pg.id_transaksi=pb.id_transaksi
 WHERE pb.jenis_pembayaran='Denda' AND pb.status_konfirmasi='Dikonfirmasi'
 AND pg.id_pengembalian IS NULL;

-- Cek notifikasi pembayaran hanya dibuat dari pembayaran Dikonfirmasi.
SELECT n.id_notifikasi,n.id_pembayaran,n.status,p.status_konfirmasi
 FROM notifikasi_pembayaran n JOIN pembayaran p ON p.id_pembayaran=n.id_pembayaran
 WHERE p.status_konfirmasi<>'Dikonfirmasi';

-- Satu ID pelanggan punya telepon kosong, berisiko duplikat di auto-create.
SELECT id_pelanggan,nama_pelanggan,no_hp FROM pelanggan WHERE no_hp IS NULL OR TRIM(no_hp)='';

-- Barang mitra nonaktif yang masih tampil di katalog dapat diaudit manual.
SELECT bm.id_barang_mitra,bm.nama_barang,bm.status AS status_barang,m.nama_mitra,m.status AS status_mitra
 FROM barang_mitra bm JOIN mitra m ON m.id_mitra=bm.id_mitra
 WHERE bm.status='Aktif' AND m.status='Nonaktif';
