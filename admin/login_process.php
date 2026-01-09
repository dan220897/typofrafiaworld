<?php
// admin/login_process.php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Проверка на пустые поля
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Защита от брутфорса
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    if (time() - $_SESSION['last_attempt'] < 300) { // 5 минут блокировки
        echo json_encode(['success' => false, 'message' => 'Слишком много попыток. Попробуйте через 5 минут.']);
        exit;
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $admin = new Admin($db);
    
    $admin_data = $admin->login($username, $password);
    
    if ($admin_data) {
        // Успешный вход
        $_SESSION['admin_id'] = $admin_data['id'];
        $_SESSION['admin_username'] = $admin_data['username'];
        $_SESSION['admin_name'] = $admin_data['full_name'];
        $_SESSION['admin_role'] = $admin_data['role'];
        $_SESSION['admin_email'] = $admin_data['email'];
        $_SESSION['login_time'] = time();
        
        // Сброс счетчика попыток
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt']);
        
        // Если выбрано "Запомнить меня"
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('admin_remember', $token, time() + (86400 * 30), '/admin/', '', true, true);
            
            // Сохраняем токен в БД (нужно добавить поле remember_token в таблицу admins)
            $update_query = "UPDATE admins SET remember_token = :token WHERE id = :id";
            $stmt = $db->prepare($update_query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':id', $admin_data['id']);
            $stmt->execute();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Успешный вход',
            'redirect' => 'index.php'
        ]);
    } else {
        // Неудачный вход
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_attempt'] = time();
        
        echo json_encode([
            'success' => false,
            'message' => 'Неверный логин или пароль'
        ]);
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка системы. Попробуйте позже.'
    ]);
}

// admin/logout.php
session_start();
session_destroy();

// Удаляем cookie
setcookie('admin_remember', '', time() - 3600, '/admin/');

header('Location: login.php');
exit;

// admin/assets/js/chat-realtime.js
class ChatManager {
    constructor() {
        this.currentChatId = null;
        this.currentUserId = null;
        this.pollInterval = null;
        this.lastMessageId = 0;
        this.isTyping = false;
        this.typingTimeout = null;
        
        this.init();
    }
    
    init() {
        // Инициализация элементов
        this.chatsList = document.querySelector('.chats-wrapper');
        this.messagesContainer = document.getElementById('chatMessages');
        this.messageInput = document.querySelector('.chat-input');
        this.sendButton = document.querySelector('.send-btn');
        
        // Привязка событий
        this.bindEvents();
        
        // Загрузка списка чатов
        this.loadChats();
        
        // Запуск polling для новых сообщений
        this.startPolling();
    }
    
    bindEvents() {
        // Отправка сообщения
        if (this.sendButton) {
            this.sendButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
        }
        
        // Отправка по Enter
        if (this.messageInput) {
            this.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            // Индикатор набора текста
            this.messageInput.addEventListener('input', () => {
                this.handleTyping();
            });
        }
        
        // Переключение чатов
        document.addEventListener('click', (e) => {
            const chatItem = e.target.closest('.chat-item');
            if (chatItem) {
                const chatId = chatItem.dataset.chatId;
                this.openChat(chatId);
            }
        });
        
        // Загрузка файлов
        const fileBtn = document.querySelector('.input-action-btn');
        if (fileBtn) {
            fileBtn.addEventListener('click', () => {
                this.selectFile();
            });
        }
    }
    
    async loadChats() {
        try {
            const response = await fetch('/admin/api/chats.php');
            const data = await response.json();
            
            if (data.success) {
                this.renderChats(data.chats);
                
                // Открываем первый чат если есть
                if (data.chats.length > 0 && !this.currentChatId) {
                    this.openChat(data.chats[0].id);
                }
            }
        } catch (error) {
            console.error('Ошибка загрузки чатов:', error);
        }
    }
    
