<?php
// admin/chats.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Chat.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Admin.php';
require_once __DIR__ . '/classes/TelegramNotifier.php';

// Проверка авторизации
checkAuth();

// Инициализация базы данных
$database = new Database();
$db = $database->getConnection();

// Создаем объекты для работы
$chat = new Chat($db);
$user = new User($db);

// Получаем параметры из URL
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$chat_id = $_GET['id'] ?? null;

// Если есть поиск
if (!empty($search)) {
    $chats = $chat->searchChats($search);
} else {
    // Получаем список чатов
    $chats = $chat->getChats(null, $status_filter);
}

// Получаем статистику чатов
$chat_stats = $chat->getChatStats();

// Если выбран конкретный чат
$current_chat = null;
$messages = [];
$chat_user = null;

if ($chat_id) {
    $current_chat = $chat->getChatById($chat_id);
    if ($current_chat) {
        $messages = $chat->getMessages($chat_id);
        $chat_user = $user->getUserById($current_chat['user_id']);
        // Отмечаем сообщения как прочитанные
        $chat->markAsRead($chat_id, 'admin');
    }
}

$current_page = 'chats';
include 'includes/header.php';
?>

<div class="chats-container">
    <!-- Боковая панель со списком чатов -->
    <div class="chats-sidebar">
        <div class="chats-header">
            <h3>Чаты</h3>
            <div class="chats-stats">
                <span class="stat-item">
                    <i class="fas fa-comment-dots"></i>
                    <?php echo $chat_stats['total'] ?? 0; ?> всего
                </span>
                <?php if ($chat_stats['unread'] > 0): ?>
                <span class="stat-item unread">
                    <i class="fas fa-envelope"></i>
                    <?php echo $chat_stats['unread']; ?> новых
                </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Поиск и фильтры -->
        <div class="chats-controls">
            <div class="search-box">
                <input type="text" 
                       id="chatSearch" 
                       placeholder="Поиск по чатам..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="form-control">
                <i class="fas fa-search"></i>
            </div>
            
            <div class="filter-tabs">
                <a href="?status=all" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">
                    Все чаты
                </a>
                <a href="?status=active" class="filter-tab <?php echo $status_filter == 'active' ? 'active' : ''; ?>">
                    Активные
                </a>
                <a href="?status=closed" class="filter-tab <?php echo $status_filter == 'closed' ? 'active' : ''; ?>">
                    Закрытые
                </a>
            </div>
        </div>
        
        <!-- Список чатов -->
        <!-- Список чатов -->
