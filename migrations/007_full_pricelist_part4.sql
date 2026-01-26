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
