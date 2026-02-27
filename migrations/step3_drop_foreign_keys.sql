-- ============================================
-- ШАГ 3: Удаление всех внешних ключей на services
-- ============================================

-- Удаляем FK из order_items (если есть)
SET @fk_exists = (SELECT COUNT(*) 
                  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'order_items'
                    AND REFERENCED_TABLE_NAME = 'services');

SET @sql = IF(@fk_exists > 0,
    CONCAT('ALTER TABLE `order_items` DROP FOREIGN KEY `',
           (SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'order_items'
              AND REFERENCED_TABLE_NAME = 'services'
            LIMIT 1), '`'),
    'SELECT "No FK in order_items" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Удаляем FK из service_parameters (если есть)
SET @fk_exists = (SELECT COUNT(*) 
                  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'service_parameters'
                    AND REFERENCED_TABLE_NAME = 'services');

SET @sql = IF(@fk_exists > 0,
    CONCAT('ALTER TABLE `service_parameters` DROP FOREIGN KEY `',
           (SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'service_parameters'
              AND REFERENCED_TABLE_NAME = 'services'
            LIMIT 1), '`'),
    'SELECT "No FK in service_parameters" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Удаляем FK из service_price_rules (если есть)
SET @fk_exists = (SELECT COUNT(*) 
                  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'service_price_rules'
                    AND REFERENCED_TABLE_NAME = 'services');

SET @sql = IF(@fk_exists > 0,
    CONCAT('ALTER TABLE `service_price_rules` DROP FOREIGN KEY `',
           (SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'service_price_rules'
              AND REFERENCED_TABLE_NAME = 'services'
            LIMIT 1), '`'),
    'SELECT "No FK in service_price_rules" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'All foreign keys dropped successfully' as result;
