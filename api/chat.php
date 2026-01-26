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
require_once dirname(__DIR__) . '/classes/ChatService.php';
require_once dirname(__DIR__) . '/classes/TelegramNotifier.php';

// Определяем действие из URL параметров или пути
$action = '';

// Сначала проверяем URL параметр action
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    // Если нет параметра, пытаемся извлечь из пути
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $requestPath = str_replace(dirname($scriptName), '', $requestUri);
    $requestPath = trim($requestPath, '/');
    $requestPath = explode('?', $requestPath)[0];
    
    $pathParts = explode('/', $requestPath);
    $action = end($pathParts);
    $action = str_replace('.php', '', $action);
}

// Если action пустой или равен 'chat', то это получение сообщений для GET
if (empty($action) || $action === 'chat') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = 'messages';
    }
}

$userService = new UserService();
$chatService = new ChatService();

// Проверяем авторизацию для всех действий кроме публичных
$publicActions = []; // Все действия чата требуют авторизации
if (!in_array($action, $publicActions) && !$userService->isAuthenticated()) {
    sendJsonResponse(['success' => false, 'error' => 'Необходима авторизация'], 401);
}

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
    logMessage("Ошибка в API chat: " . $e->getMessage(), 'ERROR');
    sendJsonResponse(['success' => false, 'error' => 'Внутренняя ошибка сервера'], 500);
}

/**
 * Обработка POST запросов
 */
function handlePostRequest($action, $userService, $chatService) {
    switch ($action) {
        case 'send':
            handleSendMessage($userService, $chatService);
            break;
            
        case 'upload':
            handleFileUpload($userService, $chatService);
            break;
            
        case 'read':
            handleMarkAsRead($userService, $chatService);
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
        case 'messages':
            handleGetMessages($userService, $chatService);
            break;
            
        case 'info':
            handleGetChatInfo($userService, $chatService);
            break;
            
        default:
            sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 404);
    }
}

/**
 * Отправка сообщения
 */
function handleSendMessage($userService, $chatService) {
    // Применяем rate limiting для отправки сообщений
    checkRateLimit('send_message', 30, 60); // 30 сообщений в минуту
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'error' => 'Некорректные данные'], 400);
    }
    
    // Валидация данных
    $errors = validateMessageInput($input);
    if (!empty($errors)) {
        sendJsonResponse(['success' => false, 'error' => implode(', ', $errors)], 400);
    }
    
    $userId = $_SESSION['user_id'];
    $message = trim($input['message']);
    $chatId = isset($input['chat_id']) ? (int)$input['chat_id'] : null;
    $messageType = isset($input['type']) ? $input['type'] : 'text';
    $metadata = isset($input['metadata']) ? $input['metadata'] : null;
    
    // Если chat_id не указан, получаем/создаем чат пользователя
    if (!$chatId) {
        $chatResult = $chatService->getOrCreateUserChat($userId);
        if (!$chatResult['success']) {
            sendJsonResponse($chatResult);
        }
        $chatId = $chatResult['chat_id'];
    }
    
    logMessage("Отправка сообщения пользователем ID: {$userId} в чат ID: {$chatId}", 'INFO');
    
    // Отправляем сообщение
    $result = $chatService->sendUserMessage($chatId, $userId, $message, $messageType, $metadata);
    
    if ($result['success']) {
        logMessage("Сообщение успешно отправлено пользователем ID: {$userId} в чат ID: {$chatId}", 'INFO');
        
        // Добавляем информацию о чате и ID сообщения в ответ
        $result['chat_id'] = $chatId;
        
        // ВАЖНО: Возвращаем ID созданного сообщения
        if (isset($result['message_id'])) {
            $result['message_id'] = (int)$result['message_id'];
        }
    } else {
        logMessage("Ошибка отправки сообщения пользователем ID: {$userId}: " . $result['error'], 'WARNING');
    }
    
    sendJsonResponse($result);
}

/**
 * Получение сообщений чата
 */
function handleGetMessages($userService, $chatService) {
    $userId = $_SESSION['user_id'];
    $chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : null;
    $afterId = isset($_GET['after_id']) ? (int)$_GET['after_id'] : 0;
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50; // Максимум 100 сообщений
    
    // Если chat_id не указан, получаем чат пользователя
    if (!$chatId) {
        $chatResult = $chatService->getOrCreateUserChat($userId);
        if (!$chatResult['success']) {
            sendJsonResponse($chatResult);
        }
        $chatId = $chatResult['chat_id'];
    }
    
    logMessage("Запрос сообщений пользователем ID: {$userId} для чата ID: {$chatId}, after_id: {$afterId}", 'INFO');
    
    // Получаем сообщения
    $result = $chatService->getChatMessages($chatId, $userId, $afterId, $limit);
    
    if ($result['success']) {
        $messageCount = isset($result['messages']) ? count($result['messages']) : 0;
        logMessage("Получено {$messageCount} сообщений для пользователя ID: {$userId}", 'INFO');
        
        // Добавляем информацию о чате
        $result['chat_id'] = $chatId;
        
        // Убеждаемся, что каждое сообщение имеет числовой ID
        if (isset($result['messages']) && is_array($result['messages'])) {
            foreach ($result['messages'] as &$message) {
                if (isset($message['id'])) {
                    $message['id'] = (int)$message['id'];
                }
            }
        }
    } else {
        logMessage("Ошибка получения сообщений для пользователя ID: {$userId}: " . $result['error'], 'WARNING');
    }
    
    sendJsonResponse($result);
}

