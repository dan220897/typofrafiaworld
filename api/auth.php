<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/classes/UserService.php';
require_once dirname(__DIR__) . '/classes/EmailService.php';
require_once dirname(__DIR__) . '/classes/ChatService.php';

// Определяем действие из URL параметров, JSON body или пути
$action = '';

// Сначала проверяем URL параметр action
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    // Проверяем JSON body для POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        $jsonInput = json_decode($rawInput, true);
        if ($jsonInput && isset($jsonInput['action'])) {
            $action = $jsonInput['action'];
        }
    }

    // Если action все еще пустой, пытаемся извлечь из пути
    if (empty($action)) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestPath = str_replace(dirname($scriptName), '', $requestUri);
        $requestPath = trim($requestPath, '/');
        $requestPath = explode('?', $requestPath)[0];

        $pathParts = explode('/', $requestPath);
        $action = end($pathParts);
        $action = str_replace('.php', '', $action);
    }
}

// Если action пустой или равен 'auth', то это проверка авторизации для GET
if (empty($action) || $action === 'auth') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = 'check';
    }
}

$userService = new UserService();
$chatService = new ChatService();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            handlePostRequest($action, $userService, $chatService);
            break;
        case 'GET':
            handleGetRequest($action, $userService, $chatService);
            break;
        default:
            sendJsonResponse(['success' => false, 'error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    logMessage("Ошибка в API auth: " . $e->getMessage(), 'ERROR');
    sendJsonResponse(['success' => false, 'error' => 'Внутренняя ошибка сервера'], 500);
}

/**
 * Обработка POST запросов
 */
function handlePostRequest($action, $userService, $chatService) {
    // Для logout не требуются входные данные
    if ($action === 'logout') {
        handleLogout($userService);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'error' => 'Некорректные данные'], 400);
    }
    
    switch ($action) {
        case 'send_code':
        case 'send-code':
            handleSendCode($input, $userService);
            break;

        case 'verify_code':
        case 'verify-code':
            handleVerifyCode($input, $userService, $chatService);
            break;

        case 'update_profile':
        case 'update-profile':
            handleUpdateProfile($input, $userService);
            break;
            
        default:
            sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 404);
    }
}

/**
 * Обработка GET запросов
 */
function handleGetRequest($action, $userService, $chatService) {
    switch ($action) {
        case 'check':
            handleCheckAuth($userService, $chatService);
            break;
            
        default:
            sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 404);
    }
}

/**
 * Отправка Email кода
 */
function handleSendCode($input, $userService) {
    // Валидация входных данных
    if (!isset($input['email']) || empty($input['email'])) {
        sendJsonResponse(['success' => false, 'error' => 'Email адрес обязателен']);
    }
    
    $email = trim($input['email']);
    
    // Валидация email адреса
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse([
            'success' => false, 
            'error' => 'Некорректный email адрес'
        ]);
    }
    
    // Логируем попытку отправки
    logMessage("Запрос отправки кода на email: {$email} с IP: " . getUserIP(), 'INFO');
    
    // Отправляем код
    $result = $userService->authenticateByEmail($email);
    
    if ($result['success']) {
        logMessage("Email код успешно отправлен на адрес: {$email}", 'INFO');
    } else {
        logMessage("Ошибка отправки Email кода на адрес {$email}: " . $result['error'], 'WARNING');
    }
    
    sendJsonResponse($result);
}

/**
 * Проверка Email кода и авторизация
 */
