<?php
// admin/test_send_message.php - Тест API отправки сообщений
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_role'] = 'super_admin';

header('Content-Type: text/plain; charset=utf-8');

echo "=== ТЕСТ API ОТПРАВКИ СООБЩЕНИЙ ===\n\n";

try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    
    echo "1. Подключение к БД...\n";
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Ошибка подключения к БД");
    }
    echo "✓ Подключение успешно\n\n";
    
    echo "2. Проверка структуры таблиц...\n";
    
    // Проверяем поля в messages
    $query = "SHOW COLUMNS FROM messages";
    $stmt = $db->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Поля в таблице messages:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }
    
    $required_fields = ['is_read', 'is_read_admin', 'metadata'];
    $missing_fields = array_diff($required_fields, $columns);
    
    if (!empty($missing_fields)) {
        echo "\n❌ Отсутствуют поля: " . implode(', ', $missing_fields) . "\n";
        
        echo "Добавляем недостающие поля...\n";
        foreach ($missing_fields as $field) {
            try {
                switch ($field) {
                    case 'is_read':
                        $db->exec("ALTER TABLE messages ADD COLUMN is_read TINYINT(1) DEFAULT 0");
                        echo "✓ Добавлено поле is_read\n";
                        break;
                    case 'is_read_admin':
                        $db->exec("ALTER TABLE messages ADD COLUMN is_read_admin TINYINT(1) DEFAULT 0");
                        echo "✓ Добавлено поле is_read_admin\n";
                        break;
                    case 'metadata':
                        $db->exec("ALTER TABLE messages ADD COLUMN metadata TEXT DEFAULT NULL");
                        echo "✓ Добавлено поле metadata\n";
                        break;
                }
            } catch (Exception $e) {
                echo "❌ Ошибка добавления поля $field: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "✓ Все необходимые поля присутствуют\n";
    }
    
    echo "\n3. Проверка класса Chat...\n";
    
    require_once __DIR__ . '/classes/Chat.php';
    $chat = new Chat($db);
    echo "✓ Класс Chat загружен\n";
    
    echo "\n4. Тест отправки сообщения...\n";
    
    $test_chat_id = 1;
    $test_message = "Тестовое сообщение " . date('H:i:s');
    
    echo "Отправляем сообщение в чат $test_chat_id: '$test_message'\n";
    
    try {
        $message_id = $chat->sendMessage($test_chat_id, 'admin', 1, $test_message, 'text');
        echo "✓ Сообщение отправлено, ID: $message_id\n";
        
        // Получаем отправленное сообщение
        $sent_message = $chat->getMessageById($message_id);
        
        if ($sent_message) {
            echo "✓ Сообщение найдено в БД:\n";
            echo "  ID: {$sent_message['id']}\n";
            echo "  Текст: {$sent_message['message_text']}\n";
            echo "  Отправитель: {$sent_message['sender_name']}\n";
            echo "  Тип: {$sent_message['sender_type']}\n";
        } else {
            echo "❌ Сообщение не найдено в БД\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Ошибка отправки: " . $e->getMessage() . "\n";
        echo "Файл: " . $e->getFile() . "\n";
        echo "Строка: " . $e->getLine() . "\n";
    }
    
    echo "\n5. Тест через HTTP (имитация формы)...\n";
    
    // Имитируем POST запрос
    $_POST = [
        'chat_id' => $test_chat_id,
        'message' => "HTTP тест " . date('H:i:s')
    ];
    
    $_FILES = []; // Пустые файлы
    
    echo "POST данные установлены\n";
    
    // Захватываем вывод API
    ob_start();
    
    try {
        // Подключаем API файл напрямую
        include __DIR__ . '/api/chats.php';
    } catch (Exception $e) {
        echo "❌ Ошибка в API: " . $e->getMessage() . "\n";
    }
    
    $api_output = ob_get_clean();
    
    echo "Результат API:\n";
    echo $api_output . "\n";
    
    // Проверяем, является ли результат валидным JSON
    $json_data = json_decode($api_output, true);
    
    if ($json_data !== null) {
        echo "✓ API вернул валидный JSON\n";
        if (isset($json_data['success']) && $json_data['success']) {
            echo "✓ API операция успешна\n";
        } else {
            echo "❌ API операция неуспешна: " . ($json_data['error'] ?? 'Неизвестная ошибка') . "\n";
        }
    } else {
        echo "❌ API вернул не валидный JSON:\n";
        echo substr($api_output, 0, 500) . "...\n";
    }
    
    echo "\n=== КОНЕЦ ТЕСТА ===\n";
    
} catch (Exception $e) {
    echo "КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Файл: " . $e->getFile() . "\n";
    echo "Строка: " . $e->getLine() . "\n";
    echo "Стек:\n" . $e->getTraceAsString() . "\n";
}
?>