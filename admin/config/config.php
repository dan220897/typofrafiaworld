<?php
// admin/config/config.php - Основные настройки системы

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Режим отладки
define('DEBUG_MODE', true); // Поменяйте на false в production
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Основные настройки системы
define('SITE_NAME', 'Типография - Панель управления');
define('SITE_URL', 'https://typo-grafia.ru');
define('ADMIN_URL', SITE_URL . '/admin');

// Базовые пути
define('BASE_PATH', dirname(dirname(__DIR__))); // Корневая директория сайта
define('ADMIN_PATH', dirname(__DIR__)); // Директория админки
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LOG_PATH', BASE_PATH . '/logs');

// Директория для логов
define('LOG_DIR', dirname(__DIR__) . '/logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR, CRITICAL
define('LOG_FILE_SIZE_LIMIT', 10 * 1024 * 1024); // 10 MB

// Создаем директории если их нет
$required_dirs = [LOG_DIR, UPLOAD_PATH, UPLOAD_PATH . '/chat_files', UPLOAD_PATH . '/orders'];
foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'anikannx_printtg');
define('DB_USER', 'anikannx_printtg');
define('DB_PASS', 'Mur645519!');
define('DB_CHARSET', 'utf8mb4');

// Настройки безопасности
define('SESSION_LIFETIME', 3600); // 1 час
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 минут
define('CSRF_TOKEN_LIFETIME', 3600);
define('SALT', 'Xk8#mP2$vL9@nQ5&'); // Уникальная соль для хеширования

// API-ключ для SMS.ru
define('SMS_RU_API_KEY', '658A225F-F674-C908-78C2-BBE9E3A5F69D');
define('SMS_API_KEY', SMS_RU_API_KEY); // Алиас для совместимости
define('SMS_PROVIDER', 'sms.ru');
define('SMS_CODE_LENGTH', 6);
define('SMS_CODE_LIFETIME', 300); // 5 минут

// Демо-режим (для тестирования без отправки реальных SMS)
define('DEMO_MODE', true); // Поменяйте на false для реальной отправки SMS

// Конфигурация Telegram бота
define('BOT_TOKEN', '7864754568:AAHJ_SGi9qq7xL49JdMAW-CVYmZSCr6XaRo');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('MANAGER_CHAT_ID', '7497468073');
define('TELEGRAM_NOTIFICATIONS_ENABLED', true);

// Данные Тинькофф
define('TINKOFF_TERMINAL_KEY', '1744391857550');
define('TINKOFF_PASSWORD', '1l2M#Rh4MklXdppe');
define('TINKOFF_API_URL', 'https://securepay.tinkoff.ru/v2/');

// Настройки загрузки файлов
define('UPLOAD_DIR', dirname(dirname(__DIR__)) . '/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar']);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Настройки чата
define('CHAT_MESSAGES_PER_PAGE', 50);
define('CHAT_POLLING_INTERVAL', 3000); // 3 секунды
define('CHAT_SESSION_TIMEOUT', 1800); // 30 минут неактивности
define('TYPING_INDICATOR_TIMEOUT', 1000); // 1 секунда

// Настройки пагинации
define('ITEMS_PER_PAGE', 20);

// Настройки заказов - ПРАВИЛЬНЫЕ статусы
define('ORDER_STATUSES', [
    'draft' => 'Черновик',
    'pending' => 'Ожидает подтверждения',
    'confirmed' => 'Подтвержден',
    'in_production' => 'В производстве',
    'ready' => 'Готов',
    'delivered' => 'Доставлен',
    'cancelled' => 'Отменен'
]);

// Статусы оплаты
define('PAYMENT_STATUSES', [
    'pending' => 'Ожидает оплаты',
    'paid' => 'Оплачен',
    'failed' => 'Ошибка оплаты',
    'refunded' => 'Возврат'
]);

// Email настройки
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'noreply@typo-grafia.ru');
define('SMTP_FROM_NAME', 'Типография');

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $file = dirname(__DIR__) . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ===== БАЗОВЫЕ ФУНКЦИИ БЕЗОПАСНОСТИ =====

/**
 * Проверка авторизации администратора
 */
