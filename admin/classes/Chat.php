<?php
// admin/classes/Chat.php

class Chat {
    private $conn;
    private $table_name = "chats";
    private $telegram;
    
    public function __construct($db) {
        $this->conn = $db;
        // Проверяем существование класса TelegramNotifier
        if (class_exists('TelegramNotifier')) {
            $this->telegram = new TelegramNotifier();
        }
    }
    
    // Получить список чатов
    public function getChats($admin_id = null, $status = 'all', $limit = 50, $offset = 0) {
        $query = "SELECT c.*, u.name as user_name, u.phone as user_phone, u.email as user_email,
                        a.full_name as admin_name,
                        (SELECT message_text FROM messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                        (SELECT created_at FROM messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                        (SELECT COUNT(*) FROM orders WHERE chat_id = c.id) as orders_count
                 FROM " . $this->table_name . " c
                 LEFT JOIN users u ON c.user_id = u.id
                 LEFT JOIN admins a ON c.admin_id = a.id
                 WHERE 1=1";
        
        if ($admin_id) {
            $query .= " AND c.admin_id = :admin_id";
        }
        
        if ($status !== 'all') {
            $query .= " AND c.status = :status";
        }
        
        $query .= " ORDER BY c.last_message_at DESC
                   LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if ($admin_id) {
            $stmt->bindParam(":admin_id", $admin_id);
        }
        
        if ($status !== 'all') {
            $stmt->bindParam(":status", $status);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Получить или создать чат
    public function getOrCreateChat($user_id) {
        // Проверяем существующий активный чат
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_id = :user_id AND status = 'active' 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        
        // Создаем новый чат
        $query = "INSERT INTO " . $this->table_name . " 
                 (user_id, status, created_at) 
                 VALUES (:user_id, 'active', NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($stmt->execute()) {
            return $this->getChatById($this->conn->lastInsertId());
        }
        
        return false;
    }
    
    // Получить чат по ID
    public function getChatById($chat_id) {
        $query = "SELECT c.*, u.name as user_name, u.phone as user_phone, 
                        u.email as user_email, u.company_name, u.inn,
                        a.full_name as admin_name
                 FROM " . $this->table_name . " c
                 LEFT JOIN users u ON c.user_id = u.id
                 LEFT JOIN admins a ON c.admin_id = a.id
                 WHERE c.id = :chat_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":chat_id", $chat_id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Получить сообщения чата
    public function getMessages($chat_id, $limit = 50, $offset = 0) {
        try {
            $query = "SELECT m.id, m.chat_id, m.sender_type, m.sender_id, 
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
                     ORDER BY m.created_at DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Обрабатываем каждое сообщение
            foreach ($messages as &$message) {
                // Гарантируем наличие sender_name
                if (empty($message['sender_name'])) {
                    switch ($message['sender_type']) {
                        case 'user':
                            $message['sender_name'] = 'Пользователь';
                            break;
                        case 'admin':
                            $message['sender_name'] = 'Администратор';
                            break;
                        default:
                            $message['sender_name'] = 'Система';
                    }
                }
                
                // Получаем вложения
                $message['attachments'] = $this->getMessageAttachments($message['id']);
                
                // Декодируем метаданные
                if (!empty($message['metadata'])) {
                    $message['metadata'] = json_decode($message['metadata'], true);
                } else {
                    $message['metadata'] = null;
                }
                
                // Гарантируем наличие всех полей
                $message['message_text'] = $message['message_text'] ?? '';
                $message['message_type'] = $message['message_type'] ?? 'text';
                $message['is_read'] = $message['is_read'] ?? 0;
            }
            
            return array_reverse($messages);
            
        } catch (Exception $e) {
            error_log("Chat::getMessages Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Отправить сообщение
    // Отправить сообщение (исправленная версия)
    public function sendMessage($chat_id, $sender_type, $sender_id, $message_text, $message_type = 'text', $metadata = null) {
        try {
            $this->conn->beginTransaction();
            
            // Вставляем сообщение
            $query = "INSERT INTO messages 
                     (chat_id, sender_type, sender_id, message_text, message_type, metadata, created_at) 
                     VALUES (:chat_id, :sender_type, :sender_id, :message_text, :message_type, :metadata, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Используем bindValue для всех параметров чтобы избежать ошибок с ссылками
            $stmt->bindValue(":chat_id", $chat_id, PDO::PARAM_INT);
            $stmt->bindValue(":sender_type", $sender_type, PDO::PARAM_STR);
            $stmt->bindValue(":sender_id", $sender_id, PDO::PARAM_INT);
            $stmt->bindValue(":message_text", $message_text, PDO::PARAM_STR);
            $stmt->bindValue(":message_type", $message_type, PDO::PARAM_STR);
            $stmt->bindValue(":metadata", $metadata ? json_encode($metadata) : null, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Ошибка при отправке сообщения");
            }
            
            $message_id = $this->conn->lastInsertId();
            
            // Обновляем информацию о чате
            $update_query = "UPDATE chats SET last_message_at = NOW()";
            
            // Проверяем наличие полей счетчиков
            $check_fields = "SHOW COLUMNS FROM chats WHERE Field IN ('unread_admin_count', 'unread_user_count')";
            $check_stmt = $this->conn->query($check_fields);
            $existing_fields = $check_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (in_array('unread_admin_count', $existing_fields) && $sender_type == 'user') {
                $update_query .= ", unread_admin_count = COALESCE(unread_admin_count, 0) + 1";
            }
            
            if (in_array('unread_user_count', $existing_fields) && $sender_type == 'admin') {
                $update_query .= ", unread_user_count = COALESCE(unread_user_count, 0) + 1";
            }
            
            $update_query .= " WHERE id = :chat_id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindValue(":chat_id", $chat_id, PDO::PARAM_INT);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Ошибка при обновлении чата");
            }
            
            $this->conn->commit();
            
            // Отправляем уведомление в Telegram если сообщение от пользователя
            if ($sender_type == 'user' && isset($this->telegram) && $this->telegram) {
                try {
                    $chat_info = $this->getChatById($chat_id);
                    $this->telegram->notifyNewMessage($chat_id, $chat_info['user_name'] ?? 'Пользователь', $message_text);
                } catch (Exception $e) {
                    // Игнорируем ошибки Telegram, не прерываем основной процесс
                    error_log("Telegram notification error: " . $e->getMessage());
                }
            }
            
            return $message_id;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Chat::sendMessage Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Прикрепить файл к сообщению
    public function attachFile($message_id, $file_name, $file_path, $file_size = null, $file_type = null) {
        $query = "INSERT INTO message_attachments 
                 (message_id, file_name, file_path, file_size, file_type) 
                 VALUES (:message_id, :file_name, :file_path, :file_size, :file_type)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":message_id", $message_id);
        $stmt->bindParam(":file_name", $file_name);
        $stmt->bindParam(":file_path", $file_path);
        $stmt->bindParam(":file_size", $file_size);
        $stmt->bindParam(":file_type", $file_type);
        
        return $stmt->execute();
    }
    
    // Отметить сообщения как прочитанные
    public function markAsRead($chat_id, $reader_type) {
        $this->conn->beginTransaction();
        
        try {
            // Отмечаем сообщения как прочитанные
            $query = "UPDATE messages 
                     SET is_read = 1 
                     WHERE chat_id = :chat_id 
                     AND sender_type != :reader_type 
                     AND is_read = 0";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":chat_id", $chat_id);
            $stmt->bindParam(":reader_type", $reader_type);
            $stmt->execute();
            
            // Обнуляем счетчик непрочитанных
            $counter_field = $reader_type == 'admin' ? 'unread_admin_count' : 'unread_user_count';
            $update_query = "UPDATE " . $this->table_name . " 
                           SET $counter_field = 0 
                           WHERE id = :chat_id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(":chat_id", $chat_id);
            $update_stmt->execute();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    // Назначить администратора на чат
    public function assignAdmin($chat_id, $admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET admin_id = :admin_id 
                 WHERE id = :chat_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":admin_id", $admin_id);
        $stmt->bindParam(":chat_id", $chat_id);
        
        if ($stmt->execute()) {
            // Добавляем системное сообщение
            $admin_query = "SELECT full_name FROM admins WHERE id = :admin_id";
            $admin_stmt = $this->conn->prepare($admin_query);
            $admin_stmt->bindParam(":admin_id", $admin_id);
            $admin_stmt->execute();
            $admin = $admin_stmt->fetch();
            
            $this->sendMessage($chat_id, 'system', null, 
                             "Администратор {$admin['full_name']} подключился к чату", 
                             'system');
            
            return true;
        }
        
        return false;
    }
    
    // Изменить статус чата
    public function updateStatus($chat_id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = :status 
                 WHERE id = :chat_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":chat_id", $chat_id);
        
        return $stmt->execute();
    }
    
    // Получить статистику чатов
    public function getChatStats($user_id = null) {
        $stats = [];
        
        // Общее количество чатов
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed,
                    COUNT(CASE WHEN unread_admin_count > 0 THEN 1 END) as unread
                 FROM " . $this->table_name;
        
        if ($user_id) {
            $query .= " WHERE user_id = :user_id";
        }
        
        $stmt = $this->conn->prepare($query);
        if ($user_id) {
            $stmt->bindParam(":user_id", $user_id);
        }
        $stmt->execute();
        
        $stats = $stmt->fetch();
        
        // Среднее время ответа
        $response_query = "SELECT AVG(TIMESTAMPDIFF(MINUTE, user_msg.created_at, admin_msg.created_at)) as avg_response_time
                          FROM messages user_msg
                          JOIN messages admin_msg ON admin_msg.chat_id = user_msg.chat_id 
                                                   AND admin_msg.sender_type = 'admin'
                                                   AND admin_msg.created_at > user_msg.created_at
                          WHERE user_msg.sender_type = 'user'";
        
        if ($user_id) {
            $response_query .= " AND user_msg.chat_id IN (SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id)";
        }
        
        $response_stmt = $this->conn->prepare($response_query);
        if ($user_id) {
            $response_stmt->bindParam(":user_id", $user_id);
        }
        $response_stmt->execute();
        
        $response_time = $response_stmt->fetch();
        $stats['avg_response_time'] = round($response_time['avg_response_time'] ?: 0);
        
        return $stats;
    }
    
    // Поиск по чатам
    public function searchChats($search_term, $limit = 20) {
        $query = "SELECT c.*, u.name as user_name, u.phone as user_phone,
                        (SELECT message_text FROM messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.id
                 WHERE (u.name LIKE :search OR u.phone LIKE :search OR u.email LIKE :search
                        OR EXISTS (SELECT 1 FROM messages WHERE chat_id = c.id AND message_text LIKE :search))
                 ORDER BY c.last_message_at DESC
                 LIMIT :limit";
        
        $search_pattern = "%{$search_term}%";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":search", $search_pattern);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Получить новые сообщения (исправленная версия)
    public function getNewMessages($chat_id, $last_message_id) {
        try {
            // Валидация входных данных
            $chat_id = intval($chat_id);
            $last_message_id = intval($last_message_id);
            
            if ($chat_id <= 0) {
                return [];
            }
            
            $query = "SELECT m.id, m.chat_id, m.sender_type, m.sender_id, 
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
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
            $stmt->bindParam(":last_id", $last_message_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Обрабатываем каждое сообщение
            foreach ($messages as &$message) {
                // Гарантируем наличие sender_name
                if (empty($message['sender_name'])) {
                    switch ($message['sender_type']) {
                        case 'user':
                            $message['sender_name'] = 'Пользователь';
                            break;
                        case 'admin':
                            $message['sender_name'] = 'Администратор';
                            break;
                        default:
                            $message['sender_name'] = 'Система';
                    }
                }
                
                // Получаем вложения для этого сообщения
                $message['attachments'] = $this->getMessageAttachments($message['id']);
                
                // Декодируем метаданные если есть
                if (!empty($message['metadata'])) {
                    $message['metadata'] = json_decode($message['metadata'], true);
                } else {
                    $message['metadata'] = null;
                }
                
                // Гарантируем наличие всех полей
                $message['message_text'] = $message['message_text'] ?? '';
                $message['message_type'] = $message['message_type'] ?? 'text';
                $message['is_read'] = $message['is_read'] ?? 0;
            }
            
            return $messages;
            
        } catch (PDOException $e) {
            // Логируем ошибку
            error_log("Chat::getNewMessages PDO Error: " . $e->getMessage());
            return [];
        } catch (Exception $e) {
            // Логируем ошибку
            error_log("Chat::getNewMessages Error: " . $e->getMessage());
            return [];
        }
    }

    private function getMessageAttachments($message_id) {
        try {
            $query = "SELECT id, file_name, file_path, file_size, file_type 
                     FROM message_attachments 
                     WHERE message_id = :message_id
                     ORDER BY id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":message_id", $message_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Chat::getMessageAttachments Error: " . $e->getMessage());
            return [];
        }
    }

    /**
 * Обновление статуса клиента в чате
 */
public function updateClientStatus($chat_id, $client_status) {
    try {
        $query = "UPDATE " . $this->table_name . " 
                  SET client_status = :client_status,
                      updated_at = NOW()
                  WHERE id = :chat_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':chat_id', $chat_id);
        $stmt->bindParam(':client_status', $client_status);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating client status: " . $e->getMessage());
        return false;
    }
}
/**
 * Удаление чата из базы данных
 */
public function deleteChat($chat_id) {
    try {
        error_log("Starting chat deletion for ID: " . $chat_id);
        
        $this->conn->beginTransaction();
        
        // Проверяем существование чата
        $check_query = "SELECT id FROM " . $this->table_name . " WHERE id = :chat_id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':chat_id', $chat_id, PDO::PARAM_INT);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            error_log("Chat not found: " . $chat_id);
            $this->conn->rollBack();
            return false;
        }
        
        // Удаляем сообщения чата
        $delete_messages = "DELETE FROM chat_messages WHERE chat_id = :chat_id";
        $stmt = $this->conn->prepare($delete_messages);
        $stmt->bindParam(':chat_id', $chat_id, PDO::PARAM_INT);
        $stmt->execute();
        $messages_deleted = $stmt->rowCount();
        error_log("Deleted messages: " . $messages_deleted);
        
        // Удаляем сам чат
        $delete_chat = "DELETE FROM " . $this->table_name . " WHERE id = :chat_id";
        $stmt = $this->conn->prepare($delete_chat);
        $stmt->bindParam(':chat_id', $chat_id, PDO::PARAM_INT);
        $stmt->execute();
        $chat_deleted = $stmt->rowCount();
        error_log("Deleted chat rows: " . $chat_deleted);
        
        $this->conn->commit();
        
        error_log("Chat deletion completed successfully for ID: " . $chat_id);
        return $chat_deleted > 0;
        
    } catch (PDOException $e) {
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        error_log("Error deleting chat " . $chat_id . ": " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}
    
    // Получить сообщение по ID
    public function getMessageById($message_id) {
        try {
            $query = "SELECT m.id, m.chat_id, m.sender_type, m.sender_id, 
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
                     WHERE m.id = :message_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":message_id", $message_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($message) {
                // Гарантируем наличие sender_name
                if (empty($message['sender_name'])) {
                    switch ($message['sender_type']) {
                        case 'user':
                            $message['sender_name'] = 'Пользователь';
                            break;
                        case 'admin':
                            $message['sender_name'] = 'Администратор';
                            break;
                        default:
                            $message['sender_name'] = 'Система';
                    }
                }
                
                // Получаем вложения
                $message['attachments'] = $this->getMessageAttachments($message['id']);
                
                // Декодируем метаданные
                if (!empty($message['metadata'])) {
                    $message['metadata'] = json_decode($message['metadata'], true);
                } else {
                    $message['metadata'] = null;
                }
                
                // Гарантируем наличие всех полей
                $message['message_text'] = $message['message_text'] ?? '';
                $message['message_type'] = $message['message_type'] ?? 'text';
                $message['is_read'] = $message['is_read'] ?? 0;
            }
            
            return $message;
            
        } catch (Exception $e) {
            error_log("Chat::getMessageById Error: " . $e->getMessage());
            return null;
        }
    }
}