/**
 * Загрузка файла
 */
function handleFileUpload($userService, $chatService) {
    // Применяем rate limiting для загрузки файлов
    checkRateLimit('upload_file', 10, 300); // 10 файлов в 5 минут
    
    $userId = $_SESSION['user_id'];
    $chatId = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : null;
    
    // Проверяем наличие файла
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        sendJsonResponse(['success' => false, 'error' => 'Файл не загружен или произошла ошибка']);
    }
    
    // Если chat_id не указан, получаем чат пользователя
    if (!$chatId) {
        $chatResult = $chatService->getOrCreateUserChat($userId);
        if (!$chatResult['success']) {
            sendJsonResponse($chatResult);
        }
        $chatId = $chatResult['chat_id'];
    }
    
    $file = $_FILES['file'];
    
    logMessage("Загрузка файла '{$file['name']}' пользователем ID: {$userId} в чат ID: {$chatId}", 'INFO');
    
    // Загружаем файл
    $result = $chatService->uploadFile($chatId, $userId, $file);
    
    if ($result['success']) {
        logMessage("Файл '{$file['name']}' успешно загружен пользователем ID: {$userId}", 'INFO');
        
        // Добавляем информацию о чате
        $result['chat_id'] = $chatId;
    } else {
        logMessage("Ошибка загрузки файла '{$file['name']}' пользователем ID: {$userId}: " . $result['error'], 'WARNING');
    }
    
    sendJsonResponse($result);
}

/**
 * Отметка сообщений как прочитанные
 */
function handleMarkAsRead($userService, $chatService) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'error' => 'Некорректные данные'], 400);
    }
    
    $userId = $_SESSION['user_id'];
    $chatId = isset($input['chat_id']) ? (int)$input['chat_id'] : null;
    
    if (!$chatId) {
        sendJsonResponse(['success' => false, 'error' => 'Не указан ID чата']);
    }
    
    // Здесь мы просто обновляем время последнего чтения пользователя
    // Логика отметки сообщений как прочитанных уже реализована в getChatMessages
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE chats SET last_user_read_at = NOW() WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$chatId, $userId]);
        
        if ($result) {
            logMessage("Сообщения отмечены как прочитанные пользователем ID: {$userId} в чате ID: {$chatId}", 'INFO');
            sendJsonResponse(['success' => true, 'message' => 'Сообщения отмечены как прочитанные']);
        } else {
            sendJsonResponse(['success' => false, 'error' => 'Ошибка обновления статуса прочтения']);
        }
    } catch (Exception $e) {
        logMessage("Ошибка отметки сообщений как прочитанные: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка обновления статуса прочтения']);
    }
}

/**
 * Получение информации о чате
 */
