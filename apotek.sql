-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 07 Des 2025 pada 02.28
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
  `nama_barang` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_beli` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stok` int NOT NULL DEFAULT '0',
  `stok_minimum` int NOT NULL DEFAULT '10',
  `lokasi_rak` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `satuan_terkecil` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tablet',
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `nama_barang`, `kategori`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `lokasi_rak`, `satuan_terkecil`, `tanggal_kadaluarsa`, `deskripsi`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'OBT-0001', 'Paracetamol 500mg', 'Obat Umum', 1000.00, 650.00, 1097, 100, NULL, 'tablet', '2027-12-05', 'Obat penurun panas dan pereda nyeri', 1, '2025-12-05 07:03:20', '2025-12-05 19:44:11'),
(2, 'OBT-0002', 'Amoxicillin 500mg', 'Antibiotik', 1000.00, 1250.00, 1599, 50, 'Rak Kanan', 'Pcs', '2026-12-05', 'Antibiotik untuk infeksi bakteri', 1, '2025-12-05 07:03:20', '2025-12-06 05:44:37'),
(3, 'OBT-0003', 'Vitamin C 1000mg', 'Vitamin', 800.00, 1040.00, 800, 80, NULL, 'tablet', '2027-06-05', 'Suplemen vitamin C dosis tinggi', 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(4, 'OBT-0004', 'Obat Batuk Hitam (OBH)', 'Obat Batuk', 8000.00, 10400.00, 60, 10, 'Rak Kiri', 'Pcs', '2027-12-05', 'Sirup obat batuk 60ml', 1, '2025-12-05 07:03:20', '2025-12-05 18:45:12'),
(5, 'OBT-0005', 'Mylanta', 'Obat Umum', 5000.00, 6000.00, 227, 10, 'Rak Kanan', 'Dos', NULL, NULL, 1, '2025-12-06 05:15:18', '2025-12-06 06:04:37');

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
(1, 2, 1, 100, 'tablet', 1000.00, 100000.00, '2027-01-06', NULL, '2025-12-05 18:56:36', '2025-12-05 18:56:36'),
(2, 3, 2, 1000, 'Pcs', 200.00, 200000.00, '2027-10-06', NULL, '2025-12-06 05:21:33', '2025-12-06 05:21:33'),
(3, 4, 2, 100, 'Pcs', 1000.00, 100000.00, '2026-01-06', NULL, '2025-12-06 05:44:37', '2025-12-06 05:44:37'),
(4, 5, 5, 100, 'Dos', 5000.00, 500000.00, NULL, NULL, '2025-12-06 05:47:38', '2025-12-06 05:47:38'),
(5, 6, 5, 10, 'Dos', 5000.00, 50000.00, NULL, NULL, '2025-12-06 05:51:36', '2025-12-06 05:51:36'),
(6, 7, 5, 10, 'Dos', 5000.00, 50000.00, '2027-01-06', NULL, '2025-12-06 05:58:18', '2025-12-06 05:58:18'),
(7, 8, 5, 10, 'Dos', 5000.00, 50000.00, NULL, NULL, '2025-12-06 06:03:57', '2025-12-06 06:03:57');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id`, `penjualan_id`, `barang_id`, `jumlah`, `satuan`, `harga_jual`, `diskon_item`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 'tablet', 650.00, 0.00, 650.00, '2025-12-05 18:06:55', '2025-12-05 18:06:55'),
(2, 3, 1, 1, 'tablet', 650.00, 0.00, 650.00, '2025-12-05 18:07:23', '2025-12-05 18:07:23'),
(3, 4, 1, 1, 'tablet', 650.00, 0.00, 650.00, '2025-12-05 19:44:11', '2025-12-05 19:44:11'),
(4, 4, 2, 1, 'Pcs', 1560.00, 0.00, 1560.00, '2025-12-05 19:44:11', '2025-12-05 19:44:11'),
(5, 5, 5, 1, 'Dos', 6000.00, 0.00, 6000.00, '2025-12-06 05:55:32', '2025-12-06 05:55:32'),
(6, 6, 5, 1, 'Dos', 7000.00, 0.00, 7000.00, '2025-12-06 05:58:58', '2025-12-06 05:58:58'),
(7, 7, 5, 1, 'Dos', 6000.00, 0.00, 6000.00, '2025-12-06 06:04:37', '2025-12-06 06:04:37');

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
(16, '2025_12_06_023823_add_lokasi_rak_to_barang_table', 2);

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

INSERT INTO `pembelian` (`id`, `nomor_pembelian`, `supplier_id`, `user_id`, `tanggal_pembelian`, `total_pembelian`, `diskon`, `pajak`, `grand_total`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES
(2, '001', 1, 1, '2025-12-06', 100000.00, 0.00, 0.00, 100000.00, NULL, 'approved', '2025-12-05 18:56:36', '2025-12-05 18:56:36'),
(3, '002', 1, 1, '2025-12-06', 200000.00, 0.00, 0.00, 200000.00, NULL, 'approved', '2025-12-06 05:21:33', '2025-12-06 05:21:33'),
(4, '003', 1, 1, '2025-12-06', 100000.00, 0.00, 0.00, 100000.00, '-', 'approved', '2025-12-06 05:44:37', '2025-12-06 05:44:37'),
(5, '004', 1, 1, '2025-12-06', 500000.00, 0.00, 0.00, 500000.00, '-', 'approved', '2025-12-06 05:47:38', '2025-12-06 05:47:38'),
(6, '007', 1, 1, '2025-12-06', 50000.00, 0.00, 0.00, 50000.00, '-', 'approved', '2025-12-06 05:51:36', '2025-12-06 05:51:36'),
(7, '009', 1, 1, '2025-12-06', 50000.00, 0.00, 0.00, 50000.00, '-', 'approved', '2025-12-06 05:58:18', '2025-12-06 05:58:18'),
(8, '010', 1, 1, '2025-12-06', 50000.00, 0.00, 0.00, 50000.00, '-', 'approved', '2025-12-06 06:03:57', '2025-12-06 06:03:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor_nota` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `shift_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal_penjualan` datetime NOT NULL,
  `nama_pelanggan` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_penjualan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pajak` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `jumlah_bayar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(15,2) NOT NULL DEFAULT '0.00',
  `metode_pembayaran` enum('cash','debit','credit','qris') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_bayar` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id`, `nomor_nota`, `user_id`, `shift_id`, `tanggal_penjualan`, `nama_pelanggan`, `total_penjualan`, `diskon`, `pajak`, `grand_total`, `jumlah_bayar`, `kembalian`, `metode_pembayaran`, `keterangan`, `created_at`, `updated_at`, `total_bayar`) VALUES
