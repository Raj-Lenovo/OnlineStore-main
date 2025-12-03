-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 03:06 AM
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
-- Database: `online_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Laptops', 'High-performance laptops for work and gaming', '2025-11-26 23:05:07'),
(2, 'Desktops', 'Powerful desktop computers', '2025-11-26 23:05:07'),
(3, 'Monitors', 'High-quality displays', '2025-11-26 23:05:07'),
(4, 'Keyboards', 'Mechanical and membrane keyboards', '2025-11-26 23:05:07'),
(5, 'Mice', 'Gaming and office mice', '2025-11-26 23:05:07'),
(6, 'Accessories', 'Computer accessories and peripherals', '2025-11-26 23:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'card',
  `payment_status` varchar(50) NOT NULL DEFAULT 'paid',
  `card_last4` char(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `full_name`, `total_amount`, `status`, `shipping_address`, `phone`, `payment_method`, `payment_status`, `card_last4`, `created_at`) VALUES
(1, 7, '', 1299.99, 'pending', '25 conover', '33145035', 'card', 'paid', NULL, '2025-11-27 01:11:14'),
(2, 7, '', 899.99, 'pending', '34', '23', 'card', 'paid', NULL, '2025-11-27 01:12:02'),
(3, 7, '', 399.99, 'pending', 'ss', 'ss', 'card', 'paid', NULL, '2025-11-27 01:12:46'),
(4, 7, '', 899.99, 'pending', '5165', '51', 'card', 'paid', NULL, '2025-11-27 02:04:54');

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

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 1299.99),
(2, 2, 2, 1, 899.99),
(3, 3, 5, 1, 399.99),
(4, 4, 2, 1, 899.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Gaming Laptop Pro', 'High-performance gaming laptop with RTX 4060, 16GB RAM, 512GB SSD. Perfect for gaming and content creation.', 1299.99, 14, 1, 'laptop1.jpg', '2025-11-26 23:05:07', '2025-11-27 01:11:14'),
(2, 'Business Laptop', 'Lightweight laptop for professionals. 14-inch display, long battery life, perfect for business travel.', 899.99, 18, 1, 'laptop2.jpg', '2025-11-26 23:05:07', '2025-11-27 02:04:54'),
(3, 'Gaming Desktop', 'Powerful desktop with Intel i7 processor and RTX 4070 graphics card. Ready for 4K gaming.', 1599.99, 10, 2, 'desktop1.jpg', '2025-11-26 23:05:07', '2025-11-26 23:05:07'),
(4, 'Office Desktop', 'Affordable desktop for office work. Includes keyboard and mouse. Great for small businesses.', 599.99, 25, 2, 'desktop2.jpg', '2025-11-26 23:05:07', '2025-11-26 23:05:07'),
(5, '4K Monitor 27\"', 'Ultra HD 4K monitor with HDR support. Perfect for photo editing, video production, and entertainment.', 399.99, 29, 3, 'monitor1.jpg', '2025-11-26 23:05:07', '2025-11-27 01:12:46'),
(6, 'Gaming Monitor 24\"', '144Hz gaming monitor with 1ms response time. Smooth gameplay for competitive gaming.', 249.99, 35, 3, 'monitor2.jpg', '2025-11-26 23:05:07', '2025-11-26 23:05:07'),
(7, 'Mechanical Keyboard', 'RGB mechanical keyboard with customizable lighting. Tactile switches for satisfying typing experience.', 129.99, 40, 4, 'keyboard1.jpg', '2025-11-26 23:05:07', '2025-11-26 23:05:07'),
(8, 'Wireless Mouse', 'Ergonomic wireless mouse with long battery life. Comfortable for extended use.', 49.99, 50, 5, 'mouse1.jpg', '2025-11-26 23:05:07', '2025-11-26 23:05:07'),
(9, 'Gaming Mouse', 'High-precision gaming mouse with adjustable DPI. Perfect for FPS and competitive gaming.', 79.99, 45, 5, 'mouse2.jpg', '2025-11-26 23:05:07', '2025-11-26 23:05:07'),
(10, 'USB-C Hub', 'Multi-port USB-C hub with HDMI, USB 3.0, and card reader. Expand your laptop connectivity.', 39.99, 60, 6, 'hub1.jpg', '2025-11-26 23:05:07', '2025-11-26 23:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 5, 'Excellent gaming laptop! The RTX 4060 handles all my games smoothly. Great build quality and the display is amazing. Highly recommend for gamers!', '2025-11-11 23:05:07'),
(2, 1, 3, 4, 'Really good laptop for the price. Performance is solid, though the battery life could be better. Overall satisfied with my purchase.', '2025-11-18 23:05:07'),
(3, 2, 4, 5, 'Perfect for work! Lightweight and fast. Battery lasts all day. The keyboard is comfortable for long typing sessions. Great value!', '2025-11-14 23:05:07'),
(4, 2, 5, 4, 'Good laptop for business use. Lightweight and portable. Screen could be brighter but overall very satisfied.', '2025-11-21 23:05:07'),
(5, 2, 6, 3, 'Decent laptop but nothing special. Does the job for basic tasks. Wish it had more storage space.', '2025-11-23 23:05:07'),
(6, 3, 2, 5, 'Amazing desktop! The RTX 4070 is a beast. Handles 4K gaming without any issues. Very happy with this purchase!', '2025-11-06 23:05:07'),
(7, 3, 3, 5, 'Best desktop I\'ve ever owned. Fast, powerful, and runs everything smoothly. Worth every penny!', '2025-11-16 23:05:07'),
(8, 3, 4, 4, 'Great performance for gaming and video editing. Only minor complaint is the case could be quieter, but overall excellent.', '2025-11-20 23:05:07'),
(9, 5, 5, 5, 'Stunning display! The 4K resolution is crystal clear and HDR makes everything look amazing. Perfect for photo editing and watching movies.', '2025-11-08 23:05:07'),
(10, 5, 6, 4, 'Very good monitor with excellent color accuracy. The 27\" size is perfect for my desk. Only wish it had USB-C input.', '2025-11-17 23:05:07'),
(11, 5, 2, 5, 'Love this monitor! The picture quality is outstanding. Great for both work and entertainment. Highly recommend!', '2025-11-22 23:05:07'),
(12, 7, 3, 5, 'Best keyboard I\'ve used! The RGB lighting is beautiful and the mechanical switches feel amazing. Typing is so satisfying now.', '2025-11-12 23:05:07'),
(13, 7, 4, 4, 'Great keyboard with nice tactile feedback. RGB effects are cool. A bit loud for office use but perfect for home gaming setup.', '2025-11-19 23:05:08'),
(14, 7, 5, 3, 'Good keyboard but the keys are a bit too sensitive for my liking. RGB is nice though. Decent for the price.', '2025-11-24 23:05:08'),
(15, 9, 6, 5, 'Excellent gaming mouse! Very precise and comfortable. The DPI settings are perfect for FPS games. Great build quality.', '2025-11-10 23:05:08'),
(16, 9, 2, 4, 'Really good mouse for gaming. Accurate sensor and comfortable grip. Software could be better but overall satisfied.', '2025-11-15 23:05:08'),
(17, 9, 3, 5, 'Perfect mouse for competitive gaming. The precision is amazing and it feels great in hand. Best mouse I\'ve owned!', '2025-11-25 23:05:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `address`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@store.com', '$2y$10$wKgPNhuKLpAmTORQA/IFlu.cxFfu6WlV6NKqrPkD//no9oHgtbf86', 'Administrator', NULL, NULL, 'admin', '2025-11-26 23:05:07'),
(2, 'john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', NULL, NULL, 'user', '2025-11-26 23:05:07'),
(3, 'jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', NULL, NULL, 'user', '2025-11-26 23:05:07'),
(4, 'mike_wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Wilson', NULL, NULL, 'user', '2025-11-26 23:05:07'),
(5, 'sarah_jones', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Jones', NULL, NULL, 'user', '2025-11-26 23:05:07'),
(6, 'david_brown', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Brown', NULL, NULL, 'user', '2025-11-26 23:05:07'),
(7, 'mdt', 'm@gmail.com', '$2y$10$xMyjHMWXkbSKmf5yVGGDcO.brDOLFrtllvYQSV3Myv3Bdi3ENL38a', 'mdt', '', '', 'user', '2025-11-26 23:46:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

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
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product_review` (`user_id`,`product_id`),
  ADD KEY `idx_product_rating` (`product_id`,`rating`),
  ADD KEY `idx_user_reviews` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