<div class="chats-list" id="chatsList">
    <?php if (empty($chats)): ?>
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <p>Нет чатов</p>
        </div>
    <?php else: ?>
        <?php foreach ($chats as $chat_item): ?>
            <div class="chat-item <?php echo $chat_id == $chat_item['id'] ? 'active' : ''; ?> 
                        <?php echo $chat_item['unread_admin_count'] > 0 ? 'unread' : ''; ?>
                        chat-status-<?php echo $chat_item['client_status'] ?? 'new'; ?>" 
                 data-chat-id="<?php echo $chat_item['id']; ?>">
                
                <div class="chat-avatar" onclick="openChat(<?php echo $chat_item['id']; ?>)">
                   <?php echo mb_substr($chat_item['user_email'] ?? $chat_item['user_name'] ?? 'П', 0, 1); ?>
                </div>
                
                <div class="chat-info" onclick="openChat(<?php echo $chat_item['id']; ?>)">
                    <div class="chat-header">
                        <h4><?php echo htmlspecialchars($chat_item['user_email'] ?? $chat_item['user_name'] ?? 'Неизвестный'); ?></h4>
                        <span class="chat-time">
                            <?php echo formatTime($chat_item['last_message_time']); ?>
                        </span>
                    </div>
                    
                    <div class="chat-preview">
                        <?php echo htmlspecialchars(mb_substr($chat_item['last_message'] ?? 'Нет сообщений', 0, 50)); ?>
                        <?php if (mb_strlen($chat_item['last_message'] ?? '') > 50): ?>...<?php endif; ?>
                    </div>
                    
                    <div class="chat-meta">
                        <?php if ($chat_item['unread_admin_count'] > 0): ?>
                            <span class="badge badge-primary"><?php echo $chat_item['unread_admin_count']; ?></span>
                        <?php endif; ?>
                        
                        <?php if ($chat_item['orders_count'] > 0): ?>
                            <span class="badge badge-info">
                                <i class="fas fa-shopping-cart"></i> <?php echo $chat_item['orders_count']; ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($chat_item['status'] == 'closed'): ?>
                            <span class="badge badge-secondary">Закрыт</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Выпадающее меню статуса и удаление -->
                <div class="chat-item-actions" onclick="event.stopPropagation();">
                    <div class="status-dropdown">
                        <button class="status-btn" onclick="toggleStatusMenu(<?php echo $chat_item['id']; ?>)">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="status-menu" id="statusMenu<?php echo $chat_item['id']; ?>">
                            <div class="status-menu-header">Статус клиента:</div>
                            <button class="status-option" data-status="new" onclick="changeClientStatus(<?php echo $chat_item['id']; ?>, 'new')">
                                <span class="status-dot status-new"></span> Новый
                            </button>
                            <button class="status-option" data-status="in_progress" onclick="changeClientStatus(<?php echo $chat_item['id']; ?>, 'in_progress')">
                                <span class="status-dot status-in-progress"></span> В обработке
                            </button>
                            <button class="status-option" data-status="waiting_client" onclick="changeClientStatus(<?php echo $chat_item['id']; ?>, 'waiting_client')">
                                <span class="status-dot status-waiting"></span> Клиент думает
                            </button>
                            <button class="status-option" data-status="no_response" onclick="changeClientStatus(<?php echo $chat_item['id']; ?>, 'no_response')">
                                <span class="status-dot status-no-response"></span> Нет ответа
                            </button>
                            <button class="status-option" data-status="resolved" onclick="changeClientStatus(<?php echo $chat_item['id']; ?>, 'resolved')">
                                <span class="status-dot status-resolved"></span> Решено
                            </button>
                            <div class="status-menu-divider"></div>
                            <button class="status-option danger" onclick="deleteChat(<?php echo $chat_item['id']; ?>)">
                                <i class="fas fa-trash"></i> Удалить чат
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
    </div>
    
    <!-- Область чата -->
    <div class="chat-area">
        <?php if ($current_chat): ?>
            <!-- Заголовок чата -->
            <div class="chat-header">
                <div class="chat-user-info">
                    <h3><?php echo htmlspecialchars($chat_user['name'] ?? 'Пользователь'); ?></h3>
                    <div class="user-details">
                        <?php if ($chat_user['phone']): ?>
                            <span><i class="fas fa-phone"></i> <?php echo formatPhone($chat_user['phone']); ?></span>
                        <?php endif; ?>
                        <?php if ($chat_user['email']): ?>
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($chat_user['email']); ?></span>
                        <?php endif; ?>
                        <?php if ($chat_user['company_name']): ?>
                            <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($chat_user['company_name']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="chat-actions">
                    <button class="btn btn-secondary btn-sm" onclick="showUserInfo(<?php echo $current_chat['user_id']; ?>)">
                        <i class="fas fa-info-circle"></i> Информация
                    </button>
                    
                    <?php if ($current_chat['status'] == 'active'): ?>
                        <button class="btn btn-warning btn-sm" onclick="closeChat(<?php echo $current_chat['id']; ?>)">
                            <i class="fas fa-times"></i> Закрыть чат
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success btn-sm" onclick="reopenChat(<?php echo $current_chat['id']; ?>)">
                            <i class="fas fa-redo"></i> Открыть чат
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Сообщения -->
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($messages as $message): ?>
                    <?php if ($message['message_type'] == 'system'): ?>
                        <div class="system-message">
                            <span><?php echo htmlspecialchars($message['message_text']); ?></span>
                            <time><?php echo formatTime($message['created_at']); ?></time>
                        </div>
                    <?php else: ?>
                        <div class="message <?php echo $message['sender_type'] == 'admin' ? 'sent' : 'received'; ?>" data-message-id="<?php echo $message['id']; ?>">
                            <div class="message-avatar">
                                <?php 
                                $avatar = $message['sender_type'] == 'admin' ? 'А' : mb_substr($message['sender_name'] ?? 'П', 0, 1);
                                echo $avatar;
                                ?>
                            </div>
                            <div class="message-content">
                                <div class="message-bubble">
                                    <div class="message-text"><?php echo nl2br(htmlspecialchars($message['message_text'])); ?></div>
                                    
                                    <?php if (!empty($message['attachments'])): ?>
                                        <?php foreach ($message['attachments'] as $attachment): ?>
                                            <?php 
        // Проверяем структуру данных вложения
        $file_name = isset($attachment['name']) ? $attachment['name'] : 
                    (isset($attachment['file_name']) ? $attachment['file_name'] : 'Файл');
        $file_path = isset($attachment['path']) ? $attachment['path'] : 
                    (isset($attachment['file_path']) ? $attachment['file_path'] : '');
        $file_size = isset($attachment['size']) ? $attachment['size'] : 
                    (isset($attachment['file_size']) ? $attachment['file_size'] : 0);
        
        $file_info = pathinfo($file_name);
        $ext = strtolower($file_info['extension'] ?? '');
        $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        ?>
        
                                            
                                            <?php if ($is_image && $file_path): ?>
            <div class="message-image" onclick="openLightbox('<?php echo htmlspecialchars($file_path); ?>')">
                <img src="<?php echo htmlspecialchars($file_path); ?>" alt="<?php echo htmlspecialchars($file_name); ?>">
            </div>
        <?php else: ?>
            <div class="message-file" onclick="downloadFile('<?php echo htmlspecialchars($file_path); ?>', '<?php echo htmlspecialchars($file_name); ?>')">
                <div class="message-file-icon">
                    <i class="fas fa-<?php echo getFileIcon($ext); ?>"></i>
                </div>
                <div class="message-file-info">
                    <div class="message-file-name"><?php echo htmlspecialchars($file_name); ?></div>
                    <div class="message-file-size"><?php echo formatFileSize($file_size); ?></div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
                                </div>
                                <div class="message-meta">
                                    <span class="message-sender"><?php echo htmlspecialchars($message['sender_name']); ?></span>
                                    <time class="message-time"><?php echo formatTime($message['created_at']); ?></time>
                                    <?php if ($message['is_read']): ?>
                                        <i class="fas fa-check-double read-indicator"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Форма отправки сообщения -->
            <?php if ($current_chat['status'] == 'active'): ?>
            <div class="chat-input-wrapper">
                <form id="chatForm" class="chat-input-form">
                    <input type="hidden" id="chatId" value="<?php echo $current_chat['id']; ?>">
                    
                    <div class="input-group">
                        <textarea 
                            id="messageInput"
                            class="chat-input" 
                            placeholder="Введите сообщение..." 
                            rows="1"
                            maxlength="4096"
                            required></textarea>
                        
                        <div class="input-actions">
                            <input type="file" id="fileInput" style="display: none;" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                            <button type="button" class="input-action-btn" onclick="selectFiles()">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            
                            <button type="button" class="input-action-btn" onclick="insertTemplate()">
                                <i class="fas fa-file-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
                
                <div id="filePreview" class="file-preview"></div>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Пустое состояние -->
            <div class="empty-chat">
                <i class="fas fa-comments"></i>
                <h3>Выберите чат</h3>
                <p>Выберите чат из списка слева для просмотра сообщений</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Боковая панель с информацией о пользователе -->
    <div class="user-info-sidebar" id="userInfoSidebar" style="display: none;">
        <div class="sidebar-header">
            <h3>Информация о клиенте</h3>
            <button class="close-btn" onclick="hideUserInfo()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="sidebar-content" id="userInfoContent">
            <!-- Загружается динамически -->
        </div>
    </div>
</div>

<!-- Модальное окно для шаблонов -->
<div class="modal fade" id="templatesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Шаблоны ответов</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="templates-list">
                    <div class="template-item" onclick="useTemplate('Здравствуйте! Спасибо за обращение. Чем могу помочь?')">
                        <h6>Приветствие</h6>
                        <p>Здравствуйте! Спасибо за обращение. Чем могу помочь?</p>
                    </div>
                    
                    <div class="template-item" onclick="useTemplate('Для расчета стоимости мне необходимо уточнить следующие детали:')">
                        <h6>Запрос информации</h6>
                        <p>Для расчета стоимости мне необходимо уточнить следующие детали:</p>
                    </div>
                    
                    <div class="template-item" onclick="useTemplate('Ваш заказ готов. Вы можете забрать его по адресу: ')">
                        <h6>Заказ готов</h6>
                        <p>Ваш заказ готов. Вы можете забрать его по адресу: </p>
                    </div>
                    
                    <div class="template-item" onclick="useTemplate('Спасибо за заказ! Если у вас возникнут вопросы, не стесняйтесь обращаться.')">
                        <h6>Завершение</h6>
                        <p>Спасибо за заказ! Если у вас возникнут вопросы, не стесняйтесь обращаться.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Лайтбокс для изображений -->
<div class="image-lightbox" id="imageLightbox" onclick="closeLightbox(event)">
    <button class="image-lightbox-close" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </button>
    <img src="" alt="" id="lightboxImage">
</div>

<?php
// PHP функции для работы с файлами
function getFileIcon($extension) {
    $icons = [
        'pdf' => 'file-pdf',
        'doc' => 'file-word',
        'docx' => 'file-word',
        'xls' => 'file-excel',
        'xlsx' => 'file-excel',
        'zip' => 'file-archive',
        'rar' => 'file-archive',
        'txt' => 'file-alt',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'webp' => 'image'
    ];
    
    return isset($icons[$extension]) ? $icons[$extension] : 'file';
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Байт';
    
    $k = 1024;
    $sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>

<script>
// Конфигурация
const CHAT_CONFIG = {
    POLLING_INTERVAL: 3000,
    ERROR_POLLING_INTERVAL: 10000,
    NOTIFICATION_INTERVAL: 30000,
    MAX_FILE_SIZE: 10 * 1024 * 1024 // 10 МБ
};

// Инициализация
let currentChatId = <?php echo $chat_id ? $chat_id : 'null'; ?>;
let messagePollingInterval = null;
let notificationPollingInterval = null;
let typingTimeout = null;
let selectedFiles = [];
let isPollingActive = true;

// Допустимые типы файлов
const ALLOWED_FILE_TYPES = {
    'image/jpeg': { ext: 'jpg', icon: 'image' },
    'image/jpg': { ext: 'jpg', icon: 'image' },
    'image/png': { ext: 'png', icon: 'image' },
    'image/gif': { ext: 'gif', icon: 'image' },
    'image/webp': { ext: 'webp', icon: 'image' },
    'application/pdf': { ext: 'pdf', icon: 'file-pdf' },
    'application/msword': { ext: 'doc', icon: 'file-word' },
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': { ext: 'docx', icon: 'file-word' },
    'application/vnd.ms-excel': { ext: 'xls', icon: 'file-excel' },
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': { ext: 'xlsx', icon: 'file-excel' },
    'application/zip': { ext: 'zip', icon: 'file-archive' },
    'application/x-rar-compressed': { ext: 'rar', icon: 'file-archive' }
};

// ========== НОВЫЕ ФУНКЦИИ ДЛЯ AJAX ==========

// Открытие чата через AJAX
async function openChat(chatId) {
    if (!chatId || chatId === currentChatId) return;
    
    try {
        // Показываем индикатор загрузки
        showChatLoading();
        
        // Загружаем данные чата
        const response = await fetch(`/admin/api/chats.php?action=get_chat&id=${chatId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Обновляем текущий ID чата
            currentChatId = chatId;
            
            // Отрисовываем чат
            renderChat(data.chat, data.messages, data.user);
            
            // Обновляем активный элемент в списке
            updateActiveChatItem(chatId);
            
            // Обновляем URL без перезагрузки
            window.history.pushState({chatId: chatId}, '', `?id=${chatId}`);
            
            // Запускаем автообновление для нового чата
            startMessagePolling();
            
            // Прокручиваем вниз
            scrollToBottom();
            
            // На мобильных устройствах скрываем список чатов
            if (window.innerWidth <= 768) {
                document.querySelector('.chats-container')?.classList.add('chat-open');
            }
        } else {
            showNotification(data.error || 'Ошибка загрузки чата', 'error');
            hideChatLoading();
        }
    } catch (error) {
        console.error('Error loading chat:', error);
        showNotification('Ошибка загрузки чата', 'error');
        hideChatLoading();
    }
}

// Показать индикатор загрузки
function showChatLoading() {
    const chatArea = document.querySelector('.chat-area');
    if (!chatArea) return;
    
    chatArea.innerHTML = `
        <div class="chat-loading">
            <div class="spinner"></div>
            <p>Загрузка чата...</p>
        </div>
    `;
}

// Скрыть индикатор загрузки
function hideChatLoading() {
    // Загрузка скрывается при рендеринге чата
}

// Отрисовка чата
function renderChat(chat, messages, user) {
    const chatArea = document.querySelector('.chat-area');
    if (!chatArea) return;
    
    const isActive = chat.status === 'active';
    
    chatArea.innerHTML = `
        <!-- Заголовок чата -->
        <div class="chat-header">
            <div class="chat-user-info">
                <h3>${escapeHtml(user.email || user.name || 'Неизвестный')}</h3>
                <div class="user-details">
                    ${user.phone ? `<span><i class="fas fa-phone"></i> ${formatPhone(user.phone)}</span>` : ''}
                    ${user.email ? `<span><i class="fas fa-envelope"></i> ${escapeHtml(user.email)}</span>` : ''}
                    ${user.company_name ? `<span><i class="fas fa-building"></i> ${escapeHtml(user.company_name)}</span>` : ''}
                </div>
            </div>
            
            <div class="chat-actions">
                <button class="btn btn-secondary btn-sm" onclick="showUserInfo(${chat.user_id})">
                    <i class="fas fa-info-circle"></i> Информация
                </button>
                
                ${isActive ? 
                    `<button class="btn btn-warning btn-sm" onclick="closeChat(${chat.id})">
                        <i class="fas fa-times"></i> Закрыть чат
                    </button>` :
                    `<button class="btn btn-success btn-sm" onclick="reopenChat(${chat.id})">
                        <i class="fas fa-redo"></i> Открыть чат
                    </button>`
                }
            </div>
        </div>
        
        <!-- Сообщения -->
        <div class="chat-messages" id="chatMessages">
            ${renderMessages(messages)}
        </div>
        
        <!-- Форма отправки сообщения -->
        ${isActive ? `
        <div class="chat-input-wrapper">
            <form id="chatForm" class="chat-input-form">
                <input type="hidden" id="chatId" value="${chat.id}">
                
                <div class="input-group">
                    <textarea 
                        id="messageInput"
                        class="chat-input" 
                        placeholder="Введите сообщение..." 
                        rows="1"
                        maxlength="4096"
                        required></textarea>
                    
                    <div class="input-actions">
                        <input type="file" id="fileInput" style="display: none;" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                        <button type="button" class="input-action-btn" onclick="selectFiles()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        
                        <button type="button" class="input-action-btn" onclick="insertTemplate()">
                            <i class="fas fa-file-alt"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            
            <div id="filePreview" class="file-preview"></div>
        </div>
        ` : ''}
    `;
    
    // Переинициализируем обработчики событий
    initChatEventHandlers();
}

// Отрисовка сообщений
function renderMessages(messages) {
    if (!messages || messages.length === 0) {
        return '<div class="empty-state"><i class="fas fa-comments"></i><p>Нет сообщений</p></div>';
    }
    
    return messages.map(message => {
        if (message.message_type === 'system') {
            return `
                <div class="system-message">
                    <span>${escapeHtml(message.message_text)}</span>
                    <time>${formatTime(message.created_at)}</time>
                </div>
            `;
        } else {
            const isSent = message.sender_type === 'admin';
            const avatar = isSent ? 'А' : (message.sender_name ? message.sender_name.substring(0, 1).toUpperCase() : 'П');
            
            let attachmentsHtml = '';
            if (message.attachments && Array.isArray(message.attachments) && message.attachments.length > 0) {
                attachmentsHtml = message.attachments.map(attachment => {
                    const fileName = attachment.name || attachment.file_name || 'Файл';
                    const filePath = attachment.path || attachment.file_path || '';
                    const fileSize = attachment.size || attachment.file_size || 0;
                    const fileInfo = getFileInfo(fileName);
                    
                    if (isImageFile(fileName) && filePath) {
                        return `
                            <div class="message-image" onclick="openLightbox('${escapeHtml(filePath)}')">
                                <img src="${escapeHtml(filePath)}" alt="${escapeHtml(fileName)}">
                            </div>
                        `;
                    } else {
                        return `
                            <div class="message-file" onclick="downloadFile('${escapeHtml(filePath)}', '${escapeHtml(fileName)}')">
                                <div class="message-file-icon">
                                    <i class="fas fa-${fileInfo.icon}"></i>
                                </div>
                                <div class="message-file-info">
                                    <div class="message-file-name">${escapeHtml(fileName)}</div>
                                    <div class="message-file-size">${formatFileSize(fileSize)}</div>
                                </div>
                            </div>
                        `;
                    }
                }).join('');
            }
            
            const senderName = message.sender_name || (isSent ? 'Администратор' : 'Пользователь');
            
            return `
                <div class="message ${isSent ? 'sent' : 'received'}" data-message-id="${message.id}">
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-content">
                        <div class="message-bubble">
                            <div class="message-text">${escapeHtml(message.message_text || '').replace(/\n/g, '<br>')}</div>
                            ${attachmentsHtml}
                        </div>
                        <div class="message-meta">
                            <span class="message-sender">${escapeHtml(senderName)}</span>
                            <time class="message-time">${formatTime(message.created_at)}</time>
                            ${message.is_read ? '<i class="fas fa-check-double read-indicator"></i>' : ''}
                        </div>
                    </div>
                </div>
            `;
        }
    }).join('');
}

// Обновление активного элемента в списке чатов
function updateActiveChatItem(chatId) {
    // Убираем active у всех элементов
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Добавляем active к выбранному
    const activeItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
        // Убираем индикатор непрочитанных
        activeItem.classList.remove('unread');
        const badge = activeItem.querySelector('.badge-primary');
        if (badge) badge.remove();
    }
}

// Инициализация обработчиков событий чата
function initChatEventHandlers() {
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        // Создаем новый обработчик только если его еще нет
        if (!chatForm.dataset.initialized) {
            chatForm.addEventListener('submit', handleChatSubmit);
            chatForm.dataset.initialized = 'true';
        }
    }
    
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        // Убираем старые обработчики перед добавлением новых
        const newInput = messageInput.cloneNode(true);
        messageInput.parentNode.replaceChild(newInput, messageInput);
        
        newInput.addEventListener('input', handleMessageInput);
        newInput.addEventListener('keypress', handleMessageKeypress);
        newInput.focus();
    }
    
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        const newFileInput = fileInput.cloneNode(true);
        fileInput.parentNode.replaceChild(newFileInput, fileInput);
        newFileInput.addEventListener('change', handleFileSelect);
    }
}

// Обработчик отправки формы
async function handleChatSubmit(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message && selectedFiles.length === 0) return;
    
    const sendButton = e.target.querySelector('.send-btn');
    const originalHtml = sendButton.innerHTML;
    sendButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
        const formData = new FormData();
        formData.append('chat_id', currentChatId);
        formData.append('message', message);
        
        selectedFiles.forEach((file, index) => {
            formData.append('files[]', file);
        });
        
        const response = await fetch('/admin/api/chats.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            messageInput.style.height = 'auto';
            selectedFiles = [];
            updateFilePreview();
            
            if (data.message) {
                appendNewMessages([data.message]);
            }
            
            updateChatsList();
        } else {
            showNotification(data.error || 'Ошибка отправки сообщения', 'error');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showNotification('Ошибка отправки сообщения', 'error');
    } finally {
        sendButton.disabled = false;
        sendButton.innerHTML = originalHtml;
        messageInput.focus();
    }
}

// Обработчик ввода текста
function handleMessageInput(e) {
    const input = e.target;
    input.style.height = 'auto';
    input.style.height = (input.scrollHeight) + 'px';
    
    if (typingTimeout) clearTimeout(typingTimeout);
    
    sendTypingStatus(true);
    
    typingTimeout = setTimeout(() => {
        sendTypingStatus(false);
    }, 1000);
}

// Обработчик нажатия клавиш
function handleMessageKeypress(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const form = document.getElementById('chatForm');
        if (form) {
            form.requestSubmit(); // Современный способ
        }
    }
}

// Обработчик выбора файлов
function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    
    const validFiles = files.filter(file => {
        if (file.size > CHAT_CONFIG.MAX_FILE_SIZE) {
            showNotification(`Файл ${file.name} слишком большой (максимум 10 МБ)`, 'error');
            return false;
        }
        
        const fileType = file.type || getMimeTypeByExtension(file.name);
        if (!ALLOWED_FILE_TYPES[fileType]) {
            showNotification(`Файл ${file.name} имеет неподдерживаемый формат`, 'error');
            return false;
        }
        
        return true;
    });
    
    selectedFiles = validFiles;
    updateFilePreview();
    
    // Очищаем input для возможности повторного выбора тех же файлов
    e.target.value = '';
}

// Обработка кнопки "Назад" в браузере
window.addEventListener('popstate', function(e) {
    if (e.state && e.state.chatId) {
        openChat(e.state.chatId);
    } else {
        // Возврат к списку чатов
        currentChatId = null;
        const chatArea = document.querySelector('.chat-area');
        if (chatArea) {
            chatArea.innerHTML = `
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <h3>Выберите чат</h3>
                    <p>Выберите чат из списка слева для просмотра сообщений</p>
                </div>
            `;
        }
        
        // Убираем активный класс у всех чатов
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // На мобильных показываем список
        if (window.innerWidth <= 768) {
            document.querySelector('.chats-container')?.classList.remove('chat-open');
        }
        
        // Останавливаем автообновление
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
            messagePollingInterval = null;
        }
    }
});

// ========== ОСТАЛЬНЫЕ ФУНКЦИИ ==========

// Автообновление сообщений
function startMessagePolling() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    
    if (currentChatId && isPollingActive) {
        loadNewMessages();
        messagePollingInterval = setInterval(loadNewMessages, CHAT_CONFIG.POLLING_INTERVAL);
    }
}

// Загрузка новых сообщений
async function loadNewMessages() {
    if (!currentChatId || !isPollingActive) return;
    
    try {
        const lastMessageEl = document.querySelector('.message:last-child[data-message-id]');
        const lastMessageId = lastMessageEl ? lastMessageEl.dataset.messageId : 0;
        
        const response = await fetch(`/admin/api/chats.php?action=new_messages&chat_id=${currentChatId}&last_id=${lastMessageId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.messages && data.messages.length > 0) {
            appendNewMessages(data.messages);
            playNotificationSound();
        }
        
    } catch (error) {
        console.error('Error loading new messages:', error);
    }
}

// Добавление новых сообщений
function appendNewMessages(messages) {
    const container = document.getElementById('chatMessages');
    if (!container) return;
    
    messages.forEach(message => {
        const messageEl = createMessageElement(message);
        if (messageEl) {
            container.appendChild(messageEl);
        }
    });
    scrollToBottom();
}

// Создание элемента сообщения с поддержкой файлов
function createMessageElement(message) {
    if (!message) return null;
    
    const div = document.createElement('div');
    
    if (message.message_type === 'system') {
        div.className = 'system-message';
        div.innerHTML = `
            <span>${escapeHtml(message.message_text || '')}</span>
            <time>${formatTime(message.created_at)}</time>
        `;
    } else {
        const isSent = message.sender_type === 'admin';
        div.className = `message ${isSent ? 'sent' : 'received'}`;
        div.dataset.messageId = message.id || '';
        
        let avatar = '?';
        if (isSent) {
            avatar = 'А';
        } else if (message.sender_name) {
            avatar = message.sender_name.substring(0, 1).toUpperCase();
        } else {
            avatar = 'П';
        }
        
        // Обработка вложений с превью
        let attachmentsHtml = '';
        if (message.attachments && Array.isArray(message.attachments) && message.attachments.length > 0) {
            message.attachments.forEach(attachment => {
                const fileName = attachment.name || attachment.file_name || 'Файл';
                const filePath = attachment.path || attachment.file_path || '';
                const fileSize = attachment.size || attachment.file_size || 0;
                
                const fileInfo = getFileInfo(fileName);
                
                if (isImageFile(fileName) && filePath) {
                    attachmentsHtml += `
                        <div class="message-image" onclick="openLightbox('${escapeHtml(filePath)}')">
                            <img src="${escapeHtml(filePath)}" alt="${escapeHtml(fileName)}">
                        </div>
                    `;
                } else {
                    attachmentsHtml += `
                        <div class="message-file" onclick="downloadFile('${escapeHtml(filePath)}', '${escapeHtml(fileName)}')">
                            <div class="message-file-icon">
                                <i class="fas fa-${fileInfo.icon}"></i>
                            </div>
                            <div class="message-file-info">
                                <div class="message-file-name">${escapeHtml(fileName)}</div>
                                <div class="message-file-size">${formatFileSize(fileSize)}</div>
                            </div>
                        </div>
                    `;
                }
            });
        }
        
        const senderName = message.sender_name || (isSent ? 'Администратор' : 'Пользователь');
        
        div.innerHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="message-content">
                <div class="message-bubble">
                    <div class="message-text">${escapeHtml(message.message_text || '').replace(/\n/g, '<br>')}</div>
                    ${attachmentsHtml}
                </div>
                <div class="message-meta">
                    <span class="message-sender">${escapeHtml(senderName)}</span>
                    <time class="message-time">${formatTime(message.created_at)}</time>
                    ${message.is_read ? '<i class="fas fa-check-double read-indicator"></i>' : ''}
                </div>
            </div>
        `;
    }
    
    return div;
}

// Проверка, является ли файл изображением
function isImageFile(filename) {
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    const ext = filename.split('.').pop().toLowerCase();
    return imageExtensions.includes(ext);
}

// Получение информации о файле
function getFileInfo(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const fileIcons = {
        'pdf': 'file-pdf',
        'doc': 'file-word',
        'docx': 'file-word',
        'xls': 'file-excel',
        'xlsx': 'file-excel',
        'zip': 'file-archive',
        'rar': 'file-archive',
        'txt': 'file-alt'
    };
    
    return {
        ext: ext,
        icon: fileIcons[ext] || 'file'
    };
}

// Открытие лайтбокса для изображений
function openLightbox(imageSrc) {
    document.getElementById('lightboxImage').src = imageSrc;
    document.getElementById('imageLightbox').classList.add('active');
}

// Закрытие лайтбокса
function closeLightbox(event) {
    if (!event || event.target.id === 'imageLightbox' || event.target.classList.contains('image-lightbox-close')) {
        document.getElementById('imageLightbox').classList.remove('active');
        document.getElementById('lightboxImage').src = '';
    }
}

// Скачивание файла
function downloadFile(fileUrl, fileName) {
    const link = document.createElement('a');
    link.href = fileUrl;
    link.download = fileName;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Выбор файлов
function selectFiles() {
    document.getElementById('fileInput')?.click();
}

// Определение MIME типа по расширению
function getMimeTypeByExtension(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const mimeMap = {
        'jpg': 'image/jpeg',
        'jpeg': 'image/jpeg',
        'png': 'image/png',
        'gif': 'image/gif',
        'webp': 'image/webp',
        'pdf': 'application/pdf',
        'doc': 'application/msword',
        'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls': 'application/vnd.ms-excel',
        'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'zip': 'application/zip',
        'rar': 'application/x-rar-compressed'
    };
    return mimeMap[ext] || 'application/octet-stream';
}

// Обновление превью файлов
function updateFilePreview() {
    const preview = document.getElementById('filePreview');
    if (!preview) return;
    
    if (selectedFiles.length === 0) {
        preview.innerHTML = '';
        return;
    }
    
    preview.innerHTML = selectedFiles.map((file, index) => {
        const fileInfo = getFileInfo(file.name);
        const isImage = isImageFile(file.name);
        let previewContent = '';
        
        if (isImage) {
            const url = URL.createObjectURL(file);
            previewContent = `<img src="${url}" alt="${escapeHtml(file.name)}" style="max-height: 60px;">`;
        } else {
            previewContent = `<i class="fas fa-${fileInfo.icon}" style="font-size: 24px;"></i>`;
        }
        
        return `
            <div class="file-item">
                <div class="file-preview-icon">
                    ${previewContent}
                </div>
                <div class="file-info">
                    <span class="file-name">${escapeHtml(file.name)}</span>
                    <span class="file-size">${formatFileSize(file.size)}</span>
                </div>
                <button onclick="removeFile(${index})" class="remove-file" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }).join('');
}

// Удаление файла из списка
function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFilePreview();
}

// Вставка шаблона
function insertTemplate() {
    const modal = document.getElementById('templatesModal');
    if (modal && typeof $ !== 'undefined') {
        $(modal).modal('show');
    }
}

// Использование шаблона
function useTemplate(text) {
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.value = text;
        if (typeof $ !== 'undefined') {
            $('#templatesModal').modal('hide');
        }
        messageInput.focus();
    }
}

