<?php
// admin/includes/constants.php - Константы системы

// Статусы заказов
define('ORDER_STATUSES', [
    'new' => 'Новый',
    'processing' => 'В обработке',
    'in_production' => 'В производстве',
    'shipping' => 'Доставка',
    'completed' => 'Завершен',
    'canceled' => 'Отменен'
]);

// Статусы оплаты
define('PAYMENT_STATUSES', [
    'pending' => 'Ожидает оплаты',
    'paid' => 'Оплачен',
    'partially_paid' => 'Частично оплачен',
    'refunded' => 'Возврат'
]);

// Роли администраторов
define('ADMIN_ROLES', [
    'super_admin' => 'Суперадминистратор',
    'admin' => 'Администратор',
    'manager' => 'Менеджер',
    'operator' => 'Оператор'
]);

// Права доступа
define('ADMIN_PERMISSIONS', [
    // Заказы
    'view_orders' => 'Просмотр заказов',
    'edit_orders' => 'Редактирование заказов',
    'delete_orders' => 'Удаление заказов',
    'export_orders' => 'Экспорт заказов',
    
    // Пользователи
    'view_users' => 'Просмотр пользователей',
    'edit_users' => 'Редактирование пользователей',
    'delete_users' => 'Удаление пользователей',
    'block_users' => 'Блокировка пользователей',
    
    // Чаты
    'view_chats' => 'Просмотр чатов',
    'send_messages' => 'Отправка сообщений',
    'close_chats' => 'Закрытие чатов',
    
    // Услуги
    'view_services' => 'Просмотр услуг',
    'edit_services' => 'Редактирование услуг',
    'delete_services' => 'Удаление услуг',
    
    // Отзывы
    'view_reviews' => 'Просмотр отзывов',
    'edit_reviews' => 'Редактирование отзывов',
    'delete_reviews' => 'Удаление отзывов',
    'publish_reviews' => 'Публикация отзывов',
    
    // Промокоды
    'view_promocodes' => 'Просмотр промокодов',
    'edit_promocodes' => 'Редактирование промокодов',
    'delete_promocodes' => 'Удаление промокодов',
    
    // Система
    'view_reports' => 'Просмотр отчетов',
    'manage_settings' => 'Управление настройками',
    'view_logs' => 'Просмотр логов',
    'manage_admins' => 'Управление администраторами',
    'backup_database' => 'Резервное копирование',
    'export_data' => 'Экспорт данных',
    'send_notifications' => 'Отправка уведомлений'
]);

// Типы доставки
define('DELIVERY_TYPES', [
    'pickup' => 'Самовывоз',
    'delivery' => 'Доставка',
    'courier' => 'Курьер'
]);

// Типы скидок
define('DISCOUNT_TYPES', [
    'percent' => 'Процент',
    'fixed' => 'Фиксированная сумма'
]);

// Статусы чатов
define('CHAT_STATUSES', [
    'active' => 'Активный',
    'closed' => 'Закрыт'
]);

// Типы отправителей в чате
define('CHAT_SENDER_TYPES', [
    'user' => 'Пользователь',
    'admin' => 'Администратор'
]);

// Категории услуг
define('SERVICE_CATEGORIES', [
    'печать' => 'Печать',
    'дизайн' => 'Дизайн',
    'постпечать' => 'Постпечатная обработка',
    'широкоформат' => 'Широкоформатная печать',
    'сувенирка' => 'Сувенирная продукция'
]);

// Типы параметров услуг
define('SERVICE_PARAMETER_TYPES', [
    'material' => 'Материал',
    'size' => 'Размер',
    'color' => 'Цвет',
    'finish' => 'Обработка',
    'quantity' => 'Тираж'
]);

// Категории настроек
define('SETTINGS_CATEGORIES', [
    'general' => 'Основные',
    'company' => 'Компания',
    'orders' => 'Заказы',
    'notifications' => 'Уведомления',
    'telegram' => 'Telegram',
    'sms' => 'SMS',
    'email' => 'Email',
    'system' => 'Система',
    'backup' => 'Резервное копирование'
]);

// Периоды для статистики
define('STAT_PERIODS', [
    'today' => 'Сегодня',
    'yesterday' => 'Вчера',
    'week' => 'Неделя',
    'month' => 'Месяц',
    'quarter' => 'Квартал',
    'year' => 'Год',
    'all' => 'Все время'
]);

// Форматы экспорта
define('EXPORT_FORMATS', [
    'csv' => 'CSV',
    'excel' => 'Excel',
    'pdf' => 'PDF'
]);

