<?php
require_once dirname(__DIR__) . '/config/config.php';

class TelegramNotifier {
    private $botToken;
    private $chatId;
    private $apiUrl;
    
    public function __construct() {
        $this->botToken = BOT_TOKEN;
        $this->chatId = MANAGER_CHAT_ID;
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }
    
    /**
     * Отправка текстового сообщения
     */
    public function sendMessage($text, $parseMode = 'HTML', $disableNotification = false) {
        if (!TELEGRAM_NOTIFICATIONS_ENABLED) {
            return ['success' => false, 'message' => 'Telegram уведомления отключены'];
        }
        
        $data = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_notification' => $disableNotification
        ];
        
        return $this->makeRequest('sendMessage', $data);
    }
    
    /**
     * Отправка фото
     */
    public function sendPhoto($photoPath, $caption = '', $parseMode = 'HTML') {
        if (!TELEGRAM_NOTIFICATIONS_ENABLED) {
            return ['success' => false, 'message' => 'Telegram уведомления отключены'];
        }
        
        if (!file_exists($photoPath)) {
            return ['success' => false, 'message' => 'Файл не найден'];
        }
        
        $data = [
            'chat_id' => $this->chatId,
            'photo' => new CURLFile($photoPath),
            'caption' => $caption,
            'parse_mode' => $parseMode
        ];
        
        return $this->makeRequest('sendPhoto', $data);
    }
    
    /**
     * Отправка документа
     */
    public function sendDocument($documentPath, $caption = '', $parseMode = 'HTML') {
        if (!TELEGRAM_NOTIFICATIONS_ENABLED) {
            return ['success' => false, 'message' => 'Telegram уведомления отключены'];
        }
        
        if (!file_exists($documentPath)) {
            return ['success' => false, 'message' => 'Файл не найден'];
        }
        
        $data = [
            'chat_id' => $this->chatId,
            'document' => new CURLFile($documentPath),
            'caption' => $caption,
            'parse_mode' => $parseMode
        ];
        
        return $this->makeRequest('sendDocument', $data);
    }
    
    /**
     * Уведомление о новом сообщении в чате
     */
    public function notifyNewMessage($chatId, $clientName, $message) {
        $text = "💬 <b>Новое сообщение в чате</b>\n\n";
        $text .= "👤 Клиент: <b>{$this->escape($clientName)}</b>\n";
        $text .= "🆔 Чат ID: <code>#{$chatId}</code>\n";
        $text .= "📝 Сообщение: <i>{$this->escape($this->truncate($message, 200))}</i>\n\n";
        $text .= "🔗 <a href='" . ADMIN_URL . "/chats.php?id={$chatId}'>Открыть чат</a>";
        
        return $this->sendMessage($text);
    }
    
    /**
     * Уведомление о новом заказе
     */
    public function notifyNewOrder($order) {
        $text = "📦 <b>Новый заказ</b>\n\n";
        $text .= "📋 ID заказа: <code>#{$order['id']}</code>\n";
        $text .= "👤 Клиент: <b>{$this->escape($order['client_name'])}</b>\n";
        $text .= "📱 Телефон: <code>{$order['client_phone']}</code>\n";
        $text .= "🛍️ Услуги: <b>{$this->escape($order['service_name'])}</b>\n";
        
        if (!empty($order['description'])) {
            $text .= "📝 Описание: <i>{$this->escape($this->truncate($order['description'], 150))}</i>\n";
        }
        
        if (!empty($order['price'])) {
            $text .= "💰 Сумма: <b>{$order['price']} ₽</b>\n";
        }
        
        $text .= "⏰ Создан: " . date('d.m.Y H:i') . "\n\n";
        $text .= "🔗 <a href='" . ADMIN_URL . "/orders.php?id={$order['id']}'>Открыть заказ</a>";
        
        // Добавляем кнопки для быстрых действий
        $buttons = [
            [
                ['text' => '✅ Подтвердить', 'callback_data' => "confirm_order_{$order['id']}"],
                ['text' => '💬 Написать клиенту', 'callback_data' => "chat_order_{$order['id']}"]
            ],
            [
                ['text' => '📊 Все заказы', 'url' => ADMIN_URL . '/orders.php']
            ]
        ];
        
        return $this->sendMessageWithButtons($text, $buttons);
    }
    
    /**
     * Уведомление об изменении статуса заказа
     */
    public function notifyOrderStatusChanged($order, $oldStatus, $newStatus) {
        $statusNames = ORDER_STATUSES;
        
        $text = "🔄 <b>Изменен статус заказа</b>\n\n";
        $text .= "📋 Заказ: <code>#{$order['id']}</code>\n";
        $text .= "📊 Статус: <b>{$statusNames[$oldStatus]}</b> → <b>{$statusNames[$newStatus]}</b>\n";
        $text .= "👤 Клиент: <b>{$this->escape($order['client_name'])}</b>\n";
        $text .= "🛍️ Услуги: <b>{$this->escape($order['service_name'])}</b>\n";
        
        // Добавляем специальные сообщения для важных статусов
        $statusEmojis = [
            'ready' => "✅ <b>Заказ готов к выдаче!</b>",
            'delivered' => "🎉 <b>Заказ доставлен!</b>",
            'cancelled' => "❌ <b>Заказ отменен!</b>",
            'in_production' => "🔨 <b>Заказ в производстве</b>",
            'confirmed' => "✅ <b>Заказ подтвержден</b>"
        ];
        
        if (isset($statusEmojis[$newStatus])) {
            $text .= "\n" . $statusEmojis[$newStatus];
        }
        
        $text .= "\n⏰ Изменен: " . date('d.m.Y H:i');
        $text .= "\n\n🔗 <a href='" . ADMIN_URL . "/orders.php?id={$order['id']}'>Открыть заказ</a>";
        
        return $this->sendMessage($text);
    }
    
    /**
     * Уведомление о новом пользователе
     */
    public function notifyNewUser($user) {
        $text = "👥 <b>Новый пользователь</b>\n\n";
        $text .= "📱 Телефон: <code>{$user['phone']}</code>\n";
        
        if (!empty($user['name'])) {
            $text .= "👤 Имя: <b>{$this->escape($user['name'])}</b>\n";
        }
        
        if (!empty($user['email'])) {
            $text .= "📧 Email: <code>{$user['email']}</code>\n";
        }
        
        if (!empty($user['company_name'])) {
            $text .= "🏢 Компания: <b>{$this->escape($user['company_name'])}</b>\n";
        }
        
        $text .= "⏰ Регистрация: " . date('d.m.Y H:i') . "\n\n";
        $text .= "🔗 <a href='" . ADMIN_URL . "/users.php'>Все пользователи</a>";
        
        return $this->sendMessage($text, 'HTML', true); // Отправляем без звука
    }
    
    /**
     * Уведомление об ошибке в системе
     */
    public function notifyError($error, $context = []) {
        $text = "🚨 <b>Ошибка в системе</b>\n\n";
        $text .= "❌ Ошибка: <code>{$this->escape($error)}</code>\n";
        
        if (!empty($context)) {
            $text .= "\n📋 Контекст:\n";
            
            // Показываем только важные данные из контекста
            $importantKeys = ['file', 'line', 'user_id', 'action', 'ip'];
            foreach ($importantKeys as $key) {
                if (isset($context[$key])) {
                    $text .= "• {$key}: <code>{$this->escape($context[$key])}</code>\n";
                }
            }
        }
        
        $text .= "\n⏰ Время: " . date('d.m.Y H:i:s');
        $text .= "\n🌐 IP: " . getUserIP();
        
        return $this->sendMessage($text, 'HTML', true); // Отправляем без звука
    }
    
    /**
     * Уведомление о входе администратора
     */
    public function notifyAdminLogin($adminName, $ip) {
        $text = "🔐 <b>Вход в админ-панель</b>\n\n";
        $text .= "👤 Администратор: <b>{$this->escape($adminName)}</b>\n";
        $text .= "🌐 IP адрес: <code>{$ip}</code>\n";
        $text .= "⏰ Время: " . date('d.m.Y H:i:s');
        
        return $this->sendMessage($text, 'HTML', true); // Отправляем без звука
    }
    
    /**
     * Уведомление о критических событиях
     */
    public function notifyCriticalEvent($event, $details = '') {
        $text = "🔥 <b>КРИТИЧЕСКОЕ СОБЫТИЕ</b>\n\n";
        $text .= "⚠️ Событие: <b>{$this->escape($event)}</b>\n";
        
        if ($details) {
            $text .= "📄 Детали: <i>{$this->escape($details)}</i>\n";
        }
        
        $text .= "⏰ Время: " . date('d.m.Y H:i:s') . "\n";
        $text .= "🌐 Сервер: " . ($_SERVER['SERVER_NAME'] ?? 'unknown');
        
        return $this->sendMessage($text);
    }
    
    /**
     * Ежедневная статистика
     */
    public function sendDailyStats($stats) {
        $text = "📊 <b>Статистика за " . date('d.m.Y') . "</b>\n\n";
        
        // Заказы
        $text .= "📦 <b>Заказы:</b>\n";
        $text .= "• Новых: <b>{$stats['new_orders']}</b>\n";
        $text .= "• Выполненных: <b>{$stats['completed_orders']}</b>\n";
        $text .= "• Всего активных: <b>{$stats['active_orders']}</b>\n\n";
        
        // Финансы
        $text .= "💰 <b>Финансы:</b>\n";
        $text .= "• Доход за день: <b>{$stats['daily_revenue']} ₽</b>\n";
        $text .= "• Доход за месяц: <b>{$stats['monthly_revenue']} ₽</b>\n\n";
        
        // Клиенты
        $text .= "👥 <b>Клиенты:</b>\n";
        $text .= "• Новых пользователей: <b>{$stats['new_users']}</b>\n";
        $text .= "• Новых сообщений: <b>{$stats['new_messages']}</b>\n";
        $text .= "• Активных чатов: <b>{$stats['active_chats']}</b>\n\n";
        
        // Популярные услуги
        if (!empty($stats['popular_services'])) {
            $text .= "🏆 <b>Популярные услуги:</b>\n";
            foreach ($stats['popular_services'] as $index => $service) {
                $emoji = ['🥇', '🥈', '🥉'][$index] ?? '•';
                $text .= "{$emoji} {$this->escape($service['name'])} - {$service['count']} заказов\n";
            }
        }
        
        return $this->sendMessage($text);
    }
    
    /**
     * Уведомление о загрузке файла
     */
    public function notifyFileUpload($fileName, $fileSize, $uploadedBy, $context = '') {
        $text = "📎 <b>Загружен новый файл</b>\n\n";
        $text .= "📄 Файл: <code>{$this->escape($fileName)}</code>\n";
        $text .= "📏 Размер: <b>" . formatFileSize($fileSize) . "</b>\n";
        $text .= "👤 Загрузил: <b>{$this->escape($uploadedBy)}</b>\n";
        
        if ($context) {
            $text .= "📋 Контекст: <i>{$this->escape($context)}</i>\n";
        }
        
        $text .= "⏰ Время: " . date('d.m.Y H:i:s');
        
        return $this->sendMessage($text, 'HTML', true); // Без звука
    }
    
    /**
     * Выполнение запроса к Telegram API
     */
    private function makeRequest($method, $data) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $method);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PrintHub Bot/1.0');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($response === false || $curlError) {
                throw new Exception('CURL Error: ' . ($curlError ?: 'Unknown error'));
            }
            
            $result = json_decode($response, true);
            
            if ($httpCode !== 200) {
                $errorMsg = isset($result['description']) ? $result['description'] : "HTTP Error: {$httpCode}";
                throw new Exception($errorMsg);
            }
            
            if (!$result || !isset($result['ok']) || !$result['ok']) {
                $errorMsg = isset($result['description']) ? $result['description'] : 'Unknown Telegram API error';
                throw new Exception("Telegram API Error: {$errorMsg}");
            }
            
            logMessage("Telegram уведомление отправлено успешно: метод {$method}", 'INFO');
            
            return ['success' => true, 'result' => $result['result']];
            
        } catch (Exception $e) {
            logMessage("Ошибка отправки в Telegram: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Экранирование HTML для Telegram
     */
    private function escape($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Обрезание текста
     */
    private function truncate($text, $length) {
        if (mb_strlen($text) > $length) {
            return mb_substr($text, 0, $length) . '...';
        }
        return $text;
    }
    
    /**
     * Форматирование кнопок (Inline Keyboard)
     */
    public function createInlineKeyboard($buttons) {
        $keyboard = [];
        
        foreach ($buttons as $row) {
            $keyboardRow = [];
            foreach ($row as $button) {
                $btn = ['text' => $button['text']];
                
                if (isset($button['callback_data'])) {
                    $btn['callback_data'] = $button['callback_data'];
                }
                
                if (isset($button['url'])) {
                    $btn['url'] = $button['url'];
                }
                
                $keyboardRow[] = $btn;
            }
            $keyboard[] = $keyboardRow;
        }
        
        return json_encode(['inline_keyboard' => $keyboard]);
    }
    
    /**
     * Отправка сообщения с кнопками
     */
    public function sendMessageWithButtons($text, $buttons, $parseMode = 'HTML') {
        if (!TELEGRAM_NOTIFICATIONS_ENABLED) {
            return ['success' => false, 'message' => 'Telegram уведомления отключены'];
        }
        
        $data = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'reply_markup' => $this->createInlineKeyboard($buttons)
        ];
        
        return $this->makeRequest('sendMessage', $data);
    }
    
    /**
     * Проверка конфигурации и тест соединения
     */
    public function testConnection() {
        $testMessage = "✅ <b>Тестовое сообщение</b>\n\n";
        $testMessage .= "Telegram уведомления настроены корректно!\n";
        $testMessage .= "🤖 Бот: <code>" . $this->botToken . "</code>\n";
        $testMessage .= "💬 Чат: <code>" . $this->chatId . "</code>\n";
        $testMessage .= "⏰ Время: " . date('d.m.Y H:i:s') . "\n";
        $testMessage .= "🌐 Сервер: " . ($_SERVER['SERVER_NAME'] ?? 'localhost');
        
        return $this->sendMessage($testMessage);
    }
    
    /**
     * Получение информации о боте
     */
    public function getBotInfo() {
        return $this->makeRequest('getMe', []);
    }
    
    /**
     * Получение обновлений (для webhook)
     */
    public function getUpdates($offset = 0, $limit = 100) {
        $data = [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => 0
        ];
        
        return $this->makeRequest('getUpdates', $data);
    }
    
    /**
     * Установка webhook
     */
    public function setWebhook($url, $secretToken = null) {
        $data = ['url' => $url];
        
        if ($secretToken) {
            $data['secret_token'] = $secretToken;
        }
        
        return $this->makeRequest('setWebhook', $data);
    }
    
    /**
     * Удаление webhook
     */
    public function deleteWebhook() {
        return $this->makeRequest('deleteWebhook', []);
    }
    
    /**
     * Отправка уведомления с задержкой (для группировки)
     */
    public function sendDelayedNotification($text, $delay = 30) {
        // Простая реализация группировки уведомлений
        $cacheKey = 'delayed_notification_' . md5($text);
        $cacheFile = sys_get_temp_dir() . "/{$cacheKey}.json";
        
        $now = time();
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && ($now - $data['timestamp']) < $delay) {
                // Обновляем время последнего обращения
                $data['timestamp'] = $now;
                $data['count']++;
                file_put_contents($cacheFile, json_encode($data));
                return ['success' => true, 'message' => 'Уведомление отложено'];
            }
        }
        
        // Отправляем уведомление
        $result = $this->sendMessage($text);
        
        // Сохраняем информацию об отправке
        file_put_contents($cacheFile, json_encode([
            'timestamp' => $now,
            'count' => 1
        ]));
        
        return $result;
    }
    
    /**
     * Очистка кэша отложенных уведомлений
     */
    public function cleanupDelayedNotifications() {
        $files = glob(sys_get_temp_dir() . '/delayed_notification_*.json');
        $now = time();
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && ($now - $data['timestamp']) > 3600) { // 1 час
                unlink($file);
                $cleaned++;
            }
        }
        
        if ($cleaned > 0) {
            logMessage("Очищено {$cleaned} файлов отложенных уведомлений", 'INFO');
        }
        
        return $cleaned;
    }
}
?>