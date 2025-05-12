-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Bulan Mei 2025 pada 14.23
-- Versi server: 10.6.15-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rental_system`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `user_type` enum('renter','owner','both') NOT NULL DEFAULT 'renter',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `address`, `phone`, `profile_image`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 'John Owner', 'owner@example.com', '$2y$10$6SLSl3LRmIT.Y3nZAFe89uQQshJLs0iLDvy1ItLXsID0XsH5NoCd2', 'Jl. Raya No. 123, Gresik', '081234567890', NULL, 'owner', '2025-05-10 02:34:32', '2025-05-10 02:34:32'),
(2, 'Jane Renter', 'renter@example.com', '$2y$10$6SLSl3LRmIT.Y3nZAFe89uQQshJLs0iLDvy1ItLXsID0XsH5NoCd2', 'Jl. Mawar No. 45, Gresik', '089876543210', NULL, 'renter', '2025-05-10 02:34:32', '2025-05-10 02:34:32'),
(3, 'dhani', 'dhani@admin.com', '$2y$10$fH6uTZT6hrDVMwkv1m5Cm.vxCa8Or.q9m.E.xn0dfoDmOTUggH7U6', 'sini', '08574857454', NULL, 'renter', '2025-05-11 07:32:55', '2025-05-11 07:32:55'),
(4, 'mojo', 'admin@admin', '$2y$10$veluVQxpwlSW82yk1k.A/e8Uma.4AtH6NJV.2FOZJ1EMkBA7L2Sj6', 'sana', '08578585755', 'profiles_052563.jpg', 'owner', '2025-05-11 07:37:49', '2025-05-12 12:22:43'),
(5, 'dhanimojo', 'dhani@gmail.com', '$2y$10$r7Gmaha1s/djySl64TlFje8JNFk68csJmP/d6Pnf3bk6ZJnpVeNNy', 'sana sini', '08585648656', NULL, 'renter', '2025-05-11 12:20:07', '2025-05-11 12:20:07'),
(6, 'admin123', 'admin@baru.com', '$2y$10$9gLuNGB6INTQ8jkn5xmkDOabXmEmbEBHiBCwb25jYS0He7RAk9vv.', 'sanaaaa sinii senanggg', '08175475653', NULL, 'owner', '2025-05-12 10:40:25', '2025-05-12 10:40:25'),
(7, 'firmandhaniadmin', 'admin@admin12.com', '$2y$10$Gcs0aw9U/txKCXTajfLK5eG306Qr4UfuXTwYLcTpq8TqZ0WjK0Wsa', 'sana sini sunu sini', '087455522563', NULL, 'owner', '2025-05-12 10:49:20', '2025-05-12 10:49:20');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
