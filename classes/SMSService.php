<?php
require_once dirname(__DIR__) . '/config/config.php';

class SMSService {
    private $apiKey;
    private $apiUrl = 'https://sms.ru/sms/send';
    private $db;
    
    public function __construct() {
        $this->apiKey = SMS_RU_API_KEY;
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Отправка SMS кода
     */
    public function sendCode($phone, $code) {
        try {
            // Формируем текст сообщения
            $message = "Ваш код для входа в " . SITE_NAME . ": {$code}. Никому не сообщайте этот код.";
            
            // Данные для отправки
            $postData = [
                'api_id' => $this->apiKey,
                'to' => $phone,
                'msg' => $message,
                'json' => 1
            ];
            
            // Выполняем запрос
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                throw new Exception('CURL Error: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            // Парсим ответ
            $result = json_decode($response, true);
            
            if ($httpCode !== 200) {
                throw new Exception("HTTP Error: {$httpCode}");
            }
            
            if (!$result || $result['status'] !== 'OK') {
                $errorMsg = isset($result['status_text']) ? $result['status_text'] : 'Неизвестная ошибка';
                throw new Exception("SMS.ru Error: {$errorMsg}");
            }
            
            logMessage("SMS код отправлен на номер {$phone}", 'INFO');
            
            return [
                'success' => true,
                'message' => 'SMS код отправлен',
                'sms_id' => $result['sms'][$phone]['sms_id'] ?? null
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка отправки SMS на {$phone}: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Создание и сохранение SMS кода в БД
     */
    public function createAndSendCode($phone) {
        try {
            // Проверяем лимит отправки (не более 3 SMS в час на один номер)
            if (!$this->checkSendLimit($phone)) {
                return [
                    'success' => false,
                    'error' => 'Превышен лимит отправки SMS. Попробуйте позже.'
                ];
            }
            
            // Генерируем код
            $code = generateCode(6);
            
            // Сохраняем в базу
            $stmt = $this->db->prepare("
                INSERT INTO sms_codes (phone, code, expires_at, ip_address, user_agent) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?, ?)
            ");
            
            $result = $stmt->execute([
                $phone,
                $code,
                SMS_CODE_LIFETIME,
                getUserIP(),
                getUserAgent()
            ]);
            
            if (!$result) {
                throw new Exception('Ошибка сохранения кода в базу данных');
            }
            
            // Отправляем SMS
            $smsResult = $this->sendCode($phone, $code);
            
            if (!$smsResult['success']) {
                // Если отправка не удалась, удаляем код из БД
                $this->db->prepare("DELETE FROM sms_codes WHERE phone = ? AND code = ?")
                         ->execute([$phone, $code]);
                
                return $smsResult;
            }
            
            return [
                'success' => true,
                'message' => 'SMS код отправлен на номер ' . $this->maskPhone($phone)
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка создания SMS кода для {$phone}: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => 'Ошибка отправки SMS кода'
            ];
        }
    }
    
    /**
     * Проверка SMS кода
     */
    public function verifyCode($phone, $code) {
        try {
            // Получаем код из БД
            $stmt = $this->db->prepare("
                SELECT id, attempts, expires_at, is_used 
                FROM sms_codes 
                WHERE phone = ? AND code = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$phone, $code]);
            $smsCode = $stmt->fetch();
            
            if (!$smsCode) {
                return [
                    'success' => false,
                    'error' => 'Неверный код'
                ];
            }
            
            // Проверяем, не истек ли код
            if (strtotime($smsCode['expires_at']) < time()) {
                return [
                    'success' => false,
                    'error' => 'Код истек. Запросите новый код.'
                ];
            }
            
            // Проверяем, не использован ли код
            if ($smsCode['is_used']) {
                return [
                    'success' => false,
                    'error' => 'Код уже использован'
                ];
            }
            
            // Проверяем количество попыток
            if ($smsCode['attempts'] >= 3) {
                return [
                    'success' => false,
                    'error' => 'Превышено количество попыток. Запросите новый код.'
                ];
            }
            
            // Отмечаем код как использованный
            $stmt = $this->db->prepare("
                UPDATE sms_codes 
                SET is_used = 1, attempts = attempts + 1 
                WHERE id = ?
            ");
            $stmt->execute([$smsCode['id']]);
            
            logMessage("SMS код успешно проверен для номера {$phone}", 'INFO');
            
            return [
                'success' => true,
                'message' => 'Код подтвержден'
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка проверки SMS кода для {$phone}: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => 'Ошибка проверки кода'
            ];
        }
    }
    
    /**
     * Увеличение счетчика попыток при неверном коде
     */
    public function incrementAttempts($phone, $code) {
        try {
            $stmt = $this->db->prepare("
                UPDATE sms_codes 
                SET attempts = attempts + 1 
                WHERE phone = ? AND code = ? AND is_used = 0
            ");
            $stmt->execute([$phone, $code]);
            
        } catch (Exception $e) {
            logMessage("Ошибка увеличения счетчика попыток для {$phone}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Проверка лимита отправки SMS
     */
    private function checkSendLimit($phone) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM sms_codes 
                WHERE phone = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$phone]);
            $result = $stmt->fetch();
            
            return $result['count'] < 3;
            
        } catch (Exception $e) {
            logMessage("Ошибка проверки лимита SMS для {$phone}: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Маскировка номера телефона для отображения
     */
    private function maskPhone($phone) {
        if (strlen($phone) === 12) { // +7XXXXXXXXXX
            return substr($phone, 0, 2) . 'XXX' . substr($phone, 5, 3) . 'XX' . substr($phone, -2);
        }
        return $phone;
    }
    
    /**
     * Очистка старых SMS кодов
     */
    public function cleanupOldCodes() {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM sms_codes 
                WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
            ");
            $result = $stmt->execute();
            
            $deletedCount = $stmt->rowCount();
            if ($deletedCount > 0) {
                logMessage("Удалено {$deletedCount} старых SMS кодов", 'INFO');
            }
            
            return $deletedCount;
            
        } catch (Exception $e) {
            logMessage("Ошибка очистки старых SMS кодов: " . $e->getMessage(), 'ERROR');
            return 0;
        }
    }
    
    /**
     * Получение статистики SMS
     */
    public function getStats($period = 'today') {
        try {
            $whereClause = '';
            
            switch ($period) {
                case 'today':
                    $whereClause = "WHERE DATE(created_at) = CURDATE()";
                    break;
                case 'week':
                    $whereClause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $whereClause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_sent,
                    COUNT(CASE WHEN is_used = 1 THEN 1 END) as verified_count,
                    COUNT(DISTINCT phone) as unique_phones
                FROM sms_codes 
                {$whereClause}
            ");
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            logMessage("Ошибка получения статистики SMS: " . $e->getMessage(), 'ERROR');
            return [
                'total_sent' => 0,
                'verified_count' => 0,
                'unique_phones' => 0
            ];
        }
    }
    
    /**
     * Проверка баланса на SMS.ru
     */
    public function checkBalance() {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://sms.ru/my/balance');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'api_id' => $this->apiKey,
                'json' => 1
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception("HTTP Error: {$httpCode}");
            }
            
            $result = json_decode($response, true);
            
            if (!$result || $result['status'] !== 'OK') {
                throw new Exception('Ошибка получения баланса');
            }
            
            return [
                'success' => true,
                'balance' => $result['balance']
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка проверки баланса SMS.ru: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>