// Типы действий для логов
define('LOG_ACTIONS', [
    // Авторизация
    'login' => 'Вход в систему',
    'logout' => 'Выход из системы',
    'failed_login' => 'Неудачная попытка входа',
    
    // Заказы
    'create_order' => 'Создание заказа',
    'update_order' => 'Обновление заказа',
    'delete_order' => 'Удаление заказа',
    'update_order_status' => 'Изменение статуса заказа',
    'update_payment_status' => 'Изменение статуса оплаты',
    'duplicate_order' => 'Дублирование заказа',
    'print_order' => 'Печать заказа',
    'send_order_notification' => 'Отправка уведомления по заказу',
    
    // Пользователи
    'create_user' => 'Создание пользователя',
    'update_user' => 'Обновление пользователя',
    'delete_user' => 'Удаление пользователя',
    'block_user' => 'Блокировка пользователя',
    'unblock_user' => 'Разблокировка пользователя',
    'verify_user' => 'Верификация пользователя',
    
    // Услуги
    'create_service' => 'Создание услуги',
    'update_service' => 'Обновление услуги',
    'delete_service' => 'Удаление услуги',
    'toggle_service_status' => 'Изменение статуса услуги',
    
    // Чаты
    'send_chat_message' => 'Отправка сообщения в чат',
    'close_chat' => 'Закрытие чата',
    'reopen_chat' => 'Переоткрытие чата',
    'delete_chat' => 'Удаление чата',
    
    // Отзывы
    'publish_review' => 'Публикация отзыва',
    'unpublish_review' => 'Снятие с публикации отзыва',
    'reply_review' => 'Ответ на отзыв',
    'delete_review' => 'Удаление отзыва',
    
    // Промокоды
    'create_promocode' => 'Создание промокода',
    'update_promocode' => 'Обновление промокода',
    'delete_promocode' => 'Удаление промокода',
    'toggle_promocode_status' => 'Изменение статуса промокода',
    
    // Администраторы
    'create_admin' => 'Создание администратора',
    'update_admin' => 'Обновление администратора',
    'delete_admin' => 'Удаление администратора',
    'update_admin_permissions' => 'Обновление прав администратора',
    
    // Настройки
    'update_settings' => 'Обновление настроек',
    'backup_database' => 'Резервное копирование',
    'restore_backup' => 'Восстановление из резервной копии',
    'clean_logs' => 'Очистка логов',
    
    // Экспорт
    'export_orders' => 'Экспорт заказов',
    'export_users' => 'Экспорт пользователей',
    'export_reports' => 'Экспорт отчетов'
]);

// Размеры файлов
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5 MB

// Разрешенные типы файлов
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/plain',
    'application/zip',
    'application/x-rar-compressed'
]);

// Разрешенные расширения файлов
define('ALLOWED_FILE_EXTENSIONS', [
    'jpg', 'jpeg', 'png', 'gif', 'webp',
    'pdf', 'doc', 'docx', 'xls', 'xlsx',
    'txt', 'zip', 'rar'
]);

// Настройки пагинации
define('DEFAULT_PER_PAGE', 20);
define('MAX_PER_PAGE', 100);

// Настройки безопасности
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_LIFETIME', 86400); // 24 часа
define('REMEMBER_ME_LIFETIME', 2592000); // 30 дней
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 минут

// Валюта
define('CURRENCY_SYMBOL', '₽');
define('CURRENCY_CODE', 'RUB');

// Часовой пояс
define('DEFAULT_TIMEZONE', 'Europe/Moscow');

// Форматы даты и времени
define('DATE_FORMAT', 'd.m.Y');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'd.m.Y H:i');

// Пути к директориям
define('UPLOAD_DIR', '../uploads/');
define('CHAT_UPLOAD_DIR', UPLOAD_DIR . 'chat/');
define('ORDER_UPLOAD_DIR', UPLOAD_DIR . 'orders/');
define('TEMP_DIR', '../temp/');
define('BACKUP_DIR', '../backups/');

// API конфигурация
define('API_RATE_LIMIT', 60); // запросов в минуту
define('API_TIMEOUT', 30); // секунд

// Уведомления
define('NOTIFICATION_TYPES', [
    'order_created' => 'Новый заказ',
    'order_status_changed' => 'Изменение статуса заказа',
    'order_payment_received' => 'Оплата получена',
    'order_ready' => 'Заказ готов',
    'chat_new_message' => 'Новое сообщение в чате',
    'review_posted' => 'Новый отзыв'
]);