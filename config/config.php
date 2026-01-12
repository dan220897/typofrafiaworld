<?php
// Предотвращение прямого доступа
if (!defined('SYSTEM_INIT')) {
    define('SYSTEM_INIT', true);
}

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'anikannx_printtg');
define('DB_USERNAME', 'anikannx_printtg');
define('DB_PASSWORD', 'Mur645519!');
define('DB_CHARSET', 'utf8mb4');

// API ключи
define('SMS_RU_API_KEY', '658A225F-F674-C908-78C2-BBE9E3A5F69D');

// Основные настройки системы
define('SITE_NAME', 'Типо-графия');
define('SITE_URL', 'https://typo-grafia.ru');
define('ADMIN_EMAIL', 'info@typo-grafia.ru');

define('BOT_TOKEN', '8265444504:AAE2pfiHbPQPqy6RM8bueO831cPFtz3sWqg');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('MANAGER_CHAT_ID', '-1003168549220');
define('TELEGRAM_NOTIFICATIONS_ENABLED', true);

// Настройки Email и SMTP
define('USE_SMTP', true); // Использовать SMTP вместо mail()
define('SMTP_HOST', 'smtp.beget.ru');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'info@etat.agency');
define('SMTP_PASSWORD', 'Mur220897!');
define('SMTP_ENCRYPTION', 'ssl'); // 'ssl' или 'tls'
define('EMAIL_FROM_NAME', 'PHOTO.ETAT');
define('EMAIL_FROM_ADDRESS', 'info@etat.agency');
define('LOG_EMAILS', true); // Логировать отправку писем
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('UPLOADS_URL', SITE_URL . '/uploads/');

// Настройки безопасности
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 дней
define('SMS_CODE_LIFETIME', 300); // 5 минут
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_BLOCK_TIME', 900); // 15 минут

// Настройки файлов
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_FILE_TYPES', [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf', 'application/msword', 
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
]);

// Статусы заказов
define('ORDER_STATUSES', [
    'draft' => 'Черновик',
    'pending' => 'Ожидает подтверждения',
    'confirmed' => 'Подтвержден',
    'in_production' => 'В производстве',
    'ready' => 'Готов к выдаче',
    'delivered' => 'Доставлен',
    'cancelled' => 'Отменен'
]);

// Настройки логирования
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/../logs/system.log');

// Настройки временной зоны
date_default_timezone_set('Europe/Moscow');

// Настройки сессии
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Класс для работы с базой данных
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            logMessage("Ошибка подключения к БД: " . $e->getMessage(), 'ERROR');
            throw new Exception("Ошибка подключения к базе данных");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Запрет клонирования и десериализации
    private function __clone() {}
    public function __wakeup() {}
}

// Функция логирования
function logMessage($message, $level = 'INFO') {
    if (!LOG_ERRORS) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    // Создаем директорию для логов если её нет
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    error_log($logEntry, 3, LOG_FILE);
}

// Функция для безопасного получения данных из массива
function getArrayValue($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Функция для санитизации данных
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Функция для валидации номера телефона
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 11 && substr($phone, 0, 1) === '7') {
        return '+' . $phone;
    } elseif (strlen($phone) === 10) {
        return '+7' . $phone;
    }
    return false;
}

// Функция для генерации случайного кода
function generateCode($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Функция для создания безопасного имени файла
function sanitizeFilename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}

// Функция для получения MIME типа файла
function getMimeType($filePath) {
    if (function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($finfo, $filePath);
    } elseif (function_exists('mime_content_type')) {
        return mime_content_type($filePath);
    }
    return false;
}

// Функция для проверки разрешенного типа файла
function isAllowedFileType($mimeType) {
    return in_array($mimeType, ALLOWED_FILE_TYPES);
}

// Функция для форматирования размера файла
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Функция для отправки JSON ответа
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Функция для получения IP адреса пользователя
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Функция для получения User Agent
function getUserAgent() {
    return getArrayValue($_SERVER, 'HTTP_USER_AGENT', 'Unknown');
}

// Создание необходимых директорий
function createRequiredDirectories() {
    $dirs = [
        dirname(LOG_FILE),
        UPLOADS_DIR,
        UPLOADS_DIR . 'messages',
        UPLOADS_DIR . 'orders',
        UPLOADS_DIR . 'temp'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Инициализация системы
function initSystem() {
    // Запускаем сессию
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Создаем необходимые директории
    createRequiredDirectories();
    
    // Логируем запуск системы
    logMessage("Система инициализирована", 'INFO');
}

// Автоматическая инициализация при подключении файла
initSystem();
?>