<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/classes/EmailService.php';

class UserService {
    private $db;
    private $emailService;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->emailService = new EmailService();
    }
    
    /**
     * Аутентификация пользователя по email
     */
    public function authenticateByEmail($email) {
        try {
            // Валидируем email
            $validEmail = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
            if (!$validEmail) {
                return [
                    'success' => false,
                    'error' => 'Некорректный email адрес'
                ];
            }
            
            // Проверяем блокировку по IP
            if ($this->isIPBlocked()) {
                return [
                    'success' => false,
                    'error' => 'Превышено количество попыток входа. Попробуйте позже.'
                ];
            }
            
            // Находим или создаем пользователя
            $user = $this->findOrCreateUser($validEmail);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Ошибка создания пользователя'
                ];
            }
            
            // Проверяем, не заблокирован ли пользователь
            if ($user['is_blocked']) {
                $this->logLoginAttempt($user['id'], 'email', 'failed', 'Пользователь заблокирован');
                return [
                    'success' => false,
                    'error' => 'Пользователь заблокирован. Обратитесь в поддержку.'
                ];
            }
            
            // Отправляем Email код
            $emailResult = $this->emailService->createAndSendCode($validEmail);
            if (!$emailResult['success']) {
                $this->logLoginAttempt($user['id'], 'email', 'failed', $emailResult['error']);
                return $emailResult;
            }
            
            // Сохраняем попытку входа в сессии
            $_SESSION['login_email'] = $validEmail;
            $_SESSION['login_user_id'] = $user['id'];
            $_SESSION['login_attempt_time'] = time();
            
            return [
                'success' => true,
                'message' => $emailResult['message'],
                'user_id' => $user['id']
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка аутентификации для {$email}: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => 'Ошибка аутентификации'
            ];
        }
    }
    
    /**
     * Подтверждение входа по Email коду
     */
    public function verifyLogin($code) {
        try {
            // Проверяем данные в сессии
            if (!isset($_SESSION['login_email']) || !isset($_SESSION['login_user_id'])) {
                return [
                    'success' => false,
                    'error' => 'Сессия истекла. Начните процедуру входа заново.'
                ];
            }
            
            $email = $_SESSION['login_email'];
            $userId = $_SESSION['login_user_id'];
            
            // Проверяем код
            $verifyResult = $this->emailService->verifyCode($email, $code);
            if (!$verifyResult['success']) {
                $this->emailService->incrementAttempts($email, $code);
                $this->logLoginAttempt($userId, 'email', 'failed', $verifyResult['error']);
                return $verifyResult;
            }
            
            // Получаем пользователя
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Пользователь не найден'
                ];
            }
            
            // Обновляем время последнего входа
            $this->updateLastLogin($userId);
            
            // Создаем сессию пользователя
            $this->createUserSession($user);
            
            // Логируем успешный вход
            $this->logLoginAttempt($userId, 'email', 'success');
            
            // Очищаем временные данные сессии
            unset($_SESSION['login_email']);
            unset($_SESSION['login_user_id']);
            unset($_SESSION['login_attempt_time']);
            
            logMessage("Пользователь {$email} успешно авторизован", 'INFO');
            
            return [
                'success' => true,
                'message' => 'Авторизация успешна',
                'user' => $this->getUserData($user)
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка подтверждения входа: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => 'Ошибка подтверждения кода'
            ];
        }
    }
    
    /**
     * Найти или создать пользователя по email
     */
    private function findOrCreateUser($email) {
        try {
            // Ищем существующего пользователя
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                return $user;
            }
            
            // Создаем нового пользователя
            // ВАЖНО: явно указываем phone = NULL, чтобы избежать конфликта уникального индекса
            $stmt = $this->db->prepare("
                INSERT INTO users (email, phone, is_verified, created_at, updated_at) 
                VALUES (?, NULL, 1, NOW(), NOW())
            ");
            $result = $stmt->execute([$email]);
            
            if (!$result) {
                throw new Exception('Ошибка создания пользователя');
            }
            
            $userId = $this->db->lastInsertId();
            
            // Получаем созданного пользователя
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            logMessage("Создан новый пользователь с email {$email}, ID: {$userId}", 'INFO');
            
            return $user;
            
        } catch (Exception $e) {
            logMessage("Ошибка поиска/создания пользователя {$email}: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Получение пользователя по ID
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Ошибка получения пользователя по ID {$userId}: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Создание пользовательской сессии
     */
    private function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_authenticated'] = true;
        $_SESSION['login_time'] = time();
        
        // Регенерируем ID сессии для безопасности
        session_regenerate_id(true);
    }
    
    /**
     * Проверка аутентификации пользователя
     */
    public function isAuthenticated() {
        return isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated'] === true;
    }
    
    /**
     * Получение текущего пользователя
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->getUserById($_SESSION['user_id']);
    }
    
    /**
     * Выход пользователя
     */
    public function logout() {
        try {
            if (isset($_SESSION['user_email'])) {
                logMessage("Пользователь {$_SESSION['user_email']} вышел из системы", 'INFO');
            }
            
            // Очищаем сессию
            session_unset();
            session_destroy();
            
            return ['success' => true, 'message' => 'Выход выполнен'];
            
        } catch (Exception $e) {
            logMessage("Ошибка выхода: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => 'Ошибка выхода'];
        }
    }
    
    /**
     * Обновление профиля пользователя
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['name', 'email', 'phone', 'company_name', 'inn'];
            $updateFields = [];
            $updateValues = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $value = $data[$field];
                    
                    // Для phone: если пусто, устанавливаем NULL
                    if ($field === 'phone') {
                        if (empty($value) || trim($value) === '') {
                            $updateFields[] = "{$field} = NULL";
                            continue;
                        }
                    } else {
                        // Для остальных полей пропускаем пустые значения
                        if (empty($value)) {
                            continue;
                        }
                    }
                    
                    $updateFields[] = "{$field} = ?";
                    $updateValues[] = sanitizeInput($value);
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'error' => 'Нет данных для обновления'];
            }
            
            // Проверяем уникальность email если он указан и изменен
            if (isset($data['email']) && !empty($data['email'])) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $userId]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'error' => 'Пользователь с таким email уже существует'];
                }
            }
            
            // Проверяем уникальность phone если он указан и не пустой
            if (isset($data['phone']) && !empty($data['phone'])) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
                $stmt->execute([$data['phone'], $userId]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'error' => 'Пользователь с таким телефоном уже существует'];
                }
            }
            
            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);
            
            if (!$result) {
                throw new Exception('Ошибка обновления профиля');
            }
            
            // Обновляем данные в сессии
            if (isset($data['name'])) {
                $_SESSION['user_name'] = $data['name'];
            }
            if (isset($data['email'])) {
                $_SESSION['user_email'] = $data['email'];
            }
            if (isset($data['phone'])) {
                $_SESSION['user_phone'] = !empty($data['phone']) ? $data['phone'] : null;
            }
            
            logMessage("Обновлен профиль пользователя ID: {$userId}", 'INFO');
            
            return ['success' => true, 'message' => 'Профиль обновлен'];
            
        } catch (Exception $e) {
            logMessage("Ошибка обновления профиля пользователя {$userId}: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => 'Ошибка обновления профиля'];
        }
    }
    
    /**
     * Обновление времени последнего входа
     */
    private function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            logMessage("Ошибка обновления времени входа для пользователя {$userId}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Логирование попыток входа
     */
    private function logLoginAttempt($userId, $method, $status, $failureReason = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_history 
                (user_type, user_id, login_method, ip_address, user_agent, status, failure_reason, created_at) 
                VALUES ('user', ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $method,
                getUserIP(),
                getUserAgent(),
                $status,
                $failureReason
            ]);
            
        } catch (Exception $e) {
            logMessage("Ошибка логирования входа для пользователя {$userId}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Проверка блокировки IP адреса
     */
    private function isIPBlocked() {
        try {
            $ip = getUserIP();
            
            // Проверяем количество неудачных попыток за последние 15 минут
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as attempts 
                FROM login_history 
                WHERE ip_address = ? 
                AND status = 'failed' 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([$ip, LOGIN_BLOCK_TIME]);
            $result = $stmt->fetch();
            
            return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
            
        } catch (Exception $e) {
            logMessage("Ошибка проверки блокировки IP {$ip}: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Получение безопасных данных пользователя для фронтенда
     */
    private function getUserData($user) {
        return [
            'id' => $user['id'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'name' => $user['name'],
            'company' => $user['company_name'],
            'inn' => $user['inn'],
            'is_verified' => (bool) $user['is_verified'],
            'created_at' => $user['created_at']
        ];
    }
    
    /**
     * Получение статистики пользователей
     */
    public function getUserStats($period = 'today') {
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
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN is_verified = 1 THEN 1 END) as verified_users,
                    COUNT(CASE WHEN last_login_at IS NOT NULL THEN 1 END) as active_users
                FROM users 
                {$whereClause}
            ");
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            logMessage("Ошибка получения статистики пользователей: " . $e->getMessage(), 'ERROR');
            return [
                'total_users' => 0,
                'verified_users' => 0,
                'active_users' => 0
            ];
        }
    }
    
    /**
     * Поиск пользователей (для админки)
     */
    public function searchUsers($query, $limit = 20, $offset = 0) {
        try {
            $searchTerm = "%{$query}%";
            
            $stmt = $this->db->prepare("
                SELECT id, email, phone, name, company_name, is_verified, is_blocked, 
                       created_at, last_login_at
                FROM users 
                WHERE email LIKE ? OR phone LIKE ? OR name LIKE ? OR company_name LIKE ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            logMessage("Ошибка поиска пользователей: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Блокировка/разблокировка пользователя
     */
    public function toggleUserBlock($userId, $blocked = true) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_blocked = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$blocked ? 1 : 0, $userId]);
            
            if ($result) {
                $action = $blocked ? 'заблокирован' : 'разблокирован';
                logMessage("Пользователь ID {$userId} {$action}", 'INFO');
                return ['success' => true, 'message' => "Пользователь {$action}"];
            }
            
            return ['success' => false, 'error' => 'Ошибка изменения статуса пользователя'];
            
        } catch (Exception $e) {
            logMessage("Ошибка блокировки пользователя {$userId}: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => 'Ошибка изменения статуса пользователя'];
        }
    }
}
?>
