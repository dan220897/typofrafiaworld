-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Янв 09 2026 г., 22:39
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
-- База данных: `anikannx_printtg`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admins`
--
-- Создание: Июн 13 2025 г., 06:13
-- Последнее обновление: Янв 07 2026 г., 08:34
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'super_admin, manager, operator',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `role`, `is_active`, `created_at`, `updated_at`, `last_login_at`, `remember_token`, `last_login`) VALUES
(1, 'admin', 'Mur645519!', 'Главный администратор', 'etatagency@ya.ru', '+79312024000', 'super_admin', 1, '2025-06-13 06:16:22', '2026-01-07 08:34:09', NULL, NULL, '2026-01-07 08:34:09');

-- --------------------------------------------------------

--
-- Структура таблицы `admin_logs`
--
-- Создание: Июн 13 2025 г., 20:03
-- Последнее обновление: Янв 07 2026 г., 08:34
--

DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 07:31:13'),
(2, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 07:51:46'),
(3, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 08:00:13'),
(4, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '84.17.46.67', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 09:10:00'),
(5, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '84.17.46.67', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 11:19:08'),
(6, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '84.17.46.67', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 15:14:58'),
(7, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '37.120.217.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 20:04:01'),
(8, 1, 'update_settings', 'settings', NULL, 'Обновлены основные настройки', '37.120.217.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-13 20:04:33'),
(9, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '109.252.56.11', 'Mozilla/5.0 (Linux; Android 11; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.7151.88 Mobile Safari/537.36', '2025-06-14 08:12:10'),
(10, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-14 08:12:54'),
(11, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-14 14:42:29'),
(12, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-14 16:14:24'),
(13, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-14 20:53:02'),
(14, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 07:41:02'),
(15, 1, 'create_order', 'order', 2, 'Создан заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 08:05:58'),
(16, 1, 'create_order', 'order', 3, 'Создан заказ #2025-0002', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 08:08:31'),
(17, 1, 'print_order', 'order', 3, 'Распечатан заказ #2025-0002', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 08:22:23'),
(18, 1, 'delete_order', 'order', 3, 'Удален заказ #2025-0002', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 08:31:05'),
(19, 1, 'delete_order', 'order', 2, 'Удален заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 08:32:18'),
(20, 1, 'create_order', 'order', 4, 'Создан заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 08:51:44'),
(21, 1, 'delete_order', 'order', 4, 'Удален заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 08:52:20'),
(22, 1, 'create_order', 'order', 7, 'Создан заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 10:03:09'),
(23, 1, 'update_payment_status', 'order', 7, 'Изменен статус оплаты заказа #2025-0001 на pending', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 10:03:50'),
(24, 1, 'delete_order', 'order', 7, 'Удален заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 10:21:36'),
(25, 1, 'create_order', 'order', 8, 'Создан заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 10:22:05'),
(26, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:14:56'),
(27, 1, 'delete_order', 'order', 8, 'Удален заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:16:19'),
(28, 1, 'create_order', 'order', 9, 'Создан заказ #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:16:44'),
(29, 1, 'generate_payment_link', 'order', 9, 'Создана платежная ссылка для заказа #2025-0001', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:19:34'),
(30, 1, 'update_payment_status', 'order', 9, 'Изменен статус оплаты заказа #2025-0001 на paid', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:20:02'),
(31, 1, 'update_order_status', 'order', 9, 'Изменен статус заказа #2025-0001 на confirmed', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:21:55'),
(32, 1, 'create_order', 'order', 10, 'Создан заказ #2025-0002', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:24:21'),
(33, 1, 'update_order_status', 'order', 10, 'Изменен статус заказа #2025-0002 на confirmed', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:29:01'),
(34, 1, 'update_payment_status', 'order', 10, 'Изменен статус оплаты заказа #2025-0002 на paid', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:29:12'),
(35, 1, 'check_payment_status', 'order', 10, 'Проверен статус платежа для заказа #2025-0002: pending', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:32:02'),
(36, 1, 'update_payment_status', 'order', 10, 'Изменен статус оплаты заказа #2025-0002 на paid', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:32:06'),
(37, 1, 'send_sms', 'user', 1, 'SMS отправлено пользователю. Телефон: +79312024000', NULL, NULL, '2025-06-15 12:51:44'),
(38, 1, 'delete_order', 'order', 10, 'Удален заказ #2025-0002', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:58:09'),
(39, 1, 'print_order', 'order', 9, 'Распечатан заказ #2025-0001', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 12:58:19'),
(40, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '149.22.90.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-15 16:21:24'),
(41, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-16 08:12:02'),
(42, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-16 19:46:57'),
(43, 1, 'send_sms', 'user', 6, 'SMS отправлено пользователю. Телефон: +79159158623', NULL, NULL, '2025-06-16 19:48:02'),
(44, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '109.252.56.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-17 05:03:03'),
(45, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '154.47.24.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-21 08:42:12'),
(46, 1, 'update_order_status', 'order', 9, 'Изменен статус заказа #2025-0001 на cancelled', '154.47.24.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', '2025-06-21 08:57:41'),
(47, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 10:05:36'),
(48, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '217.15.57.12', 'Mozilla/5.0 (Linux; Android 16; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.155 Mobile Safari/537.36', '2025-10-04 11:20:51'),
(49, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 11:33:43'),
(50, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '84.17.55.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:50:32'),
(51, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '178.176.73.33', 'Mozilla/5.0 (Linux; Android 16; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.207 Mobile Safari/537.36', '2025-10-04 14:11:18'),
(52, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '84.17.55.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 17:35:07'),
(53, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '213.87.153.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 08:14:58'),
(54, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '31.173.83.128', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-13 07:41:27'),
(55, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '156.146.33.98', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-14 08:43:32'),
(56, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '213.87.150.99', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-16 11:52:31'),
(57, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '213.87.150.99', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-16 12:04:00'),
(58, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '217.15.57.12', 'Mozilla/5.0 (Linux; Android 16; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.207 Mobile Safari/537.36', '2025-10-21 06:31:47'),
(59, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '213.87.137.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-07 12:46:23'),
(60, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '217.15.57.12', 'Mozilla/5.0 (Linux; Android 16; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.7390.122 Mobile Safari/537.36', '2025-11-07 20:44:25'),
(61, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '213.87.137.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 09:31:15'),
(62, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '217.15.57.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 YaBrowser/25.10.0.0 Safari/537.36', '2025-11-11 20:00:38'),
(63, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '213.87.135.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-12 09:36:21'),
(64, 1, 'create_order', 'order', 10, 'Создан заказ #2025-0002', '213.87.135.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-12 09:37:44'),
(65, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '217.15.57.12', 'Mozilla/5.0 (Linux; Android 11; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.7444.171 Mobile Safari/537.36', '2025-11-26 20:10:17'),
(66, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '46.242.8.79', 'Mozilla/5.0 (Linux; Android 16; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.7444.174 Mobile Safari/537.36', '2025-12-19 11:34:17'),
(67, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '46.242.8.79', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 12:54:12'),
(68, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '46.242.8.79', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 12:46:55'),
(69, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '46.242.8.79', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-28 12:08:25'),
(70, 1, 'login_sms', NULL, NULL, 'Вход через SMS', '79.127.160.81', 'Mozilla/5.0 (Linux; Android 16; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.7499.34 Mobile Safari/537.36', '2026-01-07 08:34:09');

-- --------------------------------------------------------

--
-- Структура таблицы `admin_permissions`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `permission` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'manage_orders, manage_users, manage_services, etc.',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `chats`
--
-- Создание: Окт 04 2025 г., 12:06
-- Последнее обновление: Янв 08 2026 г., 17:15
--

DROP TABLE IF EXISTS `chats`;
CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'active, closed, archived',
  `client_status` enum('new','in_progress','waiting_client','no_response','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `last_message_at` timestamp NULL DEFAULT NULL,
  `unread_user_count` int(11) DEFAULT '0',
  `unread_admin_count` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_admin_read_at` timestamp NULL DEFAULT NULL,
  `last_user_read_at` timestamp NULL DEFAULT NULL,
  `telegram_chat_id` bigint(20) DEFAULT NULL COMMENT 'ID чата в Telegram (для группы с топиками)',
  `telegram_thread_id` int(11) DEFAULT NULL COMMENT 'ID треда/топика в Telegram группе',
  `telegram_message_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Чаты с клиентами, включая интеграцию с Telegram';

--
-- Дамп данных таблицы `chats`
--

INSERT INTO `chats` (`id`, `user_id`, `admin_id`, `status`, `client_status`, `last_message_at`, `unread_user_count`, `unread_admin_count`, `created_at`, `updated_at`, `last_admin_read_at`, `last_user_read_at`, `telegram_chat_id`, `telegram_thread_id`, `telegram_message_id`) VALUES
(1, 1, NULL, 'active', 'new', '2025-06-15 12:28:25', 0, 0, '2025-06-14 07:52:25', '2025-06-15 13:02:56', NULL, '2025-06-15 12:29:16', NULL, NULL, NULL),
(2, 6, NULL, 'active', 'new', '2025-06-16 19:47:22', 2, 0, '2025-06-16 19:15:13', '2025-06-16 19:47:22', NULL, '2025-06-16 19:15:16', NULL, NULL, NULL),
(3, 5, NULL, 'active', 'new', '2025-06-19 14:05:51', 0, 0, '2025-06-18 13:39:00', '2025-10-04 10:07:00', NULL, '2025-06-19 19:26:46', NULL, NULL, NULL),
(4, 7, NULL, 'active', 'new', '2025-06-22 11:06:43', 0, 0, '2025-06-22 11:06:18', '2025-10-04 10:07:10', NULL, '2025-06-22 11:06:18', NULL, NULL, NULL),
(5, 8, NULL, 'active', 'new', '2025-06-23 12:00:16', 0, 0, '2025-06-23 12:00:14', '2025-10-04 12:30:16', NULL, '2025-06-23 12:00:14', NULL, NULL, NULL),
(6, 9, NULL, 'active', 'new', '2025-06-25 08:02:02', 0, 0, '2025-06-25 08:01:58', '2025-10-04 12:30:19', NULL, '2025-06-25 08:51:17', NULL, NULL, NULL),
(7, 10, NULL, 'active', 'new', '2025-06-25 10:17:33', 0, 0, '2025-06-25 10:16:44', '2025-10-04 12:30:20', NULL, '2025-06-25 11:21:11', NULL, NULL, NULL),
(8, 12, NULL, 'active', 'new', '2025-06-27 02:28:41', 0, 0, '2025-06-27 02:28:19', '2025-10-04 12:30:22', NULL, '2025-06-27 02:28:20', NULL, NULL, NULL),
(9, 13, NULL, 'active', 'new', '2025-06-27 16:49:38', 0, 0, '2025-06-27 16:49:27', '2025-10-04 12:30:23', NULL, '2025-06-27 16:49:27', NULL, NULL, NULL),
(10, 16, NULL, 'active', 'new', '2025-07-02 07:08:29', 0, 0, '2025-07-02 07:07:51', '2025-10-04 10:15:40', NULL, '2025-07-02 07:22:37', NULL, NULL, NULL),
(11, 19, NULL, 'active', 'new', '2025-07-07 23:06:31', 0, 0, '2025-07-07 23:03:58', '2025-10-04 10:15:33', NULL, '2025-07-07 23:05:39', NULL, NULL, NULL),
(12, 20, NULL, 'active', 'new', '2025-07-14 11:46:14', 0, 0, '2025-07-14 11:45:30', '2025-10-04 10:15:26', NULL, '2025-07-14 11:45:31', NULL, NULL, NULL),
(13, 21, NULL, 'active', 'new', '2025-07-15 14:09:28', 0, 0, '2025-07-15 14:09:21', '2025-10-04 10:09:14', NULL, '2025-07-15 14:10:03', NULL, NULL, NULL),
(14, 22, NULL, 'active', 'new', '2025-07-17 16:12:18', 0, 0, '2025-07-17 16:00:29', '2025-10-04 10:09:05', NULL, '2025-07-17 16:00:30', NULL, NULL, NULL),
(15, 23, NULL, 'active', 'new', '2025-07-18 07:22:30', 0, 0, '2025-07-18 07:21:33', '2025-10-04 10:09:01', NULL, '2025-07-18 07:21:33', NULL, NULL, NULL),
(16, 27, NULL, 'active', 'new', '2025-07-29 11:46:08', 0, 0, '2025-07-29 08:43:18', '2025-10-04 10:08:52', NULL, '2025-07-29 11:44:50', NULL, NULL, NULL),
(17, 30, NULL, 'active', 'new', '2025-08-06 08:46:01', 0, 0, '2025-08-06 08:45:22', '2025-10-04 10:08:33', NULL, '2025-08-06 08:45:23', NULL, NULL, NULL),
(18, 31, NULL, 'active', 'new', '2025-08-08 12:11:09', 0, 0, '2025-08-08 12:11:05', '2025-10-04 10:08:29', NULL, '2025-08-08 15:00:18', NULL, NULL, NULL),
(19, 33, NULL, 'active', 'new', '2025-08-13 09:50:26', 0, 0, '2025-08-13 09:49:31', '2025-10-04 10:07:54', NULL, '2025-08-13 09:58:57', NULL, NULL, NULL),
(20, 36, NULL, 'active', 'new', '2025-09-03 11:03:06', 0, 0, '2025-09-03 11:02:12', '2025-10-04 10:07:46', NULL, '2025-09-03 11:03:06', NULL, NULL, NULL),
(21, 37, NULL, 'active', 'new', '2025-09-08 08:18:50', 0, 0, '2025-09-08 08:18:48', '2025-10-04 10:07:40', NULL, '2025-09-08 08:18:48', NULL, NULL, NULL),
(22, 38, NULL, 'active', 'new', '2025-09-13 08:17:37', 0, 0, '2025-09-13 08:17:04', '2025-10-04 12:18:59', NULL, '2025-09-13 08:17:04', NULL, NULL, NULL),
(24, 41, NULL, 'active', 'new', '2025-09-20 11:20:06', 0, 0, '2025-09-20 11:20:03', '2025-10-04 10:07:21', NULL, '2025-09-20 11:20:07', NULL, NULL, NULL),
(28, 50, NULL, 'active', 'waiting_client', '2025-10-07 08:17:26', 0, 0, '2025-10-04 12:58:41', '2025-10-13 07:42:56', NULL, '2025-10-07 08:16:40', NULL, NULL, NULL),
(29, 56, NULL, 'active', 'new', '2025-10-16 12:04:32', 2, 0, '2025-10-16 11:51:06', '2025-10-16 12:04:32', NULL, '2025-10-16 12:00:26', NULL, NULL, NULL),
(30, 57, NULL, 'active', 'new', '2025-10-21 06:32:20', 4, 0, '2025-10-21 06:14:31', '2025-10-21 06:32:20', NULL, '2025-10-21 06:15:15', NULL, NULL, NULL),
(31, 61, NULL, 'active', 'new', '2025-11-07 12:56:50', 0, 0, '2025-11-07 12:44:55', '2025-11-11 20:02:42', NULL, '2025-11-07 12:50:28', NULL, NULL, NULL),
(32, 62, NULL, 'active', 'new', '2025-11-07 20:45:04', 8, 0, '2025-11-07 20:35:24', '2025-11-07 20:45:04', NULL, '2025-11-07 20:35:25', NULL, NULL, NULL),
(33, 67, NULL, 'active', 'new', '2025-11-11 20:01:37', 0, 0, '2025-11-11 19:54:18', '2025-11-11 20:01:58', NULL, '2025-11-11 20:01:58', NULL, NULL, NULL),
(34, 68, NULL, 'active', 'new', '2025-11-19 16:36:03', 0, 2, '2025-11-19 16:32:05', '2025-11-19 16:36:03', NULL, '2025-11-19 16:35:51', NULL, NULL, NULL),
(35, 71, NULL, 'active', 'new', '2025-11-26 11:35:24', 0, 3, '2025-11-26 11:27:08', '2025-11-26 11:35:24', NULL, '2025-11-26 11:34:29', NULL, NULL, NULL),
(36, 72, NULL, 'active', 'new', '2025-11-26 20:10:48', 4, 0, '2025-11-26 20:09:34', '2025-11-26 20:10:48', NULL, '2025-11-26 20:09:35', NULL, NULL, NULL),
(37, 73, NULL, 'active', 'new', '2025-11-28 11:28:38', 0, 0, '2025-11-28 11:26:50', '2025-12-28 12:09:14', NULL, '2025-11-28 11:28:28', NULL, NULL, NULL),
(38, 79, NULL, 'active', 'new', '2025-12-19 11:35:46', 0, 0, '2025-12-19 11:33:02', '2025-12-19 12:55:04', NULL, '2025-12-19 11:35:33', NULL, NULL, NULL),
(39, 87, NULL, 'active', 'new', '2026-01-07 08:36:28', 2, 2, '2026-01-07 08:33:04', '2026-01-07 08:36:28', NULL, '2026-01-07 08:35:44', NULL, NULL, NULL),
(40, 88, NULL, 'active', 'new', '2026-01-08 16:33:04', 0, 3, '2026-01-08 16:29:20', '2026-01-08 17:15:57', NULL, '2026-01-08 17:15:57', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `chat_messages`
--
-- Создание: Июн 14 2025 г., 07:09
--

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_type` enum('user','admin','system') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `dashboard_stats`
-- (См. Ниже фактическое представление)
--
DROP VIEW IF EXISTS `dashboard_stats`;
CREATE TABLE `dashboard_stats` (
`orders_today` bigint(21)
,`orders_week` bigint(21)
,`orders_month` bigint(21)
,`revenue_today` decimal(32,2)
,`revenue_week` decimal(32,2)
,`revenue_month` decimal(32,2)
,`new_users_today` bigint(21)
,`active_chats` bigint(21)
,`unread_messages` bigint(21)
);

