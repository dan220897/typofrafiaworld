-- ============================================
-- МИГРАЦИЯ 009: Полный прайс-лист типографии (Часть 6 - Финальная)
-- Дата: 2026-01-11
-- Описание: Дизайн-услуги и Дополнительные услуги
-- ============================================

-- ============================================
-- КАТЕГОРИЯ: Дизайн-услуги
-- ============================================

-- Услуга: Дизайн визитки
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_business_card', 'Дизайн визитки', 'Дизайн-услуги', 'Разработка уникального дизайна визитной карточки. Цена за 1 макет.', 'fa-palette', 1, 140)
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

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('design_bc_1', 'design_business_card', '1 вариант', 1, 1.00, 0),
('design_bc_2', 'design_business_card', '2 варианта', 2, 0.90, 0),
('design_bc_3', 'design_business_card', '3 варианта', 3, 0.83, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Дизайн листовки/флаера
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_flyer', 'Дизайн листовки/флаера', 'Дизайн-услуги', 'Профессиональный дизайн рекламной листовки или флаера.', 'fa-palette', 1, 141)
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

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('design_fl_1', 'design_flyer', '1 вариант', 1, 1.00, 0),
('design_fl_2', 'design_flyer', '2 варианта', 2, 0.88, 0),
('design_fl_3', 'design_flyer', '3 варианта', 3, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Дизайн буклета
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_booklet', 'Дизайн буклета (6 полос)', 'Дизайн-услуги', 'Дизайн многостраничного буклета формата евро или А4.', 'fa-palette', 1, 142)
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

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('design_book_1', 'design_booklet', '1 вариант', 1, 1.00, 0),
('design_book_2', 'design_booklet', '2 варианта', 2, 0.88, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Дизайн логотипа
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_logo', 'Дизайн логотипа', 'Дизайн-услуги', 'Разработка уникального логотипа для вашего бренда.', 'fa-stamp', 1, 143)
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

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('design_logo_3', 'design_logo', '3 варианта', 3, 1.00, 0),
('design_logo_5', 'design_logo', '5 вариантов', 5, 0.94, 0),
('design_logo_10', 'design_logo', '10 вариантов', 10, 0.88, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Разработка фирменного стиля
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('design_branding', 'Разработка фирменного стиля', 'Дизайн-услуги', 'Полный фирменный стиль: логотип, визитки, бланки, конверты.', 'fa-briefcase', 1, 144)
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

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('branding_basic', 'design_branding', 'Базовый пакет', 1, 1.00, 0),
('branding_premium', 'design_branding', 'Премиум пакет', 1, 1.40, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Верстка макета
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('layout_service', 'Верстка макета', 'Дизайн-услуги', 'Профессиональная верстка вашего макета для печати.', 'fa-file-alt', 1, 145)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('layout_service', 500.00, 200.00, 300.00, 150.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('layout_1', 'layout_service', '1 страница', 1, 1.00, 0),
('layout_5', 'layout_service', '5 страниц', 5, 0.90, 0),
('layout_10', 'layout_service', '10 страниц', 10, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Подготовка к печати
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('prepress_service', 'Подготовка к печати', 'Дизайн-услуги', 'Технический контроль и подготовка макета к печати (цветопроба, треппинг).', 'fa-cog', 1, 146)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('prepress_service', 300.00, 100.00, 200.00, 200.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('prepress_1', 'prepress_service', '1 макет', 1, 1.00, 0),
('prepress_3', 'prepress_service', '3 макета', 3, 0.93, 0),
('prepress_5', 'prepress_service', '5 макетов', 5, 0.87, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- КАТЕГОРИЯ: Дополнительные услуги
-- ============================================

-- Услуга: Срочная печать (+50%)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('urgent_1hour', 'Срочная печать за 1 час (+50%)', 'Дополнительные услуги', 'Экспресс-изготовление вашего заказа за 1 час. Надбавка +50% к стоимости.', 'fa-bolt', 1, 150)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('urgent_1hour', 500.00, 0.00, 500.00, 0.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Экспресс за 3 часа (+30%)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('urgent_3hours', 'Экспресс за 3 часа (+30%)', 'Дополнительные услуги', 'Быстрое изготовление заказа за 3 часа. Надбавка +30% к стоимости.', 'fa-fast-forward', 1, 151)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('urgent_3hours', 300.00, 0.00, 300.00, 0.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Печать в день обращения (+20%)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('urgent_sameday', 'Печать в день обращения (+20%)', 'Дополнительные услуги', 'Изготовление заказа в течение дня. Надбавка +20% к стоимости.', 'fa-calendar-day', 1, 152)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('urgent_sameday', 200.00, 0.00, 200.00, 0.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

-- Услуга: Доставка по Москве (до 10 км)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('delivery_moscow_10km', 'Доставка по Москве (до 10 км)', 'Дополнительные услуги', 'Курьерская доставка в пределах 10 км от нашего офиса.', 'fa-truck', 1, 153)
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

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('del_msk_1', 'delivery_moscow_10km', '1 адрес', 1, 1.00, 0),
('del_msk_2', 'delivery_moscow_10km', '2 адреса', 2, 0.88, 0),
('del_msk_3', 'delivery_moscow_10km', '3 адреса', 3, 0.83, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Доставка МО (до 30 км)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('delivery_mo_30km', 'Доставка МО (до 30 км)', 'Дополнительные услуги', 'Доставка в Московскую область в радиусе 30 км.', 'fa-truck', 1, 154)
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

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('del_mo_1', 'delivery_mo_30km', '1 адрес', 1, 1.00, 0),
('del_mo_2', 'delivery_mo_30km', '2 адреса', 2, 0.90, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Набор текста
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('typing_service', 'Набор текста (1 стр А4)', 'Дополнительные услуги', 'Набор текста с рукописного или отсканированного документа.', 'fa-keyboard', 1, 155)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('typing_service', 100.00, 0.00, 100.00, 0.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('typing_1', 'typing_service', '1 страница', 1, 1.00, 0),
('typing_5', 'typing_service', '5 страниц', 5, 1.00, 0),
('typing_10', 'typing_service', '10 страниц', 10, 0.90, 0),
('typing_20', 'typing_service', '20 страниц', 20, 0.85, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- Услуга: Распознавание текста OCR (уже есть в Part 1 как сканирование, но добавим как отдельную)
INSERT INTO `services` (`id`, `label`, `category`, `description`, `icon`, `is_active`, `sort_order`) VALUES
('ocr_text_recognition', 'Распознавание текста OCR (1 стр)', 'Дополнительные услуги', 'Автоматическое распознавание текста с изображений и PDF.', 'fa-file-word', 1, 156)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `category` = VALUES(`category`),
  `description` = VALUES(`description`);

INSERT INTO `service_base_prices` (`service_id`, `base_price`, `cost_price`, `margin`, `margin_percent`) VALUES
('ocr_text_recognition', 50.00, 0.00, 50.00, 0.00)
ON DUPLICATE KEY UPDATE
  `base_price` = VALUES(`base_price`),
  `cost_price` = VALUES(`cost_price`),
  `margin` = VALUES(`margin`),
  `margin_percent` = VALUES(`margin_percent`);

INSERT INTO `service_quantities` (`id`, `service_id`, `label`, `quantity`, `multiplier`, `price`) VALUES
('ocr_1', 'ocr_text_recognition', '1 страница', 1, 1.00, 0),
('ocr_10', 'ocr_text_recognition', '10 страниц', 10, 1.00, 0),
('ocr_50', 'ocr_text_recognition', '50 страниц', 50, 0.90, 0),
('ocr_100', 'ocr_text_recognition', '100 страниц', 100, 0.80, 0)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `quantity` = VALUES(`quantity`),
  `multiplier` = VALUES(`multiplier`);

-- ============================================
-- ЗАВЕРШЕНО!
-- ============================================

SELECT 'Миграция 009 успешно выполнена! Добавлено 14 новых услуг:' as result;
SELECT '- Дизайн-услуги: 7 услуг (дизайн визитки, листовки, буклета, логотипа, фирменный стиль, верстка, подготовка)' as info;
SELECT '- Дополнительные услуги: 7 услуг (срочная печать, экспресс, доставка, набор текста, OCR)' as info;
SELECT 'ВСЕГО УСЛУГ В КАТАЛОГЕ: ~114 услуг!' as total;
SELECT 'Полный прайс-лист загружен в базу данных!' as final;
