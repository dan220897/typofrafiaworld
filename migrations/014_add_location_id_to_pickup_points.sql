-- Migration: Add location_id to pickup_points
-- Date: 2026-01-24
-- Description: Links pickup points to locations for proper order routing

-- Add location_id column to pickup_points
ALTER TABLE `pickup_points`
ADD COLUMN `location_id` int(11) DEFAULT NULL COMMENT 'ID локации/филиала' AFTER `sort_order`,
ADD KEY `idx_location_id` (`location_id`),
ADD CONSTRAINT `fk_pickup_points_location` FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL;

-- Add pickup_point_id column to orders (check if exists first)
SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'pickup_point_id');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `orders` ADD COLUMN `pickup_point_id` int(11) DEFAULT NULL COMMENT ''ID точки самовывоза'' AFTER `delivery_address`',
    'SELECT ''Column pickup_point_id already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index and foreign key for pickup_point_id
SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND INDEX_NAME = 'idx_pickup_point_id');

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE `orders` ADD KEY `idx_pickup_point_id` (`pickup_point_id`)',
    'SELECT ''Index idx_pickup_point_id already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'fk_orders_pickup_point');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_pickup_point` FOREIGN KEY (`pickup_point_id`) REFERENCES `pickup_points`(`id`) ON DELETE SET NULL',
    'SELECT ''Foreign key fk_orders_pickup_point already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing pickup points to link to locations
-- This assumes pickup points and locations have matching addresses or names
-- Adjust the UPDATE statements based on your actual data mapping
UPDATE `pickup_points` pp
INNER JOIN `locations` l ON pp.name LIKE CONCAT('%', l.name, '%')
SET pp.location_id = l.id
WHERE pp.location_id IS NULL;

COMMIT;
