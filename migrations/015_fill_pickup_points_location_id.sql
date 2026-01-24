-- Migration 015: Fill location_id in pickup_points table
-- Date: 2026-01-24
-- Description: Manually link existing pickup points to locations

-- Сначала проверяем, есть ли столбец location_id в pickup_points
-- Если нет - добавляем
SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pickup_points' AND COLUMN_NAME = 'location_id');

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `pickup_points` ADD COLUMN `location_id` int(11) DEFAULT NULL COMMENT ''ID локации/филиала'' AFTER `sort_order`',
    'SELECT ''Column location_id already exists in pickup_points''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Привязываем точки самовывоза к локациям по совпадению адресов/названий
-- Белорусская (pickup_points.id=3) -> Белорусская (locations.id=4)
UPDATE `pickup_points`
SET location_id = 4
WHERE id = 3 AND (name LIKE '%Белорусская%' OR address LIKE '%Белорусская%' OR address LIKE '%Тверская-Ямская%');

-- Патрики (pickup_points.id=4) -> Патрики (locations.id=6)
UPDATE `pickup_points`
SET location_id = 6
WHERE id = 4 AND (name LIKE '%Патрики%' OR address LIKE '%Садовая-Кудринская%');

-- м. 1905 (pickup_points.id=5) -> м. 1905 (locations.id=5)
UPDATE `pickup_points`
SET location_id = 5
WHERE id = 5 AND (name LIKE '%1905%' OR address LIKE '%Шмитовский%');

-- Показываем результат привязки
SELECT
    pp.id as pickup_point_id,
    pp.name as pickup_point_name,
    pp.location_id,
    l.name as location_name
FROM pickup_points pp
LEFT JOIN locations l ON pp.location_id = l.id
ORDER BY pp.id;

COMMIT;
