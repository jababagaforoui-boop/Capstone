-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 04:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `freshfarmegg`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(3, 'Admin', 'admin@freshfarmegg.com', '$2y$10$T5H1u5Zh2fA2Qo8Z1q4E.u5hNYXkNw0bq9B1rH9E6cT/1kYH6KJFi', '2026-02-26 15:14:42');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `low_stock_threshold` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `low_stock_threshold`, `is_active`, `created_at`, `updated_at`) VALUES
(110, 'Iloilo Supermart Villa', 100, 1, '2026-01-14 00:30:45', '2026-01-14 00:30:45'),
(111, 'Iloilo Supermart Molo', 100, 1, '2026-01-14 00:40:34', '2026-01-14 00:40:34'),
(113, 'Iloilo Supermart Atrium', 100, 1, '2026-01-14 01:25:54', '2026-01-14 01:25:54'),
(114, 'Iloilo Supermart GQ', 100, 1, '2026-01-14 02:05:15', '2026-01-14 02:05:15'),
(115, 'Iloilo Supermart Washington', 100, 1, '2026-01-14 11:34:59', '2026-01-14 11:34:59');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `big_trays` int(10) UNSIGNED NOT NULL,
  `small_trays` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `delivered_by` varchar(100) NOT NULL,
  `egg_pieces` int(10) UNSIGNED GENERATED ALWAYS AS (`big_trays` * 20 * 12) STORED,
  `delivery_datetime` datetime DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `branch_id`, `big_trays`, `small_trays`, `delivered_by`, `delivery_datetime`, `created_at`) VALUES
(13, 113, 10, 5, '', '2026-01-14 12:20:38', '2026-01-14 12:20:38'),
(14, 113, 2, 2, '', '2026-01-14 13:14:31', '2026-01-14 13:14:31'),
(15, 114, 10, 12, '', '2026-02-01 22:19:03', '2026-02-01 22:19:03'),
(16, 114, 10, 12, '', '2026-02-01 22:27:34', '2026-02-01 22:27:34'),
(17, 111, 10, 20, '', '2026-02-01 23:03:52', '2026-02-01 23:03:52'),
(18, 111, 20, 10, '', '2026-02-28 15:30:35', '2026-02-28 15:30:35'),
(19, 114, 200, 100, 'User', '2026-03-03 15:41:12', '2026-03-03 15:41:12'),
(20, 114, 200, 100, 'User', '2026-03-03 15:52:59', '2026-03-03 15:52:59'),
(21, 115, 200, 200, 'User', '2026-03-03 22:34:33', '2026-03-03 22:34:33'),
(22, 113, 10, 10, '', '2026-03-03 22:57:05', '2026-03-03 22:57:05'),
(23, 114, 20, 20, '', '2026-03-03 22:57:25', '2026-03-03 22:57:25'),
(24, 115, 50, 50, '', '2026-03-04 09:47:47', '2026-03-04 09:47:47'),
(25, 115, 50, 50, '', '2026-03-04 09:53:16', '2026-03-04 09:53:16'),
(26, 115, 20, 60, '', '2026-03-04 10:06:18', '2026-03-04 10:06:18'),
(27, 113, 10, 10, '', '2026-03-12 09:25:31', '2026-03-12 09:25:31'),
(28, 114, 10, 20, 'User', '2026-03-12 20:45:56', '2026-03-12 20:45:56'),
(29, 114, 45, 40, '', '2026-03-12 20:57:54', '2026-03-12 20:57:54'),
(30, 114, 20, 30, '', '2026-03-12 21:09:03', '2026-03-12 21:09:03'),
(31, 114, 20, 30, '', '2026-03-12 21:14:41', '2026-03-12 21:14:41');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `big_trays` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `small_trays` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `egg_pieces` int(11) GENERATED ALWAYS AS (`big_trays` * 240 + `small_trays` * 12) STORED,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `branch_id`, `big_trays`, `small_trays`, `is_confirmed`, `is_active`, `created_at`, `updated_at`) VALUES
(10, 110, 2, 2, 0, 1, '2026-01-14 00:31:03', '2026-01-14 09:32:32'),
(11, 111, 10, 5, 0, 1, '2026-01-14 00:40:34', '2026-03-18 10:13:26'),
(12, 113, 16, 11, 0, 1, '2026-01-14 01:25:54', '2026-01-14 13:14:31'),
(13, 114, 440, 260, 0, 1, '2026-01-14 02:05:15', '2026-03-12 21:14:41'),
(14, 115, 180, 140, 0, 1, '2026-01-14 11:46:44', '2026-03-03 22:36:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `item` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_requests`
--

CREATE TABLE `order_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `trays_requested` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reset_code` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `big_trays` int(11) DEFAULT 0,
  `small_trays` int(11) DEFAULT 0,
  `message` text DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `request_datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `branch_id`, `big_trays`, `small_trays`, `message`, `admin_reply`, `status`, `request_datetime`) VALUES
(1, 106, 4, 9, 'i want to order these eggs in monday', 'ok, noted', 'confirmed', '2026-01-13 22:52:10'),
(2, 111, 3, 3, 'asap', 'ok', 'confirmed', '2026-01-14 00:57:18'),
(3, 113, 3, 3, 'order', 'noted', 'confirmed', '2026-01-14 09:35:13'),
(4, 114, 10, 20, 'need now', 'ok', 'confirmed', '2026-02-28 11:49:58'),
(5, 114, 12, 12, 'need tommorow', NULL, 'pending', '2026-02-28 20:53:13'),
(6, 114, 10, 10, 'need today', NULL, 'pending', '2026-03-03 15:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) UNSIGNED NOT NULL,
  `branch_id` int(11) NOT NULL,
  `return_type` varchar(50) NOT NULL,
  `big_trays` int(11) DEFAULT 0,
  `small_trays` int(11) DEFAULT 0,
  `egg_pieces` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `return_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `branch_id`, `return_type`, `big_trays`, `small_trays`, `egg_pieces`, `remarks`, `return_datetime`) VALUES
