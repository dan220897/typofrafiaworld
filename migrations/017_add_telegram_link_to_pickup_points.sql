-- ============================================
-- МИГРАЦИЯ: Добавление telegram_link в pickup_points
-- Дата: 2026-02-03
-- Описание: Добавляет поле telegram_link для ссылки на Telegram каждой точки
-- ============================================

-- Добавляем поле telegram_link
ALTER TABLE `pickup_points`
ADD COLUMN `telegram_link` VARCHAR(255) DEFAULT NULL COMMENT 'Ссылка на Telegram точки' AFTER `description`;

-- Заполняем ссылки для существующих точек
UPDATE `pickup_points` SET `telegram_link` = 'https://t.me/Typografia_ru' WHERE `name` LIKE '%Белорусская%' OR `name` LIKE '%белорусская%';
UPDATE `pickup_points` SET `telegram_link` = 'https://t.me/byetat1905' WHERE `name` LIKE '%1905%';
UPDATE `pickup_points` SET `telegram_link` = 'https://t.me/Managermoscoweta' WHERE `name` LIKE '%Патрик%' OR `name` LIKE '%патрик%' OR `name` LIKE '%Патриарш%';
