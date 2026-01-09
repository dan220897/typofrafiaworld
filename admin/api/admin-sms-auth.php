<?php
// admin/api/admin-sms-auth.php - API для SMS авторизации администраторов

session_start();
header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Admin.php';
// Убираем эту строку: require_once '../classes/SMSService.php';

// Получаем данные запроса
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Класс для работы с SMS.ru
class SMSService {
    private $apiKey;
    private $apiUrl = 'https://sms.ru/sms/send';
    
    public function __construct() {
        $this->apiKey = SMS_RU_API_KEY;
    }
    
    public function sendCode($phone, $code) {
        $message = "Ваш код для входа в админ-панель: {$code}\nКод действителен 5 минут.";
        
        $data = [
            'api_id' => $this->apiKey,
            'to' => $phone,
            'msg' => $message,
            'json' => 1
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'HTTP error: ' . $httpCode];
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['status']) && $result['status'] === 'OK') {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => $result['status_text'] ?? 'Unknown error'];
    }
}

// Класс для Telegram уведомлений
class TelegramNotifier {
    private $botToken;
    private $chatId;
    
    public function __construct() {
        $this->botToken = BOT_TOKEN;
        $this->chatId = MANAGER_CHAT_ID;
    }
    
    public function sendAdminLoginNotification($username, $method, $ip) {
        $message = "🔐 *Вход в админ-панель*\n\n";
        $message .= "👤 Администратор: {$username}\n";
        $message .= "🔑 Метод: {$method}\n";
        $message .= "🌐 IP: {$ip}\n";
        $message .= "🕐 Время: " . date('d.m.Y H:i:s');
        
        $this->sendMessage($message);
    }
    
    private function sendMessage($text) {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        curl_exec($ch);
        curl_close($ch);
    }
}

// Функция логирования
function logMessage($message, $level = 'INFO', $context = []) {
    // Проверяем, определена ли константа LOG_DIR
    $logDir = defined('LOG_DIR') ? LOG_DIR : __DIR__ . '/../logs/';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . 'admin_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logEntry = "[{$timestamp}] [{$level}] {$message} {$contextStr}\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Функция логирования действий администратора  
function logAdminAction($adminId, $action, $details = null) {
    global $db;
    
    try {
        $query = "INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent, created_at) 
                  VALUES (:admin_id, :action, :details, :ip, :user_agent, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':admin_id', $adminId);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':details', $details);
        $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
        $stmt->execute();
    } catch (Exception $e) {
        error_log('Failed to log admin action: ' . $e->getMessage());
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    switch($action) {
        case 'send_code':
            // Отправка SMS кода администратору
            $phone = $input['phone'] ?? '';
            
            if (!preg_match('/^\+7\d{10}$/', $phone)) {
                throw new Exception('Неверный формат телефона');
            }
            
            // Проверяем, есть ли админ с таким телефоном
            $query = "SELECT id, username, full_name, email, role, is_active
          FROM admins 
          WHERE phone = :phone 
          AND is_active = 1
          LIMIT 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                throw new Exception('Администратор с таким телефоном не найден');
            }
            
            $admin = $stmt->fetch();
            
            // Проверяем количество попыток за последний час
            $check_attempts = "SELECT COUNT(*) as attempts 
                             FROM sms_codes 
                             WHERE phone = :phone 
                             AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $stmt = $db->prepare($check_attempts);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['attempts'] >= 5) {
                throw new Exception('Слишком много попыток. Попробуйте через час.');
            }
            
            // Генерируем 6-значный код
            $code = sprintf('%06d', mt_rand(100000, 999999));
            
            // Сохраняем код в БД
            $query = "INSERT INTO sms_codes (phone, code, expires_at, created_at) 
                     VALUES (:phone, :code, DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW())
                     ON DUPLICATE KEY UPDATE 
                     code = :code2, 
                     expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE),
                     attempts = 0,
                     is_used = 0";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':code2', $code);
            $stmt->execute();
            
            // Проверяем, в демо-режиме ли мы
            if (defined('DEMO_MODE') && DEMO_MODE) {
                // В демо-режиме не отправляем реальную SMS
                logMessage("SMS код создан для администратора (демо-режим): {$admin['username']}", 'INFO', [
                    'phone' => $phone,
                    'code' => $code,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Код отправлен (демо-режим)',
                    'demo_code' => $code
                ]);
                exit;
            }
            
            // Отправляем SMS через SMS.ru
            $smsService = new SMSService();
            $smsResult = $smsService->sendCode($phone, $code);
            
            if (!$smsResult['success']) {
                // Если не удалось отправить SMS, записываем в лог
                error_log("SMS sending failed for admin {$phone}: " . $smsResult['error']);
                
                throw new Exception('Ошибка отправки SMS. Попробуйте позже.');
            }
            
            // Логируем попытку входа
            logMessage("SMS код отправлен администратору: {$admin['username']}", 'INFO', [
                'phone' => $phone,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Код отправлен на ваш телефон'
            ]);
            break;
            
        case 'verify_code':
            // Проверка кода
            $phone = $input['phone'] ?? '';
            $code = $input['code'] ?? '';
            
            if (!$phone || !$code) {
                throw new Exception('Недостаточно данных');
            }
            
            // Проверяем код
            $query = "SELECT sc.*, a.id as admin_id, a.username, a.full_name, a.email, a.role 
          FROM sms_codes sc
          JOIN admins a ON sc.phone = a.phone
          WHERE sc.phone = :phone 
          AND sc.code = :code 
          AND sc.is_used = 0 
          AND sc.attempts < 5
          AND sc.expires_at > NOW()
          AND a.is_active = 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                // Увеличиваем счетчик попыток
                $update = "UPDATE sms_codes SET attempts = attempts + 1 WHERE phone = :phone";
                $update_stmt = $db->prepare($update);
                $update_stmt->bindParam(':phone', $phone);
                $update_stmt->execute();
                
                throw new Exception('Неверный или истекший код');
            }
            
            $data = $stmt->fetch();
            
            // Помечаем код как использованный
            $update = "UPDATE sms_codes SET is_used = 1 WHERE phone = :phone AND code = :code";
            $update_stmt = $db->prepare($update);
            $update_stmt->bindParam(':phone', $phone);
            $update_stmt->bindParam(':code', $code);
            $update_stmt->execute();
            
            // Создаем сессию
            $_SESSION['admin_id'] = $data['admin_id'];
            $_SESSION['admin_username'] = $data['username'];
            $_SESSION['admin_name'] = $data['full_name'];
            $_SESSION['admin_role'] = $data['role'];
            $_SESSION['admin_email'] = $data['email'];
            $_SESSION['login_time'] = time();
            $_SESSION['login_type'] = 'sms';
            
            // Обновляем время последнего входа
            $update = "UPDATE admins SET last_login = NOW() WHERE id = :id";
            $update_stmt = $db->prepare($update);
            $update_stmt->bindParam(':id', $data['admin_id']);
            $update_stmt->execute();
            
            // Логируем успешный вход
            logAdminAction($data['admin_id'], 'login_sms', 'Вход через SMS');
            
            // Отправляем уведомление в Telegram
            if (defined('TELEGRAM_NOTIFICATIONS_ENABLED') && TELEGRAM_NOTIFICATIONS_ENABLED) {
                $telegram = new TelegramNotifier();
                $telegram->sendAdminLoginNotification($data['username'], 'SMS', $_SERVER['REMOTE_ADDR']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Успешный вход',
                'redirect' => '/admin/index.php'
            ]);
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}