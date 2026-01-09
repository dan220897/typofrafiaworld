<?php
/**
 * Webhook для обработки входящих сообщений из Telegram
 * URL для установки webhook: https://your-domain.com/telegram_webhook.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/ChatService.php';
require_once __DIR__ . '/classes/TelegramNotifier.php';

// Логируем все входящие запросы
$input = file_get_contents('php://input');
logMessage("Telegram webhook получен: " . $input, 'INFO');

// Получаем данные от Telegram
$update = json_decode($input, true);

if (!$update) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Проверяем наличие сообщения
if (!isset($update['message'])) {
    http_response_code(200);
    exit('OK');
}

$message = $update['message'];
$telegramChatId = $message['chat']['id'];
$text = isset($message['text']) ? trim($message['text']) : '';
$fromUser = $message['from'];

// Игнорируем сообщения от ботов
if (isset($fromUser['is_bot']) && $fromUser['is_bot']) {
    http_response_code(200);
    exit('OK');
}

try {
    $db = Database::getInstance()->getConnection();
    $chatService = new ChatService();
    $telegram = new TelegramNotifier();
    
    // Проверяем, является ли это сообщением в личном чате с клиентом
    // Формат: каждый чат с клиентом имеет уникальный telegram_thread_id
    
    // Ищем чат по telegram_chat_id + telegram_thread_id (для групповых чатов)
    // Или просто по telegram_chat_id для личных чатов
    $threadId = isset($message['message_thread_id']) ? $message['message_thread_id'] : null;
    
    $stmt = $db->prepare("
        SELECT c.id, c.user_id, u.phone, u.name 
        FROM chats c
        JOIN users u ON c.user_id = u.id
        WHERE c.telegram_chat_id = ? AND c.telegram_thread_id = ?
        LIMIT 1
    ");
    $stmt->execute([$telegramChatId, $threadId]);
    $chat = $stmt->fetch();
    
    if (!$chat) {
        // Это новое обращение или сообщение не в контексте существующего чата
        // Отправляем инструкцию
        $telegram->sendMessageToChat(
            $telegramChatId,
            "ℹ️ Это сообщение не связано ни с одним чатом клиента.\n\n" .
            "Чтобы ответить клиенту, используйте кнопку 'Ответить' в уведомлении о новом сообщении.",
            $threadId
        );
        http_response_code(200);
        exit('OK');
    }
    
    // Сохраняем сообщение администратора в базу данных
    $stmt = $db->prepare("
        INSERT INTO messages 
        (chat_id, sender_type, sender_id, message_text, message_type, created_at) 
        VALUES (?, 'admin', NULL, ?, 'text', NOW())
    ");
    $stmt->execute([$chat['id'], $text]);
    
    // Обновляем время последнего сообщения в чате
    $stmt = $db->prepare("
        UPDATE chats 
        SET last_message_at = NOW(), updated_at = NOW(), unread_user_count = unread_user_count + 1
        WHERE id = ?
    ");
    $stmt->execute([$chat['id']]);
    
    logMessage("Сообщение от админа в Telegram сохранено в чат ID: {$chat['id']}", 'INFO');
    
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    logMessage("Ошибка обработки Telegram webhook: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    exit('Error');
}
?>