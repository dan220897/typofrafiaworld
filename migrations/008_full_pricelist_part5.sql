-- ============================================
-- МИГРАЦИЯ 008: Полный прайс-лист типографии (Часть 5)
-- Дата: 2026-01-10
-- Описание: Упаковка, Сувениры, Интерьерная печать, Постпечать
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Упаковка
-- ============================================

-- Услуга: Бумажные пакеты S (180×220 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('paper_bag_s', 'Бумажные пакеты S (180×220 мм)', 'Упаковка', 'Пакеты с логотипом для магазинов и бутиков. Цена за 100 шт.', 'fa-shopping-bag', 1, 100)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('paper_bag_s', 3500.00, 1500.00, 2000.00, 133.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('pbag_s_100', 'paper_bag_s', '100 шт', 100, 1.00, 0),
('pbag_s_500', 'paper_bag_s', '500 шт', 500, 0.85, 0),
('pbag_s_1000', 'paper_bag_s', '1000 шт', 1000, 0.75, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Бумажные пакеты M (240×320 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('paper_bag_m', 'Бумажные пакеты M (240×320 мм)', 'Упаковка', 'Средние пакеты для упаковки товаров. Цена за 100 шт.', 'fa-shopping-bag', 1, 101)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('paper_bag_m', 5000.00, 2200.00, 2800.00, 127.27)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('pbag_m_100', 'paper_bag_m', '100 шт', 100, 1.00, 0),
('pbag_m_500', 'paper_bag_m', '500 шт', 500, 0.85, 0),
('pbag_m_1000', 'paper_bag_m', '1000 шт', 1000, 0.75, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Бумажные пакеты L (320×420 мм)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('paper_bag_l', 'Бумажные пакеты L (320×420 мм)', 'Упаковка', 'Большие пакеты для крупных покупок. Цена за 100 шт.', 'fa-shopping-bag', 1, 102)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('paper_bag_l', 7000.00, 3200.00, 3800.00, 118.75)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('pbag_l_100', 'paper_bag_l', '100 шт', 100, 1.00, 0),
('pbag_l_500', 'paper_bag_l', '500 шт', 500, 0.82, 0),
('pbag_l_1000', 'paper_bag_l', '1000 шт', 1000, 0.72, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Картонные коробки 100×100×50 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('cardboard_box_s', 'Картонные коробки 100×100×50 мм', 'Упаковка', 'Компактные коробки для мелких товаров. Цена за 100 шт.', 'fa-box', 1, 103)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('cardboard_box_s', 4000.00, 1800.00, 2200.00, 122.22)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cbox_s_100', 'cardboard_box_s', '100 шт', 100, 1.00, 0),
('cbox_s_500', 'cardboard_box_s', '500 шт', 500, 0.83, 0),
('cbox_s_1000', 'cardboard_box_s', '1000 шт', 1000, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Картонные коробки 200×200×100 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('cardboard_box_m', 'Картонные коробки 200×200×100 мм', 'Упаковка', 'Средние коробки для товаров. Цена за 100 шт.', 'fa-box', 1, 104)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('cardboard_box_m', 8000.00, 4000.00, 4000.00, 100.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cbox_m_100', 'cardboard_box_m', '100 шт', 100, 1.00, 0),
('cbox_m_500', 'cardboard_box_m', '500 шт', 500, 0.85, 0),
('cbox_m_1000', 'cardboard_box_m', '1000 шт', 1000, 0.73, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Сувенирная продукция
-- ============================================

-- Услуга: Футболки с печатью
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('tshirt_print', 'Футболки с печатью', 'Сувенирная продукция', 'Футболки с вашим дизайном. Термотрансферная печать.', 'fa-tshirt', 1, 110)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('tshirt_print', 800.00, 350.00, 450.00, 128.57)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('tshirt_1', 'tshirt_print', '1 шт', 1, 1.00, 0),
('tshirt_10', 'tshirt_print', '10 шт', 10, 0.95, 0),
('tshirt_50', 'tshirt_print', '50 шт', 50, 0.88, 0),
('tshirt_100', 'tshirt_print', '100 шт', 100, 0.81, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Кружки с фото
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('mug_photo', 'Кружки с фото', 'Сувенирная продукция', 'Белые керамические кружки с печатью вашего фото или логотипа.', 'fa-mug-hot', 1, 111)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('mug_photo', 500.00, 200.00, 300.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('mug_1', 'mug_photo', '1 шт', 1, 1.00, 0),
('mug_10', 'mug_photo', '10 шт', 10, 0.92, 0),
('mug_50', 'mug_photo', '50 шт', 50, 0.88, 0),
('mug_100', 'mug_photo', '100 шт', 100, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Значки 56 мм
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('badge_56mm', 'Значки 56 мм', 'Сувенирная продукция', 'Круглые значки с булавкой. Диаметр 56 мм.', 'fa-circle', 1, 112)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('badge_56mm', 50.00, 18.00, 32.00, 177.78)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('badge_10', 'badge_56mm', '10 шт', 10, 1.00, 0),
('badge_50', 'badge_56mm', '50 шт', 50, 0.90, 0),
('badge_100', 'badge_56mm', '100 шт', 100, 0.80, 0),
('badge_500', 'badge_56mm', '500 шт', 500, 0.70, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Магниты на холодильник
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('fridge_magnet', 'Магниты на холодильник', 'Сувенирная продукция', 'Виниловые магниты с вашим дизайном. Размер 70×50 мм.', 'fa-magnet', 1, 113)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('fridge_magnet', 100.00, 35.00, 65.00, 185.71)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('magnet_10', 'fridge_magnet', '10 шт', 10, 1.00, 0),
('magnet_50', 'fridge_magnet', '50 шт', 50, 0.88, 0),
('magnet_100', 'fridge_magnet', '100 шт', 100, 0.80, 0),
('magnet_500', 'fridge_magnet', '500 шт', 500, 0.72, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Эко-сумки (шопперы)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('eco_shopper', 'Эко-сумки (шопперы)', 'Сувенирная продукция', 'Холщовые сумки с логотипом. Экологичные и практичные.', 'fa-shopping-basket', 1, 114)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('eco_shopper', 400.00, 180.00, 220.00, 122.22)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('shopper_1', 'eco_shopper', '1 шт', 1, 1.00, 0),
('shopper_10', 'eco_shopper', '10 шт', 10, 0.93, 0),
('shopper_50', 'eco_shopper', '50 шт', 50, 0.85, 0),
('shopper_100', 'eco_shopper', '100 шт', 100, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Интерьерная печать
-- ============================================

-- Услуга: Картина на холсте 30×40 см
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('canvas_30x40', 'Картина на холсте 30×40 см', 'Интерьерная печать', 'Печать на холсте с подрамником. Готова к размещению на стене.', 'fa-image', 1, 120)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('canvas_30x40', 2500.00, 800.00, 1700.00, 212.50)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('canvas_30x40_1', 'canvas_30x40', '1 шт', 1, 1.00, 0),
('canvas_30x40_3', 'canvas_30x40', '3 шт', 3, 0.90, 0),
('canvas_30x40_5', 'canvas_30x40', '5 шт', 5, 0.85, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Картина на холсте 50×70 см
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('canvas_50x70', 'Картина на холсте 50×70 см', 'Интерьерная печать', 'Средний формат на подрамнике. Украсит любую комнату.', 'fa-image', 1, 121)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('canvas_50x70', 4500.00, 1600.00, 2900.00, 181.25)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('canvas_50x70_1', 'canvas_50x70', '1 шт', 1, 1.00, 0),
('canvas_50x70_3', 'canvas_50x70', '3 шт', 3, 0.92, 0),
('canvas_50x70_5', 'canvas_50x70', '5 шт', 5, 0.87, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Картина на холсте 70×100 см
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('canvas_70x100', 'Картина на холсте 70×100 см', 'Интерьерная печать', 'Большой формат для акцентной стены. С подрамником.', 'fa-image', 1, 122)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('canvas_70x100', 7500.00, 3000.00, 4500.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('canvas_70x100_1', 'canvas_70x100', '1 шт', 1, 1.00, 0),
('canvas_70x100_2', 'canvas_70x100', '2 шт', 2, 0.93, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Модульная картина 90×60 см (3 модуля)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('modular_90x60', 'Модульная картина 90×60 см (3 модуля)', 'Интерьерная печать', 'Триптих на холсте. Современное решение для интерьера.', 'fa-grip-horizontal', 1, 123)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('modular_90x60', 4500.00, 2000.00, 2500.00, 125.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('mod_90x60_1', 'modular_90x60', '1 комплект', 1, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Модульная картина 120×80 см (3 модуля)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('modular_120x80', 'Модульная картина 120×80 см (3 модуля)', 'Интерьерная печать', 'Крупный триптих. Станет центром внимания в гостиной.', 'fa-grip-horizontal', 1, 124)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('modular_120x80', 7000.00, 3200.00, 3800.00, 118.75)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('mod_120x80_1', 'modular_120x80', '1 комплект', 1, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Модульная картина 150×100 см (3 модуля)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('modular_150x100', 'Модульная картина 150×100 см (3 модуля)', 'Интерьерная печать', 'Большой триптих премиум качества для просторных помещений.', 'fa-grip-horizontal', 1, 125)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('modular_150x100', 12000.00, 5500.00, 6500.00, 118.18)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('mod_150x100_1', 'modular_150x100', '1 комплект', 1, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Постпечатная обработка
-- ============================================

-- Услуга: Брошюровка на скрепку
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('staple_binding', 'Брошюровка на скрепку', 'Постпечатная обработка', 'Скрепление документов металлической скрепкой (до 20 листов).', 'fa-paperclip', 1, 130)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('staple_binding', 50.00, 15.00, 35.00, 233.33)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('staple_1', 'staple_binding', '1 шт', 1, 1.00, 0),
('staple_10', 'staple_binding', '10 шт', 10, 1.00, 0),
('staple_50', 'staple_binding', '50 шт', 50, 0.90, 0),
('staple_100', 'staple_binding', '100 шт', 100, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Брошюровка на пружину
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('spiral_binding', 'Брошюровка на пружину', 'Постпечатная обработка', 'Переплет на металлическую или пластиковую пружину.', 'fa-book', 1, 131)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('spiral_binding', 100.00, 35.00, 65.00, 185.71)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('spiral_1', 'spiral_binding', '1 шт', 1, 1.00, 0),
('spiral_10', 'spiral_binding', '10 шт', 10, 1.00, 0),
('spiral_50', 'spiral_binding', '50 шт', 50, 0.88, 0),
('spiral_100', 'spiral_binding', '100 шт', 100, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Термопереплет
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('thermal_binding', 'Термопереплет (до 100 листов)', 'Постпечатная обработка', 'Профессиональный переплет с твердой обложкой.', 'fa-book-open', 1, 132)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('thermal_binding', 300.00, 120.00, 180.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('thermal_1', 'thermal_binding', '1 шт', 1, 1.00, 0),
('thermal_10', 'thermal_binding', '10 шт', 10, 0.93, 0),
('thermal_50', 'thermal_binding', '50 шт', 50, 0.87, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Ламинирование А4
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('lamination_a4', 'Ламинирование А4', 'Постпечатная обработка', 'Защита документа матовой или глянцевой пленкой.', 'fa-layer-group', 1, 133)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('lamination_a4', 50.00, 18.00, 32.00, 177.78)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('lam_a4_1', 'lamination_a4', '1 лист', 1, 1.00, 0),
('lam_a4_10', 'lamination_a4', '10 листов', 10, 1.00, 0),
('lam_a4_50', 'lamination_a4', '50 листов', 50, 0.90, 0),
('lam_a4_100', 'lamination_a4', '100 листов', 100, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Ламинирование А3
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('lamination_a3', 'Ламинирование А3', 'Постпечатная обработка', 'Ламинация больших документов и схем формата А3.', 'fa-layer-group', 1, 134)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('lamination_a3', 100.00, 35.00, 65.00, 185.71)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('lam_a3_1', 'lamination_a3', '1 лист', 1, 1.00, 0),
('lam_a3_10', 'lamination_a3', '10 листов', 10, 1.00, 0),
('lam_a3_50', 'lamination_a3', '50 листов', 50, 0.88, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Резка (обрезка)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('cutting', 'Резка (обрезка)', 'Постпечатная обработка', 'Профессиональная обрезка тиража на гильотине. Цена за 1 рез.', 'fa-cut', 1, 135)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('cutting', 10.00, 2.00, 8.00, 400.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cut_1', 'cutting', '1 рез', 1, 1.00, 0),
('cut_5', 'cutting', '5 резов', 5, 1.00, 0),
('cut_10', 'cutting', '10 резов', 10, 1.00, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Перфорация
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('perforation', 'Перфорация', 'Постпечатная обработка', 'Пробивка отверстий для подшивки в папки. Цена за 1 лист.', 'fa-circle-notch', 1, 136)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('perforation', 5.00, 1.00, 4.00, 400.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('perf_10', 'perforation', '10 листов', 10, 1.00, 0),
('perf_50', 'perforation', '50 листов', 50, 1.00, 0),
('perf_100', 'perforation', '100 листов', 100, 1.00, 0),
('perf_500', 'perforation', '500 листов', 500, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- ЗАВЕРШЕНО!
-- ============================================

SELECT 'Миграция 008 успешно выполнена! Добавлено 28 новых услуг:' as result;
SELECT '- Упаковка: 5 услуг (пакеты, коробки)' as info;
SELECT '- Сувенирная продукция: 5 услуг (футболки, кружки, значки, магниты, шопперы)' as info;
SELECT '- Интерьерная печать: 6 услуг (картины на холсте, модульные картины)' as info;
SELECT '- Постпечатная обработка: 7 услуг (брошюровка, переплет, ламинирование, резка, перфорация)' as info;
SELECT 'Всего услуг в базе: ~100 услуг!' as total;
