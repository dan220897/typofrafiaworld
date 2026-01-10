-- ============================================
-- МИГРАЦИЯ 003: Добавление себестоимости в таблицы
-- Дата: 2026-01-10
-- Описание: Добавляет поля cost_price для учета себестоимости
-- Совместимость: MySQL 5.7+
-- ============================================

-- Процедура для безопасного добавления колонки
DELIMITER $$

DROP PROCEDURE IF EXISTS add_column_if_not_exists$$
CREATE PROCEDURE add_column_if_not_exists(
    IN tableName VARCHAR(128),
    IN columnName VARCHAR(128),
    IN columnDefinition VARCHAR(512)
)
BEGIN
    DECLARE column_exists INT DEFAULT 0;

    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = tableName
        AND COLUMN_NAME = columnName;

    IF column_exists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', tableName, '` ADD COLUMN `', columnName, '` ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Добавляем поля в service_base_prices
CALL add_column_if_not_exists('service_base_prices', 'cost_price', 'DECIMAL(10,2) DEFAULT 0.00 AFTER `base_price`');
CALL add_column_if_not_exists('service_base_prices', 'margin', 'DECIMAL(10,2) DEFAULT 0.00 AFTER `cost_price`');
CALL add_column_if_not_exists('service_base_prices', 'margin_percent', 'DECIMAL(6,2) DEFAULT 0.00 AFTER `margin`');

-- Добавляем поле в service_sizes
CALL add_column_if_not_exists('service_sizes', 'cost_price', 'DECIMAL(10,2) DEFAULT 0.00 AFTER `price`');

-- Добавляем поле в service_density
CALL add_column_if_not_exists('service_density', 'cost_price', 'DECIMAL(10,2) DEFAULT 0.00 AFTER `price`');

-- Добавляем поле в service_quantities
CALL add_column_if_not_exists('service_quantities', 'cost_price', 'DECIMAL(10,2) DEFAULT 0.00 AFTER `price`');

-- Добавляем поле в service_lamination
CALL add_column_if_not_exists('service_lamination', 'cost_price', 'DECIMAL(10,2) DEFAULT 0.00 AFTER `price`');

-- Удаляем процедуру после использования
DROP PROCEDURE IF EXISTS add_column_if_not_exists;

-- ============================================
-- Конец миграции 003
-- ============================================
