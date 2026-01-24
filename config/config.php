<?php
// Предотвращение повторной загрузки конфигурации
if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

// Предотвращение прямого доступа
if (!defined('SYSTEM_INIT')) {
    define('SYSTEM_INIT', true);
}

// Настройки базы данных
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'anikannx_printtg');
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'anikannx_printtg');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', 'Mur645519!');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// API ключи
if (!defined('SMS_RU_API_KEY')) {
    define('SMS_RU_API_KEY', '658A225F-F674-C908-78C2-BBE9E3A5F69D');
}

// Основные настройки системы
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Типо-графия');
}
if (!defined('SITE_URL')) {
    define('SITE_URL', 'https://typo-grafia.ru');
}
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'info@typo-grafia.ru');
}

if (!defined('BOT_TOKEN')) {
    define('BOT_TOKEN', '8265444504:AAE2pfiHbPQPqy6RM8bueO831cPFtz3sWqg');
}
if (!defined('TELEGRAM_API_URL')) {
    define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
}
if (!defined('MANAGER_CHAT_ID')) {
    define('MANAGER_CHAT_ID', '-1003168549220');
}
if (!defined('TELEGRAM_NOTIFICATIONS_ENABLED')) {
    define('TELEGRAM_NOTIFICATIONS_ENABLED', true);
}
if (!defined('MANAGER_TELEGRAM_LINK')) {
    define('MANAGER_TELEGRAM_LINK', 'https://t.me/typografia_manager'); // Ссылка на менеджера
}

// Настройки Email и SMTP
if (!defined('USE_SMTP')) {
    define('USE_SMTP', true); // Использовать SMTP вместо mail()
}
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.beget.ru');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 465);
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', 'info@etat.agency');
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', 'Mur220897!');
}
if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', 'ssl'); // 'ssl' или 'tls'
}
if (!defined('EMAIL_FROM_NAME')) {
    define('EMAIL_FROM_NAME', 'PHOTO.ETAT');
}
if (!defined('EMAIL_FROM_ADDRESS')) {
    define('EMAIL_FROM_ADDRESS', 'info@etat.agency');
}
if (!defined('LOG_EMAILS')) {
    define('LOG_EMAILS', true); // Логировать отправку писем
}
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', SITE_URL . '/admin');
}
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', __DIR__ . '/../uploads/');
}
if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', SITE_URL . '/uploads/');
}

// Настройки безопасности
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 дней
}
if (!defined('SMS_CODE_LIFETIME')) {
    define('SMS_CODE_LIFETIME', 300); // 5 минут
}
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}
if (!defined('LOGIN_BLOCK_TIME')) {
    define('LOGIN_BLOCK_TIME', 900); // 15 минут
}

// Настройки файлов
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
}
if (!defined('ALLOWED_FILE_TYPES')) {
    define('ALLOWED_FILE_TYPES', [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ]);
}

// Статусы заказов
if (!defined('ORDER_STATUSES')) {
    define('ORDER_STATUSES', [
        'draft' => 'Черновик',
        'pending' => 'Ожидает подтверждения',
        'confirmed' => 'Подтвержден',
        'in_production' => 'В производстве',
        'ready' => 'Готов к выдаче',
        'delivered' => 'Доставлен',
        'cancelled' => 'Отменен'
    ]);
}

// Настройки логирования
if (!defined('LOG_ERRORS')) {
    define('LOG_ERRORS', true);
}
if (!defined('LOG_FILE')) {
    define('LOG_FILE', __DIR__ . '/../logs/system.log');
}

// Настройки временной зоны
date_default_timezone_set('Europe/Moscow');

// Настройки сессии
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
}

// Класс для работы с базой данных
if (!class_exists('Database')) {
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
                if (function_exists('logMessage')) {
                    logMessage("Ошибка подключения к БД: " . $e->getMessage(), 'ERROR');
                }
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
}

// Функция логирования
if (!function_exists('logMessage')) {
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
}

// Функция для безопасного получения данных из массива
if (!function_exists('getArrayValue')) {
    function getArrayValue($array, $key, $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

// Функция для санитизации данных
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map('sanitizeInput', $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

// Функция для валидации номера телефона
if (!function_exists('validatePhone')) {
    function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '7') {
            return '+' . $phone;
        } elseif (strlen($phone) === 10) {
            return '+7' . $phone;
        }
        return false;
    }
}

// Функция для генерации случайного кода
if (!function_exists('generateCode')) {
    function generateCode($length = 6) {
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}

// Функция для создания безопасного имени файла
if (!function_exists('sanitizeFilename')) {
    function sanitizeFilename($filename) {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return $filename;
    }
}

// Функция для получения MIME типа файла
if (!function_exists('getMimeType')) {
    function getMimeType($filePath) {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($finfo, $filePath);
        } elseif (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }
        return false;
    }
}

// Функция для проверки разрешенного типа файла
if (!function_exists('isAllowedFileType')) {
    function isAllowedFileType($mimeType) {
        return in_array($mimeType, ALLOWED_FILE_TYPES);
    }
}

// Функция для форматирования размера файла
if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Функция для отправки JSON ответа
if (!function_exists('sendJsonResponse')) {
    function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Функция для получения IP адреса пользователя
if (!function_exists('getUserIP')) {
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
}

// Функция для получения User Agent
if (!function_exists('getUserAgent')) {
    function getUserAgent() {
        return getArrayValue($_SERVER, 'HTTP_USER_AGENT', 'Unknown');
    }
}

// Создание необходимых директорий
if (!function_exists('createRequiredDirectories')) {
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
}

// Инициализация системы
if (!function_exists('initSystem')) {
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
}

// Автоматическая инициализация при подключении файла
if (function_exists('initSystem')) {
    initSystem();
}
?>