function handleVerifyCode($input, $userService, $chatService) {
    // Валидация входных данных
    if (!isset($input['code']) || empty($input['code'])) {
        sendJsonResponse(['success' => false, 'error' => 'Код подтверждения обязателен']);
    }
    
    $code = preg_replace('/\D/', '', $input['code']); // Оставляем только цифры
    
    if (strlen($code) !== 6) {
        sendJsonResponse(['success' => false, 'error' => 'Код должен содержать 6 цифр']);
    }
    
    // Проверяем данные в сессии
    if (!isset($_SESSION['login_email']) || !isset($_SESSION['login_user_id'])) {
        sendJsonResponse([
            'success' => false, 
            'error' => 'Сессия истекла. Запросите код повторно.'
        ]);
    }
    
    $email = $_SESSION['login_email'];
    
    logMessage("Попытка верификации кода для email: {$email} с IP: " . getUserIP(), 'INFO');
    
    // Проверяем код и авторизуем пользователя
    $result = $userService->verifyLogin($code);
    
    if ($result['success']) {
        // Получаем или создаем чат для пользователя
        $chatResult = $chatService->getOrCreateUserChat($_SESSION['user_id']);
        
        if ($chatResult['success']) {
            $result['chat_id'] = $chatResult['chat_id'];
            
            logMessage("Пользователь {$email} успешно авторизован, чат ID: {$chatResult['chat_id']}", 'INFO');
        } else {
            logMessage("Ошибка создания чата для пользователя {$email}: " . $chatResult['error'], 'WARNING');
            // Не прерываем авторизацию из-за ошибки чата
            $result['chat_id'] = null;
        }
    } else {
        logMessage("Ошибка верификации кода для email {$email}: " . $result['error'], 'WARNING');
    }
    
    sendJsonResponse($result);
}

/**
 * Проверка статуса авторизации
 */
function handleCheckAuth($userService, $chatService) {
    if (!$userService->isAuthenticated()) {
        sendJsonResponse([
            'success' => true,
            'authenticated' => false
        ]);
    }
    
    $user = $userService->getCurrentUser();
    if (!$user) {
        // Очищаем некорректную сессию
        $userService->logout();
        sendJsonResponse([
            'success' => true,
            'authenticated' => false
        ]);
    }
    
    // Получаем чат пользователя
    $chatResult = $chatService->getOrCreateUserChat($user['id']);
    $chatId = $chatResult['success'] ? $chatResult['chat_id'] : null;
    
    $userData = [
        'id' => $user['id'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'name' => $user['name'],
        'company' => $user['company_name'],
        'inn' => $user['inn'],
        'is_verified' => (bool) $user['is_verified'],
        'created_at' => $user['created_at']
    ];
    
    sendJsonResponse([
        'success' => true,
        'authenticated' => true,
        'user' => $userData,
        'chat_id' => $chatId
    ]);
}

/**
 * Обновление профиля пользователя
 */
function handleUpdateProfile($input, $userService) {
    // Проверяем авторизацию
    if (!$userService->isAuthenticated()) {
        sendJsonResponse(['success' => false, 'error' => 'Необходима авторизация'], 401);
    }
    
    $userId = $_SESSION['user_id'];
    
    // Валидация данных - добавлено поле phone
    $allowedFields = ['name', 'email', 'phone', 'company', 'inn'];
    $updateData = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $value = trim($input[$field]);
            
            // Пропускаем пустые значения
            if (empty($value)) {
                continue;
            }
            
            // Дополнительная валидация
            switch ($field) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        sendJsonResponse(['success' => false, 'error' => 'Некорректный email адрес']);
                    }
                    break;
                
                case 'phone':
                    $validPhone = validatePhone($value);
                    if (!$validPhone) {
                        sendJsonResponse(['success' => false, 'error' => 'Некорректный номер телефона']);
                    }
                    $value = $validPhone; // Используем отформатированный телефон
                    break;
                    
                case 'inn':
                    if (!preg_match('/^\d{10}$|^\d{12}$/', $value)) {
                        sendJsonResponse(['success' => false, 'error' => 'ИНН должен содержать 10 или 12 цифр']);
                    }
                    break;
                    
                case 'name':
                    if (mb_strlen($value) > 255) {
                        sendJsonResponse(['success' => false, 'error' => 'Имя слишком длинное']);
                    }
                    break;
                    
                case 'company':
                    if (mb_strlen($value) > 255) {
                        sendJsonResponse(['success' => false, 'error' => 'Название компании слишком длинное']);
                    }
                    break;
            }
            
            // Сохраняем корректное имя поля для БД
            $dbFieldName = $field === 'company' ? 'company_name' : $field;
            $updateData[$dbFieldName] = $value;
        }
    }
    
    if (empty($updateData)) {
        sendJsonResponse(['success' => false, 'error' => 'Нет данных для обновления']);
    }
    
    logMessage("Обновление профиля пользователя ID: {$userId}", 'INFO');
    
    // Обновляем профиль
    $result = $userService->updateProfile($userId, $updateData);
    
    if ($result['success']) {
        logMessage("Профиль пользователя ID: {$userId} успешно обновлен", 'INFO');
    } else {
        logMessage("Ошибка обновления профиля пользователя ID: {$userId}: " . $result['error'], 'WARNING');
    }
    
    sendJsonResponse($result);
}

