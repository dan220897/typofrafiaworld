-- ============================================
-- ШАГ 2: Удаление старых данных из services
-- ============================================

-- Удаляем старые данные из связанных таблиц (если есть)
DELETE FROM service_parameters WHERE service_id IN (SELECT id FROM services);
DELETE FROM service_price_rules WHERE service_id IN (SELECT id FROM services);
DELETE FROM order_items WHERE service_id IN (SELECT id FROM services);

-- Удаляем старые услуги
DELETE FROM services;

-- Проверяем, что таблица пуста
SELECT COUNT(*) as remaining_services FROM services;
