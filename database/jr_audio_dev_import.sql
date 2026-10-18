-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 01, 2026 at 07:51 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jr_audio`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `harga_sewa` int NOT NULL DEFAULT '0',
  `stok` int NOT NULL DEFAULT '0',
  `stok_disewa` int NOT NULL DEFAULT '0',
  `stok_maintenance` int NOT NULL DEFAULT '0',
  `status` enum('Tersedia','Disewa','Maintenance') NOT NULL DEFAULT 'Tersedia',
  `gambar` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `kategori`, `harga_sewa`, `stok`, `stok_disewa`, `stok_maintenance`, `status`, `gambar`) VALUES
(1, 'Mixer 24 Ch', 'Mixer', 250000, 1, 0, 0, 'Tersedia', '1780332139_WhatsApp_Image_2026-06-01_at_20.53.10.jpeg'),
(2, 'Speaker Huper 15 inch', 'Speaker', 175000, 4, 0, 0, 'Tersedia', '1780332123_WhatsApp_Image_2026-06-01_at_20.53.11.jpeg'),
(3, 'Mic wireless', 'Microphone', 150000, 2, 0, 0, 'Tersedia', '1780332108_WhatsApp_Image_2026-06-01_at_20.53.12.jpeg'),
(4, 'Speaker Array', 'Speaker', 200000, 8, 0, 0, 'Tersedia', '1780332094_WhatsApp_Image_2026-06-01_at_20.53.12__1_.jpeg'),
(5, 'Subwoofer 18 inch', 'Subwoofer', 250000, 8, 0, 0, 'Tersedia', '1780332078_WhatsApp_Image_2026-06-01_at_20.53.28.jpeg'),
(6, 'Generator set 9 sd 20 kVA', 'Genset', 400000, 1, 0, 0, 'Tersedia', '1780332058_WhatsApp_Image_2026-06-01_at_20.55.52.jpeg'),
(7, 'Stand mic', 'Stand', 25000, 6, 0, 0, 'Tersedia', '1780332043_WhatsApp_Image_2026-06-01_at_21.02.28.jpeg'),
(8, 'Power amplifier 2 channel', 'Amplifier', 180000, 2, 0, 0, 'Tersedia', '1780332031_WhatsApp_Image_2026-06-01_at_21.02.28__1_.jpeg'),
(9, 'Power amplifier 4 Channel', 'Amplifier', 250000, 2, 0, 0, 'Tersedia', '1780332018_WhatsApp_Image_2026-06-01_at_21.02.29.jpeg'),
(10, 'Digital Loudspeaker Management System (DLMS)', 'Processor', 150000, 2, 0, 0, 'Tersedia', '1780332002_WhatsApp_Image_2026-06-01_at_21.07.59.jpeg'),
(11, 'Crossover', 'Processor', 100000, 1, 0, 0, 'Tersedia', '1780331979_WhatsApp_Image_2026-06-01_at_21.08.00.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `id_barang` int DEFAULT NULL,
  `nama_item_external` varchar(150) DEFAULT NULL,
  `sumber_external` varchar(150) DEFAULT NULL,
  `qty` int NOT NULL,
  `harga_satuan` int NOT NULL DEFAULT '0',
  `subtotal` int NOT NULL DEFAULT '0',
  `tipe_item` enum('Barang','External') NOT NULL DEFAULT 'Barang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_barang`, `nama_item_external`, `sumber_external`, `qty`, `harga_satuan`, `subtotal`, `tipe_item`) VALUES
(1, 1, 5, NULL, NULL, 4, 0, 0, 'Barang'),
(2, 1, 4, NULL, NULL, 4, 0, 0, 'Barang'),
(3, 1, 2, NULL, NULL, 2, 0, 0, 'Barang'),
(4, 1, 3, NULL, NULL, 2, 0, 0, 'Barang'),
(5, 1, 1, NULL, NULL, 1, 0, 0, 'Barang'),
(6, 1, 7, NULL, NULL, 6, 0, 0, 'Barang'),
(7, 1, NULL, 'Tenda', 'LaKi Tenda', 1, 0, 0, 'External'),
(8, 1, 6, NULL, NULL, 1, 0, 0, 'Barang'),
(9, 2, 4, NULL, NULL, 2, 200000, 400000, 'Barang'),
(10, 2, 6, NULL, NULL, 1, 0, 0, 'Barang');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `alamat` text,
  `jabatan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `id_user`, `nama_karyawan`, `no_hp`, `alamat`, `jabatan`) VALUES
(1, 1, 'Owner JR Audio', '081234567890', 'Yogyakarta', 'Owner'),
(2, 2, 'Operator Lapangan', '081234567892', 'Yogyakarta', 'Operator');

-- --------------------------------------------------------

--
-- Table structure for table `paket`
--

CREATE TABLE `paket` (
  `id_paket` int NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text,
  `range_harga` varchar(100) DEFAULT NULL,
  `gambar` varchar(150) DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paket`
