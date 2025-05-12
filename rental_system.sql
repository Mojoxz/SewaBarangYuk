-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 04:13 PM
-- Server version: 10.6.15-MariaDB
-- PHP Version: 8.0.30

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
-- Stand-in structure for view `active_rentals_view`
-- (See below for the actual view)
--
CREATE TABLE `active_rentals_view` (
`rental_id` int(11)
,`item_id` int(11)
,`item_name` varchar(100)
,`renter_id` int(11)
,`renter_name` varchar(100)
,`renter_phone` varchar(20)
,`start_date` date
,`end_date` date
,`total_price` decimal(10,2)
,`status` enum('pending','confirmed','active','completed','cancelled','late')
,`days_remaining` int(7)
);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 1,
  `image` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `owner_id`, `name`, `description`, `price_per_day`, `stock`, `image`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'Camping Tent', 'Large camping tent for 4 people', 50000.00, 3, NULL, 1, '2025-05-10 02:34:32', '2025-05-10 02:34:32'),
(2, 1, 'Mountain Bike', 'High-quality mountain bike', 75000.00, 2, NULL, 1, '2025-05-10 02:34:32', '2025-05-10 02:34:32'),
(3, 1, 'DSLR Camera', 'Canon EOS 1500D with 18-55mm lens', 100000.00, 1, NULL, 1, '2025-05-10 02:34:32', '2025-05-10 02:34:32'),
(6, 4, 'cek ', 'cek', 120000.00, 11, 'items_031017.jpeg', 1, '2025-05-12 06:18:36', '2025-05-12 13:13:14'),
(7, 4, 'Camping Tent', 'Tenda Camping untuk 10 ORANG', 250000.00, 5, 'items_031452.jpg', 1, '2025-05-12 06:30:52', '2025-05-12 14:06:41'),
(8, 4, 'unesa', 'sewa unesa', 12000000.00, 3, 'items_057586.png', 1, '2025-05-12 13:46:26', '2025-05-12 13:46:26'),
(9, 4, 'Traktor', 'traktor mini', 12000000.00, 1, 'items_058910.jpg', 1, '2025-05-12 14:08:30', '2025-05-12 14:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `late_fees`
--

CREATE TABLE `late_fees` (
  `fee_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `days_late` int(11) NOT NULL,
  `is_paid` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `notification_type` enum('reminder','new_order','confirmation','late_fee','return') NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `notification_type`, `related_id`, `created_at`) VALUES
(1, 1, 'Permintaan Penyewaan Baru', 'Ada permintaan penyewaan baru untuk barang \'Camping Tent\' dari dhanimojo.', 0, 'new_order', 1, '2025-05-11 12:20:47'),
(2, 4, 'Permintaan Penyewaan Baru', 'Ada permintaan penyewaan baru untuk barang \'tes\' dari dhanimojo.', 0, 'new_order', 2, '2025-05-11 12:22:04'),
(3, 5, 'Permintaan Penyewaan Dikonfirmasi', 'Permintaan penyewaan Anda untuk barang \'tes\' telah dikonfirmasi.', 0, 'confirmation', 2, '2025-05-11 12:23:10'),
(4, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'tes\' telah dimulai.', 0, 'confirmation', 2, '2025-05-11 12:23:15'),
(5, 4, 'Permintaan Penyewaan Baru', 'Ada permintaan penyewaan baru untuk barang \'Camping Tent\' dari dhanimojo.', 0, 'new_order', 3, '2025-05-12 12:25:19'),
(6, 5, 'Permintaan Penyewaan Dikonfirmasi', 'Permintaan penyewaan Anda untuk barang \'Camping Tent\' telah dikonfirmasi.', 0, 'confirmation', 3, '2025-05-12 12:26:00'),
(7, 4, 'Permintaan Penyewaan Baru', 'Ada permintaan penyewaan baru untuk barang \'cek \' dari dhanimojo.', 0, 'new_order', 4, '2025-05-12 12:30:24'),
(8, 5, 'Permintaan Penyewaan Dikonfirmasi', 'Permintaan penyewaan Anda untuk barang \'cek \' telah dikonfirmasi.', 0, 'confirmation', 4, '2025-05-12 12:30:39'),
(9, 5, 'Permintaan Penyewaan Dikonfirmasi', 'Permintaan penyewaan Anda untuk barang \'cek \' telah dikonfirmasi.', 0, 'confirmation', 4, '2025-05-12 12:32:57'),
(10, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'Camping Tent\' telah dimulai.', 0, 'confirmation', 3, '2025-05-12 12:33:12'),
(11, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'Camping Tent\' telah dimulai.', 0, 'confirmation', 3, '2025-05-12 12:34:05'),
(12, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'Camping Tent\' telah dimulai.', 0, 'confirmation', 3, '2025-05-12 12:34:28'),
(13, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'Camping Tent\' telah dimulai.', 0, 'confirmation', 3, '2025-05-12 12:35:19'),
(14, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'cek \' telah dimulai.', 0, 'confirmation', 4, '2025-05-12 12:35:22'),
(15, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'cek \' telah dimulai.', 0, 'confirmation', 4, '2025-05-12 12:40:46'),
(16, 5, 'Penyewaan Selesai', 'Penyewaan Anda untuk barang \'tes\' telah selesai. Terima kasih telah menggunakan layanan kami.', 0, 'return', 2, '2025-05-12 12:40:55'),
(17, 4, 'Permintaan Penyewaan Baru', 'Ada permintaan penyewaan baru untuk barang \'Camping Tent\' dari dhanimojo.', 0, 'new_order', 5, '2025-05-12 13:02:29'),
(18, 5, 'Permintaan Penyewaan Dikonfirmasi', 'Permintaan penyewaan Anda untuk barang \'Camping Tent\' telah dikonfirmasi.', 0, 'confirmation', 5, '2025-05-12 13:02:51'),
(19, 5, 'Penyewaan Dimulai', 'Penyewaan Anda untuk barang \'Camping Tent\' telah dimulai.', 0, 'confirmation', 5, '2025-05-12 13:02:55'),
(20, 5, 'Penyewaan Selesai', 'Penyewaan Anda untuk barang \'cek \' telah selesai. Terima kasih telah menggunakan layanan kami.', 0, 'return', 4, '2025-05-12 13:13:14'),
(21, 5, 'Penyewaan Selesai', 'Penyewaan Anda untuk barang \'Camping Tent\' telah selesai. Terima kasih telah menggunakan layanan kami.', 0, 'return', 3, '2025-05-12 13:42:37'),
(22, 5, 'Penyewaan Selesai', 'Penyewaan Anda untuk barang \'Camping Tent\' telah selesai. Terima kasih telah menggunakan layanan kami.', 0, 'return', 5, '2025-05-12 14:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `renter_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','active','completed','cancelled','late') NOT NULL DEFAULT 'pending',
  `id_card_image` varchar(255) NOT NULL,
  `return_status` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `item_id`, `renter_id`, `start_date`, `end_date`, `total_price`, `status`, `id_card_image`, `return_status`, `created_at`, `updated_at`) VALUES
(1, 1, 5, '2025-05-12', '2025-05-15', 200000.00, 'pending', '1746966047_img002.jpg', 0, '2025-05-11 12:20:47', '2025-05-11 12:20:47'),
(3, 7, 5, '2025-05-13', '2025-05-14', 500000.00, 'completed', 'id_cards_052719.png', 1, '2025-05-12 12:25:19', '2025-05-12 13:42:37'),
(4, 6, 5, '2025-05-12', '2025-05-13', 240000.00, 'completed', 'id_cards_053024.png', 1, '2025-05-12 12:30:24', '2025-05-12 13:13:14'),
(5, 7, 5, '2025-05-29', '2025-05-30', 500000.00, 'completed', 'id_cards_054949.jpg', 1, '2025-05-12 13:02:29', '2025-05-12 14:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
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
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `address`, `phone`, `profile_image`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 'John Owner', 'owner@example.com', '$2y$10$6SLSl3LRmIT.Y3nZAFe89uQQshJLs0iLDvy1ItLXsID0XsH5NoCd2', 'Jl. Raya No. 123, Gresik', '081234567890', NULL, 'owner', '2025-05-10 02:34:32', '2025-05-10 02:34:32'),
(2, 'Jane Renter', 'renter@example.com', '$2y$10$6SLSl3LRmIT.Y3nZAFe89uQQshJLs0iLDvy1ItLXsID0XsH5NoCd2', 'Jl. Mawar No. 45, Gresik', '089876543210', NULL, 'renter', '2025-05-10 02:34:32', '2025-05-10 02:34:32'),
(3, 'dhani', 'dhani@admin.com', '$2y$10$fH6uTZT6hrDVMwkv1m5Cm.vxCa8Or.q9m.E.xn0dfoDmOTUggH7U6', 'sini', '08574857454', NULL, 'renter', '2025-05-11 07:32:55', '2025-05-11 07:32:55'),
(4, 'mojo', 'admin@admin', '$2y$10$veluVQxpwlSW82yk1k.A/e8Uma.4AtH6NJV.2FOZJ1EMkBA7L2Sj6', 'sana', '08578585755', 'profiles_052563.jpg', 'owner', '2025-05-11 07:37:49', '2025-05-12 12:22:43'),
(5, 'dhanimojo', 'dhani@gmail.com', '$2y$10$r7Gmaha1s/djySl64TlFje8JNFk68csJmP/d6Pnf3bk6ZJnpVeNNy', 'sana sini', '08585648656', 'profiles_057494.jpg', 'renter', '2025-05-11 12:20:07', '2025-05-12 13:44:54'),
(6, 'admin123', 'admin@baru.com', '$2y$10$9gLuNGB6INTQ8jkn5xmkDOabXmEmbEBHiBCwb25jYS0He7RAk9vv.', 'sanaaaa sinii senanggg', '08175475653', NULL, 'owner', '2025-05-12 10:40:25', '2025-05-12 10:40:25'),
(7, 'firmandhaniadmin', 'admin@admin12.com', '$2y$10$Gcs0aw9U/txKCXTajfLK5eG306Qr4UfuXTwYLcTpq8TqZ0WjK0Wsa', 'sana sini sunu sini', '087455522563', NULL, 'owner', '2025-05-12 10:49:20', '2025-05-12 10:49:20');

-- --------------------------------------------------------

--
-- Structure for view `active_rentals_view`
--
DROP TABLE IF EXISTS `active_rentals_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_rentals_view`  AS SELECT `r`.`rental_id` AS `rental_id`, `r`.`item_id` AS `item_id`, `i`.`name` AS `item_name`, `r`.`renter_id` AS `renter_id`, `u`.`name` AS `renter_name`, `u`.`phone` AS `renter_phone`, `r`.`start_date` AS `start_date`, `r`.`end_date` AS `end_date`, `r`.`total_price` AS `total_price`, `r`.`status` AS `status`, to_days(`r`.`end_date`) - to_days(curdate()) AS `days_remaining` FROM ((`rentals` `r` join `items` `i` on(`r`.`item_id` = `i`.`item_id`)) join `users` `u` on(`r`.`renter_id` = `u`.`user_id`)) WHERE `r`.`status` in ('confirmed','active') ORDER BY to_days(`r`.`end_date`) - to_days(curdate()) ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `late_fees`
--
ALTER TABLE `late_fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `renter_id` (`renter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `late_fees`
--
ALTER TABLE `late_fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `late_fees`
--
ALTER TABLE `late_fees`
  ADD CONSTRAINT `late_fees_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`renter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