if (!function_exists('checkAuth')) {
    function checkAuth() {
        if (!isset($_SESSION['admin_id'])) {
            // Сохраняем текущий URL для редиректа после входа
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            header('Location: /admin/login.php');
            exit;
        }
        
        // Обновляем время последней активности
        $_SESSION['last_activity'] = time();
        
        // Проверяем таймаут сессии
        if (isset($_SESSION['login_time'])) {
            $session_lifetime = SESSION_LIFETIME;
            if (time() - $_SESSION['login_time'] > $session_lifetime) {
                session_destroy();
                header('Location: /admin/login.php?timeout=1');
                exit;
            }
        }
    }
}

/**
 * Проверка прав доступа
 */
if (!function_exists('checkPermission')) {
    function checkPermission($permission) {
        if (!isset($_SESSION['admin_role'])) {
            return false;
        }
        
        // Супер-админ имеет все права
        if ($_SESSION['admin_role'] === 'super_admin') {
            return true;
        }
        
        // Список прав для ролей
        $permissions = [
            'manager' => [
                'view_orders', 'edit_orders', 'view_users', 
                'view_chats', 'send_messages', 'view_services', 'view_reports'
            ],
            'operator' => [
                'view_orders', 'view_chats', 'send_messages'
            ]
        ];
        
        $role = $_SESSION['admin_role'];
        
        return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
    }
}

// ===== ФУНКЦИИ ФОРМАТИРОВАНИЯ =====

/**
 * Форматирование времени для отображения
 */
if (!function_exists('formatTime')) {
    function formatTime($datetime) {
        if (!$datetime) return '';
        
        $timestamp = strtotime($datetime);
        $now = time();
        $diff = $now - $timestamp;
        
        // Менее минуты назад
        if ($diff < 60) {
            return 'только что';
        }
        
        // Менее часа назад
        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' ' . plural($minutes, 'минуту', 'минуты', 'минут') . ' назад';
        }
        
        // Сегодня
        if (date('Y-m-d', $timestamp) == date('Y-m-d', $now)) {
            return 'сегодня в ' . date('H:i', $timestamp);
        }
        
        // Вчера
        if (date('Y-m-d', $timestamp) == date('Y-m-d', $now - 86400)) {
            return 'вчера в ' . date('H:i', $timestamp);
        }
        
        // В этом году
        if (date('Y', $timestamp) == date('Y', $now)) {
            return date('d ', $timestamp) . getMonthName(date('n', $timestamp)) . ' в ' . date('H:i', $timestamp);
        }
        
        // Полная дата
        return date('d.m.Y H:i', $timestamp);
    }
}

/**
 * Форматирование даты
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd.m.Y') {
        if (!$date) return '';
        return date($format, strtotime($date));
    }
}

/**
 * Форматирование телефона
 */
if (!function_exists('formatPhone')) {
    function formatPhone($phone) {
        if (!$phone) return '';
        
        // Удаляем все кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Форматируем в зависимости от длины
        if (strlen($phone) == 11) {
            // +7 (999) 123-45-67
            return sprintf('+%s (%s) %s-%s-%s',
                substr($phone, 0, 1),
                substr($phone, 1, 3),
                substr($phone, 4, 3),
                substr($phone, 7, 2),
                substr($phone, 9, 2)
            );
        } elseif (strlen($phone) == 10) {
            // (999) 123-45-67
            return sprintf('(%s) %s-%s-%s',
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6, 2),
                substr($phone, 8, 2)
            );
        }
        
        return $phone;
    }
}

/**
 * Форматирование цены
 */
if (!function_exists('formatPrice')) {
    function formatPrice($price, $currency = '₽') {
        return number_format($price, 0, ',', ' ') . ' ' . $currency;
    }
}

/**
 * Склонение числительных
 */
if (!function_exists('plural')) {
    function plural($number, $one, $two, $five) {
        $number = abs($number) % 100;
        $n1 = $number % 10;
        
        if ($number > 10 && $number < 20) {
            return $five;
        }
        if ($n1 > 1 && $n1 < 5) {
            return $two;
        }
        if ($n1 == 1) {
            return $one;
        }
        
        return $five;
    }
}

/**
 * Получение названия месяца
 */
if (!function_exists('getMonthName')) {
    function getMonthName($month) {
        $months = [
            1 => 'января', 2 => 'февраля', 3 => 'марта',
            4 => 'апреля', 5 => 'мая', 6 => 'июня',
            7 => 'июля', 8 => 'августа', 9 => 'сентября',
            10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        ];
        
        return $months[$month] ?? '';
    }
}

