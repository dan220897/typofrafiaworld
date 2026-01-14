-- Migration: Add Multi-Location Support
-- Date: 2026-01-14
-- Description: Adds locations table and updates schema for multi-location functionality

-- Create locations table
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL UNIQUE,
  `address` text,
  `phone` varchar(20),
  `email` varchar(255),
  `password_hash` varchar(255) NOT NULL COMMENT 'Password for location admin access',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add location_id to admins table
ALTER TABLE `admins`
ADD COLUMN `location_id` int(11) DEFAULT NULL COMMENT 'NULL for super_admin, specific location for location admins',
ADD KEY `idx_location_id` (`location_id`),
ADD CONSTRAINT `fk_admins_location` FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL;

-- Add location_id to orders table
ALTER TABLE `orders`
ADD COLUMN `location_id` int(11) DEFAULT NULL,
ADD KEY `idx_location_id` (`location_id`),
ADD CONSTRAINT `fk_orders_location` FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL;

-- Add location_id to services table (optional - if services are location-specific)
ALTER TABLE `services`
ADD COLUMN `location_id` int(11) DEFAULT NULL COMMENT 'NULL for global services, specific for location-only services',
ADD KEY `idx_location_id` (`location_id`),
ADD CONSTRAINT `fk_services_location` FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL;

-- Create location_services junction table for many-to-many relationship
CREATE TABLE IF NOT EXISTS `location_services` (
  `location_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `price_modifier` decimal(10,2) DEFAULT 0.00 COMMENT 'Additional price for this location',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`, `service_id`),
  KEY `idx_service_id` (`service_id`),
  CONSTRAINT `fk_location_services_location` FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_location_services_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update admin roles - add location_admin role
ALTER TABLE `admins`
MODIFY COLUMN `role` enum('super_admin','location_admin','manager','operator') NOT NULL DEFAULT 'operator';

-- Insert sample locations
INSERT INTO `locations` (`name`, `code`, `address`, `phone`, `email`, `password_hash`, `is_active`) VALUES
('Центральный офис', 'central', 'г. Москва, ул. Примерная, д. 1', '+7 (495) 123-45-67', 'central@typografia.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Филиал на Арбате', 'arbat', 'г. Москва, Арбат, д. 10', '+7 (495) 234-56-78', 'arbat@typografia.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Филиал в ТЦ Мега', 'mega', 'г. Москва, ТЦ Мега Белая Дача', '+7 (495) 345-67-89', 'mega@typografia.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Note: Default password for all locations is 'password' (change after first login)

-- Add admin logs for location changes
ALTER TABLE `admin_logs`
ADD COLUMN `location_id` int(11) DEFAULT NULL,
ADD KEY `idx_location_id` (`location_id`);

-- Create index on orders for better performance
CREATE INDEX `idx_orders_location_status` ON `orders` (`location_id`, `status`);
CREATE INDEX `idx_orders_location_created` ON `orders` (`location_id`, `created_at`);

COMMIT;
