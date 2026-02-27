-- ============================================
-- ШАГ 4: Изменение структуры таблицы services
-- ============================================

-- 1. Убираем AUTO_INCREMENT (если есть)
ALTER TABLE `services` MODIFY `id` INT NOT NULL;

-- 2. Меняем тип id на VARCHAR(50)
ALTER TABLE `services` MODIFY `id` VARCHAR(50) NOT NULL;

-- 3. Добавляем поле label (если нет)
SET @column_exists = (SELECT COUNT(*) 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'services'
                        AND COLUMN_NAME = 'label');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `services` ADD COLUMN `label` VARCHAR(255) NOT NULL AFTER `id`',
    'SELECT "Column label already exists" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Добавляем поле icon (если нет)
SET @column_exists = (SELECT COUNT(*) 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'services'
                        AND COLUMN_NAME = 'icon');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `services` ADD COLUMN `icon` VARCHAR(50) DEFAULT NULL AFTER `label`',
    'SELECT "Column icon already exists" as result');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Проверяем структуру
DESCRIBE services;
