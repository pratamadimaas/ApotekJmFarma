-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 13 Des 2025 pada 09.36
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apotek`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_barang` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_barang` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_beli` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stok` int NOT NULL DEFAULT '0',
  `stok_minimal` int NOT NULL DEFAULT '10',
  `stok_minimum` int NOT NULL DEFAULT '10',
  `lokasi_rak` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `satuan_terkecil` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tablet',
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cabang_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `barcode`, `nama_barang`, `kategori`, `harga_beli`, `harga_jual`, `stok`, `stok_minimal`, `stok_minimum`, `lokasi_rak`, `satuan_terkecil`, `tanggal_kadaluarsa`, `deskripsi`, `aktif`, `created_at`, `updated_at`, `cabang_id`) VALUES
(1, 'OBT-0001', NULL, 'Paracetamol 500mg', 'Obat Umum', 1000.00, 2000.00, 995, 10, 100, NULL, 'tablet', '2027-12-05', 'Obat penurun panas dan pereda nyeri', 1, '2025-12-05 07:03:20', '2025-12-10 05:26:32', 1),
(2, 'OBT-0002', '12345678', 'Amoxicillin 500mg', 'Antibiotik', 1000.00, 1200.00, 1447, 10, 50, 'Rak A-1', 'Pcs', '2026-12-05', 'Antibiotik untuk infeksi bakteri', 1, '2025-12-05 07:03:20', '2025-12-13 05:40:36', 1),
(3, 'OBT-0003', NULL, 'Vitamin C 1000mg', 'Vitamin', 800.00, 1040.00, 800, 10, 80, NULL, 'tablet', '2027-06-05', 'Suplemen vitamin C dosis tinggi', 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20', 1),
(4, 'OBT-0004', NULL, 'Obat Batuk Hitam (OBH)', 'Obat Batuk', 8000.00, 10400.00, 60, 10, 10, 'Rak Kiri', 'Pcs', '2027-12-05', 'Sirup obat batuk 60ml', 1, '2025-12-05 07:03:20', '2025-12-10 03:47:27', 1),
(5, 'OBT-0005', '23456789', 'Mylanta', 'Obat Umum', 5000.00, 6000.00, 217, 10, 10, 'Rak Kanan', 'Dos', NULL, NULL, 1, '2025-12-06 05:15:18', '2025-12-10 12:20:41', 1),
(6, 'OBT-00010', NULL, 'Promag', 'Obat Maag', 5000.00, 6000.00, 10, 10, 10, 'Rak A-2', 'Dos', NULL, NULL, 1, '2025-12-07 09:53:27', '2025-12-07 09:53:27', 1),
(11, 'BRG001', '8992772311212', 'Betadine', 'Obat', 1000.00, 7000.00, 118, 10, 10, 'Rak A1', 'Strip', NULL, 'Obat demam', 1, '2025-12-11 00:59:01', '2025-12-13 04:35:23', 2),
(13, 'BRG002', '8992772311229', 'Obat Ganteng', 'Obat', 8000.00, 12000.00, 48, 10, 10, 'Rak A2', 'Strip', NULL, 'Antibiotik', 1, '2025-12-11 07:35:31', '2025-12-13 04:37:21', 2),
(30, '111', '123456', 'a', 'a', 1000.00, 2000.00, 8, 0, 10, NULL, 'a', NULL, NULL, 1, '2025-12-11 11:29:07', '2025-12-11 11:36:09', 2),
(32, 'BRG003', '8992772311213', 'Obat Kaki', 'Obat', 5000.00, 7000.00, 100, 10, 10, 'Rak A1', 'Strip', NULL, 'Obat demam', 1, '2025-12-11 11:54:34', '2025-12-11 11:54:34', 2),
(33, 'BRG004', '8992772311224', 'Obat Rambut', 'Obat', 8000.00, 12000.00, 50, 10, 10, 'Rak A2', 'Strip', NULL, 'Antibiotik', 1, '2025-12-11 11:54:34', '2025-12-11 11:54:34', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cabang`
--

CREATE TABLE `cabang` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_cabang` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_cabang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penanggung_jawab` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cabang`
--

INSERT INTO `cabang` (`id`, `kode_cabang`, `nama_cabang`, `alamat`, `telepon`, `email`, `penanggung_jawab`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'CB001', 'Cabang Pusat Makassar', 'Jl. Pahlawan No. 123, Makassar', '0411-123456', 'pusat@jmfarma.com', 'Apt. John Doe', 1, '2025-12-10 23:55:54', '2025-12-10 23:55:54'),
(2, 'CB002', 'Cabang Makassar Utara', 'Jl. Sungai Saddang No. 45, Makassar', '0411-654321', 'utara@jmfarma.com', 'Apt. Jane Smith', 1, '2025-12-10 23:55:54', '2025-12-10 23:55:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cabangs`
--

CREATE TABLE `cabangs` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `id` bigint UNSIGNED NOT NULL,
  `pembelian_id` bigint UNSIGNED NOT NULL,
  `barang_id` bigint UNSIGNED NOT NULL,
  `jumlah` int NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `batch_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_pembelian`
--

