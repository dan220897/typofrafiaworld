// Настройки чата
const CHAT_CONFIG = {
    pollingInterval: 3000, // 3 секунды
    maxRetries: 3,
    retryDelay: 5000,
    messageSound: true,
    autoScroll: true,
    typingTimeout: 1000
};

// Класс для управления чатом
class ChatRealtime {
    constructor(chatId, options = {}) {
        this.chatId = chatId;
        this.options = { ...CHAT_CONFIG, ...options };
        this.lastMessageId = 0;
        this.isPolling = false;
        this.retryCount = 0;
        this.typingTimer = null;
        this.isTyping = false;
        
        // Элементы DOM
        this.messagesContainer = document.getElementById('chat-messages');
        this.messageInput = document.getElementById('message-input');
        this.sendButton = document.getElementById('send-button');
        this.typingIndicator = document.getElementById('typing-indicator');
        this.connectionStatus = document.getElementById('connection-status');
        
        // Звук уведомления
        this.notificationSound = new Audio('/admin/assets/sounds/notification.mp3');
        
        this.init();
    }
    
    init() {
        // Привязка событий
        if (this.sendButton) {
            this.sendButton.addEventListener('click', () => this.sendMessage());
        }
        
        if (this.messageInput) {
            this.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                } else {
                    this.handleTyping();
                }
            });
            
            // Автофокус на поле ввода
            this.messageInput.focus();
        }
        
        // Начинаем polling
        this.startPolling();
        
        // Загружаем историю сообщений
        this.loadMessages();
        
        // Обработка закрытия страницы
        window.addEventListener('beforeunload', () => this.stopPolling());
    }
    
    // Запуск polling
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollInterval = setInterval(() => this.checkNewMessages(), this.options.pollingInterval);
        this.updateConnectionStatus('online');
    }
    
    // Остановка polling
    stopPolling() {
        if (!this.isPolling) return;
        
        this.isPolling = false;
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
        this.updateConnectionStatus('offline');
    }
    
    // Проверка новых сообщений
    async checkNewMessages() {
        try {
            const response = await fetch(`/admin/api/chats.php?action=get_new_messages&chat_id=${this.chatId}&last_id=${this.lastMessageId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.messages && data.messages.length > 0) {
                this.retryCount = 0;
                this.renderNewMessages(data.messages);
                
                // Воспроизводим звук для новых сообщений от клиента
                if (this.options.messageSound) {
                    const hasClientMessage = data.messages.some(msg => msg.sender_type === 'client');
                    if (hasClientMessage) {
                        this.playNotificationSound();
                    }
                }
            }
            
            // Обновляем статус набора текста
            if (data.typing_status !== undefined) {
                this.updateTypingStatus(data.typing_status);
            }
            
        } catch (error) {
            console.error('Error checking new messages:', error);
            this.handlePollingError();
        }
    }
    
    // Загрузка истории сообщений
    async loadMessages() {
        try {
            const response = await fetch(`/admin/api/chats.php?action=get_messages&chat_id=${this.chatId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.messages) {
                this.renderMessages(data.messages);
                this.scrollToBottom();
            }
            
        } catch (error) {
            console.error('Error loading messages:', error);
            this.showError('Ошибка загрузки сообщений');
        }
    }
    
    // Отправка сообщения
    async sendMessage() {
        const message = this.messageInput.value.trim();
        if (!message) return;
        
        // Блокируем кнопку отправки
        this.setSendButtonState(false);
        
        try {
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('chat_id', this.chatId);
            formData.append('message', message);
            
            const response = await fetch('/admin/api/chats.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Очищаем поле ввода
                this.messageInput.value = '';
                
                // Добавляем сообщение в чат
                if (data.message) {
                    this.renderNewMessages([data.message]);
                }
            } else {
                this.showError(data.message || 'Ошибка отправки сообщения');
            }
            
        } catch (error) {
            console.error('Error sending message:', error);
            this.showError('Ошибка отправки сообщения');
        } finally {
            this.setSendButtonState(true);
            this.messageInput.focus();
        }
    }
    
    // Обработка набора текста
    handleTyping() {
        if (!this.isTyping) {
            this.isTyping = true;
            this.sendTypingStatus(true);
        }
        
        // Сбрасываем таймер
        if (this.typingTimer) {
            clearTimeout(this.typingTimer);
        }
        
        // Устанавливаем новый таймер
        this.typingTimer = setTimeout(() => {
            this.isTyping = false;
            this.sendTypingStatus(false);
        }, this.options.typingTimeout);
    }
    
    // Отправка статуса набора текста
    async sendTypingStatus(isTyping) {
        try {
            const formData = new FormData();
            formData.append('action', 'update_typing');
            formData.append('chat_id', this.chatId);
            formData.append('is_typing', isTyping ? '1' : '0');
            
            await fetch('/admin/api/chats.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
        } catch (error) {
            console.error('Error sending typing status:', error);
        }
    }
    
    // Отображение сообщений
    renderMessages(messages) {
        this.messagesContainer.innerHTML = '';
        messages.forEach(message => this.renderMessage(message));
        
        if (messages.length > 0) {
            this.lastMessageId = Math.max(...messages.map(m => m.id));
        }
    }
    
    // Отображение новых сообщений
    renderNewMessages(messages) {
        messages.forEach(message => {
            if (message.id > this.lastMessageId) {
                this.renderMessage(message);
                this.lastMessageId = message.id;
            }
        });
        
        if (this.options.autoScroll) {
            this.scrollToBottom();
        }
    }
    
    // Отображение одного сообщения
    renderMessage(message) {
        const messageEl = document.createElement('div');
        messageEl.className = `message ${message.sender_type}`;
        messageEl.dataset.messageId = message.id;
        
        const time = new Date(message.created_at).toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const senderName = message.sender_type === 'admin' ? message.admin_name : 'Клиент';
        
        messageEl.innerHTML = `
            <div class="message-header">
                <span class="sender">${this.escapeHtml(senderName)}</span>
                <span class="time">${time}</span>
            </div>
            <div class="message-body">${this.escapeHtml(message.message)}</div>
            ${message.is_read ? '<span class="read-indicator">✓✓</span>' : ''}
        `;
        
        this.messagesContainer.appendChild(messageEl);
    }
    
    // Обновление статуса набора текста
    updateTypingStatus(isTyping) {
        if (this.typingIndicator) {
            this.typingIndicator.style.display = isTyping ? 'block' : 'none';
        }
    }
    
    // Обновление статуса соединения
    updateConnectionStatus(status) {
        if (this.connectionStatus) {
            this.connectionStatus.className = `connection-status ${status}`;
            this.connectionStatus.textContent = status === 'online' ? 'Подключено' : 'Отключено';
        }
    }
    
    // Обработка ошибок polling
    handlePollingError() {
        this.retryCount++;
        
        if (this.retryCount >= this.options.maxRetries) {
            this.stopPolling();
            this.showError('Потеряно соединение с сервером');
            
            // Пытаемся переподключиться через некоторое время
            setTimeout(() => {
                this.retryCount = 0;
                this.startPolling();
            }, this.options.retryDelay);
        }
    }
    
    // Прокрутка вниз
    scrollToBottom() {
        if (this.messagesContainer) {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }
    }
    
    // Воспроизведение звука уведомления
    playNotificationSound() {
        try {
            this.notificationSound.play().catch(e => {
                console.log('Cannot play notification sound:', e);
            });
        } catch (e) {
            console.log('Error playing notification sound:', e);
        }
    }
    
    // Установка состояния кнопки отправки
    setSendButtonState(enabled) {
        if (this.sendButton) {
            this.sendButton.disabled = !enabled;
            this.sendButton.textContent = enabled ? 'Отправить' : 'Отправка...';
        }
    }
    
    // Показ ошибки
    showError(message) {
        const errorEl = document.createElement('div');
        errorEl.className = 'alert alert-danger';
        errorEl.textContent = message;
        
        this.messagesContainer.appendChild(errorEl);
        
        setTimeout(() => {
            errorEl.remove();
        }, 5000);
    }
    
    // Экранирование HTML
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Получаем ID чата из URL или атрибута
    const chatId = getChatIdFromUrl() || document.body.dataset.chatId;
    
    if (chatId) {
        window.chatRealtime = new ChatRealtime(chatId);
    }
});

// Получение ID чата из URL
function getChatIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ChatRealtime;
}