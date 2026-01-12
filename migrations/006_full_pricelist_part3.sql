-- ============================================
-- МИГРАЦИЯ 006: Полный прайс-лист типографии (Часть 3)
-- Дата: 2026-01-10
-- Описание: Плакаты, Наклейки, Широкоформатная печать
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Плакаты и постеры
-- ============================================

-- Услуга: Плакат A3 (297×420 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a3', 'Плакат А3 (297×420 мм)', 'Плакаты', 'Яркий плакат формата А3. Цена за 1 шт.', 'fa-image', 1, 60)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a3', 150.00, 50.00, 100.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a3_no_lam', 'poster_a3', 'Без ламинации', 0, 0),
('poster_a3_lam', 'poster_a3', 'С ламинацией', 150.00, 50.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a3_1', 'poster_a3', '1 шт', 1, 1.00, 0),
('poster_a3_10', 'poster_a3', '10 шт', 10, 0.80, 0),
('poster_a3_50', 'poster_a3', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Плакат A2 (420×594 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a2', 'Плакат А2 (420×594 мм)', 'Плакаты', 'Большой плакат формата А2. Цена за 1 шт.', 'fa-image', 1, 61)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a2', 350.00, 120.00, 230.00, 191.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a2_no_lam', 'poster_a2', 'Без ламинации', 0, 0),
('poster_a2_lam', 'poster_a2', 'С ламинацией', 250.00, 80.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a2_1', 'poster_a2', '1 шт', 1, 1.00, 0),
('poster_a2_10', 'poster_a2', '10 шт', 10, 0.80, 0),
('poster_a2_50', 'poster_a2', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Плакат A1 (594×841 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a1', 'Плакат А1 (594×841 мм)', 'Плакаты', 'Очень большой плакат формата А1. Цена за 1 шт.', 'fa-image', 1, 62)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a1', 700.00, 250.00, 450.00, 180.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a1_no_lam', 'poster_a1', 'Без ламинации', 0, 0),
('poster_a1_lam', 'poster_a1', 'С ламинацией', 500.00, 150.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a1_1', 'poster_a1', '1 шт', 1, 1.00, 0),
('poster_a1_10', 'poster_a1', '10 шт', 10, 0.80, 0),
('poster_a1_50', 'poster_a1', '50 шт', 50, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Плакат A0 (841×1189 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('poster_a0', 'Плакат А0 (841×1189 мм)', 'Плакаты', 'Гигантский плакат формата А0. Цена за 1 шт.', 'fa-image', 1, 63)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('poster_a0', 1500.00, 550.00, 950.00, 172.73)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`, `cost_price`) VALUES
('poster_a0_no_lam', 'poster_a0', 'Без ламинации', 0, 0),
('poster_a0_lam', 'poster_a0', 'С ламинацией', 1000.00, 300.00)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('poster_a0_1', 'poster_a0', '1 шт', 1, 1.00, 0),
('poster_a0_10', 'poster_a0', '10 шт', 10, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Наклейки и стикеры
-- ============================================

-- Услуга: Бумажные наклейки 50×50 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_paper_50x50', 'Бумажные наклейки 50×50 мм', 'Наклейки', 'Круглые или квадратные бумажные наклейки 50×50 мм. Цена за 100 шт.', 'fa-tag', 1, 70)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_paper_50x50', 800.00, 300.00, 500.00, 166.67)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_p50_100', 'stickers_paper_50x50', '100 шт', 100, 1.00, 0),
('stickers_p50_500', 'stickers_paper_50x50', '500 шт', 500, 0.75, 0),
('stickers_p50_1000', 'stickers_paper_50x50', '1000 шт', 1000, 0.60, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Бумажные наклейки 100×100 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_paper_100x100', 'Бумажные наклейки 100×100 мм', 'Наклейки', 'Круглые или квадратные бумажные наклейки 100×100 мм. Цена за 100 шт.', 'fa-tag', 1, 71)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_paper_100x100', 1500.00, 600.00, 900.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_p100_100', 'stickers_paper_100x100', '100 шт', 100, 1.00, 0),
('stickers_p100_500', 'stickers_paper_100x100', '500 шт', 500, 0.70, 0),
('stickers_p100_1000', 'stickers_paper_100x100', '1000 шт', 1000, 0.60, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Виниловые наклейки 50×50 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_vinyl_50x50', 'Виниловые наклейки 50×50 мм', 'Наклейки', 'Водостойкие виниловые наклейки 50×50 мм. Долговечные. Цена за 100 шт.', 'fa-tag', 1, 72)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_vinyl_50x50', 1500.00, 600.00, 900.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_v50_100', 'stickers_vinyl_50x50', '100 шт', 100, 1.00, 0),
('stickers_v50_500', 'stickers_vinyl_50x50', '500 шт', 500, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Виниловые наклейки 100×100 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('stickers_vinyl_100x100', 'Виниловые наклейки 100×100 мм', 'Наклейки', 'Водостойкие виниловые наклейки 100×100 мм. Долговечные. Цена за 100 шт.', 'fa-tag', 1, 73)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('stickers_vinyl_100x100', 2500.00, 1100.00, 1400.00, 127.27)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickers_v100_100', 'stickers_vinyl_100x100', '100 шт', 100, 1.00, 0),
('stickers_v100_500', 'stickers_vinyl_100x100', '500 шт', 500, 0.65, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Стикерпаки (набор 10-20 стикеров)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('sticker_packs', 'Стикерпаки (набор 10-20 шт)', 'Наклейки', 'Набор стикеров 10-20 штук. Популярно у молодежи! Цена за 50 наборов.', 'fa-tags', 1, 74)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('sticker_packs', 5000.00, 2000.00, 3000.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('stickerpack_50', 'sticker_packs', '50 наборов', 50, 1.00, 0),
('stickerpack_100', 'sticker_packs', '100 наборов', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Широкоформатная печать
-- ============================================

-- Услуга: Баннер 1×1 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_1x1', 'Баннер 1×1 м', 'Широкоформатная печать', 'Баннер на виниле 1×1 метр. Для наружной рекламы.', 'fa-flag', 1, 80)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_1x1', 1200.00, 500.00, 700.00, 140.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Баннер 2×1 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_2x1', 'Баннер 2×1 м', 'Широкоформатная печать', 'Баннер на виниле 2×1 метр. Для наружной рекламы.', 'fa-flag', 1, 81)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_2x1', 2000.00, 900.00, 1100.00, 122.22)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Баннер 3×2 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_3x2', 'Баннер 3×2 м', 'Широкоформатная печать', 'Баннер на виниле 3×2 метра. Большой формат для наружной рекламы.', 'fa-flag', 1, 82)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_3x2', 5000.00, 2200.00, 2800.00, 127.27)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Баннер 6×3 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('banner_6x3', 'Баннер 6×3 м', 'Широкоформатная печать', 'Баннер на виниле 6×3 метра. Огромный формат для наружной рекламы.', 'fa-flag', 1, 83)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('banner_6x3', 15000.00, 7000.00, 8000.00, 114.29)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Roll-up стенд 0.8×2 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('rollup_08x2', 'Roll-up стенд 0.8×2 м', 'Широкоформатная печать', 'Roll-up стенд с конструкцией 0.8×2 метра. Готов к установке.', 'fa-display', 1, 84)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('rollup_08x2', 3500.00, 1800.00, 1700.00, 94.44)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Roll-up стенд 1×2 м
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('rollup_1x2', 'Roll-up стенд 1×2 м', 'Широкоформатная печать', 'Roll-up стенд с конструкцией 1×2 метра. Готов к установке.', 'fa-display', 1, 85)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('rollup_1x2', 4500.00, 2300.00, 2200.00, 95.65)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Фотообои (цена за м²)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('wallpaper_photo', 'Фотообои (за м²)', 'Широкоформатная печать', 'Печать фотообоев. Цена указана за 1 м². Минимальный заказ 3 м².', 'fa-image', 1, 86)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('wallpaper_photo', 2500.00, 1000.00, 1500.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('wallpaper_3m', 'wallpaper_photo', '1-5 м²', 3, 1.00, 0),
('wallpaper_5m', 'wallpaper_photo', '5-10 м²', 7, 0.90, 0),
('wallpaper_10m', 'wallpaper_photo', '10+ м²', 10, 0.75, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- Конец миграции 006 (Часть 3)
-- ============================================
