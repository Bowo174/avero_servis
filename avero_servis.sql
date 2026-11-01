-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 31, 2026 at 03:41 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `avero_servis`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `nama_barang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelengkapan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_barang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_servis`
--

CREATE TABLE `detail_servis` (
  `id_detail_servis` int(11) NOT NULL,
  `id_servis` int(11) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `harga_satuan` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_servis`
--

-- --------------------------------------------------------

--
-- Table structure for table `foto_servis`
--

CREATE TABLE `foto_servis` (
  `id_foto` int(11) NOT NULL,
  `id_servis` int(11) NOT NULL,
  `file_foto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_servis` int(11) NOT NULL,
  `no_tujuan` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_kirim` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu',
  `waktu_kirim` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelanggan`
--

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_toko`
--

CREATE TABLE `pengaturan_toko` (
  `id_pengaturan` int(11) NOT NULL,
  `nama_toko` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `syarat_ketentuan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaturan_toko`
--

INSERT INTO `pengaturan_toko` (`id_pengaturan`, `nama_toko`, `alamat`, `no_telp`, `logo`, `syarat_ketentuan`, `footer`, `created_at`, `updated_at`) VALUES
(1, 'AVERO KOMPUTER', 'Jl. Pasar Balong No 05 Kadipaten Majalengka\r\n', '0852 1818 2280', 'logo_1790656809.png', 'PERHATIAN : BARANG YANG TELAH SELESAI DI SERVIS ATAU GAGAL, DAN TIDAK DIAMBIL DALAM TEMPO 2 (DUA) BULAN. BUKAN TANGGUNG JAWAB KAMI.\r\n\r\n\r\n\r\n\r\n\r\n\r\n', 'Terimakasih telah mempercayakan servis kepada Avero komputer', '2026-09-17 12:04:20', '2026-09-29 11:40:09');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_status`
--

CREATE TABLE `riwayat_status` (
  `id_riwayat` int(11) NOT NULL,
  `id_servis` int(11) NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riwayat_status`
--

-- --------------------------------------------------------

--
-- Table structure for table `servis`
--

CREATE TABLE `servis` (
  `id_servis` int(11) NOT NULL,
  `nomor_servis` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `tanggal_masuk` datetime NOT NULL DEFAULT current_timestamp(),
  `keluhan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_servis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Diterima',
  `qr_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `syarat_setuju` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal_selesai` datetime DEFAULT NULL,
  `tanggal_diambil` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `servis`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admin',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `aktif`, `created_at`) VALUES
(1, 'Admin Avero', 'admin', '$2y$10$68oQYi3.wNiuWk3lXJ0MpuTZaL3n2xG3uiw1J/c9Gi0T/NKXc9YZe', 'Admin', 1, '2026-09-17 06:53:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `fk_barang_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `detail_servis`
--
ALTER TABLE `detail_servis`
  ADD PRIMARY KEY (`id_detail_servis`),
  ADD KEY `idx_detail_servis_id_servis` (`id_servis`);

--
-- Indexes for table `foto_servis`
--
ALTER TABLE `foto_servis`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `fk_foto_servis` (`id_servis`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `fk_notifikasi_servis` (`id_servis`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `pengaturan_toko`
--
ALTER TABLE `pengaturan_toko`
  ADD PRIMARY KEY (`id_pengaturan`);

--
-- Indexes for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `fk_riwayat_servis` (`id_servis`),
  ADD KEY `fk_riwayat_user` (`id_user`);

--
-- Indexes for table `servis`
--
ALTER TABLE `servis`
  ADD PRIMARY KEY (`id_servis`),
  ADD UNIQUE KEY `nomor_servis` (`nomor_servis`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD KEY `fk_servis_pelanggan` (`id_pelanggan`),
  ADD KEY `fk_servis_barang` (`id_barang`);

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
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_servis`
--
ALTER TABLE `detail_servis`
  MODIFY `id_detail_servis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `foto_servis`
--
ALTER TABLE `foto_servis`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengaturan_toko`
--
ALTER TABLE `pengaturan_toko`
  MODIFY `id_pengaturan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servis`
--
ALTER TABLE `servis`
  MODIFY `id_servis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_servis`
--
ALTER TABLE `detail_servis`
  ADD CONSTRAINT `fk_detail_servis_servis` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `foto_servis`
--
ALTER TABLE `foto_servis`
  ADD CONSTRAINT `fk_foto_servis` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_servis` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD CONSTRAINT `fk_riwayat_servis` FOREIGN KEY (`id_servis`) REFERENCES `servis` (`id_servis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_riwayat_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `servis`
--
ALTER TABLE `servis`
  ADD CONSTRAINT `fk_servis_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_servis_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
