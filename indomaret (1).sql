-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Nov 2025 pada 10.48
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `indomaret`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cashiers`
--

CREATE TABLE `cashiers` (
  `cashier_id` int(11) NOT NULL,
  `cashier_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cashiers`
--

INSERT INTO `cashiers` (`cashier_id`, `cashier_name`, `username`, `password`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Radit', 'radit', 'radit123', '2025-11-27 14:13:13', '2025-11-27 14:13:13', NULL),
(6, 'gangga', 'wahyu', 'gangga123', '2025-11-30 09:13:34', '2025-11-30 09:14:00', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `price`, `stock`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'jawa', 'pulau', 715.00, 0, '2025-11-27 14:05:08', '2025-11-27 14:13:30', NULL),
(7, 'sumatra ', 'pulau', 415.00, 0, '2025-11-27 15:52:46', '2025-11-29 11:24:38', NULL),
(8, 'kalimantan', 'pulau', 715222.00, 219, '2025-11-29 11:35:53', '2025-11-30 09:12:29', NULL),
(9, 'bali', 'pulau', 21321333.00, 0, '2025-11-29 23:38:56', '2025-11-30 02:34:50', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `voucher_id` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(12,2) NOT NULL,
  `final_amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','debit','credit','ewallet') DEFAULT 'cash',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `cashier_id`, `voucher_id`, `transaction_date`, `total_amount`, `final_amount`, `payment_method`, `created_at`, `updated_at`, `deleted_at`) VALUES
(11, 1, NULL, '2025-11-27 15:13:30', 715.00, 715.00, 'cash', '2025-11-27 14:13:30', '2025-11-27 14:13:30', NULL),
(12, 1, 7, '2025-11-27 17:35:24', 830.00, 0.00, 'cash', '2025-11-27 16:35:24', '2025-11-27 16:35:24', NULL),
(13, 1, NULL, '2025-11-29 12:24:38', 415.00, 415.00, 'cash', '2025-11-29 11:24:38', '2025-11-29 11:24:38', NULL),
(14, 1, NULL, '2025-11-30 03:34:50', 42642666.00, 42642666.00, 'cash', '2025-11-30 02:34:50', '2025-11-30 02:34:50', NULL),
(15, 1, 8, '2025-11-30 10:12:29', 2860888.00, 1430444.00, 'cash', '2025-11-30 09:12:29', '2025-11-30 09:12:29', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_details`
--

CREATE TABLE `transaction_details` (
  `detail_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaction_details`
--

INSERT INTO `transaction_details` (`detail_id`, `transaction_id`, `product_id`, `quantity`, `subtotal`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 11, 6, 1, 715.00, '2025-11-27 14:13:30', '2025-11-27 14:13:30', NULL),
(10, 12, 7, 2, 830.00, '2025-11-27 16:35:24', '2025-11-27 16:35:24', NULL),
(11, 13, 7, 1, 415.00, '2025-11-29 11:24:38', '2025-11-29 11:24:38', NULL),
(12, 14, 9, 2, 42642666.00, '2025-11-30 02:34:50', '2025-11-30 02:34:50', NULL),
(13, 15, 8, 4, 2860888.00, '2025-11-30 09:12:29', '2025-11-30 09:12:29', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` int(11) NOT NULL,
  `voucher_code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `vouchers`
--

INSERT INTO `vouchers` (`voucher_id`, `voucher_code`, `discount_type`, `discount_value`, `expiration_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'JAWA1', 'percentage', 19.98, '2025-11-12', '2025-11-27 14:05:27', '2025-11-29 11:36:58', NULL),
(7, 'JAWA23', 'percentage', 2000.00, '2025-11-30', '2025-11-27 16:35:08', '2025-11-29 11:36:45', '2025-11-29 11:36:45'),
(8, 'HIDUP', 'percentage', 50.00, '2025-12-24', '2025-11-30 09:12:00', '2025-11-30 09:12:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cashiers`
--
ALTER TABLE `cashiers`
  ADD PRIMARY KEY (`cashier_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `cashier_id` (`cashier_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indeks untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`),
  ADD UNIQUE KEY `voucher_code` (`voucher_code`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cashiers`
--
ALTER TABLE `cashiers`
  MODIFY `cashier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `cashiers` (`cashier_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`voucher_id`);

--
-- Ketidakleluasaan untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `transaction_details_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`),
  ADD CONSTRAINT `transaction_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
