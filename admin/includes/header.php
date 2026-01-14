<?php
// admin/includes/header.php - Шапка админ-панели

// Проверяем, что страница вызывается из админки
if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', dirname(dirname(__FILE__)));
}

// Определяем активную страницу
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Админ-панель'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    
    <!-- Дополнительные стили -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6fa;
            color: #333;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Сайдбар */
        .admin-sidebar {
            width: 250px;
            background: #1e293b;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: width 0.3s ease;
        }
        
        .admin-sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 68px;
        }
        
        .sidebar-logo {
            font-size: 20px;
            font-weight: 700;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar-logo i {
            min-width: 20px;
            flex-shrink: 0;
        }
        
        .sidebar-logo span {
            opacity: 1;
            transition: opacity 0.3s;
        }
        
        .admin-sidebar.collapsed .sidebar-logo span {
            opacity: 0;
            width: 0;
        }
        
        .sidebar-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            flex-shrink: 0;
        }
        
        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .admin-sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }
        
        .sidebar-toggle i {
            transition: transform 0.3s;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-section {
            margin-bottom: 20px;
        }
        
        .nav-section-title {
            padding: 0 20px;
            margin-bottom: 10px;
            font-size: 12px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.3s;
        }
        
        .admin-sidebar.collapsed .nav-section-title {
            opacity: 0;
            height: 0;
            margin: 0;
            padding: 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
            white-space: nowrap;
        }
        
        .admin-sidebar.collapsed .nav-link {
            padding: 12px 0;
            justify-content: center;
            gap: 0;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link.active {
            background: #3b82f6;
            color: white;
        }
        
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: white;
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .nav-link span {
            opacity: 1;
            transition: opacity 0.3s;
        }
        
        .admin-sidebar.collapsed .nav-link span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        
        .admin-sidebar.collapsed .nav-badge {
            opacity: 0;
            width: 0;
            padding: 0;
        }
        
        /* Тултипы для свёрнутого сайдбара */
        .admin-sidebar.collapsed .nav-link {
            position: relative;
        }
        
        .admin-sidebar.collapsed .nav-link::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            z-index: 1001;
        }
        
        .admin-sidebar.collapsed .nav-link:hover::after {
            opacity: 1;
        }
        
        /* Основной контент */
        .admin-main {
            flex: 1;
            margin-left: 250px;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }
        
        .admin-sidebar.collapsed ~ .admin-main {
            margin-left: 70px;
        }
        
        /* Верхняя панель */
        .admin-header {
            background: white;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }
        
        .header-search {
            position: relative;
        }
        
        .header-search input {
            padding: 8px 16px 8px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 300px;
            font-size: 14px;
        }
        
        .header-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .header-btn {
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: background 0.3s;
        }
        
        .header-btn:hover {
            background: #f3f4f6;
        }
        
        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #f3f4f6;
            border-radius: 8px;
            cursor: pointer;
            position: relative;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-info {
            text-align: left;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-role {
            font-size: 12px;
            color: #6b7280;
        }
        
        /* Контент */
        .admin-content {
            padding: 24px;
            flex: 1;
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-sidebar.collapsed {
                width: 250px;
            }
            
            .admin-main {
                margin-left: 0 !important;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .header-search {
                display: none;
            }
            
            .sidebar-toggle {
                display: none;
            }
        }
        
        /* Дропдаун меню */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            min-width: 200px;
            margin-top: 8px;
            display: none;
            z-index: 1000;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .dropdown-item:hover {
            background: #f3f4f6;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Сайдбар -->
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/admin/" class="sidebar-logo">
                    <i class="fas fa-print"></i>
                    <span>Админ-панель</span>
                </a>
                <button class="sidebar-toggle" onclick="toggleSidebarCollapse()">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Основное меню -->
                <div class="nav-section">
                    <div class="nav-section-title">Основное</div>

                    <a href="/admin/index.php" class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>" data-tooltip="Дашборд">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Дашборд</span>
                    </a>

                    <a href="/admin/orders.php" class="nav-link <?php echo $current_page == 'orders' ? 'active' : ''; ?>" data-tooltip="Заказы">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Заказы</span>
                        <?php if (isset($stats['new_orders']) && $stats['new_orders'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['new_orders']; ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if (!isLocationAdmin()): ?>
                    <a href="/admin/chats.php" class="nav-link <?php echo $current_page == 'chats' ? 'active' : ''; ?>" data-tooltip="Чаты">
                        <i class="fas fa-comments"></i>
                        <span>Чаты</span>
                        <?php if (isset($stats['unread_chats']) && $stats['unread_chats'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['unread_chats']; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>

                    <?php if (isSuperAdmin()): ?>
                    <a href="/admin/services.php" class="nav-link <?php echo $current_page == 'services' ? 'active' : ''; ?>" data-tooltip="Услуги">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Услуги</span>
                    </a>
                    <?php endif; ?>

                    <a href="/admin/users.php" class="nav-link <?php echo $current_page == 'users' ? 'active' : ''; ?>" data-tooltip="Пользователи">
                        <i class="fas fa-users"></i>
                        <span>Пользователи</span>
                    </a>
                </div>

                <?php if (isSuperAdmin()): ?>
                <!-- Администрирование -->
                <div class="nav-section">
                    <div class="nav-section-title">Администрирование</div>

                    <a href="/admin/locations.php" class="nav-link <?php echo $current_page == 'locations' ? 'active' : ''; ?>" data-tooltip="Точки">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Точки</span>
                    </a>

                    <a href="/admin/admins.php" class="nav-link <?php echo $current_page == 'admins' ? 'active' : ''; ?>" data-tooltip="Администраторы">
                        <i class="fas fa-user-shield"></i>
                        <span>Администраторы</span>
                    </a>

                    <a href="/admin/settings.php" class="nav-link <?php echo $current_page == 'settings' ? 'active' : ''; ?>" data-tooltip="Настройки">
                        <i class="fas fa-cog"></i>
                        <span>Настройки</span>
                    </a>

                    <a href="/admin/logs.php" class="nav-link <?php echo $current_page == 'logs' ? 'active' : ''; ?>" data-tooltip="Логи">
                        <i class="fas fa-history"></i>
                        <span>Логи</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>
        
        <!-- Основной контент -->
        <main class="admin-main">
            <!-- Верхняя панель -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Поиск..." id="globalSearch">
                    </div>
                </div>
                
                <div class="header-right">
                    <!-- Быстрые действия -->
                    <button class="header-btn" id="quickActionsBtn">
                        <i class="fas fa-plus"></i>
                    </button>

                    <!-- Меню пользователя -->
                    <div class="user-menu" onclick="toggleUserMenu()">
                        <div class="user-avatar">
                            <?php
                            if (isLocationAdmin()) {
                                echo mb_substr($_SESSION['location_name'] ?? 'L', 0, 1);
                            } else {
                                echo mb_substr($_SESSION['admin_name'] ?? 'A', 0, 1);
                            }
                            ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name">
                                <?php
                                if (isLocationAdmin()) {
                                    echo htmlspecialchars($_SESSION['location_name'] ?? 'Location Admin');
                                } else {
                                    echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
                                }
                                ?>
                            </div>
                            <div class="user-role">
                                <?php
                                if (isLocationAdmin()) {
                                    echo 'Администратор точки';
                                } elseif (isSuperAdmin()) {
                                    echo 'Суперадминистратор';
                                } else {
                                    echo 'Администратор';
                                }
                                ?>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                        
                        <!-- Дропдаун меню -->
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="/admin/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Выйти</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Контент страницы -->
            <div class="admin-content">
                <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
                    <?php 
                    echo $_SESSION['flash_message']; 
                    unset($_SESSION['flash_message']);
                    unset($_SESSION['flash_type']);
                    ?>
                </div>
                <?php endif; ?>

<script>
// Функция сворачивания/разворачивания сайдбара
function toggleSidebarCollapse() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    
    // Сохраняем состояние в localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Функция для мобильного меню
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
}

// Восстанавливаем состояние сайдбара при загрузке
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }
});

// Функция переключения меню пользователя
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Закрытие дропдауна при клике вне его
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');

    if (userMenu && !userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }

    // Закрытие быстрых действий при клике вне его
    const quickActionsBtn = document.getElementById('quickActionsBtn');
    const quickActionsMenu = document.getElementById('quickActionsMenu');

    if (quickActionsMenu && !quickActionsBtn?.contains(event.target) && !quickActionsMenu.contains(event.target)) {
        quickActionsMenu.classList.remove('show');
    }
});

// Быстрые действия
const quickActionsBtn = document.getElementById('quickActionsBtn');
if (quickActionsBtn) {
    quickActionsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const menu = document.getElementById('quickActionsMenu');
        if (menu) {
            menu.classList.toggle('show');
        }
    });
}
</script>

