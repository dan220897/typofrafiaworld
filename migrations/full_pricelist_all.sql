-- ============================================
-- МИГРАЦИЯ 002: Обновление структуры таблицы services
-- Дата: 2026-01-10
-- Описание: Приводит таблицу services к новой структуре
-- Совместимость: MySQL 5.7+
-- ============================================

-- Процедура для безопасного добавления/изменения колонок
DELIMITER $$

DROP PROCEDURE IF EXISTS alter_services_structure$$
CREATE PROCEDURE alter_services_structure()
BEGIN
    DECLARE label_exists INT DEFAULT 0;
    DECLARE icon_exists INT DEFAULT 0;
    DECLARE id_type VARCHAR(50);
    DECLARE fk_name VARCHAR(128);
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur CURSOR FOR
        SELECT CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'order_items'
            AND REFERENCED_TABLE_NAME = 'services'
            AND REFERENCED_COLUMN_NAME = 'id';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Проверяем существование колонки label
    SELECT COUNT(*) INTO label_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'services'
        AND COLUMN_NAME = 'label';

    -- Проверяем существование колонки icon
    SELECT COUNT(*) INTO icon_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'services'
        AND COLUMN_NAME = 'icon';

    -- Получаем текущий тип колонки id
    SELECT DATA_TYPE INTO id_type
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'services'
        AND COLUMN_NAME = 'id';

    -- Если id имеет тип int, меняем структуру
    IF id_type = 'int' THEN
        -- 1. Удаляем все внешние ключи на services.id
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO fk_name;
            IF done THEN
                LEAVE read_loop;
            END IF;
            SET @drop_fk = CONCAT('ALTER TABLE `order_items` DROP FOREIGN KEY `', fk_name, '`');
            PREPARE stmt FROM @drop_fk;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END LOOP;
        CLOSE cur;

        -- 2. Очищаем связанные данные (заказы со старыми услугами)
        DELETE FROM order_items WHERE service_id IN (SELECT id FROM services);

        -- 3. Очищаем таблицу services (старые данные)
        DELETE FROM services;

        -- 4. Удаляем AUTO_INCREMENT
        ALTER TABLE `services` MODIFY `id` INT NOT NULL;

        -- 5. Меняем тип id на VARCHAR
        ALTER TABLE `services` MODIFY `id` VARCHAR(50) NOT NULL;

        -- 6. Добавляем поле label
        IF label_exists = 0 THEN
            ALTER TABLE `services` ADD COLUMN `label` VARCHAR(255) NOT NULL AFTER `id`;
        END IF;

        -- 7. Добавляем поле icon
        IF icon_exists = 0 THEN
            ALTER TABLE `services` ADD COLUMN `icon` VARCHAR(50) DEFAULT NULL AFTER `label`;
        END IF;

        -- 8. Убеждаемся что есть category, description, is_active, sort_order
        SET @alter_query = 'ALTER TABLE `services` ';

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'services'
                      AND COLUMN_NAME = 'category') THEN
            SET @alter_query = CONCAT(@alter_query, 'ADD COLUMN `category` VARCHAR(100) NULL AFTER `icon`, ');
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'services'
                      AND COLUMN_NAME = 'is_active') THEN
            SET @alter_query = CONCAT(@alter_query, 'ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `description`, ');
        END IF;

        IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'services'
                      AND COLUMN_NAME = 'sort_order') THEN
            SET @alter_query = CONCAT(@alter_query, 'ADD COLUMN `sort_order` INT DEFAULT 0 AFTER `is_active`');
        END IF;

        -- Убираем последнюю запятую если есть
        SET @alter_query = TRIM(TRAILING ', ' FROM @alter_query);

        -- Выполняем только если есть что добавлять
        IF @alter_query != 'ALTER TABLE `services` ' THEN
            PREPARE stmt FROM @alter_query;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;

        -- 9. Меняем тип service_id в order_items на VARCHAR(50)
        ALTER TABLE `order_items` MODIFY `service_id` VARCHAR(50) DEFAULT NULL;

        -- 10. Восстанавливаем внешний ключ (но теперь VARCHAR)
        ALTER TABLE `order_items`
        ADD CONSTRAINT `order_items_ibfk_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE;

    ELSE
        -- id уже VARCHAR, просто добавляем недостающие поля
        IF label_exists = 0 THEN
            ALTER TABLE `services` ADD COLUMN `label` VARCHAR(255) NOT NULL AFTER `id`;
        END IF;

        IF icon_exists = 0 THEN
            ALTER TABLE `services` ADD COLUMN `icon` VARCHAR(50) DEFAULT NULL AFTER `label`;
        END IF;
    END IF;

END$$

DELIMITER ;

-- Выполняем процедуру
CALL alter_services_structure();

-- Удаляем процедуру
DROP PROCEDURE IF EXISTS alter_services_structure;

-- ============================================
-- Конец миграции 002
-- ============================================
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
-- ============================================
-- МИГРАЦИЯ 004: Полный прайс-лист типографии (Часть 1)
-- Дата: 2026-01-10
-- Описание: Добавляет все услуги из прайс-листа с себестоимостью
-- Часть 1: Печать документов, Копирование, Сканирование
-- ============================================

-- Очищаем старые данные (если нужно)
-- DELETE FROM service_quantities WHERE service_id LIKE 'print_%' OR service_id LIKE 'copy_%' OR service_id LIKE 'scan_%';
-- DELETE FROM service_sides WHERE service_id LIKE 'print_%' OR service_id LIKE 'copy_%' OR service_id LIKE 'scan_%';
-- DELETE FROM service_base_prices WHERE service_id LIKE 'print_%' OR service_id LIKE 'copy_%' OR service_id LIKE 'scan_%';
-- DELETE FROM services WHERE id LIKE 'print_%' OR id LIKE 'copy_%' OR id LIKE 'scan_%';

-- ============================================
-- КАТЕГОРИЯ: Печать документов
-- ============================================

-- Услуга: Черно-белая печать A4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('print_bw_a4', 'Ч/Б печать А4', 'Печать документов', 'Черно-белая печать документов формата А4. Быстро и качественно.', 'fa-file-text', 1, 1)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`),
  `icon` = VALUES(`icon`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('print_bw_a4', 3.00, 1.00, 2.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_bw_a4_1side', 'print_bw_a4', 'Односторонняя', 1.00),
('print_bw_a4_2side', 'print_bw_a4', 'Двусторонняя', 1.67)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('print_bw_a4_1', 'print_bw_a4', '1 лист', 1, 1.00, 0),
('print_bw_a4_10', 'print_bw_a4', '10 листов', 10, 1.00, 0),
('print_bw_a4_50', 'print_bw_a4', '50 листов', 50, 1.00, 0),
('print_bw_a4_100', 'print_bw_a4', '100 листов', 100, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `price` = VALUES(`price`);

-- Услуга: Черно-белая печать A3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('print_bw_a3', 'Ч/Б печать А3', 'Печать документов', 'Черно-белая печать документов формата А3. Большой формат для чертежей и схем.', 'fa-file-text', 1, 2)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('print_bw_a3', 10.00, 3.00, 7.00, 233.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_bw_a3_1side', 'print_bw_a3', 'Односторонняя', 1.00),
('print_bw_a3_2side', 'print_bw_a3', 'Двусторонняя', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('print_bw_a3_1', 'print_bw_a3', '1 лист', 1, 1.00, 0),
('print_bw_a3_10', 'print_bw_a3', '10 листов', 10, 1.00, 0),
('print_bw_a3_50', 'print_bw_a3', '50 листов', 50, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`);

-- Услуга: Цветная печать A4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('print_color_a4', 'Цветная печать А4', 'Печать документов', 'Цветная печать документов формата А4. Яркие и насыщенные цвета.', 'fa-print', 1, 3)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('print_color_a4', 15.00, 5.00, 10.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_color_a4_1side', 'print_color_a4', 'Односторонняя', 1.00),
('print_color_a4_2side', 'print_color_a4', 'Двусторонняя', 1.67)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('print_color_a4_1', 'print_color_a4', '1 лист', 1, 1.00, 0),
('print_color_a4_10', 'print_color_a4', '10 листов', 10, 1.00, 0),
('print_color_a4_50', 'print_color_a4', '50 листов', 50, 1.00, 0),
('print_color_a4_100', 'print_color_a4', '100 листов', 100, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`);

-- Услуга: Цветная печать A3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('print_color_a3', 'Цветная печать А3', 'Печать документов', 'Цветная печать документов формата А3. Для презентаций и плакатов.', 'fa-print', 1, 4)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('print_color_a3', 40.00, 15.00, 25.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_color_a3_1side', 'print_color_a3', 'Односторонняя', 1.00),
('print_color_a3_2side', 'print_color_a3', 'Двусторонняя', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Копирование
-- ============================================

-- Услуга: Ксерокопия A4 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('copy_bw_a4', 'Ксерокопия А4 ч/б', 'Копирование', 'Черно-белое копирование формата А4. Быстро и недорого.', 'fa-copy', 1, 10)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('copy_bw_a4', 3.00, 0.80, 2.20, 275.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('copy_bw_a4_1', 'copy_bw_a4', '1 копия', 1, 1.00, 0),
('copy_bw_a4_10', 'copy_bw_a4', '10 копий', 10, 1.00, 0),
('copy_bw_a4_50', 'copy_bw_a4', '50 копий', 50, 1.00, 0),
('copy_bw_a4_100', 'copy_bw_a4', '100 копий', 100, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`);

-- Услуга: Ксерокопия A3 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('copy_bw_a3', 'Ксерокопия А3 ч/б', 'Копирование', 'Черно-белое копирование формата А3.', 'fa-copy', 1, 11)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('copy_bw_a3', 10.00, 2.50, 7.50, 300.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Ксерокопия A4 цветная
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('copy_color_a4', 'Ксерокопия А4 цветная', 'Копирование', 'Цветное копирование формата А4.', 'fa-copy', 1, 12)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('copy_color_a4', 15.00, 4.00, 11.00, 275.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Ксерокопия A3 цветная
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('copy_color_a3', 'Ксерокопия А3 цветная', 'Копирование', 'Цветное копирование формата А3.', 'fa-copy', 1, 13)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('copy_color_a3', 35.00, 12.00, 23.00, 191.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Копирование паспорта
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('copy_passport', 'Копирование паспорта', 'Копирование', 'Копирование всех страниц паспорта на один лист. Очень выгодно!', 'fa-id-card', 1, 14)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('copy_passport', 20.00, 2.00, 18.00, 900.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- ============================================
-- КАТЕГОРИЯ: Сканирование
-- ============================================

-- Услуга: Сканирование A4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('scan_a4', 'Сканирование А4', 'Сканирование', 'Сканирование документов формата А4 в PDF/JPG. Высокое качество.', 'fa-scanner', 1, 20)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('scan_a4', 10.00, 1.00, 9.00, 900.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('scan_a4_1', 'scan_a4', '1 страница', 1, 1.00, 0),
('scan_a4_10', 'scan_a4', '10 страниц', 10, 1.00, 0),
('scan_a4_50', 'scan_a4', '50 страниц', 50, 1.00, 0),
('scan_a4_100', 'scan_a4', '100 страниц', 100, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`);

-- Услуга: Сканирование A3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('scan_a3', 'Сканирование А3', 'Сканирование', 'Сканирование документов формата А3 в PDF/JPG.', 'fa-scanner', 1, 21)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('scan_a3', 20.00, 2.00, 18.00, 900.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Распознавание текста (OCR)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('scan_ocr', 'Распознавание текста (OCR)', 'Сканирование', 'Сканирование с распознаванием текста в редактируемый формат Word/PDF.', 'fa-file-word', 1, 22)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('scan_ocr', 30.00, 3.00, 27.00, 900.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Сканирование книги
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('scan_book', 'Сканирование книги', 'Сканирование', 'Сканирование книги с разделением на страницы. От 10 страниц.', 'fa-book', 1, 23)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('scan_book', 100.00, 10.00, 90.00, 900.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('scan_book_10', 'scan_book', '10 страниц', 10, 1.00, 0),
('scan_book_50', 'scan_book', '50 страниц', 50, 1.00, 0),
('scan_book_100', 'scan_book', '100 страниц', 100, 1.00, 0),
('scan_book_200', 'scan_book', '200 страниц', 200, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`);

-- ============================================
-- Конец миграции 004 (Часть 1)
-- ============================================
-- ============================================
-- МИГРАЦИЯ 005: Полный прайс-лист типографии (Часть 2)
-- Дата: 2026-01-10
-- Описание: Визитки, Листовки, Брошюры
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Визитные карточки
-- ============================================

-- Услуга: Визитки стандартные односторонние
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_std_1side', 'Визитки односторонние', 'Визитки', 'Стандартные визитки 90×50 мм, односторонняя печать. Базовая цена за 100 шт.', 'fa-id-card', 1, 30)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_std_1side', 500.00, 150.00, 350.00, 233.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_std_1s_100', 'business_cards_std_1side', '100 шт', 100, 1.00, 0),
('bc_std_1s_200', 'business_cards_std_1side', '200 шт', 200, 1.00, 0),
('bc_std_1s_300', 'business_cards_std_1side', '300 шт', 300, 1.00, 0),
('bc_std_1s_500', 'business_cards_std_1side', '500 шт', 500, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Визитки стандартные двусторонние
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_std_2side', 'Визитки двусторонние', 'Визитки', 'Стандартные визитки 90×50 мм, двусторонняя печать. Базовая цена за 100 шт.', 'fa-id-card', 1, 31)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_std_2side', 700.00, 250.00, 450.00, 180.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_std_2s_100', 'business_cards_std_2side', '100 шт', 100, 1.00, 0),
('bc_std_2s_200', 'business_cards_std_2side', '200 шт', 200, 1.00, 0),
('bc_std_2s_300', 'business_cards_std_2side', '300 шт', 300, 1.00, 0),
('bc_std_2s_500', 'business_cards_std_2side', '500 шт', 500, 0.85, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Визитки с ламинацией
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_laminated', 'Визитки с ламинацией', 'Визитки', 'Визитки с матовой или глянцевой ламинацией. Цена за 100 шт.', 'fa-id-card', 1, 32)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_laminated', 1200.00, 400.00, 800.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('bc_lam_matte', 'business_cards_laminated', 'Матовая ламинация', 0),
('bc_lam_gloss', 'business_cards_laminated', 'Глянцевая ламинация', 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_lam_100', 'business_cards_laminated', '100 шт', 100, 1.00, 0),
('bc_lam_200', 'business_cards_laminated', '200 шт', 200, 1.00, 0),
('bc_lam_500', 'business_cards_laminated', '500 шт', 500, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Визитки на дизайнерской бумаге
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_designer', 'Визитки на дизайнерской бумаге', 'Визитки', 'Визитки на дизайнерской бумаге премиум качества. Цена за 100 шт.', 'fa-id-card', 1, 33)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_designer', 1500.00, 600.00, 900.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_des_100', 'business_cards_designer', '100 шт', 100, 1.00, 0),
('bc_des_200', 'business_cards_designer', '200 шт', 200, 1.00, 0),
('bc_des_500', 'business_cards_designer', '500 шт', 500, 0.85, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Листовки и флаеры
-- ============================================

-- Услуга: Листовки A6 (105×148 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('flyers_a6', 'Листовки А6 (105×148 мм)', 'Листовки', 'Яркие рекламные листовки формата А6. Идеально для промо-акций.', 'fa-file-alt', 1, 40)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('flyers_a6', 800.00, 250.00, 550.00, 220.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyers_a6_1side', 'flyers_a6', 'Односторонние', 1.00),
('flyers_a6_2side', 'flyers_a6', 'Двусторонние', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyers_a6_100', 'flyers_a6', '100 шт', 100, 1.00, 0),
('flyers_a6_500', 'flyers_a6', '500 шт', 500, 0.85, 0),
('flyers_a6_1000', 'flyers_a6', '1000 шт', 1000, 0.70, 0),
('flyers_a6_2000', 'flyers_a6', '2000 шт', 2000, 0.60, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Листовки A5 (148×210 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('flyers_a5', 'Листовки А5 (148×210 мм)', 'Листовки', 'Рекламные листовки формата А5. Популярный формат для распространения.', 'fa-file-alt', 1, 41)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('flyers_a5', 1200.00, 400.00, 800.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyers_a5_1side', 'flyers_a5', 'Односторонние', 1.00),
('flyers_a5_2side', 'flyers_a5', 'Двусторонние', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyers_a5_100', 'flyers_a5', '100 шт', 100, 1.00, 0),
('flyers_a5_500', 'flyers_a5', '500 шт', 500, 0.80, 0),
('flyers_a5_1000', 'flyers_a5', '1000 шт', 1000, 0.65, 0),
('flyers_a5_2000', 'flyers_a5', '2000 шт', 2000, 0.55, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Листовки A4 (210×297 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('flyers_a4', 'Листовки А4 (210×297 мм)', 'Листовки', 'Большие рекламные листовки формата А4. Максимум информации.', 'fa-file-alt', 1, 42)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('flyers_a4', 1800.00, 600.00, 1200.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyers_a4_1side', 'flyers_a4', 'Односторонние', 1.00),
('flyers_a4_2side', 'flyers_a4', 'Двусторонние', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyers_a4_100', 'flyers_a4', '100 шт', 100, 1.00, 0),
('flyers_a4_500', 'flyers_a4', '500 шт', 500, 0.75, 0),
('flyers_a4_1000', 'flyers_a4', '1000 шт', 1000, 0.60, 0),
('flyers_a4_2000', 'flyers_a4', '2000 шт', 2000, 0.50, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Брошюры и буклеты
-- ============================================

-- Услуга: Брошюры A5 на скрепке (8 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a5_8p_staple', 'Брошюра А5 на скрепке (8 стр)', 'Брошюры', 'Брошюра формата А5 на скрепке, 8 страниц. Цена за 50 шт.', 'fa-book', 1, 50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a5_8p_staple', 3000.00, 1200.00, 1800.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a5_8p_50', 'brochure_a5_8p_staple', '50 шт', 50, 1.00, 0),
('broch_a5_8p_100', 'brochure_a5_8p_staple', '100 шт', 100, 0.90, 0),
('broch_a5_8p_200', 'brochure_a5_8p_staple', '200 шт', 200, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A5 на скрепке (16 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a5_16p_staple', 'Брошюра А5 на скрепке (16 стр)', 'Брошюры', 'Брошюра формата А5 на скрепке, 16 страниц. Цена за 50 шт.', 'fa-book', 1, 51)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a5_16p_staple', 4500.00, 1800.00, 2700.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a5_16p_50', 'brochure_a5_16p_staple', '50 шт', 50, 1.00, 0),
('broch_a5_16p_100', 'brochure_a5_16p_staple', '100 шт', 100, 0.90, 0),
('broch_a5_16p_200', 'brochure_a5_16p_staple', '200 шт', 200, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A5 на скрепке (24 страницы)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a5_24p_staple', 'Брошюра А5 на скрепке (24 стр)', 'Брошюры', 'Брошюра формата А5 на скрепке, 24 страницы. Цена за 50 шт.', 'fa-book', 1, 52)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a5_24p_staple', 6000.00, 2500.00, 3500.00, 140.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a5_24p_50', 'brochure_a5_24p_staple', '50 шт', 50, 1.00, 0),
('broch_a5_24p_100', 'brochure_a5_24p_staple', '100 шт', 100, 0.85, 0),
('broch_a5_24p_200', 'brochure_a5_24p_staple', '200 шт', 200, 0.75, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A4 на пружине (20 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a4_20p_spiral', 'Брошюра А4 на пружине (20 стр)', 'Брошюры', 'Брошюра формата А4 на пружине, 20 страниц. Презентабельный вид. Цена за 50 шт.', 'fa-book', 1, 53)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a4_20p_spiral', 6000.00, 2500.00, 3500.00, 140.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a4_20p_50', 'brochure_a4_20p_spiral', '50 шт', 50, 1.00, 0),
('broch_a4_20p_100', 'brochure_a4_20p_spiral', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A4 на пружине (50 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a4_50p_spiral', 'Брошюра А4 на пружине (50 стр)', 'Брошюры', 'Брошюра формата А4 на пружине, 50 страниц. Каталоги, прайсы. Цена за 50 шт.', 'fa-book', 1, 54)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a4_50p_spiral', 12000.00, 5500.00, 6500.00, 118.18)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a4_50p_50', 'brochure_a4_50p_spiral', '50 шт', 50, 1.00, 0),
('broch_a4_50p_100', 'brochure_a4_50p_spiral', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A4 на пружине (100 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a4_100p_spiral', 'Брошюра А4 на пружине (100 стр)', 'Брошюры', 'Брошюра формата А4 на пружине, 100 страниц. Толстые каталоги. Цена за 50 шт.', 'fa-book', 1, 55)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a4_100p_spiral', 20000.00, 10000.00, 10000.00, 100.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a4_100p_50', 'brochure_a4_100p_spiral', '50 шт', 50, 1.00, 0),
('broch_a4_100p_100', 'brochure_a4_100p_spiral', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- Конец миграции 005 (Часть 2)
-- ============================================
-- ============================================
-- МИГРАЦИЯ 006: Полный прайс-лист типографии (Часть 3)
-- Дата: 2026-01-10
-- Описание: Плакаты, Наклейки, Широкоформатная печать
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Плакаты и постеры
-- ============================================

-- Услуга: Плакат A3 (297×420 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a3', 'Плакат А3 (297×420 мм)', 'Плакаты', 'Яркий плакат формата А3. Цена за 1 шт.', 'fa-image', 1, 60)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a3', 150.00, 50.00, 100.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a3_no_lam', 'poster_a3', 'Без ламинации', 0, 0),
('poster_a3_lam', 'poster_a3', 'С ламинацией', 150.00, 50.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a3_1', 'poster_a3', '1 шт', 1, 1.00, 0),
('poster_a3_10', 'poster_a3', '10 шт', 10, 0.80, 0),
('poster_a3_50', 'poster_a3', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Плакат A2 (420×594 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a2', 'Плакат А2 (420×594 мм)', 'Плакаты', 'Большой плакат формата А2. Цена за 1 шт.', 'fa-image', 1, 61)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a2', 350.00, 120.00, 230.00, 191.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a2_no_lam', 'poster_a2', 'Без ламинации', 0, 0),
('poster_a2_lam', 'poster_a2', 'С ламинацией', 250.00, 80.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a2_1', 'poster_a2', '1 шт', 1, 1.00, 0),
('poster_a2_10', 'poster_a2', '10 шт', 10, 0.80, 0),
('poster_a2_50', 'poster_a2', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Плакат A1 (594×841 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a1', 'Плакат А1 (594×841 мм)', 'Плакаты', 'Очень большой плакат формата А1. Цена за 1 шт.', 'fa-image', 1, 62)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a1', 700.00, 250.00, 450.00, 180.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a1_no_lam', 'poster_a1', 'Без ламинации', 0, 0),
('poster_a1_lam', 'poster_a1', 'С ламинацией', 500.00, 150.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a1_1', 'poster_a1', '1 шт', 1, 1.00, 0),
('poster_a1_10', 'poster_a1', '10 шт', 10, 0.80, 0),
('poster_a1_50', 'poster_a1', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Плакат A0 (841×1189 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a0', 'Плакат А0 (841×1189 мм)', 'Плакаты', 'Гигантский плакат формата А0. Цена за 1 шт.', 'fa-image', 1, 63)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a0', 1500.00, 550.00, 950.00, 172.73)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a0_no_lam', 'poster_a0', 'Без ламинации', 0, 0),
('poster_a0_lam', 'poster_a0', 'С ламинацией', 1000.00, 300.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a0_1', 'poster_a0', '1 шт', 1, 1.00, 0),
('poster_a0_10', 'poster_a0', '10 шт', 10, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Наклейки и стикеры
-- ============================================

-- Услуга: Бумажные наклейки 50×50 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_paper_50x50', 'Бумажные наклейки 50×50 мм', 'Наклейки', 'Круглые или квадратные бумажные наклейки 50×50 мм. Цена за 100 шт.', 'fa-tag', 1, 70)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_paper_50x50', 800.00, 300.00, 500.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_p50_100', 'stickers_paper_50x50', '100 шт', 100, 1.00, 0),
('stickers_p50_500', 'stickers_paper_50x50', '500 шт', 500, 0.75, 0),
('stickers_p50_1000', 'stickers_paper_50x50', '1000 шт', 1000, 0.60, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Бумажные наклейки 100×100 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_paper_100x100', 'Бумажные наклейки 100×100 мм', 'Наклейки', 'Круглые или квадратные бумажные наклейки 100×100 мм. Цена за 100 шт.', 'fa-tag', 1, 71)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_paper_100x100', 1500.00, 600.00, 900.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_p100_100', 'stickers_paper_100x100', '100 шт', 100, 1.00, 0),
('stickers_p100_500', 'stickers_paper_100x100', '500 шт', 500, 0.70, 0),
('stickers_p100_1000', 'stickers_paper_100x100', '1000 шт', 1000, 0.60, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Виниловые наклейки 50×50 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_vinyl_50x50', 'Виниловые наклейки 50×50 мм', 'Наклейки', 'Водостойкие виниловые наклейки 50×50 мм. Долговечные. Цена за 100 шт.', 'fa-tag', 1, 72)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_vinyl_50x50', 1500.00, 600.00, 900.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_v50_100', 'stickers_vinyl_50x50', '100 шт', 100, 1.00, 0),
('stickers_v50_500', 'stickers_vinyl_50x50', '500 шт', 500, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Виниловые наклейки 100×100 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_vinyl_100x100', 'Виниловые наклейки 100×100 мм', 'Наклейки', 'Водостойкие виниловые наклейки 100×100 мм. Долговечные. Цена за 100 шт.', 'fa-tag', 1, 73)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_vinyl_100x100', 2500.00, 1100.00, 1400.00, 127.27)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_v100_100', 'stickers_vinyl_100x100', '100 шт', 100, 1.00, 0),
('stickers_v100_500', 'stickers_vinyl_100x100', '500 шт', 500, 0.65, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Стикерпаки (набор 10-20 стикеров)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('sticker_packs', 'Стикерпаки (набор 10-20 шт)', 'Наклейки', 'Набор стикеров 10-20 штук. Популярно у молодежи! Цена за 50 наборов.', 'fa-tags', 1, 74)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('sticker_packs', 5000.00, 2000.00, 3000.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickerpack_50', 'sticker_packs', '50 наборов', 50, 1.00, 0),
('stickerpack_100', 'sticker_packs', '100 наборов', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Широкоформатная печать
-- ============================================

-- Услуга: Баннер 1×1 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_1x1', 'Баннер 1×1 м', 'Широкоформатная печать', 'Баннер на виниле 1×1 метр. Для наружной рекламы.', 'fa-flag', 1, 80)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_1x1', 1200.00, 500.00, 700.00, 140.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Баннер 2×1 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_2x1', 'Баннер 2×1 м', 'Широкоформатная печать', 'Баннер на виниле 2×1 метр. Для наружной рекламы.', 'fa-flag', 1, 81)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_2x1', 2000.00, 900.00, 1100.00, 122.22)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Баннер 3×2 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_3x2', 'Баннер 3×2 м', 'Широкоформатная печать', 'Баннер на виниле 3×2 метра. Большой формат для наружной рекламы.', 'fa-flag', 1, 82)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_3x2', 5000.00, 2200.00, 2800.00, 127.27)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Баннер 6×3 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_6x3', 'Баннер 6×3 м', 'Широкоформатная печать', 'Баннер на виниле 6×3 метра. Огромный формат для наружной рекламы.', 'fa-flag', 1, 83)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_6x3', 15000.00, 7000.00, 8000.00, 114.29)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Roll-up стенд 0.8×2 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('rollup_08x2', 'Roll-up стенд 0.8×2 м', 'Широкоформатная печать', 'Roll-up стенд с конструкцией 0.8×2 метра. Готов к установке.', 'fa-display', 1, 84)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('rollup_08x2', 3500.00, 1800.00, 1700.00, 94.44)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Roll-up стенд 1×2 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('rollup_1x2', 'Roll-up стенд 1×2 м', 'Широкоформатная печать', 'Roll-up стенд с конструкцией 1×2 метра. Готов к установке.', 'fa-display', 1, 85)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('rollup_1x2', 4500.00, 2300.00, 2200.00, 95.65)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Фотообои (цена за м²)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('wallpaper_photo', 'Фотообои (за м²)', 'Широкоформатная печать', 'Печать фотообоев. Цена указана за 1 м². Минимальный заказ 3 м².', 'fa-image', 1, 86)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('wallpaper_photo', 2500.00, 1000.00, 1500.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('wallpaper_3m', 'wallpaper_photo', '1-5 м²', 3, 1.00, 0),
('wallpaper_5m', 'wallpaper_photo', '5-10 м²', 7, 0.90, 0),
('wallpaper_10m', 'wallpaper_photo', '10+ м²', 10, 0.75, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- Конец миграции 006 (Часть 3)
-- ============================================
-- ============================================
-- МИГРАЦИЯ 007: Полный прайс-лист типографии (Часть 4)
-- Дата: 2026-01-10
-- Описание: Чертежи, Фотоуслуги, Календари, Дипломы, Открытки, Дизайн
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Чертежи и схемы
-- ============================================

-- Услуга: Чертеж A3 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a3_bw', 'Чертеж А3 ч/б', 'Чертежи и схемы', 'Печать чертежа формата А3 черно-белый. Для строителей и архитекторов.', 'fa-ruler-combined', 1, 90)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a3_bw', 50.00, 15.00, 35.00, 233.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Чертеж A3 цветной
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a3_color', 'Чертеж А3 цветной', 'Чертежи и схемы', 'Печать чертежа формата А3 цветной.', 'fa-ruler-combined', 1, 91)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a3_color', 150.00, 55.00, 95.00, 172.73)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Чертеж A2 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a2_bw', 'Чертеж А2 ч/б', 'Чертежи и схемы', 'Печать чертежа формата А2 черно-белый.', 'fa-ruler-combined', 1, 92)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a2_bw', 100.00, 35.00, 65.00, 185.71)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Чертеж A2 цветной
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a2_color', 'Чертеж А2 цветной', 'Чертежи и схемы', 'Печать чертежа формата А2 цветной.', 'fa-ruler-combined', 1, 93)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a2_color', 300.00, 110.00, 190.00, 172.73)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Чертеж A1 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a1_bw', 'Чертеж А1 ч/б', 'Чертежи и схемы', 'Печать чертежа формата А1 черно-белый.', 'fa-ruler-combined', 1, 94)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a1_bw', 200.00, 70.00, 130.00, 185.71)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Чертеж A1 цветной
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a1_color', 'Чертеж А1 цветной', 'Чертежи и схемы', 'Печать чертежа формата А1 цветной.', 'fa-ruler-combined', 1, 95)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a1_color', 600.00, 220.00, 380.00, 172.73)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Чертеж A0 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a0_bw', 'Чертеж А0 ч/б', 'Чертежи и схемы', 'Печать чертежа формата А0 черно-белый.', 'fa-ruler-combined', 1, 96)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a0_bw', 400.00, 150.00, 250.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Чертеж A0 цветной
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('blueprint_a0_color', 'Чертеж А0 цветной', 'Чертежи и схемы', 'Печать чертежа формата А0 цветной.', 'fa-ruler-combined', 1, 97)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('blueprint_a0_color', 1200.00, 450.00, 750.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- ============================================
-- КАТЕГОРИЯ: Фотоуслуги
-- ============================================

-- Услуга: Печать фото 10×15 см
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('photo_10x15', 'Печать фото 10×15 см', 'Фотоуслуги', 'Печать фотографий 10×15 см. Стандартный формат.', 'fa-camera', 1, 100)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('photo_10x15', 15.00, 5.00, 10.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('photo_10x15_1', 'photo_10x15', '1 фото', 1, 1.00, 0),
('photo_10x15_10', 'photo_10x15', '10 фото', 10, 1.00, 0),
('photo_10x15_50', 'photo_10x15', '50 фото', 50, 0.90, 0),
('photo_10x15_100', 'photo_10x15', '100 фото', 100, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Печать фото 15×20 см
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('photo_15x20', 'Печать фото 15×20 см', 'Фотоуслуги', 'Печать фотографий 15×20 см.', 'fa-camera', 1, 101)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('photo_15x20', 30.00, 11.00, 19.00, 172.73)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Печать фото 20×30 см
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('photo_20x30', 'Печать фото 20×30 см', 'Фотоуслуги', 'Печать фотографий 20×30 см.', 'fa-camera', 1, 102)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('photo_20x30', 80.00, 30.00, 50.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Печать фото 30×40 см
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('photo_30x40', 'Печать фото 30×40 см', 'Фотоуслуги', 'Печать фотографий 30×40 см. Большой формат.', 'fa-camera', 1, 103)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('photo_30x40', 200.00, 75.00, 125.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Фото на документы (4 фото)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('photo_passport_4', 'Фото на документы (4 шт)', 'Фотоуслуги', 'Фото на паспорт, права, визу. 4 фотографии. ОЧЕНЬ ВЫГОДНО!', 'fa-id-badge', 1, 104)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('photo_passport_4', 200.00, 30.00, 170.00, 566.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Фото на документы (6 фото)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('photo_passport_6', 'Фото на визу (6 шт)', 'Фотоуслуги', 'Фото на визу. 6 фотографий. ОЧЕНЬ ВЫГОДНО!', 'fa-id-badge', 1, 105)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('photo_passport_6', 300.00, 40.00, 260.00, 650.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Срочное фото на документы (5 мин)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('photo_passport_urgent', 'Срочное фото на документы (5 мин)', 'Фотоуслуги', 'Срочное фото на документы за 5 минут. 4 фотографии.', 'fa-id-badge', 1, 106)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('photo_passport_urgent', 400.00, 30.00, 370.00, 1233.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- ============================================
-- КАТЕГОРИЯ: Календари
-- ============================================

-- Услуга: Настенный календарь A3 (12 листов)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('calendar_wall_a3', 'Настенный календарь А3 (12 л)', 'Календари', 'Перекидной настенный календарь А3 на 12 месяцев. Цена за 50 шт.', 'fa-calendar', 1, 110)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('calendar_wall_a3', 12000.00, 6000.00, 6000.00, 100.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_a3_50', 'calendar_wall_a3', '50 шт', 50, 1.00, 0),
('cal_a3_100', 'calendar_wall_a3', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Настенный календарь A2 (12 листов)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('calendar_wall_a2', 'Настенный календарь А2 (12 л)', 'Календари', 'Перекидной настенный календарь А2 на 12 месяцев. Большой формат. Цена за 50 шт.', 'fa-calendar', 1, 111)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('calendar_wall_a2', 18000.00, 9000.00, 9000.00, 100.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_a2_50', 'calendar_wall_a2', '50 шт', 50, 1.00, 0),
('cal_a2_100', 'calendar_wall_a2', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Квартальный календарь (трио)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('calendar_quarterly', 'Квартальный календарь (трио)', 'Календари', 'Квартальный календарь с тремя блоками. Популярен в офисах. Цена за 50 шт.', 'fa-calendar-alt', 1, 112)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('calendar_quarterly', 8000.00, 4000.00, 4000.00, 100.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_q_50', 'calendar_quarterly', '50 шт', 50, 1.00, 0),
('cal_q_100', 'calendar_quarterly', '100 шт', 100, 0.90, 0),
('cal_q_300', 'calendar_quarterly', '300 шт', 300, 0.75, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Карманный календарь
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('calendar_pocket', 'Карманный календарь', 'Календари', 'Компактный карманный календарь. Цена за 100 шт.', 'fa-calendar-day', 1, 113)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('calendar_pocket', 800.00, 300.00, 500.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_p_100', 'calendar_pocket', '100 шт', 100, 1.00, 0),
('cal_p_500', 'calendar_pocket', '500 шт', 500, 0.75, 0),
('cal_p_1000', 'calendar_pocket', '1000 шт', 1000, 0.60, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Дипломы и сертификаты
-- ============================================

-- Услуга: Диплом A4 простой
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('diploma_simple', 'Диплом А4 простой', 'Дипломы и сертификаты', 'Простой диплом формата А4 на стандартной бумаге.', 'fa-award', 1, 120)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('diploma_simple', 50.00, 15.00, 35.00, 233.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('dip_s_1', 'diploma_simple', '1 шт', 1, 1.00, 0),
('dip_s_10', 'diploma_simple', '10 шт', 10, 0.90, 0),
('dip_s_50', 'diploma_simple', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Диплом A4 на дизайнерской бумаге
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('diploma_designer', 'Диплом А4 на дизайнерской бумаге', 'Дипломы и сертификаты', 'Диплом формата А4 на дизайнерской бумаге премиум качества.', 'fa-award', 1, 121)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('diploma_designer', 100.00, 35.00, 65.00, 185.71)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('dip_d_1', 'diploma_designer', '1 шт', 1, 1.00, 0),
('dip_d_10', 'diploma_designer', '10 шт', 10, 0.85, 0),
('dip_d_50', 'diploma_designer', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Сертификат A4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('certificate', 'Сертификат А4', 'Дипломы и сертификаты', 'Сертификат участника, победителя, благодарность.', 'fa-certificate', 1, 122)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('certificate', 80.00, 25.00, 55.00, 220.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cert_1', 'certificate', '1 шт', 1, 1.00, 0),
('cert_10', 'certificate', '10 шт', 10, 0.85, 0),
('cert_50', 'certificate', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Открытки и приглашения
-- ============================================

-- Услуга: Открытки A6 односложные
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('postcard_a6_single', 'Открытки А6 односложные', 'Открытки', 'Открытки формата А6 односложные. Цена за 50 шт.', 'fa-envelope', 1, 130)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('postcard_a6_single', 1500.00, 600.00, 900.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('pc_a6_50', 'postcard_a6_single', '50 шт', 50, 1.00, 0),
('pc_a6_100', 'postcard_a6_single', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Открытки Евро двусложные
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('postcard_euro_double', 'Открытки Евро двусложные', 'Открытки', 'Открытки формата Евро двусложные с биговкой. Цена за 50 шт.', 'fa-envelope', 1, 131)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('postcard_euro_double', 2500.00, 1000.00, 1500.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('pc_euro_50', 'postcard_euro_double', '50 шт', 50, 1.00, 0),
('pc_euro_100', 'postcard_euro_double', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Свадебные приглашения (стандарт)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('wedding_invite_std', 'Свадебные приглашения (стандарт)', 'Открытки', 'Свадебные приглашения стандартное оформление. Цена за 50 шт.', 'fa-heart', 1, 132)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('wedding_invite_std', 3500.00, 1500.00, 2000.00, 133.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('wed_std_50', 'wedding_invite_std', '50 шт', 50, 1.00, 0),
('wed_std_100', 'wedding_invite_std', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Свадебные приглашения (премиум)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('wedding_invite_premium', 'Свадебные приглашения (премиум)', 'Открытки', 'Свадебные приглашения премиум с фольгой, конгревом. Цена за 50 шт.', 'fa-heart', 1, 133)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('wedding_invite_premium', 7000.00, 3000.00, 4000.00, 133.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('wed_prem_50', 'wedding_invite_premium', '50 шт', 50, 1.00, 0),
('wed_prem_100', 'wedding_invite_premium', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Дизайн-услуги
-- ============================================

-- Услуга: Дизайн визитки
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_business_card', 'Дизайн визитки', 'Дизайн', 'Разработка дизайна визитки с нуля или по вашим пожеланиям.', 'fa-palette', 1, 140)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('design_business_card', 1500.00, 500.00, 1000.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Дизайн листовки/флаера
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_flyer', 'Дизайн листовки/флаера', 'Дизайн', 'Разработка яркого и эффективного дизайна листовки.', 'fa-palette', 1, 141)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('design_flyer', 2000.00, 800.00, 1200.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Дизайн буклета (6 полос)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_booklet', 'Дизайн буклета (6 полос)', 'Дизайн', 'Дизайн буклета с версткой на 6 полос.', 'fa-palette', 1, 142)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('design_booklet', 4000.00, 1500.00, 2500.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Дизайн логотипа
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_logo', 'Дизайн логотипа', 'Дизайн', 'Разработка уникального логотипа для вашего бренда.', 'fa-palette', 1, 143)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('design_logo', 8000.00, 3000.00, 5000.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Разработка фирменного стиля
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_branding', 'Разработка фирменного стиля', 'Дизайн', 'Полная разработка фирменного стиля: логотип, визитки, бланки, конверты.', 'fa-palette', 1, 144)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('design_branding', 25000.00, 10000.00, 15000.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Верстка макета
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_layout', 'Верстка макета', 'Дизайн', 'Профессиональная верстка макета по вашим материалам.', 'fa-file-alt', 1, 145)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('design_layout', 500.00, 200.00, 300.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Подготовка к печати
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_prepress', 'Подготовка к печати', 'Дизайн', 'Подготовка файлов к печати: проверка, вылеты, цветокоррекция.', 'fa-check-circle', 1, 146)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('design_prepress', 300.00, 100.00, 200.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- ============================================
-- КАТЕГОРИЯ: Дополнительные услуги
-- ============================================

-- Услуга: Доставка по Москве (до 10 км)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('delivery_moscow_10km', 'Доставка по Москве (до 10 км)', 'Дополнительные услуги', 'Доставка готового заказа по Москве в пределах 10 км от офиса.', 'fa-truck', 1, 150)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('delivery_moscow_10km', 400.00, 200.00, 200.00, 100.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Доставка МО (до 30 км)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('delivery_mo_30km', 'Доставка МО (до 30 км)', 'Дополнительные услуги', 'Доставка готового заказа по Московской области до 30 км.', 'fa-truck', 1, 151)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('delivery_mo_30km', 800.00, 350.00, 450.00, 128.57)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Набор текста (1 страница A4)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('typing_a4', 'Набор текста (1 стр А4)', 'Дополнительные услуги', 'Ручной набор текста с рукописного/печатного текста. За 1 страницу.', 'fa-keyboard', 1, 152)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('typing_a4', 100.00, 0.00, 100.00, 0.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- ============================================
-- Конец миграции 007 (Часть 4 - ФИНАЛ)
-- ============================================
