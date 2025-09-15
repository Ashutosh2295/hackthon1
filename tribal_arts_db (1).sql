-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2025 at 03:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tribal_arts_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `activity_type`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'update_user_role', 'Updated user ID 5 role to supplier', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-14 21:36:06'),
(2, 1, 'update_user_role', 'Updated user ID 6 role to supplier', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-15 01:05:47'),
(3, 1, 'update_user_role', 'Updated user ID 2 role to customer', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-15 01:05:54');

-- --------------------------------------------------------

--
-- Table structure for table `admin1`
--

CREATE TABLE `admin1` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin1`
--

INSERT INTO `admin1` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$LY5u5CTGxVhjDqqsBalfYOvUKsTzC/zQ12Ee.PB.hlgNxK0AhCizW', '2025-09-13 10:17:06'),
(2, 'Kuldeepsinh', '$2b$10$OFn4E3vu3UidrWpznXaEYenMDiYny7MFvQxM6t6bF.0.62iO1ewe6', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`) VALUES
(1, 'Pottery &amp; Ceramics', 'Traditional clay pottery and ceramic art pieces', NULL, '2025-09-13 10:41:06'),
(2, 'Textiles & Weavings', 'Handwoven fabrics, rugs, and textile art', NULL, '2025-09-13 10:41:06'),
(3, 'Jewelry & Accessories', 'Traditional ornaments and decorative accessories', NULL, '2025-09-13 10:41:06'),
(4, 'Wood Crafts', 'Carved wooden sculptures and functional items', NULL, '2025-09-13 10:41:06'),
(5, 'Metalwork', 'Forged and crafted metal art pieces', NULL, '2025-09-13 10:41:06'),
(12, 'Pottery & Ceramics', 'Traditional clay pottery and ceramic art pieces', NULL, '2025-09-14 05:41:58'),
(13, 'Stone Carvings', 'Sculpted stone and rock art', NULL, '2025-09-14 05:41:58'),
(14, 'bamboo', 'bamboo wood art', NULL, '2025-09-14 07:02:03'),
(15, 'Traditional Pottery', 'Handcrafted ceramic vessels and sculptures', NULL, '2025-09-14 09:29:55'),
(16, 'Textiles & Weavings', 'Traditional fabrics, rugs, and woven items', NULL, '2025-09-14 09:29:55'),
(17, 'Jewelry & Ornaments', 'Handmade jewelry and decorative pieces', NULL, '2025-09-14 09:29:55'),
(18, 'Wood Carvings', 'Carved wooden sculptures and functional items', NULL, '2025-09-14 09:29:55'),
(19, 'Paintings & Art', 'Traditional and contemporary tribal artwork', NULL, '2025-09-14 09:29:55'),
(20, 'Traditional Pottery', 'Handcrafted ceramic vessels and sculptures', NULL, '2025-09-14 09:49:38'),
(21, 'Textiles & Weavings', 'Traditional fabrics, rugs, and woven items', NULL, '2025-09-14 09:49:38'),
(22, 'Jewelry & Ornaments', 'Handmade jewelry and decorative pieces', NULL, '2025-09-14 09:49:38'),
(23, 'Wood Carvings', 'Carved wooden sculptures and functional items', NULL, '2025-09-14 09:49:38'),
(24, 'Paintings & Art', 'Traditional and contemporary tribal artwork', NULL, '2025-09-14 09:49:38');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders1`
--

CREATE TABLE `orders1` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders1`
--

INSERT INTO `orders1` (`id`, `user_id`, `total_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, NULL, 125.50, 'pending', 'Sample guest order - Pottery and textile items', '2025-09-13 11:04:54', '2025-09-13 11:04:54'),
(2, 1, 2000.00, 'processing', 'bnm', '2025-09-13 11:05:11', '2025-09-13 11:05:11'),
(3, 1, 2000.00, 'processing', 'tfgyhjkl', '2025-09-13 11:14:59', '2025-09-13 11:14:59'),
(4, NULL, 125.50, 'pending', 'Sample guest order - Pottery and textile items', '2025-09-13 14:31:43', '2025-09-13 14:31:43'),
(5, NULL, 125.50, 'pending', 'Sample guest order - Pottery and textile items', '2025-09-14 06:56:51', '2025-09-14 06:56:51'),
(6, NULL, 125.50, 'pending', 'Sample guest order - Pottery and textile items', '2025-09-14 06:57:01', '2025-09-14 06:57:01'),
(7, 1, 200.00, 'processing', '', '2025-09-14 06:58:12', '2025-09-14 06:58:12');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `supplier_id`, `image`, `stock_quantity`, `status`, `created_at`, `category`) VALUES
(23, 'Warle art', 'Warli art is a stunning example of tribal minimalism—rich in symbolism, yet crafted with the simplest of shapes. 🌀🔺◼️ It originates from the Warli tribe of Maharashtra, India, and dates back thousands of years, possibly as early as 3000 BCE. Traditionally painted on mud walls using rice paste, Warli art uses circles (sun/moon), triangles (mountains/trees), and squares (sacred enclosures) to depict scenes of village life, rituals, farming, and festivals.\r\n', 4000.00, 1, 1, 'uploads/IMG-20250914-WA0008.jpg', 2, 'active', '2025-09-14 21:38:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `business_registration` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `status` enum('active','pending','suspended') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers1`
--

CREATE TABLE `suppliers1` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `tribe` varchar(100) DEFAULT NULL,
  `specialty` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers1`
--

INSERT INTO `suppliers1` (`id`, `name`, `tribe`, `specialty`, `email`, `phone`, `address`, `image`, `bio`, `status`, `created_at`, `password`) VALUES
(1, 'Maya Patel', 'Cherokee', 'Traditional pottery and ceramic art', 'maya@example.com', '+1-555-0101', NULL, NULL, NULL, 'active', '2025-09-13 10:41:06', '$2b$10$IBxa0zF1jhFbwwF01jlZv.3GOINNiippZtaqC7qUV5nkjr16IO1/K'),
(19, 'Maria Gonzales', 'Hopi Tribe', 'Traditional pottery and ceramics', 'maria@email.com', '555-0101', '123 Reservation Rd, Arizona', NULL, NULL, 'active', '2025-09-14 09:49:38', '123456'),
(20, 'Joseph Running Bear', 'Lakota Sioux', 'Beadwork and leather crafts', 'joseph@email.com', '555-0102', '456 Tribal Ave, South Dakota', NULL, NULL, 'active', '2025-09-14 09:49:38', NULL),
(21, 'Sarah Featherstone', 'Cherokee Nation', 'Basket weaving and textiles', 'sarah@email.com', '555-0103', '789 Heritage St, Oklahoma', NULL, NULL, 'active', '2025-09-14 09:49:38', NULL),
(22, 'Robert Stonecrow', 'Navajo Nation', 'Silver jewelry and turquoise work', 'robert@email.com', '555-0104', '321 Canyon Rd, New Mexico', NULL, NULL, 'active', '2025-09-14 09:49:38', NULL),
(23, 'Elena Windwalker', 'Pueblo', 'Traditional paintings and art', 'elena@email.com', '555-0105', '654 Mesa Dr, New Mexico', NULL, NULL, 'active', '2025-09-14 09:49:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_temp_credentials`
--

CREATE TABLE `supplier_temp_credentials` (
  `supplier_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `plain_password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user1`
--

CREATE TABLE `user1` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user1`
--

INSERT INTO `user1` (`id`, `name`, `email`, `password`, `phone`, `address`, `status`, `created_at`) VALUES
(1, 'vraj123', 'vraj123@gmail.com', '$2y$10$oKGU72Kj9grgV8mE2FadkOjduQWarys51pUg27D9RQBwPhiwXFLOK', '07990206073', 'sabarkatha', 'active', '2025-09-13 10:50:48'),
(2, 'John Smith', 'john.smith@email.com', NULL, '555-1001', '123 Main St, Denver, CO', 'active', '2025-09-13 11:09:35'),
(3, 'Lisa Johnson', 'lisa.j@email.com', NULL, '555-1002', '456 Oak Ave, Seattle, WA', 'active', '2025-09-13 11:09:35'),
(4, 'Michael Brown', 'mike.brown@email.com', NULL, '555-1003', '789 Pine St, Portland, OR', 'active', '2025-09-13 11:09:35'),
(5, 'Sarah Davis', 'sarah.d@email.com', NULL, '555-1004', '321 Elm Rd, Austin, TX', 'active', '2025-09-13 11:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','supplier','customer') DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'Kuldeep', 'kuldeepsinhzala261@gmail.com', '$2y$10$IF4qjh6B2WRnCgeDG/Kon.nLCAH/dvC5507TeQBVstQywRS9DMalq', '0000-00-00 00:00:00', 'admin'),
(2, 'Ashutosh', 'ashu123@gmail.com', '$2y$10$48SoMKVUzH1TrFgygqIyeO3u99fLqoJ/nu2pAjTgiGm6/SoHyrUOy', '2025-09-14 19:51:54', 'customer'),
(3, 'Admin User', 'admin@artisanark.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-09-14 20:33:27', 'admin'),
(4, 'vraj123', 'vraj123@gmail.com', '$2y$10$adsTgQ7G1CExzykChH4fB.J6i1IGV9.2RCzSd/NJ5IcYVu6awgMpC', '2025-09-14 20:47:06', 'customer'),
(5, 'Suchi', 'suchi123@gmail.com', '$2y$10$EyrYIe8ikVac5BWZACH8W.Zcyfqs2eeOV6WIJ3BYHJhHZfUxBoV1O', '2025-09-14 21:35:27', 'supplier'),
(6, 'Kevin', 'kevin123@gmail.com', '$2y$10$L8hxhK9JK0ioja4B6JDsRuO52QvNj/i1OE3yqjuz8MI4q8Op8t61O', '2025-09-15 01:05:07', 'supplier');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin1`
--
ALTER TABLE `admin1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders1`
--
ALTER TABLE `orders1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `suppliers1`
--
ALTER TABLE `suppliers1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_temp_credentials`
--
ALTER TABLE `supplier_temp_credentials`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `user1`
--
ALTER TABLE `user1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin1`
--
ALTER TABLE `admin1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders1`
--
ALTER TABLE `orders1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers1`
--
ALTER TABLE `suppliers1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user1`
--
ALTER TABLE `user1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers1` (`id`);

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