    renderChats(chats) {
        if (!this.chatsList) return;
        
        this.chatsList.innerHTML = chats.map(chat => `
            <div class="chat-item ${chat.id == this.currentChatId ? 'active' : ''}" data-chat-id="${chat.id}">
                <div class="chat-item-header">
                    <div class="chat-user-name">${this.escapeHtml(chat.user_name || 'Без имени')}</div>
                    <div class="chat-time">${this.formatTime(chat.last_message_time)}</div>
                </div>
                <div class="chat-preview">${this.escapeHtml(chat.last_message || 'Нет сообщений')}</div>
                <div class="chat-meta">
                    ${chat.unread_admin_count > 0 ? `<span class="chat-badge badge-unread">${chat.unread_admin_count}</span>` : ''}
                    ${chat.orders_count > 0 ? `<span class="chat-badge badge-order">Заказов: ${chat.orders_count}</span>` : ''}
                </div>
            </div>
        `).join('');
    }
    
    async openChat(chatId) {
        if (this.currentChatId == chatId) return;
        
        this.currentChatId = chatId;
        this.lastMessageId = 0;
        
        // Обновляем активный чат в списке
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.toggle('active', item.dataset.chatId == chatId);
        });
        
        // Загружаем сообщения
        await this.loadMessages();
        
        // Отмечаем как прочитанные
        this.markAsRead();
        
        // Загружаем информацию о пользователе
        this.loadUserInfo();
    }
    
    async loadMessages() {
        if (!this.currentChatId || !this.messagesContainer) return;
        
        try {
            const response = await fetch(`/admin/api/chats.php?id=${this.currentChatId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderMessages(data.messages);
                
                // Запоминаем последнее сообщение
                if (data.messages.length > 0) {
                    this.lastMessageId = Math.max(...data.messages.map(m => m.id));
                }
                
                // Прокручиваем вниз
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Ошибка загрузки сообщений:', error);
        }
    }
    
    renderMessages(messages) {
        if (!this.messagesContainer) return;
        
        const messagesHtml = messages.map(msg => {
            if (msg.message_type === 'system') {
                return `
                    <div class="system-message">
                        <span>${this.escapeHtml(msg.message_text)}</span>
                    </div>
                `;
            }
            
            const isSent = msg.sender_type === 'admin';
            const avatar = isSent ? 'А' : (msg.sender_name ? msg.sender_name.substring(0, 2).toUpperCase() : 'П');
            
            let attachmentsHtml = '';
            if (msg.attachments && msg.attachments.length > 0) {
                attachmentsHtml = msg.attachments.map(att => `
                    <div class="message-attachment">
                        <i class="fas fa-file"></i>
                        <span>${this.escapeHtml(att.name)}</span>
                    </div>
                `).join('');
            }
            
            return `
                <div class="message ${isSent ? 'sent' : ''}" data-message-id="${msg.id}">
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-content">
                        <div class="message-bubble">
                            <div class="message-text">${this.escapeHtml(msg.message_text)}</div>
                            ${attachmentsHtml}
                        </div>
                        <div class="message-time">${this.formatTime(msg.created_at)}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        this.messagesContainer.innerHTML = messagesHtml;
    }
    
    async sendMessage() {
        const message = this.messageInput.value.trim();
        if (!message || !this.currentChatId) return;
        
        // Блокируем отправку
        this.messageInput.disabled = true;
        this.sendButton.disabled = true;
        
        try {
            const response = await fetch('/admin/api/chats.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chat_id: this.currentChatId,
                    message: message,
                    type: 'text'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Очищаем поле
                this.messageInput.value = '';
                this.messageInput.style.height = 'auto';
                
                // Добавляем сообщение в чат
                this.addMessageToChat({
                    id: data.message_id,
                    sender_type: 'admin',
                    message_text: message,
                    created_at: new Date().toISOString()
                });
                
                // Обновляем список чатов
                this.loadChats();
            }
        } catch (error) {
            console.error('Ошибка отправки сообщения:', error);
            this.showNotification('Ошибка отправки сообщения', 'error');
        } finally {
            this.messageInput.disabled = false;
            this.sendButton.disabled = false;
            this.messageInput.focus();
        }
    }
    
    addMessageToChat(message) {
        if (!this.messagesContainer) return;
        
        const isSent = message.sender_type === 'admin';
        const avatar = isSent ? 'А' : 'П';
        
        const messageHtml = `
            <div class="message ${isSent ? 'sent' : ''}" data-message-id="${message.id}">
                <div class="message-avatar">${avatar}</div>
                <div class="message-content">
                    <div class="message-bubble">
                        <div class="message-text">${this.escapeHtml(message.message_text)}</div>
                    </div>
                    <div class="message-time">${this.formatTime(message.created_at)}</div>
                </div>
            </div>
        `;
        
        this.messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        this.scrollToBottom();
        
        // Обновляем lastMessageId
        if (message.id > this.lastMessageId) {
            this.lastMessageId = message.id;
        }
    }
    
    async markAsRead() {
        if (!this.currentChatId) return;
        
        try {
            await fetch(`/admin/api/chats.php?id=${this.currentChatId}&action=read`, {
                method: 'PUT'
            });
        } catch (error) {
            console.error('Ошибка отметки прочтения:', error);
        }
    }
    
    startPolling() {
        // Проверка новых сообщений каждые 3 секунды
        this.pollInterval = setInterval(() => {
            this.checkNewMessages();
        }, 3000);
    }
    
    async checkNewMessages() {
        if (!this.currentChatId) return;
        
        try {
            const response = await fetch(`/admin/api/chats.php?id=${this.currentChatId}&after_id=${this.lastMessageId}`);
            const data = await response.json();
            
            if (data.success && data.messages.length > 0) {
                // Добавляем новые сообщения
                data.messages.forEach(msg => {
                    if (msg.id > this.lastMessageId) {
                        this.addMessageToChat(msg);
                    }
                });
                
                // Воспроизводим звук уведомления
                this.playNotificationSound();
                
                // Обновляем список чатов
                this.loadChats();
            }
        } catch (error) {
            console.error('Ошибка проверки новых сообщений:', error);
        }
    }
    
    handleTyping() {
        if (!this.isTyping) {
            this.isTyping = true;
            // Отправляем индикатор набора текста
            // this.sendTypingIndicator(true);
        }
        
        clearTimeout(this.typingTimeout);
        this.typingTimeout = setTimeout(() => {
            this.isTyping = false;
            // this.sendTypingIndicator(false);
        }, 1000);
    }
    
    selectFile() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*,.pdf,.doc,.docx,.xls,.xlsx';
        
        input.onchange = async (e) => {
            const file = e.target.files[0];
            if (file) {
                await this.uploadFile(file);
            }
        };
        
        input.click();
    }
    
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('chat_id', this.currentChatId);
        
        try {
            const response = await fetch('/admin/api/upload.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Отправляем сообщение с файлом
                await this.sendMessage(`Файл: ${file.name}`, 'file', {
                    file_id: data.file_id,
                    file_name: file.name,
                    file_size: file.size
                });
            }
        } catch (error) {
            console.error('Ошибка загрузки файла:', error);
            this.showNotification('Ошибка загрузки файла', 'error');
        }
    }
    
    playNotificationSound() {
        const audio = new Audio('/admin/assets/sounds/notification.mp3');
        audio.play().catch(e => console.log('Не удалось воспроизвести звук:', e));
    }
    
    showNotification(message, type = 'info') {
        // Реализация показа уведомлений
        console.log(`${type}: ${message}`);
    }
    
    scrollToBottom() {
        if (this.messagesContainer) {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }
    }
    
    formatTime(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) { // менее минуты
            return 'только что';
        } else if (diff < 3600000) { // менее часа
            return Math.floor(diff / 60000) + ' мин';
        } else if (diff < 86400000) { // менее суток
            return Math.floor(diff / 3600000) + ' ч';
        } else {
            return date.toLocaleDateString('ru-RU');
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    destroy() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
        
        if (this.typingTimeout) {
            clearTimeout(this.typingTimeout);
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.chat-container')) {
        window.chatManager = new ChatManager();
    }
});

// Очистка при уходе со страницы
window.addEventListener('beforeunload', () => {
    if (window.chatManager) {
        window.chatManager.destroy();
    }
});
?>