INSERT INTO `detail_pembelian` (`id`, `pembelian_id`, `barang_id`, `jumlah`, `satuan`, `harga_beli`, `subtotal`, `tanggal_kadaluarsa`, `batch_number`, `created_at`, `updated_at`) VALUES
(2, 3, 2, 1000, 'Pcs', 200.00, 200000.00, '2027-10-06', NULL, '2025-12-06 05:21:33', '2025-12-06 05:21:33'),
(3, 4, 2, 100, 'Pcs', 1000.00, 100000.00, '2026-01-06', NULL, '2025-12-06 05:44:37', '2025-12-06 05:44:37'),
(4, 5, 5, 100, 'Dos', 5000.00, 500000.00, NULL, NULL, '2025-12-06 05:47:38', '2025-12-06 05:47:38'),
(5, 6, 5, 10, 'Dos', 5000.00, 50000.00, NULL, NULL, '2025-12-06 05:51:36', '2025-12-06 05:51:36'),
(6, 7, 5, 10, 'Dos', 5000.00, 50000.00, '2027-01-06', NULL, '2025-12-06 05:58:18', '2025-12-06 05:58:18'),
(7, 8, 5, 10, 'Dos', 5000.00, 50000.00, NULL, NULL, '2025-12-06 06:03:57', '2025-12-06 06:03:57'),
(8, 9, 11, 20, 'Strip', 1000.00, 20000.00, '2027-12-11', NULL, '2025-12-11 11:35:32', '2025-12-11 11:35:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id` bigint UNSIGNED NOT NULL,
  `penjualan_id` bigint UNSIGNED NOT NULL,
  `barang_id` bigint UNSIGNED NOT NULL,
  `jumlah` int NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `diskon_item` decimal(12,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL,
  `is_return` tinyint(1) NOT NULL DEFAULT '0',
  `return_date` datetime DEFAULT NULL,
  `return_keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id`, `penjualan_id`, `barang_id`, `jumlah`, `satuan`, `harga_jual`, `diskon_item`, `subtotal`, `is_return`, `return_date`, `return_keterangan`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 'tablet', 650.00, 0.00, 650.00, 0, NULL, NULL, '2025-12-05 18:06:55', '2025-12-05 18:06:55'),
(2, 3, 1, 1, 'tablet', 650.00, 0.00, 650.00, 0, NULL, NULL, '2025-12-05 18:07:23', '2025-12-05 18:07:23'),
(3, 4, 1, 1, 'tablet', 650.00, 0.00, 650.00, 0, NULL, NULL, '2025-12-05 19:44:11', '2025-12-05 19:44:11'),
(4, 4, 2, 1, 'Pcs', 1560.00, 0.00, 1560.00, 0, NULL, NULL, '2025-12-05 19:44:11', '2025-12-05 19:44:11'),
(5, 5, 5, 1, 'Dos', 6000.00, 0.00, 6000.00, 0, NULL, NULL, '2025-12-06 05:55:32', '2025-12-06 05:55:32'),
(6, 6, 5, 1, 'Dos', 7000.00, 0.00, 7000.00, 0, NULL, NULL, '2025-12-06 05:58:58', '2025-12-06 05:58:58'),
(7, 7, 5, 1, 'Dos', 6000.00, 0.00, 6000.00, 0, NULL, NULL, '2025-12-06 06:04:37', '2025-12-06 06:04:37'),
(8, 8, 2, 4, 'Pcs', 1250.00, 0.00, 5000.00, 0, NULL, NULL, '2025-12-07 04:04:05', '2025-12-07 04:04:05'),
(9, 9, 2, 11, 'Pcs', 1200.00, 0.00, 13200.00, 0, NULL, NULL, '2025-12-09 06:48:13', '2025-12-09 06:48:13'),
(10, 10, 1, 1, 'tablet', 2000.00, 0.00, 2000.00, 1, '2025-12-09 21:11:20', NULL, '2025-12-09 13:05:22', '2025-12-09 13:11:20'),
(11, 11, 1, 1, 'tablet', 2000.00, 0.00, 2000.00, 0, NULL, NULL, '2025-12-09 13:09:28', '2025-12-09 13:09:28'),
(12, 11, 2, 1, 'Pcs', 1200.00, 0.00, 1200.00, 0, NULL, NULL, '2025-12-09 13:09:28', '2025-12-09 13:09:28'),
(13, 12, 1, 1, 'tablet', 2000.00, 0.00, 2000.00, 0, NULL, NULL, '2025-12-09 13:19:52', '2025-12-09 13:19:52'),
(14, 13, 4, 1, 'Pcs', 10400.00, 0.00, 10400.00, 1, '2025-12-10 11:47:27', NULL, '2025-12-10 03:44:53', '2025-12-10 03:47:27'),
(15, 14, 5, 2, 'Dos', 6000.00, 0.00, 12000.00, 0, NULL, NULL, '2025-12-10 04:16:32', '2025-12-10 04:16:32'),
(16, 15, 5, 1, 'Dos', 6000.00, 0.00, 6000.00, 0, NULL, NULL, '2025-12-10 12:20:41', '2025-12-10 12:20:41'),
(17, 15, 2, 1, 'Pcs', 1200.00, 0.00, 1200.00, 0, NULL, NULL, '2025-12-10 12:20:41', '2025-12-10 12:20:41'),
(18, 16, 11, 1, 'Strip', 7000.00, 0.00, 7000.00, 0, NULL, NULL, '2025-12-11 11:14:58', '2025-12-11 11:14:58'),
(19, 17, 30, 1, 'a', 2000.00, 0.00, 2000.00, 0, NULL, NULL, '2025-12-11 11:32:09', '2025-12-11 11:32:09'),
(20, 18, 30, 1, 'a', 2000.00, 0.00, 2000.00, 0, NULL, NULL, '2025-12-11 11:36:09', '2025-12-11 11:36:09'),
(21, 19, 11, 1, 'Strip', 7000.00, 0.00, 7000.00, 0, NULL, NULL, '2025-12-13 04:35:23', '2025-12-13 04:35:23'),
(22, 20, 13, 2, 'Strip', 12000.00, 0.00, 24000.00, 0, NULL, NULL, '2025-12-13 04:37:21', '2025-12-13 04:37:21'),
(23, 21, 2, 2, 'Pcs', 1200.00, 0.00, 2400.00, 0, NULL, NULL, '2025-12-13 05:40:36', '2025-12-13 05:40:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_stok_opname`
--

CREATE TABLE `detail_stok_opname` (
  `id` bigint UNSIGNED NOT NULL,
  `stok_opname_id` bigint UNSIGNED NOT NULL,
  `barang_id` bigint UNSIGNED NOT NULL,
  `stok_sistem` int NOT NULL DEFAULT '0',
  `stok_fisik` int NOT NULL DEFAULT '0',
  `selisih` int NOT NULL DEFAULT '0',
  `expired_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_stok_opname`
--

INSERT INTO `detail_stok_opname` (`id`, `stok_opname_id`, `barang_id`, `stok_sistem`, `stok_fisik`, `selisih`, `expired_date`, `created_at`, `updated_at`) VALUES
(5, 6, 2, 1583, 1500, -83, NULL, '2025-12-09 13:47:54', '2025-12-09 13:48:46'),
(7, 8, 2, 1500, 1450, -50, '2026-11-09', '2025-12-09 13:49:57', '2025-12-09 13:50:48'),
(8, 8, 5, 227, 220, -7, '2026-01-09', '2025-12-09 13:50:04', '2025-12-09 13:50:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_12_05_124504_create_barang_table', 1),
(6, '2025_12_05_124505_create_satuan_konversi_table', 1),
(7, '2025_12_05_124505_create_suppliers_table', 1),
(8, '2025_12_05_124506_create_pembelian_table', 1),
(9, '2025_12_05_124507_create_detail_pembelian_table', 1),
(10, '2025_12_05_124507_create_shifts_table', 1),
(11, '2025_12_05_124508_create_penjualan_table', 1),
(12, '2025_12_05_124509_create_detail_penjualan_table', 1),
(13, '2025_12_05_124510_create_settings_table', 1),
(14, '2025_12_05_140252_increase_status_length_in_shifts_table', 1),
(15, '2025_12_05_142919_add_total_bayar_to_penjualan_table', 1),
(16, '2025_12_06_023823_add_lokasi_rak_to_barang_table', 2),
(17, '2025_12_07_180232_create_stok_opname_tables', 3),
(18, '2025_12_07_181146_add_barcode_to_barang_table', 4),
(19, '2025_12_07_183541_add_missing_columns_to_barang_table', 5),
(21, '2025_12_09_202801_add_return_detail_penjualan', 6),
(22, '2025_12_09_211846_fix_metode_pembayaran_enum_in_penjualan', 7),
(23, '2025_12_10_134107_add_kode_shift_to_shifts_table', 8),
(24, '2025_12_11_073620_create_cabang_table', 9),
(25, '2025_12_11_073640_update_users_add_cabang', 9),
(26, '2025_12_11_073844_create_cabangs_table', 9),
(27, '2025_12_11_081349_add_cabang_id_to_tables', 10),
(28, '2025_12_13_122936_add_tracking_columns_to_shifts_table', 11);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor_pembelian` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `cabang_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal_pembelian` date NOT NULL,
  `total_pembelian` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pajak` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','approved','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pembelian`
--

INSERT INTO `pembelian` (`id`, `nomor_pembelian`, `supplier_id`, `user_id`, `cabang_id`, `tanggal_pembelian`, `total_pembelian`, `diskon`, `pajak`, `grand_total`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES
(3, '002', 1, 1, NULL, '2025-12-06', 200000.00, 0.00, 0.00, 200000.00, NULL, 'approved', '2025-12-06 05:21:33', '2025-12-06 05:21:33'),
(4, '003', 1, 1, NULL, '2025-12-06', 100000.00, 0.00, 0.00, 100000.00, '-', 'approved', '2025-12-06 05:44:37', '2025-12-06 05:44:37'),
(5, '004', 1, 1, NULL, '2025-12-06', 500000.00, 0.00, 0.00, 500000.00, '-', 'approved', '2025-12-06 05:47:38', '2025-12-06 05:47:38'),
(6, '007', 1, 1, NULL, '2025-12-06', 50000.00, 0.00, 0.00, 50000.00, '-', 'approved', '2025-12-06 05:51:36', '2025-12-06 05:51:36'),
(7, '009', 1, 1, NULL, '2025-12-06', 50000.00, 0.00, 0.00, 50000.00, '-', 'approved', '2025-12-06 05:58:18', '2025-12-06 05:58:18'),
(8, '010', 1, 1, NULL, '2025-12-06', 50000.00, 0.00, 0.00, 50000.00, '-', 'approved', '2025-12-06 06:03:57', '2025-12-06 06:03:57'),
(9, '00', 1, 4, 2, '2025-12-11', 20000.00, 0.00, 0.00, 20000.00, NULL, 'approved', '2025-12-11 11:35:32', '2025-12-11 11:35:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor_nota` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `cabang_id` bigint UNSIGNED DEFAULT NULL,
  `shift_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal_penjualan` datetime NOT NULL,
  `nama_pelanggan` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_penjualan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pajak` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `jumlah_bayar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(15,2) NOT NULL DEFAULT '0.00',
  `metode_pembayaran` enum('cash','debit','credit','qris','transfer') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `nomor_referensi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_bayar` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id`, `nomor_nota`, `user_id`, `cabang_id`, `shift_id`, `tanggal_penjualan`, `nama_pelanggan`, `total_penjualan`, `diskon`, `pajak`, `grand_total`, `jumlah_bayar`, `kembalian`, `metode_pembayaran`, `nomor_referensi`, `keterangan`, `created_at`, `updated_at`, `total_bayar`) VALUES
(2, 'TRX202512060001', 1, NULL, 4, '2025-12-06 02:06:55', NULL, 650.00, 0.00, 0.00, 650.00, 1000.00, 350.00, 'cash', NULL, NULL, '2025-12-05 18:06:55', '2025-12-05 18:06:55', 0.00),
(3, 'TRX202512060002', 1, NULL, 4, '2025-12-06 02:07:23', NULL, 650.00, 0.00, 0.00, 650.00, 1000.00, 350.00, 'cash', NULL, NULL, '2025-12-05 18:07:23', '2025-12-05 18:07:23', 0.00),
(4, 'TRX202512060003', 1, NULL, 4, '2025-12-06 03:44:11', NULL, 2210.00, 0.00, 0.00, 2210.00, 2210.00, 0.00, 'cash', NULL, NULL, '2025-12-05 19:44:11', '2025-12-05 19:44:11', 0.00),
(5, 'TRX202512060004', 1, NULL, 6, '2025-12-06 13:55:32', NULL, 6000.00, 0.00, 0.00, 6000.00, 6000.00, 0.00, 'cash', NULL, NULL, '2025-12-06 05:55:32', '2025-12-06 05:55:32', 0.00),
(6, 'TRX202512060005', 1, NULL, 6, '2025-12-06 13:58:58', NULL, 7000.00, 0.00, 0.00, 7000.00, 7000.00, 0.00, 'cash', NULL, NULL, '2025-12-06 05:58:58', '2025-12-06 05:58:58', 0.00),
(7, 'TRX202512060006', 1, NULL, 6, '2025-12-06 14:04:37', NULL, 6000.00, 0.00, 0.00, 6000.00, 6000.00, 0.00, 'cash', NULL, NULL, '2025-12-06 06:04:37', '2025-12-06 06:04:37', 0.00),
(8, 'TRX202512070001', 1, NULL, 6, '2025-12-07 12:04:05', NULL, 5000.00, 0.00, 0.00, 5000.00, 5000.00, 0.00, 'cash', NULL, NULL, '2025-12-07 04:04:05', '2025-12-07 04:04:05', 0.00),
(9, 'TRX202512090001', 3, NULL, 9, '2025-12-09 14:48:12', NULL, 13200.00, 0.00, 0.00, 13200.00, 13200.00, 0.00, 'cash', NULL, NULL, '2025-12-09 06:48:12', '2025-12-09 06:48:12', 0.00),
(10, 'TRX202512090002', 3, NULL, 12, '2025-12-09 21:05:22', NULL, 2000.00, 0.00, 0.00, 2000.00, 2000.00, 0.00, 'cash', NULL, NULL, '2025-12-09 13:05:22', '2025-12-09 13:05:22', 0.00),
(11, 'TRX202512090003', 3, NULL, 12, '2025-12-09 21:09:28', NULL, 3200.00, 0.00, 0.00, 3200.00, 3200.00, 0.00, 'cash', NULL, NULL, '2025-12-09 13:09:28', '2025-12-09 13:09:28', 0.00),
(12, 'TRX202512090004', 1, NULL, 11, '2025-12-09 21:19:52', NULL, 2000.00, 0.00, 0.00, 2000.00, 2000.00, 0.00, 'transfer', '001', NULL, '2025-12-09 13:19:52', '2025-12-09 13:19:52', 0.00),
(13, 'TRX202512100001', 1, NULL, 11, '2025-12-10 11:44:53', NULL, 10400.00, 0.00, 0.00, 10400.00, 10400.00, 0.00, 'transfer', '002', NULL, '2025-12-10 03:44:53', '2025-12-10 03:44:53', 0.00),
(14, 'TRX202512100002', 1, NULL, 11, '2025-12-10 12:16:32', NULL, 12000.00, 0.00, 0.00, 12000.00, 12000.00, 0.00, 'cash', NULL, NULL, '2025-12-10 04:16:32', '2025-12-10 04:16:32', 0.00),
(15, 'TRX202512100003', 1, NULL, 18, '2025-12-10 20:20:41', NULL, 7200.00, 0.00, 0.00, 7200.00, 7200.00, 0.00, 'transfer', '002', NULL, '2025-12-10 12:20:41', '2025-12-10 12:20:41', 0.00),
(16, 'TRX202512110001', 4, 2, 22, '2025-12-11 19:14:58', NULL, 7000.00, 0.00, 0.00, 7000.00, 7000.00, 0.00, 'cash', NULL, NULL, '2025-12-11 11:14:58', '2025-12-11 11:14:58', 0.00),
(17, 'TRX202512110002', 7, 2, 23, '2025-12-11 19:32:09', NULL, 2000.00, 0.00, 0.00, 2000.00, 2000.00, 0.00, 'cash', NULL, NULL, '2025-12-11 11:32:09', '2025-12-11 11:32:09', 0.00),
(18, 'TRX202512110003', 4, 2, 22, '2025-12-11 19:36:09', NULL, 2000.00, 0.00, 0.00, 2000.00, 2000.00, 0.00, 'cash', NULL, NULL, '2025-12-11 11:36:09', '2025-12-11 11:36:09', 0.00),
(19, 'TRX202512130001', 8, 2, 24, '2025-12-13 12:35:23', NULL, 7000.00, 0.00, 0.00, 7000.00, 7000.00, 0.00, 'cash', NULL, NULL, '2025-12-13 04:35:23', '2025-12-13 04:35:23', 0.00),
(20, 'TRX202512130002', 8, 2, 25, '2025-12-13 12:37:21', NULL, 24000.00, 0.00, 0.00, 24000.00, 24000.00, 0.00, 'cash', NULL, NULL, '2025-12-13 04:37:21', '2025-12-13 04:37:21', 0.00),
(21, 'TRX202512130003', 6, 1, 26, '2025-12-13 13:40:36', NULL, 2400.00, 0.00, 0.00, 2400.00, 10000.00, 7600.00, 'cash', NULL, NULL, '2025-12-13 05:40:36', '2025-12-13 05:40:36', 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `satuan_konversi`
--

CREATE TABLE `satuan_konversi` (
  `id` bigint UNSIGNED NOT NULL,
  `barang_id` bigint UNSIGNED NOT NULL,
  `nama_satuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah_konversi` int NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `satuan_konversi`
--

INSERT INTO `satuan_konversi` (`id`, `barang_id`, `nama_satuan`, `jumlah_konversi`, `harga_jual`, `is_default`, `created_at`, `updated_at`) VALUES
(6, 3, 'Tablet', 1, 1040.00, 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(7, 3, 'Botol', 30, 30000.00, 0, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(18, 6, 'Kardus', 100, 550000.00, 0, '2025-12-07 09:53:27', '2025-12-07 09:53:27'),
(27, 1, 'Tablet', 1, 650.00, 1, '2025-12-07 11:04:02', '2025-12-07 11:04:02'),
(28, 1, 'Strip', 10, 6000.00, 0, '2025-12-07 11:04:02', '2025-12-07 11:04:02'),
(29, 1, 'Box', 100, 55000.00, 0, '2025-12-07 11:04:02', '2025-12-07 11:04:02'),
(36, 2, 'Box', 10, 10000.00, 0, '2025-12-10 06:20:42', '2025-12-10 06:20:42'),
(37, 2, 'dos', 5, 40000.00, 0, '2025-12-10 06:20:42', '2025-12-10 06:20:42'),
(38, 11, 'Strip', 1, 2000.00, 0, '2025-12-11 11:35:32', '2025-12-11 11:35:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `label`, `description`, `created_at`, `updated_at`) VALUES
(1, 'nama_apotek', 'Apotek Sehat', 'string', 'apotek', 'Nama Apotek', 'Nama apotek yang ditampilkan di aplikasi', '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(2, 'alamat_apotek', 'Jl. Kesehatan No. 123, Jakarta', 'string', 'apotek', 'Alamat Apotek', 'Alamat lengkap apotek', '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(3, 'telepon_apotek', '021-1234567', 'string', 'apotek', 'Telepon', 'Nomor telepon apotek', '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(4, 'margin_default', '30', 'number', 'kasir', 'Margin Harga Default (%)', 'Persentase margin untuk hitung harga jual otomatis', '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(5, 'pajak_penjualan', '10', 'number', 'kasir', 'Pajak Penjualan (%)', 'Persentase pajak yang dikenakan pada penjualan', '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(6, 'minimal_stok_alert', '10', 'number', 'general', 'Minimal Stok Alert', 'Jumlah minimal stok sebelum muncul peringatan', '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(7, 'auto_print_struk', 'false', 'boolean', 'kasir', 'Auto Print Struk', 'Otomatis print struk setelah transaksi', '2025-12-05 07:03:20', '2025-12-05 07:03:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_shift` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `cabang_id` bigint UNSIGNED DEFAULT NULL,
  `waktu_buka` datetime NOT NULL,
  `waktu_tutup` datetime DEFAULT NULL,
  `saldo_awal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_penjualan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_cash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_non_cash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_akhir` decimal(15,2) NOT NULL DEFAULT '0.00',
  `selisih` decimal(15,2) NOT NULL DEFAULT '0.00',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `shifts`
--

INSERT INTO `shifts` (`id`, `kode_shift`, `user_id`, `cabang_id`, `waktu_buka`, `waktu_tutup`, `saldo_awal`, `total_penjualan`, `total_cash`, `total_non_cash`, `saldo_akhir`, `selisih`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, NULL, '2025-12-05 15:12:18', '2025-12-05 15:13:43', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 07:12:18', '2025-12-05 07:13:43'),
(2, NULL, 1, NULL, '2025-12-05 15:14:00', '2025-12-05 15:19:00', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 07:14:00', '2025-12-05 07:19:00'),
(3, NULL, 1, NULL, '2025-12-05 15:20:21', '2025-12-06 01:45:15', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 07:20:21', '2025-12-05 17:45:15'),
(4, NULL, 1, NULL, '2025-12-06 01:45:38', '2025-12-06 04:09:12', 0.00, 3510.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 17:45:38', '2025-12-05 20:09:12'),
(5, NULL, 1, NULL, '2025-12-06 13:17:32', '2025-12-06 13:53:05', 0.00, 0.00, 0.00, 0.00, 0.00, 3510.00, NULL, 'tutup', '2025-12-06 05:17:32', '2025-12-06 05:53:05'),
(6, NULL, 1, NULL, '2025-12-06 13:55:13', '2025-12-09 13:32:35', 0.00, 24000.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-06 05:55:13', '2025-12-09 05:32:35'),
(7, NULL, 3, NULL, '2025-12-07 12:26:57', '2025-12-09 14:47:10', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-07 04:26:57', '2025-12-09 06:47:10'),
(8, NULL, 1, NULL, '2025-12-09 13:42:35', '2025-12-09 13:42:51', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-09 05:42:35', '2025-12-09 05:42:51'),
(9, NULL, 3, NULL, '2025-12-09 14:47:55', '2025-12-09 14:48:45', 0.00, 13200.00, 0.00, 0.00, 0.00, 2000.00, NULL, 'tutup', '2025-12-09 06:47:55', '2025-12-09 06:48:45'),
(10, NULL, 3, NULL, '2025-12-09 15:30:04', '2025-12-09 20:17:46', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-09 07:30:04', '2025-12-09 12:17:46'),
(11, NULL, 1, NULL, '2025-12-09 20:37:01', '2025-12-10 12:44:27', 0.00, 24400.00, 0.00, 0.00, 0.00, 10400.00, NULL, 'tutup', '2025-12-09 12:37:01', '2025-12-10 04:44:27'),
(12, NULL, 3, NULL, '2025-12-09 21:03:05', '2025-12-10 13:29:58', 0.00, 5200.00, 0.00, 0.00, 0.00, -5200.00, NULL, 'tutup', '2025-12-09 13:03:05', '2025-12-10 05:29:58'),
(14, '02-10122025', 3, NULL, '2025-12-10 13:45:35', '2025-12-10 13:45:46', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-10 05:45:35', '2025-12-10 05:45:46'),
(18, '03-10122025', 1, NULL, '2025-12-10 19:03:28', '2025-12-10 21:27:27', 0.00, 7200.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-10 11:03:28', '2025-12-10 13:27:27'),
(19, '01-11122025', 1, NULL, '2025-12-11 07:25:45', '2025-12-11 07:29:50', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-10 23:25:45', '2025-12-10 23:29:50'),
(20, '02-11122025', 6, NULL, '2025-12-11 09:01:57', '2025-12-13 12:22:29', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-11 01:01:57', '2025-12-13 04:22:29'),
(21, '03-11122025', 4, NULL, '2025-12-11 18:31:37', '2025-12-11 18:31:52', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-11 10:31:37', '2025-12-11 10:31:52'),
(22, '04-11122025', 4, NULL, '2025-12-11 19:14:39', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'aktif', '2025-12-11 11:14:39', '2025-12-11 11:14:39'),
(23, '05-11122025', 7, NULL, '2025-12-11 19:31:59', '2025-12-11 19:55:13', 0.00, 2000.00, 0.00, 0.00, 0.00, 9000.00, NULL, 'tutup', '2025-12-11 11:31:59', '2025-12-11 11:55:13'),
(24, '01-13122025', 8, NULL, '2025-12-13 12:34:51', '2025-12-13 12:35:47', 100000.00, 7000.00, 7000.00, 0.00, 107000.00, 0.00, NULL, 'closed', '2025-12-13 04:34:51', '2025-12-13 04:35:47'),
(25, '02-13122025', 8, NULL, '2025-12-13 12:36:59', NULL, 100000.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'open', '2025-12-13 04:36:59', '2025-12-13 04:36:59'),
(26, '03-13122025', 6, NULL, '2025-12-13 13:39:52', '2025-12-13 13:42:37', 1000000.00, 2400.00, 2400.00, 0.00, 1002400.00, 0.00, NULL, 'closed', '2025-12-13 05:39:52', '2025-12-13 05:42:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_opname`
--

CREATE TABLE `stok_opname` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `cabang_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stok_opname`
--

INSERT INTO `stok_opname` (`id`, `user_id`, `cabang_id`, `tanggal`, `keterangan`, `status`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '2025-12-07', 'Sesi SO - 07 Dec 2025 18:08', 'completed', '2025-12-07 10:23:03', '2025-12-07 10:08:04', '2025-12-07 10:23:03'),
(2, 1, NULL, '2025-12-07', 'Sesi SO - 07 Dec 2025 18:23', 'completed', '2025-12-07 10:23:54', '2025-12-07 10:23:42', '2025-12-07 10:23:54'),
(3, 1, NULL, '2025-12-07', 'Sesi SO - 07 Dec 2025 18:39', 'completed', '2025-12-07 10:39:15', '2025-12-07 10:39:00', '2025-12-07 10:39:15'),
(4, 1, NULL, '2025-12-09', 'Sesi SO - 09 Dec 2025 14:01', 'completed', '2025-12-09 06:08:53', '2025-12-09 06:01:43', '2025-12-09 06:08:53'),
(5, 1, NULL, '2025-12-09', 'Sesi SO - 09 Dec 2025 15:27', 'completed', '2025-12-09 07:28:39', '2025-12-09 07:27:56', '2025-12-09 07:28:39'),
(6, 1, NULL, '2025-12-09', 'Sesi SO - 09 Dec 2025 15:56', 'completed', '2025-12-09 13:48:48', '2025-12-09 07:56:23', '2025-12-09 13:48:48'),
(7, 3, NULL, '2025-12-09', 'Sesi SO - 09 Dec 2025 21:43', 'completed', '2025-12-09 13:44:02', '2025-12-09 13:43:59', '2025-12-09 13:44:02'),
(8, 1, NULL, '2025-12-09', 'Sesi SO - 09 Dec 2025 21:49', 'completed', '2025-12-09 13:51:01', '2025-12-09 13:49:01', '2025-12-09 13:51:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_supplier` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_supplier` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `suppliers`
--

INSERT INTO `suppliers` (`id`, `kode_supplier`, `nama_supplier`, `alamat`, `telepon`, `email`, `contact_person`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'SUP-0001', 'PT. Kimia Farma', 'Jakarta Pusat', '021-5555001', 'supply@kimiafarma.co.id', 'Budi Santoso', 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(2, 'SUP-0002', 'PT. Kalbe Farma', 'Jakarta Timur', '021-5555002', 'supply@kalbe.co.id', 'Siti Nurhaliza', 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(3, 'SUP-0003', 'PT. Sanbe Farma', 'Bandung', '022-5555003', 'supply@sanbe.co.id', 'Ahmad Fauzi', 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kasir',
  `cabang_id` bigint UNSIGNED DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `cabang_id`, `aktif`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@apotek.com', NULL, '$2y$12$nlwtwlY1EVFEHePoS14qBuFkJ7t6nuyuT7Q.Pbv8bt6LPnHR20Eci', 'admin', NULL, 1, NULL, '2025-12-05 07:03:19', '2025-12-05 21:51:48'),
(2, 'Kasir 1', 'kasir@apotek.com', NULL, '$2y$12$bUvkrZbEWec5w23lDZLmhODW4zBjLubqpkfmBOromat2lZSakFEBe', 'kasir', NULL, 1, NULL, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(3, 'Dims', 'kasir@gmail.com', NULL, '$2y$12$TfeD.ylFJo3DreU3qfO0q.mBNJGRYjLnSHAXZRTN7gyFSm97vWY5O', 'kasir', NULL, 1, NULL, '2025-12-05 19:36:45', '2025-12-10 05:27:24'),
(4, 'Super Admin', 'superadmin@jmfarma.com', NULL, '$2y$12$0g1g/8bG8hlRcG66G5rL2Ow6AzzbTDKO9gQlfqnm1BSImSpiPbVvO', 'super_admin', NULL, 1, NULL, '2025-12-10 23:55:55', '2025-12-10 23:55:55'),
(5, 'Admin Cabang Pusat', 'admin.pusat@jmfarma.com', NULL, '$2y$12$WRY4G6trDghfUcyQvVhPPey9oaV9vp6xf/34L7xjbF21KQdDeO.Pq', 'admin_cabang', 1, 1, NULL, '2025-12-10 23:55:55', '2025-12-10 23:55:55'),
(6, 'Kasir Pusat 1', 'kasir.pusat1@jmfarma.com', NULL, '$2y$12$D0i.3abgHgNUenwRAbAZjeLUgVmzsRWdy89dFtWxD7IUyRYLx8xRW', 'kasir', 1, 1, NULL, '2025-12-10 23:55:55', '2025-12-10 23:55:55'),
(7, 'Admin Cabang Utara', 'admin.utara@jmfarma.com', NULL, '$2y$12$iKFO8SkmPigfxxAm0F0iJ.mO/W9Ttg8r7sPGw1J6SwCnP2Mhv/7Yi', 'admin_cabang', 2, 1, NULL, '2025-12-10 23:55:56', '2025-12-10 23:55:56'),
(8, 'Kasir Utara 1', 'kasir.utara1@jmfarma.com', NULL, '$2y$12$YAR/Uxuwgg3JgSbZfZMQLeTlXkVDAbvYgjHHHYSbfDcCWC8SsS3ES', 'kasir', 2, 1, NULL, '2025-12-10 23:55:56', '2025-12-10 23:55:56');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barang_kode_barang_unique` (`kode_barang`),
  ADD KEY `barang_kode_barang_index` (`kode_barang`),
  ADD KEY `barang_nama_barang_index` (`nama_barang`),
  ADD KEY `barang_barcode_index` (`barcode`),
  ADD KEY `barang_cabang_id_foreign` (`cabang_id`);

--
-- Indeks untuk tabel `cabang`
--
ALTER TABLE `cabang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cabang_kode_cabang_unique` (`kode_cabang`);

--
-- Indeks untuk tabel `cabangs`
--
ALTER TABLE `cabangs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_pembelian_pembelian_id_index` (`pembelian_id`),
  ADD KEY `detail_pembelian_barang_id_index` (`barang_id`);

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_penjualan_penjualan_id_index` (`penjualan_id`),
  ADD KEY `detail_penjualan_barang_id_index` (`barang_id`);

--
-- Indeks untuk tabel `detail_stok_opname`
--
ALTER TABLE `detail_stok_opname`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_stok_opname_stok_opname_id_foreign` (`stok_opname_id`),
  ADD KEY `detail_stok_opname_barang_id_foreign` (`barang_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pembelian_nomor_pembelian_unique` (`nomor_pembelian`),
  ADD KEY `pembelian_supplier_id_foreign` (`supplier_id`),
  ADD KEY `pembelian_user_id_foreign` (`user_id`),
  ADD KEY `pembelian_nomor_pembelian_index` (`nomor_pembelian`),
  ADD KEY `pembelian_tanggal_pembelian_index` (`tanggal_pembelian`),
  ADD KEY `pembelian_cabang_id_foreign` (`cabang_id`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `penjualan_nomor_nota_unique` (`nomor_nota`),
  ADD KEY `penjualan_user_id_foreign` (`user_id`),
  ADD KEY `penjualan_nomor_nota_index` (`nomor_nota`),
  ADD KEY `penjualan_tanggal_penjualan_index` (`tanggal_penjualan`),
  ADD KEY `penjualan_shift_id_index` (`shift_id`),
  ADD KEY `penjualan_cabang_id_foreign` (`cabang_id`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `satuan_konversi`
--
ALTER TABLE `satuan_konversi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `satuan_konversi_barang_id_index` (`barang_id`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_key_index` (`key`),
  ADD KEY `settings_group_index` (`group`);

--
-- Indeks untuk tabel `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shifts_kode_shift_unique` (`kode_shift`),
  ADD KEY `shifts_user_id_index` (`user_id`),
  ADD KEY `shifts_status_index` (`status`),
  ADD KEY `shifts_waktu_buka_index` (`waktu_buka`),
  ADD KEY `shifts_cabang_id_foreign` (`cabang_id`);

--
-- Indeks untuk tabel `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stok_opname_user_id_foreign` (`user_id`),
  ADD KEY `stok_opname_cabang_id_foreign` (`cabang_id`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `suppliers_kode_supplier_unique` (`kode_supplier`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_cabang_id_foreign` (`cabang_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `cabang`
--
ALTER TABLE `cabang`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `cabangs`
--
ALTER TABLE `cabangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `detail_stok_opname`
--
ALTER TABLE `detail_stok_opname`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `satuan_konversi`
--
ALTER TABLE `satuan_konversi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `stok_opname`
--
ALTER TABLE `stok_opname`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_cabang_id_foreign` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `detail_pembelian_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  ADD CONSTRAINT `detail_pembelian_pembelian_id_foreign` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelian` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `detail_penjualan_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  ADD CONSTRAINT `detail_penjualan_penjualan_id_foreign` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_stok_opname`
--
ALTER TABLE `detail_stok_opname`
  ADD CONSTRAINT `detail_stok_opname_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_stok_opname_stok_opname_id_foreign` FOREIGN KEY (`stok_opname_id`) REFERENCES `stok_opname` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_cabang_id_foreign` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembelian_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `pembelian_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_cabang_id_foreign` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penjualan_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`),
  ADD CONSTRAINT `penjualan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `satuan_konversi`
--
ALTER TABLE `satuan_konversi`
  ADD CONSTRAINT `satuan_konversi_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_cabang_id_foreign` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD CONSTRAINT `stok_opname_cabang_id_foreign` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stok_opname_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_cabang_id_foreign` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
