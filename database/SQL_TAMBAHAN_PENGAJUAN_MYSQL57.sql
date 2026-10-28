-- JR Audio - struktur tambahan saja. Kompatibel MySQL 5.7+.
-- Gunakan ini HANYA jika tidak menjalankan php artisan migrate.
-- Backup database sebelum impor. Tidak ada DELETE/UPDATE pada tabel lama.
CREATE TABLE IF NOT EXISTS `pengajuan_sewa` (
  `id_pengajuan` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(32) NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL,
  `nama_pelanggan` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(30) NOT NULL,
  `jenis_acara` VARCHAR(50) NOT NULL,
  `lokasi_acara` VARCHAR(180) NULL,
  `tanggal_acara` DATE NOT NULL,
  `rekomendasi_paket` VARCHAR(30) NOT NULL,
  `estimasi_min` INT UNSIGNED NULL,
  `estimasi_max` INT UNSIGNED NULL,
  `kebutuhan_json` LONGTEXT NOT NULL,
  `status` ENUM('Menunggu','Revisi','Disetujui','Batal') NOT NULL DEFAULT 'Menunggu',
  `catatan_admin` TEXT NULL,
  `harga_final` INT UNSIGNED NULL,
  `biaya_genset` INT UNSIGNED NOT NULL DEFAULT 0,
  `id_transaksi` INT NULL,
  `diproses_oleh` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id_pengajuan`),
  UNIQUE KEY `pengajuan_sewa_kode_unique` (`kode`),
  UNIQUE KEY `pengajuan_sewa_token_hash_unique` (`token_hash`),
  UNIQUE KEY `pengajuan_sewa_id_transaksi_unique` (`id_transaksi`),
  KEY `pengajuan_sewa_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `pengajuan_sewa_id_transaksi_foreign`
    FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pengajuan_detail` (
  `id_pengajuan_detail` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pengajuan` BIGINT UNSIGNED NOT NULL,
  `id_barang` INT NULL,
  `nama_barang` VARCHAR(150) NOT NULL,
  `peran` VARCHAR(100) NULL,
  `qty` INT UNSIGNED NOT NULL,
  `jumlah_vendor` INT UNSIGNED NOT NULL DEFAULT 0,
  `tipe_item` ENUM('Barang','External') NOT NULL DEFAULT 'Barang',
  `sumber_external` VARCHAR(150) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id_pengajuan_detail`),
  KEY `pengajuan_detail_id_pengajuan_foreign` (`id_pengajuan`),
  KEY `pengajuan_detail_id_barang_foreign` (`id_barang`),
  CONSTRAINT `pengajuan_detail_id_pengajuan_foreign`
    FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_sewa` (`id_pengajuan`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_detail_id_barang_foreign`
    FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
