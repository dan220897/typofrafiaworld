<?php
// admin/includes/footer.php - Футер админ-панели
?>
            </div><!-- .admin-content -->
        </main><!-- .admin-main -->
    </div><!-- .admin-wrapper -->
    
    <!-- Общие модальные окна -->
    <!-- Быстрое создание -->
    <div class="modal" id="quickCreateModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Быстрое создание</h3>
                <button class="modal-close" onclick="closeModal('quickCreateModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="quick-actions-grid">
                    <a href="/admin/order-create.php" class="quick-action-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Новый заказ</span>
                    </a>
                    <a href="/admin/user-create.php" class="quick-action-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Новый клиент</span>
                    </a>
                    <a href="/admin/service-create.php" class="quick-action-item">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Новая услуга</span>
                    </a>
                    <a href="/admin/promocode-create.php" class="quick-action-item">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Промокод</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Уведомления -->
    <div class="notifications-panel" id="notificationsPanel">
        <div class="notifications-header">
            <h4>Уведомления</h4>
            <button class="btn-text" onclick="markAllAsRead()">Прочитать все</button>
        </div>
        <div class="notifications-list" id="notificationsList">
            <!-- Загрузка уведомлений через JS -->
        </div>
    </div>
    
    <!-- Глобальный поиск -->
    <div class="search-overlay" id="searchOverlay">
        <div class="search-container">
            <input type="text" id="globalSearchInput" placeholder="Поиск заказов, клиентов, услуг...">
            <div class="search-results" id="searchResults"></div>
        </div>
    </div>
    
    <!-- Toast уведомления -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Основные скрипты -->
    <script>
        // Переключение сайдбара
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        
        // Переключение меню пользователя
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }
        
        
        
        // Переключение уведомлений
        function toggleNotifications() {
            const panel = document.getElementById('notificationsPanel');
            panel.classList.toggle('show');
            if (panel.classList.contains('show')) {
                loadNotifications();
            }
        }
        
        
        
        // Отображение уведомлений
        function renderNotifications(notifications) {
            const container = document.getElementById('notificationsList');
            
            if (notifications.length === 0) {
                container.innerHTML = '<div class="empty-state">Нет новых уведомлений</div>';
                return;
            }
            
            container.innerHTML = notifications.map(n => `
                <div class="notification-item ${n.is_read ? '' : 'unread'}" onclick="viewNotification(${n.id})">
                    <div class="notification-icon">
                        <i class="fas ${getNotificationIcon(n.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${n.title}</div>
                        <div class="notification-text">${n.message}</div>
                        <div class="notification-time">${formatTime(n.created_at)}</div>
                    </div>
                </div>
            `).join('');
        }
        
        // Иконка для типа уведомления
        function getNotificationIcon(type) {
            const icons = {
                'order': 'fa-shopping-cart',
                'chat': 'fa-comment',
                'payment': 'fa-credit-card',
                'system': 'fa-info-circle'
            };
            return icons[type] || 'fa-bell';
        }
        
        // Быстрые действия
        function showQuickActions() {
            document.getElementById('quickCreateModal').classList.add('show');
        }
        
        
        
        // Глобальный поиск
        const globalSearchInput = document.getElementById('globalSearch');
        let searchTimeout;
        
        globalSearchInput?.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                document.getElementById('searchOverlay').classList.remove('show');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performGlobalSearch(query);
            }, 300);
        });
        
        async function performGlobalSearch(query) {
            document.getElementById('searchOverlay').classList.add('show');
            document.getElementById('globalSearchInput').value = query;
            document.getElementById('globalSearchInput').focus();
            
            try {
                const response = await fetch(`/admin/api/search.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success) {
                    renderSearchResults(data.results);
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        }
        
        function renderSearchResults(results) {
            const container = document.getElementById('searchResults');
            
            if (results.length === 0) {
                container.innerHTML = '<div class="no-results">Ничего не найдено</div>';
                return;
            }
            
            const grouped = results.reduce((acc, item) => {
                if (!acc[item.type]) acc[item.type] = [];
                acc[item.type].push(item);
                return acc;
            }, {});
            
            container.innerHTML = Object.entries(grouped).map(([type, items]) => `
                <div class="search-group">
                    <div class="search-group-title">${getSearchTypeLabel(type)}</div>
                    ${items.map(item => `
                        <a href="${item.url}" class="search-result-item">
                            <i class="fas ${getSearchTypeIcon(type)}"></i>
                            <div class="search-result-content">
                                <div class="search-result-title">${item.title}</div>
                                <div class="search-result-meta">${item.meta}</div>
                            </div>
                        </a>
                    `).join('')}
                </div>
            `).join('');
        }
        
        // Toast уведомления
        function showNotification(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <i class="fas ${getToastIcon(type)}"></i>
                <span>${message}</span>
            `;
            
            document.getElementById('toastContainer').appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        function getToastIcon(type) {
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            return icons[type] || 'fa-info-circle';
        }
        
        // Форматирование времени
        function formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) return 'только что';
            if (diff < 3600000) return Math.floor(diff / 60000) + ' мин назад';
            if (diff < 86400000) return Math.floor(diff / 3600000) + ' ч назад';
            
            return date.toLocaleDateString('ru-RU');
        }
        
        // Автообновление активности
        setInterval(() => {
            fetch('/admin/api/heartbeat.php', { method: 'POST' });
        }, 60000); // Каждую минуту
        
        // Проверка новых уведомлений
        setInterval(() => {
            checkNewNotifications();
        }, 30000); // Каждые 30 секунд
        
        
    </script>
    
    <!-- Дополнительные стили -->
    <style>
        /* Модальные окна */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow: auto;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        /* Быстрые действия */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .quick-action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .quick-action-item:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }
        
        .quick-action-item i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3b82f6;
        }
        
        /* Панель уведомлений */
        .notifications-panel {
            position: fixed;
            right: -350px;
            top: 0;
            width: 350px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            z-index: 1500;
            transition: right 0.3s;
            display: flex;
            flex-direction: column;
        }
        
        .notifications-panel.show {
            right: 0;
        }
        
        .notifications-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notifications-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            display: flex;
            gap: 15px;
            transition: background 0.3s;
        }
        
        .notification-item:hover {
            background: #f9fafb;
        }
        
        .notification-item.unread {
            background: #eff6ff;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        /* Поиск */
        .search-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            z-index: 2500;
        }
        
        .search-overlay.show {
            display: block;
        }
        
        .search-container {
            max-width: 600px;
            margin: 100px auto 0;
        }
        
        #globalSearchInput {
            width: 100%;
            padding: 20px;
            font-size: 18px;
            border: none;
            border-radius: 8px;
        }
        
        .search-results {
            background: white;
            border-radius: 8px;
            margin-top: 10px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Toast уведомления */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 3000;
        }
        
        .toast {
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.3s;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast-success { border-left: 4px solid #10b981; }
        .toast-error { border-left: 4px solid #ef4444; }
        .toast-warning { border-left: 4px solid #f59e0b; }
        .toast-info { border-left: 4px solid #3b82f6; }
    </style>
    
    <!-- Дополнительные скрипты страницы -->
    <?php if (isset($page_scripts)): ?>
    <?php echo $page_scripts; ?>
    <?php endif; ?>
</body>
</html>