<!-- Меню быстрых действий -->
<div class="quick-actions-menu" id="quickActionsMenu">
    <div class="quick-action-item" onclick="window.location.href='/admin/orders.php?action=create'">
        <i class="fas fa-shopping-cart"></i>
        <span>Новый заказ</span>
    </div>
    <div class="quick-action-item" onclick="window.location.href='/admin/users.php?action=create'">
        <i class="fas fa-user-plus"></i>
        <span>Новый клиент</span>
    </div>
    <?php if (isSuperAdmin()): ?>
    <div class="quick-action-item" onclick="window.location.href='/admin/services.php?action=create'">
        <i class="fas fa-clipboard-list"></i>
        <span>Новая услуга</span>
    </div>
    <div class="quick-action-item" onclick="window.location.href='/admin/locations.php?action=create'">
        <i class="fas fa-map-marked-alt"></i>
        <span>Новая точка</span>
    </div>
    <?php endif; ?>
</div>

<style>
.quick-actions-menu {
    position: fixed;
    top: 60px;
    right: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    z-index: 1000;
    display: none;
    overflow: hidden;
}

.quick-actions-menu.show {
    display: block;
    animation: slideDown 0.2s ease-out;
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

.quick-action-item {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: background 0.2s;
    color: #333;
}

.quick-action-item:hover {
    background: #f3f4f6;
}

.quick-action-item i {
    width: 20px;
    text-align: center;
    color: #3b82f6;
}

.quick-action-item span {
    font-size: 14px;
    font-weight: 500;
}
</style>