// ===== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ =====

/**
 * Функция для безопасного вывода
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Функция для логирования
 */
if (!function_exists('logMessage')) {
    function logMessage($message, $level = 'INFO', $context = []) {
        if (!is_dir(LOG_DIR)) {
            mkdir(LOG_DIR, 0755, true);
        }
        
        $logFile = LOG_DIR . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        // Ротация логов
        if (file_exists($logFile) && filesize($logFile) > LOG_FILE_SIZE_LIMIT) {
            rename($logFile, $logFile . '.' . time());
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Логирование действий администратора
 */
if (!function_exists('logAdminAction')) {
    function logAdminAction($admin_id, $action, $details = null) {
        global $db;
        
        if (!$db) {
            return false;
        }
        
        try {
            $query = "INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent, created_at) 
                     VALUES (:admin_id, :action, :details, :ip, :user_agent, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':details', $details);
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('Error logging admin action: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Генерация безопасного токена
 */
if (!function_exists('generateToken')) {
    function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}

/**
 * Очистка входных данных
 */
if (!function_exists('cleanInput')) {
    function cleanInput($data) {
        if (is_array($data)) {
            return array_map('cleanInput', $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
}

/**
 * Валидация email
 */
if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

/**
 * Валидация телефона
 */
if (!function_exists('validatePhone')) {
    function validatePhone($phone) {
        // Удаляем все кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Проверяем длину (10 или 11 цифр)
        return strlen($phone) == 10 || strlen($phone) == 11;
    }
}

/**
 * Генерация пароля
 */
if (!function_exists('generatePassword')) {
    function generatePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        return substr(str_shuffle($chars), 0, $length);
    }
}

/**
 * Хеширование пароля
 */
if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

/**
 * Проверка пароля
 */
if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

/**
 * Форматирование размера файла
 */
if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

/**
 * Генерация уникального имени файла
 */
if (!function_exists('generateUniqueFileName')) {
    function generateUniqueFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $uniqueName = $filename . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        return $uniqueName;
    }
}

/**
 * Проверка расширения файла
 */
if (!function_exists('isAllowedFileType')) {
    function isAllowedFileType($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, ALLOWED_FILE_TYPES);
    }
}

/**
 * Получение названия роли администратора
 */
if (!function_exists('getAdminRoleLabel')) {
    function getAdminRoleLabel($role) {
        $roles = [
            'super_admin' => 'Супер администратор',
            'manager' => 'Менеджер', 
            'operator' => 'Оператор'
        ];
        return $roles[$role] ?? $role;
    }
}

/**
 * Получение IP адреса клиента
 */
if (!function_exists('getClientIP')) {
    function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

/**
 * Генерация CSRF токена
 */
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = generateToken(32);
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Проверка CSRF токена
 */
if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Создание миниатюры изображения
 */
if (!function_exists('createThumbnail')) {
    function createThumbnail($source, $destination, $width, $height) {
        $info = getimagesize($source);
        
        if ($info === false) {
            return false;
        }
        
        $mime = $info['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                break;
            default:
                return false;
        }
        
        $thumb = imagecreatetruecolor($width, $height);
        
        // Сохраняем прозрачность для PNG
        if ($mime == 'image/png') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, 
                          $width, $height, $info[0], $info[1]);
        
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($thumb, $destination, 90);
                break;
            case 'image/png':
                imagepng($thumb, $destination, 9);
                break;
            case 'image/gif':
                imagegif($thumb, $destination);
                break;
        }
        
        imagedestroy($image);
        imagedestroy($thumb);
        
        return true;
    }
}

/**
 * Отправка email
 */
if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $message, $from = null) {
        if (!$from) {
            $from = SMTP_FROM_EMAIL;
        }
        
        $headers = [
            'From' => $from,
            'Reply-To' => $from,
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        return mail($to, $subject, $message, $headers);
    }
}

// ===== ПРОВЕРКА АВТОРИЗАЦИИ =====

// Проверка авторизации (кроме страницы логина и API)
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'login-sms.php', 'logout.php', 'forgot-password.php'];
$is_api = strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

if (!in_array($current_page, $public_pages) && !$is_api) {
    checkAuth();
}
