# Пошаговая миграция прайс-листа типографии

## ⚠️ ВАЖНО: Выполняйте скрипты СТРОГО ПО ПОРЯДКУ!

## Шаг 1: Проверка внешних ключей
**Файл:** `step1_check_foreign_keys.sql`

Выполните этот скрипт, чтобы увидеть все внешние ключи на таблицу services.
Запишите результат - это поможет понять, что нужно будет восстановить.

```sql
-- Скопируйте и выполните содержимое step1_check_foreign_keys.sql
```

---

## Шаг 2: Удаление старых данных
**Файл:** `step2_delete_old_services.sql`

⚠️ **ВНИМАНИЕ!** Этот скрипт УДАЛИТ:
- Все старые услуги из таблицы `services`
- Связанные данные из `service_parameters`
- Связанные данные из `service_price_rules`
- Связанные данные из `order_items`

Если у вас есть важные заказы - СДЕЛАЙТЕ БЭКАП перед выполнением!

```sql
-- Скопируйте и выполните содержимое step2_delete_old_services.sql
```

Результат должен показать: `remaining_services = 0`

---

## Шаг 3: Удаление внешних ключей
**Файл:** `step3_drop_foreign_keys.sql`

Удаляет все внешние ключи на таблицу services, чтобы мы могли изменить тип колонки id.

```sql
-- Скопируйте и выполните содержимое step3_drop_foreign_keys.sql
```

Результат должен показать: `All foreign keys dropped successfully`

---

## Шаг 4: Изменение структуры services
**Файл:** `step4_alter_services_structure.sql`

Изменяет тип id с INT на VARCHAR(50) и добавляет поля label и icon.

```sql
-- Скопируйте и выполните содержимое step4_alter_services_structure.sql
```

В конце покажет новую структуру таблицы. Проверьте:
- `id` должен быть VARCHAR(50)
- Должно быть поле `label`
- Должно быть поле `icon`

---

## Шаг 5: Применить миграцию добавления себестоимости
**Файл:** `003_add_cost_prices.sql`

```sql
-- Скопируйте и выполните содержимое migrations/003_add_cost_prices.sql
```

---

## Шаг 6: Добавить все услуги (части 1-4)
**Файлы:** 
- `004_full_pricelist_part1.sql`
- `005_full_pricelist_part2.sql`
- `006_full_pricelist_part3.sql`
- `007_full_pricelist_part4.sql`

Выполняйте ПО ОЧЕРЕДИ:

```sql
-- 1. Часть 1
-- Скопируйте и выполните migrations/004_full_pricelist_part1.sql

-- 2. Часть 2
-- Скопируйте и выполните migrations/005_full_pricelist_part2.sql

-- 3. Часть 3
-- Скопируйте и выполните migrations/006_full_pricelist_part3.sql

-- 4. Часть 4
-- Скопируйте и выполните migrations/007_full_pricelist_part4.sql
```

---

## Проверка результата

После выполнения всех шагов выполните:

```sql
-- Проверка количества услуг
SELECT COUNT(*) as total_services FROM services;
-- Должно быть: 80+

-- Проверка по категориям
SELECT category, COUNT(*) as count 
FROM services 
GROUP BY category 
ORDER BY category;
-- Должно быть: 16 категорий

-- Проверка структуры
DESCRIBE services;
-- id должен быть VARCHAR(50), должны быть поля label, icon

-- Проверка цен
SELECT s.id, s.label, bp.base_price, bp.cost_price, bp.margin
FROM services s
LEFT JOIN service_base_prices bp ON s.id = bp.service_id
LIMIT 10;
```

---

## Что делать если что-то пошло не так

Если на каком-то шаге возникла ошибка:
1. **НЕ ПРОДОЛЖАЙТЕ** выполнение следующих шагов
2. Скопируйте текст ошибки
3. Напишите мне, я помогу исправить

---

Дата создания: 2026-01-10
Версия MySQL: 5.7.21
