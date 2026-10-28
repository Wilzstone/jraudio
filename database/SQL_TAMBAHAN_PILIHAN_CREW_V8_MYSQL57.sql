-- OPSIONAL jika tidak menggunakan php artisan migrate. Pilih SATU cara instalasi.
-- Backup database terlebih dahulu. Jangan jalankan jika kolom telah ada.
ALTER TABLE `pengajuan_sewa` ADD COLUMN `pilihan_crew` VARCHAR(20) NULL AFTER `rekomendasi_paket`;
