-- ============================================
-- МИГРАЦИЯ: Добавление полного каталога услуг типографии
-- Дата: 2026-01-09
-- Описание: Добавляет категории и все услуги из прайс-листа
-- ============================================

-- Шаг 1: Добавляем поле category в таблицу services
ALTER TABLE `services`
ADD COLUMN `category` VARCHAR(100) NULL AFTER `label`,
ADD COLUMN `description` TEXT NULL AFTER `category`,
ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`,
ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0 AFTER `is_active`;

-- Шаг 2: Добавляем поле quantity в service_quantities (если нет)
ALTER TABLE `service_quantities`
ADD COLUMN `quantity` INT DEFAULT 1 AFTER `label`,
ADD COLUMN `price` DECIMAL(10,2) DEFAULT 0.00 AFTER `quantity`;

-- Шаг 3: Добавляем таблицу для сторон печати (если нет)
CREATE TABLE IF NOT EXISTS `service_sides` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `multiplier` decimal(5,2) DEFAULT '1.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- КАТЕГОРИЯ: Печать документов
-- ============================================

-- Услуга: Черно-белая печать A4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_bw_a4', 'Ч/Б печать А4', 'Печать документов', 'Черно-белая печать документов формата А4', '📄', 1, 1);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_bw_a4', 3.00);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_bw_a4_1side', 'print_bw_a4', 'Односторонняя', 1.00),
('print_bw_a4_2side', 'print_bw_a4', 'Двусторонняя', 1.60);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('print_bw_a4_1', 'print_bw_a4', '1 лист', 1, 1.00, 0),
('print_bw_a4_10', 'print_bw_a4', '10 листов', 10, 0.95, 0),
('print_bw_a4_50', 'print_bw_a4', '50 листов', 50, 0.90, 0),
('print_bw_a4_100', 'print_bw_a4', '100 листов', 100, 0.85, 0);

-- Услуга: Цветная печать A4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_color_a4', 'Цветная печать А4', 'Печать документов', 'Цветная печать документов формата А4', '🎨', 1, 2);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_color_a4', 15.00);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_color_a4_1side', 'print_color_a4', 'Односторонняя', 1.00),
('print_color_a4_2side', 'print_color_a4', 'Двусторонняя', 1.65);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('print_color_a4_1', 'print_color_a4', '1 лист', 1, 1.00, 0),
('print_color_a4_10', 'print_color_a4', '10 листов', 10, 0.95, 0),
('print_color_a4_50', 'print_color_a4', '50 листов', 50, 0.90, 0),
('print_color_a4_100', 'print_color_a4', '100 листов', 100, 0.85, 0);

-- Услуга: Ч/Б печать A3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_bw_a3', 'Ч/Б печать А3', 'Печать документов', 'Черно-белая печать документов формата А3', '📄', 1, 3);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_bw_a3', 10.00);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_bw_a3_1side', 'print_bw_a3', 'Односторонняя', 1.00),
('print_bw_a3_2side', 'print_bw_a3', 'Двусторонняя', 1.50);

-- Услуга: Цветная печать A3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_color_a3', 'Цветная печать А3', 'Печать документов', 'Цветная печать документов формата А3', '🎨', 1, 4);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_color_a3', 40.00);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_color_a3_1side', 'print_color_a3', 'Односторонняя', 1.00),
('print_color_a3_2side', 'print_color_a3', 'Двусторонняя', 1.50);

-- ============================================
-- КАТЕГОРИЯ: Копировальные услуги
-- ============================================

-- Услуга: Ксерокопия A4 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('copy_bw_a4', 'Ксерокопия А4 ч/б', 'Копирование', 'Черно-белое копирование формата А4', '📋', 1, 10);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('copy_bw_a4', 3.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('copy_bw_a4_1', 'copy_bw_a4', '1 копия', 1, 1.00, 0),
('copy_bw_a4_10', 'copy_bw_a4', '10 копий', 10, 0.95, 0),
('copy_bw_a4_50', 'copy_bw_a4', '50 копий', 50, 0.85, 0),
('copy_bw_a4_100', 'copy_bw_a4', '100 копий', 100, 0.80, 0);

-- Услуга: Ксерокопия A4 цветная
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('copy_color_a4', 'Ксерокопия А4 цветная', 'Копирование', 'Цветное копирование формата А4', '🎨', 1, 11);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('copy_color_a4', 15.00);

-- Услуга: Копирование паспорта
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('copy_passport', 'Копирование паспорта', 'Копирование', 'Копирование всех страниц паспорта на один лист', '🪪', 1, 12);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('copy_passport', 20.00);

-- ============================================
-- КАТЕГОРИЯ: Сканирование
-- ============================================

-- Услуга: Сканирование A4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('scan_a4', 'Сканирование А4', 'Сканирование', 'Сканирование документов формата А4 в PDF/JPG', '📷', 1, 20);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('scan_a4', 10.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('scan_a4_1', 'scan_a4', '1 страница', 1, 1.00, 0),
('scan_a4_10', 'scan_a4', '10 страниц', 10, 0.90, 0),
('scan_a4_50', 'scan_a4', '50 страниц', 50, 0.80, 0),
('scan_a4_100', 'scan_a4', '100 страниц', 100, 0.70, 0);

-- Услуга: Распознавание текста OCR
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('scan_ocr', 'Распознавание текста (OCR)', 'Сканирование', 'Сканирование с распознаванием текста в редактируемый формат', '🔍', 1, 21);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('scan_ocr', 30.00);

-- ============================================
-- КАТЕГОРИЯ: Визитные карточки
-- ============================================

-- Услуга: Визитки стандартные односторонние
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('business_cards_std_1side', 'Визитки односторонние', 'Визитки', 'Стандартные визитки 90x50мм, односторонняя печать', '💼', 1, 30);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('business_cards_std_1side', 5.00);

INSERT INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('bc_std_250', 'business_cards_std_1side', '250 г/м²', 0),
('bc_std_300', 'business_cards_std_1side', '300 г/м²', 100),
('bc_std_350', 'business_cards_std_1side', '350 г/м²', 200);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_std_100', 'business_cards_std_1side', '100 шт', 100, 1.00, 0),
('bc_std_200', 'business_cards_std_1side', '200 шт', 200, 0.90, 0),
('bc_std_300', 'business_cards_std_1side', '300 шт', 300, 0.85, 0),
('bc_std_500', 'business_cards_std_1side', '500 шт', 500, 0.75, 0),
('bc_std_1000', 'business_cards_std_1side', '1000 шт', 1000, 0.60, 0);

-- Услуга: Визитки двусторонние
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('business_cards_std_2side', 'Визитки двусторонние', 'Визитки', 'Стандартные визитки 90x50мм, двусторонняя печать', '💼', 1, 31);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('business_cards_std_2side', 7.00);

INSERT INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('bc_2side_250', 'business_cards_std_2side', '250 г/м²', 0),
('bc_2side_300', 'business_cards_std_2side', '300 г/м²', 100),
('bc_2side_350', 'business_cards_std_2side', '350 г/м²', 200);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_2side_100', 'business_cards_std_2side', '100 шт', 100, 1.00, 0),
('bc_2side_200', 'business_cards_std_2side', '200 шт', 200, 0.92, 0),
('bc_2side_300', 'business_cards_std_2side', '300 шт', 300, 0.87, 0),
('bc_2side_500', 'business_cards_std_2side', '500 шт', 500, 0.78, 0),
('bc_2side_1000', 'business_cards_std_2side', '1000 шт', 1000, 0.65, 0);

-- Услуга: Визитки с ламинацией
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('business_cards_laminated', 'Визитки с ламинацией', 'Визитки', 'Визитки с матовой или глянцевой ламинацией', '✨', 1, 32);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('business_cards_laminated', 12.00);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('bc_lam_matte', 'business_cards_laminated', 'Матовая ламинация', 0),
('bc_lam_gloss', 'business_cards_laminated', 'Глянцевая ламинация', 0);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_lam_100', 'business_cards_laminated', '100 шт', 100, 1.00, 0),
('bc_lam_200', 'business_cards_laminated', '200 шт', 200, 0.93, 0),
('bc_lam_500', 'business_cards_laminated', '500 шт', 500, 0.83, 0),
('bc_lam_1000', 'business_cards_laminated', '1000 шт', 1000, 0.70, 0);

-- ============================================
-- КАТЕГОРИЯ: Листовки и Флаеры
-- ============================================

-- Услуга: Листовки А6
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('flyer_a6', 'Листовки А6', 'Листовки', 'Листовки формата А6 (105x148 мм)', '📄', 1, 40);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('flyer_a6', 8.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a6_size', 'flyer_a6', '105×148 мм (A6)', 0);

INSERT INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a6_130', 'flyer_a6', '130 г/м²', 0),
('flyer_a6_150', 'flyer_a6', '150 г/м²', 50),
('flyer_a6_200', 'flyer_a6', '200 г/м²', 100);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyer_a6_1side', 'flyer_a6', 'Односторонняя', 1.00),
('flyer_a6_2side', 'flyer_a6', 'Двусторонняя', 1.50);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyer_a6_100', 'flyer_a6', '100 шт', 100, 1.00, 0),
('flyer_a6_500', 'flyer_a6', '500 шт', 500, 0.70, 0),
('flyer_a6_1000', 'flyer_a6', '1000 шт', 1000, 0.55, 0),
('flyer_a6_2000', 'flyer_a6', '2000 шт', 2000, 0.45, 0);

-- Услуга: Листовки А5
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('flyer_a5', 'Листовки А5', 'Листовки', 'Листовки формата А5 (148x210 мм)', '📄', 1, 41);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('flyer_a5', 12.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a5_size', 'flyer_a5', '148×210 мм (A5)', 0);

INSERT INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a5_130', 'flyer_a5', '130 г/м²', 0),
('flyer_a5_150', 'flyer_a5', '150 г/м²', 80),
('flyer_a5_200', 'flyer_a5', '200 г/м²', 150);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyer_a5_1side', 'flyer_a5', 'Односторонняя', 1.00),
('flyer_a5_2side', 'flyer_a5', 'Двусторонняя', 1.50);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyer_a5_100', 'flyer_a5', '100 шт', 100, 1.00, 0),
('flyer_a5_500', 'flyer_a5', '500 шт', 500, 0.75, 0),
('flyer_a5_1000', 'flyer_a5', '1000 шт', 1000, 0.60, 0),
('flyer_a5_2000', 'flyer_a5', '2000 шт', 2000, 0.50, 0);

-- Услуга: Листовки А4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('flyer_a4', 'Листовки А4', 'Листовки', 'Листовки формата А4 (210x297 мм)', '📄', 1, 42);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('flyer_a4', 18.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a4_size', 'flyer_a4', '210×297 мм (A4)', 0);

INSERT INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a4_130', 'flyer_a4', '130 г/м²', 0),
('flyer_a4_150', 'flyer_a4', '150 г/м²', 100),
('flyer_a4_200', 'flyer_a4', '200 г/м²', 180);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyer_a4_1side', 'flyer_a4', 'Односторонняя', 1.00),
('flyer_a4_2side', 'flyer_a4', 'Двусторонняя', 1.50);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyer_a4_100', 'flyer_a4', '100 шт', 100, 1.00, 0),
('flyer_a4_500', 'flyer_a4', '500 шт', 500, 0.80, 0),
('flyer_a4_1000', 'flyer_a4', '1000 шт', 1000, 0.65, 0),
('flyer_a4_2000', 'flyer_a4', '2000 шт', 2000, 0.55, 0);

-- Продолжение следует...
-- Это первая часть миграции (основные услуги)
-- Остальные категории будут добавлены далее
