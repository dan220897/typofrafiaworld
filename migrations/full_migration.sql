-- ============================================
-- ПОЛНАЯ МИГРАЦИЯ: Каталог услуг типографии
-- Дата: 2026-01-09
-- Описание: Объединенная миграция всех услуг
-- ============================================

-- ============================================
-- ШАГ 1: Создание структуры таблиц
-- ============================================

-- Таблица: service_base_prices (базовые цены услуг)
CREATE TABLE IF NOT EXISTS `service_base_prices` (
  `service_id` varchar(50) NOT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Таблица: service_sizes (размеры)
CREATE TABLE IF NOT EXISTS `service_sizes` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Таблица: service_density (плотность бумаги)
CREATE TABLE IF NOT EXISTS `service_density` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Таблица: service_sides (стороны печати)
CREATE TABLE IF NOT EXISTS `service_sides` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `multiplier` decimal(5,2) DEFAULT '1.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Таблица: service_quantities (тиражи)
CREATE TABLE IF NOT EXISTS `service_quantities` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `multiplier` decimal(5,2) DEFAULT '1.00',
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Таблица: service_lamination (ламинация)
CREATE TABLE IF NOT EXISTS `service_lamination` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Обновляем таблицу services (добавляем поля, если их нет)
-- Для MySQL 5.x используем процедуры для безопасного добавления колонок

DELIMITER $$

DROP PROCEDURE IF EXISTS add_column_if_not_exists$$
CREATE PROCEDURE add_column_if_not_exists()
BEGIN
  -- Добавляем category
  IF NOT EXISTS(
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='services' AND COLUMN_NAME='category'
  ) THEN
    ALTER TABLE `services` ADD COLUMN `category` VARCHAR(100) NULL AFTER `label`;
  END IF;

  -- Добавляем description
  IF NOT EXISTS(
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='services' AND COLUMN_NAME='description'
  ) THEN
    ALTER TABLE `services` ADD COLUMN `description` TEXT NULL AFTER `category`;
  END IF;

  -- Добавляем is_active
  IF NOT EXISTS(
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='services' AND COLUMN_NAME='is_active'
  ) THEN
    ALTER TABLE `services` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`;
  END IF;

  -- Добавляем sort_order
  IF NOT EXISTS(
    SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='services' AND COLUMN_NAME='sort_order'
  ) THEN
    ALTER TABLE `services` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0 AFTER `is_active`;
  END IF;
END$$

DELIMITER ;

CALL add_column_if_not_exists();
DROP PROCEDURE IF EXISTS add_column_if_not_exists;

-- ============================================
-- ШАГ 2: КАТЕГОРИЯ - Печать документов
-- ============================================

-- Услуга: Черно-белая печать A4
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_bw_a4', 'Ч/Б печать А4', 'Печать документов', 'Черно-белая печать документов формата А4', '📄', 1, 1);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_bw_a4', 3.00);

INSERT IGNORE INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_bw_a4_1side', 'print_bw_a4', 'Односторонняя', 1.00),
('print_bw_a4_2side', 'print_bw_a4', 'Двусторонняя', 1.60);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('print_bw_a4_1', 'print_bw_a4', '1 лист', 1, 1.00, 0),
('print_bw_a4_10', 'print_bw_a4', '10 листов', 10, 0.95, 0),
('print_bw_a4_50', 'print_bw_a4', '50 листов', 50, 0.90, 0),
('print_bw_a4_100', 'print_bw_a4', '100 листов', 100, 0.85, 0);

-- Услуга: Цветная печать A4
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_color_a4', 'Цветная печать А4', 'Печать документов', 'Цветная печать документов формата А4', '🎨', 1, 2);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_color_a4', 15.00);

INSERT IGNORE INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_color_a4_1side', 'print_color_a4', 'Односторонняя', 1.00),
('print_color_a4_2side', 'print_color_a4', 'Двусторонняя', 1.65);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('print_color_a4_1', 'print_color_a4', '1 лист', 1, 1.00, 0),
('print_color_a4_10', 'print_color_a4', '10 листов', 10, 0.95, 0),
('print_color_a4_50', 'print_color_a4', '50 листов', 50, 0.90, 0),
('print_color_a4_100', 'print_color_a4', '100 листов', 100, 0.85, 0);

-- Услуга: Ч/Б печать A3
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_bw_a3', 'Ч/Б печать А3', 'Печать документов', 'Черно-белая печать документов формата А3', '📄', 1, 3);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_bw_a3', 10.00);

INSERT IGNORE INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_bw_a3_1side', 'print_bw_a3', 'Односторонняя', 1.00),
('print_bw_a3_2side', 'print_bw_a3', 'Двусторонняя', 1.50);

-- Услуга: Цветная печать A3
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_color_a3', 'Цветная печать А3', 'Печать документов', 'Цветная печать документов формата А3', '🎨', 1, 4);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('print_color_a3', 40.00);

INSERT IGNORE INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('print_color_a3_1side', 'print_color_a3', 'Односторонняя', 1.00),
('print_color_a3_2side', 'print_color_a3', 'Двусторонняя', 1.50);

-- ============================================
-- КАТЕГОРИЯ: Копировальные услуги
-- ============================================

-- Услуга: Ксерокопия A4 ч/б
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('copy_bw_a4', 'Ксерокопия А4 ч/б', 'Копирование', 'Черно-белое копирование формата А4', '📋', 1, 10);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('copy_bw_a4', 3.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('copy_bw_a4_1', 'copy_bw_a4', '1 копия', 1, 1.00, 0),
('copy_bw_a4_10', 'copy_bw_a4', '10 копий', 10, 0.95, 0),
('copy_bw_a4_50', 'copy_bw_a4', '50 копий', 50, 0.85, 0),
('copy_bw_a4_100', 'copy_bw_a4', '100 копий', 100, 0.80, 0);

-- Услуга: Ксерокопия A4 цветная
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('copy_color_a4', 'Ксерокопия А4 цветная', 'Копирование', 'Цветное копирование формата А4', '🎨', 1, 11);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('copy_color_a4', 15.00);

-- Услуга: Копирование паспорта
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('copy_passport', 'Копирование паспорта', 'Копирование', 'Копирование всех страниц паспорта на один лист', '🪪', 1, 12);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('copy_passport', 20.00);

-- ============================================
-- КАТЕГОРИЯ: Сканирование
-- ============================================

-- Услуга: Сканирование A4
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('scan_a4', 'Сканирование А4', 'Сканирование', 'Сканирование документов формата А4 в PDF/JPG', '📷', 1, 20);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('scan_a4', 10.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('scan_a4_1', 'scan_a4', '1 страница', 1, 1.00, 0),
('scan_a4_10', 'scan_a4', '10 страниц', 10, 0.90, 0),
('scan_a4_50', 'scan_a4', '50 страниц', 50, 0.80, 0),
('scan_a4_100', 'scan_a4', '100 страниц', 100, 0.70, 0);

-- Услуга: Распознавание текста OCR
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('scan_ocr', 'Распознавание текста (OCR)', 'Сканирование', 'Сканирование с распознаванием текста в редактируемый формат', '🔍', 1, 21);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('scan_ocr', 30.00);

-- ============================================
-- КАТЕГОРИЯ: Визитные карточки
-- ============================================

-- Услуга: Визитки стандартные односторонние
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('business_cards_std_1side', 'Визитки односторонние', 'Визитки', 'Стандартные визитки 90x50мм, односторонняя печать', '💼', 1, 30);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('business_cards_std_1side', 5.00);

INSERT IGNORE INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('bc_std_250', 'business_cards_std_1side', '250 г/м²', 0),
('bc_std_300', 'business_cards_std_1side', '300 г/м²', 100),
('bc_std_350', 'business_cards_std_1side', '350 г/м²', 200);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_std_100', 'business_cards_std_1side', '100 шт', 100, 1.00, 0),
('bc_std_200', 'business_cards_std_1side', '200 шт', 200, 0.90, 0),
('bc_std_300', 'business_cards_std_1side', '300 шт', 300, 0.85, 0),
('bc_std_500', 'business_cards_std_1side', '500 шт', 500, 0.75, 0),
('bc_std_1000', 'business_cards_std_1side', '1000 шт', 1000, 0.60, 0);

-- Услуга: Визитки двусторонние
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('business_cards_std_2side', 'Визитки двусторонние', 'Визитки', 'Стандартные визитки 90x50мм, двусторонняя печать', '💼', 1, 31);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('business_cards_std_2side', 7.00);

INSERT IGNORE INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('bc_2side_250', 'business_cards_std_2side', '250 г/м²', 0),
('bc_2side_300', 'business_cards_std_2side', '300 г/м²', 100),
('bc_2side_350', 'business_cards_std_2side', '350 г/м²', 200);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_2side_100', 'business_cards_std_2side', '100 шт', 100, 1.00, 0),
('bc_2side_200', 'business_cards_std_2side', '200 шт', 200, 0.92, 0),
('bc_2side_300', 'business_cards_std_2side', '300 шт', 300, 0.87, 0),
('bc_2side_500', 'business_cards_std_2side', '500 шт', 500, 0.78, 0),
('bc_2side_1000', 'business_cards_std_2side', '1000 шт', 1000, 0.65, 0);

-- Услуга: Визитки с ламинацией
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('business_cards_laminated', 'Визитки с ламинацией', 'Визитки', 'Визитки с матовой или глянцевой ламинацией', '✨', 1, 32);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('business_cards_laminated', 12.00);

INSERT IGNORE INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('bc_lam_matte', 'business_cards_laminated', 'Матовая ламинация', 0),
('bc_lam_gloss', 'business_cards_laminated', 'Глянцевая ламинация', 0);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_lam_100', 'business_cards_laminated', '100 шт', 100, 1.00, 0),
('bc_lam_200', 'business_cards_laminated', '200 шт', 200, 0.93, 0),
('bc_lam_500', 'business_cards_laminated', '500 шт', 500, 0.83, 0),
('bc_lam_1000', 'business_cards_laminated', '1000 шт', 1000, 0.70, 0);

-- ============================================
-- КАТЕГОРИЯ: Листовки и Флаеры
-- ============================================

-- Услуга: Листовки А6
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('flyer_a6', 'Листовки А6', 'Листовки', 'Листовки формата А6 (105x148 мм)', '📄', 1, 40);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('flyer_a6', 8.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a6_size', 'flyer_a6', '105×148 мм (A6)', 0);

INSERT IGNORE INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a6_130', 'flyer_a6', '130 г/м²', 0),
('flyer_a6_150', 'flyer_a6', '150 г/м²', 50),
('flyer_a6_200', 'flyer_a6', '200 г/м²', 100);

INSERT IGNORE INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyer_a6_1side', 'flyer_a6', 'Односторонняя', 1.00),
('flyer_a6_2side', 'flyer_a6', 'Двусторонняя', 1.50);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyer_a6_100', 'flyer_a6', '100 шт', 100, 1.00, 0),
('flyer_a6_500', 'flyer_a6', '500 шт', 500, 0.70, 0),
('flyer_a6_1000', 'flyer_a6', '1000 шт', 1000, 0.55, 0),
('flyer_a6_2000', 'flyer_a6', '2000 шт', 2000, 0.45, 0);

-- Услуга: Листовки А5
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('flyer_a5', 'Листовки А5', 'Листовки', 'Листовки формата А5 (148x210 мм)', '📄', 1, 41);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('flyer_a5', 12.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a5_size', 'flyer_a5', '148×210 мм (A5)', 0);

INSERT IGNORE INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a5_130', 'flyer_a5', '130 г/м²', 0),
('flyer_a5_150', 'flyer_a5', '150 г/м²', 80),
('flyer_a5_200', 'flyer_a5', '200 г/м²', 150);

INSERT IGNORE INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyer_a5_1side', 'flyer_a5', 'Односторонняя', 1.00),
('flyer_a5_2side', 'flyer_a5', 'Двусторонняя', 1.50);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyer_a5_100', 'flyer_a5', '100 шт', 100, 1.00, 0),
('flyer_a5_500', 'flyer_a5', '500 шт', 500, 0.75, 0),
('flyer_a5_1000', 'flyer_a5', '1000 шт', 1000, 0.60, 0),
('flyer_a5_2000', 'flyer_a5', '2000 шт', 2000, 0.50, 0);

-- Услуга: Листовки А4
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('flyer_a4', 'Листовки А4', 'Листовки', 'Листовки формата А4 (210x297 мм)', '📄', 1, 42);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('flyer_a4', 18.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a4_size', 'flyer_a4', '210×297 мм (A4)', 0);

INSERT IGNORE INTO `service_density` (`id`, `service_id`, `label`, `price`) VALUES
('flyer_a4_130', 'flyer_a4', '130 г/м²', 0),
('flyer_a4_150', 'flyer_a4', '150 г/м²', 100),
('flyer_a4_200', 'flyer_a4', '200 г/м²', 180);

INSERT IGNORE INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyer_a4_1side', 'flyer_a4', 'Односторонняя', 1.00),
('flyer_a4_2side', 'flyer_a4', 'Двусторонняя', 1.50);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyer_a4_100', 'flyer_a4', '100 шт', 100, 1.00, 0),
('flyer_a4_500', 'flyer_a4', '500 шт', 500, 0.80, 0),
('flyer_a4_1000', 'flyer_a4', '1000 шт', 1000, 0.65, 0),
('flyer_a4_2000', 'flyer_a4', '2000 шт', 2000, 0.55, 0);

-- ============================================
-- КАТЕГОРИЯ: Плакаты и Постеры
-- ============================================

-- Услуга: Плакат А3
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a3', 'Плакат А3', 'Плакаты', 'Плакат формата А3 (297x420 мм)', '🖼', 1, 50);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a3', 150.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a3_size', 'poster_a3', '297×420 мм (A3)', 0);

INSERT IGNORE INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a3_no', 'poster_a3', 'Без ламинации', 0),
('poster_a3_lam', 'poster_a3', 'С ламинацией', 150);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a3_1', 'poster_a3', '1 шт', 1, 1.00, 0),
('poster_a3_5', 'poster_a3', '5 шт', 5, 0.85, 0),
('poster_a3_10', 'poster_a3', '10 шт', 10, 0.75, 0);

-- Услуга: Плакат А2
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a2', 'Плакат А2', 'Плакаты', 'Плакат формата А2 (420x594 мм)', '🖼', 1, 51);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a2', 350.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a2_size', 'poster_a2', '420×594 мм (A2)', 0);

INSERT IGNORE INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a2_no', 'poster_a2', 'Без ламинации', 0),
('poster_a2_lam', 'poster_a2', 'С ламинацией', 250);

-- Услуга: Плакат А1
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a1', 'Плакат А1', 'Плакаты', 'Плакат формата А1 (594x841 мм)', '🖼', 1, 52);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a1', 700.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a1_size', 'poster_a1', '594×841 мм (A1)', 0);

INSERT IGNORE INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a1_no', 'poster_a1', 'Без ламинации', 0),
('poster_a1_lam', 'poster_a1', 'С ламинацией', 500);

-- Услуга: Плакат А0
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a0', 'Плакат А0', 'Плакаты', 'Плакат формата А0 (841x1189 мм)', '🖼', 1, 53);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a0', 1500.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a0_size', 'poster_a0', '841×1189 мм (A0)', 0);

INSERT IGNORE INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a0_no', 'poster_a0', 'Без ламинации', 0),
('poster_a0_lam', 'poster_a0', 'С ламинацией', 1000);

-- ============================================
-- КАТЕГОРИЯ: Наклейки и Стикеры
-- ============================================

-- Услуга: Бумажные наклейки 50x50
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('stickers_paper_50', 'Бумажные наклейки 50×50', 'Наклейки', 'Круглые или квадратные наклейки 50x50 мм', '🏷', 1, 60);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('stickers_paper_50', 8.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('stickers_50_circle', 'stickers_paper_50', 'Круглые 50 мм', 0),
('stickers_50_square', 'stickers_paper_50', 'Квадратные 50×50 мм', 0);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_50_100', 'stickers_paper_50', '100 шт', 100, 1.00, 0),
('stickers_50_500', 'stickers_paper_50', '500 шт', 500, 0.65, 0),
('stickers_50_1000', 'stickers_paper_50', '1000 шт', 1000, 0.50, 0);

-- Услуга: Бумажные наклейки 100x100
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('stickers_paper_100', 'Бумажные наклейки 100×100', 'Наклейки', 'Круглые или квадратные наклейки 100x100 мм', '🏷', 1, 61);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('stickers_paper_100', 15.00);

INSERT IGNORE INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('stickers_100_circle', 'stickers_paper_100', 'Круглые 100 мм', 0),
('stickers_100_square', 'stickers_paper_100', 'Квадратные 100×100 мм', 0);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_100_100', 'stickers_paper_100', '100 шт', 100, 1.00, 0),
('stickers_100_500', 'stickers_paper_100', '500 шт', 500, 0.70, 0),
('stickers_100_1000', 'stickers_paper_100', '1000 шт', 1000, 0.55, 0);

-- Услуга: Виниловые наклейки 50x50
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('stickers_vinyl_50', 'Виниловые наклейки 50×50', 'Наклейки', 'Водостойкие виниловые наклейки 50x50 мм', '💧', 1, 62);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('stickers_vinyl_50', 15.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_vinyl_50_100', 'stickers_vinyl_50', '100 шт', 100, 1.00, 0),
('stickers_vinyl_50_500', 'stickers_vinyl_50', '500 шт', 500, 0.70, 0),
('stickers_vinyl_50_1000', 'stickers_vinyl_50', '1000 шт', 1000, 0.55, 0);

-- Услуга: Стикерпаки
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('sticker_packs', 'Стикерпаки', 'Наклейки', 'Наборы стикеров 10-20 штук', '📦', 1, 63);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('sticker_packs', 100.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('sticker_pack_50', 'sticker_packs', '50 наборов', 50, 1.00, 0),
('sticker_pack_100', 'sticker_packs', '100 наборов', 100, 0.85, 0),
('sticker_pack_200', 'sticker_packs', '200 наборов', 200, 0.75, 0);

-- ============================================
-- КАТЕГОРИЯ: Широкоформатная печать
-- ============================================

-- Услуга: Баннер 1x1м
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('banner_1x1', 'Баннер 1×1 м', 'Баннеры', 'Виниловый баннер 1x1 метр', '🪧', 1, 70);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('banner_1x1', 1200.00);

-- Услуга: Баннер 2x1м
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('banner_2x1', 'Баннер 2×1 м', 'Баннеры', 'Виниловый баннер 2x1 метр', '🪧', 1, 71);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('banner_2x1', 2000.00);

-- Услуга: Баннер 3x2м
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('banner_3x2', 'Баннер 3×2 м', 'Баннеры', 'Виниловый баннер 3x2 метра', '🪧', 1, 72);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('banner_3x2', 5000.00);

-- Услуга: Roll-up стенд 0.8x2м
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('rollup_08x2', 'Roll-up стенд 0.8×2 м', 'Баннеры', 'Мобильный стенд с печатью 0.8x2 метра', '📋', 1, 73);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('rollup_08x2', 3500.00);

-- Услуга: Roll-up стенд 1x2м
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('rollup_1x2', 'Roll-up стенд 1×2 м', 'Баннеры', 'Мобильный стенд с печатью 1x2 метра', '📋', 1, 74);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('rollup_1x2', 4500.00);

-- ============================================
-- КАТЕГОРИЯ: Чертежи
-- ============================================

-- Услуга: Чертежи А3 ч/б
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a3_bw', 'Чертеж А3 ч/б', 'Чертежи', 'Печать чертежей А3 черно-белая', '📐', 1, 80);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a3_bw', 50.00);

-- Услуга: Чертежи А3 цветные
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a3_color', 'Чертеж А3 цветной', 'Чертежи', 'Печать чертежей А3 цветная', '📐', 1, 81);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a3_color', 150.00);

-- Услуга: Чертежи А2 ч/б
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a2_bw', 'Чертеж А2 ч/б', 'Чертежи', 'Печать чертежей А2 черно-белая', '📐', 1, 82);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a2_bw', 100.00);

-- Услуга: Чертежи А1 ч/б
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a1_bw', 'Чертеж А1 ч/б', 'Чертежи', 'Печать чертежей А1 черно-белая', '📐', 1, 83);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a1_bw', 200.00);

-- Услуга: Чертежи А0 ч/б
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a0_bw', 'Чертеж А0 ч/б', 'Чертежи', 'Печать чертежей А0 черно-белая', '📐', 1, 84);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a0_bw', 400.00);

-- ============================================
-- КАТЕГОРИЯ: Фотоуслуги
-- ============================================

-- Услуга: Печать фото 10x15
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_10x15', 'Фото 10×15 см', 'Фотопечать', 'Печать фотографий формата 10x15 см', '📸', 1, 90);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_10x15', 15.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('photo_10x15_1', 'photo_10x15', '1 фото', 1, 1.00, 0),
('photo_10x15_10', 'photo_10x15', '10 фото', 10, 0.90, 0),
('photo_10x15_50', 'photo_10x15', '50 фото', 50, 0.80, 0);

-- Услуга: Печать фото 15x20
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_15x20', 'Фото 15×20 см', 'Фотопечать', 'Печать фотографий формата 15x20 см', '📸', 1, 91);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_15x20', 30.00);

-- Услуга: Печать фото 20x30
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_20x30', 'Фото 20×30 см', 'Фотопечать', 'Печать фотографий формата 20x30 см', '📸', 1, 92);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_20x30', 80.00);

-- Услуга: Фото на документы
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_passport', 'Фото на документы', 'Фотопечать', 'Фотография на паспорт, визу (4-6 фото)', '🪪', 1, 93);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_passport', 200.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('photo_pass_4', 'photo_passport', '4 фото (паспорт)', 4, 1.00, 0),
('photo_pass_6', 'photo_passport', '6 фото (виза)', 6, 1.15, 0);

-- ============================================
-- КАТЕГОРИЯ: Календари
-- ============================================

-- Услуга: Календари квартальные
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('calendar_quarterly', 'Календарь квартальный', 'Календари', 'Квартальный календарь-трио', '📅', 1, 100);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('calendar_quarterly', 160.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_q_50', 'calendar_quarterly', '50 шт', 50, 1.00, 0),
('cal_q_100', 'calendar_quarterly', '100 шт', 100, 0.88, 0),
('cal_q_300', 'calendar_quarterly', '300 шт', 300, 0.73, 0);

-- Услуга: Календари настенные перекидные А3
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('calendar_wall_a3', 'Календарь настенный А3', 'Календари', 'Настенный перекидной календарь А3, 12 листов', '📅', 1, 101);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('calendar_wall_a3', 240.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_wall_a3_50', 'calendar_wall_a3', '50 шт', 50, 1.00, 0),
('cal_wall_a3_100', 'calendar_wall_a3', '100 шт', 100, 0.83, 0);

-- Услуга: Карманные календари
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('calendar_pocket', 'Календарь карманный', 'Календари', 'Карманный календарик', '🗓', 1, 102);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('calendar_pocket', 8.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_pocket_100', 'calendar_pocket', '100 шт', 100, 1.00, 0),
('cal_pocket_500', 'calendar_pocket', '500 шт', 500, 0.65, 0),
('cal_pocket_1000', 'calendar_pocket', '1000 шт', 1000, 0.55, 0);

-- ============================================
-- КАТЕГОРИЯ: Дипломы и Сертификаты
-- ============================================

-- Услуга: Диплом А4 простой
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('diploma_a4_simple', 'Диплом А4 простой', 'Дипломы', 'Печать диплома на обычной бумаге А4', '🏆', 1, 110);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('diploma_a4_simple', 50.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('diploma_simple_1', 'diploma_a4_simple', '1 шт', 1, 1.00, 0),
('diploma_simple_10', 'diploma_a4_simple', '10 шт', 10, 0.85, 0),
('diploma_simple_50', 'diploma_a4_simple', '50 шт', 50, 0.65, 0);

-- Услуга: Диплом А4 на дизайнерской бумаге
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('diploma_a4_premium', 'Диплом А4 премиум', 'Дипломы', 'Печать диплома на дизайнерской бумаге', '🏆', 1, 111);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('diploma_a4_premium', 100.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('diploma_premium_1', 'diploma_a4_premium', '1 шт', 1, 1.00, 0),
('diploma_premium_10', 'diploma_a4_premium', '10 шт', 10, 0.85, 0),
('diploma_premium_50', 'diploma_a4_premium', '50 шт', 50, 0.70, 0);

-- Услуга: Сертификат А4
INSERT IGNORE INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('certificate_a4', 'Сертификат А4', 'Дипломы', 'Печать сертификата на плотной бумаге', '📜', 1, 112);

INSERT IGNORE INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('certificate_a4', 80.00);

INSERT IGNORE INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cert_1', 'certificate_a4', '1 шт', 1, 1.00, 0),
('cert_10', 'certificate_a4', '10 шт', 10, 0.88, 0),
('cert_50', 'certificate_a4', '50 шт', 50, 0.70, 0);

-- ============================================
-- КОНЕЦ МИГРАЦИИ
-- ============================================