// Поиск по чатам
document.getElementById('chatSearch')?.addEventListener('input', debounce(function(e) {
    const search = e.target.value;
    if (search.length >= 2 || search.length === 0) {
        window.location.href = `?search=${encodeURIComponent(search)}`;
    }
}, 500));

// Закрытие чата
async function closeChat(chatId) {
    if (!confirm('Закрыть этот чат?')) return;
    
    try {
        const response = await fetch(`/admin/api/chats.php?id=${chatId}&action=close`, {
            method: 'PUT'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Обновляем чат через AJAX вместо перезагрузки
            openChat(chatId);
            showNotification('Чат закрыт', 'success');
        } else {
            showNotification(data.error || 'Ошибка закрытия чата', 'error');
        }
    } catch (error) {
        console.error('Error closing chat:', error);
        showNotification('Ошибка закрытия чата', 'error');
    }
}

// Переоткрытие чата
async function reopenChat(chatId) {
    try {
        const response = await fetch(`/admin/api/chats.php?id=${chatId}&action=reopen`, {
            method: 'PUT'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Обновляем чат через AJAX вместо перезагрузки
            openChat(chatId);
            showNotification('Чат открыт', 'success');
        } else {
            showNotification(data.error || 'Ошибка открытия чата', 'error');
        }
    } catch (error) {
        console.error('Error reopening chat:', error);
        showNotification('Ошибка открытия чата', 'error');
    }
}

// Показ информации о пользователе
async function showUserInfo(userId) {
    const sidebar = document.getElementById('userInfoSidebar');
    const content = document.getElementById('userInfoContent');
    
    if (!sidebar || !content) return;
    
    sidebar.style.display = 'block';
    content.innerHTML = '<div class="loading">Загрузка...</div>';
    
    try {
        const response = await fetch(`/admin/api/users.php?id=${userId}`);
        const data = await response.json();
        
        if (data.success && data.user) {
            content.innerHTML = renderUserInfo(data.user);
        } else {
            content.innerHTML = '<div class="error">Ошибка загрузки данных пользователя</div>';
        }
    } catch (error) {
        console.error('Error loading user info:', error);
        content.innerHTML = '<div class="error">Ошибка загрузки</div>';
    }
}

// Скрытие информации о пользователе
function hideUserInfo() {
    const sidebar = document.getElementById('userInfoSidebar');
    if (sidebar) {
        sidebar.style.display = 'none';
    }
}

// Рендер информации о пользователе
function renderUserInfo(user) {
    return `
        <div class="user-info-section">
            <h4>Основная информация</h4>
            <div class="info-item">
                <label>Имя:</label>
                <span>${escapeHtml(user.name || 'Не указано')}</span>
            </div>
            <div class="info-item">
                <label>Телефон:</label>
                <span>${formatPhone(user.phone || '')}</span>
            </div>
            <div class="info-item">
                <label>Email:</label>
                <span>${escapeHtml(user.email || 'Не указан')}</span>
            </div>
            <div class="info-item">
                <label>Компания:</label>
                <span>${escapeHtml(user.company_name || 'Не указана')}</span>
            </div>
            <div class="info-item">
                <label>ИНН:</label>
                <span>${escapeHtml(user.inn || 'Не указан')}</span>
            </div>
        </div>
        
        <div class="user-info-section">
            <h4>Статистика</h4>
            <div class="info-item">
                <label>Заказов:</label>
                <span>${user.orders_count || 0}</span>
            </div>
            <div class="info-item">
                <label>Сумма заказов:</label>
                <span>${formatPrice(user.total_spent || 0)}</span>
            </div>
            <div class="info-item">
                <label>Средний чек:</label>
                <span>${formatPrice(user.avg_order_value || 0)}</span>
            </div>
            <div class="info-item">
                <label>Дата регистрации:</label>
                <span>${formatDate(user.created_at)}</span>
            </div>
        </div>
        
        <div class="user-actions">
            <a href="/admin/users.php?id=${user.id}" class="btn btn-primary btn-block">
                Подробнее
            </a>
            <a href="/admin/orders.php?user_id=${user.id}" class="btn btn-secondary btn-block">
                Заказы клиента
            </a>
        </div>
    `;
}

// Обновление списка чатов
async function updateChatsList() {
    try {
        const response = await fetch('/admin/api/chats.php');
        const data = await response.json();
        
        if (data.success && data.chats) {
            const unreadCount = data.chats.filter(c => c.unread_admin_count > 0).length;
            updateNotificationBadge(unreadCount);
        }
    } catch (error) {
        console.error('Error updating chats list:', error);
    }
}

// Обновление значка уведомлений
function updateNotificationBadge(count) {
    const badge = document.querySelector('.nav-badge, .notification-badge, .badge');
    if (badge) {
        badge.textContent = count || '';
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
    
    // Обновляем заголовок страницы
    const title = document.title;
    if (count > 0) {
        if (!title.startsWith('(')) {
            document.title = `(${count}) ${title}`;
        }
    } else {
        document.title = title.replace(/^\(\d+\)\s*/, '');
    }
}

// Воспроизведение звука уведомления
function playNotificationSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    } catch (error) {
        // Игнорируем ошибки со звуком
    }
}

// Прокрутка вниз
function scrollToBottom() {
    const messages = document.getElementById('chatMessages');
    if (messages) {
        messages.scrollTop = messages.scrollHeight;
    }
}

// Отправка статуса набора текста
async function sendTypingStatus(isTyping) {
    if (!currentChatId) return;
    
    try {
        await fetch('/admin/api/chats.php?action=typing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                chat_id: currentChatId,
                is_typing: isTyping
            })
        });
    } catch (error) {
        console.error('Error sending typing status:', error);
    }
}

