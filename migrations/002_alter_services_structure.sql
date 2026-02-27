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
