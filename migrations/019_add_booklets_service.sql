-- ============================================
-- МИГРАЦИЯ: Добавление услуги Буклеты
-- Дата: 2026-02-22
-- Описание: Добавляет услугу "Буклеты" в категорию Полиграфия
-- ============================================

-- ============================================
-- Услуга: Буклеты
-- ============================================
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('booklets', 'Буклеты', 'Полиграфия', 'Печать буклетов различных форматов со сгибом', '📖', 1, 52)
ON DUPLICATE KEY UPDATE label = 'Буклеты', is_active = 1;

INSERT INTO `service_base_prices` (`service_id`, `base_price`)
VALUES ('booklets', 25.00)
ON DUPLICATE KEY UPDATE base_price = 25.00;

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`, `is_active`) VALUES
('booklet_euro', 'booklets', 'Евробуклет (210x99 мм)', 0.00, 1),
('booklet_a5', 'booklets', 'A5 (1 сгиб)', 10.00, 1),
('booklet_a4', 'booklets', 'A4 (2 сгиба)', 25.00, 1),
('booklet_a4_1fold', 'booklets', 'A4 (1 сгиб)', 20.00, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), price = VALUES(price);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('booklet_50', 'booklets', '50 шт', 50, 1.00, 0),
('booklet_100', 'booklets', '100 шт', 100, 0.90, 0),
('booklet_250', 'booklets', '250 шт', 250, 0.80, 0),
('booklet_500', 'booklets', '500 шт', 500, 0.70, 0),
('booklet_1000', 'booklets', '1000 шт', 1000, 0.60, 0)
ON DUPLICATE KEY UPDATE label = VALUES(label), quantity = VALUES(quantity), multiplier = VALUES(multiplier);
