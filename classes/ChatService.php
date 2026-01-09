<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/classes/TelegramNotifier.php';

class ChatService {
    private $db;
    private $telegramNotifier;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->telegramNotifier = new TelegramNotifier();
    }
    
    /**
     * Получение или создание чата для пользователя
     */
    public function getOrCreateUserChat($userId) {
        try {
            // Ищем активный чат пользователя
            $stmt = $this->db->prepare("
                SELECT id, admin_id, status, created_at 
                FROM chats 
                WHERE user_id = ? AND status != 'archived'
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $chat = $stmt->fetch();
            
            if ($chat) {
                return [
                    'success' => true,
                    'chat_id' => $chat['id'],
                    'is_new' => false
                ];
            }
            
            // Создаем новый чат
            $stmt = $this->db->prepare("
                INSERT INTO chats (user_id, status, created_at, updated_at) 
                VALUES (?, 'active', NOW(), NOW())
            ");
            $result = $stmt->execute([$userId]);
            
            if (!$result) {
                throw new Exception('Ошибка создания чата');
            }
            
            $chatId = $this->db->lastInsertId();
            
            // Отправляем приветственное сообщение
            $this->sendSystemMessage($chatId, 'Здравствуйте! 👋 Я помогу вам оформить заказ или отвечу на любые вопросы о наших услугах.');
            
            logMessage("Создан новый чат ID: {$chatId} для пользователя ID: {$userId}", 'INFO');
            
            return [
                'success' => true,
                'chat_id' => $chatId,
                'is_new' => true
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка создания чата для пользователя {$userId}: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => 'Ошибка создания чата'
            ];
        }
    }
    
    /**
     * Отправка сообщения пользователем
     */
    public function sendUserMessage($chatId, $userId, $message, $messageType = 'text', $metadata = null) {
        try {
            // Проверяем существование чата и права пользователя
            if (!$this->checkChatAccess($chatId, $userId)) {
                return [
                    'success' => false,
                    'error' => 'Доступ к чату запрещен'
                ];
            }
            
            // Сохраняем сообщение
            $stmt = $this->db->prepare("
                INSERT INTO messages 
                (chat_id, sender_type, sender_id, message_text, message_type, metadata, created_at) 
                VALUES (?, 'user', ?, ?, ?, ?, NOW())
            ");
            
            $metadataJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;
            $result = $stmt->execute([$chatId, $userId, $message, $messageType, $metadataJson]);
            
            if (!$result) {
                throw new Exception('Ошибка сохранения сообщения');
            }
            
            $messageId = $this->db->lastInsertId();
            
            // Обновляем время последнего сообщения в чате
            $this->updateChatLastMessage($chatId);
            
            // Отправляем уведомление в Telegram администраторам
            $this->notifyAdminsNewMessage($chatId, $userId, $message);
            
            logMessage("Новое сообщение от пользователя {$userId} в чате {$chatId}", 'INFO');
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'message' => 'Сообщение отправлено'
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка отправки сообщения в чат {$chatId}: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => 'Ошибка отправки сообщения'
            ];
        }
    }
    
    /**
     * Отправка системного сообщения
     */
    public function sendSystemMessage($chatId, $message, $messageType = 'system', $metadata = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO messages 
                (chat_id, sender_type, sender_id, message_text, message_type, metadata, is_read_user, created_at) 
                VALUES (?, 'system', NULL, ?, ?, ?, 0, NOW())
            ");
            
            $metadataJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;
            $result = $stmt->execute([$chatId, $message, $messageType, $metadataJson]);
            
            if ($result) {
                $this->updateChatLastMessage($chatId);
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Ошибка отправки системного сообщения в чат {$chatId}: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Получение сообщений чата
     */
    public function getChatMessages($chatId, $userId, $afterId = 0, $limit = 50) {
        try {
            // Проверяем доступ к чату
            if (!$this->checkChatAccess($chatId, $userId)) {
                return [
                    'success' => false,
                    'error' => 'Доступ к чату запрещен'
                ];
            }
            
            // Получаем сообщения
            $stmt = $this->db->prepare("
                SELECT 
                    m.id,
                    m.sender_type,
                    m.sender_id,
                    m.message_text,
                    m.message_type,
                    m.metadata,
                    m.created_at,
                    ma.file_name,
                    ma.file_path,
                    ma.file_size,
                    ma.file_type
                FROM messages m
                LEFT JOIN message_attachments ma ON m.id = ma.message_id
                WHERE m.chat_id = ? AND m.id > ?
                ORDER BY m.created_at ASC
                LIMIT ?
            ");
            
            $stmt->execute([$chatId, $afterId, $limit]);
            $messages = $stmt->fetchAll();
            
            // Отмечаем сообщения как прочитанные пользователем
            if (!empty($messages)) {
                $this->markMessagesAsRead($chatId, $userId, 'user');
            }
            
            // Форматируем сообщения
            $formattedMessages = [];
            foreach ($messages as $message) {
                $formattedMessage = [
                    'id' => $message['id'],
                    'sender_type' => $message['sender_type'],
                    'sender_id' => $message['sender_id'],
                    'message_text' => $message['message_text'],
                    'message_type' => $message['message_type'],
                    'created_at' => $message['created_at']
                ];
                
                // Добавляем метаданные если есть
                if ($message['metadata']) {
                    $formattedMessage['metadata'] = json_decode($message['metadata'], true);
                }
                
                // Добавляем информацию о файле если есть
                if ($message['file_name']) {
                    $formattedMessage['file_name'] = $message['file_name'];
                    $formattedMessage['file_path'] = $message['file_path'];
                    $formattedMessage['file_size'] = $message['file_size'];
                    $formattedMessage['file_type'] = $message['file_type'];
                }
                
                $formattedMessages[] = $formattedMessage;
            }
            
            return [
                'success' => true,
                'messages' => $formattedMessages
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка получения сообщений чата {$chatId}: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => 'Ошибка получения сообщений'
            ];
        }
    }
    
    /**
     * Загрузка файла в чат
     */
    public function uploadFile($chatId, $userId, $file) {
        try {
            // Проверяем доступ к чату
            if (!$this->checkChatAccess($chatId, $userId)) {
                return [
                    'success' => false,
                    'error' => 'Доступ к чату запрещен'
                ];
            }
            
            // Проверяем файл
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'error' => 'Ошибка загрузки файла'
                ];
            }
            
            if ($file['size'] > MAX_FILE_SIZE) {
                return [
                    'success' => false,
                    'error' => 'Файл слишком большой. Максимальный размер: ' . formatFileSize(MAX_FILE_SIZE)
                ];
            }
            
            // Проверяем MIME тип
            $mimeType = getMimeType($file['tmp_name']);
            if (!isAllowedFileType($mimeType)) {
                return [
                    'success' => false,
                    'error' => 'Неподдерживаемый тип файла'
                ];
            }
            
            // Генерируем безопасное имя файла
            $originalName = sanitizeFilename($file['name']);
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            
            // Создаем директорию для файлов сообщений
            $uploadDir = UPLOADS_DIR . 'messages/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filePath = $uploadDir . $fileName;
            $fileUrl = UPLOADS_URL . 'messages/' . $fileName;
            
            // Перемещаем загруженный файл
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return [
                    'success' => false,
                    'error' => 'Ошибка сохранения файла'
                ];
            }
            
            // Сохраняем сообщение с файлом
            $messageText = "📎 Файл: " . $originalName;
            $messageResult = $this->sendUserMessage($chatId, $userId, $messageText, 'file');
            
            if (!$messageResult['success']) {
                // Удаляем файл если не удалось создать сообщение
                unlink($filePath);
                return $messageResult;
            }
            
            // Сохраняем информацию о файле
            $stmt = $this->db->prepare("
                INSERT INTO message_attachments 
                (message_id, file_name, file_path, file_size, file_type, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $messageResult['message_id'],
                $originalName,
                $fileUrl,
                $file['size'],
                $mimeType
            ]);
            
            if (!$result) {
                // Удаляем файл и сообщение если не удалось сохранить вложение
                unlink($filePath);
                throw new Exception('Ошибка сохранения информации о файле');
            }
            
            logMessage("Загружен файл {$originalName} в чат {$chatId} пользователем {$userId}", 'INFO');
            
            return [
                'success' => true,
                'message' => 'Файл загружен',
                'file_name' => $originalName,
                'file_path' => $fileUrl,
                'file_size' => $file['size']
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка загрузки файла в чат {$chatId}: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'error' => 'Ошибка загрузки файла'
            ];
        }
    }
    
    /**
     * Проверка доступа к чату
     */
    private function checkChatAccess($chatId, $userId) {
        try {
            $stmt = $this->db->prepare("SELECT user_id FROM chats WHERE id = ?");
            $stmt->execute([$chatId]);
            $chat = $stmt->fetch();
            
            return $chat && $chat['user_id'] == $userId;
            
        } catch (Exception $e) {
            logMessage("Ошибка проверки доступа к чату {$chatId}: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Обновление времени последнего сообщения
     */
    private function updateChatLastMessage($chatId) {
        try {
            $stmt = $this->db->prepare("UPDATE chats SET last_message_at = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$chatId]);
        } catch (Exception $e) {
            logMessage("Ошибка обновления времени последнего сообщения в чате {$chatId}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Отметка сообщений как прочитанные
     */
    private function markMessagesAsRead($chatId, $userId, $readerType) {
        try {
            if ($readerType === 'user') {
                // Отмечаем сообщения от админов и системы как прочитанные пользователем
                $stmt = $this->db->prepare("
                    UPDATE messages 
                    SET is_read_user = 1 
                    WHERE chat_id = ? AND sender_type IN ('admin', 'system') AND is_read_user = 0
                ");
                $stmt->execute([$chatId]);
                
                // Обновляем счетчик непрочитанных сообщений в чате
                $stmt = $this->db->prepare("UPDATE chats SET unread_user_count = 0, last_user_read_at = NOW() WHERE id = ?");
                $stmt->execute([$chatId]);
                
            } elseif ($readerType === 'admin') {
                // Отмечаем сообщения от пользователей как прочитанные админом
                $stmt = $this->db->prepare("
                    UPDATE messages 
                    SET is_read_admin = 1 
                    WHERE chat_id = ? AND sender_type = 'user' AND is_read_admin = 0
                ");
                $stmt->execute([$chatId]);
                
                // Обновляем счетчик непрочитанных сообщений в чате
                $stmt = $this->db->prepare("UPDATE chats SET unread_admin_count = 0, last_admin_read_at = NOW() WHERE id = ?");
                $stmt->execute([$chatId]);
            }
            
        } catch (Exception $e) {
            logMessage("Ошибка отметки сообщений как прочитанные в чате {$chatId}: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Уведомление администраторов о новом сообщении
     */
    private function notifyAdminsNewMessage($chatId, $userId, $message) {
        try {
            // Получаем информацию о пользователе
            $stmt = $this->db->prepare("SELECT phone, name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) return;
            
            $clientName = $user['name'] ?: $user['phone'];
            
            // Отправляем уведомление в Telegram
            $this->telegramNotifier->notifyNewMessage($chatId, $clientName, $message);
            
        } catch (Exception $e) {
            logMessage("Ошибка отправки уведомления о новом сообщении: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Получение списка чатов для админки
     */
    public function getChatsForAdmin($status = 'active', $limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.id,
                    c.status,
                    c.unread_admin_count,
                    c.last_message_at,
                    c.created_at,
                    u.phone,
                    u.name,
                    u.email,
                    (SELECT message_text FROM messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
                FROM chats c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.status = ?
                ORDER BY c.last_message_at DESC, c.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$status, $limit, $offset]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            logMessage("Ошибка получения списка чатов: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Получение статистики чатов
     */
    public function getChatStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_chats,
                    COUNT(CASE WHEN unread_admin_count > 0 THEN 1 END) as unread_chats,
                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_chats,
                    SUM(unread_admin_count) as total_unread_messages
                FROM chats
            ");
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            logMessage("Ошибка получения статистики чатов: " . $e->getMessage(), 'ERROR');
            return [
                'active_chats' => 0,
                'unread_chats' => 0,
                'today_chats' => 0,
                'total_unread_messages' => 0
            ];
        }
    }
    
    /**
     * Назначение администратора на чат
     */
    public function assignAdminToChat($chatId, $adminId) {
        try {
            $stmt = $this->db->prepare("UPDATE chats SET admin_id = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$adminId, $chatId]);
            
            if ($result) {
                logMessage("Администратор {$adminId} назначен на чат {$chatId}", 'INFO');
                return ['success' => true, 'message' => 'Администратор назначен'];
            }
            
            return ['success' => false, 'error' => 'Ошибка назначения администратора'];
            
        } catch (Exception $e) {
            logMessage("Ошибка назначения администратора на чат {$chatId}: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => 'Ошибка назначения администратора'];
        }
    }
    
    /**
     * Закрытие чата
     */
    public function closeChat($chatId, $adminId = null) {
        try {
            $stmt = $this->db->prepare("UPDATE chats SET status = 'closed', updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$chatId]);
            
            if ($result) {
                // Отправляем системное сообщение о закрытии
                $this->sendSystemMessage($chatId, 'Чат закрыт администратором. Спасибо за обращение!');
                
                logMessage("Чат {$chatId} закрыт" . ($adminId ? " администратором {$adminId}" : ""), 'INFO');
                return ['success' => true, 'message' => 'Чат закрыт'];
            }
            
            return ['success' => false, 'error' => 'Ошибка закрытия чата'];
            
        } catch (Exception $e) {
            logMessage("Ошибка закрытия чата {$chatId}: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => 'Ошибка закрытия чата'];
        }
    }
    
    /**
     * Очистка старых чатов и сообщений
     */
    public function cleanupOldData($daysBefore = 365) {
        try {
            // Архивируем старые закрытые чаты
            $stmt = $this->db->prepare("
                UPDATE chats 
                SET status = 'archived' 
                WHERE status = 'closed' 
                AND updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$daysBefore]);
            $archivedChats = $stmt->rowCount();
            
            // Удаляем очень старые архивные чаты (с файлами)
            $stmt = $this->db->prepare("
                SELECT c.id, ma.file_path 
                FROM chats c
                LEFT JOIN messages m ON c.id = m.chat_id
                LEFT JOIN message_attachments ma ON m.id = ma.message_id
                WHERE c.status = 'archived' 
                AND c.updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$daysBefore * 2]);
            $oldChats = $stmt->fetchAll();
            
            // Удаляем файлы
            foreach ($oldChats as $chat) {
                if ($chat['file_path'] && file_exists($chat['file_path'])) {
                    unlink($chat['file_path']);
                }
            }
            
            // Удаляем чаты и связанные данные (CASCADE удалит сообщения и вложения)
            if (!empty($oldChats)) {
                $chatIds = array_unique(array_column($oldChats, 'id'));
                $placeholders = str_repeat('?,', count($chatIds) - 1) . '?';
                $stmt = $this->db->prepare("DELETE FROM chats WHERE id IN ({$placeholders})");
                $stmt->execute($chatIds);
                $deletedChats = $stmt->rowCount();
            } else {
                $deletedChats = 0;
            }
            
            logMessage("Очистка чатов: архивировано {$archivedChats}, удалено {$deletedChats}", 'INFO');
            
            return [
                'archived' => $archivedChats,
                'deleted' => $deletedChats
            ];
            
        } catch (Exception $e) {
            logMessage("Ошибка очистки старых данных чатов: " . $e->getMessage(), 'ERROR');
            return ['archived' => 0, 'deleted' => 0];
        }
    }
    /**
 * Загрузка нескольких файлов в чат
 */
public function uploadMultipleFiles($chatId, $userId, $files) {
    $uploadedFiles = [];
    $errors = [];
    
    foreach ($files as $index => $file) {
        $result = $this->uploadFile($chatId, $userId, $file);
        
        if ($result['success']) {
            $uploadedFiles[] = $result;
        } else {
            $errors[] = "Файл {$file['name']}: " . $result['error'];
        }
    }
    
    if (count($uploadedFiles) === count($files)) {
        return [
            'success' => true,
            'message' => 'Все файлы загружены',
            'files' => $uploadedFiles
        ];
    } elseif (count($uploadedFiles) > 0) {
        return [
            'success' => true,
            'message' => 'Часть файлов загружена',
            'files' => $uploadedFiles,
            'errors' => $errors
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Ошибка загрузки файлов',
            'errors' => $errors
        ];
    }
}
}
?>