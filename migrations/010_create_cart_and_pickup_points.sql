-- ============================================
-- МИГРАЦИЯ 010: Создание таблиц корзины и точек самовывоза
-- Дата: 2026-01-11
-- Описание: Добавляет функционал корзины и точек самовывоза
-- Совместимость: MySQL 5.5+
-- ============================================

-- Таблица: cart (корзина)
-- Хранит товары в корзине для авторизованных и неавторизованных пользователей
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) DEFAULT NULL COMMENT 'ID сессии для неавторизованных пользователей',
  `user_id` int(11) DEFAULT NULL COMMENT 'ID пользователя для авторизованных',
  `service_id` varchar(50) NOT NULL COMMENT 'ID услуги',
  `quantity` int(11) NOT NULL DEFAULT '1' COMMENT 'Количество',
  `parameters` text COMMENT 'Параметры услуги в JSON формате',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Цена за единицу',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Общая стоимость',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Корзина покупок';

-- Таблица: pickup_points (точки самовывоза)
CREATE TABLE IF NOT EXISTS `pickup_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Название точки',
  `address` varchar(500) NOT NULL COMMENT 'Адрес',
  `latitude` decimal(10,8) NOT NULL COMMENT 'Широта для карты',
  `longitude` decimal(11,8) NOT NULL COMMENT 'Долгота для карты',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Телефон точки',
  `working_hours` varchar(255) DEFAULT NULL COMMENT 'Часы работы',
  `description` text COMMENT 'Описание точки',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активна ли точка',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT 'Порядок сортировки',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Точки самовывоза';

-- Добавляем тестовые точки самовывоза в Москве
INSERT INTO `pickup_points` (`name`, `address`, `latitude`, `longitude`, `phone`, `working_hours`, `description`, `is_active`, `sort_order`, `created_at`) VALUES
('Типо-графия на Тверской', 'г. Москва, ул. Тверская, д. 10', 55.764484, 37.605713, '+7 (495) 123-45-67', 'Пн-Пт: 9:00-20:00, Сб-Вс: 10:00-18:00', 'Наш главный офис в центре Москвы', 1, 1, NOW()),
('Типо-графия на Арбате', 'г. Москва, ул. Арбат, д. 25', 55.751244, 37.589179, '+7 (495) 234-56-78', 'Пн-Пт: 10:00-19:00, Сб: 11:00-17:00', 'Уютная точка на Старом Арбате', 1, 2, NOW()),
('Типо-графия в Крылатском', 'г. Москва, Осенний бульвар, д. 10', 55.756994, 37.407846, '+7 (495) 345-67-89', 'Пн-Пт: 9:00-21:00, Сб-Вс: 10:00-19:00', 'Удобная парковка', 1, 3, NOW()),
('Типо-графия на Комсомольской', 'г. Москва, Комсомольская площадь, д. 3', 55.775150, 37.654663, '+7 (495) 456-78-90', 'Круглосуточно', 'Рядом с метро Комсомольская, работаем 24/7', 1, 4, NOW()),
('Типо-графия в Южном Бутово', 'г. Москва, ул. Адмирала Лазарева, д. 52', 55.534543, 37.536026, '+7 (495) 567-89-01', 'Пн-Пт: 9:00-20:00, Сб-Вс: 10:00-18:00', 'Большой выставочный зал', 1, 5, NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `address` = VALUES(`address`),
  `latitude` = VALUES(`latitude`),
  `longitude` = VALUES(`longitude`);

-- Индексы для оптимизации запросов корзины (MySQL 5.x совместимость)
-- Проверяем и создаем индексы если их нет
SET @exist_idx_1 := (SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'cart' AND index_name = 'idx_cart_session_created');
SET @sqlstmt_1 := IF(@exist_idx_1 > 0, 'SELECT ''Index idx_cart_session_created already exists''',
    'CREATE INDEX idx_cart_session_created ON cart(session_id, created_at)');
PREPARE stmt_1 FROM @sqlstmt_1;
EXECUTE stmt_1;
DEALLOCATE PREPARE stmt_1;

SET @exist_idx_2 := (SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'cart' AND index_name = 'idx_cart_user_created');
SET @sqlstmt_2 := IF(@exist_idx_2 > 0, 'SELECT ''Index idx_cart_user_created already exists''',
    'CREATE INDEX idx_cart_user_created ON cart(user_id, created_at)');
PREPARE stmt_2 FROM @sqlstmt_2;
EXECUTE stmt_2;
DEALLOCATE PREPARE stmt_2;

-- ============================================
-- Конец миграции 010
-- ============================================