function handleGetChatInfo($userService, $chatService) {
    $userId = $_SESSION['user_id'];
    $chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : null;
    
    // Если chat_id не указан, получаем чат пользователя
    if (!$chatId) {
        $chatResult = $chatService->getOrCreateUserChat($userId);
        if (!$chatResult['success']) {
            sendJsonResponse($chatResult);
        }
        $chatId = $chatResult['chat_id'];
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Получаем информацию о чате
        $stmt = $db->prepare("
            SELECT 
                c.id,
                c.status,
                c.admin_id,
                c.unread_user_count,
                c.last_message_at,
                c.created_at,
                a.full_name as admin_name
            FROM chats c
            LEFT JOIN admins a ON c.admin_id = a.id
            WHERE c.id = ? AND c.user_id = ?
        ");
        $stmt->execute([$chatId, $userId]);
        $chat = $stmt->fetch();
        
        if (!$chat) {
            sendJsonResponse(['success' => false, 'error' => 'Чат не найден']);
        }
        
        // Получаем статистику сообщений
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_messages,
                COUNT(CASE WHEN sender_type = 'user' THEN 1 END) as user_messages,
                COUNT(CASE WHEN sender_type = 'admin' THEN 1 END) as admin_messages,
                COUNT(CASE WHEN sender_type = 'system' THEN 1 END) as system_messages
            FROM messages 
            WHERE chat_id = ?
        ");
        $stmt->execute([$chatId]);
        $stats = $stmt->fetch();
        
        $chatInfo = [
            'id' => $chat['id'],
            'status' => $chat['status'],
            'admin_id' => $chat['admin_id'],
            'admin_name' => $chat['admin_name'],
            'unread_count' => $chat['unread_user_count'],
            'last_message_at' => $chat['last_message_at'],
            'created_at' => $chat['created_at'],
            'stats' => $stats
        ];
        
        sendJsonResponse([
            'success' => true,
            'chat' => $chatInfo
        ]);
        
    } catch (Exception $e) {
        logMessage("Ошибка получения информации о чате {$chatId}: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка получения информации о чате']);
    }
}

/**
 * Валидация данных сообщения
 */
function validateMessageInput($input) {
    $errors = [];
    
    // Проверяем наличие текста сообщения
    if (!isset($input['message']) || empty(trim($input['message']))) {
        $errors[] = 'Текст сообщения не может быть пустым';
    } else {
        $message = trim($input['message']);
        
        // Проверяем длину сообщения
        if (mb_strlen($message) > 5000) {
            $errors[] = 'Сообщение слишком длинное (максимум 5000 символов)';
        }
        
        if (mb_strlen($message) < 1) {
            $errors[] = 'Сообщение не может быть пустым';
        }
    }
    
    // Проверяем тип сообщения
    if (isset($input['type'])) {
        $allowedTypes = ['text', 'order_link', 'system'];
        if (!in_array($input['type'], $allowedTypes)) {
            $errors[] = 'Недопустимый тип сообщения';
        }
    }
    
    // Проверяем chat_id если указан
    if (isset($input['chat_id'])) {
        if (!is_numeric($input['chat_id']) || $input['chat_id'] <= 0) {
            $errors[] = 'Некорректный ID чата';
        }
    }
    
    return $errors;
}

/**
 * Проверка rate limiting
 */
function checkRateLimit($action, $limit = 10, $window = 3600) {
    $userId = $_SESSION['user_id'] ?? 'anonymous';
    $ip = getUserIP();
    $key = "rate_limit_{$action}_{$userId}_{$ip}";
    
    // Простая реализация rate limiting через файлы
    $rateFile = sys_get_temp_dir() . "/{$key}.json";
    
    $now = time();
    $data = ['count' => 1, 'window_start' => $now];
    
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
        logMessage("Rate limit exceeded for user {$userId}, IP {$ip}, action {$action}", 'WARNING');
        sendJsonResponse([
            'success' => false, 
            'error' => 'Превышен лимит запросов. Попробуйте позже.'
        ], 429);
    }
    
    return true;
}

/**
 * Получение безопасной информации о файле
 */
function getFileInfo($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    
    $fileInfo = pathinfo($filePath);
    $mimeType = getMimeType($filePath);
    
    return [
        'name' => $fileInfo['basename'],
        'size' => filesize($filePath),
        'type' => $mimeType,
        'extension' => $fileInfo['extension'] ?? ''
    ];
}

/**
 * Очистка временных файлов rate limiting
 */
function cleanupRateLimitFiles() {
    $tempDir = sys_get_temp_dir();
    $files = glob($tempDir . '/rate_limit_*.json');
    $now = time();
    $cleaned = 0;
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && ($now - $data['window_start']) > 7200) { // 2 часа
            unlink($file);
            $cleaned++;
        }
    }
    
    if ($cleaned > 0) {
        logMessage("Очищено {$cleaned} файлов rate limiting", 'INFO');
    }
}

/**
 * Проверка безопасности файла
 */
function isSecureFile($file) {
    // Проверяем размер
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Проверяем MIME тип
    $mimeType = getMimeType($file['tmp_name']);
    if (!isAllowedFileType($mimeType)) {
        return false;
    }
    
    // Проверяем расширение файла
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'rtf', 'zip', 'rar'
    ];
    
    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }
    
    // Дополнительные проверки безопасности
    $dangerousExtensions = ['php', 'html', 'js', 'exe', 'bat', 'cmd', 'scr'];
    if (in_array($extension, $dangerousExtensions)) {
        return false;
    }
    
    return true;
}

/**
 * Логирование действий в чате
 */
function logChatAction($action, $userId, $chatId, $details = '') {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user_id' => $userId,
        'chat_id' => $chatId,
        'ip' => getUserIP(),
        'details' => $details
    ];
    
    logMessage("Chat Action: " . json_encode($logData, JSON_UNESCAPED_UNICODE), 'INFO');
}

// Периодическая очистка временных файлов (выполняется случайно в 1% случаев)
if (rand(1, 100) === 1) {
    cleanupRateLimitFiles();
}
?>
