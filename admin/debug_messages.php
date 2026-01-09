<?php
// admin/full_debug.php - Полная диагностика
session_start();
$_SESSION['admin_id'] = 1; // Временно для тестирования

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Chat.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== ПОЛНАЯ ДИАГНОСТИКА ЧАТОВ И СООБЩЕНИЙ ===\n\n";
    
    // 1. Проверяем все чаты
    echo "1. Список всех чатов:\n";
    $query = "SELECT id, user_id, status, created_at FROM chats ORDER BY id";
    $stmt = $db->query($query);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($chats)) {
        echo "   ❌ Чатов в базе данных НЕТ!\n\n";
        
        // Создаем тестовый чат
        echo "2. Создаем тестовый чат...\n";
        
        // Сначала проверим пользователей
        $user_query = "SELECT id, name, phone FROM users LIMIT 3";
        $user_stmt = $db->query($user_query);
        $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($users)) {
            echo "   ❌ Пользователей в базе данных НЕТ!\n";
            echo "   Создаем тестового пользователя...\n";
            
            $create_user = "INSERT INTO users (phone, name, created_at) VALUES ('+79991234567', 'Тестовый пользователь', NOW())";
            $db->exec($create_user);
            $user_id = $db->lastInsertId();
            echo "   ✓ Создан пользователь с ID: $user_id\n";
        } else {
            $user_id = $users[0]['id'];
            echo "   ✓ Используем существующего пользователя ID: $user_id\n";
        }
        
        // Создаем чат
        $create_chat = "INSERT INTO chats (user_id, status, created_at) VALUES (:user_id, 'active', NOW())";
        $stmt = $db->prepare($create_chat);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $chat_id = $db->lastInsertId();
        echo "   ✓ Создан чат с ID: $chat_id\n";
        
        // Создаем тестовые сообщения
        echo "   Создаем тестовые сообщения...\n";
        
        $messages = [
            ['sender_type' => 'user', 'sender_id' => $user_id, 'text' => 'Привет! Мне нужна помощь с заказом'],
            ['sender_type' => 'admin', 'sender_id' => 1, 'text' => 'Здравствуйте! Конечно, помогу вам с заказом'],
            ['sender_type' => 'user', 'sender_id' => $user_id, 'text' => 'Спасибо! Хочу заказать визитки'],
            ['sender_type' => 'admin', 'sender_id' => 1, 'text' => 'Отлично! Сколько визиток вам нужно?']
        ];
        
        foreach ($messages as $msg) {
            $insert_msg = "INSERT INTO messages (chat_id, sender_type, sender_id, message_text, message_type, created_at) 
                          VALUES (:chat_id, :sender_type, :sender_id, :message_text, 'text', NOW())";
            $msg_stmt = $db->prepare($insert_msg);
            $msg_stmt->bindParam(':chat_id', $chat_id);
            $msg_stmt->bindParam(':sender_type', $msg['sender_type']);
            $msg_stmt->bindParam(':sender_id', $msg['sender_id']);
            $msg_stmt->bindParam(':message_text', $msg['text']);
            $msg_stmt->execute();
            echo "     - Создано сообщение ID: " . $db->lastInsertId() . "\n";
        }
        
        echo "\n   ✓ Тестовые данные созданы!\n\n";
        
        // Обновляем список чатов
        $stmt = $db->query($query);
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    foreach ($chats as $chat) {
        echo "   - Чат ID: {$chat['id']}, Пользователь: {$chat['user_id']}, Статус: {$chat['status']}\n";
    }
    
    echo "\n";
    
    // 2. Проверяем сообщения в первом чате
    $first_chat_id = $chats[0]['id'];
    echo "2. Сообщения в чате ID=$first_chat_id:\n";
    
    $msg_query = "SELECT m.*, u.name as user_name, a.full_name as admin_name
                  FROM messages m
                  LEFT JOIN users u ON m.sender_type = 'user' AND m.sender_id = u.id
                  LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
                  WHERE m.chat_id = :chat_id
                  ORDER BY m.created_at ASC";
    
    $msg_stmt = $db->prepare($msg_query);
    $msg_stmt->bindParam(':chat_id', $first_chat_id);
    $msg_stmt->execute();
    $messages = $msg_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($messages)) {
        echo "   ❌ Сообщений в чате НЕТ!\n\n";
    } else {
        foreach ($messages as $msg) {
            $sender = $msg['sender_type'] == 'user' ? $msg['user_name'] : $msg['admin_name'];
            echo "   - ID: {$msg['id']}, От: {$msg['sender_type']} ($sender), Текст: " . mb_substr($msg['message_text'], 0, 50) . "\n";
        }
    }
    
    echo "\n";
    
    // 3. Тестируем метод getNewMessages
    echo "3. Тестируем Chat::getNewMessages($first_chat_id, 0):\n";
    
    $chat = new Chat($db);
    $new_messages = $chat->getNewMessages($first_chat_id, 0);
    
    if (empty($new_messages)) {
        echo "   ❌ Метод вернул пустой массив!\n";
        
        // Проверим SQL запрос напрямую
        echo "\n   Проверяем SQL запрос напрямую:\n";
        
        $test_query = "SELECT m.id, m.chat_id, m.sender_type, m.sender_id, 
                              m.message_text, m.message_type, m.metadata, 
                              m.is_read, m.created_at,
                              CASE 
                                  WHEN m.sender_type = 'user' THEN COALESCE(u.name, 'Пользователь')
                                  WHEN m.sender_type = 'admin' THEN COALESCE(a.full_name, 'Администратор')
                                  ELSE 'Система'
                              END as sender_name
                       FROM messages m
                       LEFT JOIN users u ON m.sender_type = 'user' AND m.sender_id = u.id
                       LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
                       WHERE m.chat_id = :chat_id 
                       AND m.id > :last_id
                       ORDER BY m.created_at ASC
                       LIMIT 50";
        
        $test_stmt = $db->prepare($test_query);
        $test_stmt->bindParam(":chat_id", $first_chat_id, PDO::PARAM_INT);
        $test_stmt->bindValue(":last_id", 0, PDO::PARAM_INT);
        $test_stmt->execute();
        
        $test_results = $test_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   Результат прямого SQL запроса: " . count($test_results) . " сообщений\n";
        
        if (!empty($test_results)) {
            foreach ($test_results as $result) {
                echo "     - ID: {$result['id']}, Тип: {$result['sender_type']}, Имя: {$result['sender_name']}\n";
            }
        }
        
    } else {
        echo "   ✓ Метод вернул " . count($new_messages) . " сообщений:\n";
        foreach ($new_messages as $msg) {
            echo "     - ID: {$msg['id']}, От: {$msg['sender_type']}, Имя: {$msg['sender_name']}\n";
        }
    }
    
    echo "\n";
    
    // 4. Проверяем администраторов
    echo "4. Проверяем администраторов:\n";
    $admin_query = "SELECT id, username, full_name FROM admins";
    $admin_stmt = $db->query($admin_query);
    $admins = $admin_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "   ❌ Администраторов в базе данных НЕТ!\n";
        echo "   Создаем тестового администратора...\n";
        
        $create_admin = "INSERT INTO admins (username, full_name, password_hash, role, is_active, created_at) 
                        VALUES ('admin', 'Тестовый Администратор', 'hash', 'super_admin', 1, NOW())";
        $db->exec($create_admin);
        $admin_id = $db->lastInsertId();
        echo "   ✓ Создан администратор с ID: $admin_id\n";
    } else {
        foreach ($admins as $admin) {
            echo "   - Админ ID: {$admin['id']}, Логин: {$admin['username']}, Имя: {$admin['full_name']}\n";
        }
    }
    
    echo "\n";
    
    // 5. Финальный тест API
    echo "5. Финальный тест API через HTTP:\n";
    
    // Устанавливаем правильную сессию
    $_SESSION['admin_id'] = !empty($admins) ? $admins[0]['id'] : 1;
    $_SESSION['admin_role'] = 'super_admin';
    
    echo "   Сессия админа: {$_SESSION['admin_id']}\n";
    echo "   Тестируем чат ID: $first_chat_id\n";
    
    // Имитируем API запрос
    $_GET['action'] = 'new_messages';
    $_GET['chat_id'] = $first_chat_id;
    $_GET['last_id'] = 0;
    
    ob_start();
    
    try {
        // Подключаем API файл
        $api_content = file_get_contents(__DIR__ . '/api/chats.php');
        eval('?>' . $api_content);
    } catch (Exception $e) {
        echo "   ❌ Ошибка API: " . $e->getMessage() . "\n";
    }
    
    $api_output = ob_get_clean();
    
    echo "   Результат API:\n";
    echo "   " . str_replace("\n", "\n   ", $api_output) . "\n";
    
    echo "\n=== КОНЕЦ ДИАГНОСТИКИ ===\n";
    
} catch (Exception $e) {
    echo "КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Файл: " . $e->getFile() . "\n";
    echo "Строка: " . $e->getLine() . "\n";
    echo "Стек:\n" . $e->getTraceAsString() . "\n";
}
?>