-- --------------------------------------------------------

--
-- Структура таблицы `email_codes`
--
-- Создание: Окт 04 2025 г., 08:39
-- Последнее обновление: Янв 08 2026 г., 16:33
--

DROP TABLE IF EXISTS `email_codes`;
CREATE TABLE `email_codes` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(1) NOT NULL DEFAULT '0',
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` datetime NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `email_codes`
--

INSERT INTO `email_codes` (`id`, `email`, `code`, `attempts`, `is_used`, `expires_at`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'dan220897@gmail.com', '043240', 0, 0, '2025-10-04 11:48:14', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 11:43:14'),
(2, 'etatagency@yandex.ru', '280448', 1, 1, '2025-10-04 11:54:24', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 11:49:24'),
(3, 'etatagency@yandex.ru', '443801', 1, 1, '2025-10-04 12:14:33', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:09:33'),
(4, 'etatagency@yandex.ru', '335311', 0, 0, '2025-10-04 12:20:53', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:15:53'),
(5, 'typo-grafia@ya.ru', '408892', 0, 0, '2025-10-04 12:25:34', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:20:34'),
(6, 'typo-grafia@ya.ru', '970613', 0, 0, '2025-10-04 12:27:50', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:22:50'),
(7, 'dan220897@gmail.com', '529735', 0, 0, '2025-10-04 12:28:32', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:23:32'),
(8, 'dan220897@gmail.com', '481963', 0, 0, '2025-10-04 12:30:15', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:25:15'),
(9, 'typo-grafia@ya.ru', '491596', 1, 1, '2025-10-04 12:49:41', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:44:41'),
(10, 'typo-grafia@ya.ru', '307111', 1, 1, '2025-10-04 12:56:31', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 12:51:31'),
(11, 'diane_kilchurina@mail.ru', '728884', 1, 1, '2025-10-04 14:23:32', '217.15.57.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15', '2025-10-04 14:18:32'),
(12, 'diane_kilchurina@mail.ru', '263323', 1, 1, '2025-10-04 20:54:13', '84.17.55.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-04 20:49:13'),
(13, 'andinomobile@gmail.com', '583741', 0, 0, '2025-10-06 14:10:16', '89.113.157.169', 'Mozilla/5.0 (Linux; arm_64; Android 15; 23078PND5G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.7204.116 YaBrowser/25.8.6.116.00 SA/3 Mobile Safari/537.36', '2025-10-06 14:05:16'),
(14, 'andinomobile@gmail.com', '411182', 0, 0, '2025-10-11 17:23:35', '188.32.82.38', 'Mozilla/5.0 (Linux; arm_64; Android 15; 23078PND5G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.7204.64 YaBrowser/25.8.8.64.00 SA/3 Mobile Safari/537.36', '2025-10-11 17:18:35'),
(15, 'eva13@yandex.ru', '901704', 0, 0, '2025-10-14 13:24:45', '128.70.165.113', 'Mozilla/5.0 (Linux; arm_64; Android 15; 2406APNFAG) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.7204.64 YaBrowser/25.8.8.64.00 SA/3 Mobile Safari/537.36', '2025-10-14 13:19:45'),
(16, 'eva13@yandex.ru', '409725', 0, 0, '2025-10-14 13:26:41', '128.70.165.113', 'Mozilla/5.0 (Linux; arm_64; Android 15; 2406APNFAG) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.7204.64 YaBrowser/25.8.8.64.00 SA/3 Mobile Safari/537.36', '2025-10-14 13:21:41'),
(17, 'polyatryikk@gmail.com', '853318', 0, 0, '2025-10-16 12:41:37', '176.15.47.165', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-16 12:36:37'),
(18, 'pola02139@gmail.com', '368895', 0, 0, '2025-10-16 12:42:25', '176.15.47.165', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-16 12:37:25'),
(19, 'pwld01032@gmail.com', '024170', 0, 0, '2025-10-16 12:43:04', '176.15.47.165', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-16 12:38:04'),
(20, 'pwld01032@gmail.com', '691944', 0, 0, '2025-10-16 12:50:50', '176.15.47.165', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-16 12:45:50'),
(21, 'pwld01032@gmail.com', '646427', 0, 0, '2025-10-16 12:51:03', '176.15.47.165', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-16 12:46:03'),
(22, 'v-valker@mail.ru', '101344', 1, 1, '2025-10-16 14:55:53', '95.84.235.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-16 14:50:53'),
(23, 'MironovaEE3@mos.ru', '345016', 1, 1, '2025-10-21 09:18:58', '90.154.106.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-10-21 09:13:58'),
(24, 'phoenixa1a@yandex.ru', '269035', 0, 0, '2025-10-28 14:41:16', '82.144.67.117', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-28 14:36:16'),
(25, 'phoenixa1a@yandex.ru', '806847', 1, 0, '2025-10-28 14:43:01', '82.144.67.117', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-28 14:38:01'),
(26, 'phoenixa1a@yandex.ru', '535911', 0, 0, '2025-10-28 14:49:40', '82.144.67.117', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-28 14:44:40'),
(27, 'kinodrama@mail.ru', '809643', 0, 0, '2025-10-28 16:50:36', '92.36.2.173', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2025-10-28 16:45:36'),
(28, 'phoenixa1a@yandex.ru', '365521', 0, 0, '2025-10-29 10:06:19', '213.5.224.66', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', '2025-10-29 10:01:19'),
(29, 'tereshkina@termst.ru', '083272', 0, 0, '2025-11-06 11:10:00', '89.175.56.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-11-06 11:05:00'),
(30, 'tereshkina@termst.ru', '724841', 0, 0, '2025-11-06 11:15:08', '89.175.56.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-11-06 11:10:08'),
(31, 'y.solovyeva@moscow.tran-express.ru', '128822', 0, 0, '2025-11-07 15:49:27', '194.135.9.242', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-07 15:44:27'),
(32, 'y.solovyeva@moscow.tran-express.ru', '988220', 1, 1, '2025-11-07 15:49:45', '194.135.9.242', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-07 15:44:45'),
(33, 'nastya-online@mail.ru', '203785', 1, 1, '2025-11-07 23:39:40', '46.39.229.209', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-11-07 23:34:40'),
(34, 'bet.xarmon@yandex.ru', '357028', 0, 0, '2025-11-09 16:08:29', '128.70.163.129', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-11-09 16:03:29'),
(35, 'bet.xarmon@yandex.ru', '116388', 0, 0, '2025-11-09 16:09:18', '128.70.163.129', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-11-09 16:04:18'),
(36, 'bet.xarmon@yandex.ru', '035458', 0, 0, '2025-11-09 16:09:57', '128.70.163.129', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-11-09 16:04:57'),
(37, 'arinainozemtsevaa@gmail.com', '836647', 0, 0, '2025-11-09 16:10:19', '128.70.163.129', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-11-09 16:05:19'),
(38, 'arinainozemtsevaa@gmail.com', '703665', 0, 0, '2025-11-09 16:11:44', '128.70.163.129', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-11-09 16:06:44'),
(39, 'ea@gp-agency.ru', '227014', 0, 0, '2025-11-11 16:19:23', '37.204.18.139', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Safari/605.1.15', '2025-11-11 16:14:23'),
(40, 'ea@gp-agency.ru', '916461', 0, 0, '2025-11-11 16:20:11', '37.204.18.139', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Safari/605.1.15', '2025-11-11 16:15:11'),
(41, 'e.alferova@12kosmonavtov.ru', '286500', 0, 0, '2025-11-11 16:21:09', '37.204.18.139', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Safari/605.1.15', '2025-11-11 16:16:09'),
(42, 'e.alferova@12kosmonavtov.ru', '193634', 0, 0, '2025-11-11 16:21:41', '37.204.18.139', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Safari/605.1.15', '2025-11-11 16:16:41'),
(43, 'ea@gp-agency.ru', '239327', 0, 0, '2025-11-11 16:22:55', '37.204.18.139', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Safari/605.1.15', '2025-11-11 16:17:55'),
(44, 'danleyz@mail.ru', '563959', 1, 1, '2025-11-11 22:58:27', '46.242.15.62', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-11-11 22:53:27'),
(45, 'ana.detenkova@yandex.ru', '841673', 0, 0, '2025-11-18 19:05:55', '79.139.182.131', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', '2025-11-18 19:00:55'),
(46, 'ana.detenkova@yandex.ru', '128602', 0, 0, '2025-11-18 19:08:20', '79.139.182.131', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', '2025-11-18 19:03:20'),
(47, 'ana.detenkova@yandex.ru', '734226', 0, 0, '2025-11-19 14:35:33', '213.87.146.37', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', '2025-11-19 14:30:33'),
(48, 'ana.detenkova@yandex.ru', '354766', 0, 0, '2025-11-19 16:10:21', '92.36.22.129', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', '2025-11-19 16:05:21'),
(49, 'etatagency@yandex.ru', '924067', 0, 0, '2025-11-19 16:34:40', '213.87.156.137', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 16:29:40'),
(50, 'ana.detenkova@yandex.ru', '919326', 0, 0, '2025-11-19 16:35:00', '92.36.22.129', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', '2025-11-19 16:30:00'),
(51, 'ana.detenkova@yandex.ru', '181928', 0, 0, '2025-11-19 19:32:42', '79.139.187.38', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', '2025-11-19 19:27:42'),
(52, 'ana.detenkova@yandex.ru', '098671', 1, 1, '2025-11-19 19:36:36', '79.139.187.38', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', '2025-11-19 19:31:36'),
(53, 'guryanovaanastasia03@yandex.ru', '437252', 0, 0, '2025-11-21 11:42:02', '109.252.108.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 YaBrowser/25.10.0.0 Safari/537.36', '2025-11-21 11:37:02'),
(54, 'dozamyatkin@gmail.com', '833513', 0, 0, '2025-11-24 00:46:34', '212.233.86.206', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.10.1.710.10 YaApp_iOS/2510.1 YaApp_iOS_Browser/2510.1 Safari/604.1 SA/3', '2025-11-24 00:41:34'),
(55, 'dozamyatkin@gmail.com', '158586', 0, 0, '2025-11-24 01:00:35', '212.233.86.206', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.10.1.710.10 YaApp_iOS/2510.1 YaApp_iOS_Browser/2510.1 Safari/604.1 SA/3', '2025-11-24 00:55:35'),
(56, 'jchugunova@mail.ru', '808081', 1, 1, '2025-11-26 14:31:47', '89.113.155.160', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-11-26 14:26:47'),
(57, 'andinomobile@gmail.com', '809260', 0, 0, '2025-11-26 18:38:15', '188.32.82.38', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-26 18:33:15'),
(58, 'karih.nastasya@mail.ru', '672821', 1, 1, '2025-11-26 23:14:15', '46.31.25.196', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-11-26 23:09:15'),
(59, 'kbk2022@mail.ru', '035823', 1, 1, '2025-11-28 14:31:22', '128.70.185.18', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-11-28 14:26:22'),
(60, 'diane_kilchurina@mail.ru', '190629', 0, 0, '2025-12-07 13:41:51', '217.15.57.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 YaBrowser/25.10.0.0 Safari/537.36', '2025-12-07 13:36:51'),
(61, 'p.pitchugin@gmail.com', '273772', 0, 0, '2025-12-08 18:05:40', '213.87.133.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-08 18:00:40'),
(62, 'p.pitchugin@gmail.com', '240294', 0, 0, '2025-12-08 18:07:19', '213.87.133.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-08 18:02:19'),
(63, 'mkhirina26@ya.ru', '558842', 0, 0, '2025-12-10 17:17:50', '83.143.87.125', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-12-10 17:12:50'),
(64, 'shershan52@gmail.com', '480325', 0, 0, '2025-12-11 09:00:58', '92.36.106.33', 'Mozilla/5.0 (Linux; arm_64; Android 12; ADY-LX9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.60 YaBrowser/25.10.6.60.00 SA/3 Mobile Safari/537.36', '2025-12-11 08:55:58'),
(65, 'artyrhaims2@gmail.com', '566514', 0, 0, '2025-12-17 12:25:36', '217.15.57.139', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', '2025-12-17 12:20:36'),
(66, 'latika2010a@yandex.ru', '025627', 0, 0, '2025-12-17 19:27:59', '95.24.74.127', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-12-17 19:22:59'),
(67, 'latika2010a@yandex.ru', '484483', 0, 0, '2025-12-17 19:28:42', '95.24.74.127', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-12-17 19:23:42'),
(68, 'purchaserf2@phoenix-plus.ru', '137369', 1, 1, '2025-12-19 14:37:33', '193.93.121.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 YaBrowser/25.10.0.0 Safari/537.36', '2025-12-19 14:32:33'),
(69, 'tarn.ann@yandex.ru', '678020', 0, 0, '2025-12-21 12:05:09', '79.139.255.137', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-21 12:00:09'),
(70, 'tarn.ann@yandex.ru', '809266', 0, 0, '2025-12-21 12:06:57', '79.139.255.137', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-21 12:01:57'),
(71, 'a.meteleva@gmail.com', '909378', 0, 0, '2025-12-23 16:01:31', '31.173.82.162', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-12-23 15:56:31'),
(72, 'a.meteleva@gmail.com', '424713', 0, 0, '2025-12-23 16:02:21', '31.173.82.162', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-12-23 15:57:21'),
(73, 'a.meteleva@gmail.com', '174286', 0, 0, '2025-12-23 16:04:01', '31.173.82.162', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', '2025-12-23 15:59:01'),
(74, 'valeriakusnarenko499@gmail.com', '564961', 0, 0, '2025-12-24 23:25:31', '37.230.157.7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.12.1.773.10 YaApp_iOS/2512.1 YaApp_iOS_Browser/2512.1 Safari/604.1 SA/3', '2025-12-24 23:20:31'),
(75, 'valeriakusnarenko499@gmail.com', '662588', 0, 0, '2025-12-24 23:26:40', '37.230.157.7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.12.1.773.10 YaApp_iOS/2512.1 YaApp_iOS_Browser/2512.1 Safari/604.1 SA/3', '2025-12-24 23:21:40'),
(76, 'kushnarenkovaleriia@yandex.ru', '230208', 0, 0, '2025-12-24 23:27:28', '37.230.157.7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.12.1.773.10 YaApp_iOS/2512.1 YaApp_iOS_Browser/2512.1 Safari/604.1 SA/3', '2025-12-24 23:22:28'),
(77, 'ads@isrp.ru', '007737', 0, 0, '2025-12-25 18:11:30', '185.238.201.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:06:30'),
(78, 'isrp0112@yandex.ru', '829318', 0, 0, '2025-12-25 18:13:47', '185.238.201.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:08:47'),
(79, 'lisandfox@gmail.com', '723507', 0, 0, '2025-12-30 02:06:27', '46.39.34.219', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.12.3.617.10 YaApp_iOS/2512.3 YaApp_iOS_Browser/2512.3 Safari/604.1 SA/3 Version/17.5', '2025-12-30 02:01:27'),
(80, 'lisandfox@gmail.com', '532072', 0, 0, '2025-12-30 02:07:46', '46.39.34.219', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.12.3.617.10 YaApp_iOS/2512.3 YaApp_iOS_Browser/2512.3 Safari/604.1 SA/3 Version/17.5', '2025-12-30 02:02:46'),
(81, 'natasha_kezich@mail.ru', '285183', 1, 1, '2026-01-07 11:38:01', '194.28.28.234', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', '2026-01-07 11:33:01'),
(82, 'anastasia_novikova2015@mail.ru', '022696', 1, 1, '2026-01-08 19:33:59', '31.173.85.134', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-01-08 19:28:59'),
(83, 'akadare832@gmail.com', '847345', 0, 0, '2026-01-08 19:34:13', '92.36.81.75', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/23B85 Safari/604.1', '2026-01-08 19:29:13'),
(84, 'akadare832@gmail.com', '783068', 0, 0, '2026-01-08 19:34:41', '92.36.81.75', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/23B85 Safari/604.1', '2026-01-08 19:29:41'),
(85, 'akadare833@gmail.com', '420999', 0, 0, '2026-01-08 19:35:42', '92.36.81.75', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', '2026-01-08 19:30:42'),
(86, 'anastasia_novikova2015@mail.ru', '405313', 1, 1, '2026-01-08 19:37:13', '31.173.85.134', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-01-08 19:32:13'),
(87, 'akadare832@gmail.com', '074562', 0, 0, '2026-01-08 19:38:20', '92.36.81.75', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', '2026-01-08 19:33:20'),
(88, 'akadare832@gmail.com', '448361', 0, 0, '2026-01-08 19:38:39', '92.36.81.75', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', '2026-01-08 19:33:39');

-- --------------------------------------------------------

--
-- Структура таблицы `login_history`
--
-- Создание: Окт 04 2025 г., 08:40
-- Последнее обновление: Янв 08 2026 г., 16:32
--

DROP TABLE IF EXISTS `login_history`;
CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `user_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'admin, user',
  `user_id` int(11) NOT NULL,
  `login_method` enum('sms','email','password') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sms',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'success' COMMENT 'success, failed',
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `login_history`
--

