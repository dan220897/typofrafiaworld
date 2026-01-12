-- ============================================
-- МИГРАЦИЯ 005: Полный прайс-лист типографии (Часть 2)
-- Дата: 2026-01-10
-- Описание: Визитки, Листовки, Брошюры
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Визитные карточки
-- ============================================

-- Услуга: Визитки стандартные односторонние
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_std_1side', 'Визитки односторонние', 'Визитки', 'Стандартные визитки 90×50 мм, односторонняя печать. Базовая цена за 100 шт.', 'fa-id-card', 1, 30)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_std_1side', 500.00, 150.00, 350.00, 233.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_std_1s_100', 'business_cards_std_1side', '100 шт', 100, 1.00, 0),
('bc_std_1s_200', 'business_cards_std_1side', '200 шт', 200, 1.00, 0),
('bc_std_1s_300', 'business_cards_std_1side', '300 шт', 300, 1.00, 0),
('bc_std_1s_500', 'business_cards_std_1side', '500 шт', 500, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Визитки стандартные двусторонние
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_std_2side', 'Визитки двусторонние', 'Визитки', 'Стандартные визитки 90×50 мм, двусторонняя печать. Базовая цена за 100 шт.', 'fa-id-card', 1, 31)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_std_2side', 700.00, 250.00, 450.00, 180.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_std_2s_100', 'business_cards_std_2side', '100 шт', 100, 1.00, 0),
('bc_std_2s_200', 'business_cards_std_2side', '200 шт', 200, 1.00, 0),
('bc_std_2s_300', 'business_cards_std_2side', '300 шт', 300, 1.00, 0),
('bc_std_2s_500', 'business_cards_std_2side', '500 шт', 500, 0.85, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Визитки с ламинацией
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_laminated', 'Визитки с ламинацией', 'Визитки', 'Визитки с матовой или глянцевой ламинацией. Цена за 100 шт.', 'fa-id-card', 1, 32)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_laminated', 1200.00, 400.00, 800.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_lamination` (`id`, `service_id`, `label`, `price`) VALUES
('bc_lam_matte', 'business_cards_laminated', 'Матовая ламинация', 0),
('bc_lam_gloss', 'business_cards_laminated', 'Глянцевая ламинация', 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `price` = VALUES(`price`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_lam_100', 'business_cards_laminated', '100 шт', 100, 1.00, 0),
('bc_lam_200', 'business_cards_laminated', '200 шт', 200, 1.00, 0),
('bc_lam_500', 'business_cards_laminated', '500 шт', 500, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Визитки на дизайнерской бумаге
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('business_cards_designer', 'Визитки на дизайнерской бумаге', 'Визитки', 'Визитки на дизайнерской бумаге премиум качества. Цена за 100 шт.', 'fa-id-card', 1, 33)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('business_cards_designer', 1500.00, 600.00, 900.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('bc_des_100', 'business_cards_designer', '100 шт', 100, 1.00, 0),
('bc_des_200', 'business_cards_designer', '200 шт', 200, 1.00, 0),
('bc_des_500', 'business_cards_designer', '500 шт', 500, 0.85, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Листовки и флаеры
-- ============================================

-- Услуга: Листовки A6 (105×148 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('flyers_a6', 'Листовки А6 (105×148 мм)', 'Листовки', 'Яркие рекламные листовки формата А6. Идеально для промо-акций.', 'fa-file-alt', 1, 40)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('flyers_a6', 800.00, 250.00, 550.00, 220.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyers_a6_1side', 'flyers_a6', 'Односторонние', 1.00),
('flyers_a6_2side', 'flyers_a6', 'Двусторонние', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyers_a6_100', 'flyers_a6', '100 шт', 100, 1.00, 0),
('flyers_a6_500', 'flyers_a6', '500 шт', 500, 0.85, 0),
('flyers_a6_1000', 'flyers_a6', '1000 шт', 1000, 0.70, 0),
('flyers_a6_2000', 'flyers_a6', '2000 шт', 2000, 0.60, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Листовки A5 (148×210 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('flyers_a5', 'Листовки А5 (148×210 мм)', 'Листовки', 'Рекламные листовки формата А5. Популярный формат для распространения.', 'fa-file-alt', 1, 41)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('flyers_a5', 1200.00, 400.00, 800.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyers_a5_1side', 'flyers_a5', 'Односторонние', 1.00),
('flyers_a5_2side', 'flyers_a5', 'Двусторонние', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyers_a5_100', 'flyers_a5', '100 шт', 100, 1.00, 0),
('flyers_a5_500', 'flyers_a5', '500 шт', 500, 0.80, 0),
('flyers_a5_1000', 'flyers_a5', '1000 шт', 1000, 0.65, 0),
('flyers_a5_2000', 'flyers_a5', '2000 шт', 2000, 0.55, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Листовки A4 (210×297 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('flyers_a4', 'Листовки А4 (210×297 мм)', 'Листовки', 'Большие рекламные листовки формата А4. Максимум информации.', 'fa-file-alt', 1, 42)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('flyers_a4', 1800.00, 600.00, 1200.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_sides` (`id`, `service_id`, `label`, `multiplier`) VALUES
('flyers_a4_1side', 'flyers_a4', 'Односторонние', 1.00),
('flyers_a4_2side', 'flyers_a4', 'Двусторонние', 1.50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `multiplier` = VALUES(`multiplier`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('flyers_a4_100', 'flyers_a4', '100 шт', 100, 1.00, 0),
('flyers_a4_500', 'flyers_a4', '500 шт', 500, 0.75, 0),
('flyers_a4_1000', 'flyers_a4', '1000 шт', 1000, 0.60, 0),
('flyers_a4_2000', 'flyers_a4', '2000 шт', 2000, 0.50, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Брошюры и буклеты
-- ============================================

-- Услуга: Брошюры A5 на скрепке (8 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a5_8p_staple', 'Брошюра А5 на скрепке (8 стр)', 'Брошюры', 'Брошюра формата А5 на скрепке, 8 страниц. Цена за 50 шт.', 'fa-book', 1, 50)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a5_8p_staple', 3000.00, 1200.00, 1800.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a5_8p_50', 'brochure_a5_8p_staple', '50 шт', 50, 1.00, 0),
('broch_a5_8p_100', 'brochure_a5_8p_staple', '100 шт', 100, 0.90, 0),
('broch_a5_8p_200', 'brochure_a5_8p_staple', '200 шт', 200, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A5 на скрепке (16 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a5_16p_staple', 'Брошюра А5 на скрепке (16 стр)', 'Брошюры', 'Брошюра формата А5 на скрепке, 16 страниц. Цена за 50 шт.', 'fa-book', 1, 51)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a5_16p_staple', 4500.00, 1800.00, 2700.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a5_16p_50', 'brochure_a5_16p_staple', '50 шт', 50, 1.00, 0),
('broch_a5_16p_100', 'brochure_a5_16p_staple', '100 шт', 100, 0.90, 0),
('broch_a5_16p_200', 'brochure_a5_16p_staple', '200 шт', 200, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A5 на скрепке (24 страницы)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a5_24p_staple', 'Брошюра А5 на скрепке (24 стр)', 'Брошюры', 'Брошюра формата А5 на скрепке, 24 страницы. Цена за 50 шт.', 'fa-book', 1, 52)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a5_24p_staple', 6000.00, 2500.00, 3500.00, 140.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a5_24p_50', 'brochure_a5_24p_staple', '50 шт', 50, 1.00, 0),
('broch_a5_24p_100', 'brochure_a5_24p_staple', '100 шт', 100, 0.85, 0),
('broch_a5_24p_200', 'brochure_a5_24p_staple', '200 шт', 200, 0.75, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A4 на пружине (20 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a4_20p_spiral', 'Брошюра А4 на пружине (20 стр)', 'Брошюры', 'Брошюра формата А4 на пружине, 20 страниц. Презентабельный вид. Цена за 50 шт.', 'fa-book', 1, 53)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a4_20p_spiral', 6000.00, 2500.00, 3500.00, 140.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a4_20p_50', 'brochure_a4_20p_spiral', '50 шт', 50, 1.00, 0),
('broch_a4_20p_100', 'brochure_a4_20p_spiral', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A4 на пружине (50 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a4_50p_spiral', 'Брошюра А4 на пружине (50 стр)', 'Брошюры', 'Брошюра формата А4 на пружине, 50 страниц. Каталоги, прайсы. Цена за 50 шт.', 'fa-book', 1, 54)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a4_50p_spiral', 12000.00, 5500.00, 6500.00, 118.18)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a4_50p_50', 'brochure_a4_50p_spiral', '50 шт', 50, 1.00, 0),
('broch_a4_50p_100', 'brochure_a4_50p_spiral', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюры A4 на пружине (100 страниц)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('brochure_a4_100p_spiral', 'Брошюра А4 на пружине (100 стр)', 'Брошюры', 'Брошюра формата А4 на пружине, 100 страниц. Толстые каталоги. Цена за 50 шт.', 'fa-book', 1, 55)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('brochure_a4_100p_spiral', 20000.00, 10000.00, 10000.00, 100.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('broch_a4_100p_50', 'brochure_a4_100p_spiral', '50 шт', 50, 1.00, 0),
('broch_a4_100p_100', 'brochure_a4_100p_spiral', '100 шт', 100, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- Конец миграции 005 (Часть 2)
-- ============================================