--

INSERT INTO `paket` (`id_paket`, `nama_paket`, `deskripsi`, `range_harga`, `gambar`, `status`) VALUES
(1, 'Paket 2500', 'Untuk seminar, acara indoor, makrab, dan live music.', 'Rp 600.000 - Rp 800.000', 'paket-basic.svg', 'Aktif'),
(2, 'Paket 5000', 'Untuk wedding, 17-an, dangdutan, jathilan, wayang, misa gereja, takbiran, open ceremony, wisuda/kelulusan.', 'Rp 900.000 - Rp 1.200.000', 'paket-premium.svg', 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `paket_detail`
--

CREATE TABLE `paket_detail` (
  `id_paket_detail` int NOT NULL,
  `id_paket` int NOT NULL,
  `id_barang` int NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paket_detail`
--

INSERT INTO `paket_detail` (`id_paket_detail`, `id_paket`, `id_barang`, `qty`, `keterangan`) VALUES
(1, 1, 5, 2, 'Subwoofer 2 unit'),
(2, 1, 4, 2, 'Array / middle 2 unit'),
(3, 1, 2, 1, 'Speaker monitor 1 unit'),
(4, 1, 3, 1, 'Mic wireless 1 set / 2 mic'),
(5, 1, 1, 1, 'Mixer 24 channel'),
(6, 1, 7, 6, 'Stand mic include'),
(7, 2, 5, 4, 'Subwoofer 4 unit'),
(8, 2, 4, 4, 'Array / speaker middle 4 unit'),
(9, 2, 2, 2, 'Speaker monitor 2 unit'),
(10, 2, 3, 2, 'Mic wireless 2 set / 4 pcs'),
(11, 2, 1, 1, 'Mixer 24 channel'),
(12, 2, 7, 6, 'Stand mic include');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `alamat` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `no_hp`, `alamat`) VALUES
(1, 'Budi Santoso', '081111111111', 'Yogyakarta'),
(2, 'Jodi Saputra', '089662414908', 'Yogyakarta'),
(3, 'Burju Efraim', '087715980813', 'Yogyakarta');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` int NOT NULL,
  `jenis_pembayaran` enum('DP','Pelunasan') NOT NULL,
  `status_konfirmasi` enum('Menunggu','Dikonfirmasi','Ditolak') NOT NULL DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_transaksi`, `tanggal_bayar`, `jumlah_bayar`, `jenis_pembayaran`, `status_konfirmasi`) VALUES
(1, 1, '2026-06-01', 1400000, 'Pelunasan', 'Dikonfirmasi'),
(2, 1, '2026-06-01', 50000, 'Pelunasan', 'Dikonfirmasi'),
(3, 2, '2026-06-01', 800000, 'Pelunasan', 'Dikonfirmasi');

-- --------------------------------------------------------

--
-- Table structure for table `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id_pengembalian` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `tanggal_pengembalian` date NOT NULL,
  `kondisi_barang` text,
  `denda` int NOT NULL DEFAULT '0',
  `denda_manual` int NOT NULL DEFAULT '0',
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengembalian`
--

INSERT INTO `pengembalian` (`id_pengembalian`, `id_transaksi`, `tanggal_pengembalian`, `kondisi_barang`, `denda`, `denda_manual`, `catatan`) VALUES
(1, 1, '2026-06-09', 'semua baik', 50000, 0, ''),
(2, 2, '2026-06-15', 'semua', 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `pengembalian_detail`
--

CREATE TABLE `pengembalian_detail` (
  `id_pengembalian_detail` int NOT NULL,
  `id_pengembalian` int NOT NULL,
  `id_detail` int NOT NULL,
  `id_barang` int NOT NULL,
  `qty_kembali_baik` int NOT NULL DEFAULT '0',
  `qty_rusak` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengembalian_detail`
--

INSERT INTO `pengembalian_detail` (`id_pengembalian_detail`, `id_pengembalian`, `id_detail`, `id_barang`, `qty_kembali_baik`, `qty_rusak`) VALUES
(1, 1, 1, 5, 4, 0),
(2, 1, 2, 4, 4, 0),
(3, 1, 3, 2, 2, 0),
(4, 1, 4, 3, 2, 0),
(5, 1, 5, 1, 1, 0),
(6, 1, 6, 7, 6, 0),
(7, 1, 8, 6, 1, 0),
(8, 2, 9, 4, 2, 0),
(9, 2, 10, 6, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `id_pelanggan` int NOT NULL,
  `id_karyawan` int NOT NULL,
  `jenis_transaksi` enum('Satuan','Paketan') NOT NULL DEFAULT 'Satuan',
  `id_paket` int DEFAULT NULL,
  `nama_paket` varchar(100) DEFAULT NULL,
  `harga_paket_manual` int NOT NULL DEFAULT '0',
  `biaya_genset` int NOT NULL DEFAULT '0',
  `tanggal_transaksi` date NOT NULL,
  `tanggal_sewa` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `status_transaksi` enum('Booking','Berjalan','Selesai','Batal') NOT NULL DEFAULT 'Booking',
  `total_harga` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pelanggan`, `id_karyawan`, `jenis_transaksi`, `id_paket`, `nama_paket`, `harga_paket_manual`, `biaya_genset`, `tanggal_transaksi`, `tanggal_sewa`, `tanggal_kembali`, `status_transaksi`, `total_harga`) VALUES
(1, 1, 1, 'Paketan', 2, 'Paket 5000', 1000000, 400000, '2026-06-01', '2026-06-05', '2026-06-08', 'Selesai', 1400000),
(2, 2, 1, 'Satuan', NULL, NULL, 0, 400000, '2026-06-01', '2026-06-12', '2026-06-15', 'Selesai', 800000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Owner','Karyawan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'owner', '$2y$12$.6ls09ox5FHtwF6g9YsQWuQglOrVFRv4FulbjaWtDxRXiqyoE/bVq', 'Owner'),
(2, 'karyawan', '$2y$12$.6ls09ox5FHtwF6g9YsQWuQglOrVFRv4FulbjaWtDxRXiqyoE/bVq', 'Karyawan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id_paket`);

--
-- Indexes for table `paket_detail`
--
ALTER TABLE `paket_detail`
  ADD PRIMARY KEY (`id_paket_detail`),
  ADD KEY `id_paket` (`id_paket`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id_pengembalian`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `pengembalian_detail`
--
ALTER TABLE `pengembalian_detail`
  ADD PRIMARY KEY (`id_pengembalian_detail`),
  ADD KEY `id_pengembalian` (`id_pengembalian`),
  ADD KEY `id_detail` (`id_detail`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_karyawan` (`id_karyawan`),
  ADD KEY `id_paket` (`id_paket`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id_karyawan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `paket`
--
ALTER TABLE `paket`
  MODIFY `id_paket` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `paket_detail`
--
ALTER TABLE `paket_detail`
  MODIFY `id_paket_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id_pengembalian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengembalian_detail`
--
ALTER TABLE `pengembalian_detail`
  MODIFY `id_pengembalian_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `paket_detail`
--
ALTER TABLE `paket_detail`
  ADD CONSTRAINT `paket_detail_ibfk_1` FOREIGN KEY (`id_paket`) REFERENCES `paket` (`id_paket`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `paket_detail_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pengembalian_detail`
--
ALTER TABLE `pengembalian_detail`
  ADD CONSTRAINT `pengembalian_detail_ibfk_1` FOREIGN KEY (`id_pengembalian`) REFERENCES `pengembalian` (`id_pengembalian`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pengembalian_detail_ibfk_2` FOREIGN KEY (`id_detail`) REFERENCES `detail_transaksi` (`id_detail`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pengembalian_detail_ibfk_3` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`id_paket`) REFERENCES `paket` (`id_paket`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
