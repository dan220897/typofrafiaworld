<?php
require_once dirname(__DIR__) . '/config/config.php';

class TelegramNotifier {
    private $botToken;
    private $chatId;
    private $apiUrl;
    
    public function __construct() {
        // Используем правильные имена констант
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
        $text = "📦 <b>Новый заказ #{$order['id']}</b>\n\n";
        $text .= "👤 Клиент: <b>{$this->escape($order['client_name'])}</b>\n";
        $text .= "📱 Телефон: <code>{$order['client_phone']}</code>\n";
        $text .= "🛍️ Услуга: <b>{$this->escape($order['service_name'])}</b>\n";
        
        if (!empty($order['description'])) {
            $text .= "📝 Описание: <i>{$this->escape($this->truncate($order['description'], 150))}</i>\n";
        }
        
        if (!empty($order['price'])) {
            $text .= "💰 Сумма: <b>{$order['price']} ₽</b>\n";
        }
        
        $text .= "\n🔗 <a href='" . ADMIN_URL . "/orders.php?id={$order['id']}'>Открыть заказ</a>";
        
        return $this->sendMessage($text);
    }
    
    /**
     * Уведомление об изменении статуса заказа
     */
    public function notifyOrderStatusChanged($order, $oldStatus, $newStatus) {
        $statusNames = ORDER_STATUSES;
        
        $text = "🔄 <b>Изменен статус заказа #{$order['id']}</b>\n\n";
        $text .= "📊 Статус: <b>{$statusNames[$oldStatus]}</b> → <b>{$statusNames[$newStatus]}</b>\n";
        $text .= "👤 Клиент: <b>{$this->escape($order['client_name'])}</b>\n";
        $text .= "🛍️ Услуга: <b>{$this->escape($order['service_name'])}</b>\n";
        
        if ($newStatus === 'ready') {
            $text .= "\n✅ <b>Заказ готов к выдаче!</b>";
        } elseif ($newStatus === 'delivered') {
            $text .= "\n🎉 <b>Заказ доставлен!</b>";
        } elseif ($newStatus === 'cancelled') {
            $text .= "\n❌ <b>Заказ отменен!</b>";
        }
        
        $text .= "\n\n🔗 <a href='" . ADMIN_URL . "/orders.php?id={$order['id']}'>Открыть заказ</a>";
        
        return $this->sendMessage($text);
    }
    
    /**
     * Уведомление об ошибке в системе
     */
    public function notifyError($error, $context = []) {
        $text = "🚨 <b>Ошибка в системе</b>\n\n";
        $text .= "❌ Ошибка: <code>{$this->escape($error)}</code>\n";
        
        if (!empty($context)) {
            $text .= "\n📋 Контекст:\n<pre>" . $this->escape(json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        }
        
        $text .= "\n⏰ Время: " . date('d.m.Y H:i:s');
        
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
     * Ежедневная статистика
     */
    public function sendDailyStats($stats) {
        $text = "📊 <b>Статистика за " . date('d.m.Y') . "</b>\n\n";
        $text .= "📦 Новых заказов: <b>{$stats['new_orders']}</b>\n";
        $text .= "✅ Выполненных заказов: <b>{$stats['completed_orders']}</b>\n";
        $text .= "💰 Общая сумма: <b>{$stats['total_revenue']} ₽</b>\n";
        $text .= "💬 Новых сообщений: <b>{$stats['new_messages']}</b>\n";
        $text .= "👥 Новых клиентов: <b>{$stats['new_clients']}</b>\n\n";
        
        if ($stats['popular_services']) {
            $text .= "🏆 <b>Популярные услуги:</b>\n";
            foreach ($stats['popular_services'] as $service) {
                $text .= "• {$this->escape($service['name'])} - {$service['count']} заказов\n";
            }
        }
        
        return $this->sendMessage($text);
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
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response === false) {
                throw new Exception('CURL Error: ' . curl_error($ch));
            }
            
            $result = json_decode($response, true);
            
            if ($httpCode !== 200 || !$result['ok']) {
                $errorMsg = isset($result['description']) ? $result['description'] : 'Unknown error';
                throw new Exception("Telegram API Error: {$errorMsg}");
            }
            
            return ['success' => true, 'result' => $result['result']];
            
        } catch (Exception $e) {
            if (function_exists('logMessage')) {
                logMessage("Ошибка отправки в Telegram: " . $e->getMessage(), 'ERROR');
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Экранирование HTML для Telegram
     */
    private function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
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
                $keyboardRow[] = [
                    'text' => $button['text'],
                    'callback_data' => isset($button['callback_data']) ? $button['callback_data'] : null,
                    'url' => isset($button['url']) ? $button['url'] : null
                ];
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
     * Проверка конфигурации
     */
    public function testConnection() {
        $testMessage = "✅ Тестовое сообщение\n\n";
        $testMessage .= "Telegram уведомления настроены корректно!\n";
        $testMessage .= "Время: " . date('d.m.Y H:i:s');
        
        return $this->sendMessage($testMessage);
    }
}
?>