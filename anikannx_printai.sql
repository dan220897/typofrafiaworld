-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Янв 09 2026 г., 14:27
-- Версия сервера: 5.7.21-20-beget-5.7.21-20-1-log
-- Версия PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `anikannx_printai`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admins`
--
-- Создание: Апр 06 2025 г., 19:23
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `binding_types`
--
-- Создание: Апр 08 2025 г., 22:29
--

DROP TABLE IF EXISTS `binding_types`;
CREATE TABLE `binding_types` (
  `id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `chat_messages`
--
-- Создание: Июн 12 2025 г., 07:02
--

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `chat_id` int(11) UNSIGNED NOT NULL,
  `sender_type` enum('user','ai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_json` tinyint(1) NOT NULL DEFAULT '0',
  `from_telegram` tinyint(1) DEFAULT '0',
  `telegram_message_id` bigint(20) DEFAULT NULL,
  `manager_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Имя менеджера из Telegram',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `chat_sessions`
--
-- Создание: Апр 03 2025 г., 16:51
--

DROP TABLE IF EXISTS `chat_sessions`;
CREATE TABLE `chat_sessions` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Новый чат',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Триггеры `chat_sessions`
--
DROP TRIGGER IF EXISTS `after_chat_session_insert`;
DELIMITER $$
CREATE TRIGGER `after_chat_session_insert` AFTER INSERT ON `chat_sessions` FOR EACH ROW BEGIN
    -- Создаем запись в telegram_chats со статусом pending
    INSERT INTO telegram_chats (chat_id, user_id, status)
    VALUES (NEW.id, NEW.user_id, 'pending')
    ON DUPLICATE KEY UPDATE status = status;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `design_services`
--
-- Создание: Апр 08 2025 г., 22:29
--

DROP TABLE IF EXISTS `design_services`;
CREATE TABLE `design_services` (
  `id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `icon` varchar(10) DEFAULT NULL,
  `tooltip` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--
-- Создание: Апр 08 2025 г., 11:09
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED DEFAULT NULL,
  `chat_id` int(11) UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` datetime NOT NULL,
  `upload_session_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `notification_log`
--
-- Создание: Апр 07 2025 г., 18:30
--

DROP TABLE IF EXISTS `notification_log`;
CREATE TABLE `notification_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `notification_type` enum('email','sms','system','status') NOT NULL,
  `message` text NOT NULL,
  `sms_result` text,
  `sms_id` varchar(100) DEFAULT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--
-- Создание: Апр 14 2025 г., 20:20
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_type` enum('regular','partner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `partner_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `service` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sides` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `density` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `design_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `design_link` text COLLATE utf8mb4_unicode_ci,
  `delivery` enum('self','delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'self',
  `address` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','processing','printing','ready','shipped','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `total_cost` decimal(10,2) NOT NULL,
  `privacy_agreed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `order_status_history`
--
-- Создание: Апр 07 2025 г., 20:04
--

DROP TABLE IF EXISTS `order_status_history`;
CREATE TABLE `order_status_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `status` enum('new','processing','printing','ready','shipped','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID пользователя, изменившего статус',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `parameter_options`
--
-- Создание: Апр 08 2025 г., 17:37
--

DROP TABLE IF EXISTS `parameter_options`;
CREATE TABLE `parameter_options` (
  `id` int(11) UNSIGNED NOT NULL,
  `option_id` varchar(50) NOT NULL,
  `param_id` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `price_modifier` decimal(10,2) DEFAULT '0.00',
  `price_multiplier` decimal(10,3) DEFAULT '1.000',
  `tooltip` text,
  `is_default` tinyint(1) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `partners`
--
-- Создание: Апр 07 2025 г., 13:02
--

DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Название компании',
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Имя контакта',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email для входа',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Телефон',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Хешированный пароль',
  `address` text COLLATE utf8mb4_unicode_ci COMMENT 'Адрес',
  `commission` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Комиссия партнера в %',
  `status` enum('active','inactive','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `partner_orders`
--
-- Создание: Апр 07 2025 г., 13:02
--

DROP TABLE IF EXISTS `partner_orders`;
CREATE TABLE `partner_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `original_order_id` int(11) UNSIGNED NOT NULL COMMENT 'ID оригинального заказа',
  `partner_id` int(11) UNSIGNED NOT NULL COMMENT 'ID партнера',
  `service` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sides` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `density` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `design_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT 'ID клиента',
  `delivery` enum('self','delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'self',
  `address` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','processing','printing','ready','shipped','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `partner_status` enum('new','accepted','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new' COMMENT 'Статус от партнера',
  `partner_comment` text COLLATE utf8mb4_unicode_ci COMMENT 'Комментарий партнера',
  `total_cost` decimal(10,2) NOT NULL,
  `partner_cost` decimal(10,2) NOT NULL COMMENT 'Стоимость для партнера',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `partner_order_status_history`
--
-- Создание: Апр 07 2025 г., 13:02
--

DROP TABLE IF EXISTS `partner_order_status_history`;
CREATE TABLE `partner_order_status_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `partner_order_id` int(11) UNSIGNED NOT NULL,
  `status` enum('new','accepted','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID партнера, изменившего статус',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--
-- Создание: Апр 10 2025 г., 14:56
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `chat_image` varchar(255) DEFAULT NULL COMMENT 'Путь к изображению для отображения в чат-боте'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_bag_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_bag_types`;
CREATE TABLE `service_bag_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_base_prices`
--
-- Создание: Апр 08 2025 г., 22:29
--

DROP TABLE IF EXISTS `service_base_prices`;
CREATE TABLE `service_base_prices` (
  `service_id` varchar(50) NOT NULL,
  `base_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_binding_methods`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_binding_methods`;
CREATE TABLE `service_binding_methods` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_binding_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_binding_types`;
CREATE TABLE `service_binding_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_cap_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_cap_types`;
CREATE TABLE `service_cap_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_color_count`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_color_count`;
CREATE TABLE `service_color_count` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_color_options`
--
-- Создание: Апр 10 2025 г., 08:58
--

DROP TABLE IF EXISTS `service_color_options`;
CREATE TABLE `service_color_options` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_cover_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_cover_types`;
CREATE TABLE `service_cover_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_density`
--
-- Создание: Апр 09 2025 г., 07:57
--

DROP TABLE IF EXISTS `service_density`;
CREATE TABLE `service_density` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_descriptions`
--
-- Создание: Апр 08 2025 г., 22:29
--

DROP TABLE IF EXISTS `service_descriptions`;
CREATE TABLE `service_descriptions` (
  `service_id` varchar(50) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_design_complexity`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_design_complexity`;
CREATE TABLE `service_design_complexity` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_disc_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_disc_types`;
CREATE TABLE `service_disc_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_dish_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_dish_types`;
CREATE TABLE `service_dish_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_document_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_document_types`;
CREATE TABLE `service_document_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_envelope_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_envelope_types`;
CREATE TABLE `service_envelope_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_film_thickness`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_film_thickness`;
CREATE TABLE `service_film_thickness` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_folding`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_folding`;
CREATE TABLE `service_folding` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_fold_count`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_fold_count`;
CREATE TABLE `service_fold_count` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_fold_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_fold_types`;
CREATE TABLE `service_fold_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_handle_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_handle_types`;
CREATE TABLE `service_handle_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_item_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_item_types`;
CREATE TABLE `service_item_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_lamination`
--
-- Создание: Апр 09 2025 г., 07:57
--

DROP TABLE IF EXISTS `service_lamination`;
CREATE TABLE `service_lamination` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_materials`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_materials`;
CREATE TABLE `service_materials` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_memory_sizes`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_memory_sizes`;
CREATE TABLE `service_memory_sizes` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_mounting_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_mounting_types`;
CREATE TABLE `service_mounting_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_mug_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_mug_types`;
CREATE TABLE `service_mug_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_packaging_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_packaging_types`;
CREATE TABLE `service_packaging_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_page_counts`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_page_counts`;
CREATE TABLE `service_page_counts` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_paper_types`
--
-- Создание: Апр 10 2025 г., 08:58
--

DROP TABLE IF EXISTS `service_paper_types`;
CREATE TABLE `service_paper_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_pen_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_pen_types`;
CREATE TABLE `service_pen_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_pocket_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_pocket_types`;
CREATE TABLE `service_pocket_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_print_areas`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_print_areas`;
CREATE TABLE `service_print_areas` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_processing`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_processing`;
CREATE TABLE `service_processing` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_processing_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_processing_types`;
CREATE TABLE `service_processing_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_product_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_product_types`;
CREATE TABLE `service_product_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_quantities`
--
-- Создание: Апр 13 2025 г., 14:36
--

DROP TABLE IF EXISTS `service_quantities`;
CREATE TABLE `service_quantities` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `multiplier` decimal(5,2) DEFAULT '1.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_resolutions`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_resolutions`;
CREATE TABLE `service_resolutions` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_shapes`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_shapes`;
CREATE TABLE `service_shapes` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_sheet_count`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_sheet_count`;
CREATE TABLE `service_sheet_count` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_sheet_month_count`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_sheet_month_count`;
CREATE TABLE `service_sheet_month_count` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_sides`
--
-- Создание: Апр 09 2025 г., 07:57
--

DROP TABLE IF EXISTS `service_sides`;
CREATE TABLE `service_sides` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_sign_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_sign_types`;
CREATE TABLE `service_sign_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_sizes`
--
-- Создание: Апр 09 2025 г., 07:57
--

DROP TABLE IF EXISTS `service_sizes`;
CREATE TABLE `service_sizes` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_spring_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_spring_types`;
CREATE TABLE `service_spring_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_structure_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_structure_types`;
CREATE TABLE `service_structure_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_substrates`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_substrates`;
CREATE TABLE `service_substrates` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_surfaces`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_surfaces`;
CREATE TABLE `service_surfaces` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_tshirt_types`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_tshirt_types`;
CREATE TABLE `service_tshirt_types` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `service_urgency`
--
-- Создание: Апр 10 2025 г., 08:59
--

DROP TABLE IF EXISTS `service_urgency`;
CREATE TABLE `service_urgency` (
  `id` varchar(50) NOT NULL,
  `service_id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `multiplier` decimal(5,2) DEFAULT '1.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `support_requests`
--
-- Создание: Апр 07 2025 г., 07:30
--

DROP TABLE IF EXISTS `support_requests`;
CREATE TABLE `support_requests` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `request_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','processing','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_chats`
--
-- Создание: Июн 12 2025 г., 06:49
--

DROP TABLE IF EXISTS `telegram_chats`;
CREATE TABLE `telegram_chats` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL COMMENT 'ID чата на сайте',
  `user_id` int(11) NOT NULL COMMENT 'ID пользователя',
  `telegram_group_id` varchar(100) DEFAULT NULL COMMENT 'ID группы в Telegram',
  `status` enum('pending','active','archived') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_settings`
--
-- Создание: Июн 12 2025 г., 06:59
--

DROP TABLE IF EXISTS `telegram_settings`;
CREATE TABLE `telegram_settings` (
  `key` varchar(50) NOT NULL,
  `value` text,
  `description` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_user_states`
--
-- Создание: Июн 12 2025 г., 06:49
--

DROP TABLE IF EXISTS `telegram_user_states`;
CREATE TABLE `telegram_user_states` (
  `telegram_user_id` bigint(20) NOT NULL,
  `state` varchar(50) DEFAULT NULL,
  `data` text COMMENT 'JSON данные состояния',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_webhook_logs`
--
-- Создание: Июн 12 2025 г., 06:49
--

DROP TABLE IF EXISTS `telegram_webhook_logs`;
CREATE TABLE `telegram_webhook_logs` (
  `id` int(11) NOT NULL,
  `update_id` bigint(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL COMMENT 'message, callback_query, etc',
  `data` text COMMENT 'JSON данные update',
  `processed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--
-- Создание: Апр 07 2025 г., 07:30
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Имя пользователя',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email пользователя',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Номер телефона',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Хешированный пароль',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',
  `last_login` datetime DEFAULT NULL COMMENT 'Дата последнего входа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_delivery_info`
--
-- Создание: Апр 11 2025 г., 10:38
--

DROP TABLE IF EXISTS `user_delivery_info`;
CREATE TABLE `user_delivery_info` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `delivery_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_settings`
--
-- Создание: Апр 03 2025 г., 15:38
--

DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE `user_settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `notification_email` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Получать уведомления по email',
  `notification_sms` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Получать SMS-уведомления',
  `theme` enum('light','dark','system') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `verification_codes`
--
-- Создание: Апр 03 2025 г., 15:29
--

DROP TABLE IF EXISTS `verification_codes`;
CREATE TABLE `verification_codes` (
  `id` int(11) UNSIGNED NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `attempts` int(2) UNSIGNED NOT NULL DEFAULT '0',
  `used` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `v_chat_messages_with_telegram`
-- (См. Ниже фактическое представление)
--
DROP VIEW IF EXISTS `v_chat_messages_with_telegram`;
CREATE TABLE `v_chat_messages_with_telegram` (
`id` int(11) unsigned
,`chat_id` int(11) unsigned
,`sender_type` enum('user','ai')
,`content` text
,`is_json` tinyint(1)
,`from_telegram` tinyint(1)
,`telegram_message_id` bigint(20)
,`manager_name` varchar(255)
,`created_at` datetime
,`user_id` int(11) unsigned
,`chat_title` varchar(255)
,`user_name` varchar(255)
,`user_phone` varchar(20)
,`telegram_group_id` varchar(100)
,`telegram_status` enum('pending','active','archived')
);

-- --------------------------------------------------------

--
-- Структура для представления `v_chat_messages_with_telegram`
--
DROP TABLE IF EXISTS `v_chat_messages_with_telegram`;

CREATE ALGORITHM=UNDEFINED DEFINER=`anikannx_printai`@`localhost` SQL SECURITY DEFINER VIEW `v_chat_messages_with_telegram`  AS SELECT `cm`.`id` AS `id`, `cm`.`chat_id` AS `chat_id`, `cm`.`sender_type` AS `sender_type`, `cm`.`content` AS `content`, `cm`.`is_json` AS `is_json`, `cm`.`from_telegram` AS `from_telegram`, `cm`.`telegram_message_id` AS `telegram_message_id`, `cm`.`manager_name` AS `manager_name`, `cm`.`created_at` AS `created_at`, `cs`.`user_id` AS `user_id`, `cs`.`title` AS `chat_title`, `u`.`name` AS `user_name`, `u`.`phone` AS `user_phone`, `tc`.`telegram_group_id` AS `telegram_group_id`, `tc`.`status` AS `telegram_status` FROM (((`chat_messages` `cm` join `chat_sessions` `cs` on((`cm`.`chat_id` = `cs`.`id`))) join `users` `u` on((`cs`.`user_id` = `u`.`id`))) left join `telegram_chats` `tc` on((`cs`.`id` = `tc`.`chat_id`))) ORDER BY `cm`.`created_at` DESC ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Индексы таблицы `binding_types`
--
ALTER TABLE `binding_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_created_at` (`chat_id`,`created_at`),
  ADD KEY `idx_telegram_msg` (`telegram_message_id`),
  ADD KEY `idx_chat_created` (`chat_id`,`created_at`);
ALTER TABLE `chat_messages` ADD FULLTEXT KEY `message_content_fulltext` (`content`);

--
-- Индексы таблицы `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `design_services`
--
ALTER TABLE `design_services`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `chat_id` (`chat_id`);

--
-- Индексы таблицы `notification_log`
--
ALTER TABLE `notification_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Индексы таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Индексы таблицы `parameter_options`
--
ALTER TABLE `parameter_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_option_parameter` (`param_id`);

--
-- Индексы таблицы `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `partner_orders`
--
ALTER TABLE `partner_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `original_order_id` (`original_order_id`),
  ADD KEY `partner_id` (`partner_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `partner_order_status_history`
--
ALTER TABLE `partner_order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_order_id` (`partner_order_id`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `service_bag_types`
--
ALTER TABLE `service_bag_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_base_prices`
--
ALTER TABLE `service_base_prices`
  ADD PRIMARY KEY (`service_id`);

--
-- Индексы таблицы `service_binding_methods`
--
ALTER TABLE `service_binding_methods`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_binding_types`
--
ALTER TABLE `service_binding_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_cap_types`
--
ALTER TABLE `service_cap_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_color_count`
--
ALTER TABLE `service_color_count`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_color_options`
--
ALTER TABLE `service_color_options`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_cover_types`
--
ALTER TABLE `service_cover_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_density`
--
ALTER TABLE `service_density`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_descriptions`
--
ALTER TABLE `service_descriptions`
  ADD PRIMARY KEY (`service_id`);

--
-- Индексы таблицы `service_design_complexity`
--
ALTER TABLE `service_design_complexity`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_disc_types`
--
ALTER TABLE `service_disc_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_dish_types`
--
ALTER TABLE `service_dish_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_document_types`
--
ALTER TABLE `service_document_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_envelope_types`
--
ALTER TABLE `service_envelope_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_film_thickness`
--
ALTER TABLE `service_film_thickness`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_folding`
--
ALTER TABLE `service_folding`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_fold_count`
--
ALTER TABLE `service_fold_count`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_fold_types`
--
ALTER TABLE `service_fold_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_handle_types`
--
ALTER TABLE `service_handle_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_item_types`
--
ALTER TABLE `service_item_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_lamination`
--
ALTER TABLE `service_lamination`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_materials`
--
ALTER TABLE `service_materials`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_memory_sizes`
--
ALTER TABLE `service_memory_sizes`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_mounting_types`
--
ALTER TABLE `service_mounting_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_mug_types`
--
ALTER TABLE `service_mug_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_packaging_types`
--
ALTER TABLE `service_packaging_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_page_counts`
--
ALTER TABLE `service_page_counts`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_paper_types`
--
ALTER TABLE `service_paper_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_pen_types`
--
ALTER TABLE `service_pen_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_pocket_types`
--
ALTER TABLE `service_pocket_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_print_areas`
--
ALTER TABLE `service_print_areas`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_processing`
--
ALTER TABLE `service_processing`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_processing_types`
--
ALTER TABLE `service_processing_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_product_types`
--
ALTER TABLE `service_product_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_quantities`
--
ALTER TABLE `service_quantities`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `service_id_2` (`service_id`);

--
-- Индексы таблицы `service_resolutions`
--
ALTER TABLE `service_resolutions`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_shapes`
--
ALTER TABLE `service_shapes`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_sheet_count`
--
ALTER TABLE `service_sheet_count`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_sheet_month_count`
--
ALTER TABLE `service_sheet_month_count`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_sides`
--
ALTER TABLE `service_sides`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_sign_types`
--
ALTER TABLE `service_sign_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_sizes`
--
ALTER TABLE `service_sizes`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_spring_types`
--
ALTER TABLE `service_spring_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_structure_types`
--
ALTER TABLE `service_structure_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_substrates`
--
ALTER TABLE `service_substrates`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_surfaces`
--
ALTER TABLE `service_surfaces`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_tshirt_types`
--
ALTER TABLE `service_tshirt_types`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `service_urgency`
--
ALTER TABLE `service_urgency`
  ADD PRIMARY KEY (`id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `support_requests`
--
ALTER TABLE `support_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `telegram_chats`
--
ALTER TABLE `telegram_chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `telegram_group_id` (`telegram_group_id`);

--
-- Индексы таблицы `telegram_settings`
--
ALTER TABLE `telegram_settings`
  ADD PRIMARY KEY (`key`);

--
-- Индексы таблицы `telegram_user_states`
--
ALTER TABLE `telegram_user_states`
  ADD PRIMARY KEY (`telegram_user_id`);

--
-- Индексы таблицы `telegram_webhook_logs`
--
ALTER TABLE `telegram_webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `update_id` (`update_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_unique` (`phone`),
  ADD KEY `created_at_idx` (`created_at`),
  ADD KEY `name_idx` (`name`(100));
ALTER TABLE `users` ADD FULLTEXT KEY `users_fulltext` (`name`,`email`,`phone`);

--
-- Индексы таблицы `user_delivery_info`
--
ALTER TABLE `user_delivery_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_unique` (`user_id`);

--
-- Индексы таблицы `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone` (`phone`),
  ADD KEY `expires_at` (`expires_at`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `notification_log`
--
ALTER TABLE `notification_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `parameter_options`
--
ALTER TABLE `parameter_options`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `partner_orders`
--
ALTER TABLE `partner_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `partner_order_status_history`
--
ALTER TABLE `partner_order_status_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `support_requests`
--
ALTER TABLE `support_requests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `telegram_chats`
--
ALTER TABLE `telegram_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `telegram_webhook_logs`
--
ALTER TABLE `telegram_webhook_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_delivery_info`
--
ALTER TABLE `user_delivery_info`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `parameter_options`
--
ALTER TABLE `parameter_options`
  ADD CONSTRAINT `fk_option_parameter` FOREIGN KEY (`param_id`) REFERENCES `service_parameters` (`param_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `partner_orders`
--
ALTER TABLE `partner_orders`
  ADD CONSTRAINT `partner_orders_ibfk_1` FOREIGN KEY (`original_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partner_orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `partner_order_status_history`
--
ALTER TABLE `partner_order_status_history`
  ADD CONSTRAINT `partner_order_status_history_ibfk_1` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `service_bag_types`
--
ALTER TABLE `service_bag_types`
  ADD CONSTRAINT `service_bag_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_base_prices`
--
ALTER TABLE `service_base_prices`
  ADD CONSTRAINT `service_base_prices_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_binding_methods`
--
ALTER TABLE `service_binding_methods`
  ADD CONSTRAINT `service_binding_methods_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_binding_types`
--
ALTER TABLE `service_binding_types`
  ADD CONSTRAINT `service_binding_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_cap_types`
--
ALTER TABLE `service_cap_types`
  ADD CONSTRAINT `service_cap_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_color_count`
--
ALTER TABLE `service_color_count`
  ADD CONSTRAINT `service_color_count_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_color_options`
--
ALTER TABLE `service_color_options`
  ADD CONSTRAINT `service_color_options_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_cover_types`
--
ALTER TABLE `service_cover_types`
  ADD CONSTRAINT `service_cover_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_density`
--
ALTER TABLE `service_density`
  ADD CONSTRAINT `service_density_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_descriptions`
--
ALTER TABLE `service_descriptions`
  ADD CONSTRAINT `service_descriptions_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_design_complexity`
--
ALTER TABLE `service_design_complexity`
  ADD CONSTRAINT `service_design_complexity_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_disc_types`
--
ALTER TABLE `service_disc_types`
  ADD CONSTRAINT `service_disc_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_dish_types`
--
ALTER TABLE `service_dish_types`
  ADD CONSTRAINT `service_dish_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_document_types`
--
ALTER TABLE `service_document_types`
  ADD CONSTRAINT `service_document_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_envelope_types`
--
ALTER TABLE `service_envelope_types`
  ADD CONSTRAINT `service_envelope_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_film_thickness`
--
ALTER TABLE `service_film_thickness`
  ADD CONSTRAINT `service_film_thickness_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_folding`
--
ALTER TABLE `service_folding`
  ADD CONSTRAINT `service_folding_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_fold_count`
--
ALTER TABLE `service_fold_count`
  ADD CONSTRAINT `service_fold_count_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_fold_types`
--
ALTER TABLE `service_fold_types`
  ADD CONSTRAINT `service_fold_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_handle_types`
--
ALTER TABLE `service_handle_types`
  ADD CONSTRAINT `service_handle_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_item_types`
--
ALTER TABLE `service_item_types`
  ADD CONSTRAINT `service_item_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_lamination`
--
ALTER TABLE `service_lamination`
  ADD CONSTRAINT `service_lamination_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_materials`
--
ALTER TABLE `service_materials`
  ADD CONSTRAINT `service_materials_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_memory_sizes`
--
ALTER TABLE `service_memory_sizes`
  ADD CONSTRAINT `service_memory_sizes_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_mounting_types`
--
ALTER TABLE `service_mounting_types`
  ADD CONSTRAINT `service_mounting_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_mug_types`
--
ALTER TABLE `service_mug_types`
  ADD CONSTRAINT `service_mug_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_packaging_types`
--
ALTER TABLE `service_packaging_types`
  ADD CONSTRAINT `service_packaging_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_page_counts`
--
ALTER TABLE `service_page_counts`
  ADD CONSTRAINT `service_page_counts_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_paper_types`
--
ALTER TABLE `service_paper_types`
  ADD CONSTRAINT `service_paper_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_pen_types`
--
ALTER TABLE `service_pen_types`
  ADD CONSTRAINT `service_pen_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_pocket_types`
--
ALTER TABLE `service_pocket_types`
  ADD CONSTRAINT `service_pocket_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_print_areas`
--
ALTER TABLE `service_print_areas`
  ADD CONSTRAINT `service_print_areas_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_processing`
--
ALTER TABLE `service_processing`
  ADD CONSTRAINT `service_processing_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_processing_types`
--
ALTER TABLE `service_processing_types`
  ADD CONSTRAINT `service_processing_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_product_types`
--
ALTER TABLE `service_product_types`
  ADD CONSTRAINT `service_product_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_resolutions`
--
ALTER TABLE `service_resolutions`
  ADD CONSTRAINT `service_resolutions_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_shapes`
--
ALTER TABLE `service_shapes`
  ADD CONSTRAINT `service_shapes_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_sheet_count`
--
ALTER TABLE `service_sheet_count`
  ADD CONSTRAINT `service_sheet_count_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_sheet_month_count`
--
ALTER TABLE `service_sheet_month_count`
  ADD CONSTRAINT `service_sheet_month_count_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_sides`
--
ALTER TABLE `service_sides`
  ADD CONSTRAINT `service_sides_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_sign_types`
--
ALTER TABLE `service_sign_types`
  ADD CONSTRAINT `service_sign_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_sizes`
--
ALTER TABLE `service_sizes`
  ADD CONSTRAINT `service_sizes_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_spring_types`
--
ALTER TABLE `service_spring_types`
  ADD CONSTRAINT `service_spring_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_structure_types`
--
ALTER TABLE `service_structure_types`
  ADD CONSTRAINT `service_structure_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_substrates`
--
ALTER TABLE `service_substrates`
  ADD CONSTRAINT `service_substrates_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_surfaces`
--
ALTER TABLE `service_surfaces`
  ADD CONSTRAINT `service_surfaces_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_tshirt_types`
--
ALTER TABLE `service_tshirt_types`
  ADD CONSTRAINT `service_tshirt_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `service_urgency`
--
ALTER TABLE `service_urgency`
  ADD CONSTRAINT `service_urgency_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `support_requests`
--
ALTER TABLE `support_requests`
  ADD CONSTRAINT `support_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_delivery_info`
--
ALTER TABLE `user_delivery_info`
  ADD CONSTRAINT `user_delivery_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