// Вспомогательные функции
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(datetime) {
    if (!datetime) return '';
    
    try {
        const date = new Date(datetime);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) {
            return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        } else if (days === 1) {
            return 'Вчера';
        } else if (days < 7) {
            return `${days} дн. назад`;
        } else {
            return date.toLocaleDateString('ru-RU');
        }
    } catch (e) {
        return '';
    }
}

function formatDate(datetime) {
    if (!datetime) return '';
    try {
        return new Date(datetime).toLocaleDateString('ru-RU');
    } catch (e) {
        return '';
    }
}

function formatPhone(phone) {
    if (!phone) return '';
    return phone.replace(/(\+\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '$1 ($2) $3-$4-$5');
}

function formatPrice(price) {
    if (!price) return '0 ₽';
    try {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            minimumFractionDigits: 0
        }).format(price);
    } catch (e) {
        return price + ' ₽';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Байт';
    
    const k = 1024;
    const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('Инициализация чатов...');
    
    // Запускаем автообновление если чат открыт
    if (currentChatId) {
        startMessagePolling();
        
        // Прокручиваем вниз при загрузке
        scrollToBottom();
        
        // Сохраняем состояние в истории браузера
        window.history.replaceState({chatId: currentChatId}, '', `?id=${currentChatId}`);
    }
    
    // Инициализируем обработчики для формы чата (если чат открыт)
    initChatEventHandlers();
    
    // Закрытие боковой панели при клике вне её
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('userInfoSidebar');
        if (sidebar && sidebar.style.display === 'block' && 
            !sidebar.contains(e.target) && 
            !e.target.closest('.btn')) {
            hideUserInfo();
        }
    });
    
    // Обработка видимости страницы
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            isPollingActive = false;
        } else {
            isPollingActive = true;
            if (currentChatId) {
                startMessagePolling();
            }
        }
    });
    
    // Обработка клавиши Escape для закрытия лайтбокса
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const lightbox = document.getElementById('imageLightbox');
            if (lightbox && lightbox.classList.contains('active')) {
                closeLightbox();
            }
        }
    });
});

