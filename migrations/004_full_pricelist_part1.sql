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
