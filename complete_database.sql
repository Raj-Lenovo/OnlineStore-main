-- =====================================================
-- COMPLETE ONLINE COMPUTER STORE DATABASE
-- =====================================================
-- This file contains EVERYTHING needed for the store
-- Run this ONE file to set up the entire database
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS online_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_store;

-- =====================================================
-- TABLES
-- =====================================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category_id INT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cart table (session-based cart)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews table (for product reviews and ratings)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_product_rating ON reviews(product_id, rating);
CREATE INDEX idx_user_reviews ON reviews(user_id);

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert default admin user
-- Default credentials: username='admin', password='admin123'
-- IMPORTANT: Change the password after first login!
-- Password hash generated with: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Laptops', 'High-performance laptops for work and gaming'),
('Desktops', 'Powerful desktop computers'),
('Monitors', 'High-quality displays'),
('Keyboards', 'Mechanical and membrane keyboards'),
('Mice', 'Gaming and office mice'),
('Accessories', 'Computer accessories and peripherals')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample products
INSERT INTO products (name, description, price, stock, category_id, image) VALUES
('Gaming Laptop Pro', 'High-performance gaming laptop with RTX 4060, 16GB RAM, 512GB SSD. Perfect for gaming and content creation.', 1299.99, 15, 1, 'laptop1.jpg'),
('Business Laptop', 'Lightweight laptop for professionals. 14-inch display, long battery life, perfect for business travel.', 899.99, 20, 1, 'laptop2.jpg'),
('Gaming Desktop', 'Powerful desktop with Intel i7 processor and RTX 4070 graphics card. Ready for 4K gaming.', 1599.99, 10, 2, 'desktop1.jpg'),
('Office Desktop', 'Affordable desktop for office work. Includes keyboard and mouse. Great for small businesses.', 599.99, 25, 2, 'desktop2.jpg'),
('4K Monitor 27"', 'Ultra HD 4K monitor with HDR support. Perfect for photo editing, video production, and entertainment.', 399.99, 30, 3, 'monitor1.jpg'),
('Gaming Monitor 24"', '144Hz gaming monitor with 1ms response time. Smooth gameplay for competitive gaming.', 249.99, 35, 3, 'monitor2.jpg'),
('Mechanical Keyboard', 'RGB mechanical keyboard with customizable lighting. Tactile switches for satisfying typing experience.', 129.99, 40, 4, 'keyboard1.jpg'),
('Wireless Mouse', 'Ergonomic wireless mouse with long battery life. Comfortable for extended use.', 49.99, 50, 5, 'mouse1.jpg'),
('Gaming Mouse', 'High-precision gaming mouse with adjustable DPI. Perfect for FPS and competitive gaming.', 79.99, 45, 5, 'mouse2.jpg'),
('USB-C Hub', 'Multi-port USB-C hub with HDMI, USB 3.0, and card reader. Expand your laptop connectivity.', 39.99, 60, 6, 'hub1.jpg')
ON DUPLICATE KEY UPDATE name=name;

