-- ============================================
-- МИГРАЦИЯ: Добавление услуг Фотопечать и Печать сертификатов
-- Дата: 2026-02-02
-- Описание: Добавляет услуги "Фотопечать" и "Печать сертификатов"
-- ============================================

-- ============================================
-- Услуга: Фотопечать
-- ============================================
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('photo_print', 'Фотопечать', 'Фотоуслуги', 'Печать фотографий различных форматов на фотобумаге', '📷', 1, 50)
ON DUPLICATE KEY UPDATE label = 'Фотопечать', is_active = 1;

INSERT INTO `service_base_prices` (`service_id`, `base_price`)
VALUES ('photo_print', 15.00)
ON DUPLICATE KEY UPDATE base_price = 15.00;

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`, `is_active`) VALUES
('photo_print_10x15', 'photo_print', '10x15 см', 0.00, 1),
('photo_print_15x20', 'photo_print', '15x20 см', 10.00, 1),
('photo_print_20x30', 'photo_print', '20x30 см', 30.00, 1),
('photo_print_30x40', 'photo_print', '30x40 см', 80.00, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), price = VALUES(price);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('photo_print_1', 'photo_print', '1 шт', 1, 1.00, 0),
('photo_print_10', 'photo_print', '10 шт', 10, 0.90, 0),
('photo_print_50', 'photo_print', '50 шт', 50, 0.80, 0),
('photo_print_100', 'photo_print', '100 шт', 100, 0.70, 0)
ON DUPLICATE KEY UPDATE label = VALUES(label), quantity = VALUES(quantity), multiplier = VALUES(multiplier);

-- ============================================
-- Услуга: Печать сертификатов
-- ============================================
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`)
VALUES ('print_certificates', 'Печать сертификатов', 'Полиграфия', 'Печать подарочных сертификатов, грамот, дипломов на плотной бумаге', '🏆', 1, 51)
ON DUPLICATE KEY UPDATE label = 'Печать сертификатов', is_active = 1;

INSERT INTO `service_base_prices` (`service_id`, `base_price`)
VALUES ('print_certificates', 100.00)
ON DUPLICATE KEY UPDATE base_price = 100.00;

INSERT INTO `service_sizes` (`id`, `service_id`, `label`, `price`, `is_active`) VALUES
('cert_a5', 'print_certificates', 'A5 (148x210 мм)', 0.00, 1),
('cert_a4', 'print_certificates', 'A4 (210x297 мм)', 50.00, 1),
('cert_a3', 'print_certificates', 'A3 (297x420 мм)', 150.00, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), price = VALUES(price);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('cert_1', 'print_certificates', '1 шт', 1, 1.00, 0),
('cert_10', 'print_certificates', '10 шт', 10, 0.90, 0),
('cert_50', 'print_certificates', '50 шт', 50, 0.80, 0),
('cert_100', 'print_certificates', '100 шт', 100, 0.70, 0)
ON DUPLICATE KEY UPDATE label = VALUES(label), quantity = VALUES(quantity), multiplier = VALUES(multiplier);
