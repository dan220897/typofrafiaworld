-- Исправление типа поля service_id в таблице order_items
-- Изменяем с INT на VARCHAR(50) для соответствия таблице services

-- Сначала удаляем внешний ключ, если он есть
ALTER TABLE `order_items` DROP FOREIGN KEY IF EXISTS `order_items_ibfk_2`;

-- Изменяем тип поля service_id на VARCHAR(50)
ALTER TABLE `order_items` MODIFY `service_id` VARCHAR(50) NOT NULL;

-- Восстанавливаем внешний ключ
ALTER TABLE `order_items`
ADD CONSTRAINT `order_items_ibfk_2`
FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
ON DELETE RESTRICT ON UPDATE CASCADE;

-- Проверяем изменения
DESCRIBE `order_items`;
