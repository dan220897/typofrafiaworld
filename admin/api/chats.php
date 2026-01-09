<?php
// admin/api/chats.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Функция для логирования
function logError($message, $context = []) {
    $logDir = __DIR__ . '/../logs/';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . 'api_errors_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $logEntry = "[{$timestamp}] {$message} {$contextStr}\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Обработчик фатальных ошибок
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        logError("Fatal error: " . $error['message'], $error);
        
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Внутренняя ошибка сервера'
        ]);
    }
});

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../classes/Chat.php';
    require_once __DIR__ . '/../classes/User.php';
    
    if (file_exists(__DIR__ . '/../classes/TelegramNotifier.php')) {
        require_once __DIR__ . '/../classes/TelegramNotifier.php';
    }

    // Проверка авторизации
    if (!function_exists('checkAuth')) {
        function checkAuth() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['admin_id'])) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Не авторизован']);
                exit;
            }
        }
    }

    checkAuth();

    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Ошибка подключения к базе данных');
    }
    
    $chat = new Chat($db);
    $user = new User($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    logError("API request: $method $action", [
        'GET' => $_GET,
        'POST' => !empty($_POST) ? array_keys($_POST) : [],
        'FILES' => !empty($_FILES) ? array_keys($_FILES) : []
    ]);

    switch ($method) {
        case 'GET':
            handleGetRequest($chat, $user, $action);
            break;
            
        case 'POST':
            handlePostRequest($chat, $action, $db);
            break;
            
        case 'PUT':
            handlePutRequest($chat, $action);
            break;
            
        case 'DELETE':
            handleDeleteRequest($chat);
            break;
            
        default:
            throw new Exception('Метод не поддерживается');
    }

} catch (Exception $e) {
    logError("API Exception: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Обработка GET запросов
 */
function handleGetRequest($chat, $user, $action) {
    // НОВОЕ: Получение данных чата для AJAX
    if ($action === 'get_chat') {
        $chat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$chat_id) {
            throw new Exception('ID чата не указан');
        }
        
        // Получаем данные чата
        $current_chat = $chat->getChatById($chat_id);
        
        if (!$current_chat) {
            throw new Exception('Чат не найден');
        }
        
        // Получаем сообщения
        $messages = $chat->getMessages($chat_id);
        
        // Получаем данные пользователя
        $chat_user = $user->getUserById($current_chat['user_id']);
        
        // Отмечаем сообщения как прочитанные
        $chat->markAsRead($chat_id, 'admin');
        
        echo json_encode([
            'success' => true,
            'chat' => $current_chat,
            'messages' => $messages ? $messages : [],
            'user' => $chat_user ? $chat_user : []
        ]);
        return;
    }
    
    // Получение новых сообщений для автообновления
    if ($action === 'new_messages') {
        $chat_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;
        $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
        
        if (!$chat_id) {
            throw new Exception('ID чата не указан');
        }
        
        $messages = $chat->getNewMessages($chat_id, $last_id);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages ? $messages : []
        ]);
        return;
    }
    
    // Поиск по чатам
    if ($action === 'search') {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        if (strlen($query) < 2) {
            echo json_encode([
                'success' => true,
                'chats' => []
            ]);
            return;
        }
        
        $chats = $chat->searchChats($query, 20);
        
        echo json_encode([
            'success' => true,
            'chats' => $chats ? $chats : []
        ]);
        return;
    }
    
    // Статистика чатов
    if ($action === 'stats') {
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $stats = $chat->getChatStats($user_id);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats ? $stats : []
        ]);
        return;
    }
    
    // Получение конкретного чата (старый метод, для совместимости)
    if (isset($_GET['id'])) {
        $chat_id = intval($_GET['id']);
        $messages = $chat->getMessages($chat_id);
        $chat_info = $chat->getChatById($chat_id);
        
        if (!$chat_info) {
            throw new Exception('Чат не найден');
        }
        
        $chat->markAsRead($chat_id, 'admin');
        
        echo json_encode([
            'success' => true,
            'chat' => $chat_info,
            'messages' => $messages ? $messages : []
        ]);
        return;
    }
    
    // Список всех чатов
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $admin_id = isset($_GET['my']) ? $_SESSION['admin_id'] : null;
    
    // Валидация параметров
    if ($limit > 100) $limit = 100;
    if ($limit < 1) $limit = 50;
    if ($offset < 0) $offset = 0;
    
    $chats = $chat->getChats($admin_id, $status, $limit, $offset);
    
    echo json_encode([
        'success' => true,
        'chats' => $chats ? $chats : []
    ]);
}

/**
 * Обработка POST запросов - отправка сообщений
 */
