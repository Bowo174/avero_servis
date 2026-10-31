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
  `merk` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelengkapan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_barang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `id_pelanggan`, `nama_barang`, `merk`, `model`, `kelengkapan`, `foto_barang`, `created_at`) VALUES
(1, 2, 'LAPTOP', 'ACER', 'ASPIRE 3', 'CHARGER TAS LAPTOP', NULL, '2026-09-16 20:43:19'),
(2, 3, 'LAPTOP', 'ACER', 'ASPIRE 3', 'CHARGER', NULL, '2026-09-16 21:37:27'),
(3, 4, 'LAPTOP', 'ACER', 'ASPIRE 3', 'CHARGER', NULL, '2026-09-16 21:47:24'),
(4, 5, 'LAPTOP', 'ACER', 'ASPIRE 3', 'CHARGER', NULL, '2026-09-16 21:47:31'),
(5, 6, 'LAPTOP', 'ACER', 'ASPIRE 3', 'UNIT TAS CHARGER', NULL, '2026-09-16 21:48:51'),
(6, 7, 'PRINTER', 'EPSON', 'L3210', 'PRINTER ONLY', NULL, '2026-09-16 21:52:22'),
(7, 8, 'LAPTOP', 'aasdfsadf', 'asfsaf', 'asdfasdfasdf', NULL, '2026-10-02 16:54:07'),
(8, 9, 'adsad', 'aDASD', 'AdAD', 'adad', NULL, '2026-10-31 19:48:39');

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

INSERT INTO `detail_servis` (`id_detail_servis`, `id_servis`, `keterangan`, `jumlah`, `harga_satuan`) VALUES
(23, 3, 'ah keder gratis bae', 1, 1),
(38, 5, 'install windows', 1, 40000),
(39, 5, 'NY', 2, 30000);

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

