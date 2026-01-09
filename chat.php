<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;

// Если не авторизован - редирект
if (!$isAuthenticated) {
    header('Location: /?showAuth=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат с поддержкой - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--light-gray);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: var(--white);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .chat-title h1 {
            font-size: 1.5rem;
        }

        .back-btn {
            background: var(--light-gray);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: var(--dark);
        }

        .back-btn:hover {
            background: var(--gray);
            color: var(--white);
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: var(--white);
            box-shadow: var(--shadow);
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            max-width: 70%;
            padding: 1rem;
            border-radius: 12px;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.user {
            align-self: flex-end;
            background: var(--primary);
            color: var(--white);
        }

        .message.admin {
            align-self: flex-start;
            background: var(--light-gray);
            color: var(--dark);
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.5rem;
        }

        .input-container {
            padding: 1.5rem;
            border-top: 1px solid var(--light-gray);
            display: flex;
            gap: 1rem;
        }

        .message-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 1rem;
            resize: none;
            font-family: inherit;
        }

        .message-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .send-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }

        .send-btn:hover {
            background: var(--primary-hover);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .message {
                max-width: 85%;
            }

            .chat-header {
                padding: 1rem;
            }

            .messages-container {
                padding: 1rem;
            }

            .input-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="chat-header">
        <div class="chat-title">
            <h1><i class="fas fa-comments"></i> Чат с поддержкой</h1>
        </div>
        <a href="/" class="back-btn">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="chat-container">
        <div class="messages-container" id="messagesContainer">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Загрузка сообщений...
            </div>
        </div>

        <div class="input-container">
            <textarea
                class="message-input"
                id="messageInput"
                placeholder="Введите сообщение..."
                rows="1"
                maxlength="1000"
            ></textarea>
            <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i> Отправить
            </button>
        </div>
    </div>

    <script>
        let currentChatId = null;
        let messagesLoaded = false;

        // Загрузка чата при старте
        window.addEventListener('load', () => {
            loadOrCreateChat();
            // Обновление сообщений каждые 3 секунды
            setInterval(loadMessages, 3000);
        });

        // Enter для отправки (Shift+Enter для новой строки)
        document.getElementById('messageInput').addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Загрузить или создать чат
        async function loadOrCreateChat() {
            try {
                const response = await fetch('/api/chat.php?action=get_or_create');
                const data = await response.json();

                if (data.success && data.chat) {
                    currentChatId = data.chat.id;
                    await loadMessages();
                }
            } catch (error) {
                console.error('Ошибка загрузки чата:', error);
                showError('Не удалось загрузить чат');
            }
        }

        // Загрузить сообщения
        async function loadMessages() {
            if (!currentChatId) return;

            try {
                const response = await fetch(`/api/chat.php?action=get_messages&chat_id=${currentChatId}`);
                const data = await response.json();

                if (data.success) {
                    displayMessages(data.messages || []);
                    messagesLoaded = true;
                }
            } catch (error) {
                console.error('Ошибка загрузки сообщений:', error);
            }
        }

        // Отобразить сообщения
        function displayMessages(messages) {
            const container = document.getElementById('messagesContainer');

            if (messages.length === 0 && messagesLoaded) {
                container.innerHTML = `
                    <div class="loading">
                        <p>Нет сообщений. Напишите первым!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = messages.map(msg => `
                <div class="message ${msg.sender_type === 'user' ? 'user' : 'admin'}">
                    <div class="message-text">${escapeHtml(msg.message_text)}</div>
                    <div class="message-time">${formatTime(msg.created_at)}</div>
                </div>
            `).join('');

            // Прокрутка вниз
            container.scrollTop = container.scrollHeight;
        }

        // Отправить сообщение
        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message || !currentChatId) return;

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;

            try {
                const response = await fetch('/api/chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'send_message',
                        chat_id: currentChatId,
                        message: message
                    })
                });

                const data = await response.json();

                if (data.success) {
                    input.value = '';
                    await loadMessages();
                } else {
                    alert('Ошибка отправки: ' + data.error);
                }
            } catch (error) {
                console.error('Ошибка отправки сообщения:', error);
                alert('Не удалось отправить сообщение');
            } finally {
                sendBtn.disabled = false;
                input.focus();
            }
        }

        // Форматирование времени
        function formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;

            if (diff < 60000) return 'только что';
            if (diff < 3600000) return Math.floor(diff / 60000) + ' мин назад';
            if (diff < 86400000) {
                return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            }
            return date.toLocaleDateString('ru-RU') + ' ' + date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        }

        // Экранирование HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Показать ошибку
        function showError(message) {
            document.getElementById('messagesContainer').innerHTML = `
                <div class="loading">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>${message}</p>
                </div>
            `;
        }
    </script>
</body>
</html>
