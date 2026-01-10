-- ============================================
-- МИГРАЦИЯ 003: Добавление себестоимости в таблицы
-- Дата: 2026-01-10
-- Описание: Добавляет поля cost_price для учета себестоимости
-- ============================================

-- Добавляем поле себестоимости в базовые цены
ALTER TABLE `service_base_prices`
ADD COLUMN IF NOT EXISTS `cost_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `base_price`,
ADD COLUMN IF NOT EXISTS `margin` DECIMAL(10,2) DEFAULT 0.00 AFTER `cost_price`,
ADD COLUMN IF NOT EXISTS `margin_percent` DECIMAL(6,2) DEFAULT 0.00 AFTER `margin`;

-- Добавляем поле себестоимости в размеры
ALTER TABLE `service_sizes`
ADD COLUMN IF NOT EXISTS `cost_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `price`;

-- Добавляем поле себестоимости в плотность
ALTER TABLE `service_density`
ADD COLUMN IF NOT EXISTS `cost_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `price`;

-- Добавляем поле себестоимости в тиражи
ALTER TABLE `service_quantities`
ADD COLUMN IF NOT EXISTS `cost_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `price`;

-- Добавляем поле себестоимости в ламинацию
ALTER TABLE `service_lamination`
ADD COLUMN IF NOT EXISTS `cost_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `price`;

-- ============================================
-- Конец миграции 003
-- ============================================