function handlePostRequest($chat, $action, $db) {
    // Индикатор печати
    if ($action === 'typing') {
        $data = json_decode(file_get_contents('php://input'), true);
        $chat_id = isset($data['chat_id']) ? intval($data['chat_id']) : 0;
        $is_typing = isset($data['is_typing']) ? (bool)$data['is_typing'] : false;
        
        // Здесь можно сохранить статус печати в кеш/сессию для отображения другим пользователям
        
        echo json_encode(['success' => true]);
        return;
    }
    
    // Отправка сообщения
    $chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
    $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (!$chat_id) {
        throw new Exception('ID чата не указан');
    }
    
    // Проверяем, что есть либо текст, либо файлы
    if (empty($message_text) && empty($_FILES['files'])) {
        throw new Exception('Сообщение не может быть пустым');
    }
    
    // Валидация длины сообщения
    if (strlen($message_text) > 4096) {
        throw new Exception('Сообщение слишком длинное (максимум 4096 символов)');
    }
    
    $message_id = null;
    
    // Отправляем текстовое сообщение
    if ($message_text) {
        $message_id = $chat->sendMessage(
            $chat_id,
            'admin',
            $_SESSION['admin_id'],
            $message_text,
            'text'
        );
        
        if (!$message_id) {
            throw new Exception('Ошибка отправки сообщения');
        }
        
        logError("Text message sent", [
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ]);
    }
    
    // Обработка файлов
    if (!empty($_FILES['files']['name'])) {
        $uploaded_files = processFileUploads($chat, $chat_id, $message_id);
        
        // Если файлы были загружены, но нет текста, создаем сообщение
        if (empty($message_id) && !empty($uploaded_files)) {
            $file_names = array_column($uploaded_files, 'name');
            $file_list = implode(', ', array_slice($file_names, 0, 3));
            if (count($file_names) > 3) {
                $file_list .= ' и ещё ' . (count($file_names) - 3);
            }
            
            $message_id = $chat->sendMessage(
                $chat_id,
                'admin',
                $_SESSION['admin_id'],
                'Файлы: ' . $file_list,
                'file'
            );
            
            // Привязываем файлы к новому сообщению
            foreach ($uploaded_files as $file) {
                $chat->attachFile(
                    $message_id,
                    $file['name'],
                    $file['path'],
                    $file['size'],
                    $file['type']
                );
            }
        }
    }
    
    if (!$message_id) {
        throw new Exception('Ошибка отправки сообщения');
    }
    
    // Получаем созданное сообщение со всеми данными
    $new_message = $chat->getMessageById($message_id);
    
    // Отправка уведомления в Telegram (если настроено)
    if (class_exists('TelegramNotifier')) {
        try {
            $telegram = new TelegramNotifier();
            $chat_info = $chat->getChatById($chat_id);
            if ($chat_info && !empty($chat_info['telegram_chat_id'])) {
                $telegram->sendMessage(
                    $chat_info['telegram_chat_id'],
                    "💬 Новое сообщение от администратора:\n\n" . $message_text
                );
            }
        } catch (Exception $e) {
            logError("Telegram notification failed", ['error' => $e->getMessage()]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message_id' => $message_id,
        'message' => $new_message
    ]);
}

/**
 * Обработка загрузки файлов
 */
function processFileUploads($chat, $chat_id, $message_id = null) {
    $uploaded_files = [];
    
    // Разрешенные типы файлов
    $allowed_types = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
        'application/x-rar-compressed'
    ];
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
    
    // Базовая директория для загрузок
    $base_upload_dir = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/../../uploads';
    $upload_dir = $base_upload_dir . '/chat_files/' . date('Y/m') . '/';
    
    // Создаем директорию если не существует
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Не удалось создать директорию для загрузки');
        }
    }
    
    // Проверяем права на запись
    if (!is_writable($upload_dir)) {
        throw new Exception('Директория не доступна для записи');
    }
    
    // Обрабатываем каждый файл
    $files_count = count($_FILES['files']['name']);
    
    for ($i = 0; $i < $files_count; $i++) {
        // Проверяем ошибки загрузки
        if (!isset($_FILES['files']['error'][$i]) || $_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
            $error_code = isset($_FILES['files']['error'][$i]) ? $_FILES['files']['error'][$i] : 'unknown';
            logError("File upload error", [
                'index' => $i,
                'error' => $error_code,
                'name' => $_FILES['files']['name'][$i] ?? 'unknown'
            ]);
            continue;
        }
        
        $tmp_name = $_FILES['files']['tmp_name'][$i];
        $file_name = $_FILES['files']['name'][$i];
        $file_size = $_FILES['files']['size'][$i];
        $file_type = $_FILES['files']['type'][$i];
        
        // Проверка размера файла (10 MB)
        if ($file_size > 10 * 1024 * 1024) {
            logError("File too large", ['file' => $file_name, 'size' => $file_size]);
            throw new Exception("Файл {$file_name} слишком большой (максимум 10 МБ)");
        }
        
        // Проверка расширения
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Недопустимый тип файла: {$file_extension}");
        }
        
        // Проверка MIME-типа
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_type = finfo_file($finfo, $tmp_name);
        finfo_close($finfo);
        
        if (!in_array($detected_type, $allowed_types)) {
            logError("Invalid MIME type", [
                'file' => $file_name,
                'declared' => $file_type,
                'detected' => $detected_type
            ]);
            throw new Exception("Недопустимый тип файла");
        }
        
        // Генерируем безопасное имя файла
        $safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file_name, PATHINFO_FILENAME));
        $safe_filename = substr($safe_filename, 0, 100); // Ограничиваем длину
        $unique_name = $safe_filename . '_' . uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $unique_name;
        
        // Перемещаем файл
        if (move_uploaded_file($tmp_name, $file_path)) {
            // Устанавливаем права доступа
            chmod($file_path, 0644);
            
            // Путь относительно корня сайта
            $relative_path = '/uploads/chat_files/' . date('Y/m') . '/' . $unique_name;
            
            $uploaded_files[] = [
                'name' => $file_name,
                'path' => $relative_path,
                'size' => $file_size,
                'type' => $detected_type
            ];
            
            // Если есть message_id, сразу прикрепляем файл
            if ($message_id) {
                $chat->attachFile(
                    $message_id,
                    $file_name,
                    $relative_path,
                    $file_size,
                    $detected_type
                );
            }
            
            logError("File uploaded successfully", [
                'original' => $file_name,
                'saved_as' => $unique_name,
                'size' => $file_size
            ]);
        } else {
            logError("Failed to move uploaded file", [
                'from' => $tmp_name,
                'to' => $file_path,
                'dir_writable' => is_writable($upload_dir),
                'dir_exists' => file_exists($upload_dir)
            ]);
            throw new Exception("Ошибка сохранения файла {$file_name}");
        }
    }
    
    return $uploaded_files;
}