/**
 * Выход из системы
 */
function handleLogout($userService) {
    $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'unknown';
    
    logMessage("Выход из системы пользователя: {$email} с IP: " . getUserIP(), 'INFO');
    
    $result = $userService->logout();
    sendJsonResponse($result);
}

/**
 * Валидация входящих данных
 */
function validateInput($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = isset($data[$field]) ? $data[$field] : null;
        
        // Проверка обязательности
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = "Поле {$field} обязательно для заполнения";
            continue;
        }
        
        // Если поле пустое и не обязательное, пропускаем остальные проверки
        if (empty($value)) {
            continue;
        }
        
        // Проверка типа
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "Некорректный email адрес";
                    }
                    break;
                    
                case 'phone':
                    if (!validatePhone($value)) {
                        $errors[$field] = "Некорректный номер телефона";
                    }
                    break;
                    
                case 'string':
                    if (!is_string($value)) {
                        $errors[$field] = "Поле {$field} должно быть строкой";
                    }
                    break;
            }
        }
        
        // Проверка длины
        if (isset($rule['max_length']) && mb_strlen($value) > $rule['max_length']) {
            $errors[$field] = "Поле {$field} не может быть длиннее {$rule['max_length']} символов";
        }
        
        if (isset($rule['min_length']) && mb_strlen($value) < $rule['min_length']) {
            $errors[$field] = "Поле {$field} не может быть короче {$rule['min_length']} символов";
        }
        
        // Проверка паттерна
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
            $errors[$field] = isset($rule['pattern_message']) ? 
                $rule['pattern_message'] : 
                "Поле {$field} имеет некорректный формат";
        }
    }
    
    return $errors;
}

/**
 * Логирование API запросов
 */
function logApiRequest($action, $success = true, $error = null) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'method' => $_SERVER['REQUEST_METHOD'],
        'ip' => getUserIP(),
        'user_agent' => getUserAgent(),
        'success' => $success
    ];
    
    if ($error) {
        $logData['error'] = $error;
    }
    
    if (isset($_SESSION['user_id'])) {
        $logData['user_id'] = $_SESSION['user_id'];
    }
    
    logMessage("API Request: " . json_encode($logData, JSON_UNESCAPED_UNICODE), 'INFO');
}

/**
 * Проверка rate limiting (защита от спама)
 */
function checkRateLimit($action, $limit = 10, $window = 3600) {
    $ip = getUserIP();
    $key = "rate_limit_{$action}_{$ip}";
    
    // Простая реализация без Redis - используем файловое хранение
    $rateFile = sys_get_temp_dir() . "/{$key}.json";
    
    $now = time();
    $data = ['count' => 0, 'window_start' => $now];
    
    if (file_exists($rateFile)) {
        $existing = json_decode(file_get_contents($rateFile), true);
        if ($existing) {
            // Если прошло больше времени чем окно, сбрасываем счетчик
            if ($now - $existing['window_start'] > $window) {
                $data = ['count' => 1, 'window_start' => $now];
            } else {
                $data = $existing;
                $data['count']++;
            }
        }
    }
    
    // Сохраняем обновленные данные
    file_put_contents($rateFile, json_encode($data));
    
    // Проверяем лимит
    if ($data['count'] > $limit) {
        logMessage("Rate limit exceeded for IP {$ip}, action {$action}", 'WARNING');
        sendJsonResponse([
            'success' => false, 
            'error' => 'Превышен лимит запросов. Попробуйте позже.'
        ], 429);
    }
    
    return true;
}

// Применяем rate limiting для критичных действий
if (in_array($action, ['send-code', 'verify-code'])) {
    checkRateLimit($action, 20, 300); // 5 запросов в 5 минут
}
?>