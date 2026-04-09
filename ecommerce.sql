-- ============================================================
--  ecommerce.sql
--  Import this into phpMyAdmin or run:
--    mysql -u root -p < ecommerce.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ecommerce_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ecommerce_db`;

-- ─────────────────────────────────────────────
-- 1. USERS
--    role: 'admin' or 'customer'
-- ─────────────────────────────────────────────
CREATE TABLE `users` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(255)    NOT NULL,
  `email`          VARCHAR(255)    NOT NULL,
  `password`       VARCHAR(255)    NOT NULL,
  `role`           ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  `phone`          VARCHAR(20)     DEFAULT NULL,
  `address`        TEXT            DEFAULT NULL,
  `remember_token` VARCHAR(100)    DEFAULT NULL,
  `created_at`     TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 2. CATEGORIES
-- ─────────────────────────────────────────────
CREATE TABLE `categories` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 3. PRODUCTS
-- ─────────────────────────────────────────────
CREATE TABLE `products` (
  `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED  DEFAULT NULL,
  `name`        VARCHAR(255)     NOT NULL,
  `description` TEXT             DEFAULT NULL,
  `price`       DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
  `stock`       INT              NOT NULL DEFAULT 0,
  `image`       VARCHAR(255)     DEFAULT NULL,
  `created_at`  TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 4. ORDERS
--    delivery_type : 'takeaway' | 'delivery'
--    status        : 'wait' → 'processing' → 'ready' → 'done'
-- ─────────────────────────────────────────────
CREATE TABLE `orders` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `order_code`    VARCHAR(50)     NOT NULL,
  `delivery_type` ENUM('takeaway','delivery') NOT NULL DEFAULT 'takeaway',
  `address`       TEXT            DEFAULT NULL,
  `status`        ENUM('wait','processing','ready','done') NOT NULL DEFAULT 'wait',
  `total`         DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `notes`         TEXT            DEFAULT NULL,
  `created_at`    TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_code_unique` (`order_code`),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 5. ORDER ITEMS
--    price    = price at the time of purchase (snapshot)
--    subtotal = price * quantity
-- ─────────────────────────────────────────────
CREATE TABLE `order_items` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`   BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity`   INT             NOT NULL DEFAULT 1,
  `price`      DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `subtotal`   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SEED DATA
--  Admin password  : password
--  Customer password: password
--  (Both use Laravel's default bcrypt hash for "password")
-- ============================================================

INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
('Admin Shop',    'admin@shop.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',    NOW(), NOW()),
('John Customer', 'customer@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NOW(), NOW());

INSERT INTO `categories` (`name`, `created_at`, `updated_at`) VALUES
('Food',      NOW(), NOW()),
('Beverages', NOW(), NOW()),
('Snacks',    NOW(), NOW());

INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'Nasi Goreng',       'Indonesian fried rice with egg',       25000.00, 50, NOW(), NOW()),
(1, 'Mie Goreng',        'Indonesian fried noodles',             22000.00, 50, NOW(), NOW()),
(1, 'Ayam Bakar',        'Grilled chicken with special sauce',   35000.00, 30, NOW(), NOW()),
(2, 'Es Teh Manis',      'Sweet iced tea',                        5000.00, 100,NOW(), NOW()),
(2, 'Jus Alpukat',       'Fresh avocado juice',                  15000.00, 40, NOW(), NOW()),
(2, 'Kopi Susu',         'Milk coffee',                          18000.00, 60, NOW(), NOW()),
(3, 'Keripik Singkong',  'Cassava chips',                         8000.00, 80, NOW(), NOW()),
(3, 'Pisang Goreng',     'Fried banana',                         10000.00, 45, NOW(), NOW());

-- ============================================================
--  USEFUL QUERIES FOR ADMIN REPORT (reference)
-- ============================================================

-- Daily order report query:
-- SELECT
--   DATE(o.created_at)          AS order_date,
--   COUNT(o.id)                 AS total_orders,
--   SUM(o.total)                AS total_revenue,
--   SUM(CASE WHEN o.status='done' THEN 1 ELSE 0 END) AS completed,
--   SUM(CASE WHEN o.delivery_type='delivery' THEN 1 ELSE 0 END) AS delivery_count,
--   SUM(CASE WHEN o.delivery_type='takeaway' THEN 1 ELSE 0 END) AS takeaway_count
-- FROM orders o
-- WHERE DATE(o.created_at) = CURDATE()
-- GROUP BY DATE(o.created_at);