-- Insert test users for reviews (password for all: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'user'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'user'),
('mike_wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Wilson', 'user'),
('sarah_jones', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Jones', 'user'),
('david_brown', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Brown', 'user')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample reviews
-- Products 1, 2, 3, 5, 7, 9 have reviews
-- Products 4, 6, 8, 10 are left without reviews for users to review first

-- Reviews for Gaming Laptop Pro (Product ID 1)
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 1, id, 5, 'Excellent gaming laptop! The RTX 4060 handles all my games smoothly. Great build quality and the display is amazing. Highly recommend for gamers!', DATE_SUB(NOW(), INTERVAL 15 DAY)
FROM users WHERE username = 'john_doe' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 1, id, 4, 'Really good laptop for the price. Performance is solid, though the battery life could be better. Overall satisfied with my purchase.', DATE_SUB(NOW(), INTERVAL 8 DAY)
FROM users WHERE username = 'jane_smith' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

-- Reviews for Business Laptop (Product ID 2)
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 2, id, 5, 'Perfect for work! Lightweight and fast. Battery lasts all day. The keyboard is comfortable for long typing sessions. Great value!', DATE_SUB(NOW(), INTERVAL 12 DAY)
FROM users WHERE username = 'mike_wilson' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 2, id, 4, 'Good laptop for business use. Lightweight and portable. Screen could be brighter but overall very satisfied.', DATE_SUB(NOW(), INTERVAL 5 DAY)
FROM users WHERE username = 'sarah_jones' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 2, id, 3, 'Decent laptop but nothing special. Does the job for basic tasks. Wish it had more storage space.', DATE_SUB(NOW(), INTERVAL 3 DAY)
FROM users WHERE username = 'david_brown' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

-- Reviews for Gaming Desktop (Product ID 3)
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 3, id, 5, 'Amazing desktop! The RTX 4070 is a beast. Handles 4K gaming without any issues. Very happy with this purchase!', DATE_SUB(NOW(), INTERVAL 20 DAY)
FROM users WHERE username = 'john_doe' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 3, id, 5, 'Best desktop I''ve ever owned. Fast, powerful, and runs everything smoothly. Worth every penny!', DATE_SUB(NOW(), INTERVAL 10 DAY)
FROM users WHERE username = 'jane_smith' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 3, id, 4, 'Great performance for gaming and video editing. Only minor complaint is the case could be quieter, but overall excellent.', DATE_SUB(NOW(), INTERVAL 6 DAY)
FROM users WHERE username = 'mike_wilson' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

-- Reviews for 4K Monitor 27" (Product ID 5)
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 5, id, 5, 'Stunning display! The 4K resolution is crystal clear and HDR makes everything look amazing. Perfect for photo editing and watching movies.', DATE_SUB(NOW(), INTERVAL 18 DAY)
FROM users WHERE username = 'sarah_jones' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 5, id, 4, 'Very good monitor with excellent color accuracy. The 27" size is perfect for my desk. Only wish it had USB-C input.', DATE_SUB(NOW(), INTERVAL 9 DAY)
FROM users WHERE username = 'david_brown' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 5, id, 5, 'Love this monitor! The picture quality is outstanding. Great for both work and entertainment. Highly recommend!', DATE_SUB(NOW(), INTERVAL 4 DAY)
FROM users WHERE username = 'john_doe' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

-- Reviews for Mechanical Keyboard (Product ID 7)
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 7, id, 5, 'Best keyboard I''ve used! The RGB lighting is beautiful and the mechanical switches feel amazing. Typing is so satisfying now.', DATE_SUB(NOW(), INTERVAL 14 DAY)
FROM users WHERE username = 'jane_smith' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 7, id, 4, 'Great keyboard with nice tactile feedback. RGB effects are cool. A bit loud for office use but perfect for home gaming setup.', DATE_SUB(NOW(), INTERVAL 7 DAY)
FROM users WHERE username = 'mike_wilson' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 7, id, 3, 'Good keyboard but the keys are a bit too sensitive for my liking. RGB is nice though. Decent for the price.', DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM users WHERE username = 'sarah_jones' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

-- Reviews for Gaming Mouse (Product ID 9)
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 9, id, 5, 'Excellent gaming mouse! Very precise and comfortable. The DPI settings are perfect for FPS games. Great build quality.', DATE_SUB(NOW(), INTERVAL 16 DAY)
FROM users WHERE username = 'david_brown' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 9, id, 4, 'Really good mouse for gaming. Accurate sensor and comfortable grip. Software could be better but overall satisfied.', DATE_SUB(NOW(), INTERVAL 11 DAY)
FROM users WHERE username = 'john_doe' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
SELECT 9, id, 5, 'Perfect mouse for competitive gaming. The precision is amazing and it feels great in hand. Best mouse I''ve owned!', DATE_SUB(NOW(), INTERVAL 1 DAY)
FROM users WHERE username = 'jane_smith' LIMIT 1
ON DUPLICATE KEY UPDATE comment = comment;

-- =====================================================
-- SETUP COMPLETE!
-- =====================================================
-- 
-- Default Admin Credentials:
-- Username: admin
-- Password: admin123
-- 
-- Test User Credentials (for testing):
-- All test users have password: admin123
-- - john_doe, jane_smith, mike_wilson, sarah_jones, david_brown
-- 
-- Products with Reviews:
-- - Gaming Laptop Pro (ID: 1) - 2 reviews
-- - Business Laptop (ID: 2) - 3 reviews
-- - Gaming Desktop (ID: 3) - 3 reviews
-- - 4K Monitor 27" (ID: 5) - 3 reviews
-- - Mechanical Keyboard (ID: 7) - 3 reviews
-- - Gaming Mouse (ID: 9) - 3 reviews
-- 
-- Products without Reviews (users can be first to review):
-- - Office Desktop (ID: 4)
-- - Gaming Monitor 24" (ID: 6)
-- - Wireless Mouse (ID: 8)
-- - USB-C Hub (ID: 10)
-- 
-- IMPORTANT: Change admin password after first login!
-- =====================================================