/**
 * Обработка PUT запросов
 */
function handlePutRequest($chat, $action) {
    $chat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$chat_id) {
        throw new Exception('ID чата не указан');
    }
    
    switch ($action) {
        case 'assign':
            $data = json_decode(file_get_contents('php://input'), true);
            $admin_id = isset($data['admin_id']) ? intval($data['admin_id']) : $_SESSION['admin_id'];
            
            $result = $chat->assignAdmin($chat_id, $admin_id);
            
            echo json_encode(['success' => (bool)$result]);
            break;
            
        case 'read':
            $result = $chat->markAsRead($chat_id, 'admin');
            
            echo json_encode(['success' => (bool)$result]);
            break;
            
        case 'close':
            $result = $chat->updateStatus($chat_id, 'closed');
            
            if ($result) {
                $chat->sendMessage(
                    $chat_id,
                    'system',
                    null,
                    'Чат закрыт администратором',
                    'system'
                );
            }
            
            echo json_encode(['success' => (bool)$result]);
            break;

        // В функции handlePutRequest добавьте новый case:

case 'change_status':
    $data = json_decode(file_get_contents('php://input'), true);
    $client_status = isset($data['client_status']) ? $data['client_status'] : '';
    
    $allowed_statuses = ['new', 'in_progress', 'waiting_client', 'no_response', 'resolved'];
    if (!in_array($client_status, $allowed_statuses)) {
        throw new Exception('Недопустимый статус');
    }
    
    $result = $chat->updateClientStatus($chat_id, $client_status);
    
    echo json_encode(['success' => (bool)$result]);
    break;
            
        case 'reopen':
            $result = $chat->updateStatus($chat_id, 'active');
            
            if ($result) {
                $chat->sendMessage(
                    $chat_id,
                    'system',
                    null,
                    'Чат открыт администратором',
                    'system'
                );
            }
            
            echo json_encode(['success' => (bool)$result]);
            break;
            
        case 'status':
            $data = json_decode(file_get_contents('php://input'), true);
            $status = isset($data['status']) ? $data['status'] : '';
            
            if (!in_array($status, ['active', 'closed', 'archived'])) {
                throw new Exception('Недопустимый статус');
            }
            
            $result = $chat->updateStatus($chat_id, $status);
            
            echo json_encode(['success' => (bool)$result]);
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
}

/**
 * Обработка DELETE запросов
 */

/**
 * Обработка DELETE запросов - удаление чата
 */
function handleDeleteRequest($chat) {
    $chat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    logError("Delete chat request received", [
        'chat_id' => $chat_id,
        'admin_id' => $_SESSION['admin_id'] ?? 'not set',
        'admin_role' => $_SESSION['admin_role'] ?? 'not set'
    ]);
    
    if (!$chat_id) {
        throw new Exception('ID чата не указан');
    }
    
    // УБРАНА ПРОВЕРКА РОЛИ - теперь любой авторизованный админ может удалять
    // Если хотите оставить проверку роли, раскомментируйте:
    /*
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super_admin') {
        throw new Exception('Недостаточно прав для удаления чата');
    }
    */
    
    // Проверяем существование чата
    $chat_info = $chat->getChatById($chat_id);
    if (!$chat_info) {
        logError("Chat not found", ['chat_id' => $chat_id]);
        throw new Exception('Чат не найден');
    }
    
    logError("Chat found, attempting delete", ['chat_id' => $chat_id]);
    
    // Удаляем чат
    $result = $chat->deleteChat($chat_id);
    
    logError("Delete operation completed", [
        'chat_id' => $chat_id,
        'result' => $result ? 'success' : 'failed'
    ]);
    
    if (!$result) {
        throw new Exception('Ошибка при удалении чата из базы данных');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Чат успешно удален'
    ]);
}