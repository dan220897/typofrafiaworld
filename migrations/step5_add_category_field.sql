-- ============================================
-- ШАГ 5: Добавление поля category в таблицу services
-- ============================================

-- Добавляем поле category (если нет)
SET @column_exists = (SELECT COUNT(*)
                      FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'services'
                        AND COLUMN_NAME = 'category');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `services` ADD COLUMN `category` VARCHAR(100) DEFAULT NULL AFTER `icon`',
    'SELECT "Column category already exists" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Добавляем поле description (если нет)
SET @column_exists = (SELECT COUNT(*)
                      FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'services'
                        AND COLUMN_NAME = 'description');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `services` ADD COLUMN `description` TEXT DEFAULT NULL AFTER `category`',
    'SELECT "Column description already exists" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Добавляем поле is_active (если нет)
SET @column_exists = (SELECT COUNT(*)
                      FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'services'
                        AND COLUMN_NAME = 'is_active');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `services` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `description`',
    'SELECT "Column is_active already exists" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Добавляем поле sort_order (если нет)
SET @column_exists = (SELECT COUNT(*)
                      FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'services'
                        AND COLUMN_NAME = 'sort_order');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `services` ADD COLUMN `sort_order` INT DEFAULT 0 AFTER `is_active`',
    'SELECT "Column sort_order already exists" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Добавляем поле chat_image (если нет)
SET @column_exists = (SELECT COUNT(*)
                      FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'services'
                        AND COLUMN_NAME = 'chat_image');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `services` ADD COLUMN `chat_image` VARCHAR(255) DEFAULT NULL AFTER `sort_order`',
    'SELECT "Column chat_image already exists" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Проверяем финальную структуру
DESCRIBE services;

SELECT 'Миграция завершена! Поле category и другие поля добавлены в таблицу services.' as result;
