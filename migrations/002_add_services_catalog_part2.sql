-- ============================================
-- МИГРАЦИЯ: Часть 2 - Дополнительные услуги типографии
-- Дата: 2026-01-09
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Плакаты и Постеры
-- ============================================

-- Услуга: Плакат А3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a3', 'Плакат А3', 'Плакаты', 'Плакат формата А3 (297x420 мм)', '🖼', 1, 50);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a3', 150.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a3_size', 'poster_a3', '297×420 мм (A3)', 0);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a3_no', 'poster_a3', 'Без ламинации', 0),
('poster_a3_lam', 'poster_a3', 'С ламинацией', 150);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a3_1', 'poster_a3', '1 шт', 1, 1.00, 0),
('poster_a3_5', 'poster_a3', '5 шт', 5, 0.85, 0),
('poster_a3_10', 'poster_a3', '10 шт', 10, 0.75, 0);

-- Услуга: Плакат А2
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a2', 'Плакат А2', 'Плакаты', 'Плакат формата А2 (420x594 мм)', '🖼', 1, 51);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a2', 350.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a2_size', 'poster_a2', '420×594 мм (A2)', 0);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a2_no', 'poster_a2', 'Без ламинации', 0),
('poster_a2_lam', 'poster_a2', 'С ламинацией', 250);

-- Услуга: Плакат А1
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a1', 'Плакат А1', 'Плакаты', 'Плакат формата А1 (594x841 мм)', '🖼', 1, 52);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a1', 700.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a1_size', 'poster_a1', '594×841 мм (A1)', 0);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a1_no', 'poster_a1', 'Без ламинации', 0),
('poster_a1_lam', 'poster_a1', 'С ламинацией', 500);