INSERT INTO `login_history` (`id`, `user_type`, `user_id`, `login_method`, `ip_address`, `user_agent`, `location`, `status`, `failure_reason`, `created_at`) VALUES
(1, 'user', 1, 'sms', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', NULL, 'success', NULL, '2025-06-14 07:52:25'),
(2, 'user', 1, 'sms', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', NULL, 'success', NULL, '2025-06-14 14:41:55'),
(3, 'user', 1, 'sms', '45.12.223.210', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-06-14 16:13:19'),
(4, 'user', 1, 'sms', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36', NULL, 'success', NULL, '2025-06-15 12:24:39'),
(5, 'user', 6, 'sms', '188.170.78.80', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-06-16 19:15:13'),
(6, 'user', 5, 'sms', '91.193.179.148', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-06-18 13:39:00'),
(7, 'user', 5, 'sms', '91.193.179.148', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-06-19 14:05:34'),
(8, 'user', 7, 'sms', '213.87.156.180', 'Mozilla/5.0 (Linux; arm_64; Android 14; V2202) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 YaBrowser/21.9.0.370.00 SA/3 Mobile Safari/537.36', NULL, 'failed', 'Неверный код', '2025-06-22 11:06:01'),
(9, 'user', 7, 'sms', '213.87.156.180', 'Mozilla/5.0 (Linux; arm_64; Android 14; V2202) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 YaBrowser/21.9.0.370.00 SA/3 Mobile Safari/537.36', NULL, 'success', NULL, '2025-06-22 11:06:18'),
(10, 'user', 8, 'sms', '185.195.71.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-06-23 12:00:14'),
(11, 'user', 9, 'sms', '195.9.194.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, 'success', NULL, '2025-06-25 08:01:58'),
(12, 'user', 10, 'sms', '213.87.160.183', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-06-25 10:16:43'),
(13, 'user', 11, 'sms', '128.204.79.16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 YaBrowser/25.6.0.1838.10 SA/3 Mobile/15E148 Safari/604.1', NULL, 'failed', 'Неверный код', '2025-06-26 12:01:15'),
(14, 'user', 11, 'sms', '128.204.79.18', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 YaBrowser/25.6.0.1838.10 SA/3 Mobile/15E148 Safari/604.1', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-06-26 12:06:08'),
(15, 'user', 12, 'sms', '46.138.236.33', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', NULL, 'success', NULL, '2025-06-27 02:28:19'),
(16, 'user', 13, 'sms', '95.25.32.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, 'success', NULL, '2025-06-27 16:49:27'),
(17, 'user', 16, 'sms', '212.33.28.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 YaBrowser/25.6.0.0 Safari/537.36', NULL, 'success', NULL, '2025-07-02 07:07:51'),
(18, 'user', 17, 'sms', '65.109.15.159', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-07 15:15:18'),
(19, 'user', 17, 'sms', '65.109.15.159', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-07 15:15:33'),
(20, 'user', 17, 'sms', '65.109.15.159', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-07 15:15:34'),
(21, 'user', 17, 'sms', '65.109.15.159', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-07 15:15:34'),
(22, 'user', 17, 'sms', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-07 15:16:37'),
(23, 'user', 17, 'sms', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-07 15:17:00'),
(24, 'user', 17, 'sms', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-07 15:18:09'),
(25, 'user', 19, 'sms', '109.252.130.85', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-07-07 23:03:58'),
(26, 'user', 20, 'sms', '176.107.96.55', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.7.4.411.10 YaApp_iOS/2507.4 YaApp_iOS_Browser/2507.4 Safari/604.1 SA/3 SAPublic/0', NULL, 'success', NULL, '2025-07-14 11:45:30'),
(27, 'user', 21, 'sms', '128.204.77.66', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.6.0.1819.10 YaApp_iOS/2506.0 YaApp_iOS_Browser/2506.0 Safari/604.1 SA/3', NULL, 'success', NULL, '2025-07-15 14:09:21'),
(28, 'user', 22, 'sms', '31.28.6.120', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', NULL, 'success', NULL, '2025-07-17 16:00:29'),
(29, 'user', 23, 'sms', '176.59.55.37', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-07-18 07:21:33'),
(30, 'user', 24, 'sms', '217.15.57.7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6 Safari/605.1.15', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-23 14:11:04'),
(31, 'user', 27, 'sms', '87.251.72.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, 'success', NULL, '2025-07-29 08:43:18'),
(32, 'user', 27, 'sms', '178.238.122.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, 'success', NULL, '2025-07-29 08:48:35'),
(33, 'user', 27, 'sms', '178.238.122.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, 'success', NULL, '2025-07-29 08:50:26'),
(34, 'user', 28, 'sms', '128.204.79.242', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'failed', 'Превышен лимит отправки SMS. Попробуйте позже.', '2025-07-30 07:11:46'),
(35, 'user', 30, 'sms', '87.76.12.135', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', NULL, 'success', NULL, '2025-08-06 08:45:22'),
(36, 'user', 31, 'sms', '212.233.87.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-08-08 12:11:05'),
(37, 'user', 33, 'sms', '81.200.31.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, 'success', NULL, '2025-08-13 09:49:31'),
(38, 'user', 36, 'sms', '86.62.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-09-03 11:02:12'),
(39, 'user', 37, 'sms', '91.193.177.201', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', NULL, 'success', NULL, '2025-09-08 08:18:48'),
(40, 'user', 38, 'sms', '176.193.63.145', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-09-13 08:17:04'),
(41, 'user', 39, 'sms', '89.113.153.46', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-09-15 14:30:42'),
(42, 'user', 41, 'sms', '86.110.218.131', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0 (Edition Yx 08)', NULL, 'success', NULL, '2025-09-20 11:20:03'),
(43, 'user', 48, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-10-04 08:50:28'),
(44, 'user', 48, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-10-04 09:10:30'),
(45, 'user', 48, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки кодов. Попробуйте позже.', '2025-10-04 09:20:03'),
(46, 'user', 48, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки кодов. Попробуйте позже.', '2025-10-04 09:20:26'),
(47, 'user', 48, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки кодов. Попробуйте позже.', '2025-10-04 09:44:27'),
(48, 'user', 49, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-10-04 09:45:01'),
(49, 'user', 49, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки кодов. Попробуйте позже.', '2025-10-04 09:48:08'),
(50, 'user', 49, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'failed', 'Превышен лимит отправки кодов. Попробуйте позже.', '2025-10-04 09:50:03'),
(51, 'user', 49, 'email', '185.77.216.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-10-04 09:51:49'),
(52, 'user', 50, 'email', '217.15.57.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15', NULL, 'success', NULL, '2025-10-04 11:19:12'),
(53, 'user', 50, 'email', '84.17.55.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-10-04 17:49:27'),
(54, 'user', 55, 'email', '176.15.47.165', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', NULL, 'failed', 'Неверный код', '2025-10-16 09:47:10'),
(55, 'user', 56, 'email', '95.84.235.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-10-16 11:51:06'),
(56, 'user', 57, 'email', '90.154.106.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-10-21 06:14:31'),
(57, 'user', 58, 'email', '82.144.67.117', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', NULL, 'failed', 'Код истек. Запросите новый код.', '2025-10-28 11:46:36'),
(58, 'user', 61, 'email', '194.135.9.242', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', NULL, 'success', NULL, '2025-11-07 12:44:55'),
(59, 'user', 62, 'email', '46.39.229.209', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-11-07 20:35:24'),
(60, 'user', 67, 'email', '46.242.15.62', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', NULL, 'success', NULL, '2025-11-11 19:54:18'),
(61, 'user', 68, 'email', '79.139.187.38', 'Mozilla/5.0 (Linux; arm_64; Android 13; V2247) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.7339.876 YaSearchBrowser/25.103.1 BroPP/1.0 YaSearchApp/25.103.1 webOmni SA/3 Mobile Safari/537.36', NULL, 'success', NULL, '2025-11-19 16:32:05'),
(62, 'user', 71, 'email', '89.113.155.160', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-11-26 11:27:08'),
(63, 'user', 72, 'email', '46.31.25.196', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-11-26 20:09:34'),
(64, 'user', 73, 'email', '128.70.185.18', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2025-11-28 11:26:50'),
(65, 'user', 79, 'email', '193.93.121.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 YaBrowser/25.10.0.0 Safari/537.36', NULL, 'success', NULL, '2025-12-19 11:33:02'),
(66, 'user', 87, 'email', '194.28.28.234', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2026-01-07 08:33:04'),
(67, 'user', 88, 'email', '31.173.85.134', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2026-01-08 16:29:20'),
(68, 'user', 88, 'email', '31.173.85.134', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', NULL, 'success', NULL, '2026-01-08 16:32:26');

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--
-- Создание: Июн 14 2025 г., 08:30
-- Последнее обновление: Янв 08 2026 г., 16:33
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'user, admin, system',
  `sender_id` int(11) DEFAULT NULL,
  `message_text` text COLLATE utf8mb4_unicode_ci,
  `message_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'text' COMMENT 'text, file, order_link, system',
  `is_read_admin` tinyint(1) DEFAULT '0',
  `is_read_user` tinyint(1) DEFAULT '0',
  `metadata` text COLLATE utf8mb4_unicode_ci COMMENT 'дополнительные данные в формате JSON',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0' COMMENT 'Прочитано ли сообщение'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`id`, `chat_id`, `sender_type`, `sender_id`, `message_text`, `message_type`, `is_read_admin`, `is_read_user`, `metadata`, `created_at`, `is_read`) VALUES
(1, 1, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 1, 1, NULL, '2025-06-14 07:52:25', 1),
(2, 1, 'user', 1, 'Хочу заказать визитки', 'text', 1, 0, NULL, '2025-06-14 07:52:52', 1),
(3, 1, 'user', 1, 'как дела', 'text', 1, 0, NULL, '2025-06-14 07:53:33', 1),
(4, 1, 'user', 1, 'Хочу заказать визитки', 'text', 1, 0, NULL, '2025-06-14 07:55:10', 1),
(5, 1, 'user', 1, 'Нужна консультация', 'text', 1, 0, NULL, '2025-06-14 07:57:28', 1),
(6, 1, 'user', 1, 'Хочу заказать визитки', 'text', 1, 0, NULL, '2025-06-14 08:05:38', 1),
(7, 1, 'user', 1, 'привет как дела', 'text', 1, 0, NULL, '2025-06-14 08:05:57', 1),
(8, 1, 'user', 1, 'привет', 'text', 1, 0, NULL, '2025-06-14 08:11:39', 1),
(9, 1, 'admin', 1, 'Тестовое сообщение 11:39:23', 'text', 0, 1, NULL, '2025-06-14 08:39:23', 0),
(10, 1, 'admin', 1, 'привет', 'text', 0, 1, NULL, '2025-06-14 08:39:49', 0),
(11, 1, 'user', 1, 'как дела', 'text', 0, 0, NULL, '2025-06-14 08:40:11', 1),
(12, 1, 'admin', 1, 'все отлично', 'text', 0, 1, NULL, '2025-06-14 08:40:39', 0),
(13, 1, 'user', 1, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-06-14 08:43:59', 1),
(14, 1, 'admin', 1, 'вот фото', 'text', 0, 1, NULL, '2025-06-14 08:44:27', 0),
(15, 1, 'user', 1, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-06-14 12:07:03', 1),
(16, 1, 'user', 1, 'Интересует услуга: Флаеры и листовки', 'text', 0, 0, NULL, '2025-06-14 12:07:09', 1),
(17, 1, 'user', 1, 'Нужны баннеры', 'text', 0, 0, NULL, '2025-06-14 12:07:18', 1),
(18, 1, 'user', 1, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-06-14 12:22:03', 1),
(19, 1, 'user', 1, 'привет', 'text', 0, 0, NULL, '2025-06-14 12:23:17', 1),
(20, 1, 'user', 1, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-06-14 12:28:07', 1),
(21, 1, 'user', 1, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-06-14 12:43:49', 1),
(22, 1, 'user', 1, 'Есть ли доставка?', 'text', 0, 0, NULL, '2025-06-14 12:44:04', 1),
(23, 1, 'user', 1, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-06-14 16:12:26', 1),
(24, 1, 'user', 1, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-06-14 16:13:24', 1),
(25, 1, 'admin', 1, 'хорошо', 'text', 0, 1, NULL, '2025-06-14 16:14:36', 0),
(26, 1, 'user', 1, 'ап', 'text', 0, 0, NULL, '2025-06-14 16:15:26', 1),
(27, 1, 'user', 1, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-06-14 16:16:44', 1),
(28, 1, 'user', 1, 'Нужны баннеры', 'text', 0, 0, NULL, '2025-06-14 16:51:08', 1),
(29, 1, 'user', 1, '📎 Файл: photo_2025-06-14_18-13-58.jpg', 'file', 0, 0, NULL, '2025-06-14 16:52:00', 1),
(30, 1, 'user', 1, '📎 Файл: photo_2025-06-13_15-06-12.jpg', 'file', 0, 0, NULL, '2025-06-14 17:03:38', 1),
(31, 1, 'admin', 1, 'принято', 'text', 0, 1, NULL, '2025-06-14 17:29:05', 0),
(32, 1, 'user', 1, 'Хочу узнать статус заказа #10', 'text', 0, 0, NULL, '2025-06-15 12:24:45', 1),
(33, 1, 'user', 1, 'Хочу узнать статус заказа #10', 'text', 0, 0, NULL, '2025-06-15 12:28:25', 1),
(34, 2, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-16 19:15:13', 1),
(35, 2, 'user', 6, 'Прайс-лист', 'text', 0, 0, NULL, '2025-06-16 19:15:16', 1),
(36, 2, 'admin', 1, 'Скажите какая Вас конкретно интересует услуга?', 'text', 0, 0, NULL, '2025-06-16 19:47:22', 0),
(37, 3, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-18 13:39:00', 1),
(38, 3, 'user', 5, 'Хочу узнать статус заказа #9', 'text', 0, 0, NULL, '2025-06-18 13:39:10', 1),
(39, 3, 'user', 5, 'Хочу узнать статус заказа #9', 'text', 0, 0, NULL, '2025-06-19 14:05:47', 1),
(40, 3, 'user', 5, 'Интересуют флаеры', 'text', 0, 0, NULL, '2025-06-19 14:05:51', 1),
(41, 4, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-22 11:06:18', 1),
(42, 4, 'user', 7, 'Интересует услуга: Брошюры и каталоги', 'text', 0, 0, NULL, '2025-06-22 11:06:38', 1),
(43, 4, 'user', 7, 'Прайс-лист', 'text', 0, 0, NULL, '2025-06-22 11:06:43', 1),
(44, 5, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-23 12:00:14', 1),
(45, 5, 'user', 8, 'Делаете ли вы дипломы в жесткой обложке?', 'text', 0, 0, NULL, '2025-06-23 12:00:16', 1),
(46, 6, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-25 08:01:58', 1),
(47, 6, 'user', 9, 'Нужна 1 табличка А3 цветность 4+0 на плотном картоне', 'text', 0, 0, NULL, '2025-06-25 08:02:02', 1),
(48, 7, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-25 10:16:44', 1),
(49, 7, 'user', 10, 'Фото на паспорт', 'text', 0, 0, NULL, '2025-06-25 10:16:46', 1),
(50, 7, 'user', 10, 'Вы работаете сегодня?', 'text', 0, 0, NULL, '2025-06-25 10:17:33', 1),
(51, 8, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-27 02:28:19', 1),
(52, 8, 'user', 12, 'Фото', 'text', 0, 0, NULL, '2025-06-27 02:28:22', 1),
(53, 8, 'user', 12, 'Прайс-лист', 'text', 0, 0, NULL, '2025-06-27 02:28:41', 1),
(54, 9, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-06-27 16:49:27', 1),
(55, 9, 'user', 13, 'Интересует услуга: Флаеры и листовки', 'text', 0, 0, NULL, '2025-06-27 16:49:30', 1),
(56, 9, 'user', 13, 'Интересуют флаеры', 'text', 0, 0, NULL, '2025-06-27 16:49:33', 1),
(57, 9, 'user', 13, 'Прайс-лист', 'text', 0, 0, NULL, '2025-06-27 16:49:38', 1),
(58, 10, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-07-02 07:07:51', 1),
(59, 10, 'user', 16, 'Добрый день!', 'text', 0, 0, NULL, '2025-07-02 07:07:58', 1),
(60, 10, 'user', 16, 'Есть возможность сделать вот такие наклейки?', 'text', 0, 0, NULL, '2025-07-02 07:08:13', 1),
(61, 10, 'user', 16, '📎 Файл: TER_SGdet_Label_201_5_.idw.pdf', 'file', 0, 0, NULL, '2025-07-02 07:08:29', 1),
(62, 11, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-07-07 23:03:58', 1),
(63, 11, 'user', 19, 'Мне нужно напечатать конверты которые когда складываются то становятся размером с банковскую карту из пластика с печатью полноцветной', 'text', 0, 0, NULL, '2025-07-07 23:04:01', 1),
(64, 11, 'user', 19, 'Сколько печать минимальная? 1 лист пластика например стоит столько то', 'text', 0, 0, NULL, '2025-07-07 23:04:40', 1),
(65, 11, 'user', 19, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-07-07 23:06:09', 1),
(66, 11, 'user', 19, 'Интересует услуга: Дизайн полиграфии', 'text', 0, 0, NULL, '2025-07-07 23:06:27', 1),
(67, 11, 'user', 19, 'Интересуют флаеры', 'text', 0, 0, NULL, '2025-07-07 23:06:31', 1),
(68, 12, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-07-14 11:45:30', 1),
(69, 12, 'user', 20, 'Привет! Я хотела бы узнать сколько это стоит', 'text', 0, 0, NULL, '2025-07-14 11:45:33', 1),
(70, 12, 'user', 20, 'Прайс-лист', 'text', 0, 0, NULL, '2025-07-14 11:45:56', 1),
(71, 12, 'user', 20, 'Прайс-лист', 'text', 0, 0, NULL, '2025-07-14 11:46:14', 1),
(72, 13, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-07-15 14:09:21', 1),
(73, 13, 'user', 21, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-07-15 14:09:28', 1),
(74, 14, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-07-17 16:00:29', 1),
(75, 14, 'user', 22, 'Добрый вечер! Не дозвонился, поэтому пишу сюда. Нужен Общий журнал учета выполненных работ (строительство). Изготавливаете такое? Александр', 'text', 0, 0, NULL, '2025-07-17 16:00:34', 1),
(76, 14, 'user', 22, 'Нужна консультация', 'text', 0, 0, NULL, '2025-07-17 16:00:55', 1),
(77, 14, 'user', 22, 'Интересует услуга: Брошюры и каталоги', 'text', 0, 0, NULL, '2025-07-17 16:01:10', 1),
(78, 14, 'user', 22, 'Видимо вообще не работаете уже', 'text', 0, 0, NULL, '2025-07-17 16:12:18', 1),
(79, 15, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-07-18 07:21:33', 1),
(80, 15, 'user', 23, 'Нужна консультация', 'text', 0, 0, NULL, '2025-07-18 07:21:42', 1),
(81, 15, 'user', 23, 'Прайс-лист', 'text', 0, 0, NULL, '2025-07-18 07:22:30', 1),
(82, 16, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-07-29 08:43:18', 1),
(83, 16, 'user', 27, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-07-29 08:43:22', 1),
(84, 16, 'user', 27, 'Прайс-лист', 'text', 0, 0, NULL, '2025-07-29 08:43:28', 1),
(85, 16, 'user', 27, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-07-29 08:44:16', 1),
(86, 16, 'user', 27, 'Нужна консультация', 'text', 0, 0, NULL, '2025-07-29 08:44:35', 1),
(87, 16, 'user', 27, 'Добрый день, не подскажете, сколько денег потребуется за 500 листовок А5 двусторонняя цветная печать? Хотелось бы ознакомиться с ценой. И какой срок изготовления?', 'text', 0, 0, NULL, '2025-07-29 08:49:55', 1),
(88, 16, 'user', 27, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-07-29 11:44:40', 1),
(89, 16, 'user', 27, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-07-29 11:46:08', 1),
(90, 17, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-08-06 08:45:22', 1),
(91, 17, 'user', 30, 'Сколько будет стоить распечатать 1000 листовок формата А6', 'text', 0, 0, NULL, '2025-08-06 08:46:01', 1),
(92, 18, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-08-08 12:11:05', 1),
(93, 18, 'user', 31, 'как войти в типографию?', 'text', 0, 0, NULL, '2025-08-08 12:11:09', 1),
(94, 19, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-08-13 09:49:31', 1),
(95, 19, 'user', 33, 'Добрый день! Нам требуются карты из тонкого пластика, размеры 9х12 см, углы скруглены. Тираж 20 шт. Пример - карточки футбольного арбитра.\nКарточка должна быть фиолетового цвета, на одной из сторон должен быть белый логотип. Подскажите, могли бы вы помочь с изготовлением? Сколько по времени займет производство?', 'text', 0, 0, NULL, '2025-08-13 09:49:57', 1),
(96, 19, 'user', 33, 'Все исходники для создания макета предоставим', 'text', 0, 0, NULL, '2025-08-13 09:50:26', 1),
(97, 20, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-09-03 11:02:12', 1),
(98, 20, 'user', 36, 'Интересует услуга: Дизайн полиграфии', 'text', 0, 0, NULL, '2025-09-03 11:02:40', 1),
(99, 20, 'user', 36, 'Упс, нет) Я хотела посмотреть перечень ваших услуг. Сегодня у вас делала распечатку', 'text', 0, 0, NULL, '2025-09-03 11:03:00', 1),
(100, 20, 'user', 36, 'Интересует услуга: Брошюры и каталоги', 'text', 0, 0, NULL, '2025-09-03 11:03:06', 1),
(101, 21, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-09-08 08:18:48', 1),
(102, 21, 'user', 37, 'Добрый день. В яндекс картах не работает телефон.  89033968258 перезвоните. Александра', 'text', 0, 0, NULL, '2025-09-08 08:18:50', 1),
(103, 22, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-09-13 08:17:04', 1),
(104, 22, 'user', 38, 'Интересует услуга: Брошюры и каталоги', 'text', 0, 0, NULL, '2025-09-13 08:17:37', 1),
(107, 24, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-09-20 11:20:03', 1),
(108, 24, 'user', 41, 'Нужны баннеры', 'text', 0, 0, NULL, '2025-09-20 11:20:06', 1),
(154, 28, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-10-04 12:58:41', 1),
(155, 28, 'user', 50, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-10-04 12:58:43', 1),
(156, 28, 'admin', 1, 'ага', 'text', 0, 1, NULL, '2025-10-04 12:59:04', 0),
(157, 28, 'admin', 1, 'что еще', 'text', 0, 1, NULL, '2025-10-04 12:59:10', 0),
(158, 28, 'admin', 1, 'проверка', 'text', 0, 1, NULL, '2025-10-04 13:02:00', 0),
(159, 28, 'admin', 1, 'нажатия', 'text', 0, 1, NULL, '2025-10-04 13:02:05', 0),
(160, 28, 'admin', 1, 'на клавишу', 'text', 0, 1, NULL, '2025-10-04 13:02:12', 0),
(161, 28, 'admin', 1, '1', 'text', 0, 1, NULL, '2025-10-04 13:02:36', 0),
(162, 28, 'admin', 1, 'звук', 'text', 0, 1, NULL, '2025-10-04 13:06:55', 0),
(163, 28, 'user', 50, 'звука нет', 'text', 0, 0, NULL, '2025-10-04 13:07:29', 1),
(164, 28, 'admin', 1, 'херово', 'text', 0, 1, NULL, '2025-10-04 13:07:45', 0),
(165, 28, 'admin', 1, 'может на хер работает', 'text', 0, 1, NULL, '2025-10-04 13:08:02', 0),
(166, 28, 'admin', 1, 'хер', 'text', 0, 1, NULL, '2025-10-04 13:08:12', 0),
(167, 28, 'admin', 1, 'привет', 'text', 0, 1, NULL, '2025-10-04 13:08:23', 0),
(168, 28, 'user', 50, 'привет', 'text', 0, 0, NULL, '2025-10-04 13:08:30', 1),
(169, 28, 'admin', 1, 'прикинь', 'text', 0, 1, NULL, '2025-10-04 13:08:39', 0),
(170, 28, 'admin', 1, 'это чат который никто не смотрит', 'text', 0, 1, NULL, '2025-10-04 13:08:48', 0),
(171, 28, 'user', 50, 'Интересуют флаеры', 'text', 0, 0, NULL, '2025-10-04 13:09:20', 1),
(172, 28, 'admin', 1, 'с членом есть', 'text', 0, 1, NULL, '2025-10-04 13:09:29', 0),
(173, 28, 'user', 50, 'Нужна консультация', 'text', 0, 0, NULL, '2025-10-04 13:09:37', 1),
(174, 28, 'user', 50, 'Прайс-лист', 'text', 0, 0, NULL, '2025-10-04 13:09:39', 1),
(175, 28, 'user', 50, 'Нужны баннеры', 'text', 0, 0, NULL, '2025-10-04 13:09:41', 1),
(176, 28, 'user', 50, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-10-04 13:09:48', 1),
(177, 28, 'user', 50, 'Нужны баннеры', 'text', 0, 0, NULL, '2025-10-04 13:09:57', 1),
(178, 28, 'user', 50, 'Прайс-лист', 'text', 0, 0, NULL, '2025-10-04 13:10:16', 1),
(179, 28, 'admin', 1, 'ок', 'text', 0, 1, NULL, '2025-10-04 13:10:17', 0),
(180, 28, 'admin', 1, 'ок', 'text', 0, 1, NULL, '2025-10-04 13:10:23', 0),
(181, 28, 'user', 50, 'привет', 'text', 0, 0, NULL, '2025-10-04 13:10:36', 1),
(182, 28, 'admin', 1, 'р', 'text', 0, 1, NULL, '2025-10-04 13:10:42', 0),
(183, 28, 'admin', 1, 'р', 'text', 0, 1, NULL, '2025-10-04 13:10:55', 0),
(184, 28, 'admin', 1, 'п', 'text', 0, 1, NULL, '2025-10-04 13:11:17', 0),
(185, 28, 'admin', 1, '6', 'text', 0, 1, NULL, '2025-10-04 13:11:27', 0),
(186, 28, 'admin', 1, '9', 'text', 0, 1, NULL, '2025-10-04 13:11:32', 0),
(187, 28, 'user', 50, 'р', 'text', 0, 0, NULL, '2025-10-04 13:11:40', 1),
(188, 28, 'admin', 1, 'привет', 'text', 0, 1, NULL, '2025-10-04 17:47:13', 0),
(189, 28, 'admin', 1, 'ало', 'text', 0, 1, NULL, '2025-10-04 17:47:21', 0),
(190, 28, 'user', 50, 'нет', 'text', 0, 0, NULL, '2025-10-04 17:47:32', 1),
(191, 28, 'admin', 1, 'привет', 'text', 0, 1, NULL, '2025-10-04 17:47:39', 0),
(192, 28, 'admin', 1, 'хер', 'text', 0, 1, NULL, '2025-10-04 17:47:56', 0),
(193, 28, 'admin', 1, 'ало', 'text', 0, 1, NULL, '2025-10-04 17:48:17', 0),
(194, 28, 'admin', 1, 'ghbdtn', 'text', 0, 1, NULL, '2025-10-04 17:49:38', 0),
(195, 28, 'user', 50, 'Хочу заказать визитки', 'text', 0, 0, NULL, '2025-10-04 17:49:47', 1),
(196, 28, 'user', 50, 'Какие сроки изготовления?', 'text', 0, 0, NULL, '2025-10-04 17:49:51', 1),
(197, 28, 'admin', 1, '[th', 'text', 0, 1, NULL, '2025-10-04 17:49:55', 0),
(198, 28, 'user', 50, 'Визитки: \n\n•⁠  ⁠Логотип: https://disk.yandex.ru/d/3RKYN0ZV7sh7rw\n•⁠  ⁠Название агентства: MAISON DE MODÈLES  by ÉTAT\n•⁠  ⁠Краткий слоган: Откройте миру талант вашего ребенка\n•⁠  ⁠Контактный телефон: +7 906 069-11-53\n•⁠  ⁠Веб-сайт: https://maisondemodeles.ru\n•⁠  ⁠Иконки соцсетей с адресами: Instagram @maisondemodeles_ Telegram @maison_de_models\n•⁠  ⁠QR-код: https://instagram.com/maisondemodeles', 'text', 0, 0, NULL, '2025-10-07 08:14:23', 1),
(199, 28, 'admin', 1, 'Очень смешно)', 'text', 0, 1, NULL, '2025-10-07 08:15:09', 0),
(200, 28, 'admin', 1, 'Я тут обучаю вообщето)', 'text', 0, 1, NULL, '2025-10-07 08:15:15', 0),
(201, 28, 'admin', 1, 'ахаха', 'text', 0, 1, NULL, '2025-10-07 08:15:17', 0),
(202, 28, 'admin', 1, 'мы от вас оплату не примем', 'text', 0, 1, NULL, '2025-10-07 08:15:21', 0),
(203, 28, 'user', 50, 'ну просто я закрывала вкладки а тут написано: \nПоддержка Типо-графия\nОнлайн • Отвечаем быстро', 'text', 0, 0, NULL, '2025-10-07 08:15:59', 1),
(204, 28, 'admin', 1, 'ахаха', 'text', 0, 1, NULL, '2025-10-07 08:16:24', 0),
(205, 28, 'admin', 1, 'мы быстро отвечаем вообще то', 'text', 0, 1, NULL, '2025-10-07 08:16:30', 0),
(206, 28, 'user', 50, 'да - мне понравилось - супер!', 'text', 0, 0, NULL, '2025-10-07 08:16:52', 1),
(207, 28, 'user', 50, 'поставлю 5 звезд вам', 'text', 0, 0, NULL, '2025-10-07 08:16:59', 1),
(208, 28, 'user', 50, 'все закрываю ваш чат - хорошего рабочего дня!', 'text', 0, 0, NULL, '2025-10-07 08:17:26', 1),
(209, 29, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-10-16 11:51:06', 1),
(210, 29, 'user', 56, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-10-16 11:51:09', 1),
(211, 29, 'admin', 1, 'Добрый день', 'text', 0, 1, NULL, '2025-10-16 11:52:41', 0),
(212, 29, 'admin', 1, 'Напишите пожалуйста ТЗ для визиток', 'text', 0, 1, NULL, '2025-10-16 11:52:53', 0),
(213, 29, 'user', 56, 'Добрый день, интересует изготовление визиток с покрытием софт тач в количестве 50 либо 100 шт.\nВизитки цветные двусторонние.', 'text', 0, 0, NULL, '2025-10-16 11:54:07', 1),
(214, 29, 'user', 56, 'Дизайн готов.', 'text', 0, 0, NULL, '2025-10-16 11:54:20', 1),
(215, 29, 'admin', 1, 'По срокам будут готовы завтра, у Вас самовывоз или доставка?', 'text', 0, 1, NULL, '2025-10-16 11:54:37', 0),
(216, 29, 'user', 56, 'самовывоз', 'text', 0, 0, NULL, '2025-10-16 11:55:17', 1),
(217, 29, 'user', 56, 'подскажите сколько выйдет по цене и можно ли закать два образца для оценки качества?', 'text', 0, 0, NULL, '2025-10-16 11:55:47', 1),
(218, 29, 'admin', 1, 'к сожалению мы не можем пустить в печать одну или две штуки, только партию\r\n по цене минуту рассчитываем', 'text', 0, 1, NULL, '2025-10-16 11:56:32', 0),
(219, 29, 'admin', 1, '4000 рублей 100 шт.', 'text', 0, 1, NULL, '2025-10-16 11:59:06', 0),
(220, 29, 'admin', 1, 'можно 50шт', 'text', 0, 1, NULL, '2025-10-16 11:59:31', 0),
(221, 29, 'admin', 1, 'тогда это 2800 будет', 'text', 0, 1, NULL, '2025-10-16 11:59:48', 0),
(222, 29, 'admin', 1, 'Оставьте пожалуйста Ваш номер телефона для оформления заказа', 'text', 0, 1, NULL, '2025-10-16 12:00:03', 0),
(223, 29, 'admin', 1, 'Так же если у вас есть промокод можете его применить сейчас', 'text', 0, 1, NULL, '2025-10-16 12:00:17', 0),
(224, 29, 'user', 56, 'благодарю за информацию, свяжусь с вами чуть позже', 'text', 0, 0, NULL, '2025-10-16 12:03:21', 1),
(225, 29, 'admin', 1, 'Спасибо, будем ждать\r\nЕсли сегодня закажете сделаем скидку 20%', 'text', 0, 0, NULL, '2025-10-16 12:04:32', 0),
(226, 30, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-10-21 06:14:31', 1),
(227, 30, 'user', 57, 'здравствуйте, мне нужен буклет путеводителя', 'text', 0, 0, NULL, '2025-10-21 06:16:16', 1),
(228, 30, 'admin', 1, 'Добрый день.', 'text', 0, 0, NULL, '2025-10-21 06:32:03', 0),
(229, 30, 'admin', 1, 'Напишите пожалуйста Ваш номер телефона вотсап для уточнения деталей', 'text', 0, 0, NULL, '2025-10-21 06:32:20', 0),
(230, 31, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-11-07 12:44:55', 1),
(231, 31, 'user', 61, 'Добрый день! Сколько стоит напечатать флаеры с куар кодом для отзывов? Тариф - 100 шт', 'text', 0, 0, NULL, '2025-11-07 12:45:58', 1),
(232, 31, 'admin', 1, 'Добрый день', 'text', 0, 1, NULL, '2025-11-07 12:46:40', 0),
(233, 31, 'admin', 1, 'Какой размер? Бумага листовочная?', 'text', 0, 1, NULL, '2025-11-07 12:46:57', 0),
(234, 31, 'user', 61, 'Честно говоря не знаю, какой посоветуете? я хотела прислать вам дизайн флаера, но архив zip не подгружается сюда', 'text', 0, 0, NULL, '2025-11-07 12:49:53', 1),
(235, 31, 'admin', 1, 'напишите ваш номер телефона, сейчас напишем в вотсап', 'text', 0, 1, NULL, '2025-11-07 12:50:19', 0),
(236, 31, 'user', 61, '+7 965 004-86-96', 'text', 0, 0, NULL, '2025-11-07 12:56:50', 1),
(237, 32, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-11-07 20:35:24', 1),
(238, 32, 'user', 62, 'Прайс-лист', 'text', 0, 0, NULL, '2025-11-07 20:35:29', 1),
(239, 32, 'user', 62, 'Прайс-лист', 'text', 0, 0, NULL, '2025-11-07 20:35:35', 1),
(240, 32, 'admin', 1, 'Добрый вечер', 'text', 0, 0, NULL, '2025-11-07 20:44:37', 0),
(241, 32, 'admin', 1, 'К сожалению мы сейчас отдыхаем', 'text', 0, 0, NULL, '2025-11-07 20:44:43', 0),
(242, 32, 'admin', 1, '89853152005', 'text', 0, 0, NULL, '2025-11-07 20:44:49', 0),
(243, 32, 'admin', 1, 'Это наш номер к которому привязан вотсап, напишите и мы вам ответим', 'text', 0, 0, NULL, '2025-11-07 20:45:04', 0),
(244, 33, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-11-11 19:54:18', 1),
(245, 33, 'user', 67, 'Прайс-лист', 'text', 0, 0, NULL, '2025-11-11 19:54:23', 1),
(246, 33, 'user', 67, 'цена фотографии а4', 'text', 0, 0, NULL, '2025-11-11 19:58:28', 1),
(247, 33, 'admin', 1, 'Добрый вечер', 'text', 0, 1, NULL, '2025-11-11 20:00:50', 0),
(248, 33, 'admin', 1, 'Извиняемся за долгий ответ', 'text', 0, 1, NULL, '2025-11-11 20:01:00', 0),
(249, 33, 'admin', 1, '80 рублей штука', 'text', 0, 1, NULL, '2025-11-11 20:01:23', 0),
(250, 33, 'admin', 1, 'при большем объеме цена меньше', 'text', 0, 1, NULL, '2025-11-11 20:01:37', 0),
(251, 34, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-11-19 16:32:05', 0),
(252, 34, 'user', 68, 'Прайс-лист', 'text', 0, 0, NULL, '2025-11-19 16:32:37', 0),
(253, 34, 'user', 68, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-11-19 16:36:03', 0),
(254, 35, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-11-26 11:27:08', 0),
(255, 35, 'user', 71, 'Прайс-лист', 'text', 0, 0, NULL, '2025-11-26 11:27:19', 0),
(256, 35, 'user', 71, 'Распечатать  примерно 10 листов', 'text', 0, 0, NULL, '2025-11-26 11:27:41', 0),
(257, 35, 'user', 71, 'Интересует услуга: Визитки', 'text', 0, 0, NULL, '2025-11-26 11:35:24', 0),
(258, 36, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-11-26 20:09:34', 1),
(259, 36, 'user', 72, 'Прайс-лист', 'text', 0, 0, NULL, '2025-11-26 20:09:38', 1),
(260, 36, 'admin', 1, 'Добрый вечер', 'text', 0, 0, NULL, '2025-11-26 20:10:29', 0),
(261, 36, 'admin', 1, 'Какая именно услуга вас интересует?', 'text', 0, 0, NULL, '2025-11-26 20:10:48', 0),
(262, 37, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-11-28 11:26:50', 1),
(263, 37, 'user', 73, 'Здраствуйте вы можете делать такие удостоверения?', 'text', 0, 0, NULL, '2025-11-28 11:27:27', 1),
(264, 37, 'user', 73, '📎 Файл: IMG_8990.jpeg', 'file', 0, 0, NULL, '2025-11-28 11:28:18', 1),
(265, 37, 'user', 73, '📎 Файл: IMG_8991.jpeg', 'file', 0, 0, NULL, '2025-11-28 11:28:20', 1),
(266, 37, 'user', 73, '?', 'text', 0, 0, NULL, '2025-11-28 11:28:38', 1),
(267, 38, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2025-12-19 11:33:02', 1),
(268, 38, 'user', 79, 'Добрый день. Подскажите вы можете изготавливать на своем производстве канцелярский картон/бумагу, тетради на скобе или блокноты на скобе/пружине/в переплете 7БЦ? Тиражи интересуют от 20 тысяч экземпляров и выше. В среднем 20-200 тысяч.', 'text', 0, 0, NULL, '2025-12-19 11:33:19', 1),
(269, 38, 'admin', 1, 'Добрый день', 'text', 0, 1, NULL, '2025-12-19 11:35:10', 0),
(270, 38, 'admin', 1, 'Как с Вами можно связаться в телеграм?', 'text', 0, 1, NULL, '2025-12-19 11:35:22', 0),
(271, 38, 'user', 79, '89381455802', 'text', 0, 0, NULL, '2025-12-19 11:35:46', 1),
(272, 39, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2026-01-07 08:33:04', 1),
(273, 39, 'user', 87, 'Сколько стоить разработать листовку', 'text', 0, 0, NULL, '2026-01-07 08:33:26', 1),
(274, 39, 'user', 87, 'Интересует услуга: Флаеры и листовки', 'text', 0, 0, NULL, '2026-01-07 08:34:20', 1),
(275, 39, 'admin', 1, 'Добрый день', 'text', 0, 1, NULL, '2026-01-07 08:34:25', 0),
(276, 39, 'admin', 1, 'Напишите пожалуйста Ваш номер телефона', 'text', 0, 1, NULL, '2026-01-07 08:34:41', 0),
(277, 39, 'admin', 1, 'Сразу свяжемся с вами, чтобы расчитать', 'text', 0, 1, NULL, '2026-01-07 08:35:02', 0),
(278, 39, 'user', 87, '79773429979 напишите пожалуйста в Максе или тг', 'text', 0, 0, NULL, '2026-01-07 08:36:12', 0),
(279, 39, 'admin', 1, 'Хорошо', 'text', 0, 0, NULL, '2026-01-07 08:36:27', 0),
(280, 39, 'user', 87, '79773429970', 'text', 0, 0, NULL, '2026-01-07 08:36:28', 0),
(281, 40, 'system', NULL, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.', 'system', 0, 1, NULL, '2026-01-08 16:29:20', 0),
(282, 40, 'user', 88, 'Прайс-лист', 'text', 0, 0, NULL, '2026-01-08 16:29:25', 0),
(283, 40, 'user', 88, 'Интересует услуга: Флаеры и листовки', 'text', 0, 0, NULL, '2026-01-08 16:29:46', 0),
(284, 40, 'user', 88, 'Здравствуйте! Подскажите, пожалуйста, нужно напечатать 50шт открыток на меловке А5, какая будет стоимость и срок изготовления?', 'text', 0, 0, NULL, '2026-01-08 16:33:04', 0);

--
-- Триггеры `messages`
--
DROP TRIGGER IF EXISTS `update_unread_counts_on_insert`;
DELIMITER $$
CREATE TRIGGER `update_unread_counts_on_insert` AFTER INSERT ON `messages` FOR EACH ROW BEGIN
    IF NEW.sender_type = 'user' THEN
        UPDATE chats 
        SET unread_admin_count = unread_admin_count + 1
        WHERE id = NEW.chat_id;
    ELSEIF NEW.sender_type IN ('admin', 'system') THEN
        UPDATE chats 
        SET unread_user_count = unread_user_count + 1
        WHERE id = NEW.chat_id;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_unread_counts_on_read`;
DELIMITER $$
CREATE TRIGGER `update_unread_counts_on_read` AFTER UPDATE ON `messages` FOR EACH ROW BEGIN
    -- Если админ прочитал сообщение от пользователя
    IF NEW.sender_type = 'user' AND OLD.is_read_admin = 0 AND NEW.is_read_admin = 1 THEN
        UPDATE chats 
        SET unread_admin_count = GREATEST(0, unread_admin_count - 1)
        WHERE id = NEW.chat_id;
    END IF;
    
    -- Если пользователь прочитал сообщение от админа
    IF NEW.sender_type IN ('admin', 'system') AND OLD.is_read_user = 0 AND NEW.is_read_user = 1 THEN
        UPDATE chats 
        SET unread_user_count = GREATEST(0, unread_user_count - 1)
        WHERE id = NEW.chat_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `message_attachments`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `message_attachments`;
CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `message_attachments`
--

INSERT INTO `message_attachments` (`id`, `message_id`, `file_name`, `file_path`, `file_size`, `file_type`, `created_at`) VALUES
(1, 14, 'photo_2025-06-13_15-06-12.jpg', '/uploads/chat_files/2025/06/684d366b73dd0_1749890667.jpg', 187097, 'image/jpeg', '2025-06-14 08:44:27'),
(2, 29, 'photo_2025-06-14_18-13-58.jpg', 'https://typo-grafia.ru/uploads/messages/684da8b0a6d52_1749919920.jpg', 51442, 'image/jpeg', '2025-06-14 16:52:00'),
(3, 30, 'photo_2025-06-13_15-06-12.jpg', 'https://typo-grafia.ru/uploads/messages/684dab6adac79_1749920618.jpg', 187097, 'image/jpeg', '2025-06-14 17:03:39'),
(4, 61, 'TER_SGdet_Label_201_5_.idw.pdf', 'https://typo-grafia.ru/uploads/messages/6864daed5929a_1751440109.pdf', 46330, 'application/pdf', '2025-07-02 07:08:29'),
(8, 161, '19844978.jpg', '/uploads/chat_files/2025/10/19844978_68e11aec35959_1759582956.jpg', 55648, 'image/jpeg', '2025-10-04 13:02:36'),
(9, 264, 'IMG_8990.jpeg', 'https://typo-grafia.ru/uploads/messages/6929875260d5f_1764329298.jpeg', 2126401, 'image/jpeg', '2025-11-28 11:28:18'),
(10, 265, 'IMG_8991.jpeg', 'https://typo-grafia.ru/uploads/messages/6929875441044_1764329300.jpeg', 1822628, 'image/jpeg', '2025-11-28 11:28:20');

-- --------------------------------------------------------

--
-- Структура таблицы `notifications`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'order_status, new_message, payment, promo',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci COMMENT 'дополнительные данные в формате JSON',
  `is_read` tinyint(1) DEFAULT '0',
  `sent_via` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'push, email, sms',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `sent_via`, `created_at`, `read_at`) VALUES
(1, 5, 'order_status', 'Статус заказа #2025-0001 изменен', 'Ваш заказ теперь в статусе: Подтвержден', '{\"order_id\":\"9\",\"status\":\"confirmed\"}', 0, NULL, '2025-06-15 12:21:55', NULL),
(2, 1, 'order_status', 'Статус заказа #2025-0002 изменен', 'Ваш заказ теперь в статусе: Подтвержден', '{\"order_id\":\"10\",\"status\":\"confirmed\"}', 0, NULL, '2025-06-15 12:29:01', NULL),
(3, 5, 'order_status', 'Статус заказа #2025-0001 изменен', 'Ваш заказ теперь в статусе: Отменен', '{\"order_id\":\"9\",\"status\":\"cancelled\"}', 0, NULL, '2025-06-21 08:57:41', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--
-- Создание: Июн 15 2025 г., 09:42
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `chat_id` int(11) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft, pending, confirmed, in_production, ready, delivered, cancelled',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `final_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tinkoff_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tinkoff_payment_url` text COLLATE utf8mb4_unicode_ci,
  `payment_status` enum('pending','paid','partially_paid','refunded','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status_manual` tinyint(1) DEFAULT '0',
  `delivery_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'pickup, delivery',
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `deadline_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'website' COMMENT 'website, phone, chat',
  `manager_id` int(11) DEFAULT NULL,
  `profit_amount` decimal(10,2) DEFAULT NULL,
  `manager_notes` text COLLATE utf8mb4_unicode_ci,
  `delivery_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `chat_id`, `status`, `total_amount`, `discount_amount`, `final_amount`, `tinkoff_payment_id`, `tinkoff_payment_url`, `payment_status`, `payment_status_manual`, `delivery_method`, `delivery_address`, `notes`, `admin_notes`, `deadline_at`, `created_at`, `updated_at`, `confirmed_at`, `completed_at`, `source`, `manager_id`, `profit_amount`, `manager_notes`, `delivery_type`) VALUES
(9, '2025-0001', 5, NULL, 'cancelled', '1800.00', '0.00', '1800.00', '6534336288', 'https://securepayments.tinkoff.ru/cNNwHCnM', 'paid', 1, 'pickup', NULL, '', NULL, '2025-06-15 21:00:00', '2025-06-15 12:16:44', '2025-06-21 08:57:41', '2025-06-15 12:21:55', NULL, 'website', NULL, NULL, '[{\"id\":\"684ebb45881e0\",\"text\":\"у заказа есть дизайн он прикреплен в вотсапп\",\"author\":\"Главный администратор\",\"admin_id\":\"1\",\"created_at\":\"2025-06-15 15:23:33\"}]', NULL),
(10, '2025-0002', 29, NULL, 'draft', '1680.00', '0.00', '1680.00', '7364335629', 'https://pay.tbank.ru/KjQ4H1hC', 'pending', 0, 'pickup', NULL, '', NULL, '2025-11-11 21:00:00', '2025-11-12 09:37:44', '2025-11-12 09:37:44', NULL, NULL, 'website', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `order_files`
--
-- Создание: Июн 13 2025 г., 06:16
--

DROP TABLE IF EXISTS `order_files`;
CREATE TABLE `order_files` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `uploaded_by_id` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `parameters` text COLLATE utf8mb4_unicode_ci COMMENT 'выбранные параметры услуги в формате JSON',
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `design_file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `design_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'pending, approved, revision_needed',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `service_id`, `quantity`, `parameters`, `unit_price`, `total_price`, `design_file_path`, `design_status`, `notes`, `created_at`, `updated_at`) VALUES
(6, 9, 2, 200, '[]', '9.00', '1800.00', NULL, 'pending', '', '2025-06-15 12:16:44', '2025-06-15 12:16:44'),
(7, 10, 5, 4, '[]', '420.00', '1680.00', NULL, 'pending', '', '2025-11-12 09:37:44', '2025-11-12 09:37:44');

-- --------------------------------------------------------

--
-- Структура таблицы `order_status_history`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `order_status_history`;
CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'user, admin, system',
  `changed_by_id` int(11) DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `old_status`, `new_status`, `changed_by_type`, `changed_by_id`, `comment`, `created_at`) VALUES
(6, 9, NULL, 'draft', 'system', NULL, 'Заказ создан', '2025-06-15 12:16:44'),
(7, 9, 'draft', 'confirmed', 'admin', 1, NULL, '2025-06-15 12:21:55'),
(8, 9, 'confirmed', 'cancelled', 'admin', 1, NULL, '2025-06-21 08:57:41'),
(9, 10, NULL, 'draft', 'system', NULL, 'Заказ создан', '2025-11-12 09:37:44');

-- --------------------------------------------------------

--
-- Структура таблицы `promocodes`
--
-- Создание: Июн 13 2025 г., 15:58
--

DROP TABLE IF EXISTS `promocodes`;
CREATE TABLE `promocodes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT '0.00',
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) DEFAULT '0',
  `user_limit` int(11) DEFAULT '1' COMMENT 'Сколько раз один пользователь может использовать',
  `valid_from` timestamp NULL DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `promocode_usage`
--
-- Создание: Июн 13 2025 г., 15:58
--

DROP TABLE IF EXISTS `promocode_usage`;
CREATE TABLE `promocode_usage` (
  `id` int(11) NOT NULL,
  `promocode_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `promo_codes`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `promo_codes`;
CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'percent, fixed',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT '0',
  `valid_from` timestamp NULL DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--
-- Создание: Июн 13 2025 г., 16:07
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `admin_response` text COLLATE utf8mb4_unicode_ci,
  `is_published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `replied_by` int(11) DEFAULT NULL COMMENT 'ID администратора, который ответил',
  `admin_reply` text COLLATE utf8mb4_unicode_ci,
  `replied_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'печать, дизайн, постпечать',
  `base_price` decimal(10,2) NOT NULL,
  `min_quantity` int(11) DEFAULT '1',
  `production_time_days` int(11) DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `category`, `base_price`, `min_quantity`, `production_time_days`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Визитки', 'Качественная печать визиток на различных видах бумаги. Матовая и глянцевая ламинация, тиснение фольгой.', 'печать', '500.00', 50, 2, 1, 1, '2025-06-14 08:42:37', '2025-06-14 08:42:37'),
(2, 'Флаеры и листовки', 'Яркие флаеры для рекламных кампаний. Различные форматы и тиражи. Быстрое изготовление.', 'печать', '500.00', 100, 1, 1, 2, '2025-06-14 08:42:37', '2025-06-14 18:14:56'),
(3, 'Баннеры', 'Широкоформатная печать баннеров для наружной и внутренней рекламы. Материалы: баннерная ткань, сетка, пленка.', 'печать', '250.00', 1, 3, 1, 3, '2025-06-14 08:42:37', '2025-06-14 08:42:37'),
(4, 'Дизайн полиграфии', 'Профессиональная разработка дизайна визиток, флаеров, брошюр, логотипов и фирменного стиля.', 'дизайн', '500.00', 1, 5, 1, 4, '2025-06-14 08:42:37', '2025-06-14 18:15:06'),
(5, 'Брошюры и каталоги', 'Печать брошюр, каталогов, журналов. Различные виды переплета: скрепка, пружина, клеевое соединение.', 'печать', '15.00', 10, 4, 1, 5, '2025-06-14 08:42:37', '2025-06-14 08:42:37');

-- --------------------------------------------------------

--
-- Структура таблицы `service_parameters`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `service_parameters`;
CREATE TABLE `service_parameters` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `parameter_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'size, material, color, finishing',
  `parameter_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameter_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_modifier` decimal(10,2) DEFAULT '0.00' COMMENT 'добавка к цене',
  `price_multiplier` decimal(5,3) DEFAULT '1.000' COMMENT 'множитель цены',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `service_price_rules`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `service_price_rules`;
CREATE TABLE `service_price_rules` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `rule_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'volume_discount, urgency_surcharge',
  `min_quantity` int(11) DEFAULT NULL,
  `max_quantity` int(11) DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `fixed_discount` decimal(10,2) DEFAULT NULL,
  `surcharge_percent` decimal(5,2) DEFAULT NULL,
  `conditions` text COLLATE utf8mb4_unicode_ci COMMENT 'дополнительные условия в формате JSON',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--
-- Создание: Июн 13 2025 г., 15:58
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('string','int','float','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Типо-графия', 'string', 'general', 'Название сайта', 1, '2025-06-13 15:58:45', '2025-06-13 20:04:33'),
(2, 'site_url', 'https://typo-grafia.ru', 'string', 'general', 'URL сайта', 1, '2025-06-13 15:58:45', '2025-06-13 20:04:33'),
(3, 'admin_email', 'info@typo-grafia.ru', 'string', 'general', 'Email администратора', 1, '2025-06-13 15:58:45', '2025-06-13 20:04:33'),
(4, 'orders_per_page', '20', 'int', 'display', 'Количество заказов на странице', 1, '2025-06-13 15:58:45', '2025-06-13 16:20:01'),
(5, 'enable_sms_notifications', '1', 'boolean', 'notifications', 'Включить SMS уведомления', NULL, '2025-06-13 15:58:45', NULL),
(6, 'enable_telegram_notifications', '1', 'boolean', 'notifications', 'Включить Telegram уведомления', NULL, '2025-06-13 15:58:45', NULL),
(7, 'min_order_amount', '0', 'float', 'orders', 'Минимальная сумма заказа', 1, '2025-06-13 15:58:45', '2025-06-13 16:20:01'),
(8, 'default_delivery_days', '3', 'int', 'orders', 'Срок доставки по умолчанию (дней)', 1, '2025-06-13 15:58:45', '2025-06-13 16:20:01'),
(9, 'maintenance_mode', '0', 'boolean', 'system', 'Режим обслуживания', NULL, '2025-06-13 15:58:45', NULL),
(10, 'backup_enabled', '1', 'boolean', 'system', 'Автоматическое резервное копирование', NULL, '2025-06-13 15:58:45', NULL),
(21, 'timezone', 'Europe/Moscow', 'string', 'general', NULL, 1, '2025-06-13 16:19:16', '2025-06-13 20:04:33'),
(22, 'language', 'ru', 'string', 'general', NULL, 1, '2025-06-13 16:19:16', '2025-06-13 20:04:33'),
(23, 'allow_guest_orders', '0', 'string', 'general', NULL, 1, '2025-06-13 16:20:01', NULL),
(24, 'auto_confirm_orders', '0', 'string', 'general', NULL, 1, '2025-06-13 16:20:01', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `sms_codes`
--
-- Создание: Июн 13 2025 г., 06:16
--

DROP TABLE IF EXISTS `sms_codes`;
CREATE TABLE `sms_codes` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `attempts` int(11) DEFAULT '0',
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `sms_codes`
--

INSERT INTO `sms_codes` (`id`, `phone`, `code`, `is_used`, `attempts`, `expires_at`, `created_at`, `ip_address`, `user_agent`) VALUES
(1, '+79312024000', '461523', 1, 0, '2025-06-13 07:31:13', '2025-06-13 07:31:01', NULL, NULL),
(2, '+79312024000', '842419', 1, 0, '2025-06-13 07:51:46', '2025-06-13 07:51:41', NULL, NULL),
(3, '+79312024000', '848257', 1, 0, '2025-06-13 08:00:13', '2025-06-13 08:00:09', NULL, NULL),
(4, '+79312024000', '290233', 1, 0, '2025-06-13 09:10:00', '2025-06-13 09:09:54', NULL, NULL),
(5, '+79312024000', '941170', 1, 0, '2025-06-13 11:19:08', '2025-06-13 11:19:01', NULL, NULL),
(6, '+79312024000', '669621', 1, 0, '2025-06-13 15:14:58', '2025-06-13 15:14:53', NULL, NULL),
(7, '+79312024000', '854686', 1, 0, '2025-06-13 20:04:01', '2025-06-13 20:03:36', NULL, NULL),
(8, '+79312024000', '563470', 1, 1, '2025-06-14 07:52:25', '2025-06-14 07:51:59', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36'),
(9, '+79312024000', '350196', 1, 0, '2025-06-14 08:12:10', '2025-06-14 08:12:04', NULL, NULL),
(10, '+79312024000', '880789', 1, 0, '2025-06-14 08:12:54', '2025-06-14 08:12:50', NULL, NULL),
(11, '+79312024000', '256633', 1, 1, '2025-06-14 14:41:55', '2025-06-14 14:41:45', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36'),
(12, '+79312024000', '247386', 1, 0, '2025-06-14 14:42:29', '2025-06-14 14:42:25', NULL, NULL),
(13, '+79312024000', '744873', 1, 1, '2025-06-14 16:13:19', '2025-06-14 16:13:09', '45.12.223.210', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(14, '+79312024000', '702657', 1, 0, '2025-06-14 16:14:24', '2025-06-14 16:14:20', NULL, NULL),
(15, '+79312024000', '571337', 1, 0, '2025-06-14 20:53:02', '2025-06-14 20:52:57', NULL, NULL),
(16, '+79312024000', '804566', 1, 0, '2025-06-15 07:41:02', '2025-06-15 07:40:57', NULL, NULL),
(17, '+79312024000', '688016', 1, 0, '2025-06-15 12:14:56', '2025-06-15 12:14:52', NULL, NULL),
(18, '+79312024000', '238656', 1, 1, '2025-06-15 12:24:39', '2025-06-15 12:24:31', '45.12.223.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 YaBrowser/25.4.0.0 Safari/537.36'),
(19, '+79312024000', '832073', 1, 0, '2025-06-15 16:21:24', '2025-06-15 16:21:17', NULL, NULL),
(20, '+79312024000', '874653', 1, 0, '2025-06-16 08:12:02', '2025-06-16 08:11:57', NULL, NULL),
(21, '+79159158623', '963771', 1, 1, '2025-06-16 19:15:13', '2025-06-16 19:15:08', '188.170.78.80', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(22, '+79312024000', '875873', 1, 0, '2025-06-16 19:46:57', '2025-06-16 19:46:53', NULL, NULL),
(23, '+79312024000', '463649', 1, 0, '2025-06-17 05:03:03', '2025-06-17 05:02:58', NULL, NULL),
(24, '+79654333395', '829364', 1, 1, '2025-06-18 13:39:00', '2025-06-18 13:38:56', '91.193.179.148', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(25, '+79654333395', '260491', 1, 1, '2025-06-19 14:05:34', '2025-06-19 14:05:31', '91.193.179.148', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(26, '+79312024000', '763784', 1, 0, '2025-06-21 08:42:12', '2025-06-21 08:42:08', NULL, NULL),
(27, '+79152237699', '939559', 1, 1, '2025-06-22 11:06:18', '2025-06-22 11:05:38', '213.87.156.180', 'Mozilla/5.0 (Linux; arm_64; Android 14; V2202) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 YaBrowser/21.9.0.370.00 SA/3 Mobile Safari/537.36'),
(28, '+79859912415', '735104', 1, 1, '2025-06-23 12:00:14', '2025-06-23 12:00:08', '185.195.71.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(29, '+79969292959', '819809', 1, 1, '2025-06-25 08:01:58', '2025-06-25 08:01:29', '195.9.194.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(30, '+79888550103', '630076', 1, 1, '2025-06-25 10:16:43', '2025-06-25 10:16:40', '213.87.160.183', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(31, '+79266289921', '913203', 0, 0, '2025-06-26 12:05:26', '2025-06-26 12:00:26', '128.204.79.16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 YaBrowser/25.6.0.1838.10 SA/3 Mobile/15E148 Safari/604.1'),
(32, '+79266289921', '728674', 0, 0, '2025-06-26 12:05:50', '2025-06-26 12:00:50', '128.204.79.16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 YaBrowser/25.6.0.1838.10 SA/3 Mobile/15E148 Safari/604.1'),
(33, '+79266289921', '112346', 0, 0, '2025-06-26 12:06:18', '2025-06-26 12:01:18', '128.204.79.16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 YaBrowser/25.6.0.1838.10 SA/3 Mobile/15E148 Safari/604.1'),
(34, '+79859069036', '061824', 1, 1, '2025-06-27 02:28:19', '2025-06-27 02:28:01', '46.138.236.33', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36'),
(35, '+79610795777', '641619', 1, 1, '2025-06-27 16:49:27', '2025-06-27 16:49:12', '95.25.32.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(36, '+79066947208', '861441', 0, 0, '2025-06-28 18:32:01', '2025-06-28 18:27:01', '94.25.173.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(37, '+79035721339', '799104', 0, 0, '2025-06-29 14:34:57', '2025-06-29 14:29:57', '89.113.154.105', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1 OPX/2.8.0'),
(38, '+79653886680', '023250', 1, 1, '2025-07-02 07:07:51', '2025-07-02 07:07:38', '212.33.28.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 YaBrowser/25.6.0.0 Safari/537.36'),
(39, '+79158059167', '251212', 0, 0, '2025-07-07 15:13:23', '2025-07-07 15:08:23', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(40, '+79158059167', '857521', 0, 0, '2025-07-07 15:13:58', '2025-07-07 15:08:58', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(41, '+79158059167', '000653', 0, 0, '2025-07-07 15:19:15', '2025-07-07 15:14:15', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(42, '+79101067669', '809893', 0, 0, '2025-07-07 15:28:36', '2025-07-07 15:23:36', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(43, '+79776624634', '908215', 1, 1, '2025-07-07 23:03:58', '2025-07-07 23:03:14', '109.252.130.85', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(44, '+79158059167', '325215', 0, 0, '2025-07-08 08:25:22', '2025-07-08 08:20:22', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(45, '+79800841901', '344518', 1, 1, '2025-07-14 11:45:30', '2025-07-14 11:44:50', '176.107.96.55', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.7.4.411.10 YaApp_iOS/2507.4 YaApp_iOS_Browser/2507.4 Safari/604.1 SA/3 SAPublic/0'),
(46, '+79778812196', '144877', 0, 0, '2025-07-15 14:11:59', '2025-07-15 14:06:59', '128.204.77.66', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.6.0.1819.10 YaApp_iOS/2506.0 YaApp_iOS_Browser/2506.0 Safari/604.1 SA/3'),
(47, '+79778812196', '847526', 0, 0, '2025-07-15 14:13:06', '2025-07-15 14:08:06', '128.204.77.66', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.6.0.1819.10 YaApp_iOS/2506.0 YaApp_iOS_Browser/2506.0 Safari/604.1 SA/3'),
(48, '+79778812196', '245797', 1, 1, '2025-07-15 14:09:21', '2025-07-15 14:08:48', '128.204.77.66', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 YaBrowser/25.6.0.1819.10 YaApp_iOS/2506.0 YaApp_iOS_Browser/2506.0 Safari/604.1 SA/3'),
(49, '+79254075290', '114511', 1, 1, '2025-07-17 16:00:29', '2025-07-17 16:00:19', '31.28.6.120', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0'),
(50, '+79776690274', '002120', 1, 1, '2025-07-18 07:21:33', '2025-07-18 07:21:26', '176.59.55.37', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(51, '+79295787561', '324865', 0, 0, '2025-07-23 14:13:59', '2025-07-23 14:08:59', '217.15.57.7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(52, '+79295787561', '627311', 0, 0, '2025-07-23 14:14:17', '2025-07-23 14:09:17', '217.15.57.7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(53, '+79295787561', '364263', 0, 0, '2025-07-23 14:15:48', '2025-07-23 14:10:48', '217.15.57.7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6 Safari/605.1.15'),
(54, '+79270622623', '358213', 0, 0, '2025-07-28 15:48:21', '2025-07-28 15:43:21', '194.53.54.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0'),
(55, '+79262102047', '316474', 0, 0, '2025-07-29 08:46:57', '2025-07-29 08:41:57', '87.251.72.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(56, '+79262102047', '895779', 0, 0, '2025-07-29 08:47:41', '2025-07-29 08:42:41', '87.251.72.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(57, '+79040553269', '645238', 1, 1, '2025-07-29 08:43:18', '2025-07-29 08:43:07', '87.251.72.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(58, '+79040553269', '139309', 1, 1, '2025-07-29 08:48:35', '2025-07-29 08:48:26', '178.238.122.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(59, '+79040553269', '808453', 1, 1, '2025-07-29 08:50:26', '2025-07-29 08:50:18', '178.238.122.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(60, '+79200173309', '558990', 0, 0, '2025-07-30 07:02:04', '2025-07-30 06:57:04', '128.204.79.242', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(61, '+79200173309', '416153', 0, 0, '2025-07-30 07:02:48', '2025-07-30 06:57:48', '128.204.79.242', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(62, '+79200173309', '092818', 0, 0, '2025-07-30 07:03:15', '2025-07-30 06:58:15', '128.204.79.242', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(63, '+79165189039', '124490', 0, 0, '2025-07-31 10:02:13', '2025-07-31 09:57:13', '213.87.161.4', 'Mozilla/5.0 (Linux; arm_64; Android 15; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.7103.52 YaBrowser/25.6.6.52.00 SA/3 Mobile Safari/537.36'),
(64, '+79165189039', '887217', 0, 0, '2025-07-31 10:03:03', '2025-07-31 09:58:03', '213.87.161.4', 'Mozilla/5.0 (Linux; arm_64; Android 15; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.7103.52 YaBrowser/25.6.6.52.00 SA/3 Mobile Safari/537.36'),
(65, '+79023573613', '441617', 1, 1, '2025-08-06 08:45:22', '2025-08-06 08:45:07', '87.76.12.135', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0'),
(66, '+79165220606', '145500', 1, 1, '2025-08-08 12:11:05', '2025-08-08 12:11:00', '212.233.87.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'),
(67, '+79154158878', '938310', 0, 0, '2025-08-10 08:32:32', '2025-08-10 08:27:32', '213.87.138.80', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(68, '+79154158878', '192022', 0, 0, '2025-08-10 08:32:48', '2025-08-10 08:27:48', '213.87.138.80', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1'),
(69, '+79282671071', '423277', 1, 1, '2025-08-13 09:49:31', '2025-08-13 09:49:06', '81.200.31.190', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(70, '+79119521948', '297009', 0, 0, '2025-08-18 18:15:39', '2025-08-18 18:10:39', '188.242.192.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36'),
(71, '+79119521948', '840525', 0, 0, '2025-08-18 18:16:49', '2025-08-18 18:11:49', '188.242.192.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36'),
(72, '+79998686131', '674611', 0, 0, '2025-08-19 14:03:42', '2025-08-19 13:58:42', '143.14.42.111', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(73, '+79998686131', '659498', 0, 0, '2025-08-19 14:04:00', '2025-08-19 13:59:00', '143.14.42.111', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(74, '+79017351785', '217364', 1, 1, '2025-09-03 11:02:12', '2025-09-03 11:02:03', '86.62.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36'),
(75, '+79033968258', '409841', 1, 1, '2025-09-08 08:18:48', '2025-09-08 08:18:33', '91.193.177.201', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36'),
(76, '+79992474431', '316222', 1, 1, '2025-09-13 08:17:04', '2025-09-13 08:16:59', '176.193.63.145', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(77, '+79620823837', '596773', 1, 1, '2025-09-15 14:30:42', '2025-09-15 14:30:38', '89.113.153.46', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Mobile/15E148 Safari/604.1'),
(78, '+79263651020', '864812', 0, 0, '2025-09-16 13:25:30', '2025-09-16 13:20:30', '128.204.79.69', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(79, '+79263651020', '781296', 0, 0, '2025-09-16 13:25:48', '2025-09-16 13:20:48', '128.204.79.69', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(80, '+79165189039', '568629', 0, 0, '2025-09-17 11:09:44', '2025-09-17 11:04:44', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 YaBrowser/25.6.0.0 Safari/537.36'),
(81, '+79165189039', '603025', 0, 0, '2025-09-17 11:10:01', '2025-09-17 11:05:01', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 YaBrowser/25.6.0.0 Safari/537.36'),
(82, '+79165189039', '428076', 0, 0, '2025-09-17 11:10:14', '2025-09-17 11:05:14', '194.85.135.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 YaBrowser/25.6.0.0 Safari/537.36'),
(83, '+79312024000', '880853', 0, 0, '2025-09-18 10:31:38', '2025-09-18 10:26:38', '185.77.216.27', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36'),
(84, '+79253802166', '202755', 1, 1, '2025-09-20 11:20:03', '2025-09-20 11:19:52', '86.110.218.131', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0 (Edition Yx 08)'),
(85, '+79539119058', '420561', 0, 0, '2025-09-25 13:49:22', '2025-09-25 13:44:22', '5.77.197.70', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(86, '+79671826840', '626564', 0, 0, '2025-10-02 09:36:03', '2025-10-02 09:31:03', '79.139.150.112', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(87, '+79671826840', '874100', 0, 0, '2025-10-02 09:36:26', '2025-10-02 09:31:26', '79.139.150.112', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(88, '+79671826840', '902170', 0, 0, '2025-10-02 09:36:51', '2025-10-02 09:31:51', '79.139.150.112', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
(89, '+79312024000', '366825', 1, 0, '2025-10-04 10:05:36', '2025-10-04 10:05:30', NULL, NULL),
(90, '+79312024000', '354425', 1, 0, '2025-10-04 11:20:51', '2025-10-04 11:20:40', NULL, NULL),
(91, '+79312024000', '391349', 1, 0, '2025-10-04 11:33:43', '2025-10-04 11:33:38', NULL, NULL),
(92, '+79312024000', '876261', 1, 0, '2025-10-04 12:50:32', '2025-10-04 12:50:28', NULL, NULL),
(93, '+79312024000', '508416', 1, 0, '2025-10-04 14:11:18', '2025-10-04 14:11:10', NULL, NULL),
(94, '+79312024000', '894706', 1, 0, '2025-10-04 17:35:07', '2025-10-04 17:35:03', NULL, NULL),
(95, '+79312024000', '211665', 1, 0, '2025-10-07 08:14:58', '2025-10-07 08:14:56', NULL, NULL),
(96, '+79312024000', '851270', 1, 0, '2025-10-13 07:41:27', '2025-10-13 07:41:22', NULL, NULL),
(97, '+79312024000', '901809', 1, 0, '2025-10-14 08:43:32', '2025-10-14 08:43:28', NULL, NULL),
(98, '+79312024000', '461255', 1, 0, '2025-10-16 11:52:31', '2025-10-16 11:52:28', NULL, NULL),
(99, '+79312024000', '243709', 1, 0, '2025-10-16 12:04:00', '2025-10-16 12:03:58', NULL, NULL),
(100, '+79312024000', '154159', 1, 0, '2025-10-21 06:31:47', '2025-10-21 06:31:37', NULL, NULL),
(101, '+79312024000', '186104', 1, 0, '2025-11-07 12:46:23', '2025-11-07 12:46:19', NULL, NULL),
(102, '+79312024000', '520684', 1, 0, '2025-11-07 20:44:25', '2025-11-07 20:44:15', NULL, NULL),
(103, '+79312024000', '412012', 1, 0, '2025-11-08 09:31:15', '2025-11-08 09:31:12', NULL, NULL),
(104, '+79312024000', '674888', 1, 0, '2025-11-11 20:00:38', '2025-11-11 20:00:33', NULL, NULL),
(105, '+79312024000', '765074', 1, 0, '2025-11-12 09:36:21', '2025-11-12 09:36:18', NULL, NULL),
(106, '+79312024000', '247532', 1, 0, '2025-11-26 20:10:17', '2025-11-26 20:10:11', NULL, NULL),
(107, '+79312024000', '662617', 1, 0, '2025-12-19 11:34:17', '2025-12-19 11:34:04', NULL, NULL),
(108, '+79312024000', '599209', 1, 0, '2025-12-19 12:54:12', '2025-12-19 12:54:08', NULL, NULL),
(109, '+79312024000', '793749', 1, 0, '2025-12-24 12:46:55', '2025-12-24 12:46:50', NULL, NULL),
(110, '+79312024000', '273840', 1, 0, '2025-12-28 12:08:25', '2025-12-28 12:08:21', NULL, NULL),
(111, '+79312024000', '223774', 1, 0, '2026-01-07 08:34:09', '2026-01-07 08:34:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `sms_history`
--
-- Создание: Июн 15 2025 г., 12:47
--

DROP TABLE IF EXISTS `sms_history`;
CREATE TABLE `sms_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `error_message` text,
  `sent_by` int(11) NOT NULL COMMENT 'ID администратора',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `sms_history`
--

INSERT INTO `sms_history` (`id`, `user_id`, `phone`, `message`, `status`, `error_message`, `sent_by`, `created_at`) VALUES
(1, 1, '+79312024000', 'Ваш заказ готов к выдаче.', 'sent', NULL, 1, '2025-06-15 12:51:43'),
(2, 6, '+79159158623', 'Вам ответили на сайте https://typo-grafia.ru', 'sent', NULL, 1, '2025-06-16 19:48:02');

-- --------------------------------------------------------

--
-- Структура таблицы `sms_log`
--
-- Создание: Июн 15 2025 г., 09:42
--

DROP TABLE IF EXISTS `sms_log`;
CREATE TABLE `sms_log` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `system_logs`
--
-- Создание: Июн 13 2025 г., 15:34
--

DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `level` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INFO',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` json DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tinkoff_notifications`
--
-- Создание: Июн 15 2025 г., 09:42
--

DROP TABLE IF EXISTS `tinkoff_notifications`;
CREATE TABLE `tinkoff_notifications` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_data` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `transactions`
--
-- Создание: Июн 12 2025 г., 12:41
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'RUB',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'card, bank_transfer, cash',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'pending, processing, success, failed, cancelled, refunded',
  `gateway_response` text COLLATE utf8mb4_unicode_ci COMMENT 'ответ от платежной системы в формате JSON',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--
-- Создание: Окт 04 2025 г., 08:48
-- Последнее обновление: Янв 08 2026 г., 16:32
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inn` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `is_blocked` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `phone`, `email`, `name`, `company_name`, `inn`, `is_verified`, `is_blocked`, `created_at`, `updated_at`, `last_login_at`) VALUES
(1, '+79312024000', '', 'Иван', '', NULL, 1, 0, '2025-06-14 07:51:59', '2025-06-15 12:24:39', '2025-06-15 12:24:39'),
(5, '+79654333395', NULL, 'Макка', NULL, NULL, 1, 0, '2025-06-15 08:04:17', '2025-06-19 14:05:34', '2025-06-19 14:05:34'),
(6, '+79159158623', NULL, NULL, NULL, NULL, 1, 0, '2025-06-16 19:15:08', '2025-06-16 19:15:13', '2025-06-16 19:15:13'),
(7, '+79152237699', NULL, NULL, NULL, NULL, 1, 0, '2025-06-22 11:05:38', '2025-06-22 11:06:18', '2025-06-22 11:06:18'),
(8, '+79859912415', NULL, NULL, NULL, NULL, 1, 0, '2025-06-23 12:00:08', '2025-06-23 12:00:14', '2025-06-23 12:00:14'),
(9, '+79969292959', NULL, NULL, NULL, NULL, 1, 0, '2025-06-25 08:01:29', '2025-06-25 08:01:58', '2025-06-25 08:01:58'),
(10, '+79888550103', NULL, NULL, NULL, NULL, 1, 0, '2025-06-25 10:16:40', '2025-06-25 10:16:43', '2025-06-25 10:16:43'),
(11, '+79266289921', NULL, NULL, NULL, NULL, 1, 0, '2025-06-26 12:00:26', '2025-06-26 12:00:26', NULL),
(12, '+79859069036', NULL, NULL, NULL, NULL, 1, 0, '2025-06-27 02:28:01', '2025-06-27 02:28:19', '2025-06-27 02:28:19'),
(13, '+79610795777', NULL, NULL, NULL, NULL, 1, 0, '2025-06-27 16:49:12', '2025-06-27 16:49:27', '2025-06-27 16:49:27'),
(14, '+79066947208', NULL, NULL, NULL, NULL, 1, 0, '2025-06-28 18:27:01', '2025-06-28 18:27:01', NULL),
(15, '+79035721339', NULL, NULL, NULL, NULL, 1, 0, '2025-06-29 14:29:57', '2025-06-29 14:29:57', NULL),
(16, '+79653886680', NULL, NULL, NULL, NULL, 1, 0, '2025-07-02 07:07:38', '2025-07-02 07:07:51', '2025-07-02 07:07:51'),
(17, '+79158059167', NULL, NULL, NULL, NULL, 1, 0, '2025-07-07 15:08:23', '2025-07-07 15:08:23', NULL),
(18, '+79101067669', NULL, NULL, NULL, NULL, 1, 0, '2025-07-07 15:23:36', '2025-07-07 15:23:36', NULL),
(19, '+79776624634', NULL, NULL, NULL, NULL, 1, 0, '2025-07-07 23:03:14', '2025-07-07 23:03:58', '2025-07-07 23:03:58'),
(20, '+79800841901', NULL, NULL, NULL, NULL, 1, 0, '2025-07-14 11:44:50', '2025-07-14 11:45:30', '2025-07-14 11:45:30'),
(21, '+79778812196', NULL, NULL, NULL, NULL, 1, 0, '2025-07-15 14:06:59', '2025-07-15 14:09:21', '2025-07-15 14:09:21'),
(22, '+79254075290', NULL, NULL, NULL, NULL, 1, 0, '2025-07-17 16:00:19', '2025-07-17 16:00:29', '2025-07-17 16:00:29'),
(23, '+79776690274', NULL, NULL, NULL, NULL, 1, 0, '2025-07-18 07:21:26', '2025-07-18 07:21:33', '2025-07-18 07:21:33'),
(24, '+79295787561', NULL, NULL, NULL, NULL, 1, 0, '2025-07-23 14:08:59', '2025-07-23 14:08:59', NULL),
(25, '+79270622623', NULL, NULL, NULL, NULL, 1, 0, '2025-07-28 15:43:21', '2025-07-28 15:43:21', NULL),
(26, '+79262102047', NULL, NULL, NULL, NULL, 1, 0, '2025-07-29 08:41:57', '2025-07-29 08:41:57', NULL),
(27, '+79040553269', NULL, NULL, NULL, NULL, 1, 0, '2025-07-29 08:43:07', '2025-07-29 08:50:26', '2025-07-29 08:50:26'),
(28, '+79200173309', NULL, NULL, NULL, NULL, 1, 0, '2025-07-30 06:57:04', '2025-07-30 06:57:04', NULL),
(29, '+79165189039', NULL, NULL, NULL, NULL, 1, 0, '2025-07-31 09:57:13', '2025-07-31 09:57:13', NULL),
(30, '+79023573613', NULL, NULL, NULL, NULL, 1, 0, '2025-08-06 08:45:07', '2025-08-06 08:45:22', '2025-08-06 08:45:22'),
(31, '+79165220606', NULL, NULL, NULL, NULL, 1, 0, '2025-08-08 12:11:00', '2025-08-08 12:11:05', '2025-08-08 12:11:05'),
(32, '+79154158878', NULL, NULL, NULL, NULL, 1, 0, '2025-08-10 08:27:32', '2025-08-10 08:27:32', NULL),
(33, '+79282671071', NULL, NULL, NULL, NULL, 1, 0, '2025-08-13 09:49:06', '2025-08-13 09:49:31', '2025-08-13 09:49:31'),
(34, '+79119521948', NULL, NULL, NULL, NULL, 1, 0, '2025-08-18 18:10:39', '2025-08-18 18:10:39', NULL),
(35, '+79998686131', NULL, NULL, NULL, NULL, 1, 0, '2025-08-19 13:58:42', '2025-08-19 13:58:42', NULL),
(36, '+79017351785', NULL, NULL, NULL, NULL, 1, 0, '2025-09-03 11:02:03', '2025-09-03 11:02:12', '2025-09-03 11:02:12'),
(37, '+79033968258', NULL, NULL, NULL, NULL, 1, 0, '2025-09-08 08:18:33', '2025-09-08 08:18:48', '2025-09-08 08:18:48'),
(38, '+79992474431', NULL, NULL, NULL, NULL, 1, 0, '2025-09-13 08:16:59', '2025-09-13 08:17:04', '2025-09-13 08:17:04'),
(39, '+79620823837', NULL, NULL, NULL, NULL, 1, 0, '2025-09-15 14:30:38', '2025-09-15 14:30:42', '2025-09-15 14:30:42'),
(40, '+79263651020', NULL, NULL, NULL, NULL, 1, 0, '2025-09-16 13:20:30', '2025-09-16 13:20:30', NULL),
(41, '+79253802166', NULL, NULL, NULL, NULL, 1, 0, '2025-09-20 11:19:52', '2025-09-20 11:20:03', '2025-09-20 11:20:03'),
(42, '+79539119058', NULL, NULL, NULL, NULL, 1, 0, '2025-09-25 13:44:22', '2025-09-25 13:44:22', NULL),
(43, '+79671826840', NULL, NULL, NULL, NULL, 1, 0, '2025-10-02 09:31:03', '2025-10-02 09:31:03', NULL),
(44, '', 'dan220897@gmail.com', NULL, NULL, NULL, 1, 0, '2025-10-04 08:43:14', '2025-10-04 08:43:14', NULL),
(48, NULL, 'etatagency@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-10-04 08:49:24', '2025-10-04 09:10:30', '2025-10-04 09:10:30'),
(49, NULL, 'typo-grafia@ya.ru', NULL, NULL, NULL, 1, 0, '2025-10-04 09:20:34', '2025-10-04 09:51:49', '2025-10-04 09:51:49'),
(50, NULL, 'diane_kilchurina@mail.ru', NULL, NULL, NULL, 1, 0, '2025-10-04 11:18:32', '2025-10-04 17:49:27', '2025-10-04 17:49:27'),
(51, NULL, 'andinomobile@gmail.com', NULL, NULL, NULL, 1, 0, '2025-10-06 11:05:16', '2025-10-06 11:05:16', NULL),
(52, NULL, 'eva13@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-10-14 10:19:45', '2025-10-14 10:19:45', NULL),
(53, NULL, 'polyatryikk@gmail.com', NULL, NULL, NULL, 1, 0, '2025-10-16 09:36:37', '2025-10-16 09:36:37', NULL),
(54, NULL, 'pola02139@gmail.com', NULL, NULL, NULL, 1, 0, '2025-10-16 09:37:25', '2025-10-16 09:37:25', NULL),
(55, NULL, 'pwld01032@gmail.com', NULL, NULL, NULL, 1, 0, '2025-10-16 09:38:04', '2025-10-16 09:38:04', NULL),
(56, NULL, 'v-valker@mail.ru', NULL, NULL, NULL, 1, 0, '2025-10-16 11:50:53', '2025-10-16 11:51:06', '2025-10-16 11:51:06'),
(57, NULL, 'MironovaEE3@mos.ru', NULL, NULL, NULL, 1, 0, '2025-10-21 06:13:58', '2025-10-21 06:14:31', '2025-10-21 06:14:31'),
(58, NULL, 'phoenixa1a@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-10-28 11:36:16', '2025-10-28 11:36:16', NULL),
(59, NULL, 'kinodrama@mail.ru', NULL, NULL, NULL, 1, 0, '2025-10-28 13:45:36', '2025-10-28 13:45:36', NULL),
(60, NULL, 'tereshkina@termst.ru', NULL, NULL, NULL, 1, 0, '2025-11-06 08:05:00', '2025-11-06 08:05:00', NULL),
(61, NULL, 'y.solovyeva@moscow.tran-express.ru', NULL, NULL, NULL, 1, 0, '2025-11-07 12:44:27', '2025-11-07 12:44:55', '2025-11-07 12:44:55'),
(62, NULL, 'nastya-online@mail.ru', NULL, NULL, NULL, 1, 0, '2025-11-07 20:34:40', '2025-11-07 20:35:24', '2025-11-07 20:35:24'),
(63, NULL, 'bet.xarmon@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-11-09 13:03:29', '2025-11-09 13:03:29', NULL),
(64, NULL, 'arinainozemtsevaa@gmail.com', NULL, NULL, NULL, 1, 0, '2025-11-09 13:05:19', '2025-11-09 13:05:19', NULL),
(65, NULL, 'ea@gp-agency.ru', NULL, NULL, NULL, 1, 0, '2025-11-11 13:14:23', '2025-11-11 13:14:23', NULL),
(66, NULL, 'e.alferova@12kosmonavtov.ru', NULL, NULL, NULL, 1, 0, '2025-11-11 13:16:09', '2025-11-11 13:16:09', NULL),
(67, NULL, 'danleyz@mail.ru', NULL, NULL, NULL, 1, 0, '2025-11-11 19:53:27', '2025-11-11 19:54:18', '2025-11-11 19:54:18'),
(68, NULL, 'ana.detenkova@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-11-18 16:00:55', '2025-11-19 16:32:05', '2025-11-19 16:32:05'),
(69, NULL, 'guryanovaanastasia03@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-11-21 08:37:02', '2025-11-21 08:37:02', NULL),
(70, NULL, 'dozamyatkin@gmail.com', NULL, NULL, NULL, 1, 0, '2025-11-23 21:41:34', '2025-11-23 21:41:34', NULL),
(71, NULL, 'jchugunova@mail.ru', NULL, NULL, NULL, 1, 0, '2025-11-26 11:26:47', '2025-11-26 11:27:08', '2025-11-26 11:27:08'),
(72, NULL, 'karih.nastasya@mail.ru', NULL, NULL, NULL, 1, 0, '2025-11-26 20:09:15', '2025-11-26 20:09:34', '2025-11-26 20:09:34'),
(73, NULL, 'kbk2022@mail.ru', NULL, NULL, NULL, 1, 0, '2025-11-28 11:26:22', '2025-11-28 11:26:50', '2025-11-28 11:26:50'),
(74, NULL, 'p.pitchugin@gmail.com', NULL, NULL, NULL, 1, 0, '2025-12-08 15:00:40', '2025-12-08 15:00:40', NULL),
(75, NULL, 'mkhirina26@ya.ru', NULL, NULL, NULL, 1, 0, '2025-12-10 14:12:50', '2025-12-10 14:12:50', NULL),
(76, NULL, 'shershan52@gmail.com', NULL, NULL, NULL, 1, 0, '2025-12-11 05:55:58', '2025-12-11 05:55:58', NULL),
(77, NULL, 'artyrhaims2@gmail.com', NULL, NULL, NULL, 1, 0, '2025-12-17 09:20:36', '2025-12-17 09:20:36', NULL),
(78, NULL, 'latika2010a@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-12-17 16:22:59', '2025-12-17 16:22:59', NULL),
(79, NULL, 'purchaserf2@phoenix-plus.ru', NULL, 'Феникс+', '6164092058', 1, 0, '2025-12-19 11:32:33', '2025-12-19 11:34:10', '2025-12-19 11:33:02'),
(80, NULL, 'tarn.ann@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-12-21 09:00:09', '2025-12-21 09:00:09', NULL),
(81, NULL, 'a.meteleva@gmail.com', NULL, NULL, NULL, 1, 0, '2025-12-23 12:56:31', '2025-12-23 12:56:31', NULL),
(82, NULL, 'valeriakusnarenko499@gmail.com', NULL, NULL, NULL, 1, 0, '2025-12-24 20:20:31', '2025-12-24 20:20:31', NULL),
(83, NULL, 'kushnarenkovaleriia@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-12-24 20:22:28', '2025-12-24 20:22:28', NULL),
(84, NULL, 'ads@isrp.ru', NULL, NULL, NULL, 1, 0, '2025-12-25 15:06:30', '2025-12-25 15:06:30', NULL),
(85, NULL, 'isrp0112@yandex.ru', NULL, NULL, NULL, 1, 0, '2025-12-25 15:08:47', '2025-12-25 15:08:47', NULL),
(86, NULL, 'lisandfox@gmail.com', NULL, NULL, NULL, 1, 0, '2025-12-29 23:01:27', '2025-12-29 23:01:27', NULL),
(87, NULL, 'natasha_kezich@mail.ru', NULL, NULL, NULL, 1, 0, '2026-01-07 08:33:01', '2026-01-07 08:33:04', '2026-01-07 08:33:04'),
(88, NULL, 'anastasia_novikova2015@mail.ru', NULL, NULL, NULL, 1, 0, '2026-01-08 16:28:59', '2026-01-08 16:32:26', '2026-01-08 16:32:26'),
(89, NULL, 'akadare832@gmail.com', NULL, NULL, NULL, 1, 0, '2026-01-08 16:29:13', '2026-01-08 16:29:13', NULL),
(90, NULL, 'akadare833@gmail.com', NULL, NULL, NULL, 1, 0, '2026-01-08 16:30:42', '2026-01-08 16:30:42', NULL);

-- --------------------------------------------------------

--
-- Структура для представления `dashboard_stats`
--
DROP TABLE IF EXISTS `dashboard_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`anikannx_printtg`@`localhost` SQL SECURITY DEFINER VIEW `dashboard_stats`  AS SELECT (select count(0) from `orders` where (cast(`orders`.`created_at` as date) = curdate())) AS `orders_today`, (select count(0) from `orders` where (yearweek(`orders`.`created_at`,0) = yearweek(curdate(),0))) AS `orders_week`, (select count(0) from `orders` where ((month(`orders`.`created_at`) = month(curdate())) and (year(`orders`.`created_at`) = year(curdate())))) AS `orders_month`, (select ifnull(sum(`orders`.`final_amount`),0) from `orders` where ((`orders`.`payment_status` = 'paid') and (cast(`orders`.`created_at` as date) = curdate()))) AS `revenue_today`, (select ifnull(sum(`orders`.`final_amount`),0) from `orders` where ((`orders`.`payment_status` = 'paid') and (yearweek(`orders`.`created_at`,0) = yearweek(curdate(),0)))) AS `revenue_week`, (select ifnull(sum(`orders`.`final_amount`),0) from `orders` where ((`orders`.`payment_status` = 'paid') and (month(`orders`.`created_at`) = month(curdate())) and (year(`orders`.`created_at`) = year(curdate())))) AS `revenue_month`, (select count(0) from `users` where (cast(`users`.`created_at` as date) = curdate())) AS `new_users_today`, (select count(0) from `chats` where (`chats`.`status` = 'active')) AS `active_chats`, (select count(0) from `messages` where ((`messages`.`sender_type` = 'user') and (`messages`.`is_read_admin` = 0))) AS `unread_messages` ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_phone` (`phone`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- Индексы таблицы `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_permission` (`admin_id`,`permission`);

--
-- Индексы таблицы `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_chats` (`user_id`),
  ADD KEY `idx_admin_chats` (`admin_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_telegram_chat` (`telegram_chat_id`,`telegram_thread_id`),
  ADD KEY `idx_telegram_chat_thread` (`telegram_chat_id`,`telegram_thread_id`);

--
-- Индексы таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_id` (`chat_id`);

--
-- Индексы таблицы `email_codes`
--
ALTER TABLE `email_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Индексы таблицы `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_login` (`user_type`,`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_messages` (`chat_id`,`created_at`),
  ADD KEY `idx_unread` (`chat_id`),
  ADD KEY `idx_unread_admin` (`chat_id`,`sender_type`,`is_read_admin`),
  ADD KEY `idx_unread_user` (`chat_id`,`sender_type`,`is_read_user`);

--
-- Индексы таблицы `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Индексы таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_notifications` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `idx_user_orders` (`user_id`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_deadline` (`deadline_at`),
  ADD KEY `idx_source` (`source`),
  ADD KEY `idx_manager` (`manager_id`),
  ADD KEY `idx_tinkoff_payment_id` (`tinkoff_payment_id`);

--
-- Индексы таблицы `order_files`
--
ALTER TABLE `order_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_files` (`order_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `idx_order_items` (`order_id`);

--
-- Индексы таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_history` (`order_id`,`created_at`);

--
-- Индексы таблицы `promocodes`
--
ALTER TABLE `promocodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `valid_from` (`valid_from`),
  ADD KEY `valid_until` (`valid_until`),
  ADD KEY `created_by` (`created_by`);

--
-- Индексы таблицы `promocode_usage`
--
ALTER TABLE `promocode_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promocode_id` (`promocode_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `used_at` (`used_at`);

--
-- Индексы таблицы `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_valid_dates` (`valid_from`,`valid_until`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_review` (`order_id`),
  ADD KEY `fk_reviews_user` (`user_id`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Индексы таблицы `service_parameters`
--
ALTER TABLE `service_parameters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_params` (`service_id`,`parameter_type`);

--
-- Индексы таблицы `service_price_rules`
--
ALTER TABLE `service_price_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_rules` (`service_id`,`rule_type`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_setting_key` (`setting_key`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Индексы таблицы `sms_codes`
--
ALTER TABLE `sms_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone_code` (`phone`,`code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Индексы таблицы `sms_history`
--
ALTER TABLE `sms_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sent_by` (`sent_by`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Индексы таблицы `sms_log`
--
ALTER TABLE `sms_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `level` (`level`),
  ADD KEY `category` (`category`),
  ADD KEY `created_at` (`created_at`);

--
-- Индексы таблицы `tinkoff_notifications`
--
ALTER TABLE `tinkoff_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_transaction_status` (`status`),
  ADD KEY `idx_user_transactions` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_phone_unique` (`phone`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone_verified` (`phone`,`is_verified`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT для таблицы `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT для таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `email_codes`
--
ALTER TABLE `email_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT для таблицы `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=285;

--
-- AUTO_INCREMENT для таблицы `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `order_files`
--
ALTER TABLE `order_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `promocodes`
--
ALTER TABLE `promocodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `promocode_usage`
--
ALTER TABLE `promocode_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `service_parameters`
--
ALTER TABLE `service_parameters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `service_price_rules`
--
ALTER TABLE `service_price_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `sms_codes`
--
ALTER TABLE `sms_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT для таблицы `sms_history`
--
ALTER TABLE `sms_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `sms_log`
--
ALTER TABLE `sms_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tinkoff_notifications`
--
ALTER TABLE `tinkoff_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Ограничения внешнего ключа таблицы `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `order_files`
--
ALTER TABLE `order_files`
  ADD CONSTRAINT `order_files_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `promocodes`
--
ALTER TABLE `promocodes`
  ADD CONSTRAINT `fk_promocodes_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);

--
-- Ограничения внешнего ключа таблицы `promocode_usage`
--
ALTER TABLE `promocode_usage`
  ADD CONSTRAINT `fk_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `fk_usage_promocode` FOREIGN KEY (`promocode_id`) REFERENCES `promocodes` (`id`),
  ADD CONSTRAINT `fk_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `service_parameters`
--
ALTER TABLE `service_parameters`
  ADD CONSTRAINT `service_parameters_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `service_price_rules`
--
ALTER TABLE `service_price_rules`
  ADD CONSTRAINT `service_price_rules_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `sms_log`
--
ALTER TABLE `sms_log`
  ADD CONSTRAINT `sms_log_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