INSERT INTO `notifikasi` (`id_notifikasi`, `id_servis`, `no_tujuan`, `jenis`, `pesan`, `status_kirim`, `waktu_kirim`) VALUES
(1, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Selesai.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(2, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(3, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(4, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(5, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini dalam tahap Menunggu Sparepart.\n\nKami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.', 'menunggu', NULL),
(6, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(7, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(8, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(9, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(10, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini dalam tahap Menunggu Sparepart.\n\nKami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.', 'menunggu', NULL),
(11, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah kami terima.\n\nBarang Anda akan segera kami periksa.', 'menunggu', NULL),
(12, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(13, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(14, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(15, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(16, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(17, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor eeffasdf telah kami terima.\n\nBarang Anda akan segera kami periksa.', 'menunggu', NULL),
(18, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(19, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini dalam tahap Menunggu Sparepart.\n\nKami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.', 'menunggu', NULL),
(20, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(21, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(22, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(23, 4, '0821234567890', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00004 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(24, 1, '0821234567890', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00001 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(25, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(26, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(27, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(28, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(29, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(30, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(31, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah kami terima.\n\nBarang Anda akan segera kami periksa.', 'menunggu', NULL),
(32, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(33, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(34, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(35, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(36, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(37, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(38, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(39, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(40, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(41, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(42, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(43, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(44, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(45, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(46, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(47, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(48, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(49, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(50, 4, '0821234567890', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00004 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(51, 4, '0821234567890', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00004 telah kami terima.\n\nBarang Anda akan segera kami periksa.', 'menunggu', NULL),
(52, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(53, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(54, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(55, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(56, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(57, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(58, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(59, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini dalam tahap Menunggu Sparepart.\n\nKami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.', 'menunggu', NULL),
(60, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini dalam tahap Menunggu Sparepart.\n\nKami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.', 'menunggu', NULL),
(61, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(62, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(63, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(64, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(65, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(66, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(67, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(68, 3, '0821234567890', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00003 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(69, 3, '0821234567890', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00003 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(70, 3, '0821234567890', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00003 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(71, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(72, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(73, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(74, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(75, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(76, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(77, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(78, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(79, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Dalam Pengerjaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(80, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(81, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(82, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah selesai dan sudah dapat diambil.\n\nTerima kasih telah mempercayakan servis kepada Avero Komputer.', 'menunggu', NULL),
(83, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah kami terima.\n\nBarang Anda akan segera kami periksa.', 'menunggu', NULL),
(84, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah kami terima.\n\nBarang Anda akan segera kami periksa.', 'menunggu', NULL),
(85, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 sekarang dalam tahap Pemeriksaan.\n\nAnda dapat memantau perkembangan servis melalui link status servis Avero Komputer.', 'menunggu', NULL),
(86, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(87, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(88, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini menunggu persetujuan sebelum proses servis dapat dilanjutkan.\n\nSilakan hubungi Avero Komputer untuk informasi lebih lanjut.', 'menunggu', NULL),
(89, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 telah kami terima.\n\nBarang Anda akan segera kami periksa.', 'menunggu', NULL),
(90, 6, '23432432', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00006 saat ini dalam tahap Menunggu Sparepart.\n\nKami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.', 'menunggu', NULL),
(91, 6, '23432432', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nStatus servis Anda dengan nomor AVR-00006 telah diperbarui menjadi Sparepart Telah Tiba.', 'menunggu', NULL),
(92, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nStatus servis Anda dengan nomor AVR-00005 telah diperbarui menjadi Sparepart Telah Tiba.', 'menunggu', NULL),
(93, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini dalam tahap Menunggu Sparepart.\n\nKami akan menginformasikan kembali apabila proses servis dapat dilanjutkan.', 'menunggu', NULL),
(94, 5, '082130031750', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00005 saat ini dalam tahap Sparepart Telah Tiba.\n\nSparepart yang diperlukan untuk proses servis telah tiba. Dimohon untuk segera membawa perangkat atau device anda ke toko, supaya proses pengerjaan akan segera kami lanjutkan.', 'menunggu', NULL),
(95, 6, '23432432', 'whatsapp', 'Assalamu\'alaikum.. Sahabat Avero Komputer,\n\nServis Anda dengan nomor AVR-00006 saat ini dalam tahap Sparepart Telah Tiba.\n\nSparepart yang diperlukan untuk proses servis telah tiba. Dimohon untuk segera membawa perangkat atau device anda ke toko, supaya proses pengerjaan segera kami lanjutkan.', 'menunggu', NULL);

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

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `alamat`, `no_telp`, `created_at`) VALUES
(1, 'jani', 'kadipaten majalengka', '08213201410', '2026-09-16 20:14:16'),
(2, 'BUDI', 'KARANGSAMBUNG MAJALENGKA', '0821234567890', '2026-09-16 20:43:19'),
(3, 'ANDI', 'PASAR BALONG MAJALENGKA', '0821234567890', '2026-09-16 21:37:27'),
(4, 'ANDI', 'PASAR BALONG MAJALENGKA', '0821234567890', '2026-09-16 21:47:24'),
(5, 'ANDI', 'PASAR BALONG MAJALENGKA', '0821234567890', '2026-09-16 21:47:31'),
(6, 'ASEP', 'CIGASONG MAJALENGKA', '0821234567890', '2026-09-16 21:48:51'),
(7, 'jamal', 'MAJAELNGKA', '082130031750', '2026-09-16 21:52:22'),
(8, 'asfdsa', 'asffsf', '23432432', '2026-10-02 16:54:07'),
(9, 'sdafasd', 'ADAD', '083833142740', '2026-10-31 19:48:39');

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

INSERT INTO `riwayat_status` (`id_riwayat`, `id_servis`, `status`, `catatan`, `id_user`, `created_at`) VALUES
(5, 5, 'Diterima', 'Servis diterima oleh Avero Komputer.', NULL, '2026-09-16 21:52:22'),
(6, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', NULL, '2026-09-16 23:47:51'),
(7, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', NULL, '2026-09-17 06:38:16'),
(8, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-17 07:38:25'),
(9, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-17 07:55:41'),
(10, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-17 10:04:38'),
(11, 5, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-09-17 10:17:38'),
(12, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-18 13:28:32'),
(13, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-18 13:38:57'),
(14, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-18 13:58:10'),
(15, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-18 14:12:31'),
(16, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-18 14:13:49'),
(17, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-18 14:14:49'),
(18, 5, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-09-18 14:17:09'),
(19, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-23 13:56:47'),
(20, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-23 14:02:30'),
(21, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 11:45:59'),
(22, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 12:32:58'),
(23, 5, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-09-24 12:37:43'),
(24, 5, 'Diterima', 'Status servis diubah menjadi Diterima', 1, '2026-09-24 12:37:56'),
(25, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 12:38:06'),
(26, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 12:39:04'),
(27, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 12:39:06'),
(28, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 12:39:07'),
(29, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 12:39:09'),
(30, 5, 'Diterima', 'Status servis diubah menjadi Diterima', 1, '2026-09-24 12:40:11'),
(31, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-09-24 12:40:20'),
(32, 5, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-09-24 12:50:45'),
(33, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 12:50:58'),
(34, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 12:58:45'),
(35, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 12:58:50'),
(36, 4, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 13:00:57'),
(37, 1, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 13:01:23'),
(38, 5, 'Sudah Diambil', 'Status servis diubah menjadi Sudah Diambil', 1, '2026-09-24 13:02:39'),
(39, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-09-24 13:02:41'),
(40, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 13:02:44'),
(41, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 13:06:59'),
(42, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 13:24:51'),
(43, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 13:24:53'),
(44, 5, 'Diterima', 'Status servis diubah menjadi Diterima', 1, '2026-09-24 14:00:24'),
(45, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 14:01:13'),
(46, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 14:01:31'),
(47, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 14:49:01'),
(48, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 14:49:09'),
(49, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 14:49:13'),
(50, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 15:48:32'),
(51, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 15:49:03'),
(52, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 15:49:07'),
(53, 5, 'Sudah Diambil', 'Status servis diubah menjadi Sudah Diambil', 1, '2026-09-24 15:59:12'),
(54, 5, 'Sudah Diambil', 'Status servis diubah menjadi Sudah Diambil', 1, '2026-09-24 15:59:31'),
(55, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 15:59:33'),
(56, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 16:06:53'),
(57, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 16:09:13'),
(58, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:09:21'),
(59, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:11:26'),
(60, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:11:47'),
(61, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:14:53'),
(62, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:14:57'),
(63, 4, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-09-24 16:15:44'),
(64, 4, 'Diterima', 'Status servis diubah menjadi Diterima', 1, '2026-09-24 16:15:47'),
(65, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 16:19:37'),
(66, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 16:19:41'),
(67, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 16:19:43'),
(68, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-09-24 16:19:48'),
(69, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-09-24 16:20:12'),
(70, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-09-24 16:20:26'),
(71, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-09-24 16:20:30'),
(72, 5, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-09-24 16:20:32'),
(73, 5, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-09-24 16:21:35'),
(74, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 16:21:39'),
(75, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 16:23:20'),
(76, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:26:19'),
(77, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:26:31'),
(78, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:27:30'),
(79, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-24 16:28:25'),
(80, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:34:33'),
(81, 3, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:37:43'),
(82, 3, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:53:58'),
(83, 3, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-24 16:57:54'),
(84, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 12:49:56'),
(85, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 12:49:58'),
(86, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 12:49:58'),
(87, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 12:49:58'),
(88, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 12:49:59'),
(89, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 13:11:38'),
(90, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 16:42:44'),
(91, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-27 16:42:46'),
(92, 5, 'Dalam Pengerjaan', 'Status servis diubah menjadi Dalam Pengerjaan', 1, '2026-09-28 18:21:48'),
(93, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-29 11:18:25'),
(94, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-29 12:05:43'),
(95, 5, 'Selesai', 'Status servis diubah menjadi Selesai', 1, '2026-09-29 12:06:44'),
(96, 5, 'Diterima', 'Status servis diubah menjadi Diterima', 1, '2026-10-01 17:04:48'),
(97, 5, 'Diterima', 'Status servis diubah menjadi Diterima', 1, '2026-10-01 17:04:50'),
(98, 5, 'Pemeriksaan', 'Status servis diubah menjadi Pemeriksaan', 1, '2026-10-02 16:49:07'),
(99, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-10-02 16:49:09'),
(100, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-10-02 16:49:13'),
(101, 5, 'Menunggu Persetujuan', 'Status servis diubah menjadi Menunggu Persetujuan', 1, '2026-10-02 16:49:14'),
(102, 5, 'Diterima', 'Status servis diubah menjadi Diterima', 1, '2026-10-02 16:50:48'),
(103, 6, 'Diterima', 'Servis diterima oleh Avero Komputer.', NULL, '2026-10-02 16:54:07'),
(104, 6, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-10-02 16:54:19'),
(105, 6, 'Sparepart Telah Tiba', 'Status servis diubah menjadi Sparepart Telah Tiba', 1, '2026-10-02 17:08:06'),
(106, 5, 'Sparepart Telah Tiba', 'Status servis diubah menjadi Sparepart Telah Tiba', 1, '2026-10-02 17:08:32'),
(107, 5, 'Menunggu Sparepart', 'Status servis diubah menjadi Menunggu Sparepart', 1, '2026-10-02 17:51:20'),
(108, 5, 'Sparepart Telah Tiba', 'Status servis diubah menjadi Sparepart Telah Tiba', 1, '2026-10-02 17:51:22'),
(109, 6, 'Sparepart Telah Tiba', 'Status servis diubah menjadi Sparepart Telah Tiba', 1, '2026-10-03 13:39:23'),
(110, 7, 'Diterima', 'Servis diterima oleh Avero Komputer.', NULL, '2026-10-31 19:48:39');

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

INSERT INTO `servis` (`id_servis`, `nomor_servis`, `id_pelanggan`, `id_barang`, `tanggal_masuk`, `keluhan`, `status_servis`, `qr_token`, `syarat_setuju`, `tanggal_selesai`, `tanggal_diambil`, `created_at`, `updated_at`) VALUES
(1, 'AVR-00001', 3, 2, '2026-09-16 21:37:27', 'LAPTOP TIDAK NYALA', 'Selesai', '60d54e120998198b36176e5a712c0db508121608dced83a7d3e0d51a2693445e', 0, NULL, NULL, '2026-09-16 21:37:27', '2026-09-24 13:01:23'),
(2, 'AVR-00002', 4, 3, '2026-09-16 21:47:24', 'LAPTOP TIDAK NYALA', 'Diterima', '71906f7b85e57d8a659c2e87a53cdeaf05017d9cb30e2055ac6e10899ec59caa', 0, NULL, NULL, '2026-09-16 21:47:24', '2026-09-16 21:47:24'),
(3, 'AVR-00003', 5, 4, '2026-09-16 21:47:31', 'LAPTOP TIDAK NYALA', 'Selesai', '8743d6d56d074130c72e6ce09671bf8767fa92bd9a8dc751f03e0d1bf879e3ed', 0, NULL, NULL, '2026-09-16 21:47:31', '2026-09-24 16:37:42'),
(4, 'AVR-00004', 6, 5, '2026-09-16 21:48:51', 'TIDAK NAMPIL', 'Diterima', 'e3dda27c764a519b64a0b5e69254aae072f4c708ac7bda0e61b7abf1c6897a5b', 0, NULL, NULL, '2026-09-16 21:48:51', '2026-09-24 16:15:47'),
(5, 'AVR-00005', 7, 6, '2026-09-16 21:52:22', 'TIDAK BISA NARIK KERTAS', 'Sparepart Telah Tiba', 'c947ef56b7c4df66636a20bf79a1f919f5d56e67112e81101e931bbea3e4653e', 0, NULL, NULL, '2026-09-16 21:52:22', '2026-10-02 17:51:22'),
(6, 'AVR-00006', 8, 7, '2026-10-02 16:54:07', 'asdfasf', 'Sparepart Telah Tiba', 'd846a185241ad872db25a6060662fc2eef2044fbd0ed8a745a4c660505522915', 0, NULL, NULL, '2026-10-02 16:54:07', '2026-10-02 17:08:06'),
(7, 'AVR-00007', 9, 8, '2026-10-31 19:48:39', 'ADadad', 'Diterima', '4640e28f9306ae7ec75610be144b4498107d793135a0c3c606e057835a3236ef', 0, NULL, NULL, '2026-10-31 19:48:39', '2026-10-31 19:48:39');

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
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `detail_servis`
--
ALTER TABLE `detail_servis`
  MODIFY `id_detail_servis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `foto_servis`
--
ALTER TABLE `foto_servis`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pengaturan_toko`
--
ALTER TABLE `pengaturan_toko`
  MODIFY `id_pengaturan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `servis`
--
ALTER TABLE `servis`
  MODIFY `id_servis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