(2, 'TRX202512060001', 1, 4, '2025-12-06 02:06:55', NULL, 650.00, 0.00, 0.00, 650.00, 1000.00, 350.00, 'cash', NULL, '2025-12-05 18:06:55', '2025-12-05 18:06:55', 0.00),
(3, 'TRX202512060002', 1, 4, '2025-12-06 02:07:23', NULL, 650.00, 0.00, 0.00, 650.00, 1000.00, 350.00, 'cash', NULL, '2025-12-05 18:07:23', '2025-12-05 18:07:23', 0.00),
(4, 'TRX202512060003', 1, 4, '2025-12-06 03:44:11', NULL, 2210.00, 0.00, 0.00, 2210.00, 2210.00, 0.00, 'cash', NULL, '2025-12-05 19:44:11', '2025-12-05 19:44:11', 0.00),
(5, 'TRX202512060004', 1, 6, '2025-12-06 13:55:32', NULL, 6000.00, 0.00, 0.00, 6000.00, 6000.00, 0.00, 'cash', NULL, '2025-12-06 05:55:32', '2025-12-06 05:55:32', 0.00),
(6, 'TRX202512060005', 1, 6, '2025-12-06 13:58:58', NULL, 7000.00, 0.00, 0.00, 7000.00, 7000.00, 0.00, 'cash', NULL, '2025-12-06 05:58:58', '2025-12-06 05:58:58', 0.00),
(7, 'TRX202512060006', 1, 6, '2025-12-06 14:04:37', NULL, 6000.00, 0.00, 0.00, 6000.00, 6000.00, 0.00, 'cash', NULL, '2025-12-06 06:04:37', '2025-12-06 06:04:37', 0.00);

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
(1, 1, 'Tablet', 1, 650.00, 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(2, 1, 'Strip', 10, 6000.00, 0, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(3, 1, 'Box', 100, 55000.00, 0, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(6, 3, 'Tablet', 1, 1040.00, 1, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(7, 3, 'Botol', 30, 30000.00, 0, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(10, 2, 'Box', 1, 10000.00, 0, '2025-12-06 12:26:12', '2025-12-06 12:26:12'),
(11, 2, 'dos', 5, 40000.00, 0, '2025-12-06 12:26:12', '2025-12-06 12:26:12');

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
  `user_id` bigint UNSIGNED NOT NULL,
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

INSERT INTO `shifts` (`id`, `user_id`, `waktu_buka`, `waktu_tutup`, `saldo_awal`, `total_penjualan`, `total_cash`, `total_non_cash`, `saldo_akhir`, `selisih`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-12-05 15:12:18', '2025-12-05 15:13:43', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 07:12:18', '2025-12-05 07:13:43'),
(2, 1, '2025-12-05 15:14:00', '2025-12-05 15:19:00', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 07:14:00', '2025-12-05 07:19:00'),
(3, 1, '2025-12-05 15:20:21', '2025-12-06 01:45:15', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 07:20:21', '2025-12-05 17:45:15'),
(4, 1, '2025-12-06 01:45:38', '2025-12-06 04:09:12', 0.00, 3510.00, 0.00, 0.00, 0.00, 0.00, NULL, 'tutup', '2025-12-05 17:45:38', '2025-12-05 20:09:12'),
(5, 1, '2025-12-06 13:17:32', '2025-12-06 13:53:05', 0.00, 0.00, 0.00, 0.00, 0.00, 3510.00, NULL, 'tutup', '2025-12-06 05:17:32', '2025-12-06 05:53:05'),
(6, 1, '2025-12-06 13:55:13', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 'aktif', '2025-12-06 05:55:13', '2025-12-06 05:55:13');

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
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `aktif`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@apotek.com', NULL, '$2y$12$nlwtwlY1EVFEHePoS14qBuFkJ7t6nuyuT7Q.Pbv8bt6LPnHR20Eci', 'admin', 1, NULL, '2025-12-05 07:03:19', '2025-12-05 21:51:48'),
(2, 'Kasir 1', 'kasir@apotek.com', NULL, '$2y$12$bUvkrZbEWec5w23lDZLmhODW4zBjLubqpkfmBOromat2lZSakFEBe', 'kasir', 1, NULL, '2025-12-05 07:03:20', '2025-12-05 07:03:20'),
(3, 'Kasir2', 'kasir@gmail.com', NULL, '$2y$12$NCNobPo.RG.5c4JLCz4xZevK9h2K.Ua8NXmVxi9Sprp7JLqYO1OkK', 'kasir', 1, NULL, '2025-12-05 19:36:45', '2025-12-05 19:36:45');

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
  ADD KEY `barang_nama_barang_index` (`nama_barang`);

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
  ADD KEY `pembelian_tanggal_pembelian_index` (`tanggal_pembelian`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `penjualan_nomor_nota_unique` (`nomor_nota`),
  ADD KEY `penjualan_user_id_foreign` (`user_id`),
  ADD KEY `penjualan_nomor_nota_index` (`nomor_nota`),
  ADD KEY `penjualan_tanggal_penjualan_index` (`tanggal_penjualan`),
  ADD KEY `penjualan_shift_id_index` (`shift_id`);

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
  ADD KEY `shifts_user_id_index` (`user_id`),
  ADD KEY `shifts_status_index` (`status`),
  ADD KEY `shifts_waktu_buka_index` (`waktu_buka`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `satuan_konversi`
--
ALTER TABLE `satuan_konversi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

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
-- Ketidakleluasaan untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `pembelian_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
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
  ADD CONSTRAINT `shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
