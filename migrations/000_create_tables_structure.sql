-- ============================================
-- МИГРАЦИЯ 000: Создание структуры таблиц для каталога услуг
-- Дата: 2026-01-09
-- Описание: Создает все необходимые таблицы параметров
-- ============================================

-- Проверяем и создаем недостающие таблицы

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
ALTER TABLE `services`
ADD COLUMN IF NOT EXISTS `category` VARCHAR(100) NULL AFTER `label`,
ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `category`,
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`,
ADD COLUMN IF NOT EXISTS `sort_order` INT NOT NULL DEFAULT 0 AFTER `is_active`;

-- ============================================
-- Конец миграции 000
-- ============================================