(1, 115, 'Expired', 0, 0, 30, 'smells bad', '2026-02-28 13:58:01'),
(2, 114, 'Cracked', 2, 9, 0, '', '2026-03-03 16:16:09'),
(3, 114, 'Damaged', 1, 1, 0, '', '2026-03-03 16:26:47'),
(4, 114, 'Expired', 1, 1, 0, '', '2026-03-03 16:27:59'),
(5, 114, 'Damaged', 1, 1, 0, '', '2026-03-03 16:31:37'),
(6, 114, 'Expired', 1, 1, 0, '', '2026-03-03 16:40:35'),
(7, 114, 'Cracked', 1, 2, 0, '', '2026-03-03 20:45:37'),
(8, 114, 'Cracked', 0, 0, 12, '', '2026-03-03 20:46:04'),
(9, 114, 'Expired', 2, 3, 0, '', '2026-03-03 20:48:07'),
(10, 115, 'Expired', 1, 6, 0, '', '2026-03-03 22:36:00'),
(11, 111, 'Expired', 0, 0, 10, '', '2026-03-18 10:13:26');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `big_trays_sold` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `small_trays_sold` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `egg_pieces_sold` int(10) UNSIGNED GENERATED ALWAYS AS (`big_trays_sold` * 30 + `small_trays_sold` * 12) STORED,
  `total_amount` decimal(10,2) GENERATED ALWAYS AS (`egg_pieces_sold` * (90 / 12)) STORED,
  `sale_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `branch_id`, `big_trays_sold`, `small_trays_sold`, `sale_datetime`) VALUES
(5, 111, 2, 3, '2026-01-14 00:56:50'),
(6, 113, 6, 10, '2026-01-14 09:34:53'),
(7, 113, 1, 1, '2026-01-14 09:38:45'),
(8, 111, 2, 2, '2026-01-14 13:02:03'),
(9, 114, 3, 3, '2026-02-28 00:46:22'),
(10, 114, 5, 5, '2026-02-28 11:56:11'),
(11, 114, 1, 2, '2026-03-03 15:20:30'),
(12, 114, 50, 50, '2026-03-03 15:42:37'),
(13, 114, 20, 20, '2026-03-03 16:00:03'),
(14, 115, 30, 60, '2026-03-03 22:35:06');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `recipient_number` varchar(20) NOT NULL,
  `status` enum('sent','failed') DEFAULT 'sent',
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` int(11) NOT NULL,
  `month` varchar(7) NOT NULL,
  `big_trays` int(11) NOT NULL DEFAULT 300,
  `small_trays` int(11) NOT NULL DEFAULT 300,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `month`, `big_trays`, `small_trays`, `created_at`) VALUES
