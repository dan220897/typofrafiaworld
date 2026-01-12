<?php
require_once dirname(__DIR__) . '/config/config.php';

class EmailService {
    private $db;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->fromEmail = ADMIN_EMAIL;
        $this->fromName = SITE_NAME;
    }
    
    /**
     * Отправка Email с кодом
     */
    public function sendCode($email, $code) {
        try {
            // Код в начале темы письма
            $subject = "{$code} - Код подтверждения для " . SITE_NAME;

            // HTML шаблон письма
            $message = $this->getEmailTemplate($code);

            // Заголовки для HTML письма
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $headers .= "Reply-To: {$this->fromEmail}\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Логируем попытку отправки
            logMessage("Попытка отправки email на {$email} с кодом {$code}", 'INFO');

            // Отправляем письмо
            $sent = @mail($email, $subject, $message, $headers);

            // Проверяем результат и последнюю ошибку
            $lastError = error_get_last();

            if (!$sent) {
                $errorMsg = $lastError ? $lastError['message'] : 'mail() вернул false';
                logMessage("Ошибка mail(): {$errorMsg}. Возможно, mail() не настроен на сервере. Проверьте настройки SMTP.", 'ERROR');

                throw new Exception('Не удалось отправить письмо. Пожалуйста, обратитесь к администратору.');
            }

            logMessage("Email код успешно отправлен на адрес {$email}", 'INFO');

            return [
                'success' => true,
                'message' => 'Код отправлен на email'
            ];

        } catch (Exception $e) {
            logMessage("Ошибка отправки Email на {$email}: " . $e->getMessage(), 'ERROR');

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Создание и сохранение Email кода в БД
     */
    public function createAndSendCode($email) {
        try {
            // Проверяем лимит отправки (не более 3 писем в час на один адрес)
            if (!$this->checkSendLimit($email)) {
                return [
                    'success' => false,
                    'error' => 'Превышен лимит отправки кодов. Попробуйте позже.'
                ];
            }
            
            // Генерируем код
            $code = generateCode(6);
            
            // Сохраняем в базу
            $stmt = $this->db->prepare("
                INSERT INTO email_codes (email, code, expires_at, ip_address, user_agent) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?, ?)
            ");
            
            $result = $stmt->execute([
                $email,
                $code,
                SMS_CODE_LIFETIME, // Используем тот же таймаут
                getUserIP(),
                getUserAgent()
            ]);
            
            if (!$result) {
                throw new Exception('Ошибка сохранения кода в базу данных');
            }
            
            // Отправляем Email
            $emailResult = $this->sendCode($email, $code);
            
            if (!$emailResult['success']) {
                // Если отправка не удалась, удаляем код из БД
                $this->db->prepare("DELETE FROM email_codes WHERE email = ? AND code = ?")
                         ->execute([$email, $code]);
                
                return $emailResult;
            }
            
            return [
                'success' => true,
                'message' => 'Код отправлен на ' . $this->maskEmail($email)
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка создания Email кода для {$email}: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => 'Ошибка отправки кода'
            ];
        }
    }
    
    /**
     * Проверка Email кода
     */
    public function verifyCode($email, $code) {
        try {
            // Получаем код из БД
            $stmt = $this->db->prepare("
                SELECT id, attempts, expires_at, is_used 
                FROM email_codes 
                WHERE email = ? AND code = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$email, $code]);
            $emailCode = $stmt->fetch();
            
            if (!$emailCode) {
                return [
                    'success' => false,
                    'error' => 'Неверный код'
                ];
            }
            
            // Проверяем, не истек ли код
            if (strtotime($emailCode['expires_at']) < time()) {
                return [
                    'success' => false,
                    'error' => 'Код истек. Запросите новый код.'
                ];
            }
            
            // Проверяем, не использован ли код
            if ($emailCode['is_used']) {
                return [
                    'success' => false,
                    'error' => 'Код уже использован'
                ];
            }
            
            // Проверяем количество попыток
            if ($emailCode['attempts'] >= 3) {
                return [
                    'success' => false,
                    'error' => 'Превышено количество попыток. Запросите новый код.'
                ];
            }
            
            // Отмечаем код как использованный
            $stmt = $this->db->prepare("
                UPDATE email_codes 
                SET is_used = 1, attempts = attempts + 1 
                WHERE id = ?
            ");
            $stmt->execute([$emailCode['id']]);
            
            logMessage("Email код успешно проверен для адреса {$email}", 'INFO');
            
            return [
                'success' => true,
                'message' => 'Код подтвержден'
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка проверки Email кода для {$email}: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => 'Ошибка проверки кода'
            ];
        }
    }
    
    /**
     * Увеличение счетчика попыток при неверном коде
     */
    public function incrementAttempts($email, $code) {
        try {
            $stmt = $this->db->prepare("
                UPDATE email_codes 
                SET attempts = attempts + 1 
                WHERE email = ? AND code = ? AND is_used = 0
            ");
            $stmt->execute([$email, $code]);
            
        } catch (Exception $e) {
            logMessage("Ошибка увеличения счетчика попыток для {$email}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Проверка лимита отправки Email
     */
    private function checkSendLimit($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM email_codes 
                WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            
            return $result['count'] < 20;
            
        } catch (Exception $e) {
            logMessage("Ошибка проверки лимита Email для {$email}: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Маскировка email для отображения
     */
    private function maskEmail($email) {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        $usernameLen = strlen($username);
        if ($usernameLen <= 2) {
            $maskedUsername = str_repeat('*', $usernameLen);
        } else {
            $visibleChars = min(2, floor($usernameLen / 3));
            $maskedUsername = substr($username, 0, $visibleChars) . 
                            str_repeat('*', $usernameLen - $visibleChars * 2) . 
                            substr($username, -$visibleChars);
        }
        
        return $maskedUsername . '@' . $domain;
    }
    
    /**
     * HTML шаблон письма с кодом
     */
    private function getEmailTemplate($code) {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">' . SITE_NAME . '</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; margin-top: 0; font-size: 24px;">Код подтверждения</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6;">
                                Здравствуйте! Ваш код для входа в систему:
                            </p>
                            <div style="background-color: #f8f9fa; border: 2px dashed #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;">
                                <div style="font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 8px; font-family: monospace;">
                                    ' . $code . '
                                </div>
                            </div>
                            <p style="color: #666666; font-size: 14px; line-height: 1.6;">
                                Код действителен в течение <strong>5 минут</strong>.
                            </p>
                            <p style="color: #999999; font-size: 13px; line-height: 1.6; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eeeeee;">
                                <strong>Важно:</strong> Никому не сообщайте этот код. Если вы не запрашивали код, просто проигнорируйте это письмо.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                © ' . date('Y') . ' ' . SITE_NAME . '. Все права защищены.
                            </p>
                            <p style="color: #999999; font-size: 12px; margin: 10px 0 0 0;">
                                <a href="' . SITE_URL . '" style="color: #667eea; text-decoration: none;">' . SITE_URL . '</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        ';
    }
    
    /**
     * Очистка старых Email кодов
     */
    public function cleanupOldCodes() {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM email_codes 
                WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
            ");
            $result = $stmt->execute();
            
            $deletedCount = $stmt->rowCount();
            if ($deletedCount > 0) {
                logMessage("Удалено {$deletedCount} старых Email кодов", 'INFO');
            }
            
            return $deletedCount;
            
        } catch (Exception $e) {
            logMessage("Ошибка очистки старых Email кодов: " . $e->getMessage(), 'ERROR');
            return 0;
        }
    }
}
?>