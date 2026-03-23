-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 23, 2026 at 05:27 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundry_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `contact_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `email_verified_at`, `contact_num`, `password`, `remember_token`, `created_at`, `updated_at`, `archived`) VALUES
(1, 'John Lee Bag', 'jonleemad17@gmail.com', NULL, '090909090', '$2y$10$rrKjSJ7LCHsZ2sKCiT1mHOuRUiZ/W2F/1N6Ru0NZ6YPC6G6sF141W', NULL, '2025-04-25 05:08:20', '2025-11-15 12:18:24', 0),
(10, 'yeye', 'yeye@gmail.com', NULL, '09123456762', '$2y$10$0T1qCBelpdNHSR9royMMeONQgKfLpCtqbMOLzZgnAvIU.st9gIYWC', NULL, '2025-11-15 03:26:08', '2026-02-12 06:24:28', 0);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `user_type` enum('admin','staff','super_admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_type`, `user_name`, `action`, `description`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 01:52:24', '2025-08-26 01:52:24'),
(2, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 01:52:44', '2025-08-26 01:52:44'),
(3, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 01:57:17', '2025-08-26 01:57:17'),
(4, 9, 'admin', 'Lesterrr', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 02:07:19', '2025-08-26 02:07:19'),
(5, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 02:31:00', '2025-08-26 02:31:00'),
(6, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 02:42:55', '2025-08-26 02:42:55'),
(9, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 03:15:12', '2025-08-26 03:15:12'),
(10, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 03:21:55', '2025-08-26 03:21:55'),
(12, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 03:28:21', '2025-08-26 03:28:21'),
(13, 9, 'admin', 'Lesterrr', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 03:28:35', '2025-08-26 03:28:35'),
(14, 9, 'admin', 'Lesterrr', 'add_supply', 'Created new supply product: dwadawdw (Brand: N/A, Price: ₱123)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 03:40:37', '2025-08-26 03:40:37'),
(17, 9, 'admin', 'Lesterrr', 'add_supply', 'Created new supply product: awdw (Brand: N/A, Price: ₱231)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 04:10:17', '2025-08-26 04:10:17'),
(20, 9, 'admin', 'Lesterrr', 'update_supply', 'Updated supply product ID: 19 - dawdrr (Brand: N/A, Price: ₱123)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 04:22:05', '2025-08-26 04:22:05'),
(28, 9, 'admin', 'Lesterrr', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 05:06:21', '2025-08-26 05:06:21'),
(29, 9, 'admin', 'Lesterrr', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 05:06:35', '2025-08-26 05:06:35'),
(35, 9, 'admin', 'Lesterrr', 'update_supply', 'Updated supply product ID: 19 - eyy (Brand: N/A, Price: ₱123)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 06:09:00', '2025-08-26 06:09:00'),
(45, 9, 'admin', 'Lesterrr', 'inventory_transaction', 'IN transaction: 2 units of Tide (ID: 1)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 06:55:12', '2025-08-26 06:55:12'),
(48, 9, 'admin', 'Lesterrr', 'inventory_transaction', 'IN transaction: 1 stock(s) of Del', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 07:06:24', '2025-08-26 07:06:24'),
(49, 9, 'admin', 'Lesterrr', 'edit_transaction', 'Edited transaction #88: Changed from 1 IN to 2 IN for Del', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 07:08:52', '2025-08-26 07:08:52'),
(50, 9, 'admin', 'Lesterrr', 'edit_transaction', 'Edited transaction: Changed from 2 IN to 1 IN for Del', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 07:10:00', '2025-08-26 07:10:00'),
(61, 1, 'admin', 'Haha', 'delete_transaction', 'Deleted transaction: 1 IN for Del', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 08:13:45', '2025-08-26 08:13:45'),
(62, 1, 'admin', 'Haha', 'add_utility', 'Added electricity bill: ₱1,500.00 for August 2025', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 08:25:12', '2025-08-26 08:25:12'),
(63, 1, 'admin', 'Haha', 'add_utility', 'Added water bill: ₱1,000.00 for August 2025', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 08:25:40', '2025-08-26 08:25:40'),
(64, 1, 'admin', 'Haha', 'add_utility', 'Added maintenance bill: ₱500.00 for August 2025 - washing fix', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 08:26:16', '2025-08-26 08:26:16'),
(77, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: dryer per round (₱70.00 → ₱71.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:30:38', '2025-08-26 10:30:38'),
(78, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order #400 and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:32:06', '2025-08-26 10:32:06'),
(79, 1, 'admin', 'Haha', 'update_supply', 'Updated supply product ID: 20 - dwadawd (Brand: N/A, Price: ₱123)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:32:29', '2025-08-26 10:32:29'),
(80, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order(s) for customer: Han (ID: 7)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:33:17', '2025-08-26 10:33:17'),
(81, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order #393 for customer ID: 2. Status: Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:35:55', '2025-08-26 10:35:55'),
(82, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Han and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:53:52', '2025-08-26 10:53:52'),
(83, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order status to \"Pending\" for customer: ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:54:03', '2025-08-26 10:54:03'),
(84, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order status to \"Pending\" for customer: van', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:57:55', '2025-08-26 10:57:55'),
(85, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order status to \"Pending\" for customer: van', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 10:58:17', '2025-08-26 10:58:17'),
(87, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Amount Tendered: ₱0.00 → ₱45.00; Payment Status: Unknown → Unpaid; Laundry Details: Detergent Scoops: 2 → 1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 11:04:39', '2025-08-26 11:04:39'),
(89, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Amount Tendered: ₱50.00 → ₱40.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 11:10:30', '2025-08-26 11:10:30'),
(90, 1, 'admin', 'Haha', 'delete_product', 'Deleted supply product: \"awdw\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 11:28:01', '2025-08-26 11:28:01'),
(91, 1, 'admin', 'Haha', 'delete_product_error', 'Attempted to delete product \"Zonrox\" but it still has stock in inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 11:41:39', '2025-08-26 11:41:39'),
(92, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-26 11:41:47', '2025-08-26 11:41:47'),
(93, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:13:48', '2025-08-28 05:13:48'),
(94, 1, 'admin', 'Haha', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱80.00, Tendered: ₱50.00, Queue Number: Q3251', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:27:49', '2025-08-28 05:27:49'),
(95, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:29:47', '2025-08-28 05:29:47'),
(96, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:30:57', '2025-08-28 05:30:57'),
(97, 8, 'staff', 'Hannah Janee', 'delete_transaction', 'Deleted transaction: 1 Used for Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:33:08', '2025-08-28 05:33:08'),
(99, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: van - Changes: Amount Tendered: ₱50.00 → ₱40.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:35:34', '2025-08-28 05:35:34'),
(101, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: van - Changes: Amount Tendered: ₱40.00 → ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:37:52', '2025-08-28 05:37:52'),
(103, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: van - Changes: Amount Tendered: ₱50.00 → ₱40.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:38:07', '2025-08-28 05:38:07'),
(104, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: van - From: ₱40.00 To: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:38:32', '2025-08-28 05:38:32'),
(105, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Amount Tendered: ₱40.00 → ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:38:32', '2025-08-28 05:38:32'),
(107, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: van - Changes: Amount Tendered: ₱50.00 → ₱40.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-08-28 05:40:17', '2025-08-28 05:40:17'),
(108, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-07 08:51:35', '2025-09-07 08:51:35'),
(109, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: Lester Madrid - Changes: Status: Ready for Pickup → Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-07 10:56:28', '2025-09-07 10:56:28'),
(110, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-07 10:59:39', '2025-09-07 10:59:39'),
(111, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - No significant changes detected', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-07 11:00:43', '2025-09-07 11:00:43'),
(112, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: 1 → Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-07 11:28:21', '2025-09-07 11:28:21'),
(113, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: 1 → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-07 11:28:55', '2025-09-07 11:28:55'),
(114, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Lester Madrid - Changes: Status: Claimed → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-07 11:29:04', '2025-09-07 11:29:04'),
(115, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 1 → 4, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 00:55:33', '2025-09-08 00:55:33'),
(116, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 0 → 1, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 00:56:03', '2025-09-08 00:56:03'),
(117, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 00:58:11', '2025-09-08 00:58:11'),
(118, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 00:58:36', '2025-09-08 00:58:36'),
(119, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: Yes → No, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:26:20', '2025-09-08 01:26:20'),
(120, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 0 → 1, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:26:44', '2025-09-08 01:26:44'),
(121, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: No → Yes, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:26:53', '2025-09-08 01:26:53'),
(122, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 0 → 1, Folding Service: Yes → No, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:28:04', '2025-09-08 01:28:04'),
(123, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: No → Yes, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:28:17', '2025-09-08 01:28:17'),
(124, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: Jane - From: ₱40.00 To: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:28:47', '2025-09-08 01:28:47'),
(125, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Amount Tendered: ₱40.00 → ₱50.00; Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:28:47', '2025-09-08 01:28:47'),
(126, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Folding Service: No → Yes, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:28:57', '2025-09-08 01:28:57'),
(127, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 0 → 1, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:44:37', '2025-09-08 01:44:37'),
(128, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: van - From: ₱40.00 To: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:44:51', '2025-09-08 01:44:51'),
(129, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Amount Tendered: ₱40.00 → ₱50.00; Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:44:51', '2025-09-08 01:44:51'),
(130, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: Yes → No, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:44:57', '2025-09-08 01:44:57'),
(131, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: van - From: ₱50.00 To: ₱70.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:45:14', '2025-09-08 01:45:14'),
(132, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Amount Tendered: ₱50.00 → ₱70.00; Laundry Details: Detergent Scoops: 0 → 4, Detergent Product: None → 1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:45:14', '2025-09-08 01:45:14'),
(133, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Status: Pending → Ongoing; Payment Status: Unpaid → Paid; Laundry Details: Detergent Product: 1 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:45:35', '2025-09-08 01:45:35'),
(134, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Status: Ongoing → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:46:47', '2025-09-08 01:46:47'),
(135, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Payment Status: Paid → Unpaid; Laundry Details: Detergent Scoops: 0 → 1, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:46:55', '2025-09-08 01:46:55'),
(136, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Fabcon Cups: 0 → 1, Fabcon Product: None → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:47:10', '2025-09-08 01:47:10'),
(137, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: No → Yes, Fabcon Product: 2 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:47:17', '2025-09-08 01:47:17'),
(138, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:47:33', '2025-09-08 01:47:33'),
(139, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Payment Status: Unpaid → Paid; Laundry Details: Folding Service: Yes → No, Fabcon Cups: 1 → 0, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 01:47:46', '2025-09-08 01:47:46'),
(140, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Payment Status: Paid → Unpaid; Laundry Details: Detergent Scoops: 0 → 2, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 02:06:33', '2025-09-08 02:06:33'),
(141, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Payment Status: Unpaid → Paid; Laundry Details: Folding Service: No → Yes, Detergent Product: 17 → None', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 02:06:39', '2025-09-08 02:06:39'),
(142, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Payment Status: Paid → Unpaid; Laundry Details: Detergent Scoops: 0 → 1, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 02:06:47', '2025-09-08 02:06:47'),
(143, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 02:06:52', '2025-09-08 02:06:52'),
(144, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: Yes → No', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:34:33', '2025-09-08 03:34:33'),
(145, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Fabcon Cups: 0 → 1, Fabcon Product: None → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:34:46', '2025-09-08 03:34:46'),
(146, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Folding Service: No → Yes, Bleach Cups: 0 → 1, Bleach Product: None → 18', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:35:00', '2025-09-08 03:35:00'),
(147, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Fabcon Cups: 1 → , Fabcon Product: 2 → ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:35:07', '2025-09-08 03:35:07'),
(148, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Fabcon Cups: 0 → 1, Fabcon Product: None → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:35:17', '2025-09-08 03:35:17'),
(149, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Status: Pending → Ongoing; Laundry Details: Folding Service: Yes → No', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:35:34', '2025-09-08 03:35:34'),
(150, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Status: Ongoing → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:36:59', '2025-09-08 03:36:59'),
(151, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: van - Changes: Laundry Details: Detergent Scoops: 2 → 1, Detergent Product: 17 → 1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:37:09', '2025-09-08 03:37:09'),
(152, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 0 → 2, Detergent Product: None → 17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:37:35', '2025-09-08 03:37:35'),
(153, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 2 → 1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:37:54', '2025-09-08 03:37:54'),
(154, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:38:33', '2025-09-08 03:38:33'),
(155, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: van and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:38:37', '2025-09-08 03:38:37'),
(156, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: evange and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:38:39', '2025-09-08 03:38:39'),
(157, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Jane and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:38:42', '2025-09-08 03:38:42'),
(158, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:39:41', '2025-09-08 03:39:41'),
(159, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 2 → 3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:41:01', '2025-09-08 03:41:01'),
(160, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:41:33', '2025-09-08 03:41:33'),
(161, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:46:58', '2025-09-08 03:46:58'),
(162, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:47:08', '2025-09-08 03:47:08'),
(163, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:47:24', '2025-09-08 03:47:24'),
(164, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:47:32', '2025-09-08 03:47:32'),
(165, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Jane (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:48:23', '2025-09-08 03:48:23'),
(166, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 3 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 03:49:23', '2025-09-08 03:49:23'),
(167, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - No significant changes detected', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:07:03', '2025-09-08 05:07:03'),
(168, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:41:54', '2025-09-08 05:41:54'),
(169, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 2 → 3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:42:12', '2025-09-08 05:42:12'),
(170, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: Jane - From: ₱50.00 To: ₱60.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:42:30', '2025-09-08 05:42:30'),
(171, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Amount Tendered: ₱50.00 → ₱60.00; Laundry Details: Detergent Scoops: 3 → 5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:42:30', '2025-09-08 05:42:30'),
(172, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 5 → 1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:44:14', '2025-09-08 05:44:14'),
(173, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:44:30', '2025-09-08 05:44:30'),
(174, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Folding Service: Yes → No', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:44:49', '2025-09-08 05:44:49'),
(175, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Folding Service: No → Yes, Fabcon Cups: 0 → 1, Fabcon Product: None → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:45:03', '2025-09-08 05:45:03'),
(176, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Folding Service: Yes → No', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:45:10', '2025-09-08 05:45:10'),
(177, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 2 → 1, Fabcon Cups: 1 → , Fabcon Product: 2 → ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 05:45:22', '2025-09-08 05:45:22'),
(178, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 06:09:16', '2025-09-08 06:09:16'),
(179, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: Jane - From: ₱50.00 To: ₱60.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 06:10:29', '2025-09-08 06:10:29'),
(180, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Amount Tendered: ₱50.00 → ₱60.00; Laundry Details: Fabcon Cups: 0 → 1, Fabcon Product: None → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 06:10:29', '2025-09-08 06:10:29'),
(181, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 2 → 1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 06:37:34', '2025-09-08 06:37:34'),
(182, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 1 → 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 06:39:42', '2025-09-08 06:39:42'),
(183, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Folding Service: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 06:39:48', '2025-09-08 06:39:48'),
(184, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Product: 17 → 1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 06:40:12', '2025-09-08 06:40:12'),
(185, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: Jane - From: ₱60.00 To: ₱70.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 07:00:56', '2025-09-08 07:00:56'),
(186, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Amount Tendered: ₱60.00 → ₱70.00; Laundry Details: Detergent Scoops: 2 → 4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 07:00:56', '2025-09-08 07:00:56'),
(187, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Laundry Details: Detergent Scoops: 4 → 2, Detergent Product: Tide → surf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 07:01:22', '2025-09-08 07:01:22'),
(188, 1, 'admin', 'Haha', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱73.00, Tendered: ₱40.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 07:08:09', '2025-09-08 07:08:09'),
(189, 1, 'admin', 'Haha', 'update_order', 'Amount tendered modified for customer: Jane - From: ₱70.00 To: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 07:11:05', '2025-09-08 07:11:05'),
(190, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Amount Tendered: ₱70.00 → ₱50.00; Laundry Details: Detergent Scoops: 2 → 1, Detergent Product: surf → Tide', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 07:11:05', '2025-09-08 07:11:05'),
(191, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Jane - Changes: Amount Tendered: ₱50.00 → ₱45.00; Laundry Details: Detergent Product: Tide → surf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-08 07:16:07', '2025-09-08 07:16:07'),
(192, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 04:07:03', '2025-09-09 04:07:03'),
(193, 8, 'staff', 'Hannah Janee', 'create_order', 'Created 1 laundry order for customer: Jane (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:06:41', '2025-09-09 05:06:41'),
(194, 8, 'staff', 'Hannah Janee', 'delete_order', 'Deleted laundry order for customer: Jane and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:07:02', '2025-09-09 05:07:02'),
(195, 8, 'staff', 'Hannah Janee', 'create_order', 'Created 1 laundry order for customer: yeehaa (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:10:06', '2025-09-09 05:10:06'),
(196, 8, 'staff', 'Hannah Janee', 'delete_order', 'Deleted laundry order for customer: yeehaa and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:10:34', '2025-09-09 05:10:34'),
(197, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:11:49', '2025-09-09 05:11:49'),
(198, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:13:41', '2025-09-09 05:13:41'),
(199, 8, 'staff', 'Hannah Janee', 'create_order', 'Created 1 laundry order for customer: Janjan (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:24:17', '2025-09-09 05:24:17'),
(200, 8, 'staff', 'Hannah Janee', 'delete_order', 'Deleted laundry order for customer: Janjan and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 05:24:25', '2025-09-09 05:24:25'),
(201, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:22:38', '2025-09-09 07:22:38'),
(202, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:26:04', '2025-09-09 07:26:04'),
(203, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:36:24', '2025-09-09 07:36:24'),
(204, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:36:30', '2025-09-09 07:36:30'),
(205, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱70.00 → ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:37:40', '2025-09-09 07:37:40'),
(206, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱80.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:37:58', '2025-09-09 07:37:58'),
(207, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱70.00 → ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:38:23', '2025-09-09 07:38:23'),
(208, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱80.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:39:35', '2025-09-09 07:39:35'),
(209, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱70.00 → ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 07:40:17', '2025-09-09 07:40:17'),
(210, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 08:42:58', '2025-09-09 08:42:58'),
(211, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 08:43:06', '2025-09-09 08:43:06'),
(212, 8, 'staff', 'Hannah Janee', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory (₱10.00 refunded)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 09:44:11', '2025-09-09 09:44:11'),
(213, 8, 'staff', 'Hannah Janee', 'delete_order', 'Deleted laundry order for customer: Jane and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 09:44:16', '2025-09-09 09:44:16'),
(214, 8, 'staff', 'Hannah Janee', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 09:44:33', '2025-09-09 09:44:33'),
(215, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 09:53:22', '2025-09-09 09:53:22'),
(216, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 09:53:30', '2025-09-09 09:53:30'),
(217, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Hannah Janee and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:23:36', '2025-09-09 10:23:36'),
(218, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Lester Madrid - Changes: Status: Ready for Pickup → Pending; Payment Status: Paid → Unpaid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:23:44', '2025-09-09 10:23:44'),
(219, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Ready for Pickup → Pending; Payment Status: Paid → Unpaid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:23:53', '2025-09-09 10:23:53'),
(220, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Lester Madrid and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:23:57', '2025-09-09 10:23:57'),
(221, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Hannah Janee and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:24:02', '2025-09-09 10:24:02'),
(222, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: 1 → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:24:09', '2025-09-09 10:24:09'),
(223, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Madrid and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:24:12', '2025-09-09 10:24:12'),
(224, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Vennies - Changes: Status: Ready for Pickup → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:24:19', '2025-09-09 10:24:19'),
(225, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:24:27', '2025-09-09 10:24:27'),
(226, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Jane and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:24:30', '2025-09-09 10:24:30'),
(227, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:24:52', '2025-09-09 10:24:52'),
(228, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: dryer per round (₱71.00 → ₱70.00), Service: wash per round (₱80.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:25:04', '2025-09-09 10:25:04'),
(229, 1, 'admin', 'Haha', 'inventory_transaction', 'IN transaction: 5 stock(s) of Del', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:26:49', '2025-09-09 10:26:49'),
(230, 1, 'admin', 'Haha', 'inventory_transaction', 'IN transaction: 5 stock(s) of surf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:27:02', '2025-09-09 10:27:02'),
(231, 1, 'admin', 'Haha', 'inventory_transaction', 'IN transaction: 5 stock(s) of Tide', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:27:14', '2025-09-09 10:27:14'),
(232, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Pending → Ongoing; Laundry Details: Folding Service: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:31:17', '2025-09-09 10:31:17'),
(233, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Ongoing → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:31:29', '2025-09-09 10:31:29'),
(234, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Ready for Pickup → Claimed; Amount Tendered: ₱50.00 → ₱80.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:31:46', '2025-09-09 10:31:46'),
(235, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:51:06', '2025-09-09 10:51:06'),
(236, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:52:14', '2025-09-09 10:52:14'),
(237, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:52:38', '2025-09-09 10:52:38'),
(238, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:53:29', '2025-09-09 10:53:29'),
(239, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:53:45', '2025-09-09 10:53:45'),
(240, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:54:43', '2025-09-09 10:54:43'),
(241, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 10:58:43', '2025-09-09 10:58:43'),
(242, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 11:02:41', '2025-09-09 11:02:41'),
(243, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Ongoing → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 11:05:51', '2025-09-09 11:05:51'),
(244, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 11:06:47', '2025-09-09 11:06:47'),
(245, 1, 'admin', 'Haha', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱80.00, Tendered: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 11:08:16', '2025-09-09 11:08:16'),
(246, 1, 'admin', 'Haha', 'add_supply', 'Created new supply product: Ariel (Brand: N/A, Price: ₱1500)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 11:08:58', '2025-09-09 11:08:58');
INSERT INTO `audit_logs` (`id`, `user_id`, `user_type`, `user_name`, `action`, `description`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(247, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: 0 → Ready for Pickup; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-09 11:18:01', '2025-09-09 11:18:01'),
(248, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-10 05:23:58', '2025-09-10 05:23:58'),
(249, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-10 05:24:31', '2025-09-10 05:24:31'),
(250, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-10 05:24:38', '2025-09-10 05:24:38'),
(251, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-10 05:24:50', '2025-09-10 05:24:50'),
(252, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-10 05:36:52', '2025-09-10 05:36:52'),
(253, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 01:40:38', '2025-09-11 01:40:38'),
(254, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 02:04:51', '2025-09-11 02:04:51'),
(255, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Janjan (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 02:05:32', '2025-09-11 02:05:32'),
(256, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Janjan and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 02:05:35', '2025-09-11 02:05:35'),
(257, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Hannah Janee and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 02:05:36', '2025-09-11 02:05:36'),
(258, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Janjan (Total: ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 02:05:46', '2025-09-11 02:05:46'),
(259, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 05:47:11', '2025-09-11 05:47:11'),
(260, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-11 05:56:13', '2025-09-11 05:56:13'),
(261, 1, 'admin', 'Haha', 'delete_product_error', 'Attempted to delete product \"Yes\" but it still has stock in inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:27:31', '2025-09-12 01:27:31'),
(262, 1, 'admin', 'Haha', 'inventory_transaction', 'OUT transaction: 3 stock(s) of Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:27:53', '2025-09-12 01:27:53'),
(263, 1, 'admin', 'Haha', 'delete_product_error', 'Attempted to delete product \"Yes\" but it still has stock in inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:27:59', '2025-09-12 01:27:59'),
(264, 1, 'admin', 'Haha', 'delete_product', 'Deleted supply product: \"Yes\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:29:31', '2025-09-12 01:29:31'),
(265, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:50:34', '2025-09-12 01:50:34'),
(266, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:50:39', '2025-09-12 01:50:39'),
(267, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Janjan and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:50:41', '2025-09-12 01:50:41'),
(268, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Janjan (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:50:50', '2025-09-12 01:50:50'),
(269, 1, 'admin', 'Haha', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱80.00, Tendered: ₱40.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:52:39', '2025-09-12 01:52:39'),
(270, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:52:50', '2025-09-12 01:52:50'),
(271, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:52:53', '2025-09-12 01:52:53'),
(272, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:52:55', '2025-09-12 01:52:55'),
(273, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Janjan and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:52:57', '2025-09-12 01:52:57'),
(274, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Janjan (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:53:05', '2025-09-12 01:53:05'),
(275, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Janjan and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:53:10', '2025-09-12 01:53:10'),
(276, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:53:59', '2025-09-12 01:53:59'),
(277, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:54:08', '2025-09-12 01:54:08'),
(278, 1, 'admin', 'Haha', 'order_delete_error', 'Failed to delete laundry order for customer: Hannah Janee - Error: Cannot delete an order that is Ongoing, Ready for Pickup, or Claimed.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:55:09', '2025-09-12 01:55:09'),
(279, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 01:55:32', '2025-09-12 01:55:32'),
(280, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 02:38:45', '2025-09-12 02:38:45'),
(281, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 02:38:54', '2025-09-12 02:38:54'),
(282, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 02:52:44', '2025-09-12 02:52:44'),
(283, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 02:52:48', '2025-09-12 02:52:48'),
(284, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Han (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 02:53:02', '2025-09-12 02:53:02'),
(285, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Han (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 02:53:13', '2025-09-12 02:53:13'),
(286, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Han and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:02:09', '2025-09-12 03:02:09'),
(287, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:02:19', '2025-09-12 03:02:19'),
(288, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:19:22', '2025-09-12 03:19:22'),
(289, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:19:31', '2025-09-12 03:19:31'),
(290, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:20:22', '2025-09-12 03:20:22'),
(291, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:20:29', '2025-09-12 03:20:29'),
(292, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Vennies and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:20:34', '2025-09-12 03:20:34'),
(293, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Han and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:20:36', '2025-09-12 03:20:36'),
(294, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: yeehaa (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:20:57', '2025-09-12 03:20:57'),
(295, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: 1 → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:06', '2025-09-12 03:22:06'),
(296, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Claimed → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:14', '2025-09-12 03:22:14'),
(297, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: 1 → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:19', '2025-09-12 03:22:19'),
(298, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: Hannah Janee and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:31', '2025-09-12 03:22:31'),
(299, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: yeehaa and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:33', '2025-09-12 03:22:33'),
(300, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: yeehaa (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:45', '2025-09-12 03:22:45'),
(301, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: yeehaa and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:49', '2025-09-12 03:22:49'),
(302, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:22:51', '2025-09-12 03:22:51'),
(303, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: yeehaa (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:00', '2025-09-12 03:23:00'),
(304, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: yeehaa - Changes: Status: Pending → Claimed; Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:12', '2025-09-12 03:23:12'),
(305, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: yeehaa (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:21', '2025-09-12 03:23:21'),
(306, 1, 'admin', 'Haha', 'order_delete_error', 'Failed to delete laundry order for customer: yeehaa - Error: Cannot delete an order that is Ongoing, Ready for Pickup, or Claimed.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:29', '2025-09-12 03:23:29'),
(307, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: yeehaa - Changes: Status: Claimed → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:43', '2025-09-12 03:23:43'),
(308, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: yeehaa and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:45', '2025-09-12 03:23:45'),
(309, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: yeehaa and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:47', '2025-09-12 03:23:47'),
(310, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:23:50', '2025-09-12 03:23:50'),
(311, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Janjan (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 03:24:01', '2025-09-12 03:24:01'),
(312, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Status: Pending → Ready for Pickup; Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:56:04', '2025-09-12 04:56:04'),
(313, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Status: Ready for Pickup → Pending', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:56:41', '2025-09-12 04:56:41'),
(314, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Amount Tendered: ₱100.00 → ₱50.00; Payment Status: Paid → Unpaid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:56:48', '2025-09-12 04:56:48'),
(315, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:56:54', '2025-09-12 04:56:54'),
(316, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Status: Ongoing → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:57:31', '2025-09-12 04:57:31'),
(317, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:58:18', '2025-09-12 04:58:18'),
(318, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Vennies - Changes: Status: Pending → Claimed; Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:58:44', '2025-09-12 04:58:44'),
(319, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Han (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 04:58:59', '2025-09-12 04:58:59'),
(320, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 05:08:31', '2025-09-12 05:08:31'),
(321, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Status: Ready for Pickup → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 05:10:26', '2025-09-12 05:10:26'),
(322, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Han - Changes: Status: Pending → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 05:13:07', '2025-09-12 05:13:07'),
(323, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Rapunzel (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 05:23:56', '2025-09-12 05:23:56'),
(324, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Rapunzel - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 05:24:17', '2025-09-12 05:24:17'),
(325, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Rapunzel - Changes: Status: Ongoing → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 05:24:23', '2025-09-12 05:24:23'),
(326, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-12 07:52:42', '2025-09-12 07:52:42'),
(327, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 04:21:56', '2025-09-15 04:21:56'),
(328, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 06:31:02', '2025-09-15 06:31:02'),
(329, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Laundry Details: Folding Service: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 06:31:55', '2025-09-15 06:31:55'),
(330, 1, 'admin', 'Haha', 'delete_product', 'Deleted supply product: \"Ariel\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 06:46:39', '2025-09-15 06:46:39'),
(331, 1, 'admin', 'Haha', 'delete_product_error', 'Attempted to delete product \"surf\" but it still has stock in inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 06:46:53', '2025-09-15 06:46:53'),
(332, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Status: Ongoing → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 07:03:52', '2025-09-15 07:03:52'),
(333, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 07:07:25', '2025-09-15 07:07:25'),
(334, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Janjan - Changes: Status: Ready for Pickup → Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-15 07:08:01', '2025-09-15 07:08:01'),
(335, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-16 02:20:31', '2025-09-16 02:20:31'),
(336, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-16 06:41:35', '2025-09-16 06:41:35'),
(337, 9, 'staff', 'Hannah Jane', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', '2025-09-17 04:19:42', '2025-09-17 04:19:42'),
(338, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-25 01:10:47', '2025-09-25 01:10:47'),
(339, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-25 01:26:30', '2025-09-25 01:26:30'),
(340, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-25 03:36:25', '2025-09-25 03:36:25'),
(341, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-25 03:36:27', '2025-09-25 03:36:27'),
(342, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 05:47:14', '2025-09-27 05:47:14'),
(343, 1, 'admin', 'Haha', 'order_create_error', 'Failed to create laundry order for customer: Hannah Janee - Error: Field \'queue_number\' doesn\'t have a default value', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 06:02:49', '2025-09-27 06:02:49'),
(344, 1, 'admin', 'Haha', 'inventory_transaction', 'IN transaction: 2 stock(s) of Zonrox', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 06:37:03', '2025-09-27 06:37:03'),
(345, 1, 'admin', 'Haha', 'inventory_transaction', 'OUT transaction: 7 stock(s) of Zonrox', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 06:38:47', '2025-09-27 06:38:47'),
(346, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 06:44:55', '2025-09-27 06:44:55'),
(347, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 06:49:37', '2025-09-27 06:49:37'),
(348, 1, 'admin', 'Haha', 'delete_transaction', 'Deleted transaction: 7 OUT for Zonrox', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 06:59:07', '2025-09-27 06:59:07'),
(349, 1, 'admin', 'Haha', 'delete_transaction', 'Deleted transaction: 2 IN for Zonrox', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 06:59:57', '2025-09-27 06:59:57'),
(350, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 08:37:28', '2025-09-27 08:37:28'),
(351, 1, 'admin', 'Haha', 'order_create_error', 'Failed to create laundry order for customer: Lester Madrid - Error: Field \'queue_number\' doesn\'t have a default value', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 08:38:20', '2025-09-27 08:38:20'),
(352, 1, 'admin', 'Haha', 'order_create_error', 'Failed to create laundry order for customer: Hannah Janee - Error: Field \'queue_number\' doesn\'t have a default value', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 08:38:39', '2025-09-27 08:38:39'),
(353, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 08:39:17', '2025-09-27 08:39:17'),
(354, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Ongoing → Claimed; Amount Tendered: ₱50.00 → ₱90.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 08:40:32', '2025-09-27 08:40:32'),
(355, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Han - Changes: Status: Ready for Pickup → Claimed; Amount Tendered: ₱50.00 → ₱90.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 08:40:45', '2025-09-27 08:40:45'),
(356, 1, 'admin', 'Haha', 'order_create_error', 'Failed to create laundry order for customer: Hannah Janee - Error: Field \'receipt_number\' doesn\'t have a default value', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 09:34:35', '2025-09-27 09:34:35'),
(357, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 09:37:44', '2025-09-27 09:37:44'),
(358, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Janjan (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 09:53:26', '2025-09-27 09:53:26'),
(359, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 10:46:41', '2025-09-27 10:46:41'),
(360, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 11:00:05', '2025-09-27 11:00:05'),
(361, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Lester Madrid (Total: ₱103.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 11:03:10', '2025-09-27 11:03:10'),
(362, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:13:10', '2025-09-27 12:13:10'),
(363, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:13:38', '2025-09-27 12:13:38'),
(364, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱150.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:16:08', '2025-09-27 12:16:08'),
(365, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 01:31:17', '2025-09-28 01:31:17'),
(366, 1, 'admin', 'Haha', 'order_delete_error', 'Failed to delete laundry order for customer: John - Error: Cannot delete an order that is Ongoing, Ready for Pickup, or Claimed.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 03:56:18', '2025-09-28 03:56:18'),
(367, 1, 'admin', 'Haha', 'inventory_transaction', 'OUT transaction: 4 stock(s) of surf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 05:35:22', '2025-09-28 05:35:22'),
(368, 1, 'admin', 'Haha', 'inventory_error', 'Failed inventory transaction: Unknown column \'description\' in \'field list\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 09:51:33', '2025-09-28 09:51:33'),
(369, 1, 'admin', 'Haha', 'inventory_transaction', 'OUT transaction: 2 stock(s) of Plastic Bag - nalaglag sa bangin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 09:56:12', '2025-09-28 09:56:12'),
(370, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 10:02:39', '2025-09-28 10:02:39'),
(371, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 10:23:40', '2025-09-28 10:23:40'),
(372, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 10:55:59', '2025-09-28 10:55:59'),
(373, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 10:59:16', '2025-09-28 10:59:16'),
(374, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: dryer per round (₱70.00 → ₱75.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 11:15:41', '2025-09-28 11:15:41'),
(375, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 11:15:49', '2025-09-28 11:15:49'),
(376, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-28 11:16:57', '2025-09-28 11:16:57'),
(377, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 03:02:50', '2025-09-29 03:02:50'),
(378, 1, 'admin', 'Haha', 'update_prices', 'Scheduled price changes for Sep 30, 2025: Service: dryer per round (₱75.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 03:08:18', '2025-09-29 03:08:18'),
(379, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱155.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 03:17:50', '2025-09-29 03:17:50'),
(380, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 03:31:36', '2025-09-29 03:31:36'),
(381, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 03:31:51', '2025-09-29 03:31:51'),
(382, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 03:34:02', '2025-09-29 03:34:02'),
(383, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 04:46:42', '2025-09-29 04:46:42'),
(384, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 04:46:47', '2025-09-29 04:46:47'),
(385, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 06:06:58', '2025-09-29 06:06:58'),
(386, 1, 'admin', 'Haha', 'delete_announcement', 'Deleted announcement: \'Price Adjustment Notice\' (Effective: Sep 29, 2025, Type: price_increase)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 07:18:55', '2025-09-29 07:18:55'),
(387, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 07:32:13', '2025-09-29 07:32:13'),
(388, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 07:32:21', '2025-09-29 07:32:21'),
(389, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 07:39:40', '2025-09-29 07:39:40'),
(390, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-29 07:39:46', '2025-09-29 07:39:46'),
(391, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 01:26:45', '2025-09-30 01:26:45'),
(392, 1, 'admin', 'Haha', 'update_prices', 'Scheduled price changes for Oct 1, 2025: Service: dryer per round (₱75.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 01:28:51', '2025-09-30 01:28:51'),
(393, 1, 'admin', 'Haha', 'supply_error', 'Failed to update supply product: Unknown column \'brand\' in \'field list\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 01:47:02', '2025-09-30 01:47:02'),
(394, 1, 'admin', 'Haha', 'update_supply', 'Updated supply product ID: 4 - Zonrox (measurement: 120 grams, Price: ₱91)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 01:47:45', '2025-09-30 01:47:45'),
(395, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 02:00:21', '2025-09-30 02:00:21'),
(396, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 02:00:30', '2025-09-30 02:00:30'),
(397, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 02:00:40', '2025-09-30 02:00:40'),
(398, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 02:01:04', '2025-09-30 02:01:04'),
(399, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 02:01:54', '2025-09-30 02:01:54'),
(400, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 02:02:00', '2025-09-30 02:02:00'),
(401, 1, 'admin', 'Haha', 'update_supply', 'Updated supply product: Zonrox (measurement: N/A, Price: ₱91)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 02:33:35', '2025-09-30 02:33:35'),
(402, 1, 'admin', 'Haha', 'status_change', 'Order #445 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 03:27:17', '2025-09-30 03:27:17'),
(403, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: 0 → Ready for Pickup; Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 03:27:38', '2025-09-30 03:27:38'),
(404, 1, 'admin', 'Haha', 'status_change', 'Order #445 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 03:27:42', '2025-09-30 03:27:42'),
(405, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:16:22', '2025-09-30 06:16:22'),
(406, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Rapunzel (Total: ₱96.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:16:50', '2025-09-30 06:16:50'),
(407, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Lester (Total: ₱155.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:17:09', '2025-09-30 06:17:09'),
(408, 1, 'admin', 'Haha', 'status_change', 'Order #450 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:17:16', '2025-09-30 06:17:16'),
(409, 1, 'admin', 'Haha', 'status_change', 'Order #450 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:17:23', '2025-09-30 06:17:23'),
(410, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: 0 → Ready for Pickup; Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:19:46', '2025-09-30 06:19:46'),
(411, 1, 'admin', 'Haha', 'status_change', 'Order #450 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:19:59', '2025-09-30 06:19:59'),
(412, 1, 'admin', 'Haha', 'status_change', 'Order #451 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 06:20:04', '2025-09-30 06:20:04'),
(413, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:09:05', '2025-09-30 07:09:05'),
(414, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:11:07', '2025-09-30 07:11:07'),
(415, 1, 'admin', 'Haha', 'create_order', 'Created 2 laundry orders for customer: Lester (Total: ₱248.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:18:48', '2025-09-30 07:18:48'),
(416, 1, 'admin', 'Haha', 'status_change', 'Order #453 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:22:06', '2025-09-30 07:22:06'),
(417, 1, 'admin', 'Haha', 'status_change', 'Order #453 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:22:35', '2025-09-30 07:22:35'),
(418, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Lester - Changes: Status: 0 → Ready for Pickup; Amount Tendered: ₱81.25 → ₱150.00; Payment Status: Unpaid → Paid; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:23:56', '2025-09-30 07:23:56'),
(419, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱80.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:24:36', '2025-09-30 07:24:36'),
(420, 1, 'admin', 'Haha', 'status_change', 'Order #455 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:24:43', '2025-09-30 07:24:43'),
(421, 1, 'admin', 'Haha', 'status_change', 'Order #455 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:24:48', '2025-09-30 07:24:48'),
(422, 1, 'admin', 'Haha', 'status_change', 'Order #453 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:25:35', '2025-09-30 07:25:35'),
(423, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 07:39:38', '2025-09-30 07:39:38'),
(424, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 08:40:08', '2025-09-30 08:40:08'),
(425, 1, 'admin', 'Haha', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱73.00, Tendered: ₱80.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 08:47:12', '2025-09-30 08:47:12'),
(426, 1, 'admin', 'Haha', 'status_change', 'Order #456 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 08:47:44', '2025-09-30 08:47:44'),
(427, 1, 'admin', 'Haha', 'status_change', 'Order #456 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 08:48:11', '2025-09-30 08:48:11'),
(428, 1, 'admin', 'Haha', 'status_change', 'Order #456 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 08:48:19', '2025-09-30 08:48:19'),
(429, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Lester - Changes: Amount Tendered: ₱48.75 → ₱45.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 08:53:20', '2025-09-30 08:53:20'),
(430, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: dryer per round (₱75.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 08:55:12', '2025-09-30 08:55:12'),
(431, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 09:01:48', '2025-09-30 09:01:48'),
(432, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-30 09:01:57', '2025-09-30 09:01:57'),
(433, 8, 'staff', 'Hannah Janee', 'status_change', 'Order #452 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 00:01:28', '2025-10-01 00:01:28'),
(434, 8, 'staff', 'Hannah Janee', 'status_change', 'Order #454 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 00:01:35', '2025-10-01 00:01:35'),
(435, 8, 'staff', 'Hannah Janee', 'status_change', 'Order #451 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 00:01:38', '2025-10-01 00:01:38'),
(436, 8, 'staff', 'Hannah Janee', 'status_change', 'Order #452 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 00:01:46', '2025-10-01 00:01:46'),
(437, 8, 'staff', 'Hannah Janee', 'status_change', 'Order #454 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 00:01:55', '2025-10-01 00:01:55'),
(438, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 01:02:09', '2025-10-01 01:02:09'),
(439, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 01:02:18', '2025-10-01 01:02:18'),
(440, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Haja Padrones (Total: ₱176.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 05:36:27', '2025-10-01 05:36:27'),
(441, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Rapunzel (Total: ₱230.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 05:37:06', '2025-10-01 05:37:06'),
(442, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱163.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 05:37:48', '2025-10-01 05:37:48'),
(443, 1, 'admin', 'Haha', 'status_change', 'Order #457 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-01 05:37:52', '2025-10-01 05:37:52'),
(444, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-02 02:27:01', '2025-10-02 02:27:01'),
(445, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-02 02:27:11', '2025-10-02 02:27:11'),
(446, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-02 02:49:11', '2025-10-02 02:49:11');
INSERT INTO `audit_logs` (`id`, `user_id`, `user_type`, `user_name`, `action`, `description`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(447, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-02 02:49:26', '2025-10-02 02:49:26'),
(448, 1, 'admin', 'Haha', 'update_prices', 'Scheduled price changes for Oct 3, 2025: Service: wash per round (₱70.00 → ₱60.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-02 03:10:54', '2025-10-02 03:10:54'),
(449, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-02 03:28:26', '2025-10-02 03:28:26'),
(450, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 01:04:29', '2025-10-03 01:04:29'),
(451, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: folding service (₱0.00 → ₱10.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 01:04:52', '2025-10-03 01:04:52'),
(452, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱70.00 → ₱60.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 01:20:00', '2025-10-03 01:20:00'),
(453, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱60.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 01:20:13', '2025-10-03 01:20:13'),
(454, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: folding service (₱10.00 → ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 01:22:41', '2025-10-03 01:22:41'),
(455, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Supply: surf (₱10.00 → ₱5.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 01:46:31', '2025-10-03 01:46:31'),
(456, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Supply: surf (₱5.00 → ₱10.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 02:11:11', '2025-10-03 02:11:11'),
(457, 1, 'admin', 'Haha', 'update_prices', 'Updated prices: Service: wash per round (₱70.00 → ₱60.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 02:59:55', '2025-10-03 02:59:55'),
(458, 1, 'admin', 'Haha', 'delete_announcement', 'Deleted announcement: \'Price Decrease guys\' (Effective: Oct 3, 2025, Type: price_decrease)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 03:00:15', '2025-10-03 03:00:15'),
(459, 1, 'admin', 'Haha', 'update_prices', 'Scheduled price changes for Oct 4, 2025: Service: wash per round (₱60.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 03:00:48', '2025-10-03 03:00:48'),
(460, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 08:48:37', '2025-10-03 08:48:37'),
(461, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 08:53:31', '2025-10-03 08:53:31'),
(462, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 09:06:51', '2025-10-03 09:06:51'),
(463, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-03 09:12:39', '2025-10-03 09:12:39'),
(464, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 10:56:24', '2025-10-05 10:56:24'),
(465, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Ready for Pickup → Claimed; Amount Tendered: ₱50.00 → ₱80.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 10:57:21', '2025-10-05 10:57:21'),
(466, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:06:03', '2025-10-05 11:06:03'),
(467, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: lala (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:06:16', '2025-10-05 11:06:16'),
(468, 1, 'admin', 'Haha', 'status_change', 'Order #460 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:06:21', '2025-10-05 11:06:21'),
(469, 1, 'admin', 'Haha', 'status_change', 'Order #460 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:06:24', '2025-10-05 11:06:24'),
(470, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: 0 → Ready for Pickup; Amount Tendered: ₱40.00 → ₱80.00; Payment Status: Unpaid → Paid; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:06:36', '2025-10-05 11:06:36'),
(471, 1, 'admin', 'Haha', 'status_change', 'Order #460 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:06:40', '2025-10-05 11:06:40'),
(472, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:43:03', '2025-10-05 11:43:03'),
(473, 1, 'admin', 'Haha', 'status_change', 'Order #462 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:43:07', '2025-10-05 11:43:07'),
(474, 1, 'admin', 'Haha', 'status_change', 'Order #462 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:43:10', '2025-10-05 11:43:10'),
(475, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Amount Tendered: ₱59.00 → ₱75.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:43:22', '2025-10-05 11:43:22'),
(476, 1, 'admin', 'Haha', 'status_change', 'Order #462 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-05 11:43:27', '2025-10-05 11:43:27'),
(477, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:03:17', '2025-10-06 00:03:17'),
(478, 1, 'admin', 'Haha', 'status_change', 'Order #463 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:03:24', '2025-10-06 00:03:24'),
(479, 1, 'admin', 'Haha', 'status_change', 'Order #463 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:03:27', '2025-10-06 00:03:27'),
(480, 1, 'admin', 'Haha', 'status_change', 'Order #463 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:03:30', '2025-10-06 00:03:30'),
(481, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:25:54', '2025-10-06 00:25:54'),
(482, 1, 'admin', 'Haha', 'status_change', 'Order #464 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:25:58', '2025-10-06 00:25:58'),
(483, 1, 'admin', 'Haha', 'status_change', 'Order #464 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:26:00', '2025-10-06 00:26:00'),
(484, 1, 'admin', 'Haha', 'status_change', 'Order #464 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:26:03', '2025-10-06 00:26:03'),
(485, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:28:45', '2025-10-06 00:28:45'),
(486, 1, 'admin', 'Haha', 'status_change', 'Order #465 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:28:48', '2025-10-06 00:28:48'),
(487, 1, 'admin', 'Haha', 'status_change', 'Order #465 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:28:51', '2025-10-06 00:28:51'),
(488, 1, 'admin', 'Haha', 'status_change', 'Order #465 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:28:56', '2025-10-06 00:28:56'),
(489, 1, 'admin', 'Haha', 'archive_review', 'Archived review: \"John\'s 1-star review\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 00:56:54', '2025-10-06 00:56:54'),
(490, 1, 'admin', 'Haha', 'archive_review', 'Archived review: \"John\'s 1-star review\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 02:24:55', '2025-10-06 02:24:55'),
(491, 1, 'admin', 'Haha', 'unarchive_review', 'Unarchived review: \"John\'s 1-star review\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 02:25:38', '2025-10-06 02:25:38'),
(492, 1, 'admin', 'Haha', 'delete_review', 'Deleted review: \"John\'s 1-star review - \"magaling\"\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 02:26:19', '2025-10-06 02:26:19'),
(493, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 05:41:37', '2025-10-06 05:41:37'),
(494, 1, 'super_admin', 'Super Admin', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 05:43:46', '2025-10-06 05:43:46'),
(495, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 05:46:24', '2025-10-06 05:46:24'),
(496, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 06:36:25', '2025-10-06 06:36:25'),
(497, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 06:36:42', '2025-10-06 06:36:42'),
(498, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 06:36:49', '2025-10-06 06:36:49'),
(499, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 06:46:59', '2025-10-06 06:46:59'),
(500, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 06:47:05', '2025-10-06 06:47:05'),
(501, 1, 'admin', 'Haha', 'unarchive_review', 'Unarchived review: \"John\'s 1-star review\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-06 06:48:55', '2025-10-06 06:48:55'),
(502, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 03:32:43', '2025-10-07 03:32:43'),
(503, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 03:36:17', '2025-10-07 03:36:17'),
(504, 1, 'super_admin', 'Super Admin', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 05:48:38', '2025-10-07 05:48:38'),
(505, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 06:45:49', '2025-10-07 06:45:49'),
(506, 1, 'super_admin', 'Super Admin', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 06:49:08', '2025-10-07 06:49:08'),
(507, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:03:16', '2025-10-07 07:03:16'),
(508, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:12:58', '2025-10-07 07:12:58'),
(509, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:13:10', '2025-10-07 07:13:10'),
(510, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:23:22', '2025-10-07 07:23:22'),
(511, 1, 'super_admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:23:25', '2025-10-07 07:23:25'),
(512, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:23:31', '2025-10-07 07:23:31'),
(513, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:23:43', '2025-10-07 07:23:43'),
(514, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:24:59', '2025-10-07 07:24:59'),
(515, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:25:05', '2025-10-07 07:25:05'),
(516, 1, 'super_admin', 'Super Admin', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:41:13', '2025-10-07 07:41:13'),
(517, 0, 'admin', 'jonleemad17@gmail.com', 'failed_login', 'Attempted login to archived account', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-07 07:41:18', '2025-10-07 07:41:18'),
(518, 0, 'admin', 'jonleemad17@gmail.com', 'failed_login', 'Attempted login to archived account', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 09:13:26', '2025-10-11 09:13:26'),
(519, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 09:13:36', '2025-10-11 09:13:36'),
(520, 1, 'super_admin', 'Super Admin', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 09:13:50', '2025-10-11 09:13:50'),
(521, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 09:13:56', '2025-10-11 09:13:56'),
(522, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 10:56:37', '2025-10-11 10:56:37'),
(523, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:09:55', '2025-10-11 11:09:55'),
(524, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:09:56', '2025-10-11 11:09:56'),
(525, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:09:58', '2025-10-11 11:09:58'),
(526, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:10:10', '2025-10-11 11:10:10'),
(527, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:19:03', '2025-10-11 11:19:03'),
(528, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:19:11', '2025-10-11 11:19:11'),
(529, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:19:35', '2025-10-11 11:19:35'),
(530, 1, 'admin', 'Haha', 'print_receipt', 'Printed initial receipt for order #461', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:19:40', '2025-10-11 11:19:40'),
(531, 1, 'admin', 'Haha', 'print_receipt', 'Auto-printed initial receipt for order #466', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:20:26', '2025-10-11 11:20:26'),
(532, 1, 'admin', 'Haha', 'print_receipt', 'Auto-printed initial receipt for order #466', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:28:39', '2025-10-11 11:28:39'),
(533, 1, 'admin', 'Haha', 'print_receipt', 'Auto-printed initial receipt for order #467', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:33:34', '2025-10-11 11:33:34'),
(534, 1, 'admin', 'Haha', 'print_receipt', 'Auto-printed initial receipt for order #466', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:35:08', '2025-10-11 11:35:08'),
(535, 1, 'admin', 'Haha', 'print_receipt', 'Auto-printed initial receipt for order #459', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:35:36', '2025-10-11 11:35:36'),
(536, 1, 'admin', 'Haha', 'status_change', 'Order #466 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:43:13', '2025-10-11 11:43:13'),
(537, 1, 'admin', 'Haha', 'status_change', 'Order #466 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:43:18', '2025-10-11 11:43:18'),
(538, 1, 'admin', 'Haha', 'print_receipt', 'Auto-printed initial receipt for order #468', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:44:03', '2025-10-11 11:44:03'),
(539, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:49:11', '2025-10-11 11:49:11'),
(540, 1, 'admin', 'Haha', 'print_receipt', 'Auto-printed initial receipt for order #469', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:49:16', '2025-10-11 11:49:16'),
(541, 1, 'admin', 'Haha', 'status_change', 'Order #469 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:54:45', '2025-10-11 11:54:45'),
(542, 1, 'admin', 'Haha', 'status_change', 'Order #469 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:54:50', '2025-10-11 11:54:50'),
(543, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Ready for Pickup → Claimed; Amount Tendered: ₱50.00 → ₱80.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-11 11:55:02', '2025-10-11 11:55:02'),
(544, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 02:17:48', '2025-10-12 02:17:48'),
(545, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Madrid (Total: ₱286.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 02:21:29', '2025-10-12 02:21:29'),
(546, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱229.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:03:21', '2025-10-12 04:03:21'),
(547, 1, 'admin', 'Haha', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory (₱7.00 refunded)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:16:37', '2025-10-12 04:16:37'),
(548, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱286.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:17:41', '2025-10-12 04:17:41'),
(549, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Lester (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:21:13', '2025-10-12 04:21:13'),
(550, 1, 'admin', 'Haha', 'status_change', 'Order #470 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:21:49', '2025-10-12 04:21:49'),
(551, 1, 'admin', 'Haha', 'status_change', 'Order #470 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:21:52', '2025-10-12 04:21:52'),
(552, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: 0 → Ready for Pickup; Amount Tendered: ₱150.00 → ₱300.00; Payment Status: Unpaid → Paid; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:22:06', '2025-10-12 04:22:06'),
(553, 1, 'admin', 'Haha', 'status_change', 'Order #470 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:22:10', '2025-10-12 04:22:10'),
(554, 1, 'admin', 'Haha', 'status_change', 'Order #472 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:29:03', '2025-10-12 04:29:03'),
(555, 1, 'admin', 'Haha', 'status_change', 'Order #472 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:29:06', '2025-10-12 04:29:06'),
(556, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: 0 → Ready for Pickup; Amount Tendered: ₱140.00 → ₱300.00; Payment Status: Unpaid → Paid; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:29:18', '2025-10-12 04:29:18'),
(557, 1, 'admin', 'Haha', 'status_change', 'Order #472 status changed from Ready for Pickup to Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:29:22', '2025-10-12 04:29:22'),
(558, 1, 'admin', 'Haha', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱83.00, Tendered: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:34:28', '2025-10-12 04:34:28'),
(559, 1, 'admin', 'Haha', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱80.00, Tendered: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:35:58', '2025-10-12 04:35:58'),
(560, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Lester - Changes: Status: Pending → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 04:48:33', '2025-10-12 04:48:33'),
(561, 1, 'admin', 'Haha', 'create_order', 'Created 2 laundry orders for customer: Madrid (Total: ₱146.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 05:14:56', '2025-10-12 05:14:56'),
(562, 1, 'admin', 'Haha', 'create_order', 'Created 2 laundry orders for customer: John (Total: ₱146.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 09:24:17', '2025-10-12 09:24:17'),
(563, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Pending → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 09:36:23', '2025-10-12 09:36:23'),
(564, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Ready for Pickup → Claimed; Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 09:36:37', '2025-10-12 09:36:37'),
(565, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Lester - Changes: Amount Tendered: ₱50.00 → ₱100.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 09:36:48', '2025-10-12 09:36:48'),
(566, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Lester - Changes: Status: Ready for Pickup → Claimed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 09:36:53', '2025-10-12 09:36:53'),
(567, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Pending → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 09:56:07', '2025-10-12 09:56:07'),
(568, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Ready for Pickup → Claimed; Amount Tendered: ₱50.00 → ₱70.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 09:56:27', '2025-10-12 09:56:27'),
(569, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:06:32', '2025-10-12 10:06:32'),
(570, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:07:34', '2025-10-12 10:07:34'),
(571, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: John - No significant changes detected', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:09:20', '2025-10-12 10:09:20'),
(572, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: Ongoing → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:16:08', '2025-10-12 10:16:08'),
(573, 1, 'admin', 'Haha', 'update_order', 'Updated laundry order for customer: Madrid - Changes: Status: Ready for Pickup → Claimed; Amount Tendered: ₱50.00 → ₱80.00; Payment Status: Unpaid → Paid', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:16:25', '2025-10-12 10:16:25'),
(574, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:28:00', '2025-10-12 10:28:00'),
(575, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:28:08', '2025-10-12 10:28:08'),
(576, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: John - Changes: Status: Pending → Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:28:24', '2025-10-12 10:28:24'),
(577, 8, 'staff', 'Hannah Janee', 'create_order', 'Created 2 laundry orders for customer: Hannah Janee (Total: ₱146.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:28:48', '2025-10-12 10:28:48'),
(578, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Pending → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:30:07', '2025-10-12 10:30:07'),
(579, 8, 'staff', 'Hannah Janee', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: 0 → Claimed; Amount Tendered: ₱50.00 → ₱80.00; Payment Status: Unpaid → Paid; Change Stored as Balance: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:30:29', '2025-10-12 10:30:29'),
(580, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:30:46', '2025-10-12 10:30:46'),
(581, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:30:53', '2025-10-12 10:30:53'),
(582, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:31:03', '2025-10-12 10:31:03'),
(583, 1, 'admin', 'Haha', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:57:29', '2025-10-12 10:57:29'),
(584, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-12 10:59:49', '2025-10-12 10:59:49'),
(585, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-14 06:43:18', '2025-10-14 06:43:18'),
(586, 1, 'super_admin', 'Super Admin', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-14 06:43:46', '2025-10-14 06:43:46'),
(587, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-14 06:43:55', '2025-10-14 06:43:55'),
(588, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-14 06:52:17', '2025-10-14 06:52:17'),
(589, 1, 'super_admin', 'Super Admin', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-14 06:52:22', '2025-10-14 06:52:22'),
(590, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-15 02:13:37', '2025-10-15 02:13:37'),
(591, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-17 04:44:28', '2025-10-17 04:44:28'),
(592, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-17 04:47:16', '2025-10-17 04:47:16'),
(593, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-17 08:57:21', '2025-10-17 08:57:21'),
(594, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-10-17 08:59:47', '2025-10-17 08:59:47'),
(595, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 08:34:54', '2025-10-19 08:34:54'),
(596, 1, 'admin', 'Haha', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 08:35:11', '2025-10-19 08:35:11'),
(597, 1, 'admin', 'Haha', 'delete_announcement', 'Deleted announcement: \'Price Decrease\' (Effective: Sep 30, 2025, Type: price_decrease)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 11:13:44', '2025-10-19 11:13:44'),
(598, 1, 'admin', 'Haha', 'user_archive', 'archived user: Lesterrr (staff)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 11:57:28', '2025-10-19 11:57:28'),
(599, 1, 'admin', 'Haha', 'user_restore', 'restored user: Lesterrr (staff)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 11:57:39', '2025-10-19 11:57:39'),
(600, 1, 'admin', 'Haha', 'customer_error', 'Failed to delete customer: Cannot delete or update a parent row: a foreign key constraint fails (`laundry_v2`.`receipts`, CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`laundry_list_id`) REFERENCES `laundry_lists` (`id`))', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 11:58:09', '2025-10-19 11:58:09'),
(601, 1, 'admin', 'Haha', 'customer_updated', 'Updated customer Janjan: name: Janjan → Janjann', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 11:58:40', '2025-10-19 11:58:40'),
(602, 1, 'admin', 'Haha', 'user_updated', 'Updated user John Lee Bugg: name: John Lee Bugg → John Lee Bagg', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 12:02:46', '2025-10-19 12:02:46'),
(603, 1, 'admin', 'Haha', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-19 12:26:30', '2025-10-19 12:26:30'),
(604, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-20 06:21:30', '2025-10-20 06:21:30'),
(605, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-20 06:50:55', '2025-10-20 06:50:55'),
(606, 1, 'admin', 'John Lee Bagg', 'status_change', 'Order #484 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-20 06:51:03', '2025-10-20 06:51:03'),
(607, 1, 'admin', 'John Lee Bagg', 'status_change', 'Order #484 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-20 06:51:06', '2025-10-20 06:51:06'),
(608, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-20 06:56:12', '2025-10-20 06:56:12'),
(609, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-20 07:18:21', '2025-10-20 07:18:21'),
(610, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-22 06:30:08', '2025-10-22 06:30:08'),
(611, 1, 'admin', 'John Lee Bagg', 'accept_prelist', 'Accepted pre-listed order for customer: John - Total: ₱23.00, Tendered: ₱50.00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-22 06:30:36', '2025-10-22 06:30:36'),
(612, 1, 'admin', 'John Lee Bagg', 'delete_order', 'Deleted laundry order for customer: John and reverted inventory (₱50.00 refunded)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-22 06:31:35', '2025-10-22 06:31:35'),
(613, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: John (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-22 06:32:31', '2025-10-22 06:32:31'),
(614, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-22 06:34:59', '2025-10-22 06:34:59'),
(615, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-24 02:46:23', '2025-10-24 02:46:23'),
(616, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-24 02:47:13', '2025-10-24 02:47:13'),
(617, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 01:18:33', '2025-10-29 01:18:33'),
(618, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 01:18:38', '2025-10-29 01:18:38'),
(619, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 01:40:17', '2025-10-29 01:40:17'),
(620, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 01:40:28', '2025-10-29 01:40:28'),
(621, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 02:24:43', '2025-10-29 02:24:43'),
(622, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order for customer: Janjann and reverted inventory - Reason: wala lang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 02:49:52', '2025-10-29 02:49:52'),
(623, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 02:50:46', '2025-10-29 02:50:46'),
(624, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 10:38:59', '2025-10-31 10:38:59'),
(625, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 10:40:42', '2025-10-31 10:40:42'),
(626, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order #8 for customer: Hannah Janee - Reason: Service Unavailable', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 11:15:44', '2025-10-31 11:15:44'),
(627, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order #3 for customer: Hannah Janee - Reason: Other (₱20.00 refunded)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 11:18:08', '2025-10-31 11:18:08'),
(628, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order #1 for customer: Janjann - Reason: Service Unavailable', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 11:22:01', '2025-10-31 11:22:01'),
(629, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order #6 for customer: John - Reason: Customer No-Show (₱10.50 refunded)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 11:22:41', '2025-10-31 11:22:41'),
(630, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order #7 for customer: John - Reason: Duplicate Order (₱10.50 refunded)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:00:28', '2025-10-31 12:00:28'),
(631, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:05:24', '2025-10-31 12:05:24'),
(632, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:05:32', '2025-10-31 12:05:32'),
(633, 8, 'staff', 'Hannah Janee', 'cancel_order', 'Cancelled laundry order #5 for customer: Hannah Janee - Reason: Payment Issues', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:06:03', '2025-10-31 12:06:03'),
(634, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:07:31', '2025-10-31 12:07:31'),
(635, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:07:36', '2025-10-31 12:07:36'),
(636, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:20:16', '2025-10-31 12:20:16'),
(637, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:20:23', '2025-10-31 12:20:23'),
(638, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:30:15', '2025-10-31 12:30:15'),
(639, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:39:12', '2025-10-31 12:39:12'),
(640, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:39:50', '2025-10-31 12:39:50'),
(641, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-31 12:46:36', '2025-10-31 12:46:36'),
(642, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:33:48', '2025-11-01 00:33:48'),
(643, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:37:28', '2025-11-01 00:37:28'),
(644, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:37:41', '2025-11-01 00:37:41'),
(645, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:38:45', '2025-11-01 00:38:45'),
(646, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:38:54', '2025-11-01 00:38:54'),
(647, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:40:18', '2025-11-01 00:40:18'),
(648, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:40:27', '2025-11-01 00:40:27'),
(649, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-01 00:45:33', '2025-11-01 00:45:33'),
(650, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 03:57:18', '2025-11-05 03:57:18'),
(651, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 04:10:38', '2025-11-05 04:10:38'),
(652, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 04:12:46', '2025-11-05 04:12:46');
INSERT INTO `audit_logs` (`id`, `user_id`, `user_type`, `user_name`, `action`, `description`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(653, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 04:15:28', '2025-11-05 04:15:28'),
(654, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 04:15:35', '2025-11-05 04:15:35'),
(655, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 05:02:30', '2025-11-05 05:02:30'),
(656, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 05:03:23', '2025-11-05 05:03:23'),
(657, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 05:05:39', '2025-11-05 05:05:39'),
(658, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 05:05:49', '2025-11-05 05:05:49'),
(659, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 05:08:15', '2025-11-05 05:08:15'),
(660, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 05:08:22', '2025-11-05 05:08:22'),
(661, 1, 'admin', 'John Lee Bagg', 'delete_product_error', 'Attempted to delete product \"Downy\" but it still has stock in inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 05:58:45', '2025-11-05 05:58:45'),
(662, 1, 'admin', 'John Lee Bagg', 'delete_transaction', 'Deleted transaction: 2 OUT for Plastic Bag', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:11:38', '2025-11-05 06:11:38'),
(663, 1, 'admin', 'John Lee Bagg', 'update_prices', 'Updated prices: Supply: Del (₱13.00 → ₱11.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:11:57', '2025-11-05 06:11:57'),
(664, 1, 'admin', 'John Lee Bagg', 'archive_review', 'Archived review: \"John\'s 5-star review\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:31:29', '2025-11-05 06:31:29'),
(665, 1, 'admin', 'John Lee Bagg', 'unarchive_review', 'Unarchived review: \"John\'s 5-star review\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:31:39', '2025-11-05 06:31:39'),
(666, 1, 'admin', 'John Lee Bagg', 'delete_review', 'Deleted review: \"John\'s 1-star review - \"angaling\"\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:31:55', '2025-11-05 06:31:55'),
(667, 1, 'admin', 'John Lee Bagg', 'user_archive', 'archived user: Lesterrr (staff)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:48:20', '2025-11-05 06:48:20'),
(668, 1, 'admin', 'John Lee Bagg', 'user_restore', 'restored user: Lesterrr (staff)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:48:26', '2025-11-05 06:48:26'),
(669, 1, 'admin', 'John Lee Bagg', 'user_deleted', 'Deleted user: Lesterrr (staff)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:48:31', '2025-11-05 06:48:31'),
(670, 1, 'admin', 'John Lee Bagg', 'customer_deleted', 'Deleted customer: John Madridddd (jon1@example.com)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:48:39', '2025-11-05 06:48:39'),
(671, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:54:58', '2025-11-05 06:54:58'),
(672, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:55:08', '2025-11-05 06:55:08'),
(673, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 06:55:24', '2025-11-05 06:55:24'),
(674, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 08:25:33', '2025-11-05 08:25:33'),
(675, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 08:30:58', '2025-11-05 08:30:58'),
(676, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 08:45:07', '2025-11-05 08:45:07'),
(677, 1, 'admin', 'John Lee Bagg', 'status_change', 'John Lee Bagg (UID:1) changed order status from \"Pending\" to \"Ongoing\".', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 08:46:07', '2025-11-05 08:46:07'),
(678, 1, 'admin', 'John Lee Bagg', 'status_change', 'Order Queue # status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 08:47:29', '2025-11-05 08:47:29'),
(679, 1, 'admin', 'John Lee Bagg', 'status_change', 'Order Queue # status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 08:56:18', '2025-11-05 08:56:18'),
(680, 1, 'admin', 'John Lee Bagg', 'status_change', 'Order Queue #1 status changed from Ongoing to Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 08:57:27', '2025-11-05 08:57:27'),
(681, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order #2 for customer: John - Reason: Service Unavailable', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 09:11:18', '2025-11-05 09:11:18'),
(682, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 09:12:43', '2025-11-05 09:12:43'),
(683, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-05 09:26:35', '2025-11-05 09:26:35'),
(684, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-07 10:54:47', '2025-11-07 10:54:47'),
(685, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:29:04', '2025-11-08 11:29:04'),
(686, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:43:10', '2025-11-08 11:43:10'),
(687, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:43:36', '2025-11-08 11:43:36'),
(688, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:43:44', '2025-11-08 11:43:44'),
(689, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:44:53', '2025-11-08 11:44:53'),
(690, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:54:10', '2025-11-08 11:54:10'),
(691, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:54:18', '2025-11-08 11:54:18'),
(692, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:54:52', '2025-11-08 11:54:52'),
(693, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 11:55:01', '2025-11-08 11:55:01'),
(694, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 12:10:20', '2025-11-08 12:10:20'),
(695, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 12:31:59', '2025-11-08 12:31:59'),
(696, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 12:32:32', '2025-11-08 12:32:32'),
(697, 8, 'staff', 'Hannah Janee', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 12:57:20', '2025-11-08 12:57:20'),
(698, 8, 'staff', 'Hannah Janee', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 12:58:21', '2025-11-08 12:58:21'),
(699, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 12:59:09', '2025-11-08 12:59:09'),
(700, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 13:17:34', '2025-11-08 13:17:34'),
(701, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 13:18:22', '2025-11-08 13:18:22'),
(702, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 13:22:32', '2025-11-08 13:22:32'),
(703, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 13:59:47', '2025-11-08 13:59:47'),
(704, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-08 14:00:17', '2025-11-08 14:00:17'),
(705, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-12 01:56:30', '2025-11-12 01:56:30'),
(706, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-12 01:57:49', '2025-11-12 01:57:49'),
(707, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36', '2025-11-12 04:00:04', '2025-11-12 04:00:04'),
(708, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36', '2025-11-12 04:00:29', '2025-11-12 04:00:29'),
(709, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-12 04:30:29', '2025-11-12 04:30:29'),
(710, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36', '2025-11-12 04:30:40', '2025-11-12 04:30:40'),
(711, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36', '2025-11-12 04:41:53', '2025-11-12 04:41:53'),
(712, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36', '2025-11-12 04:47:29', '2025-11-12 04:47:29'),
(713, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-12 12:53:47', '2025-11-12 12:53:47'),
(714, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-12 13:01:01', '2025-11-12 13:01:01'),
(715, 1, 'admin', 'John Lee Bagg', 'cancel_order', 'Cancelled laundry order #2 for customer: John - Reason: Payment Issues', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-12 14:32:41', '2025-11-12 14:32:41'),
(716, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-11-13 00:41:06', '2025-11-13 00:41:06'),
(717, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 13:59:09', '2025-11-14 13:59:09'),
(718, 1, 'admin', 'John Lee Bagg', 'order_create_error', 'Failed to create laundry order for customer: Unknown - Error: Duplicate entry \'0987666789\' for key \'users.users_contact_num_unique\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 14:05:09', '2025-11-14 14:05:09'),
(719, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: dada (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 14:18:45', '2025-11-14 14:18:45'),
(720, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 14:37:26', '2025-11-14 14:37:26'),
(721, 9, 'staff', 'Hannah Jane', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 14:37:37', '2025-11-14 14:37:37'),
(722, 9, 'staff', 'Hannah Jane', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 14:37:52', '2025-11-14 14:37:52'),
(723, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 00:52:12', '2025-11-15 00:52:12'),
(724, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 01:19:47', '2025-11-15 01:19:47'),
(725, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 01:56:24', '2025-11-15 01:56:24'),
(726, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 01:59:15', '2025-11-15 01:59:15'),
(727, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 01:59:36', '2025-11-15 01:59:36'),
(728, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 02:07:47', '2025-11-15 02:07:47'),
(729, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 02:08:01', '2025-11-15 02:08:01'),
(730, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 02:13:13', '2025-11-15 02:13:13'),
(731, 1, 'admin', 'John Lee Bagg', 'order_create_error', 'Failed to create laundry order for customer: Unknown - Error: Duplicate entry \'09123456786\' for key \'users.users_contact_num_unique\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 02:13:40', '2025-11-15 02:13:40'),
(732, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: dadae (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 02:14:26', '2025-11-15 02:14:26'),
(733, 1, 'admin', 'John Lee Bagg', 'order_create_error', 'Failed to create laundry order for customer: Unknown - Error: Duplicate entry \'09123456786\' for key \'users.users_contact_num_unique\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 02:15:55', '2025-11-15 02:15:55'),
(734, 1, 'admin', 'John Lee Bagg', 'order_create_error', 'Failed to create laundry order for customer: Unknown - Error: Duplicate entry \'09123456786\' for key \'users.users_contact_num_unique\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 02:17:09', '2025-11-15 02:17:09'),
(735, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 03:33:58', '2025-11-15 03:33:58'),
(736, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 03:55:05', '2025-11-15 03:55:05'),
(737, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Haja Padrones (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:09:24', '2025-11-15 04:09:24'),
(738, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:17:00', '2025-11-15 04:17:00'),
(739, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:17:20', '2025-11-15 04:17:20'),
(740, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:29:00', '2025-11-15 04:29:00'),
(741, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:37:14', '2025-11-15 04:37:14'),
(742, 1, 'admin', 'John Lee Bagg', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:37:57', '2025-11-15 04:37:57'),
(743, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:51:39', '2025-11-15 04:51:39'),
(744, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Elaine (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:54:46', '2025-11-15 04:54:46'),
(745, 1, 'admin', 'John Lee Bagg', 'update_prices', 'Updated prices: Service: folding service (₱0.00 → ₱10.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:56:11', '2025-11-15 04:56:11'),
(746, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: yeehaa (Total: ₱83.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 04:56:31', '2025-11-15 04:56:31'),
(747, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: adw (Total: ₱73.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 08:52:50', '2025-11-15 08:52:50'),
(748, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 2 laundry orders for customer: Hannah Janee (Total: ₱241.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 08:55:31', '2025-11-15 08:55:31'),
(749, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: hana (Total: ₱466.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 09:00:06', '2025-11-15 09:00:06'),
(750, 1, 'admin', 'John Lee Bagg', 'status_change', 'Order Queue #16 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 09:01:08', '2025-11-15 09:01:08'),
(751, 1, 'admin', 'John Lee Bagg', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Amount Tendered: ₱40.00 → ₱60.00; Laundry Details: Fabcon Cups: 0 → 2, Fabcon Product: None → Downy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 09:06:27', '2025-11-15 09:06:27'),
(752, 1, 'admin', 'John Lee Bagg', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱94.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 09:13:49', '2025-11-15 09:13:49'),
(753, 1, 'admin', 'John Lee Bagg', 'status_change', 'Order Queue #18 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 09:16:32', '2025-11-15 09:16:32'),
(754, 1, 'admin', 'John Lee Bagg', 'archive_review', 'Archived review: \"John\'s 2-star review\"', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:21:59', '2025-11-15 11:21:59'),
(755, 1, 'admin', 'John Lee Bagg', 'customer_error', 'Failed to delete customer: Cannot delete or update a parent row: a foreign key constraint fails (`laundry_v2`.`receipts`, CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`laundry_list_id`) REFERENCES `laundry_lists` (`id`))', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:23:38', '2025-11-15 11:23:38'),
(756, 1, 'admin', 'John Lee Bagg', 'customer_updated', 'Updated customer John: name: John → Johnn', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:24:31', '2025-11-15 11:24:31'),
(757, 1, 'admin', 'John Lee Bagg', 'user_updated', 'Updated user John Lee Bagg: name: John Lee Bagg → John Lee Bag', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:25:15', '2025-11-15 11:25:15'),
(758, 1, 'admin', 'John Lee Bagg', 'user_created', 'Added new user: yeye (yeye@gmail.com) - Role: Admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:26:08', '2025-11-15 11:26:08'),
(759, 1, 'admin', 'John Lee Bagg', 'user_archive', 'archived user: John Lee Bag (admin)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:26:14', '2025-11-15 11:26:14'),
(760, 1, 'admin', 'John Lee Bagg', 'customer_deleted', 'Deleted customer: evange (walkin_1746798349@example.com)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:27:31', '2025-11-15 11:27:31'),
(761, 1, 'admin', 'John Lee Bagg', 'customer_deletion_blocked', 'Attempted to delete customer with existing orders: hana (walkin_1763197206@example.com) - 1 orders found', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:32:28', '2025-11-15 11:32:28'),
(762, 1, 'admin', 'John Lee Bagg', 'user_deleted', 'Deleted user: Hannah Janee (staff)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 11:51:11', '2025-11-15 11:51:11'),
(763, 1, 'admin', 'John Lee Bagg', 'user_restored', 'Restored user: John Lee Bag', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:18:24', '2025-11-15 12:18:24'),
(764, 1, 'admin', 'John Lee Bagg', 'user_created', 'Added new user: Yeyes (yeyes@gmail.com) - Role: Staff', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:25:48', '2025-11-15 12:25:48'),
(765, 1, 'admin', 'John Lee Bagg', 'user_archived', 'Archived user: Yeyes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:28:43', '2025-11-15 12:28:43'),
(766, 1, 'admin', 'John Lee Bagg', 'user_archived', 'Archived user: Hannah Jane', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:32:49', '2025-11-15 12:32:49'),
(767, 1, 'admin', 'John Lee Bagg', 'user_restored', 'Restored user: Hannah Jane', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:32:54', '2025-11-15 12:32:54'),
(768, 1, 'admin', 'John Lee Bagg', 'user_restored', 'Restored user: Yeyes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:32:58', '2025-11-15 12:32:58'),
(769, 1, 'admin', 'John Lee Bagg', 'user_deleted', 'Deleted user: Yeyes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:33:06', '2025-11-15 12:33:06'),
(770, 1, 'admin', 'John Lee Bagg', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:33:15', '2025-11-15 12:33:15'),
(771, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:38:03', '2025-11-15 12:38:03'),
(772, 1, 'admin', 'John Lee Bag', 'user_archived', 'Archived user: Hannah Jane', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:38:09', '2025-11-15 12:38:09'),
(773, 1, 'admin', 'John Lee Bag', 'user_archived', 'Archived user: yeye', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:38:45', '2025-11-15 12:38:45'),
(774, 1, 'admin', 'John Lee Bag', 'user_restored', 'Restored user: Hannah Jane', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:38:49', '2025-11-15 12:38:49'),
(775, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:57:27', '2025-11-15 12:57:27'),
(776, 9, 'staff', 'Hannah Jane', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 12:57:33', '2025-11-15 12:57:33'),
(777, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 14:58:36', '2025-11-15 14:58:36'),
(778, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:08:46', '2025-11-15 15:08:46'),
(779, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:09:09', '2025-11-15 15:09:09'),
(780, 1, 'admin', 'John Lee Bag', 'update_prices', 'Updated prices: Service: folding service (₱10.00 → ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:09:36', '2025-11-15 15:09:36'),
(781, 1, 'admin', 'John Lee Bag', 'status_change', 'Order Queue #17 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:22:24', '2025-11-15 15:22:24'),
(782, 1, 'admin', 'John Lee Bag', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Status: Ongoing → Ready for Pickup', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:22:34', '2025-11-15 15:22:34'),
(783, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:31:31', '2025-11-15 15:31:31'),
(784, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:31:37', '2025-11-15 15:31:37'),
(785, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:36:01', '2025-11-15 15:36:01'),
(786, 1, 'admin', 'John Lee Bag', 'delete_transaction', 'Deleted transaction: 1 Used for Downy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:55:23', '2025-11-15 15:55:23'),
(787, 1, 'admin', 'John Lee Bag', 'add_utility', 'Added electricity bill: ₱1,000.00 for November 2025', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:56:10', '2025-11-15 15:56:10'),
(788, 1, 'admin', 'John Lee Bag', 'utility_error', 'Attempted to add duplicate electricity bill for November 2025', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:56:22', '2025-11-15 15:56:22'),
(789, 1, 'admin', 'John Lee Bag', 'update_prices', 'Updated prices: Service: dryer per round (₱70.00 → ₱0.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:56:41', '2025-11-15 15:56:41'),
(790, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:56:47', '2025-11-15 15:56:47'),
(791, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:59:02', '2025-11-15 15:59:02'),
(792, 1, 'admin', 'John Lee Bag', 'update_prices', 'Updated prices: Service: dryer per round (₱0.00 → ₱70.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 15:59:16', '2025-11-15 15:59:16'),
(793, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 16:08:54', '2025-11-15 16:08:54'),
(794, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 16:09:01', '2025-11-15 16:09:01'),
(795, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 16:09:56', '2025-11-15 16:09:56'),
(796, 9, 'staff', 'Hannah Jane', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 16:10:12', '2025-11-15 16:10:12'),
(797, 9, 'staff', 'Hannah Jane', 'delete_product_error', 'Attempted to delete product \"Zonrox\" but it still has stock in inventory', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 16:11:09', '2025-11-15 16:11:09'),
(798, 9, 'staff', 'Hannah Jane', 'delete_transaction', 'Deleted transaction: 1 Used for surf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 16:11:21', '2025-11-15 16:11:21'),
(799, 9, 'staff', 'Hannah Jane', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-15 16:11:56', '2025-11-15 16:11:56'),
(800, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:43:22', '2025-11-16 01:43:22'),
(801, 1, 'admin', 'John Lee Bag', 'update_order', 'Updated laundry order for customer: Hannah Janee - Changes: Laundry Details: Folding Service: No → Yes', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:43:36', '2025-11-16 01:43:36'),
(802, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:46:15', '2025-11-16 01:46:15'),
(803, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 02:07:26', '2025-11-16 02:07:26'),
(804, 1, 'admin', 'John Lee Bag', 'status_change', 'Order Queue #1 status changed from Pending to Ongoing', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 02:15:01', '2025-11-16 02:15:01'),
(805, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-03 00:49:25', '2025-12-03 00:49:25'),
(806, 1, 'admin', 'John Lee Bag', 'update_prices', 'Updated prices: Service: wash per round (₱60.00 → ₱61.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-03 00:49:40', '2025-12-03 00:49:40'),
(807, 1, 'admin', 'John Lee Bag', 'update_prices', 'Updated prices: Service: wash per round (₱61.00 → ₱65.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-03 00:50:18', '2025-12-03 00:50:18'),
(808, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 12:24:56', '2026-02-10 12:24:56'),
(809, 1, 'admin', 'John Lee Bag', 'create_order', 'Created 2 laundry orders for customer: Hannah Janee (Total: ₱156.00)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 14:13:08', '2026-02-10 14:13:08'),
(810, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 00:23:33', '2026-02-11 00:23:33'),
(811, 1, 'admin', 'John Lee Bag', 'create_order', 'Created 1 laundry order for customer: Hannah Janee (Total: ₱78.00)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 01:23:48', '2026-02-11 01:23:48'),
(812, 1, 'admin', 'John Lee Bag', 'create_order', 'Created 1 laundry order for customer: Janjann (Total: ₱78.00)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 01:35:07', '2026-02-11 01:35:07'),
(813, 1, 'admin', 'John Lee Bag', 'create_order', 'Created 2 laundry orders for customer: Janjann (Total: ₱156.00)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 01:53:31', '2026-02-11 01:53:31'),
(814, 1, 'admin', 'John Lee Bag', 'create_order', 'Created 2 laundry orders for customer: Janjann (Total: ₱150.00)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 02:14:52', '2026-02-11 02:14:52'),
(815, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 03:54:26', '2026-02-11 03:54:26'),
(816, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 03:55:00', '2026-02-12 03:55:00'),
(817, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 04:08:09', '2026-02-12 04:08:09'),
(818, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 04:08:18', '2026-02-12 04:08:18'),
(819, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 04:17:51', '2026-02-12 04:17:51'),
(820, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 04:17:59', '2026-02-12 04:17:59'),
(821, 1, 'admin', 'John Lee Bag', 'unarchive_review', 'Unarchived review: \"Johnn\'s 2-star review\"', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 06:23:16', '2026-02-12 06:23:16'),
(822, 1, 'admin', 'John Lee Bag', 'create_order', 'Created 1 laundry order for customer: Vennies (Total: ₱75.00)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 06:23:46', '2026-02-12 06:23:46'),
(823, 1, 'admin', 'John Lee Bag', 'user_restored', 'Restored user: yeye', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 06:24:28', '2026-02-12 06:24:28'),
(824, 1, 'admin', 'John Lee Bag', 'logout', 'User logged out successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 06:30:04', '2026-02-12 06:30:04'),
(825, 1, 'admin', 'John Lee Bag', 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 08:04:36', '2026-02-12 08:04:36'),
(826, 1, 'admin', 'John Lee Bag', 'accept_prelist', 'Accepted pre-listed order for customer: Lester Madrid - Total: ₱78.00, Tendered: ₱80.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 11:11:27', '2026-02-12 11:11:27'),
(827, 1, 'admin', 'John Lee Bag', 'accept_prelist', 'Accepted pre-listed order for customer: Lester Madrid - Total: ₱78.00, Tendered: ₱80.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 11:11:47', '2026-02-12 11:11:47'),
(828, 1, 'admin', 'John Lee Bag', 'create_order', 'Created 2 laundry orders for customer: Hannah Janee (Total: ₱156.00)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 11:56:34', '2026-02-12 11:56:34'),
(829, 1, 'admin', 'John Lee Bag', 'prelist_error', 'Failed to accept pre-listed order #101: Unknown column \'is_whites_order\' in \'field list\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 12:38:21', '2026-02-12 12:38:21'),
(830, 1, 'admin', 'John Lee Bag', 'accept_prelist', 'Accepted pre-listed order for customer: Lester Madrid - Total: ₱78.00, Tendered: ₱40.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 12:49:11', '2026-02-12 12:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int NOT NULL,
  `room_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `sender_type` enum('customer','admin','staff') NOT NULL,
  `sender_name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','image') DEFAULT 'text',
  `image_path` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `room_id`, `sender_id`, `sender_type`, `sender_name`, `message`, `message_type`, `image_path`, `is_read`, `is_deleted`, `deleted_at`, `deleted_by`, `created_at`, `read_at`) VALUES
(1, 1, 22, 'customer', 'John', 'hello', 'text', NULL, 1, 0, NULL, NULL, '2025-06-14 06:34:00', NULL),
(2, 1, 1, 'admin', 'Admin', 'how can we help you?', 'text', NULL, 1, 0, NULL, NULL, '2025-06-14 06:58:40', NULL),
(3, 1, 22, 'customer', 'John', 'hello', 'text', NULL, 1, 0, NULL, NULL, '2025-06-14 06:59:54', NULL),
(4, 2, 1, 'customer', 'Lester Madrid', 'hi', 'text', NULL, 1, 0, NULL, NULL, '2025-06-14 07:00:27', NULL),
(5, 2, 1, 'admin', 'Admin', 'hello', 'text', NULL, 1, 0, NULL, NULL, '2025-06-14 07:02:23', NULL),
(6, 2, 1, 'customer', 'Haha', 'hello', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 02:53:37', '2025-06-15 03:02:44'),
(7, 2, 1, 'customer', 'Haha', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 02:53:55', '2025-06-15 03:02:44'),
(8, 1, 22, 'customer', 'John', 'Hey bro', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 02:57:10', '2025-06-15 03:03:05'),
(9, 2, 1, 'admin', 'Admin', 'hello bruh', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 03:02:55', '2025-06-15 03:03:18'),
(10, 1, 1, 'admin', 'Admin', 'Ey bruh', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 03:03:09', '2025-06-15 03:30:50'),
(11, 2, 1, 'customer', 'Haha', 'hey bruh', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 03:03:26', '2025-06-15 03:03:45'),
(12, 2, 1, 'admin', 'Admin', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 03:03:48', '2025-06-15 03:03:52'),
(13, 2, 1, 'admin', 'Admin', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 03:04:02', '2025-06-15 03:04:07'),
(14, 2, 1, 'customer', 'Haha', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-06-15 03:04:27', '2025-06-15 03:04:27'),
(15, 1, 1, 'admin', 'Admin', 'heeeeeeeeeeeeeeeeee', 'text', NULL, 1, 0, NULL, NULL, '2025-07-16 09:26:41', '2025-07-18 03:26:50'),
(16, 1, 1, 'admin', 'Admin', 'hey bro', 'text', NULL, 1, 0, NULL, NULL, '2025-07-18 03:33:41', '2025-07-18 03:35:52'),
(26, 1, 1, 'admin', 'Admin Haha', 'ey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:00:04', '2025-08-05 06:00:05'),
(27, 1, 8, 'staff', 'Staff Member', 'ey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:00:17', '2025-08-05 06:00:19'),
(28, 1, 1, 'admin', 'Admin Haha', 'ey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:00:39', '2025-08-05 06:00:42'),
(29, 1, 1, 'admin', 'Admin Haha', 'ey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:01:03', '2025-08-05 06:01:06'),
(30, 1, 8, 'staff', 'Staff Hannah Janee', 'ey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:01:38', '2025-08-05 06:01:40'),
(31, 1, 8, 'staff', 'Staff Hannah Janee', 'ey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:01:58', '2025-08-05 06:02:00'),
(32, 1, 1, 'admin', 'Admin Administrator', 'ey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:07:37', '2025-08-05 06:07:41'),
(33, 1, 1, 'admin', 'Admin Administrator', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:07:59', '2025-08-05 06:08:24'),
(34, 1, 8, 'staff', 'Staff Hannah Janee', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:08:35', '2025-08-05 06:08:35'),
(35, 1, 1, 'admin', 'Admin Administrator', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:09:19', '2025-08-05 06:09:21'),
(36, 1, 1, 'admin', 'Admin Administrator', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:14:16', '2025-08-05 06:14:50'),
(37, 1, 8, 'staff', 'Staff Member', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:14:26', '2025-08-05 06:14:50'),
(38, 1, 8, 'staff', 'Staff Member', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:14:31', '2025-08-05 06:14:50'),
(39, 1, 1, 'admin', 'Admin Administrator', 's', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:20:19', '2025-08-21 05:22:04'),
(40, 1, 1, 'admin', 'Admin Haha', 'e', 'text', NULL, 1, 0, NULL, NULL, '2025-08-05 06:22:41', '2025-08-21 05:22:04'),
(41, 1, 22, 'customer', 'John', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-09-09 11:15:16', '2025-09-09 11:15:26'),
(42, 1, 1, 'admin', 'Admin Haha', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-09-09 11:15:34', '2025-09-09 11:15:37'),
(43, 1, 22, 'customer', 'John', 'uwi na ako, maligo ka na', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:49:10', '2025-09-27 06:49:57'),
(44, 1, 1, 'admin', 'Admin Haha', 'sige sige', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:50:13', '2025-09-27 06:50:17'),
(45, 1, 22, 'customer', 'John', 'gewalgkjwegjawelgj', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:50:29', '2025-09-27 06:50:32'),
(46, 1, 22, 'customer', 'John', 'egewgeg', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:50:34', '2025-09-27 06:50:35'),
(47, 1, 22, 'customer', 'John', 'yeragwrargkrwljgkreklgjrjlgrjgrlkjgjrlgr', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:50:42', '2025-09-27 06:50:44'),
(48, 1, 22, 'customer', 'John', 'testse', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:51:19', '2025-09-27 06:54:02'),
(49, 1, 22, 'customer', 'John', 'ewgege', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:51:25', '2025-09-27 06:54:02'),
(50, 1, 22, 'customer', 'John', 'geegegewg', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:51:54', '2025-09-27 06:54:02'),
(51, 1, 22, 'customer', 'John', '4g4g4g4g44', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:51:58', '2025-09-27 06:54:02'),
(52, 1, 22, 'customer', 'John', 'g4g4g4g4g', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 06:52:02', '2025-09-27 06:54:02'),
(53, 1, 1, 'admin', 'Admin Haha', 'gegege', 'text', NULL, 1, 0, NULL, NULL, '2025-09-27 08:44:24', '2025-09-27 08:44:29'),
(54, 1, 22, 'customer', 'John', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-09-30 07:30:18', '2025-09-30 07:30:32'),
(55, 1, 22, 'customer', 'John', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-09-30 08:59:21', '2025-09-30 08:59:30'),
(56, 1, 1, 'admin', 'Admin Haha', 'pogi', 'image', '../assets/uploads/chat-images/chat_1_1759373531_68dde8db746e3.jpg', 1, 0, NULL, NULL, '2025-10-02 02:52:11', '2025-10-02 02:52:14'),
(57, 1, 22, 'customer', 'John', 'ganda', 'image', '../assets/uploads/chat-images/chat_1_1759373614_68dde92e2ff70.jpg', 1, 0, NULL, NULL, '2025-10-02 02:53:34', '2025-10-02 02:53:34'),
(58, 3, 8, 'staff', 'Staff Hannah Janee', 'bro', 'text', NULL, 0, 0, NULL, NULL, '2025-10-06 06:04:29', NULL),
(59, 1, 22, 'customer', 'John', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:12:11', '2025-10-15 02:13:43'),
(60, 1, 22, 'customer', 'John', 'hey', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:13:23', '2025-10-15 02:13:43'),
(61, 1, 1, 'admin', 'Admin Haha', 'wassup men', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:13:47', '2025-10-15 02:13:50'),
(62, 1, 22, 'customer', 'John', 'sup', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:13:55', '2025-10-15 02:13:57'),
(63, 1, 1, 'admin', 'Admin Haha', 'sup', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:13:59', '2025-10-15 02:14:00'),
(64, 1, 1, 'admin', 'Admin Haha', 'sup', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:03', '2025-10-15 02:14:05'),
(65, 1, 22, 'customer', 'John', 'sup', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:05', '2025-10-15 02:14:06'),
(66, 1, 22, 'customer', 'John', 'a', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:11', '2025-10-15 02:14:12'),
(67, 1, 1, 'admin', 'Admin Haha', 'b', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:14', '2025-10-15 02:14:16'),
(68, 1, 22, 'customer', 'John', 'c', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:16', '2025-10-15 02:14:17'),
(69, 1, 1, 'admin', 'Admin Haha', 'd', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:19', '2025-10-15 02:14:22'),
(70, 1, 22, 'customer', 'John', 'e', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:23', '2025-10-15 02:14:26'),
(71, 1, 1, 'admin', 'Admin Haha', 'f', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:14:25', '2025-10-15 02:14:29'),
(72, 1, 1, 'admin', 'Admin Haha', 'g', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:16:58', '2025-10-15 02:17:01'),
(73, 1, 22, 'customer', 'John', 'h', 'text', NULL, 1, 0, NULL, NULL, '2025-10-15 02:22:48', '2025-10-15 02:22:50'),
(74, 1, 1, 'admin', 'Admin John Lee Bag', 'hey', 'text', NULL, 0, 0, NULL, NULL, '2026-02-11 03:57:35', NULL),
(75, 1, 1, 'admin', 'Admin John Lee Bag', 'hey', 'text', NULL, 0, 0, NULL, NULL, '2026-02-11 04:02:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `last_message_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `customer_id`, `customer_name`, `customer_email`, `last_message_at`, `is_active`, `created_at`) VALUES
(1, 22, 'John', 'lesterbeast17@gmail.com', '2026-02-11 04:02:22', 1, '2025-06-13 09:51:26'),
(2, 1, 'Lester Madrid', 'lester@example.com', '2025-06-15 03:04:27', 1, '2025-06-14 07:00:24'),
(3, 29, 'Haja Padrones', 'hajapadrones0.0@gmail.com', '2025-10-06 06:04:29', 1, '2025-08-18 06:01:57');

-- --------------------------------------------------------

--
-- Table structure for table `customer_tokens`
--

CREATE TABLE `customer_tokens` (
  `id` int NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_logins`
--

CREATE TABLE `failed_logins` (
  `email` varchar(255) NOT NULL,
  `failed_attempts` int DEFAULT '0',
  `last_failed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `failed_logins`
--

INSERT INTO `failed_logins` (`email`, `failed_attempts`, `last_failed_at`) VALUES
('dkawd', 1, '2025-10-31 18:38:50'),
('hannah@gmail.com', 1, '2025-11-16 00:10:03'),
('jabi@gmail.com', 2, '2025-09-29 13:54:40'),
('lesterbeast17@gmail.com', 1, '2025-09-27 14:45:46');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `available_units` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `stock_quantity`, `available_units`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 5, '2025-04-06 07:40:17', '2026-02-12 13:52:45'),
(2, 2, 5, 32, '2025-04-06 07:40:17', '2025-11-15 09:13:49'),
(3, 4, 5, 18, '2025-04-06 07:40:17', '2025-11-15 09:00:06'),
(6, 7, 7, 13, '2025-04-07 04:59:22', '2025-11-15 15:22:34'),
(7, 16, 4, 100, '2025-06-06 10:49:29', '2025-11-15 15:55:23'),
(8, 17, 0, 117, '2025-06-07 02:21:24', '2026-02-12 13:52:31');

-- --------------------------------------------------------

--
-- Table structure for table `laundry_details`
--

CREATE TABLE `laundry_details` (
  `id` bigint UNSIGNED NOT NULL,
  `laundry_list_id` bigint UNSIGNED NOT NULL,
  `rounds_of_wash` int NOT NULL DEFAULT '1' COMMENT 'Max 4 rounds',
  `scoops_of_detergent` int NOT NULL DEFAULT '1' COMMENT 'Min 1 per round, max 5',
  `detergent_product_id` int DEFAULT NULL,
  `folding_service` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Yes, 0 = No',
  `separate_whites` tinyint(1) NOT NULL DEFAULT '0',
  `is_whites_order` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = This is a generated whites order, 0 = Normal order',
  `dryer_preference` int NOT NULL DEFAULT '0' COMMENT '0 = No dryer, 1 = 1 round, 2 = 2 rounds',
  `bleach_cups` tinyint(1) DEFAULT '0',
  `bleach_product_id` int DEFAULT NULL,
  `fabcon_cups` tinyint(1) DEFAULT '0',
  `fabcon_product_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laundry_details`
--

INSERT INTO `laundry_details` (`id`, `laundry_list_id`, `rounds_of_wash`, `scoops_of_detergent`, `detergent_product_id`, `folding_service`, `separate_whites`, `is_whites_order`, `dryer_preference`, `bleach_cups`, `bleach_product_id`, `fabcon_cups`, `fabcon_product_id`, `created_at`, `updated_at`) VALUES
(396, 410, 1, 1, 17, 1, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-09 10:24:52', '2025-09-09 10:31:17'),
(397, 411, 1, 1, 17, 0, 0, 0, 0, 1, 4, 1, 2, '2025-09-09 10:58:43', '2025-09-09 10:58:43'),
(419, 433, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-12 03:24:01', '2025-09-12 03:24:01'),
(420, 434, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-12 04:58:18', '2025-09-12 04:58:18'),
(421, 435, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-12 04:58:59', '2025-09-12 04:58:59'),
(422, 436, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-12 05:23:56', '2025-09-12 05:23:56'),
(423, 437, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-12 07:52:42', '2025-09-12 07:52:42'),
(425, 439, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-16 06:41:35', '2025-09-16 06:41:35'),
(426, 440, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-27 09:34:35', '2025-09-27 09:34:35'),
(427, 441, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-27 09:37:44', '2025-09-27 09:37:44'),
(428, 442, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-27 09:53:26', '2025-09-27 09:53:26'),
(429, 443, 1, 1, 17, 1, 0, 0, 0, 1, 4, 1, 2, '2025-09-27 11:03:10', '2025-09-27 11:03:10'),
(430, 444, 1, 1, 17, 1, 0, 0, 1, 0, NULL, 0, NULL, '2025-09-27 12:16:08', '2025-09-27 12:16:08'),
(431, 445, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-29 03:02:50', '2025-09-29 03:02:50'),
(432, 446, 1, 1, 17, 0, 0, 0, 1, 0, NULL, 0, NULL, '2025-09-29 03:17:50', '2025-09-29 03:17:50'),
(433, 447, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-29 03:31:51', '2025-09-29 03:31:51'),
(434, 448, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-29 03:34:02', '2025-09-29 03:34:02'),
(435, 449, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-29 04:46:42', '2025-09-29 04:46:42'),
(436, 450, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-30 06:16:22', '2025-09-30 06:16:22'),
(437, 451, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 1, 2, '2025-09-30 06:16:49', '2025-09-30 06:16:49'),
(438, 452, 1, 1, 17, 0, 0, 0, 1, 0, NULL, 0, NULL, '2025-09-30 06:17:09', '2025-09-30 06:17:09'),
(439, 453, 1, 1, 17, 0, 0, 0, 1, 0, NULL, 0, NULL, '2025-09-30 07:18:48', '2025-09-30 07:18:48'),
(440, 454, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 1, 2, '2025-09-30 07:18:48', '2025-09-30 07:18:48'),
(441, 455, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-09-30 07:24:36', '2025-09-30 07:24:36'),
(442, 456, 1, 1, 17, 0, 0, 1, 0, 0, NULL, 1, 2, '2025-09-30 08:47:12', '2026-02-11 01:45:30'),
(443, 457, 1, 2, 17, 1, 0, 0, 1, 0, NULL, 1, 16, '2025-10-01 05:36:26', '2025-10-01 05:36:26'),
(444, 458, 2, 2, 17, 1, 0, 0, 1, 0, NULL, 0, NULL, '2025-10-01 05:37:06', '2025-10-01 05:37:06'),
(445, 459, 1, 1, 17, 1, 0, 0, 1, 0, NULL, 1, 2, '2025-10-01 05:37:48', '2025-10-01 05:37:48'),
(446, 460, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-05 11:06:03', '2025-10-05 11:06:03'),
(447, 461, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-05 11:06:16', '2025-10-05 11:06:16'),
(448, 462, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-05 11:43:03', '2025-10-05 11:43:03'),
(449, 463, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-06 00:03:17', '2025-10-06 00:03:17'),
(450, 464, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-06 00:25:54', '2025-10-06 00:25:54'),
(451, 465, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-06 00:28:45', '2025-10-06 00:28:45'),
(452, 466, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-11 11:01:44', '2025-10-11 11:01:44'),
(453, 467, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-11 11:33:25', '2025-10-11 11:33:25'),
(454, 468, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-11 11:44:02', '2025-10-11 11:44:02'),
(455, 469, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-11 11:49:10', '2025-10-11 11:49:10'),
(456, 470, 2, 1, 1, 1, 0, 0, 2, 0, NULL, 1, 2, '2025-10-12 02:21:28', '2025-10-12 02:21:28'),
(458, 472, 2, 1, 1, 0, 0, 0, 2, 0, NULL, 1, 2, '2025-10-12 04:17:41', '2025-10-12 04:17:41'),
(459, 473, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 04:21:13', '2025-10-12 04:21:13'),
(460, 474, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 04:34:28', '2025-10-12 04:34:28'),
(461, 475, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 04:35:58', '2025-10-12 04:35:58'),
(462, 476, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 05:14:56', '2025-10-12 05:14:56'),
(463, 477, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 05:14:56', '2025-10-12 05:14:56'),
(464, 478, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 09:24:17', '2025-10-12 09:24:17'),
(465, 479, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 09:24:17', '2025-10-12 09:24:17'),
(466, 480, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 10:28:48', '2025-10-12 10:28:48'),
(467, 481, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 10:28:48', '2025-10-12 10:28:48'),
(468, 482, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 10:31:03', '2025-10-12 10:31:03'),
(469, 483, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-12 10:57:29', '2025-10-12 10:57:29'),
(470, 484, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-20 06:50:55', '2025-10-20 06:50:55'),
(472, 486, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-22 06:32:31', '2025-10-22 06:32:31'),
(473, 487, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-24 02:47:13', '2025-10-24 02:47:13'),
(474, 488, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-10-29 02:50:46', '2025-10-29 02:50:46'),
(475, 489, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-14 14:18:45', '2025-11-14 14:18:45'),
(476, 490, 1, 1, 1, 1, 0, 0, 0, 0, NULL, 2, 16, '2025-11-15 01:19:47', '2025-11-16 01:43:35'),
(477, 491, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 01:56:23', '2025-11-15 01:56:23'),
(478, 497, 1, 1, 1, 1, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 02:13:13', '2025-11-15 02:13:13'),
(479, 498, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 02:14:26', '2025-11-15 02:14:26'),
(480, 499, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 03:55:05', '2025-11-15 03:55:05'),
(481, 500, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 04:09:23', '2025-11-15 04:09:23'),
(482, 501, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 04:28:59', '2025-11-15 04:28:59'),
(483, 502, 1, 1, 1, 0, 1, 0, 0, 0, NULL, 0, NULL, '2025-11-15 04:51:38', '2025-11-15 04:51:38'),
(484, 503, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 0, NULL, '2025-11-15 04:54:46', '2025-11-15 04:54:46'),
(485, 504, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 0, NULL, '2025-11-15 04:56:31', '2025-11-15 04:56:31'),
(486, 505, 1, 1, 1, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 08:52:49', '2025-11-15 08:52:49'),
(487, 506, 1, 1, 17, 1, 1, 0, 1, 1, 4, 1, 2, '2025-11-15 08:55:31', '2025-11-15 08:55:31'),
(488, 507, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2025-11-15 08:55:31', '2025-11-15 08:55:31'),
(489, 508, 4, 5, 17, 1, 1, 0, 2, 1, 4, 1, 16, '2025-11-15 09:00:06', '2025-11-15 09:00:06'),
(490, 509, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 1, 2, '2025-11-15 09:13:49', '2025-11-15 09:13:49'),
(491, 510, 1, 1, 1, 0, 1, 0, 0, 0, NULL, 0, NULL, '2026-02-10 14:13:07', '2026-02-10 14:13:07'),
(492, 511, 1, 1, 1, 0, 1, 0, 0, 0, NULL, 0, NULL, '2026-02-10 14:13:07', '2026-02-10 14:13:07'),
(493, 512, 1, 1, 1, 0, 0, 1, 0, 0, NULL, 0, NULL, '2026-02-11 01:23:48', '2026-02-11 01:23:48'),
(494, 513, 1, 1, 1, 0, 1, 1, 0, 0, NULL, 0, NULL, '2026-02-11 01:35:07', '2026-02-11 01:52:59'),
(495, 514, 1, 1, 1, 0, 1, 0, 0, 0, NULL, 0, NULL, '2026-02-11 01:53:31', '2026-02-11 01:53:31'),
(496, 515, 1, 1, 1, 0, 1, 1, 0, 0, NULL, 0, NULL, '2026-02-11 01:53:31', '2026-02-11 01:53:31'),
(497, 516, 1, 1, 17, 0, 1, 0, 0, 0, NULL, 0, NULL, '2026-02-11 02:14:52', '2026-02-11 02:14:52'),
(498, 517, 1, 1, 17, 0, 1, 1, 0, 0, NULL, 0, NULL, '2026-02-11 02:14:52', '2026-02-11 02:14:52'),
(499, 518, 1, 1, 17, 0, 0, 0, 0, 0, NULL, 0, NULL, '2026-02-12 06:23:45', '2026-02-12 06:23:45'),
(500, 519, 1, 1, 1, 0, 1, 0, 0, 0, NULL, 0, NULL, '2026-02-12 11:11:27', '2026-02-12 11:11:27'),
(501, 520, 1, 1, 1, 0, 1, 0, 0, 0, NULL, 0, NULL, '2026-02-12 11:11:47', '2026-02-12 11:11:47'),
(502, 521, 1, 1, 1, 0, 1, 0, 0, 0, NULL, 0, NULL, '2026-02-12 11:56:34', '2026-02-12 11:56:34'),
(503, 522, 1, 1, 1, 0, 1, 1, 0, 0, NULL, 0, NULL, '2026-02-12 11:56:34', '2026-02-12 11:56:34'),
(504, 523, 1, 1, 1, 0, 1, 1, 0, 0, NULL, 0, NULL, '2026-02-12 12:49:11', '2026-02-12 12:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `laundry_items`
--

CREATE TABLE `laundry_items` (
  `id` int NOT NULL,
  `laundry_list_id` bigint UNSIGNED NOT NULL,
  `tops` int DEFAULT '0',
  `bottoms` int DEFAULT '0',
  `undergarments` int DEFAULT '0',
  `delicates` int DEFAULT '0',
  `linens` int DEFAULT '0',
  `curtains_drapes` int DEFAULT '0',
  `blankets_comforters` int DEFAULT '0',
  `others` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laundry_items`
--

INSERT INTO `laundry_items` (`id`, `laundry_list_id`, `tops`, `bottoms`, `undergarments`, `delicates`, `linens`, `curtains_drapes`, `blankets_comforters`, `others`, `created_at`, `updated_at`) VALUES
(65, 410, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-09 10:24:52', '2025-09-09 10:24:52'),
(66, 411, 5, 5, 5, 0, 0, 0, 0, 0, '2025-09-09 10:58:43', '2025-09-09 10:58:43'),
(86, 433, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-12 03:24:01', '2025-09-12 03:24:01'),
(87, 434, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-12 04:58:18', '2025-09-12 04:58:18'),
(88, 435, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-12 04:58:59', '2025-09-12 04:58:59'),
(89, 436, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-12 05:23:56', '2025-09-12 05:23:56'),
(90, 437, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-12 07:52:42', '2025-09-12 07:52:42'),
(92, 439, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-16 06:41:35', '2025-09-16 06:41:35'),
(93, 440, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-27 09:34:35', '2025-09-27 09:34:35'),
(94, 441, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-27 09:37:44', '2025-09-27 09:37:44'),
(95, 442, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-27 09:53:26', '2025-09-27 09:53:26'),
(96, 443, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-27 11:03:10', '2025-09-27 11:03:10'),
(97, 444, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-27 12:16:08', '2025-09-27 12:16:08'),
(98, 445, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-29 03:02:50', '2025-09-29 03:02:50'),
(99, 446, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-29 03:17:50', '2025-09-29 03:17:50'),
(100, 447, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-29 03:31:51', '2025-09-29 03:31:51'),
(101, 448, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-29 03:34:02', '2025-09-29 03:34:02'),
(102, 449, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-29 04:46:42', '2025-09-29 04:46:42'),
(103, 450, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-30 06:16:22', '2025-09-30 06:16:22'),
(104, 451, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-30 06:16:50', '2025-09-30 06:16:50'),
(105, 452, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-30 06:17:09', '2025-09-30 06:17:09'),
(106, 453, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-30 07:18:48', '2025-09-30 07:18:48'),
(107, 454, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-30 07:18:48', '2025-09-30 07:18:48'),
(108, 455, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-30 07:24:36', '2025-09-30 07:24:36'),
(109, 457, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-01 05:36:26', '2025-10-01 05:36:26'),
(110, 458, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-01 05:37:06', '2025-10-01 05:37:06'),
(111, 459, 5, 5, 5, 0, 0, 0, 0, 0, '2025-10-01 05:37:48', '2025-10-01 05:37:48'),
(112, 460, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-05 11:06:03', '2025-10-05 11:06:03'),
(113, 461, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-05 11:06:16', '2025-10-05 11:06:16'),
(114, 462, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-05 11:43:03', '2025-10-05 11:43:03'),
(115, 463, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-06 00:03:17', '2025-10-06 00:03:17'),
(116, 464, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-06 00:25:54', '2025-10-06 00:25:54'),
(117, 465, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-06 00:28:45', '2025-10-06 00:28:45'),
(118, 466, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-11 11:01:44', '2025-10-11 11:01:44'),
(119, 467, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-11 11:33:25', '2025-10-11 11:33:25'),
(120, 468, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-11 11:44:02', '2025-10-11 11:44:02'),
(121, 469, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-11 11:49:10', '2025-10-11 11:49:10'),
(122, 470, 5, 5, 5, 0, 1, 1, 0, 0, '2025-10-12 02:21:28', '2025-10-12 02:21:28'),
(124, 472, 1, 1, 1, 0, 0, 0, 0, 0, '2025-10-12 04:17:41', '2025-10-12 04:17:41'),
(125, 473, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 04:21:13', '2025-10-12 04:21:13'),
(126, 476, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 05:14:56', '2025-10-12 05:14:56'),
(127, 477, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 05:14:56', '2025-10-12 05:14:56'),
(128, 478, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 09:24:17', '2025-10-12 09:24:17'),
(129, 479, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 09:24:17', '2025-10-12 09:24:17'),
(130, 480, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 10:28:48', '2025-10-12 10:28:48'),
(131, 481, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 10:28:48', '2025-10-12 10:28:48'),
(132, 482, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 10:31:03', '2025-10-12 10:31:03'),
(133, 483, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 10:57:29', '2025-10-12 10:57:29'),
(134, 484, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-20 06:50:55', '2025-10-20 06:50:55'),
(135, 486, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-22 06:32:31', '2025-10-22 06:32:31'),
(136, 487, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-24 02:47:13', '2025-10-24 02:47:13'),
(137, 488, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-29 02:50:46', '2025-10-29 02:50:46'),
(138, 489, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-14 14:18:45', '2025-11-14 14:18:45'),
(139, 490, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 01:19:47', '2025-11-15 01:19:47'),
(140, 491, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 01:56:24', '2025-11-15 01:56:24'),
(141, 492, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 01:59:15', '2025-11-15 01:59:15'),
(142, 493, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 01:59:36', '2025-11-15 01:59:36'),
(143, 495, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 02:07:46', '2025-11-15 02:07:46'),
(144, 496, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 02:08:01', '2025-11-15 02:08:01'),
(145, 497, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 02:13:13', '2025-11-15 02:13:13'),
(146, 498, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 02:14:26', '2025-11-15 02:14:26'),
(147, 499, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 03:55:05', '2025-11-15 03:55:05'),
(148, 500, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 04:09:23', '2025-11-15 04:09:23'),
(149, 501, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 04:28:59', '2025-11-15 04:28:59'),
(150, 502, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 04:51:38', '2025-11-15 04:51:38'),
(151, 503, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 04:54:46', '2025-11-15 04:54:46'),
(152, 504, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 04:56:31', '2025-11-15 04:56:31'),
(153, 505, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 08:52:50', '2025-11-15 08:52:50'),
(154, 506, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 08:55:31', '2025-11-15 08:55:31'),
(155, 507, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 08:55:31', '2025-11-15 08:55:31'),
(156, 508, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 09:00:06', '2025-11-15 09:00:06'),
(157, 509, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 09:13:49', '2025-11-15 09:13:49'),
(158, 510, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-10 14:13:07', '2026-02-10 14:13:07'),
(159, 511, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-10 14:13:07', '2026-02-10 14:13:07'),
(160, 512, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-11 01:23:48', '2026-02-11 01:23:48'),
(161, 513, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-11 01:35:07', '2026-02-11 01:35:07'),
(162, 514, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-11 01:53:31', '2026-02-11 01:53:31'),
(163, 515, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-11 01:53:31', '2026-02-11 01:53:31'),
(164, 516, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-11 02:14:52', '2026-02-11 02:14:52'),
(165, 517, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-11 02:14:52', '2026-02-11 02:14:52'),
(166, 518, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-12 06:23:45', '2026-02-12 06:23:45'),
(167, 521, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-12 11:56:34', '2026-02-12 11:56:34'),
(168, 522, 0, 0, 0, 0, 0, 0, 0, 0, '2026-02-12 11:56:34', '2026-02-12 11:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `laundry_lists`
--

CREATE TABLE `laundry_lists` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `queue_number` int NOT NULL,
  `status` enum('Pre-listed','Pending','Ongoing','Ready for Pickup','Claimed','Unclaimed','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `payment_status` enum('Paid','Unpaid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unpaid',
  `amount_tendered` decimal(10,2) DEFAULT '0.00',
  `amount_change` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `adjusted_total_price` decimal(10,2) DEFAULT '0.00',
  `deducted_balance` decimal(10,2) DEFAULT '0.00',
  `change_stored_as_balance` tinyint DEFAULT '0',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `accommodated_by_id` int DEFAULT NULL,
  `accommodated_by_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_dismissed` tinyint(1) DEFAULT '0' COMMENT 'Customer dismissed review prompt',
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_notes` text COLLATE utf8mb4_unicode_ci,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_by_id` int DEFAULT NULL,
  `cancelled_by_type` enum('admin','staff') COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laundry_lists`
--

INSERT INTO `laundry_lists` (`id`, `customer_id`, `queue_number`, `status`, `payment_status`, `amount_tendered`, `amount_change`, `total_price`, `adjusted_total_price`, `deducted_balance`, `change_stored_as_balance`, `remarks`, `created_at`, `updated_at`, `accommodated_by_id`, `accommodated_by_type`, `review_dismissed`, `cancellation_reason`, `cancellation_notes`, `cancelled_at`, `cancelled_by_id`, `cancelled_by_type`) VALUES
(410, 2, 1, 'Claimed', 'Paid', 80.00, 0.00, 80.00, 80.00, 0.00, 0, '', '2025-09-09 10:24:52', '2025-09-09 10:31:46', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(411, 22, 1, 'Unclaimed', 'Paid', 100.00, 17.00, 103.00, 83.00, 20.00, 1, '', '2025-08-17 10:58:43', '2025-09-19 01:15:49', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(433, 3, 2, 'Claimed', 'Paid', 100.00, 20.00, 80.00, 80.00, 0.00, 0, '', '2025-09-12 03:24:01', '2025-09-15 07:08:01', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(434, 6, 3, 'Claimed', 'Paid', 100.00, 20.00, 80.00, 80.00, 0.00, 0, '', '2025-09-12 04:58:18', '2025-09-12 04:58:44', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(435, 7, 3, 'Claimed', 'Paid', 90.00, 10.00, 80.00, 80.00, 0.00, 0, '', '2025-09-12 04:58:59', '2025-09-27 08:40:45', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(436, 25, 4, 'Unclaimed', 'Unpaid', 50.00, 0.00, 83.00, 83.00, 0.00, 0, '', '2025-09-12 05:23:56', '2025-10-15 02:37:03', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(437, 22, 5, 'Ongoing', 'Unpaid', 50.00, 0.00, 83.00, 66.00, 17.00, 0, '', '2025-09-12 07:52:42', '2025-09-15 07:07:25', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(439, 2, 1, 'Claimed', 'Paid', 90.00, 7.00, 83.00, 83.00, 0.00, 0, '', '2025-09-16 06:41:35', '2025-09-27 08:40:32', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(440, 2, 1, 'Ongoing', 'Unpaid', 50.00, 0.00, 80.00, 80.00, 0.00, 0, '', '2025-09-27 09:34:35', '2025-09-28 03:40:12', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(441, 2, 2, 'Ongoing', 'Unpaid', 40.00, 0.00, 80.00, 80.00, 0.00, 0, '', '2025-09-27 09:37:44', '2025-09-28 03:55:25', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(442, 3, 3, 'Ongoing', 'Unpaid', 50.00, 0.00, 83.00, 83.00, 0.00, 0, '', '2025-09-27 09:53:26', '2025-09-28 05:22:23', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(443, 1, 4, 'Pending', 'Unpaid', 51.50, 0.00, 103.00, 103.00, 0.00, 0, '', '2025-09-27 11:03:10', '2025-09-27 11:03:10', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(444, 2, 5, 'Cancelled', 'Unpaid', 80.00, 0.00, 150.00, 150.00, 0.00, 0, '', '2025-09-27 12:16:08', '2025-10-31 12:06:03', 1, 'admin', 0, 'Payment Issues', '', '2025-10-31 20:06:03', 8, 'staff'),
(445, 22, 1, 'Claimed', 'Paid', 100.00, 20.00, 80.00, 80.00, 0.00, 1, '', '2025-09-29 03:02:50', '2025-09-30 03:27:42', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(446, 22, 2, 'Cancelled', 'Unpaid', 80.00, 0.00, 155.00, 155.00, 0.00, 0, '', '2025-09-29 03:17:50', '2025-11-12 14:32:41', 1, 'admin', 0, 'Payment Issues', '', '2025-11-12 22:32:41', 1, 'admin'),
(447, 22, 3, 'Pending', 'Unpaid', 50.00, 0.00, 80.00, 80.00, 0.00, 0, '', '2025-09-29 03:31:50', '2025-09-29 03:31:50', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(448, 22, 4, 'Pending', 'Unpaid', 50.00, 0.00, 83.00, 83.00, 0.00, 0, '', '2025-09-29 03:34:02', '2025-09-29 03:34:02', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(449, 22, 5, 'Pending', 'Unpaid', 50.00, 0.00, 80.00, 80.00, 0.00, 0, '', '2025-09-29 04:46:42', '2025-09-29 04:46:42', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(450, 2, 1, 'Claimed', 'Paid', 100.00, 20.00, 80.00, 80.00, 0.00, 1, '', '2025-09-30 06:16:21', '2025-09-30 06:19:59', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(451, 25, 2, 'Unclaimed', 'Unpaid', 50.00, 0.00, 96.00, 96.00, 0.00, 0, '', '2025-09-30 06:16:49', '2025-11-05 02:32:16', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(452, 20, 3, 'Unclaimed', 'Unpaid', 80.00, 0.00, 155.00, 155.00, 0.00, 0, '', '2025-09-30 06:17:08', '2025-11-05 02:32:16', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(453, 20, 1, 'Claimed', 'Paid', 150.00, 10.00, 155.00, 140.00, 15.00, 1, '', '2025-09-30 07:18:48', '2025-09-30 07:25:35', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(454, 20, 4, 'Unclaimed', 'Unpaid', 45.00, 0.00, 93.00, 84.00, 9.00, 0, '', '2025-09-30 07:18:48', '2025-11-05 02:32:16', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(455, 22, 5, 'Claimed', 'Paid', 80.00, 10.00, 70.00, 70.00, 0.00, 0, '', '2025-09-30 07:24:36', '2025-10-05 10:57:21', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(456, 22, 1, 'Claimed', 'Paid', 80.00, 7.00, 73.00, 73.00, 20.00, 0, '', '2025-09-30 08:47:12', '2025-09-30 08:48:19', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(457, 29, 1, 'Ongoing', 'Unpaid', 100.00, 0.00, 176.00, 176.00, 0.00, 0, '', '2025-10-01 05:36:26', '2025-10-01 05:37:52', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(458, 25, 2, 'Pending', 'Unpaid', 120.00, 0.00, 230.00, 230.00, 0.00, 0, '', '2025-10-01 05:37:06', '2025-10-01 05:37:06', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(459, 2, 3, 'Cancelled', 'Unpaid', 100.00, 0.00, 163.00, 143.00, 20.00, 0, '', '2025-10-01 05:37:48', '2025-10-31 11:18:08', 1, 'admin', 0, 'Other', 'pano gagawen wala ng sabon', '2025-10-31 19:18:08', 1, 'admin'),
(460, 22, 1, 'Claimed', 'Paid', 80.00, 7.00, 73.00, 73.00, 0.00, 1, '', '2025-10-05 11:06:03', '2025-10-05 11:06:40', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(461, 16, 2, 'Pending', 'Unpaid', 60.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-05 11:06:16', '2025-10-05 11:06:16', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(462, 22, 1, 'Claimed', 'Paid', 75.00, 2.00, 73.00, 73.00, 0.00, 0, '', '2025-10-05 11:43:03', '2025-10-05 11:43:27', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(463, 22, 1, 'Claimed', 'Paid', 100.00, 27.00, 73.00, 73.00, 0.00, 0, '', '2025-10-06 00:03:17', '2025-10-06 00:25:06', 1, 'admin', 1, NULL, NULL, NULL, NULL, NULL),
(464, 22, 1, 'Claimed', 'Paid', 100.00, 27.00, 73.00, 73.00, 0.00, 0, '', '2025-10-06 00:25:54', '2025-10-06 00:28:30', 1, 'admin', 1, NULL, NULL, NULL, NULL, NULL),
(465, 22, 1, 'Claimed', 'Paid', 80.00, 7.00, 73.00, 73.00, 0.00, 0, '', '2025-10-06 00:28:45', '2025-10-06 00:28:56', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(466, 22, 1, 'Unclaimed', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-11 11:01:44', '2025-11-12 02:32:16', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(467, 22, 2, 'Cancelled', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-11 11:33:25', '2025-11-05 09:11:18', 1, 'admin', 0, 'Service Unavailable', '', '2025-11-05 17:11:18', 1, 'admin'),
(468, 24, 3, 'Pending', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-11 11:44:02', '2025-10-11 11:44:02', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(469, 2, 4, 'Claimed', 'Paid', 80.00, 7.00, 73.00, 73.00, 0.00, 0, '', '2025-10-11 11:49:10', '2025-10-11 11:55:02', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(470, 24, 1, 'Claimed', 'Paid', 300.00, 14.00, 286.00, 286.00, 0.00, 1, '', '2025-10-12 02:21:28', '2025-10-12 04:22:10', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(472, 22, 2, 'Claimed', 'Paid', 300.00, 21.00, 286.00, 279.00, 7.00, 1, '', '2025-10-12 04:17:41', '2025-10-12 04:29:22', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(473, 20, 3, 'Claimed', 'Paid', 100.00, 37.00, 73.00, 63.00, 10.00, 0, '', '2025-10-12 04:21:13', '2025-10-12 09:36:53', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(474, 22, 1, 'Claimed', 'Paid', 100.00, 27.00, 73.00, 73.00, 0.00, 0, '', '2025-10-12 04:34:28', '2025-11-08 12:29:45', 1, 'admin', 1, NULL, NULL, NULL, NULL, NULL),
(475, 22, 2, 'Claimed', 'Paid', 70.00, 0.00, 70.00, 70.00, 0.00, 0, '', '2025-10-12 04:35:58', '2025-11-05 07:45:20', 1, 'admin', 1, NULL, NULL, NULL, NULL, NULL),
(476, 24, 4, 'Claimed', 'Paid', 80.00, 7.00, 73.00, 73.00, 0.00, 0, '', '2025-10-12 05:14:56', '2025-10-12 10:16:25', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(477, 24, 5, 'Ongoing', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-12 05:14:56', '2025-10-12 10:07:34', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(478, 22, 6, 'Cancelled', 'Unpaid', 50.00, 0.00, 73.00, 62.50, 10.50, 0, '', '2025-10-12 09:24:17', '2025-10-31 11:22:41', 1, 'admin', 0, 'Customer No-Show', '', '2025-10-31 19:22:41', 1, 'admin'),
(479, 22, 7, 'Cancelled', 'Unpaid', 50.00, 0.00, 73.00, 62.50, 10.50, 0, '', '2025-10-12 09:24:17', '2025-10-31 12:00:28', 1, 'admin', 0, 'Duplicate Order', '', '2025-10-31 20:00:28', 1, 'admin'),
(480, 2, 1, 'Claimed', 'Paid', 80.00, 7.00, 73.00, 73.00, 0.00, 1, '', '2025-10-12 10:28:48', '2025-10-12 10:30:29', 8, 'staff', 0, NULL, NULL, NULL, NULL, NULL),
(481, 2, 2, 'Ongoing', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-12 10:28:48', '2025-11-05 08:56:18', 8, 'staff', 0, NULL, NULL, NULL, NULL, NULL),
(482, 2, 1, 'Ongoing', 'Unpaid', 50.00, 0.00, 70.00, 70.00, 0.00, 0, '', '2025-10-12 10:31:03', '2025-11-05 08:47:29', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(483, 2, 8, 'Cancelled', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-12 10:57:29', '2025-10-31 11:15:44', 1, 'admin', 0, 'Service Unavailable', '', '2025-10-31 19:15:44', 1, 'admin'),
(484, 22, 1, 'Unclaimed', 'Paid', 80.00, 7.00, 73.00, 73.00, 0.00, 0, '', '2025-10-20 06:50:55', '2026-02-12 02:32:45', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(486, 22, 1, 'Ongoing', 'Unpaid', 20.00, 0.00, 73.00, 23.00, 50.00, 0, '', '2025-10-22 06:32:31', '2025-11-05 08:46:07', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(487, 3, 1, 'Unclaimed', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-24 02:47:13', '2026-02-12 02:32:45', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(488, 3, 1, 'Cancelled', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-10-29 02:50:46', '2025-10-31 11:22:01', 1, 'admin', 0, 'Service Unavailable', '', '2025-10-31 19:22:01', 1, 'admin'),
(489, 33, 1, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-14 14:18:45', '2025-11-14 14:18:45', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(490, 2, 1, 'Ongoing', 'Unpaid', 60.00, 0.00, 105.00, 105.00, 0.00, 0, '', '2025-11-15 01:19:47', '2025-11-16 02:15:01', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(491, 3, 2, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 01:56:23', '2025-11-15 01:56:23', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(492, 3, 3, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 01:59:15', '2025-11-15 01:59:15', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(493, 3, 4, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 01:59:36', '2025-11-15 01:59:36', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(495, 2, 5, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 02:07:46', '2025-11-15 02:07:46', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(496, 3, 6, 'Pending', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 02:08:01', '2025-11-15 02:08:01', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(497, 2, 7, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 02:13:13', '2025-11-15 02:13:13', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(498, 35, 8, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 02:14:26', '2025-11-15 02:14:26', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(499, 2, 9, 'Pending', 'Unpaid', 50.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 03:55:05', '2025-11-15 03:55:05', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(500, 29, 10, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 04:09:23', '2025-11-15 04:09:23', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(501, 3, 11, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 04:28:59', '2025-11-15 04:28:59', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(502, 2, 12, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 04:51:38', '2025-11-15 04:51:38', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(503, 30, 13, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 04:54:46', '2025-11-15 04:54:46', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(504, 8, 14, 'Pending', 'Unpaid', 45.00, 0.00, 83.00, 83.00, 0.00, 0, '', '2025-11-15 04:56:31', '2025-11-15 04:56:31', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(505, 38, 15, 'Pending', 'Unpaid', 40.00, 0.00, 73.00, 73.00, 0.00, 0, '', '2025-11-15 08:52:49', '2025-11-15 08:52:49', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(506, 2, 16, 'Ongoing', 'Unpaid', 85.14, 0.00, 171.00, 151.84, 19.16, 0, '', '2025-11-15 08:55:31', '2025-11-15 09:01:08', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(507, 2, 17, 'Unclaimed', 'Unpaid', 34.86, 0.00, 70.00, 62.16, 7.84, 0, '', '2025-11-15 08:55:31', '2026-02-12 02:32:45', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(508, 39, 18, 'Ongoing', 'Unpaid', 250.00, 0.00, 466.00, 466.00, 0.00, 0, '', '2025-11-15 09:00:06', '2025-11-15 09:16:32', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(509, 3, 19, 'Pending', 'Unpaid', 50.00, 0.00, 94.00, 94.00, 0.00, 0, '', '2025-11-15 09:13:49', '2025-11-15 09:13:49', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(510, 2, 1, 'Pending', 'Unpaid', 45.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-10 14:13:07', '2026-02-10 14:13:07', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(511, 2, 2, 'Pending', 'Unpaid', 45.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-10 14:13:07', '2026-02-10 14:13:07', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(512, 2, 1, 'Pending', 'Unpaid', 70.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-11 01:23:48', '2026-02-11 01:23:48', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(513, 3, 2, 'Pending', 'Paid', 80.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-11 01:35:07', '2026-02-11 01:35:07', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(514, 3, 3, 'Pending', 'Unpaid', 45.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-11 01:53:31', '2026-02-11 01:53:31', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(515, 3, 4, 'Pending', 'Unpaid', 45.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-11 01:53:31', '2026-02-11 01:53:31', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(516, 3, 5, 'Pending', 'Unpaid', 40.00, 0.00, 75.00, 75.00, 0.00, 0, '', '2026-02-11 02:14:52', '2026-02-11 02:14:52', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(517, 3, 6, 'Pending', 'Unpaid', 40.00, 0.00, 75.00, 75.00, 0.00, 0, '', '2026-02-11 02:14:52', '2026-02-11 02:14:52', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(518, 6, 1, 'Pending', 'Unpaid', 50.00, 0.00, 75.00, 75.00, 0.00, 0, '', '2026-02-12 06:23:45', '2026-02-12 06:23:45', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(519, 1, 2, 'Pending', 'Paid', 80.00, 2.00, 78.00, 78.00, 0.00, 0, '', '2026-02-12 11:11:27', '2026-02-12 11:11:27', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(520, 1, 3, 'Pending', 'Paid', 80.00, 2.00, 78.00, 78.00, 0.00, 0, '', '2026-02-12 11:11:47', '2026-02-12 11:11:47', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(521, 2, 4, 'Pending', 'Unpaid', 45.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-12 11:56:34', '2026-02-12 11:56:34', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(522, 2, 5, 'Pending', 'Unpaid', 45.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-12 11:56:34', '2026-02-12 11:56:34', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL),
(523, 1, 6, 'Pending', 'Unpaid', 40.00, 0.00, 78.00, 78.00, 0.00, 0, '', '2026-02-12 12:49:11', '2026-02-12 12:49:11', 1, 'admin', 0, NULL, NULL, NULL, NULL, NULL);

--
-- Triggers `laundry_lists`
--
DELIMITER $$
CREATE TRIGGER `assign_queue_number` BEFORE INSERT ON `laundry_lists` FOR EACH ROW BEGIN
    DECLARE next_available_queue INT DEFAULT 1;
    DECLARE current_count INT DEFAULT 0;
    DECLARE today_date DATE DEFAULT CURDATE();
    DECLARE highest_active_queue INT DEFAULT 0;
    
    -- Count current active orders for today (excluding Claimed/Unclaimed which are completed)
    SELECT COUNT(*) INTO current_count
    FROM laundry_lists
    WHERE DATE(created_at) = today_date
    AND status NOT IN ('Claimed', 'Unclaimed');
    
    -- Check if we've reached the daily limit
    IF current_count >= 35 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Daily queue limit of 35 orders reached. Please try again tomorrow.';
    END IF;
    
    -- Find the highest queue number from today that's NOT Claimed/Unclaimed
    SELECT COALESCE(MAX(queue_number), 0) INTO highest_active_queue
    FROM laundry_lists
    WHERE DATE(created_at) = today_date
    AND status NOT IN ('Claimed', 'Unclaimed');
    
    -- If there are active orders, use the next number after the highest active queue
    IF highest_active_queue > 0 THEN
        SET next_available_queue = highest_active_queue + 1;
    ELSE
        -- No active orders today, check if we should reuse numbers from completed orders
        -- or start fresh
        SELECT MIN(queue_number) INTO next_available_queue
        FROM laundry_lists
        WHERE DATE(created_at) = today_date
        AND status IN ('Claimed', 'Unclaimed')
        AND queue_number NOT IN (
            SELECT queue_number 
            FROM laundry_lists 
            WHERE DATE(created_at) = today_date
            AND status NOT IN ('Claimed', 'Unclaimed')
        );
        
        -- If no reusable numbers found, start from 1
        IF next_available_queue IS NULL THEN
            SET next_available_queue = 1;
        END IF;
    END IF;
    
    SET NEW.queue_number = next_available_queue;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `reduce_plastic_bag_after_ready` AFTER UPDATE ON `laundry_lists` FOR EACH ROW BEGIN
-- Check if status changed to 'Ready for Pickup'
IF NEW.status = 'Ready for Pickup' AND OLD.status != 'Ready for Pickup' THEN
    -- Deduct 1 plastic bag from inventory (product_id = 7)
    UPDATE inventory
    SET available_units = available_units - 1,
        updated_at = NOW()
    WHERE product_id = 7;
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `laundry_prices`
--

CREATE TABLE `laundry_prices` (
  `id` int NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laundry_prices`
--

INSERT INTO `laundry_prices` (`id`, `item_name`, `price`, `description`, `updated_at`) VALUES
(1, 'wash_per_round', 65.00, 'Price per round of washing', '2025-12-03 00:50:18'),
(3, 'dryer_per_round', 70.00, 'Price per round of drying', '2025-11-15 15:59:16'),
(4, 'folding_service', 0.00, 'Price for folding service', '2025-11-15 15:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `role` enum('admin','staff','customer') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `role`, `expires_at`, `used`, `created_at`) VALUES
(1, 'lesterbeast17@gmail.com', '0acd4566265ccbcea7d06c5426dfe95233292dfdd854351faa5e2cac9515e673', 'staff', '2025-04-28 03:16:46', 1, '2025-04-28 02:16:46'),
(2, 'lesterbeast17@gmail.com', '5a885b675a679e0baa57cd16ada62ad6c7d7cf100cc39c91afc38b468c19805d', 'staff', '2025-04-28 03:20:31', 1, '2025-04-28 02:20:31'),
(3, 'lesterbeast17@gmail.com', '6b5296b867ca3a426a2f5181146348f05464b4dd8af0abc17c9602dadcda1abc', 'staff', '2025-04-28 03:22:16', 1, '2025-04-28 02:22:16'),
(4, 'lesterbeast17@gmail.com', 'bf516c868d29618d4ff28be3fef1fd0e8cb971387cbaf617fb9a6b9ccf9f1e16', 'staff', '2025-04-28 03:25:31', 1, '2025-04-28 02:25:31'),
(5, 'lesterbeast17@gmail.com', '8505515fc9e04003b3d4f43136846b764ff143a35ac5f63189d251795139af82', 'staff', '2025-04-28 03:36:44', 1, '2025-04-28 02:36:44'),
(6, 'lesterbeast17@gmail.com', '6951682cfbe3bd9fb664b3e9b9c5538bbe7c4ff9c572665402b239fdc63dc068', 'staff', '2025-04-28 03:47:16', 1, '2025-04-28 02:47:16'),
(7, 'lesterbeast17@gmail.com', '2a9bcd0aa96355c17848a3d51420043472681f179972e123da31a35eea5db0a2', 'staff', '2025-04-28 03:55:34', 1, '2025-04-28 02:55:34'),
(8, 'jonleemad17@gmail.com', '30d77dbcb8709404221f72f6705e73f931918ab02975d905f06adc4ad79799f2', 'admin', '2025-04-28 03:57:55', 1, '2025-04-28 02:57:55'),
(9, 'jonleemad17@gmail.com', 'eb9460293d13d00350673b0d8b2822cf2a6da9345be5c45982bb9f2396c8ac68', 'admin', '2025-04-28 04:05:11', 1, '2025-04-28 03:05:11'),
(10, 'jonleemad17@gmail.com', 'e0222d6fa4b49a9a15649295092027eb76379f1090782be6505fa2321333003c', 'admin', '2025-04-29 04:08:20', 1, '2025-04-28 03:08:20'),
(11, 'lesterbeast17@gmail.com', 'ffbf38ff709fc6060fdc53c77032b0126855b6d7d401208ca64e7df0a2d612c4', 'staff', '2025-04-28 12:16:33', 1, '2025-04-28 03:16:33'),
(12, 'jonleemad17@gmail.com', '562d1d32dcd658fb671377c8d0d26d8a85a9e524814bcae105bbb8c705411018', 'admin', '2025-04-28 12:19:13', 1, '2025-04-28 03:19:13'),
(13, 'lesterbeast17@gmail.com', '384f5fbf1ab242813beb12ee654960b4e286e7313204b752477f2365555c9ca7', 'staff', '2025-04-28 12:20:51', 0, '2025-04-28 03:20:51'),
(14, 'lesterbeast17@gmail.com', '49034a5ff04a7b8a3e97231e550eedafc587b5ffa4e1a07a2d59d3ab538ae58f', 'customer', '2025-06-11 12:13:30', 1, '2025-06-11 03:13:30'),
(15, 'lesterbeast17@gmail.com', 'a5e9d8bb1d791100875158542908964279edd76fc021d89fdee9e96a20fcbd94', 'customer', '2025-08-18 14:28:37', 1, '2025-08-18 05:28:37'),
(16, 'lesterbeast17@gmail.com', '3186c44ee9909856fe3891df2bd9a6d334ed3f7e572617649188305f2004c1fb', 'customer', '2025-08-18 14:31:36', 1, '2025-08-18 05:31:36'),
(17, 'jonleemad17@gmail.com', 'c8ead0e912cac7c55038dbb6a5a172bdc186cac6a736df30d2601b1f1fd48182', 'admin', '2025-10-19 21:33:57', 0, '2025-10-19 12:33:57');

-- --------------------------------------------------------

--
-- Table structure for table `prelist_details`
--

CREATE TABLE `prelist_details` (
  `id` int NOT NULL,
  `prelist_order_id` int NOT NULL,
  `rounds_of_wash` int NOT NULL,
  `scoops_of_detergent` int NOT NULL DEFAULT '0',
  `dryer_preference` int NOT NULL DEFAULT '0',
  `folding_service` tinyint(1) NOT NULL DEFAULT '0',
  `bleach_cups` int NOT NULL DEFAULT '0',
  `fabcon_cups` int NOT NULL DEFAULT '0',
  `detergent_product_id` int DEFAULT NULL,
  `fabcon_product_id` int DEFAULT NULL,
  `bleach_product_id` int DEFAULT NULL,
  `separate_whites` tinyint(1) NOT NULL DEFAULT '0',
  `is_whites_order` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indicates if this is a whites-only order created from separate_whites checkbox'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prelist_details`
--

INSERT INTO `prelist_details` (`id`, `prelist_order_id`, `rounds_of_wash`, `scoops_of_detergent`, `dryer_preference`, `folding_service`, `bleach_cups`, `fabcon_cups`, `detergent_product_id`, `fabcon_product_id`, `bleach_product_id`, `separate_whites`, `is_whites_order`) VALUES
(107, 107, 1, 1, 0, 0, 0, 0, 1, NULL, NULL, 1, 0),
(108, 108, 1, 1, 0, 0, 0, 0, 1, NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `prelist_items`
--

CREATE TABLE `prelist_items` (
  `id` int NOT NULL,
  `prelist_order_id` int NOT NULL,
  `tops` int NOT NULL DEFAULT '0',
  `bottoms` int NOT NULL DEFAULT '0',
  `undergarments` int NOT NULL DEFAULT '0',
  `delicates` int NOT NULL DEFAULT '0',
  `linens` int NOT NULL DEFAULT '0',
  `curtains_drapes` int NOT NULL DEFAULT '0',
  `blankets_comforters` int NOT NULL DEFAULT '0',
  `others` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prelist_items`
--

INSERT INTO `prelist_items` (`id`, `prelist_order_id`, `tops`, `bottoms`, `undergarments`, `delicates`, `linens`, `curtains_drapes`, `blankets_comforters`, `others`) VALUES
(107, 107, 0, 0, 0, 0, 0, 0, 0, 0),
(108, 108, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `prelist_orders`
--

CREATE TABLE `prelist_orders` (
  `id` int NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pre-listed',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deducted_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `adjusted_total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prelist_orders`
--

INSERT INTO `prelist_orders` (`id`, `customer_id`, `status`, `total_price`, `deducted_balance`, `adjusted_total_price`, `remarks`, `created_at`, `updated_at`) VALUES
(107, 1, 'Pre-listed', 78.00, 0.00, 78.00, '', '2026-02-12 13:52:45', '2026-02-12 13:52:45'),
(108, 1, 'Pre-listed', 78.00, 0.00, 78.00, '', '2026-02-12 13:52:45', '2026-02-12 13:52:45');

-- --------------------------------------------------------

--
-- Table structure for table `prelist_receipts`
--

CREATE TABLE `prelist_receipts` (
  `id` int NOT NULL,
  `prelist_order_id` int NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Unpaid',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_details` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `accommodated_by` varchar(255) DEFAULT 'System'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prelist_receipts`
--

INSERT INTO `prelist_receipts` (`id`, `prelist_order_id`, `customer_id`, `customer_name`, `receipt_number`, `payment_status`, `total_price`, `order_details`, `created_at`, `accommodated_by`) VALUES
(100, 107, 1, 'Lester Madrid', 'PRE-20260212024', 'Unpaid', 78.00, 'Rounds of Wash: 1 x ₱65.00 = ₱65.00\nDryer Preference: 0 round(s) x ₱70.00 = ₱0.00\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-12 13:52:45', 'System'),
(101, 108, 1, 'Lester Madrid', 'PRE-20260212025', 'Unpaid', 78.00, 'Rounds of Wash: 1 x ₱65.00 = ₱65.00\nDryer Preference: 0 round(s) x ₱70.00 = ₱0.00\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-12 13:52:45', 'System');

--
-- Triggers `prelist_receipts`
--
DELIMITER $$
CREATE TRIGGER `before_insert_pre_receipt` BEFORE INSERT ON `prelist_receipts` FOR EACH ROW BEGIN
    DECLARE current_date_key DATE;
    DECLARE next_sequence INT;
    DECLARE new_receipt_num VARCHAR(20);
    
    -- Get current date
    SET current_date_key = CURRENT_DATE;
    
    -- Get and increment the sequence for today
    INSERT INTO receipt_sequences (date_key, last_sequence) 
    VALUES (current_date_key, 1) 
    ON DUPLICATE KEY UPDATE last_sequence = last_sequence + 1;
    
    -- Get the updated sequence number
    SELECT last_sequence INTO next_sequence 
    FROM receipt_sequences 
    WHERE date_key = current_date_key;
    
    -- Generate the receipt number with PRE- prefix: PRE-YYYYMMDD + 3-digit sequence
    SET new_receipt_num = CONCAT(
        'PRE-',
        DATE_FORMAT(current_date_key, '%Y%m%d'),
        LPAD(next_sequence, 3, '0')
    );
    
    -- Set the receipt number
    SET NEW.receipt_number = new_receipt_num;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `price_announcements`
--

CREATE TABLE `price_announcements` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `effective_date` date NOT NULL,
  `announcement_type` enum('price_increase','price_decrease') DEFAULT 'price_increase',
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `price_announcements`
--

INSERT INTO `price_announcements` (`id`, `title`, `message`, `effective_date`, `announcement_type`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 'Magbababa tayo ng price guys', 'Dryer 75 to 70', '2025-10-01', 'price_decrease', 1, 1, '2025-09-30 01:28:51', '2025-09-30 01:28:51'),
(4, 'Magbababa tayo ng price guys', 'Wash per round 60 nalang', '2025-10-03', 'price_decrease', 1, 1, '2025-10-02 03:10:54', '2025-10-02 03:10:54'),
(6, 'Price Increase', 'Wash from 60 to 70', '2025-10-04', 'price_increase', 1, 1, '2025-10-03 03:00:48', '2025-10-03 03:00:48'),
(7, 'Sorry guys may price adjust tayo', 'Magtataas price ng laba', '2025-12-03', 'price_increase', 1, 1, '2025-12-03 00:50:18', '2025-12-03 00:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int NOT NULL,
  `receipt_number` varchar(20) NOT NULL,
  `laundry_list_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `queue_number` int DEFAULT NULL,
  `payment_status` enum('Paid','Unpaid') NOT NULL,
  `amount_tendered` decimal(10,2) DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL,
  `amount_change` decimal(10,2) DEFAULT '0.00',
  `order_details` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `receipt_number`, `laundry_list_id`, `customer_id`, `customer_name`, `queue_number`, `payment_status`, `amount_tendered`, `total_price`, `amount_change`, `order_details`, `created_at`) VALUES
(252, '20250909000007', 410, 2, 'Hannah Janee', 1, 'Paid', 80.00, 80.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Sep 09, 2025 - 06:31 PM', '2025-09-09 10:24:52'),
(253, '20250909000008', 411, 22, 'John', 1, 'Paid', 100.00, 83.00, 17.00, 'Clothing & Household Items:\nTops: 5\nBottoms: 5\nUndergarments: 5\nWashing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00\nBleach: 1 cup(s) (Zonrox) x ₱10.00 = ₱10.00\nOriginal Price: ₱103.00\nBalance Used: -₱20.00\nChange ₱17.00 stored as customer balance', '2025-09-09 10:58:43'),
(275, '20250912000018', 433, 3, 'Janjan', 2, 'Paid', 100.00, 80.00, 20.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nChange: ₱20.00', '2025-09-12 03:24:01'),
(276, '20250912000019', 434, 6, 'Vennies', 3, 'Paid', 100.00, 80.00, 20.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nChange: ₱20.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Sep 12, 2025 - 12:58 PM', '2025-09-12 04:58:18'),
(277, '20250912000020', 435, 7, 'Han', 3, 'Paid', 90.00, 80.00, 10.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nChange: ₱10.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Sep 27, 2025 - 04:40 PM', '2025-09-12 04:58:59'),
(278, '20250912000021', 436, 25, 'Rapunzel', 4, 'Unpaid', 50.00, 83.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-09-12 05:23:56'),
(279, '20250912000022', 437, 22, 'John', 5, 'Unpaid', 50.00, 66.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nOriginal Price: ₱83.00\nBalance Used: -₱17.00', '2025-09-12 07:52:42'),
(281, '20250916000002', 439, 2, 'Hannah Janee', 1, 'Paid', 90.00, 83.00, 7.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nChange: ₱7.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Sep 27, 2025 - 04:40 PM', '2025-09-16 06:41:35'),
(282, '20250927000001', 441, 2, 'Hannah Janee', 2, 'Unpaid', 40.00, 80.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2025-09-27 09:37:44'),
(283, '20250927002', 442, 3, 'Janjan', 3, 'Unpaid', 50.00, 83.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-09-27 09:53:26'),
(284, '20250927003', 443, 1, 'Lester Madrid', 4, 'Unpaid', 51.50, 103.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00\nBleach: 1 cup(s) (Zonrox) x ₱10.00 = ₱10.00', '2025-09-27 11:03:10'),
(285, '20250927004', 444, 2, 'Hannah Janee', 5, 'Unpaid', 80.00, 150.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 1 x ₱70.00 = ₱70.00\nFolding Service: Yes\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2025-09-27 12:16:08'),
(286, '20250929001', 445, 22, 'John', 1, 'Paid', 100.00, 80.00, 20.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nChange ₱20.00 stored as customer balance\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Sep 30, 2025 - 11:27 AM', '2025-09-29 03:02:50'),
(287, '20250929002', 446, 22, 'John', 2, 'Unpaid', 80.00, 155.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 1 x ₱75.00 = ₱75.00\nFolding Service: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2025-09-29 03:17:50'),
(288, '20250929003', 447, 22, 'John', 3, 'Unpaid', 50.00, 80.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2025-09-29 03:31:51'),
(289, '20250929004', 448, 22, 'John', 4, 'Unpaid', 50.00, 83.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-09-29 03:34:02'),
(290, '20250929005', 449, 22, 'John', 5, 'Unpaid', 50.00, 80.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2025-09-29 04:46:42'),
(291, '20250930001', 450, 2, 'Hannah Janee', 1, 'Paid', 100.00, 80.00, 20.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nChange ₱20.00 stored as customer balance\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Sep 30, 2025 - 02:19 PM', '2025-09-30 06:16:22'),
(292, '20250930002', 451, 25, 'Rapunzel', 2, 'Unpaid', 50.00, 96.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00', '2025-09-30 06:16:50'),
(293, '20250930003', 452, 20, 'Lester', 3, 'Unpaid', 80.00, 155.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 1 x ₱75.00 = ₱75.00\nFolding Service: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2025-09-30 06:17:09'),
(294, '20250930004', 453, 20, 'Lester', 1, 'Paid', 150.00, 140.00, 10.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 1 x ₱75.00 = ₱75.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nOriginal Price: ₱155.00\nBalance Used: -₱15.00\nChange ₱10.00 stored as customer balance\n\nUpdated due to amount tendered modification (Previous: ₱81.25) - Last Modified: Sep 30, 2025 - 03:23 PM', '2025-09-30 07:18:48'),
(295, '20250930005', 454, 20, 'Lester', 4, 'Unpaid', 45.00, 84.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00\nOriginal Price: ₱93.00\nBalance Used: -₱9.00\n\nUpdated due to amount tendered modification (Previous: ₱48.75) - Last Modified: Sep 30, 2025 - 04:53 PM', '2025-09-30 07:18:48'),
(296, '20250930006', 455, 22, 'John', 5, 'Paid', 80.00, 70.00, 10.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nChange: ₱10.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Oct 05, 2025 - 06:57 PM', '2025-09-30 07:24:36'),
(297, '20250930009', 456, 22, 'John', 0, 'Paid', 80.00, 73.00, 7.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 0 x ₱75.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00\nOriginal Price: ₱93.00\nBalance Used: -₱20.00', '2025-09-30 08:47:12'),
(298, '20251001001', 457, 29, 'Haja Padrones', 1, 'Unpaid', 100.00, 176.00, 0.00, 'Washing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 1 x ₱70.00 = ₱70.00\nFolding Service: Yes\nDetergent: 2 scoop(s) (surf) x ₱10.00 = ₱20.00\nFabric Conditioner: 1 cup(s) (Downy) x ₱16.00 = ₱16.00', '2025-10-01 05:36:27'),
(299, '20251001002', 458, 25, 'Rapunzel', 2, 'Unpaid', 120.00, 230.00, 0.00, 'Washing Round(s): 2 x ₱70.00 = ₱140.00\nDrying Round(s): 1 x ₱70.00 = ₱70.00\nFolding Service: Yes\nDetergent: 2 scoop(s) (surf) x ₱10.00 = ₱20.00', '2025-10-01 05:37:06'),
(300, '20251001003', 459, 2, 'Hannah Janee', 3, 'Unpaid', 100.00, 143.00, 0.00, 'Clothing & Household Items:\nTops: 5\nBottoms: 5\nUndergarments: 5\nWashing Round(s): 1 x ₱70.00 = ₱70.00\nDrying Round(s): 1 x ₱70.00 = ₱70.00\nFolding Service: Yes\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00\nOriginal Price: ₱163.00\nBalance Used: -₱20.00', '2025-10-01 05:37:48'),
(301, '20251005001', 460, 22, 'John', 1, 'Paid', 80.00, 73.00, 7.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nChange ₱7.00 stored as customer balance\n\nUpdated due to amount tendered modification (Previous: ₱40.00) - Last Modified: Oct 05, 2025 - 07:06 PM', '2025-10-05 11:06:03'),
(302, '20251005002', 461, 16, 'lala', 2, 'Unpaid', 60.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-05 11:06:16'),
(303, '20251005003', 462, 22, 'John', 1, 'Paid', 75.00, 73.00, 2.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nChange: ₱2.00\n\nUpdated due to amount tendered modification (Previous: ₱59.00) - Last Modified: Oct 05, 2025 - 07:43 PM', '2025-10-05 11:43:03'),
(304, '20251006001', 463, 22, 'John', 1, 'Paid', 100.00, 73.00, 27.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-06 00:03:17'),
(305, '20251006002', 464, 22, 'John', 1, 'Paid', 100.00, 73.00, 27.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-06 00:25:54'),
(306, '20251006003', 465, 22, 'John', 1, 'Paid', 80.00, 73.00, 7.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-06 00:28:45'),
(307, '20251011001', 469, 2, 'Hannah Janee', 4, 'Paid', 80.00, 73.00, 7.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nChange: ₱7.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Oct 11, 2025 - 07:55 PM', '2025-10-11 11:49:11'),
(308, '20251012001', 470, 24, 'Madrid', 1, 'Paid', 300.00, 286.00, 14.00, 'Clothing & Household Items:\nTops: 5\nBottoms: 5\nUndergarments: 5\nLinens: 1\nCurtains & Drapes: 1\nWashing Round(s): 2 x ₱60.00 = ₱120.00\nDrying Round(s): 2 x ₱70.00 = ₱140.00\nFolding Service: Yes\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00\nChange ₱14.00 stored as customer balance\n\nUpdated due to amount tendered modification (Previous: ₱150.00) - Last Modified: Oct 12, 2025 - 12:22 PM', '2025-10-12 02:21:29'),
(310, '20251012003', 472, 22, 'John', 2, 'Paid', 300.00, 279.00, 21.00, 'Clothing & Household Items:\nTops: 1\nBottoms: 1\nUndergarments: 1\nWashing Round(s): 2 x ₱60.00 = ₱120.00\nDrying Round(s): 2 x ₱70.00 = ₱140.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nFabric Conditioner: 1 cup(s) (Del) x ₱13.00 = ₱13.00\nOriginal Price: ₱286.00\nBalance Used: -₱7.00\nChange ₱21.00 stored as customer balance\n\nUpdated due to amount tendered modification (Previous: ₱140.00) - Last Modified: Oct 12, 2025 - 12:29 PM', '2025-10-12 04:17:41'),
(311, '20251012004', 473, 20, 'Lester', 3, 'Paid', 100.00, 63.00, 37.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nOriginal Price: ₱73.00\nBalance Used: -₱10.00\nChange: ₱37.00', '2025-10-12 04:21:13'),
(312, '20251012005', 474, 22, 'John', 1, 'Paid', 100.00, 73.00, 27.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nChange: ₱27.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Oct 12, 2025 - 05:36 PM', '2025-10-12 04:34:28'),
(313, '20251012006', 475, 22, 'John', 2, 'Paid', 70.00, 70.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Oct 12, 2025 - 05:56 PM', '2025-10-12 04:35:58'),
(314, '20251012007', 476, 24, 'Madrid', 4, 'Paid', 80.00, 73.00, 7.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nChange: ₱7.00\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Oct 12, 2025 - 06:16 PM', '2025-10-12 05:14:56'),
(315, '20251012008', 477, 24, 'Madrid', 5, 'Unpaid', 50.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-12 05:14:56'),
(316, '20251012009', 478, 22, 'John', 6, 'Unpaid', 50.00, 62.50, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nOriginal Price: ₱73.00\nBalance Used: -₱10.50', '2025-10-12 09:24:17'),
(317, '20251012010', 479, 22, 'John', 7, 'Unpaid', 50.00, 62.50, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nOriginal Price: ₱73.00\nBalance Used: -₱10.50', '2025-10-12 09:24:17'),
(318, '20251012011', 480, 2, 'Hannah Janee', 1, 'Paid', 80.00, 73.00, 7.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nChange ₱7.00 stored as customer balance\n\nUpdated due to amount tendered modification (Previous: ₱50.00) - Last Modified: Oct 12, 2025 - 06:30 PM', '2025-10-12 10:28:48'),
(319, '20251012012', 481, 2, 'Hannah Janee', 2, 'Unpaid', 50.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-12 10:28:48'),
(320, '20251012013', 482, 2, 'Hannah Janee', 1, 'Unpaid', 50.00, 70.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2025-10-12 10:31:03'),
(321, '20251012014', 483, 2, 'Hannah Janee', 8, 'Unpaid', 50.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-12 10:57:29'),
(322, '20251020001', 484, 22, 'John', 1, 'Paid', 80.00, 73.00, 7.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-20 06:50:55'),
(324, '20251022003', 486, 22, 'John', 1, 'Unpaid', 20.00, 23.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nOriginal Price: ₱73.00\nBalance Used: -₱50.00', '2025-10-22 06:32:31'),
(325, '20251024001', 487, 3, 'Janjann', 1, 'Unpaid', 50.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-24 02:47:13'),
(326, '20251029001', 488, 3, 'Janjann', 1, 'Unpaid', 50.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-10-29 02:50:46'),
(327, '20251114001', 489, 33, 'dada', 1, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-14 14:18:45'),
(328, '20251115001', 490, 2, 'Hannah Janee', 1, 'Unpaid', 60.00, 105.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nFabric Conditioner: 2 cup(s) (Downy) x ₱16.00 = ₱32.00', '2025-11-15 01:19:47'),
(329, '20251115002', 491, 3, 'Janjann', 2, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 01:56:24'),
(330, '20251115003', 492, 3, 'Janjann', 3, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 01:59:15'),
(331, '20251115004', 493, 3, 'Janjann', 4, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 01:59:36'),
(332, '20251115005', 495, 2, 'Hannah Janee', 5, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 02:07:47'),
(333, '20251115006', 496, 3, 'Janjann', 6, 'Unpaid', 50.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 02:08:01'),
(334, '20251115007', 497, 2, 'Hannah Janee', 7, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 02:13:13'),
(335, '20251115008', 498, 35, 'dadae', 8, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 02:14:26'),
(336, '20251115009', 499, 2, 'Hannah Janee', 9, 'Unpaid', 50.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 03:55:05'),
(337, '20251115010', 500, 29, 'Haja Padrones', 10, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 04:09:23'),
(338, '20251115011', 501, 3, 'Janjann', 11, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 04:29:00'),
(339, '20251115012', 502, 2, 'Hannah Janee', 12, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 04:51:39'),
(340, '20251115013', 503, 30, 'Elaine', 13, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 04:54:46'),
(341, '20251115014', 504, 8, 'yeehaa', 14, 'Unpaid', 45.00, 83.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes - ₱10.00\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 04:56:31'),
(342, '20251115015', 505, 38, 'adw', 15, 'Unpaid', 40.00, 73.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2025-11-15 08:52:50'),
(343, '20251115016', 506, 2, 'Hannah Janee', 16, 'Unpaid', 85.14, 151.84, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 1 x ₱70.00 = ₱70.00\nFolding Service: Yes - ₱10.00\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nFabric Conditioner: 1 cup(s) (Del) x ₱11.00 = ₱11.00\nBleach: 1 cup(s) (Zonrox) x ₱10.00 = ₱10.00\nOriginal Price: ₱171.00\nBalance Used: -₱19.16', '2025-11-15 08:55:31'),
(344, '20251115017', 507, 2, 'Hannah Janee', 17, 'Unpaid', 34.86, 62.16, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00\nOriginal Price: ₱70.00\nBalance Used: -₱7.84', '2025-11-15 08:55:31'),
(345, '20251115018', 508, 39, 'hana', 18, 'Unpaid', 250.00, 466.00, 0.00, 'Washing Round(s): 4 x ₱60.00 = ₱240.00\nDrying Round(s): 2 x ₱70.00 = ₱140.00\nFolding Service: Yes - ₱10.00\nSeparate Whites: Yes\nDetergent: 5 scoop(s) (surf) x ₱10.00 = ₱50.00\nFabric Conditioner: 1 cup(s) (Downy) x ₱16.00 = ₱16.00\nBleach: 1 cup(s) (Zonrox) x ₱10.00 = ₱10.00', '2025-11-15 09:00:06'),
(346, '20251115019', 509, 3, 'Janjann', 19, 'Unpaid', 50.00, 94.00, 0.00, 'Washing Round(s): 1 x ₱60.00 = ₱60.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: Yes - ₱10.00\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00\nFabric Conditioner: 1 cup(s) (Del) x ₱11.00 = ₱11.00', '2025-11-15 09:13:49'),
(347, '20260210001', 510, 2, 'Hannah Janee', 1, 'Unpaid', 45.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-10 14:13:08'),
(348, '20260210002', 511, 2, 'Hannah Janee', 2, 'Unpaid', 45.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-10 14:13:08'),
(349, '20260211001', 512, 2, 'Hannah Janee', 1, 'Unpaid', 70.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-11 01:23:48'),
(350, '20260211002', 513, 3, 'Janjann', 2, 'Paid', 80.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-11 01:35:07'),
(351, '20260211003', 514, 3, 'Janjann', 3, 'Unpaid', 45.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-11 01:53:31'),
(352, '20260211004', 515, 3, 'Janjann', 4, 'Unpaid', 45.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-11 01:53:31'),
(353, '20260211005', 516, 3, 'Janjann', 5, 'Unpaid', 40.00, 75.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2026-02-11 02:14:52'),
(354, '20260211006', 517, 3, 'Janjann', 6, 'Unpaid', 40.00, 75.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2026-02-11 02:14:52'),
(355, '20260212001', 518, 6, 'Vennies', 1, 'Unpaid', 50.00, 75.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: No\nDetergent: 1 scoop(s) (surf) x ₱10.00 = ₱10.00', '2026-02-12 06:23:45'),
(356, '20260212012', 519, 1, 'Lester Madrid', 0, 'Paid', 80.00, 78.00, 2.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-12 11:11:27'),
(357, '20260212013', 520, 1, 'Lester Madrid', 0, 'Paid', 80.00, 78.00, 2.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-12 11:11:47'),
(358, '20260212016', 521, 2, 'Hannah Janee', 4, 'Unpaid', 45.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-12 11:56:34'),
(359, '20260212017', 522, 2, 'Hannah Janee', 5, 'Unpaid', 45.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-12 11:56:34'),
(360, '20260212020', 523, 1, 'Lester Madrid', 0, 'Unpaid', 40.00, 78.00, 0.00, 'Washing Round(s): 1 x ₱65.00 = ₱65.00\nDrying Round(s): 0 x ₱70.00 = ₱0.00\nFolding Service: No\nSeparate Whites: Yes\nDetergent: 1 scoop(s) (Tide) x ₱13.00 = ₱13.00', '2026-02-12 12:49:11');

--
-- Triggers `receipts`
--
DELIMITER $$
CREATE TRIGGER `before_insert_receipt` BEFORE INSERT ON `receipts` FOR EACH ROW BEGIN
    DECLARE current_date_key DATE;
    DECLARE next_sequence INT;
    DECLARE new_receipt_num VARCHAR(20);
    
    -- Get current date
    SET current_date_key = CURRENT_DATE;
    
    -- Get and increment the sequence for today
    INSERT INTO receipt_sequences (date_key, last_sequence) 
    VALUES (current_date_key, 1) 
    ON DUPLICATE KEY UPDATE last_sequence = last_sequence + 1;
    
    -- Get the updated sequence number
    SELECT last_sequence INTO next_sequence 
    FROM receipt_sequences 
    WHERE date_key = current_date_key;
    
    -- Generate the receipt number: YYYYMMDD + 6-digit sequence
    SET new_receipt_num = CONCAT(
        DATE_FORMAT(current_date_key, '%Y%m%d'),
        LPAD(next_sequence, 3, '0')
    );
    
    -- Set the receipt number
    SET NEW.receipt_number = new_receipt_num;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `receipt_sequences`
--

CREATE TABLE `receipt_sequences` (
  `date_key` date NOT NULL,
  `last_sequence` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `receipt_sequences`
--

INSERT INTO `receipt_sequences` (`date_key`, `last_sequence`) VALUES
('2025-07-07', 6),
('2025-07-08', 1),
('2025-07-09', 16),
('2025-07-10', 25),
('2025-07-12', 2),
('2025-07-13', 36),
('2025-07-14', 28),
('2025-07-15', 30),
('2025-07-16', 8),
('2025-07-19', 2),
('2025-07-25', 2),
('2025-07-27', 6),
('2025-07-28', 28),
('2025-07-29', 25),
('2025-07-30', 21),
('2025-08-07', 23),
('2025-08-08', 9),
('2025-08-20', 9),
('2025-08-25', 1),
('2025-08-26', 28),
('2025-08-28', 1),
('2025-09-07', 3),
('2025-09-08', 3),
('2025-09-09', 10),
('2025-09-11', 3),
('2025-09-12', 22),
('2025-09-15', 1),
('2025-09-16', 2),
('2025-09-27', 4),
('2025-09-29', 5),
('2025-09-30', 9),
('2025-10-01', 3),
('2025-10-05', 3),
('2025-10-06', 3),
('2025-10-11', 1),
('2025-10-12', 14),
('2025-10-15', 1),
('2025-10-17', 1),
('2025-10-20', 1),
('2025-10-22', 3),
('2025-10-24', 1),
('2025-10-29', 1),
('2025-11-05', 1),
('2025-11-14', 1),
('2025-11-15', 19),
('2026-02-10', 2),
('2026-02-11', 6),
('2026-02-12', 25);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `laundry_list_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `review_text` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT '0'
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `laundry_list_id`, `customer_id`, `rating`, `review_text`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 456, 22, 2, 'Mabilis at maayos', '2025-10-03 08:29:22', NULL, 0),
(4, 460, 22, 0, '', '2025-10-05 11:37:42', '2025-10-05 11:37:48', 0),
(5, 462, 22, 5, 'so good', '2025-10-05 11:56:46', NULL, 0),
(6, 465, 22, 2, 'masungit tindera niyo yung hannah jane', '2025-10-06 00:29:11', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_price_changes`
--

CREATE TABLE `scheduled_price_changes` (
  `id` int NOT NULL,
  `item_type` enum('service','supply') NOT NULL,
  `item_identifier` varchar(255) NOT NULL COMMENT 'item_name for service, product_id for supply',
  `old_price` decimal(10,2) NOT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `is_applied` tinyint(1) DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `applied_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `scheduled_price_changes`
--

INSERT INTO `scheduled_price_changes` (`id`, `item_type`, `item_identifier`, `old_price`, `new_price`, `effective_date`, `is_applied`, `created_by`, `created_at`, `applied_at`) VALUES
(1, 'service', 'dryer_per_round', 75.00, 70.00, '2025-09-30', 0, 1, '2025-09-29 03:08:18', NULL),
(2, 'service', 'dryer_per_round', 75.00, 70.00, '2025-10-01', 0, 1, '2025-09-30 01:28:51', NULL),
(3, 'service', 'wash_per_round', 70.00, 60.00, '2025-10-03', 0, 1, '2025-10-02 03:10:54', NULL),
(4, 'service', 'wash_per_round', 60.00, 70.00, '2025-10-04', 0, 1, '2025-10-03 03:00:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `contact_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`id`, `name`, `email`, `email_verified_at`, `contact_num`, `password`, `remember_token`, `created_at`, `updated_at`, `archived`) VALUES
(9, 'Hannah Jane', 'hana@gmail.com', NULL, '090909092', '$2y$10$4A17/sjKyAAsKoz34TXoH.OA1O.uC.sjUrJGfJ9SytUJtdGh23dZC', NULL, '2025-05-05 20:40:44', '2025-11-15 12:38:49', 0);

-- --------------------------------------------------------

--
-- Table structure for table `status_change_logs`
--

CREATE TABLE `status_change_logs` (
  `id` int NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `old_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by_user_id` bigint UNSIGNED NOT NULL,
  `changed_by_user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_change_logs`
--

INSERT INTO `status_change_logs` (`id`, `order_id`, `old_status`, `new_status`, `changed_by_user_id`, `changed_by_user_name`, `changed_at`) VALUES
(1, 445, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-09-30 11:27:17'),
(2, 445, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-09-30 11:27:42'),
(3, 450, 'Pending', 'Ongoing', 1, 'Haha', '2025-09-30 14:17:16'),
(4, 450, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-09-30 14:17:23'),
(5, 450, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-09-30 14:19:59'),
(6, 451, 'Pending', 'Ongoing', 1, 'Haha', '2025-09-30 14:20:04'),
(7, 453, 'Pending', 'Ongoing', 1, 'Haha', '2025-09-30 15:22:06'),
(8, 453, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-09-30 15:22:35'),
(9, 455, 'Pending', 'Ongoing', 1, 'Haha', '2025-09-30 15:24:43'),
(10, 455, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-09-30 15:24:48'),
(11, 453, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-09-30 15:25:35'),
(12, 456, 'Pending', 'Ongoing', 1, 'Haha', '2025-09-30 16:47:44'),
(13, 456, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-09-30 16:48:11'),
(14, 456, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-09-30 16:48:19'),
(15, 452, 'Pending', 'Ongoing', 8, 'Hannah Janee', '2025-10-01 08:01:28'),
(16, 454, 'Pending', 'Ongoing', 8, 'Hannah Janee', '2025-10-01 08:01:35'),
(17, 451, 'Ongoing', 'Ready for Pickup', 8, 'Hannah Janee', '2025-10-01 08:01:38'),
(18, 452, 'Ongoing', 'Ready for Pickup', 8, 'Hannah Janee', '2025-10-01 08:01:46'),
(19, 454, 'Ongoing', 'Ready for Pickup', 8, 'Hannah Janee', '2025-10-01 08:01:55'),
(20, 457, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-01 13:37:52'),
(21, 460, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-05 19:06:21'),
(22, 460, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-05 19:06:24'),
(23, 460, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-10-05 19:06:40'),
(24, 462, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-05 19:43:07'),
(25, 462, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-05 19:43:10'),
(26, 462, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-10-05 19:43:27'),
(27, 463, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-06 08:03:24'),
(28, 463, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-06 08:03:27'),
(29, 463, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-10-06 08:03:30'),
(30, 464, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-06 08:25:58'),
(31, 464, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-06 08:26:00'),
(32, 464, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-10-06 08:26:03'),
(33, 465, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-06 08:28:48'),
(34, 465, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-06 08:28:51'),
(35, 465, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-10-06 08:28:56'),
(36, 466, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-11 19:43:13'),
(37, 466, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-11 19:43:18'),
(38, 469, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-11 19:54:45'),
(39, 469, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-11 19:54:50'),
(40, 470, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-12 12:21:49'),
(41, 470, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-12 12:21:52'),
(42, 470, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-10-12 12:22:10'),
(43, 472, 'Pending', 'Ongoing', 1, 'Haha', '2025-10-12 12:29:02'),
(44, 472, 'Ongoing', 'Ready for Pickup', 1, 'Haha', '2025-10-12 12:29:06'),
(45, 472, 'Ready for Pickup', 'Claimed', 1, 'Haha', '2025-10-12 12:29:22'),
(46, 484, 'Pending', 'Ongoing', 1, 'John Lee Bagg', '2025-10-20 14:51:03'),
(47, 484, 'Ongoing', 'Ready for Pickup', 1, 'John Lee Bagg', '2025-10-20 14:51:06'),
(48, 486, 'Pending', 'Ongoing', 1, 'John Lee Bagg', '2025-11-05 16:46:07'),
(49, 482, 'Pending', 'Ongoing', 1, 'John Lee Bagg', '2025-11-05 16:47:29'),
(50, 481, 'Pending', 'Ongoing', 1, 'John Lee Bagg', '2025-11-05 16:56:18'),
(51, 487, 'Ongoing', 'Ready for Pickup', 1, 'John Lee Bagg', '2025-11-05 16:57:27'),
(52, 506, 'Pending', 'Ongoing', 1, 'John Lee Bagg', '2025-11-15 17:01:08'),
(53, 508, 'Pending', 'Ongoing', 1, 'John Lee Bagg', '2025-11-15 17:16:32'),
(54, 507, 'Pending', 'Ongoing', 1, 'John Lee Bag', '2025-11-15 23:22:24'),
(55, 490, 'Pending', 'Ongoing', 1, 'John Lee Bag', '2025-11-16 10:15:01');

-- --------------------------------------------------------

--
-- Table structure for table `super_admins`
--

CREATE TABLE `super_admins` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `super_admins`
--

INSERT INTO `super_admins` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@example.com', '$2y$10$rrKjSJ7LCHsZ2sKCiT1mHOuRUiZ/W2F/1N6Ru0NZ6YPC6G6sF141W', '2025-08-09 06:47:42', '2025-08-09 06:48:20');

-- --------------------------------------------------------

--
-- Table structure for table `supply_categories`
--

CREATE TABLE `supply_categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `supply_categories`
--

INSERT INTO `supply_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Detergent', 'Cleaning products for washing clothes', '2025-06-06 09:04:07', '2025-06-06 09:04:07'),
(2, 'Fabric Conditioner', 'Products to soften and freshen fabrics', '2025-06-06 09:04:07', '2025-06-06 09:04:07'),
(3, 'Bleach', 'Products for whitening and disinfecting clothes', '2025-06-06 09:04:07', '2025-06-06 09:04:07'),
(4, 'Plastic Bag', 'To store the finished order', '2025-06-06 09:06:07', '2025-06-06 09:06:07');

-- --------------------------------------------------------

--
-- Table structure for table `supply_products`
--

CREATE TABLE `supply_products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `measurement` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT '0.00' COMMENT 'Price per unit (scoop/cup/piece)',
  `max_unit_per_container` int NOT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `supply_products`
--

INSERT INTO `supply_products` (`id`, `category_id`, `name`, `measurement`, `price`, `unit_price`, `max_unit_per_container`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tide', NULL, 1600.00, 13.00, 120, 'yes', 1, '2025-06-06 09:07:38', '2025-08-20 04:47:30'),
(2, 2, 'Del', NULL, 1400.00, 11.00, 120, NULL, 1, '2025-06-06 09:07:38', '2025-11-05 06:11:57'),
(4, 3, 'Zonrox', NULL, 91.00, 10.00, 12, NULL, 1, '2025-06-06 09:07:38', '2025-09-30 02:33:35'),
(7, 4, 'Plastic Bag', NULL, 101.00, 10.00, 12, NULL, 1, '2025-06-06 09:07:38', '2025-08-25 11:13:02'),
(16, 2, 'Downy', NULL, 1600.00, 16.00, 100, NULL, 1, '2025-06-06 10:49:29', '2025-06-29 07:03:48'),
(17, 1, 'surf', NULL, 1500.00, 10.00, 120, NULL, 1, '2025-06-07 02:21:24', '2025-10-03 02:11:11');

--
-- Triggers `supply_products`
--
DELIMITER $$
CREATE TRIGGER `after_product_insert` AFTER INSERT ON `supply_products` FOR EACH ROW BEGIN
    INSERT INTO inventory (product_id, stock_quantity, available_units, created_at, updated_at)
    VALUES (NEW.id, 0, 0, NOW(), NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `supply_transactions`
--

CREATE TABLE `supply_transactions` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `type` enum('IN','OUT','Used') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply_transactions`
--

INSERT INTO `supply_transactions` (`id`, `product_id`, `quantity`, `type`, `description`, `created_at`, `updated_at`) VALUES
(38, 4, 1, 'IN', NULL, '2025-04-27 03:52:35', '2025-06-07 02:16:34'),
(40, 4, 1, 'IN', NULL, '2025-04-27 03:53:34', '2025-06-07 02:16:06'),
(69, 1, 15, 'IN', NULL, '2025-05-09 02:15:18', '2025-05-09 02:15:18'),
(71, 1, 1, 'Used', NULL, '2025-05-09 02:51:51', '2025-05-09 02:51:51'),
(72, 1, 10, 'IN', NULL, '2025-05-09 10:33:20', '2025-05-09 10:33:20'),
(73, 2, 10, 'IN', NULL, '2025-05-09 10:33:27', '2025-05-09 10:33:27'),
(74, 2, 1, 'Used', NULL, '2025-05-09 10:33:27', '2025-05-09 10:33:27'),
(75, 7, 5, 'IN', NULL, '2025-05-09 10:33:33', '2025-05-09 10:33:33'),
(76, 1, 3, 'IN', NULL, '2025-05-09 14:37:59', '2025-05-09 14:37:59'),
(77, 16, 5, 'IN', NULL, '2025-06-07 02:20:12', '2025-06-07 02:20:12'),
(78, 16, 1, 'Used', NULL, '2025-06-07 02:20:12', '2025-06-07 02:20:12'),
(79, 17, 10, 'IN', NULL, '2025-06-07 02:22:00', '2025-06-07 02:22:00'),
(80, 17, 1, 'Used', NULL, '2025-06-07 02:22:00', '2025-06-07 02:22:00'),
(81, 4, 5, 'IN', NULL, '2025-07-16 02:04:01', '2025-07-16 02:04:01'),
(82, 17, 1, 'Used', NULL, '2025-07-16 02:09:11', '2025-07-16 02:09:11'),
(83, 7, 1, 'Used', NULL, '2025-07-28 05:38:30', '2025-07-28 05:38:30'),
(84, 1, 5, 'IN', NULL, '2025-08-03 10:47:07', '2025-08-03 10:47:07'),
(87, 1, 2, 'IN', NULL, '2025-08-26 06:55:12', '2025-08-26 06:55:12'),
(89, 2, 5, 'IN', NULL, '2025-09-09 10:26:49', '2025-09-09 10:26:49'),
(90, 17, 5, 'IN', NULL, '2025-09-09 10:27:02', '2025-09-09 10:27:02'),
(91, 1, 5, 'IN', NULL, '2025-09-09 10:27:14', '2025-09-09 10:27:14'),
(95, 17, 4, 'OUT', NULL, '2025-09-28 05:35:22', '2025-09-28 05:35:22'),
(99, 17, 1, 'Used', NULL, '2025-11-15 16:11:21', '2025-11-15 16:11:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('walk-in','registered') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `balance` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expires` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `contact_num`, `password`, `type`, `balance`, `created_at`, `updated_at`, `verification_token`, `token_expires`, `email_verified_at`) VALUES
(1, 'Lester Madrid', 'lester1@example.com', '09123456789', '$2y$12$kcfPRcPCekOYnm3NOKw47ODZRR5UbjaRnxvGSebh5YOtG.qnrtHjq', 'registered', 0.00, '2025-03-04 02:25:07', '2025-09-09 10:50:14', NULL, NULL, NULL),
(2, 'Hannah Janee', 'hana@gmail.com', '09123456788', '$2y$12$2AxWt.SlFIv1kJD//LLFIO3oWFkAw.RTf/hPhUvsE20QtOeTnrPf2', 'registered', 0.00, '2025-03-09 19:18:29', '2025-11-15 08:55:31', NULL, NULL, NULL),
(3, 'Janjann', 'walkin_1743576722@example.com', '09123456786', '$2y$10$bR0BM64ltRskxCmGnSHbOuusiIQw9OS6j.r/e4ta.t0QrwRmPyImq', 'registered', 0.00, '2025-04-02 07:10:21', '2025-10-19 11:58:40', NULL, NULL, NULL),
(6, 'Vennies', 'walkin_1743577524@example.com', '09123456785', '$2y$10$vQjq7NlYyJ.tntUSP0mYD.2X6KPk3mwSVqp2C6TutF65bEy6mqnXi', 'walk-in', 0.00, '2025-04-02 07:10:21', '2025-04-04 04:05:14', NULL, NULL, NULL),
(7, 'Han', 'walkin_1743577581@example.com', '09123456784', '$2y$10$r.o2I.OOagCRnaGcZY1dye0HjrUM2DwRvYMKgJLzCz.VCAoF7AzMS', 'walk-in', 0.00, '2025-04-02 07:10:21', '2025-04-02 07:06:21', NULL, NULL, NULL),
(8, 'yeehaa', 'walkin_1744005996@example.com', '09123456783', '$2y$10$76OGOF7tOW.Z79/xdzj84Omx0XguMQg40Dayu7UcWYYFKS6frZE5a', 'walk-in', 0.00, '2025-04-07 06:06:36', '2025-04-07 06:06:36', NULL, NULL, NULL),
(9, 'Jane', 'walkin_1744089388@example.com', '09123456782', '$2y$10$wDJQX/KPLIOm191h0x99XOy3LCRRpjutU49gMWMA1yQTBZ2S6SDSm', 'walk-in', 0.00, '2025-04-08 05:16:28', '2025-04-08 05:16:28', NULL, NULL, NULL),
(13, 'van', 'walkin_1746787784@example.com', '0909123233', '$2y$10$xCxjyVYrKgUV6nTv/5RNqOqraxXwFs2c9gvXhLD.PqAWpnwwNzwFK', 'walk-in', 0.00, '2025-05-09 10:49:44', '2025-05-09 10:49:44', NULL, NULL, NULL),
(14, 'jon madrid', 'walkin_1746797875@example.com', '09090909009', '$2y$10$nOBAQ0CznoTMhQ.WpQ0JO.tdsLj1sRi/h9COw9Fz/YxcAaHWEtek2', 'walk-in', 0.00, '2025-05-09 13:37:55', '2025-05-09 13:37:55', NULL, NULL, NULL),
(16, 'lala', 'walkin_1746801817@example.com', '0987666789', '$2y$10$El30BI.LResRKWSBKdjNren/x1SHTmPCZfO30NyqeF.4TqRujLExe', 'walk-in', 0.00, '2025-05-09 14:43:37', '2025-05-09 14:43:37', NULL, NULL, NULL),
(20, 'Lester', 'lesterbeast177@gmail.com', '09123456781', NULL, 'registered', 0.00, '2025-06-10 10:52:03', '2025-10-12 04:21:13', 'f7bf296e387b1a88d14d2e396e0f934499827f27fd17cb31ac7cbc4f666cb0f1', '2025-06-11 10:52:03', NULL),
(22, 'Johnn', 'lester@example.com', '09123456123', '$2y$10$dp6JzWzNrP8dKq/FOtDXx.7MzONW/UZbzkb3ap9/Jkzgs901pjkmO', 'registered', 21.00, '2025-06-10 11:04:37', '2025-11-15 11:24:31', NULL, NULL, '2025-06-10 11:05:05'),
(24, 'Madrid', 'jonleemadd@gmail.com', '09123456132', NULL, 'registered', 14.00, '2025-06-11 03:05:29', '2025-10-12 04:22:06', '48ee53305949396914663b5a56a0cf5a2a7aaefd9332d30c966a88f159d1e9a5', '2025-06-12 03:05:29', NULL),
(25, 'Rapunzel', 'rapunzel@gmail.com', '09123456721', '$2y$10$HmBEFyvmTDnpx8H9a6JtFeAVAZeK2WIfTMrga8eYZs.bcwJtC59KC', 'registered', 0.00, '2025-08-10 10:31:35', '2025-08-10 10:31:35', NULL, NULL, NULL),
(27, 'John Listir', 'jonleemad17@gmail.com', '090909072', NULL, 'registered', 0.00, '2025-08-18 05:45:15', '2025-08-18 05:45:15', '273635da3b3e05afd7fca5669fe4bdb620ec84968c8b542170c50df8d013f00a', '2025-08-19 05:45:15', NULL),
(29, 'Haja Padrones', 'hajapadrones0.0@gmail.com', '090909051', '$2y$10$SHVK2.z9FoG/4gKn.0E1D.QN89wYEyOcqOzbWu9T5W6tFuifTPSSa', 'registered', 0.00, '2025-08-18 05:52:37', '2025-08-18 05:58:42', NULL, NULL, '2025-08-18 05:58:42'),
(30, 'Elaine', 'elaine@gmail.com', '09123456712', NULL, 'registered', 0.00, '2025-09-30 07:26:52', '2025-09-30 07:26:52', 'e9ce60f8513ea3c51eefc612e0b3f467034a8000ccf56643a690c7046c5a964a', '2025-10-01 07:26:52', NULL),
(33, 'dada', 'walkin_1763129925@example.com', '0987666782', '$2y$10$ju1NX487x4bpF0uWEsl9a.C5NVN07kFpx73NCHSm4sBgrmDhgoj/u', 'walk-in', 0.00, '2025-11-14 14:18:45', '2025-11-14 14:18:45', NULL, NULL, NULL),
(35, 'dadae', 'walkin_1763172866@example.com', 'Janjann', '$2y$10$DDNJA24FGf2CfiG9d/Y5beUAQ4xT43TmJcjLZwIfXdX7pgI0Pvnbe', 'walk-in', 0.00, '2025-11-15 02:14:26', '2025-11-15 02:14:26', NULL, NULL, NULL),
(38, 'adw', 'walkin_1763196769@example.com', '09123456787', '$2y$10$.CsOO6uwhayFDhBVAf2hwu2u6ZSp7kKfskli4BFWsfNYW/sEnGOo.', 'walk-in', 0.00, '2025-11-15 08:52:49', '2025-11-15 08:52:49', NULL, NULL, NULL),
(39, 'hana', 'walkin_1763197206@example.com', '09876667844', '$2y$10$yaeXxAXUvw1k36avn4AB4eKhi9oNUkLPBuGaDhto6cCbuuJd/yvzW', 'walk-in', 0.00, '2025-11-15 09:00:06', '2025-11-15 09:00:06', NULL, NULL, NULL),
(40, 'Morghan', 'lesterbeast17@gmail.com', '09454545454', NULL, 'registered', 0.00, '2025-11-15 16:13:39', '2025-11-15 16:13:39', 'bd29643706f1e6059da01e7fb9d432912c5c6145803655217810f56caeafb860', '2025-11-16 16:13:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `utility_bills`
--

CREATE TABLE `utility_bills` (
  `id` int NOT NULL,
  `type` enum('Electricity','Water','Maintenance') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `bill_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `utility_bills`
--

INSERT INTO `utility_bills` (`id`, `type`, `amount`, `bill_date`, `created_at`, `updated_at`, `description`) VALUES
(1, 'Electricity', 1500.00, '2025-06-08', '2025-06-08 07:36:55', '2025-06-08 07:36:55', NULL),
(4, 'Maintenance', 500.00, '2025-08-25', '2025-08-25 10:29:41', '2025-08-25 10:29:41', 'washing fix'),
(5, 'Electricity', 1500.00, '2025-08-26', '2025-08-26 08:25:12', '2025-08-26 08:25:12', ''),
(6, 'Water', 1000.00, '2025-08-26', '2025-08-26 08:25:39', '2025-08-26 08:25:39', ''),
(7, 'Maintenance', 500.00, '2025-08-26', '2025-08-26 08:26:16', '2025-08-26 08:26:16', 'washing fix'),
(8, 'Electricity', 1000.00, '2025-11-15', '2025-11-15 15:56:10', '2025-11-15 15:56:10', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`),
  ADD UNIQUE KEY `admins_contact_num_unique` (`contact_num`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_messages_room` (`room_id`),
  ADD KEY `idx_chat_messages_created` (`created_at`);

--
-- Indexes for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_rooms_customer` (`customer_id`),
  ADD KEY `idx_chat_rooms_active` (`is_active`);

--
-- Indexes for table `customer_tokens`
--
ALTER TABLE `customer_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `failed_logins`
--
ALTER TABLE `failed_logins`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_supply` (`product_id`);

--
-- Indexes for table `laundry_details`
--
ALTER TABLE `laundry_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laundry_details_laundry_list_id_foreign` (`laundry_list_id`),
  ADD KEY `detergent_product_id` (`detergent_product_id`),
  ADD KEY `fabcon_product_id` (`fabcon_product_id`),
  ADD KEY `bleach_product_id` (`bleach_product_id`);

--
-- Indexes for table `laundry_items`
--
ALTER TABLE `laundry_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laundry_list_id` (`laundry_list_id`);

--
-- Indexes for table `laundry_lists`
--
ALTER TABLE `laundry_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laundry_lists_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `laundry_prices`
--
ALTER TABLE `laundry_prices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prelist_details`
--
ALTER TABLE `prelist_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prelist_order_id` (`prelist_order_id`),
  ADD KEY `detergent_product_id` (`detergent_product_id`),
  ADD KEY `fabcon_product_id` (`fabcon_product_id`),
  ADD KEY `bleach_product_id` (`bleach_product_id`),
  ADD KEY `idx_is_whites_order` (`is_whites_order`);

--
-- Indexes for table `prelist_items`
--
ALTER TABLE `prelist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prelist_order_id` (`prelist_order_id`);

--
-- Indexes for table `prelist_orders`
--
ALTER TABLE `prelist_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `prelist_receipts`
--
ALTER TABLE `prelist_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prelist_order_id` (`prelist_order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `price_announcements`
--
ALTER TABLE `price_announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `laundry_list_id` (`laundry_list_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `receipt_sequences`
--
ALTER TABLE `receipt_sequences`
  ADD PRIMARY KEY (`date_key`),
  ADD KEY `idx_receipt_sequences_date` (`date_key`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_review` (`laundry_list_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `scheduled_price_changes`
--
ALTER TABLE `scheduled_price_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_effective_date` (`effective_date`,`is_applied`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `status_change_logs`
--
ALTER TABLE `status_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `supply_categories`
--
ALTER TABLE `supply_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supply_products`
--
ALTER TABLE `supply_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_supply_products_unit_price` (`unit_price`,`is_active`);

--
-- Indexes for table `supply_transactions`
--
ALTER TABLE `supply_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supply_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_contact_num_unique` (`contact_num`);

--
-- Indexes for table `utility_bills`
--
ALTER TABLE `utility_bills`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=831;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_tokens`
--
ALTER TABLE `customer_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `laundry_details`
--
ALTER TABLE `laundry_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=505;

--
-- AUTO_INCREMENT for table `laundry_items`
--
ALTER TABLE `laundry_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `laundry_lists`
--
ALTER TABLE `laundry_lists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=524;

--
-- AUTO_INCREMENT for table `laundry_prices`
--
ALTER TABLE `laundry_prices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `prelist_details`
--
ALTER TABLE `prelist_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `prelist_items`
--
ALTER TABLE `prelist_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `prelist_orders`
--
ALTER TABLE `prelist_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `prelist_receipts`
--
ALTER TABLE `prelist_receipts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `price_announcements`
--
ALTER TABLE `price_announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=361;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scheduled_price_changes`
--
ALTER TABLE `scheduled_price_changes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `status_change_logs`
--
ALTER TABLE `status_change_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supply_categories`
--
ALTER TABLE `supply_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supply_products`
--
ALTER TABLE `supply_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `supply_transactions`
--
ALTER TABLE `supply_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `utility_bills`
--
ALTER TABLE `utility_bills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `chat_rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD CONSTRAINT `chat_rooms_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_tokens`
--
ALTER TABLE `customer_tokens`
  ADD CONSTRAINT `customer_tokens_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_product` FOREIGN KEY (`product_id`) REFERENCES `supply_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laundry_details`
--
ALTER TABLE `laundry_details`
  ADD CONSTRAINT `laundry_details_ibfk_1` FOREIGN KEY (`detergent_product_id`) REFERENCES `supply_products` (`id`),
  ADD CONSTRAINT `laundry_details_ibfk_2` FOREIGN KEY (`fabcon_product_id`) REFERENCES `supply_products` (`id`),
  ADD CONSTRAINT `laundry_details_ibfk_3` FOREIGN KEY (`bleach_product_id`) REFERENCES `supply_products` (`id`),
  ADD CONSTRAINT `laundry_details_laundry_list_id_foreign` FOREIGN KEY (`laundry_list_id`) REFERENCES `laundry_lists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laundry_items`
--
ALTER TABLE `laundry_items`
  ADD CONSTRAINT `laundry_items_ibfk_1` FOREIGN KEY (`laundry_list_id`) REFERENCES `laundry_lists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laundry_lists`
--
ALTER TABLE `laundry_lists`
  ADD CONSTRAINT `laundry_lists_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prelist_details`
--
ALTER TABLE `prelist_details`
  ADD CONSTRAINT `prelist_details_ibfk_1` FOREIGN KEY (`prelist_order_id`) REFERENCES `prelist_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prelist_details_ibfk_2` FOREIGN KEY (`detergent_product_id`) REFERENCES `supply_products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prelist_details_ibfk_3` FOREIGN KEY (`fabcon_product_id`) REFERENCES `supply_products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prelist_details_ibfk_4` FOREIGN KEY (`bleach_product_id`) REFERENCES `supply_products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prelist_items`
--
ALTER TABLE `prelist_items`
  ADD CONSTRAINT `prelist_items_ibfk_1` FOREIGN KEY (`prelist_order_id`) REFERENCES `prelist_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prelist_orders`
--
ALTER TABLE `prelist_orders`
  ADD CONSTRAINT `prelist_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prelist_receipts`
--
ALTER TABLE `prelist_receipts`
  ADD CONSTRAINT `prelist_receipts_ibfk_1` FOREIGN KEY (`prelist_order_id`) REFERENCES `prelist_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prelist_receipts_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `price_announcements`
--
ALTER TABLE `price_announcements`
  ADD CONSTRAINT `price_announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`laundry_list_id`) REFERENCES `laundry_lists` (`id`),
  ADD CONSTRAINT `receipts_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`laundry_list_id`) REFERENCES `laundry_lists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scheduled_price_changes`
--
ALTER TABLE `scheduled_price_changes`
  ADD CONSTRAINT `scheduled_price_changes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `status_change_logs`
--
ALTER TABLE `status_change_logs`
  ADD CONSTRAINT `status_change_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `laundry_lists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supply_products`
--
ALTER TABLE `supply_products`
  ADD CONSTRAINT `supply_products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `supply_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supply_transactions`
--
ALTER TABLE `supply_transactions`
  ADD CONSTRAINT `supply_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `supply_products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `auto_mark_unclaimed_orders` ON SCHEDULE EVERY 1 DAY STARTS '2025-09-29 10:32:16' ON COMPLETION PRESERVE ENABLE DO BEGIN
    -- Update orders not claimed for 30 days to 'Unclaimed'
    UPDATE laundry_lists
    SET status = 'Unclaimed'
    WHERE status = 'Ready for Pickup'
      AND created_at < (NOW() - INTERVAL 30 DAY);

    -- Log the change into audit_logs
    INSERT INTO audit_logs (user_id, user_type, user_name, action, description, ip_address, user_agent, created_at, updated_at)
    VALUES (
        0,
        'system',
        'System',
        'auto_update_status',
        'Automatically updated old Ready for Pickup orders to Unclaimed',
        '127.0.0.1',
        'SYSTEM_EVENT',
        NOW(),
        NOW()
    );
END$$

CREATE DEFINER=`root`@`localhost` EVENT `apply_scheduled_price_changes` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-03 00:01:00' ON COMPLETION PRESERVE ENABLE COMMENT 'Applies scheduled price changes when effective date is reached' DO BEGIN
    -- all DECLAREs (and handlers/cursors) must appear before any executable statement
    DECLARE done INT DEFAULT 0;
    DECLARE v_id INT;
    DECLARE v_item_type VARCHAR(20);
    DECLARE v_item_identifier VARCHAR(255);
    DECLARE v_new_price DECIMAL(10,2);

    DECLARE price_cursor CURSOR FOR
        SELECT id, item_type, item_identifier, new_price
        FROM scheduled_price_changes
        WHERE effective_date = CURDATE() AND is_applied = 0;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- now executable statements
    SET @@session.time_zone = 'Asia/Manila';

    OPEN price_cursor;

    read_loop: LOOP
        FETCH price_cursor INTO v_id, v_item_type, v_item_identifier, v_new_price;
        IF done THEN
            LEAVE read_loop;
        END IF;

        IF v_item_type = 'service' THEN
            UPDATE laundry_prices
            SET price = v_new_price
            WHERE item_name = v_item_identifier;
        ELSEIF v_item_type = 'supply' THEN
            UPDATE supply_products
            SET unit_price = v_new_price, updated_at = NOW()
            WHERE id = CAST(v_item_identifier AS UNSIGNED);
        END IF;

        UPDATE scheduled_price_changes
        SET is_applied = 1, applied_at = NOW()
        WHERE id = v_id;
    END LOOP;

    CLOSE price_cursor;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
