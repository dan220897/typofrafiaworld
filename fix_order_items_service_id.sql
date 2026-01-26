-- Исправление типа поля service_id в таблице order_items
-- Совместимо с MySQL 5.x
-- Изменяем с INT на VARCHAR(50) для соответствия таблице services

-- Шаг 1: Проверяем и удаляем внешний ключ, если он существует
-- Сначала нужно узнать название внешнего ключа
SET @constraint_name = (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'order_items'
    AND COLUMN_NAME = 'service_id'
    AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1
);

-- Удаляем внешний ключ, если он найден
SET @sql = IF(@constraint_name IS NOT NULL,
    CONCAT('ALTER TABLE `order_items` DROP FOREIGN KEY `', @constraint_name, '`'),
    'SELECT "No foreign key to drop" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Шаг 2: Изменяем тип поля service_id на VARCHAR(50)
ALTER TABLE `order_items` MODIFY `service_id` VARCHAR(50) NOT NULL;

-- Шаг 3: Восстанавливаем внешний ключ
ALTER TABLE `order_items`
ADD CONSTRAINT `order_items_ibfk_2`
FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Проверяем изменения
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_KEY
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'order_items'
AND COLUMN_NAME = 'service_id';

