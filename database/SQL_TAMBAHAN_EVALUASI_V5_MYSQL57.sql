-- JR Audio v5 - hanya untuk database yang SUDAH punya tabel pengajuan.
-- Pilih salah satu: php artisan migrate ATAU SQL ini; jangan keduanya.
-- Backup dahulu. Skrip mengubah SKEMA, tidak menghapus/mengganti data lama.
ALTER TABLE `pengajuan_sewa`
  ADD COLUMN `id_pelanggan` INT NULL AFTER `id_pengajuan`,
  ADD KEY `idx_pengajuan_id_pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `fk_jr_pengajuan_pelanggan` FOREIGN KEY (`id_pelanggan`)
    REFERENCES `pelanggan`(`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `transaksi`
  ADD COLUMN `alamat_lokasi_acara` TEXT NULL,
  ADD COLUMN `link_google_maps` VARCHAR(2048) NULL;