(1, '2026-01', 188, 163, '2026-01-14 02:40:42'),
(2, '2026-02', 150, 146, '2026-02-01 12:57:53'),
(3, '2026-03', 230, 70, '2026-03-03 01:15:12');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch` varchar(100) NOT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `fullname`, `email`, `contact`, `photo`, `password`, `created_at`, `branch`, `branch_id`) VALUES
(9, 'Thea', '', 'thea@gmail.com', '', 'uploads/user_9_1772500763.png', '$2y$10$vco2lB0XzJDguj45I0CAIuXZzKB4nCr2QEbWQ1g9RhA3MVRVqCBse', '2026-02-27 07:01:09', '', 114),
(10, 'Aira', 'Nobleza', 'aira@gmail.com', '123456789', 'uploads/user_10_1772255750.png', '$2y$10$SSJ/aqVK7DwBfr1PN0kVvuqnosLF5VdXCQTdFQN3V1T394D83NrsS', '2026-02-27 07:38:47', '', 115),
(11, 'Lily', NULL, 'lily@gmail.com', NULL, NULL, '$2y$10$UfVFTh3zjOFrVfN4CoWIuOZSH/lukLE1FMCkeudHc9TqKpf9gW5km', '2026-02-27 08:03:31', '', 114),
(14, 'Gab', NULL, 'gabriel@gmail.com', NULL, NULL, '$2y$10$lgO54r0PQQF4fKD6Lo5aW.ghkvAvx.CFvGF8fliX.oEC4w3qoZ.a6', '2026-02-27 08:28:04', '', 113),
(15, 'Giah', NULL, 'giah@gmail.com', NULL, NULL, '$2y$10$TkNjOaJMrkFb74x/mKHbn./74OTHM5/xYybO7EMK1BswXQJ/iGXPq', '2026-02-28 07:31:36', '', 111),
(16, 'Alexa', NULL, 'alexa@gmail.com', NULL, NULL, '$2y$10$RJ7kJn3GPe3wdLRIwAFRhu/7p08q00bZfdjnqwfqyyxreQT4LygK6', '2026-03-12 01:27:29', '', 113),
(17, 'Lily12', NULL, 'lily12@gmail.com', NULL, NULL, '$2y$10$LzPYDwH1WY6xCzUlQTSvgOCBlyCyx.41ZBdmrIzaZCC5eP3Q1sKgW', '2026-03-18 01:32:17', '', 111);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `is_confirmed` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_pic` varchar(255) NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_name` (`branch_name`),
  ADD UNIQUE KEY `unique_branch_name` (`branch_name`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_deliveries_branch` (`branch_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inventory_branch` (`branch_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `order_requests`
--
ALTER TABLE `order_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_branch` (`branch_id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sms_branch` (`branch_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `fk_branch` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_requests`
--
ALTER TABLE `order_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `fk_deliveries_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `order_requests`
--
ALTER TABLE `order_requests`
  ADD CONSTRAINT `order_requests_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `fk_sms_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