// Остановка автообновления при уходе со страницы
window.addEventListener('beforeunload', function() {
    isPollingActive = false;
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});

// Переключение меню статусов
function toggleStatusMenu(chatId) {
    const menu = document.getElementById(`statusMenu${chatId}`);
    const allMenus = document.querySelectorAll('.status-menu');
    
    // Закрываем все другие меню
    allMenus.forEach(m => {
        if (m.id !== `statusMenu${chatId}`) {
            m.classList.remove('show');
        }
    });
    
    // Переключаем текущее меню
    if (menu) {
        menu.classList.toggle('show');
    }
}

// Закрытие меню при клике вне его
document.addEventListener('click', function(e) {
    if (!e.target.closest('.status-dropdown')) {
        document.querySelectorAll('.status-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Изменение статуса клиента
async function changeClientStatus(chatId, status) {
    try {
        const response = await fetch(`/admin/api/chats.php?id=${chatId}&action=change_status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                client_status: status
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Закрываем меню
            document.getElementById(`statusMenu${chatId}`)?.classList.remove('show');
            
            // Обновляем класс карточки чата
            const chatItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
            if (chatItem) {
                // Убираем все старые классы статусов
                chatItem.className = chatItem.className.replace(/chat-status-\w+/g, '');
                // Добавляем новый класс статуса
                chatItem.classList.add(`chat-status-${status}`);
            }
            
            showNotification('Статус обновлен', 'success');
        } else {
            showNotification(data.error || 'Ошибка изменения статуса', 'error');
        }
    } catch (error) {
        console.error('Error changing status:', error);
        showNotification('Ошибка изменения статуса', 'error');
    }
}

// Удаление чата
async function deleteChat(chatId) {
    if (!confirm('Вы уверены, что хотите удалить этот чат? Это действие нельзя отменить.')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/api/chats.php?id=${chatId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Закрываем меню
            document.getElementById(`statusMenu${chatId}`)?.classList.remove('show');
            
            // Удаляем карточку чата из списка
            const chatItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
            if (chatItem) {
                chatItem.style.transition = 'all 0.3s ease';
                chatItem.style.opacity = '0';
                chatItem.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    chatItem.remove();
                    
                    // Если удален текущий открытый чат, показываем пустое состояние
                    if (currentChatId === chatId) {
                        currentChatId = null;
                        const chatArea = document.querySelector('.chat-area');
                        if (chatArea) {
                            chatArea.innerHTML = `
                                <div class="empty-chat">
                                    <i class="fas fa-comments"></i>
                                    <h3>Выберите чат</h3>
                                    <p>Выберите чат из списка слева для просмотра сообщений</p>
                                </div>
                            `;
                        }
                        
                        // Обновляем URL
                        window.history.pushState({}, '', '/admin/chats.php');
                    }
                }, 300);
            }
            
            showNotification('Чат удален', 'success');
            updateChatsList();
        } else {
            showNotification(data.error || 'Ошибка удаления чата', 'error');
        }
    } catch (error) {
        console.error('Error deleting chat:', error);
        showNotification('Ошибка удаления чата', 'error');
    }
}
// Обновляем функцию renderChat для поддержки статусов
function renderChatItem(chat) {
    const statusClass = chat.client_status ? `chat-status-${chat.client_status}` : 'chat-status-new';
    
    return `
        <div class="chat-item ${currentChatId === chat.id ? 'active' : ''} 
                    ${chat.unread_admin_count > 0 ? 'unread' : ''} 
                    ${statusClass}" 
             data-chat-id="${chat.id}">
            
            <div class="chat-avatar" onclick="openChat(${chat.id})">
                ${chat.user_name ? chat.user_name.substring(0, 1).toUpperCase() : 'П'}
            </div>
            
            <div class="chat-info" onclick="openChat(${chat.id})">
                <div class="chat-header">
                    <h4>${escapeHtml(chat.user_email || chat.user_name || 'Неизвестный')}</h4>
                    <span class="chat-time">${formatTime(chat.last_message_time)}</span>
                </div>
                
                <div class="chat-preview">
                    ${escapeHtml((chat.last_message || 'Нет сообщений').substring(0, 50))}
                    ${chat.last_message && chat.last_message.length > 50 ? '...' : ''}
                </div>
                
                <div class="chat-meta">
                    ${chat.unread_admin_count > 0 ? `<span class="badge badge-primary">${chat.unread_admin_count}</span>` : ''}
                    ${chat.orders_count > 0 ? `<span class="badge badge-info"><i class="fas fa-shopping-cart"></i> ${chat.orders_count}</span>` : ''}
                    ${chat.status === 'closed' ? '<span class="badge badge-secondary">Закрыт</span>' : ''}
                </div>
            </div>
            
            <div class="chat-item-actions" onclick="event.stopPropagation();">
                <div class="status-dropdown">
                    <button class="status-btn" onclick="toggleStatusMenu(${chat.id})">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="status-menu" id="statusMenu${chat.id}">
                        <div class="status-menu-header">Статус клиента:</div>
                        <button class="status-option" onclick="changeClientStatus(${chat.id}, 'new')">
                            <span class="status-dot status-new"></span> Новый
                        </button>
                        <button class="status-option" onclick="changeClientStatus(${chat.id}, 'in_progress')">
                            <span class="status-dot status-in-progress"></span> В обработке
                        </button>
                        <button class="status-option" onclick="changeClientStatus(${chat.id}, 'waiting_client')">
                            <span class="status-dot status-waiting"></span> Клиент думает
                        </button>
                        <button class="status-option" onclick="changeClientStatus(${chat.id}, 'no_response')">
                            <span class="status-dot status-no-response"></span> Нет ответа
                        </button>
                        <button class="status-option" onclick="changeClientStatus(${chat.id}, 'resolved')">
                            <span class="status-dot status-resolved"></span> Решено
                        </button>
                        <div class="status-menu-divider"></div>
                        <button class="status-option danger" onclick="deleteChat(${chat.id})">
                            <i class="fas fa-trash"></i> Удалить чат
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}
</script>

<style>
/* Стили для страницы чатов */
/* Индикатор загрузки чата */
.chat-loading {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    padding: 40px;
}

.chat-loading .spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.chat-loading p {
    font-size: 14px;
    color: #999;
}
.chats-container {
    display: flex;
    height: calc(100vh - 60px);
    background: #f5f5f5;
}

/* Боковая панель со списком чатов */
.chats-sidebar {
    width: 350px;
    background: white;
    border-right: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
}

.chats-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.chats-header h3 {
    margin: 0 0 10px 0;
    font-size: 20px;
}

.chats-stats {
    display: flex;
    gap: 15px;
    font-size: 14px;
    color: #666;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.stat-item.unread {
    color: #007bff;
    font-weight: 500;
}

/* Контролы чатов */
.chats-controls {
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.search-box {
    position: relative;
    margin-bottom: 15px;
}

.search-box input {
    width: 100%;
    padding: 8px 35px 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.search-box i {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.filter-tabs {
    display: flex;
    gap: 10px;
}

.filter-tab {
    padding: 6px 12px;
    font-size: 14px;
    color: #666;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s;
}

.filter-tab:hover {
    background: #f0f0f0;
    color: #333;
}

.filter-tab.active {
    background: #007bff;
    color: white;
}

/* Основной контейнер чатов */
.chats-container {
    display: flex;
    height: calc(100vh - 100px); /* Учитываем высоту хедера */
    background: #f5f5f5;
    position: relative;
    margin: -20px; /* Компенсируем padding от родителя */
}

/* Боковая панель со списком чатов */
.chats-sidebar {
    width: 380px;
    background: white;
    border-right: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.chats-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #fafafa;
}

.chats-header h3 {
    margin: 0 0 10px 0;
    font-size: 20px;
    font-weight: 600;
}

.chats-stats {
    display: flex;
    gap: 15px;
    font-size: 14px;
    color: #666;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.stat-item i {
    font-size: 16px;
}

.stat-item.unread {
    color: #007bff;
    font-weight: 500;
}

/* Контролы чатов */
.chats-controls {
    padding: 15px;
    background: white;
}

.search-box {
    position: relative;
    margin-bottom: 15px;
}

.search-box input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
    background: #f8f9fa;
}

.search-box input:focus {
    outline: none;
    border-color: #007bff;
    background: white;
}

.search-box i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
}

.filter-tabs {
    display: flex;
    gap: 5px;
    background: #f0f0f0;
    padding: 3px;
    border-radius: 8px;
}

.filter-tab {
    flex: 1;
    padding: 8px 12px;
    font-size: 14px;
    color: #666;
    text-decoration: none;
    border-radius: 6px;
    text-align: center;
    transition: all 0.2s;
    font-weight: 500;
}

.filter-tab:hover {
    color: #333;
    text-decoration: none;
}

.filter-tab.active {
    background: white;
    color: #007bff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Список чатов */
.chats-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

.chats-list::-webkit-scrollbar {
    width: 6px;
}

.chats-list::-webkit-scrollbar-track {
    background: transparent;
}

.chats-list::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 3px;
}

.chats-list::-webkit-scrollbar-thumb:hover {
    background: #ccc;
}

.chat-item {
    display: flex;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: all 0.2s;
    gap: 12px;
    position: relative;
}

.chat-item:hover {
    background: #f8f9fa;
}

.chat-item.active {
    background: #e8f0fe;
    border-left: 3px solid #007bff;
    padding-left: 17px;
}

.chat-item.unread {
    background: #fffbf0;
}

.chat-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
    flex-shrink: 0;
    text-transform: uppercase;
}

.chat-info {
    flex: 1;
    min-width: 0;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 5px;
    gap: 10px;
}

.chat-header h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.chat-time {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.chat-preview {
    font-size: 14px;
    color: #666;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.4;
    margin-bottom: 5px;
}

.chat-meta {
    display: flex;
    gap: 8px;
    align-items: center;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 12px;
    gap: 3px;
}

.badge i {
    font-size: 10px;
}

.badge-primary {
    background: #007bff;
    color: white;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

/* Область чата */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: white;
    min-width: 0;
}

.chat-area .chat-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #fafafa;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.chat-user-info {
    flex: 1;
    min-width: 0;
}

.chat-user-info h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.user-details {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #666;
    flex-wrap: wrap;
}

.user-details span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.user-details i {
    font-size: 12px;
    color: #999;
}

.chat-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}

.chat-actions .btn {
    white-space: nowrap;
}

/* Сообщения */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    background: #fafbfc;
}

.chat-messages::-webkit-scrollbar {
    width: 8px;
}

.chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 4px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #ccc;
}

.message {
    display: flex;
    gap: 12px;
    max-width: 70%;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.sent {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    flex-shrink: 0;
    text-transform: uppercase;
}

.message.received .message-avatar {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.message-bubble {
    background: white;
    padding: 12px 16px;
    border-radius: 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    word-wrap: break-word;
}

.message.sent .message-bubble {
    background: #007bff;
    color: white;
}

.message-text {
    font-size: 14px;
    line-height: 1.5;
}

/* Файлы в сообщениях */
.message-file {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-top: 8px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #e9ecef;
}

.message.sent .message-file {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
}

.message-file:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.message.sent .message-file:hover {
    background: rgba(255, 255, 255, 0.2);
}

.message-file-icon {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.message.sent .message-file-icon {
    background: rgba(255, 255, 255, 0.2);
}

.message-file-info {
    flex: 1;
    min-width: 0;
}

.message-file-name {
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.message-file-size {
    font-size: 12px;
    opacity: 0.8;
}

/* Превью изображений */
.message-image {
    max-width: 300px;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 8px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message-image:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.message-image img {
    width: 100%;
    height: auto;
    display: block;
}

.message-meta {
    display: flex;
    gap: 10px;
    font-size: 12px;
    color: #999;
    padding: 0 5px;
    align-items: center;
}

.message.sent .message-meta {
    color: rgba(255, 255, 255, 0.7);
}

.read-indicator {
    color: #28a745;
}

.message.sent .read-indicator {
    color: rgba(255, 255, 255, 0.8);
}

.system-message {
    text-align: center;
    font-size: 13px;
    color: #999;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.system-message span {
    background: #f0f0f0;
    padding: 6px 16px;
    border-radius: 16px;
}

/* Форма ввода */
.chat-input-wrapper {
    padding: 20px;
    border-top: 1px solid #e0e0e0;
    background: white;
}

.chat-input-form {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.input-group {
    flex: 1;
    display: flex;
    background: #f1f3f4;
    border-radius: 24px;
    padding: 8px 16px;
    align-items: flex-end;
}

.chat-input {
    flex: 1;
    border: none;
    background: none;
    outline: none;
    resize: none;
    font-size: 14px;
    line-height: 1.5;
    max-height: 120px;
    padding: 8px 0;
    font-family: inherit;
}

.input-actions {
    display: flex;
    gap: 5px;
    align-items: center;
    margin-left: 10px;
}


/* Действия на карточке чата */
.chat-item {
    position: relative;
}

.chat-item-actions {
    position: absolute;
    right: -7px;
    top: 22%;
    transform: translateY(-50%);
    z-index: 10;
}

.status-dropdown {
    position: relative;
}

.status-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(0, 0, 0, 0.0);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.2s;
}

.status-btn:hover {
    background: rgba(0, 0, 0, 0.0);
    color: #333;
}

.status-menu {
    display: none;
    position: absolute;
    right: 27px;
    top: 72%;
    margin-top: 5px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    z-index: 1000;
    animation: slideDown 0.2s ease;
}

.status-menu.show {
    display: block;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.status-menu-header {
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    border-bottom: 1px solid #f0f0f0;
}

.status-option {
    width: 100%;
    padding: 10px 16px;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #333;
    transition: all 0.2s;
}

.status-option:hover {
    background: #f8f9fa;
}

.status-option.danger {
    color: #dc3545;
}

.status-option.danger:hover {
    background: #fff5f5;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-menu-divider {
    height: 1px;
    background: #f0f0f0;
    margin: 5px 0;
}

/* Цвета статусов */
.status-new {
    background: #6c757d;
}

.status-in-progress {
    background: #007bff;
}

.status-waiting {
    background: #ffc107;
}

.status-no-response {
    background: #dc3545;
}

.status-resolved {
    background: #28a745;
}

/* Цвета карточек чатов по статусам */
.chat-item.chat-status-new {
    border-left: 3px solid #6c757d;
}

.chat-item.chat-status-in_progress {
    border-left: 3px solid #007bff;
    background: #f0f7ff;
}

.chat-item.chat-status-waiting_client {
    border-left: 3px solid #ffc107;
    background: #fffbf0;
}

.chat-item.chat-status-no_response {
    border-left: 3px solid #dc3545;
    background: #fff5f5;
}

.chat-item.chat-status-resolved {
    border-left: 3px solid #28a745;
    background: #f0fff4;
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
    .status-menu {
        right: auto;
        left: 0;
    }
}

.input-action-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: none;
    color: #666;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.input-action-btn:hover {
    background: rgba(0,0,0,0.05);
    color: #333;
}

.send-btn {
    width: 44px;
    height: 44px;
    border: none;
    background: #007bff;
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.send-btn:hover {
    background: #0056b3;
    transform: scale(1.05);
}

.send-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: scale(1);
}

/* Превью файлов */
.file-preview {
    margin-top: 12px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f0f0f0;
    border-radius: 20px;
    font-size: 13px;
}

.file-preview-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.file-preview-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-preview-icon i {
    font-size: 18px;
    color: #666;
}

.file-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.file-name {
    font-weight: 600;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-size {
    font-size: 11px;
    color: #666;
}

.remove-file {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 0;
    margin-left: 5px;
    font-size: 16px;
    transition: all 0.2s;
}

.remove-file:hover {
    color: #dc3545;
}

/* Пустое состояние */
.empty-chat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999;
    padding: 40px;
}

.empty-chat i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #e0e0e0;
}

.empty-chat h3 {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 20px;
}

.empty-chat p {
    color: #999;
    font-size: 14px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #e0e0e0;
}

.empty-state p {
    font-size: 14px;
}

/* Боковая панель с информацией */
.user-info-sidebar {
    width: 320px;
    background: white;
    border-left: 1px solid #e0e0e0;
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
    box-shadow: -2px 0 5px rgba(0,0,0,0.05);
    z-index: 100;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.user-info-sidebar[style*="display: block"] {
    transform: translateX(0);
}

.sidebar-header {
    padding: 20px;
    
    display: flex;
    justify-content: space-between;
    align-items: center;
    
}

.sidebar-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #999;
    cursor: pointer;
    padding: 5px;
    transition: all 0.2s;
}

.close-btn:hover {
    color: #333;
}

.sidebar-content {
    padding: 20px;
}

.user-info-section {
    margin-bottom: 30px;
}

.user-info-section h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #333;
    font-weight: 600;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item label {
    color: #666;
    font-size: 14px;
}

.info-item span {
    color: #333;
    font-size: 14px;
    font-weight: 500;
}

.user-actions {
    margin-top: 20px;
}

.user-actions .btn {
    margin-bottom: 10px;
    width: 100%;
}

/* Лайтбокс для изображений */
.image-lightbox {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 20px;
    cursor: zoom-out;
}

.image-lightbox.active {
    display: flex;
}

.image-lightbox img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.image-lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.2s;
}

.image-lightbox-close:hover {
    background: #dc3545;
}

/* Модальное окно шаблонов */
.templates-list {
    max-height: 400px;
    overflow-y: auto;
}

.template-item {
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.template-item:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.template-item h6 {
    margin: 0 0 5px 0;
    color: #007bff;
    font-weight: 600;
}

.template-item p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

/* Уведомления */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 16px 20px;
    background: #28a745;
    color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateX(400px);
    transition: transform 0.3s ease;
    z-index: 3000;
    font-size: 14px;
    max-width: 350px;
}

.notification.show {
    transform: translateX(0);
}

.notification-error {
    background: #dc3545;
}

.notification-warning {
    background: #ffc107;
    color: #333;
}

/* Индикатор загрузки */
.loading {
    text-align: center;
    padding: 40px;
    color: #999;
}

.loading::before {
    content: '';
    display: inline-block;
    width: 24px;
    height: 24px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 10px;
    vertical-align: middle;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Адаптивность */
@media (max-width: 1200px) {
    .chats-sidebar {
        width: 320px;
    }
}

@media (max-width: 768px) {
    .chats-container {
        position: relative;
    }
    
    .chats-sidebar {
        width: 100%;
        position: absolute;
        z-index: 10;
    }
    
    .chat-area {
        display: none;
        width: 100%;
    }
    
    .chats-container.chat-open .chats-sidebar {
        display: none;
    }
    
    .chats-container.chat-open .chat-area {
        display: flex;
    }
    
    .user-info-sidebar {
        width: 100%;
    }
    
    .message-image {
        max-width: 200px;
    }
    
    .message {
        max-width: 85%;
    }
}

/* Кастомные скроллбары для всех элементов */
* {
    scrollbar-width: thin;
    scrollbar-color: #ddd transparent;
}

*::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

*::-webkit-scrollbar-track {
    background: transparent;
}

*::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 4px;
}

*::-webkit-scrollbar-thumb:hover {
    background: #ccc;
}

/* Улучшенная типографика */
.chat-messages {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Анимации */
.chat-item {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Фокус и доступность */
.btn:focus,
.input-action-btn:focus,
.send-btn:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* Темная тема (опционально) */
@media (prefers-color-scheme: dark) {
    /* Добавьте стили для темной темы здесь */
}

/* Список чатов */
.chats-list {
    flex: 1;
    overflow-y: auto;
}

.chat-item {
    display: flex;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s;
    gap: 12px;
}

.chat-item:hover {
    background: #f8f9fa;
}

.chat-item.active {
    background: #e8f0fe;
}

.chat-item.unread {
    background: #fff8e1;
}

.chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    flex-shrink: 0;
}

.chat-info {
    flex: 1;
    min-width: 0;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.chat-header h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
}

.chat-time {
    font-size: 12px;
    color: #999;
}

.chat-preview {
    font-size: 14px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 5px;
}

.chat-meta {
    display: flex;
    gap: 8px;
    align-items: center;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 12px;
}

.badge-primary {
    background: #007bff;
    color: white;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

/* Область чата */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: white;
}

.chat-area .chat-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-user-info h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.user-details {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #666;
}

.user-details span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.chat-actions {
    display: flex;
    gap: 10px;
}

/* Сообщения */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    gap: 10px;
    max-width: 70%;
}

.message.sent {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 500;
    flex-shrink: 0;
}

.message.received .message-avatar {
    background: #6c757d;
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.message-bubble {
    background: #f1f3f4;
    padding: 10px 15px;
    border-radius: 18px;
    max-width: 100%;
    word-wrap: break-word;
}

.message.sent .message-bubble {
    background: #007bff;
    color: white;
}

.message-text {
    font-size: 14px;
    line-height: 1.4;
}

/* Стили для файлов в сообщениях */
.message-file {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 12px;
    margin-top: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.message.sent .message-file {
    background: rgba(255, 255, 255, 0.15);
}

.message-file:hover {
    background: rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.message.sent .message-file:hover {
    background: rgba(255, 255, 255, 0.25);
}

.message-file-icon {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.message.sent .message-file-icon {
    background: rgba(255, 255, 255, 0.3);
}

.message-file-info {
    flex: 1;
    min-width: 0;
}

.message-file-name {
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-file-size {
    font-size: 12px;
    opacity: 0.8;
    margin-top: 2px;
}

/* Превью изображений в сообщениях */
.message-image {
    max-width: 300px;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.message-image:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.message-image img {
    width: 100%;
    height: auto;
    display: block;
}

.message-meta {
    display: flex;
    gap: 10px;
    font-size: 12px;
    color: #999;
    padding: 0 5px;
}

.read-indicator {
    color: #28a745;
}

.system-message {
    text-align: center;
    font-size: 13px;
    color: #999;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.system-message span {
    background: #f0f0f0;
    padding: 4px 12px;
    border-radius: 12px;
}

/* Форма ввода */
.chat-input-wrapper {
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    background: white;
}

.chat-input-form {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.input-group {
    flex: 1;
    display: flex;
    background: #f1f3f4;
    border-radius: 24px;
    padding: 5px 15px;
}

.chat-input {
    flex: 1;
    border: none;
    background: none;
    outline: none;
    resize: none;
    font-size: 14px;
    line-height: 1.4;
    max-height: 120px;
    padding: 8px 0;
}

.input-actions {
    display: flex;
    gap: 5px;
    align-items: center;
}

.input-action-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: none;
    color: #666;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.input-action-btn:hover {
    background: rgba(0,0,0,0.05);
    color: #333;
}

.send-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #007bff;
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.send-btn:hover {
    background: #0056b3;
}

.send-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

/* Превью файлов */
.file-preview {
    margin-top: 10px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f0f0f0;
    border-radius: 16px;
    font-size: 13px;
}

.file-preview-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0e0e0;
    border-radius: 8px;
}

.file-preview-icon img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 4px;
}

.file-info {
    display: flex;
    flex-direction: column;
}

.file-name {
    font-weight: 500;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-size {
    font-size: 11px;
    color: #666;
}

.remove-file {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 0 0 0 8px;
    font-size: 16px;
}

.remove-file:hover {
    color: #dc3545;
}

/* Пустое состояние */
.empty-chat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999;
}

.empty-chat i {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-chat h3 {
    margin: 0 0 10px 0;
    color: #666;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
}

/* Боковая панель с информацией */
.user-info-sidebar {
    width: 300px;
    background: white;
    border-left: 1px solid #e0e0e0;
    position: fixed;
    right: 0;
    top: 60px;
    bottom: 0;
    overflow-y: auto;
    box-shadow: -2px 0 5px rgba(0,0,0,0.1);
    z-index: 100;
}

.sidebar-header {
    padding: 20px;
    
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Кнопки в заголовке чата */
.chat-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}

.chat-actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    border: 1px solid #ddd;
    background: white;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.chat-actions .btn:hover {
    background: #f8f9fa;
    border-color: #ccc;
}

.chat-actions .btn i {
    font-size: 14px;
}

.chat-actions .btn-secondary {
    background: #f8f9fa;
}

.chat-actions .btn-secondary:hover {
    background: #e9ecef;
}

.chat-actions .btn-warning {
    background: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.chat-actions .btn-warning:hover {
    background: #ffeaa7;
}

/* Боковая панель с информацией о клиенте */
.user-info-sidebar {
    width: 400px;
    background: white;
    border-left: 1px solid #e0e0e0;
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
    box-shadow: -3px 0 10px rgba(0,0,0,0.05);
    z-index: 100;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.user-info-sidebar[style*="display: block"] {
    transform: translateX(0);
}

/* Заголовок боковой панели */
.sidebar-header {
    padding: 20px 24px;
    
    display: flex;
    justify-content: space-between;
    align-items: center;
    
}

.sidebar-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
}

.close-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #6c757d;
    cursor: pointer;
    padding: 8px;
    margin: -8px -8px -8px 0;
    border-radius: 4px;
    transition: all 0.2s;
    line-height: 1;
}

.close-btn:hover {
    background: rgba(0,0,0,0.05);
    color: #333;
}

/* Контент боковой панели */
.sidebar-content {
    padding: 0;
}

/* Секции информации */
.user-info-section {
    padding: 24px;
    border-bottom: 1px solid #f0f0f0;
}

.user-info-section:last-child {
    border-bottom: none;
}

.user-info-section h4 {
    margin: 0 0 20px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Элементы информации */
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-item label {
    color: #6c757d;
    font-size: 14px;
    font-weight: 400;
}

.info-item span {
    color: #1a1a1a;
    font-size: 14px;
    font-weight: 500;
    text-align: right;
}

/* Специальные стили для пустых значений */
.info-item span:contains("Не указан"),
.info-item span:contains("Не указана") {
    color: #adb5bd;
    font-weight: 400;
}

/* Форматирование телефона */
.info-item span[data-phone] {
    font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
}

/* Форматирование суммы */
.info-item span[data-currency] {
    font-feature-settings: "tnum";
    font-variant-numeric: tabular-nums;
}

/* Действия пользователя */
.user-actions {
    padding: 24px;
    background: #fafbfc;
    border-top: 1px solid #f0f0f0;
}

.user-actions a {
    display: block;
    width: 100%;
    padding: 12px 16px;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s;
    margin-bottom: 10px;
}

.user-actions a:last-child {
    margin-bottom: 0;
}

.user-actions .btn-primary {
    background: #007bff;
    color: white;
    border: 1px solid #007bff;
}

.user-actions .btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,123,255,0.2);
}

.user-actions .btn-secondary {
    background: white;
    color: #007bff;
    border: 1px solid #007bff;
}

.user-actions .btn-secondary:hover {
    background: #007bff;
    color: white;
}

/* Ссылка "Подробнее" */
.user-actions a[href*="orders"] {
    text-decoration: underline;
    text-underline-offset: 2px;
}

/* Адаптация для темной темы (опционально) */
@media (prefers-color-scheme: dark) {
    .user-info-sidebar {
        background: #1e1e1e;
        border-left-color: #333;
    }
    
    .sidebar-header {
        background: #252525;
        border-bottom-color: #333;
    }
    
    .sidebar-header h3 {
        color: #e0e0e0;
    }
    
    .close-btn {
        color: #adb5bd;
    }
    
    .close-btn:hover {
        background: rgba(255,255,255,0.1);
        color: #e0e0e0;
    }
    
    .user-info-section {
        border-bottom-color: #333;
    }
    
    .user-info-section h4 {
        color: #e0e0e0;
    }
    
    .info-item {
        border-bottom-color: #2a2a2a;
    }
    
    .info-item label {
        color: #adb5bd;
    }
    
    .info-item span {
        color: #e0e0e0;
    }
    
    .user-actions {
        background: #252525;
        border-top-color: #333;
    }
}

/* Анимация появления */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.user-info-sidebar.show {
    animation: slideInRight 0.3s ease-out;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .user-info-sidebar {
        width: 100%;
        max-width: 100%;
    }
    
    .chat-actions .btn {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .chat-actions .btn span {
        display: none; /* Скрываем текст на мобильных, оставляем только иконки */
    }
}

/* Состояния загрузки */
.user-info-sidebar .loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: #6c757d;
}

.user-info-sidebar .loading::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top-color: #007bff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-right: 10px;
}

/* Состояние ошибки */
.user-info-sidebar .error {
    text-align: center;
    padding: 40px;
    color: #dc3545;
    font-size: 14px;
}

.user-info-sidebar .error::before {
    content: '⚠️';
    display: block;
    font-size: 32px;
    margin-bottom: 10px;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 18px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #999;
    cursor: pointer;
}

.close-btn:hover {
    color: #333;
}

.sidebar-content {
    padding: 20px;
}

.user-info-section {
    margin-bottom: 30px;
}

.user-info-section h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #333;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item label {
    color: #666;
    font-size: 14px;
}

.info-item span {
    color: #333;
    font-size: 14px;
    font-weight: 500;
}

.user-actions {
    margin-top: 20px;
}

.user-actions .btn {
    margin-bottom: 10px;
}

/* Лайтбокс для изображений */
.image-lightbox {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 20px;
    cursor: zoom-out;
}

.image-lightbox.active {
    display: flex;
}

.image-lightbox img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.image-lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.2s;
}

.image-lightbox-close:hover {
    background: #dc3545;
}

/* Модальное окно шаблонов */
.templates-list {
    max-height: 400px;
    overflow-y: auto;
}

.template-item {
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.template-item:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.template-item h6 {
    margin: 0 0 5px 0;
    color: #007bff;
}

.template-item p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

/* Уведомления */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    background: #28a745;
    color: white;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transform: translateX(400px);
    transition: transform 0.3s;
    z-index: 1000;
}

.notification.show {
    transform: translateX(0);
}

.notification-error {
    background: #dc3545;
}

.notification-warning {
    background: #ffc107;
    color: #333;
}

/* Индикатор загрузки */
.loading {
    text-align: center;
    padding: 40px;
    color: #999;
}

/* Адаптивность */
@media (max-width: 768px) {
    .chats-sidebar {
        width: 100%;
    }
    
    .chat-area {
        display: none;
    }
    
    .chats-container.chat-open .chats-sidebar {
        display: none;
    }
    
    .chats-container.chat-open .chat-area {
        display: flex;
    }
    
    .user-info-sidebar {
        width: 100%;
    }
    
    .message-image {
        max-width: 200px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>