-- Услуга: Плакат А0
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('poster_a0', 'Плакат А0', 'Плакаты', 'Плакат формата А0 (841x1189 мм)', '🖼', 1, 53);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('poster_a0', 1500.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a0_size', 'poster_a0', '841×1189 мм (A0)', 0);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('poster_a0_no', 'poster_a0', 'Без ламинации', 0),
('poster_a0_lam', 'poster_a0', 'С ламинацией', 1000);

-- ============================================
-- КАТЕГОРИЯ: Наклейки и Стикеры
-- ============================================

-- Услуга: Бумажные наклейки 50x50
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('stickers_paper_50', 'Бумажные наклейки 50×50', 'Наклейки', 'Круглые или квадратные наклейки 50x50 мм', '🏷', 1, 60);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('stickers_paper_50', 8.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('stickers_50_circle', 'stickers_paper_50', 'Круглые 50 мм', 0),
('stickers_50_square', 'stickers_paper_50', 'Квадратные 50×50 мм', 0);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_50_100', 'stickers_paper_50', '100 шт', 100, 1.00, 0),
('stickers_50_500', 'stickers_paper_50', '500 шт', 500, 0.65, 0),
('stickers_50_1000', 'stickers_paper_50', '1000 шт', 1000, 0.50, 0);

-- Услуга: Бумажные наклейки 100x100
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('stickers_paper_100', 'Бумажные наклейки 100×100', 'Наклейки', 'Круглые или квадратные наклейки 100x100 мм', '🏷', 1, 61);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('stickers_paper_100', 15.00);

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`) VALUES
('stickers_100_circle', 'stickers_paper_100', 'Круглые 100 мм', 0),
('stickers_100_square', 'stickers_paper_100', 'Квадратные 100×100 мм', 0);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_100_100', 'stickers_paper_100', '100 шт', 100, 1.00, 0),
('stickers_100_500', 'stickers_paper_100', '500 шт', 500, 0.70, 0),
('stickers_100_1000', 'stickers_paper_100', '1000 шт', 1000, 0.55, 0);

-- Услуга: Виниловые наклейки 50x50
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('stickers_vinyl_50', 'Виниловые наклейки 50×50', 'Наклейки', 'Водостойкие виниловые наклейки 50x50 мм', '💧', 1, 62);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('stickers_vinyl_50', 15.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_vinyl_50_100', 'stickers_vinyl_50', '100 шт', 100, 1.00, 0),
('stickers_vinyl_50_500', 'stickers_vinyl_50', '500 шт', 500, 0.70, 0),
('stickers_vinyl_50_1000', 'stickers_vinyl_50', '1000 шт', 1000, 0.55, 0);

-- Услуга: Стикерпаки
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('sticker_packs', 'Стикерпаки', 'Наклейки', 'Наборы стикеров 10-20 штук', '📦', 1, 63);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('sticker_packs', 100.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('sticker_pack_50', 'sticker_packs', '50 наборов', 50, 1.00, 0),
('sticker_pack_100', 'sticker_packs', '100 наборов', 100, 0.85, 0),
('sticker_pack_200', 'sticker_packs', '200 наборов', 200, 0.75, 0);

-- ============================================
-- КАТЕГОРИЯ: Широкоформатная печать
-- ============================================

-- Услуга: Баннер 1x1м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('banner_1x1', 'Баннер 1×1 м', 'Баннеры', 'Виниловый баннер 1x1 метр', '🪧', 1, 70);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('banner_1x1', 1200.00);

-- Услуга: Баннер 2x1м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('banner_2x1', 'Баннер 2×1 м', 'Баннеры', 'Виниловый баннер 2x1 метр', '🪧', 1, 71);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('banner_2x1', 2000.00);

-- Услуга: Баннер 3x2м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('banner_3x2', 'Баннер 3×2 м', 'Баннеры', 'Виниловый баннер 3x2 метра', '🪧', 1, 72);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('banner_3x2', 5000.00);

-- Услуга: Roll-up стенд 0.8x2м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('rollup_08x2', 'Roll-up стенд 0.8×2 м', 'Баннеры', 'Мобильный стенд с печатью 0.8x2 метра', '📋', 1, 73);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('rollup_08x2', 3500.00);

-- Услуга: Roll-up стенд 1x2м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('rollup_1x2', 'Roll-up стенд 1×2 м', 'Баннеры', 'Мобильный стенд с печатью 1x2 метра', '📋', 1, 74);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('rollup_1x2', 4500.00);

-- ============================================
-- КАТЕГОРИЯ: Чертежи
-- ============================================

-- Услуга: Чертежи А3 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a3_bw', 'Чертеж А3 ч/б', 'Чертежи', 'Печать чертежей А3 черно-белая', '📐', 1, 80);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a3_bw', 50.00);

-- Услуга: Чертежи А3 цветные
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a3_color', 'Чертеж А3 цветной', 'Чертежи', 'Печать чертежей А3 цветная', '📐', 1, 81);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a3_color', 150.00);

-- Услуга: Чертежи А2 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a2_bw', 'Чертеж А2 ч/б', 'Чертежи', 'Печать чертежей А2 черно-белая', '📐', 1, 82);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a2_bw', 100.00);

-- Услуга: Чертежи А1 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a1_bw', 'Чертеж А1 ч/б', 'Чертежи', 'Печать чертежей А1 черно-белая', '📐', 1, 83);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a1_bw', 200.00);

-- Услуга: Чертежи А0 ч/б
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('blueprint_a0_bw', 'Чертеж А0 ч/б', 'Чертежи', 'Печать чертежей А0 черно-белая', '📐', 1, 84);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('blueprint_a0_bw', 400.00);

-- ============================================
-- КАТЕГОРИЯ: Фотоуслуги
-- ============================================

-- Услуга: Печать фото 10x15
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_10x15', 'Фото 10×15 см', 'Фотопечать', 'Печать фотографий формата 10x15 см', '📸', 1, 90);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_10x15', 15.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('photo_10x15_1', 'photo_10x15', '1 фото', 1, 1.00, 0),
('photo_10x15_10', 'photo_10x15', '10 фото', 10, 0.90, 0),
('photo_10x15_50', 'photo_10x15', '50 фото', 50, 0.80, 0);

-- Услуга: Печать фото 15x20
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_15x20', 'Фото 15×20 см', 'Фотопечать', 'Печать фотографий формата 15x20 см', '📸', 1, 91);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_15x20', 30.00);

-- Услуга: Печать фото 20x30
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_20x30', 'Фото 20×30 см', 'Фотопечать', 'Печать фотографий формата 20x30 см', '📸', 1, 92);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_20x30', 80.00);

-- Услуга: Фото на документы
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_passport', 'Фото на документы', 'Фотопечать', 'Фотография на паспорт, визу (4-6 фото)', '🪪', 1, 93);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('photo_passport', 200.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('photo_pass_4', 'photo_passport', '4 фото (паспорт)', 4, 1.00, 0),
('photo_pass_6', 'photo_passport', '6 фото (виза)', 6, 1.15, 0);

-- ============================================
-- КАТЕГОРИЯ: Календари
-- ============================================

-- Услуга: Календари квартальные
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('calendar_quarterly', 'Календарь квартальный', 'Календари', 'Квартальный календарь-трио', '📅', 1, 100);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('calendar_quarterly', 160.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_q_50', 'calendar_quarterly', '50 шт', 50, 1.00, 0),
('cal_q_100', 'calendar_quarterly', '100 шт', 100, 0.88, 0),
('cal_q_300', 'calendar_quarterly', '300 шт', 300, 0.73, 0);

-- Услуга: Календари настенные перекидные А3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('calendar_wall_a3', 'Календарь настенный А3', 'Календари', 'Настенный перекидной календарь А3, 12 листов', '📅', 1, 101);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('calendar_wall_a3', 240.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_wall_a3_50', 'calendar_wall_a3', '50 шт', 50, 1.00, 0),
('cal_wall_a3_100', 'calendar_wall_a3', '100 шт', 100, 0.83, 0);

-- Услуга: Карманные календари
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('calendar_pocket', 'Календарь карманный', 'Календари', 'Карманный календарик', '🗓', 1, 102);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('calendar_pocket', 8.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cal_pocket_100', 'calendar_pocket', '100 шт', 100, 1.00, 0),
('cal_pocket_500', 'calendar_pocket', '500 шт', 500, 0.65, 0),
('cal_pocket_1000', 'calendar_pocket', '1000 шт', 1000, 0.55, 0);

-- ============================================
-- КАТЕГОРИЯ: Дипломы и Сертификаты
-- ============================================

-- Услуга: Диплом А4 простой
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('diploma_a4_simple', 'Диплом А4 простой', 'Дипломы', 'Печать диплома на обычной бумаге А4', '🏆', 1, 110);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('diploma_a4_simple', 50.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('diploma_simple_1', 'diploma_a4_simple', '1 шт', 1, 1.00, 0),
('diploma_simple_10', 'diploma_a4_simple', '10 шт', 10, 0.85, 0),
('diploma_simple_50', 'diploma_a4_simple', '50 шт', 50, 0.65, 0);

-- Услуга: Диплом А4 на дизайнерской бумаге
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('diploma_a4_premium', 'Диплом А4 премиум', 'Дипломы', 'Печать диплома на дизайнерской бумаге', '🏆', 1, 111);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('diploma_a4_premium', 100.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('diploma_premium_1', 'diploma_a4_premium', '1 шт', 1, 1.00, 0),
('diploma_premium_10', 'diploma_a4_premium', '10 шт', 10, 0.85, 0),
('diploma_premium_50', 'diploma_a4_premium', '50 шт', 50, 0.70, 0);

-- Услуга: Сертификат А4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('certificate_a4', 'Сертификат А4', 'Дипломы', 'Печать сертификата на плотной бумаге', '📜', 1, 112);

INSERT INTO `service_base_prices` (`service_id`, `base_price`) VALUES ('certificate_a4', 80.00);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cert_1', 'certificate_a4', '1 шт', 1, 1.00, 0),
('cert_10', 'certificate_a4', '10 шт', 10, 0.88, 0),
('cert_50', 'certificate_a4', '50 шт', 50, 0.70, 0);

-- ============================================
-- КОНЕЦ МИГРАЦИИ ЧАСТЬ 2
-- ============================================
