<?php
// admin/users.php - Управление пользователями
session_start();
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/User.php';

// Временно добавляем функцию hasPermission здесь, если файл functions.php отсутствует
if (!function_exists('hasPermission')) {
    function hasPermission($permission) {
        if (!isset($_SESSION['admin_role'])) {
            return false;
        }
        
        // Суперадмин имеет все права
        if ($_SESSION['admin_role'] === 'super_admin') {
            return true;
        }
        
        // Определяем права для каждой роли
        $rolePermissions = [
            'manager' => [
                'view_orders', 'edit_orders',
                'view_users', 'edit_users',
                'view_chats', 'send_messages',
                'view_services',
                'view_reports'
            ],
            'operator' => [
                'view_orders',
                'view_users',
                'view_chats', 'send_messages'
            ]
        ];
        
        $userRole = $_SESSION['admin_role'];
        
        if (isset($rolePermissions[$userRole])) {
            return in_array($permission, $rolePermissions[$userRole]);
        }
        
        return false;
    }
}

// Добавляем необходимые функции форматирования
if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = 'd.m.Y H:i') {
        if (empty($datetime)) {
            return '—';
        }
        $date = new DateTime($datetime);
        return $date->format($format);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd.m.Y') {
        return formatDateTime($date, $format);
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price, $decimals = 0) {
        return number_format($price, $decimals, ',', ' ');
    }
}

// Проверяем авторизацию и права
checkAdminAuth('view_users');

// Проверяем и определяем константы, если они не определены
if (!defined('BOT_TOKEN')) {
    define('BOT_TOKEN', '7864754568:AAHJ_SGi9qq7xL49JdMAW-CVYmZSCr6XaRo');
}

if (!defined('MANAGER_CHAT_ID')) {
    define('MANAGER_CHAT_ID', '7497468073');
}

if (!defined('SMS_RU_API_KEY')) {
    define('SMS_RU_API_KEY', '658A225F-F674-C908-78C2-BBE9E3A5F69D');
}

// Получаем параметры фильтрации и сортировки
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$period = $_GET['period'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$view_mode = $_GET['view'] ?? 'table';

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Получаем пользователей с фильтрами
$filters = [
    'status' => $status,
    'search' => $search,
    'period' => $period
];

$offset = ($page - 1) * $per_page;
$users = $user->getUsers($filters, $per_page, $offset);
$total_users = $user->getUsersCount($filters);
$total_pages = ceil($total_users / $per_page);

// Получаем статистику
$stats = $user->getUsersStats('month');

// AJAX обработчики
if (isset($_GET['action']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'block':
            $user_id = intval($_POST['user_id']);
            $result = $user->blockUser($user_id);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'unblock':
            $user_id = intval($_POST['user_id']);
            $result = $user->unblockUser($user_id);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'verify':
            $user_id = intval($_POST['user_id']);
            $result = $user->verifyUser($user_id);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete':
            if (!hasPermission('delete_users')) {
                echo json_encode(['success' => false, 'message' => 'Нет прав']);
                exit;
            }
            $user_id = intval($_POST['user_id']);
            $result = $user->delete($user_id);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'export':
            $data = $user->exportUsers($filters, 'csv');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
            echo $data;
            exit;
    }
}

// Заголовок страницы
$page_title = 'Управление пользователями';

// Проверяем, есть ли header.php
if (file_exists('includes/header.php')) {
    require_once 'includes/header.php';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи - Админ панель</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Базовые стили */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --gray: #6b7280;
            --light: #f9fafb;
            --white: #ffffff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--light);
            color: var(--dark);
        }

        .main-content {
            
            min-height: 100vh;
        }

        /* Страница пользователей */
        .users-page {
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        /* Статистика пользователей */
        .users-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.yellow { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-icon.purple { background: rgba(99, 102, 241, 0.1); color: var(--secondary); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray);
        }

        /* Фильтры и поиск */
        .filters-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }

        .filters-row {
            display: flex;
            gap: 16px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--gray);
        }

        .form-control {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-box {
            position: relative;
            flex: 2;
        }

        .search-box input {
            padding-left: 40px;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        /* Таблица пользователей */
        .users-table-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
        }

        .view-toggle {
            display: flex;
            gap: 8px;
        }

        .view-btn {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            background: var(--white);
            cursor: pointer;
            transition: all 0.3s;
        }

        .view-btn:first-child {
            border-radius: 6px 0 0 6px;
        }

        .view-btn:last-child {
            border-radius: 0 6px 6px 0;
        }

        .view-btn.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        /* Таблица */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 16px;
            font-weight: 600;
            color: var(--gray);
            background: #f9fafb;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        tr:hover {
            background: #f9fafb;
        }

        /* Информация о пользователе */
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 500;
            margin-bottom: 2px;
        }

        .user-meta {
            font-size: 12px;
            color: var(--gray);
        }

        /* Статусы */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-badge.verified {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.unverified {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.blocked {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Действия */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: var(--white);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: var(--gray);
        }

        .action-btn:hover {
            background: #f3f4f6;
            color: var(--dark);
        }

        .action-btn.danger:hover {
            background: #fee2e2;
            color: var(--danger);
            border-color: #fecaca;
        }

        /* Карточки пользователей (grid view) */
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .user-card {
            background: var(--white);
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .user-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .user-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }

        .user-card-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 600;
        }

        .user-card-info {
            margin-bottom: 16px;
        }

        .user-card-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .user-card-contact {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 2px;
        }

        .user-card-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }

        .user-card-stat {
            text-align: center;
        }

        .user-card-stat-value {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }

        .user-card-stat-label {
            font-size: 12px;
            color: var(--gray);
        }

        /* Модальное окно */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 12px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* Кнопки */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--dark);
            border: 1px solid #e5e7eb;
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }

        /* Детали пользователя */
        .user-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .detail-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }

        .detail-title {
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray);
            font-size: 14px;
        }

        .detail-value {
            font-weight: 500;
            font-size: 14px;
        }

        /* История заказов */
        .orders-history {
            margin-top: 24px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--white);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .order-info {
            flex: 1;
        }

        .order-number {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .order-date {
            font-size: 13px;
            color: var(--gray);
        }

        .order-amount {
            font-weight: 600;
            font-size: 16px;
        }

        /* Пагинация */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 20px;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: var(--white);
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .pagination-btn:hover {
            background: #f3f4f6;
        }

        .pagination-btn.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Загрузка */
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Адаптив */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .users-grid {
                grid-template-columns: 1fr;
            }

            .user-detail-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Основной контент -->
    <main class="main-content">
        <div class="users-page">
            <!-- Заголовок страницы -->
            <div class="page-header">
                <h1 class="page-title">Управление пользователями</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="exportUsers()">
                        <i class="fas fa-download"></i>
                        Экспорт
                    </button>
                    <?php if (hasPermission('edit_users')): ?>
                    <button class="btn btn-primary" onclick="addUser()">
                        <i class="fas fa-user-plus"></i>
                        Добавить
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Статистика пользователей -->
            <div class="users-stats">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Всего пользователей</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['verified_users']); ?></div>
                    <div class="stat-label">Верифицированных</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['new_users']); ?></div>
                    <div class="stat-label">Новых за месяц</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['companies']); ?></div>
                    <div class="stat-label">Компаний</div>
                </div>
            </div>

            <!-- Фильтры -->
            <div class="filters-card">
                <form method="GET" action="">
                    <div class="filters-row">
                        <div class="filter-group search-box">
                            <label class="filter-label">Поиск</label>
                            <i style="margin-top: 14px;" class="fas fa-search"></i>
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Имя, телефон, email, компания...">
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Статус</label>
                            <select class="form-control" name="status">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все статусы</option>
                                <option value="verified" <?php echo $status === 'verified' ? 'selected' : ''; ?>>Верифицированные</option>
                                <option value="unverified" <?php echo $status === 'unverified' ? 'selected' : ''; ?>>Неверифицированные</option>
                                <option value="blocked" <?php echo $status === 'blocked' ? 'selected' : ''; ?>>Заблокированные</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Период регистрации</label>
                            <select class="form-control" name="period">
                                <option value="" <?php echo $period === '' ? 'selected' : ''; ?>>Все время</option>
                                <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>Сегодня</option>
                                <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>За неделю</option>
                                <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>За месяц</option>
                                <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>За год</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit">Применить</button>
                    </div>
                </form>
            </div>

            <!-- Таблица пользователей -->
            <div class="users-table-card">
                <div class="table-header">
                    <h3 class="table-title">Список пользователей</h3>
                    <div class="view-toggle">
                        <button class="view-btn <?php echo $view_mode === 'table' ? 'active' : ''; ?>" 
                                onclick="setView('table')">
                            <i class="fas fa-list"></i>
                        </button>
                        <button class="view-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>" 
                                onclick="setView('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>

                <!-- Табличный вид -->
                <div id="tableView" class="table-responsive" style="<?php echo $view_mode === 'grid' ? 'display:none;' : ''; ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Пользователь</th>
                                <th>Контакты</th>
                                <th>Компания</th>
                                <th>Регистрация</th>
                                <th>Заказов</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user_data): ?>
                            <tr>
                                <td>#<?php echo $user_data['id']; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo mb_substr($user_data['name'] ?: 'U', 0, 2); ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($user_data['name'] ?: 'Без имени'); ?></div>
                                            <div class="user-meta">ID: <?php echo $user_data['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($user_data['phone']); ?></div>
                                    <?php if ($user_data['email']): ?>
                                    <div style="font-size: 12px; color: var(--gray);">
                                        <?php echo htmlspecialchars($user_data['email']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user_data['company_name']): ?>
                                    <div><?php echo htmlspecialchars($user_data['company_name']); ?></div>
                                    <?php if ($user_data['inn']): ?>
                                    <div style="font-size: 12px; color: var(--gray);">
                                        ИНН: <?php echo htmlspecialchars($user_data['inn']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <div>—</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($user_data['created_at'])); ?></td>
                                <td><?php echo $user_data['orders_count']; ?></td>
                                <td style="font-weight: 600;">₽<?php echo number_format($user_data['total_spent'], 0, ',', ' '); ?></td>
                                <td>
                                    <?php if ($user_data['is_blocked']): ?>
                                        <span class="status-badge blocked">
                                            <i class="fas fa-ban"></i>
                                            Заблокирован
                                        </span>
                                    <?php elseif ($user_data['is_verified']): ?>
                                        <span class="status-badge verified">
                                            <i class="fas fa-check-circle"></i>
                                            Верифицирован
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge unverified">
                                            <i class="fas fa-clock"></i>
                                            Не верифицирован
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="Просмотр" 
                                                onclick="viewUser(<?php echo $user_data['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if (hasPermission('edit_users')): ?>
                                        <button class="action-btn" title="Редактировать" 
                                                onclick="editUser(<?php echo $user_data['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button class="action-btn" title="Чат" 
                                                onclick="openUserChat(<?php echo $user_data['id']; ?>)">
                                            <i class="fas fa-comments"></i>
                                        </button>
                                        <button class="action-btn" title="Отправить SMS" 
        onclick="openSmsModal(<?php echo $user_data['id']; ?>, '<?php echo htmlspecialchars($user_data['phone']); ?>', '<?php echo htmlspecialchars($user_data['name'] ?: 'Пользователь'); ?>')">
    <i class="fas fa-sms"></i>
</button>
                                        <?php if (hasPermission('edit_users')): ?>
                                            <?php if ($user_data['is_blocked']): ?>
                                            <button class="action-btn" title="Разблокировать" 
                                                    onclick="unblockUser(<?php echo $user_data['id']; ?>)">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="action-btn danger" title="Заблокировать" 
                                                    onclick="blockUser(<?php echo $user_data['id']; ?>)">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Карточный вид -->
                <div id="gridView" class="users-grid" style="<?php echo $view_mode === 'table' ? 'display:none;' : ''; ?>">
                    <?php foreach ($users as $user_data): ?>
                    <div class="user-card" onclick="viewUser(<?php echo $user_data['id']; ?>)">
                        <div class="user-card-header">
                            <div class="user-card-avatar">
                                <?php echo mb_substr($user_data['name'] ?: 'U', 0, 2); ?>
                            </div>
                            <?php if ($user_data['is_blocked']): ?>
                                <span class="status-badge blocked">
                                    <i class="fas fa-ban"></i>
                                    Заблокирован
                                </span>
                            <?php elseif ($user_data['is_verified']): ?>
                                <span class="status-badge verified">
                                    <i class="fas fa-check-circle"></i>
                                    Верифицирован
                                </span>
                            <?php else: ?>
                                <span class="status-badge unverified">
                                    <i class="fas fa-clock"></i>
                                    Не верифицирован
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="user-card-info">
                            <div class="user-card-name"><?php echo htmlspecialchars($user_data['name'] ?: 'Без имени'); ?></div>
                            <div class="user-card-contact"><?php echo htmlspecialchars($user_data['phone']); ?></div>
                            <?php if ($user_data['email']): ?>
                            <div class="user-card-contact"><?php echo htmlspecialchars($user_data['email']); ?></div>
                            <?php endif; ?>
                            <?php if ($user_data['company_name']): ?>
                            <div class="user-card-contact"><?php echo htmlspecialchars($user_data['company_name']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="user-card-stats">
                            <div class="user-card-stat">
                                <div class="user-card-stat-value"><?php echo $user_data['orders_count']; ?></div>
                                <div class="user-card-stat-label">Заказов</div>
                            </div>
                            <div class="user-card-stat">
                                <div class="user-card-stat-value">
                                    ₽<?php echo number_format($user_data['total_spent'] / 1000, 1); ?>k
                                </div>
                                <div class="user-card-stat-label">Сумма</div>
                            </div>
                            <div class="user-card-stat">
                                <div class="user-card-stat-value">
                                    <?php 
                                    $days = floor((time() - strtotime($user_data['created_at'])) / 86400);
                                    echo $days . 'д';
                                    ?>
                                </div>
                                <div class="user-card-stat-label">С нами</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Пагинация -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <button class="pagination-btn" <?php echo $page <= 1 ? 'disabled' : ''; ?> 
                            onclick="changePage(<?php echo $page - 1; ?>)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1):
                    ?>
                        <button class="pagination-btn" onclick="changePage(1)">1</button>
                        <?php if ($start_page > 2): ?>
                            <span style="color: var(--gray);">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <button class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>" 
                                onclick="changePage(<?php echo $i; ?>)">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span style="color: var(--gray);">...</span>
                        <?php endif; ?>
                        <button class="pagination-btn" onclick="changePage(<?php echo $total_pages; ?>)">
                            <?php echo $total_pages; ?>
                        </button>
                    <?php endif; ?>
                    
                    <button class="pagination-btn" <?php echo $page >= $total_pages ? 'disabled' : ''; ?> 
                            onclick="changePage(<?php echo $page + 1; ?>)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Модальное окно просмотра пользователя -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="font-size: 20px;">Информация о пользователе</h2>
                <button class="btn btn-secondary" onclick="closeModal('userModal')" style="padding: 8px 12px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="loading active">
                    <div class="spinner"></div>
                </div>
                <div id="userDetails" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="openUserChat(currentUserId)">
                    <i class="fas fa-comments"></i>
                    Открыть чат
                </button>
                <?php if (hasPermission('edit_users')): ?>
                <button class="btn btn-primary" onclick="editUser(currentUserId)">
                    <i class="fas fa-edit"></i>
                    Редактировать
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Модальное окно отправки SMS -->
<div class="modal" id="smsModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 style="font-size: 20px;">Отправить SMS</h2>
            <button class="btn btn-secondary" onclick="closeModal('smsModal')" style="padding: 8px 12px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="sms-recipient" style="margin-bottom: 20px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 14px; color: var(--gray); margin-bottom: 4px;">Получатель:</div>
                <div style="font-weight: 600;" id="smsRecipientName"></div>
                <div style="color: var(--gray);" id="smsRecipientPhone"></div>
            </div>
            
            <div class="form-group">
                <label class="filter-label">Текст сообщения</label>
                <textarea id="smsMessage" class="form-control" rows="4" 
                          placeholder="Введите текст сообщения..." 
                          style="resize: vertical; min-height: 100px;"></textarea>
                <div style="margin-top: 8px; font-size: 13px; color: var(--gray);">
                    <span id="smsCharCount">0</span> символов / <span id="smsParts">1</span> SMS
                </div>
            </div>
            
            <div class="sms-templates" style="margin-top: 16px;">
                <div style="font-size: 14px; font-weight: 500; margin-bottom: 8px;">Быстрые шаблоны:</div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="btn btn-secondary" style="font-size: 13px; padding: 6px 12px;" 
                            onclick="insertTemplate('Ваш заказ готов к выдаче. Ждем вас в рабочее время.')">
                        Заказ готов
                    </button>
                    <button class="btn btn-secondary" style="font-size: 13px; padding: 6px 12px;" 
                            onclick="insertTemplate('Напоминаем о необходимости оплатить заказ.')">
                        Напоминание об оплате
                    </button>
                    <button class="btn btn-secondary" style="font-size: 13px; padding: 6px 12px;" 
                            onclick="insertTemplate('У нас есть специальное предложение для вас!')">
                        Спецпредложение
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('smsModal')">
                Отмена
            </button>
            <button class="btn btn-primary" onclick="sendSms()" id="sendSmsBtn">
                <i class="fas fa-paper-plane"></i>
                Отправить SMS
            </button>
        </div>
    </div>
</div>

    <script>
        let currentUserId = null;

        // Переключение вида
        function setView(view) {
            const tableView = document.getElementById('tableView');
            const gridView = document.getElementById('gridView');
            const viewBtns = document.querySelectorAll('.view-btn');
            
            // Сохраняем выбранный вид
            const url = new URL(window.location);
            url.searchParams.set('view', view);
            window.history.pushState({}, '', url);
            
            if (view === 'table') {
                tableView.style.display = 'block';
                gridView.style.display = 'none';
                viewBtns[0].classList.add('active');
                viewBtns[1].classList.remove('active');
            } else {
                tableView.style.display = 'none';
                gridView.style.display = 'grid';
                viewBtns[0].classList.remove('active');
                viewBtns[1].classList.add('active');
            }
        }

        // Просмотр пользователя
        async function viewUser(userId) {
            currentUserId = userId;
            const modal = document.getElementById('userModal');
            const loading = modal.querySelector('.loading');
            const details = document.getElementById('userDetails');
            
            modal.classList.add('active');
            loading.classList.add('active');
            details.style.display = 'none';
            
            try {
                const response = await fetch(`api/users.php?action=get&id=${userId}`);
                const data = await response.json();
                
                if (data.success) {
                    details.innerHTML = renderUserDetails(data.user);
                    loading.classList.remove('active');
                    details.style.display = 'block';
                } else {
                    throw new Error(data.message || 'Ошибка загрузки данных');
                }
            } catch (error) {
                alert('Ошибка: ' + error.message);
                closeModal('userModal');
            }
        }

        // Отрисовка деталей пользователя
        function renderUserDetails(user) {
            return `
                <div class="user-detail-grid">
                    <div class="detail-section">
                        <h3 class="detail-title">
                            <i class="fas fa-user"></i>
                            Личная информация
                        </h3>
                        <div class="detail-row">
                            <span class="detail-label">ID</span>
                            <span class="detail-value">#${user.id}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Имя</span>
                            <span class="detail-value">${user.name || 'Не указано'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Телефон</span>
                            <span class="detail-value">${user.phone}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">${user.email || 'Не указан'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Telegram ID</span>
                            <span class="detail-value">${user.telegram_id || 'Не привязан'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Статус</span>
                            <span class="detail-value">
                                ${user.is_blocked ? 
                                    '<span class="status-badge blocked">Заблокирован</span>' : 
                                    user.is_verified ? 
                                        '<span class="status-badge verified">Верифицирован</span>' : 
                                        '<span class="status-badge unverified">Не верифицирован</span>'
                                }
                            </span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 class="detail-title">
                            <i class="fas fa-building"></i>
                            Компания
                        </h3>
                        <div class="detail-row">
                            <span class="detail-label">Название</span>
                            <span class="detail-value">${user.company_name || 'Не указано'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ИНН</span>
                            <span class="detail-value">${user.inn || 'Не указан'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Регистрация</span>
                            <span class="detail-value">${formatDate(user.created_at)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Последняя активность</span>
                            <span class="detail-value">${formatDate(user.last_activity_at)}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section" style="margin-top: 24px;">
                    <h3 class="detail-title">
                        <i class="fas fa-chart-line"></i>
                        Статистика
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                        <div style="text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: var(--primary);">${user.orders_count}</div>
                            <div style="font-size: 12px; color: var(--gray);">Заказов</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: var(--success);">₽${formatPrice(user.total_spent)}</div>
                            <div style="font-size: 12px; color: var(--gray);">Общая сумма</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: var(--warning);">₽${formatPrice(user.average_order)}</div>
                            <div style="font-size: 12px; color: var(--gray);">Средний чек</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; color: var(--secondary);">100%</div>
                            <div style="font-size: 12px; color: var(--gray);">Оплачено</div>
                        </div>
                    </div>
                </div>

                ${user.sms_history && user.sms_history.length > 0 ? `
                <div class="sms-history" style="margin-top: 24px;">
                    <h3 class="detail-title">
                        <i class="fas fa-sms"></i>
                        История SMS
                    </h3>
                    ${user.sms_history.map(sms => `
                        <div class="sms-item" style="padding: 12px; background: ${sms.status === 'sent' ? '#f0fdf4' : '#fef2f2'}; border-radius: 8px; margin-bottom: 8px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <div>
                                    <span class="status-badge ${sms.status === 'sent' ? 'verified' : 'blocked'}" style="font-size: 11px;">
                                        ${sms.status === 'sent' ? 'Отправлено' : 'Ошибка'}
                                    </span>
                                    <span style="color: var(--gray); font-size: 13px; margin-left: 8px;">
                                        ${formatDate(sms.created_at)}
                                    </span>
                                </div>
                                <div style="font-size: 13px; color: var(--gray);">
                                    ${sms.admin_name}
                                </div>
                            </div>
                            <div style="font-size: 14px; color: var(--dark);">
                                ${sms.message}
                            </div>
                            ${sms.error_message ? `
                                <div style="font-size: 12px; color: var(--danger); margin-top: 4px;">
                                    Ошибка: ${sms.error_message}
                                </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
                ` : ''}

                ${user.recent_orders && user.recent_orders.length > 0 ? `
                <div class="orders-history">
                    <h3 class="detail-title">
                        <i class="fas fa-shopping-cart"></i>
                        История заказов
                    </h3>
                    ${user.recent_orders.map(order => `
                        <div class="order-item">
                            <div class="order-info">
                                <div class="order-number">#${order.order_number}</div>
                                <div class="order-date">${formatDate(order.created_at)} - ${order.services_list || 'Услуги'}</div>
                            </div>
                            <div class="order-amount">₽${formatPrice(order.final_amount)}</div>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
            `;
        }

        // Закрытие модального окна
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Редактирование пользователя
        function editUser(userId) {
            console.log('Редактирование пользователя:', userId);
            // Здесь можно открыть модальное окно редактирования
            // или перейти на страницу редактирования
            window.location.href = `user_edit.php?id=${userId}`;
        }

        // Открытие чата с пользователем
        function openUserChat(userId) {
            window.location.href = `chats.php?user=${userId}`;
        }

        // Блокировка пользователя
        async function blockUser(userId) {
            if (!confirm('Вы уверены, что хотите заблокировать пользователя?')) {
                return;
            }
            
            try {
                const response = await fetch('users.php?action=block', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Не удалось заблокировать пользователя'));
                }
            } catch (error) {
                alert('Ошибка: ' + error.message);
            }
        }

        // Разблокировка пользователя
        async function unblockUser(userId) {
            if (!confirm('Вы уверены, что хотите разблокировать пользователя?')) {
                return;
            }
            
            try {
                const response = await fetch('users.php?action=unblock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Не удалось разблокировать пользователя'));
                }
            } catch (error) {
                alert('Ошибка: ' + error.message);
            }
        }

        // Добавление пользователя
        function addUser() {
            console.log('Добавление нового пользователя');
            // Открытие модального окна добавления
            window.location.href = 'user-create.php';
        }

        // Экспорт пользователей
        function exportUsers() {
            const params = new URLSearchParams(window.location.search);
            params.set('action', 'export');
            window.location.href = 'users.php?' + params.toString();
        }

        // Изменение страницы
        function changePage(page) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }

        // Форматирование даты
        function formatDate(dateString) {
            if (!dateString) return 'Нет данных';
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Форматирование цены
        function formatPrice(price) {
            return parseFloat(price).toLocaleString('ru-RU');
        }

        // Поиск с задержкой
        let searchTimeout;
        const searchInput = document.querySelector('.search-box input');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }, 500);
            });
        }

        // Закрытие модального окна по клику вне его
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Загрузка данных при инициализации
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Страница пользователей загружена');
        });

        // Переменные для SMS
let currentSmsUserId = null;
let currentSmsPhone = null;

// Открытие модального окна SMS
function openSmsModal(userId, phone, name) {
    currentSmsUserId = userId;
    currentSmsPhone = phone;
    
    document.getElementById('smsRecipientName').textContent = name;
    document.getElementById('smsRecipientPhone').textContent = phone;
    document.getElementById('smsMessage').value = '';
    updateSmsCounter();
    
    document.getElementById('smsModal').classList.add('active');
}

// Вставка шаблона
function insertTemplate(text) {
    document.getElementById('smsMessage').value = text;
    updateSmsCounter();
}

// Подсчет символов и SMS
function updateSmsCounter() {
    const message = document.getElementById('smsMessage').value;
    const length = message.length;
    let parts = 1;
    
    if (length > 160) {
        parts = Math.ceil(length / 153);
    }
    
    document.getElementById('smsCharCount').textContent = length;
    document.getElementById('smsParts').textContent = parts;
}

// Обработчик изменения текста
document.addEventListener('DOMContentLoaded', function() {
    const smsTextarea = document.getElementById('smsMessage');
    if (smsTextarea) {
        smsTextarea.addEventListener('input', updateSmsCounter);
    }
});

// Отправка SMS
async function sendSms() {
    const message = document.getElementById('smsMessage').value.trim();
    
    if (!message) {
        alert('Введите текст сообщения');
        return;
    }
    
    if (!confirm('Отправить SMS сообщение?')) {
        return;
    }
    
    const sendBtn = document.getElementById('sendSmsBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
    
    try {
        const response = await fetch('api/sms.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: currentSmsUserId,
                phone: currentSmsPhone,
                message: message
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('SMS успешно отправлено!');
            closeModal('smsModal');
            
            // Если модальное окно пользователя открыто, обновляем информацию
            if (currentUserId === currentSmsUserId && document.getElementById('userModal').classList.contains('active')) {
                viewUser(currentUserId);
            }
        } else {
            alert('Ошибка отправки SMS: ' + (data.error || 'Неизвестная ошибка'));
        }
    } catch (error) {
        alert('Ошибка: ' + error.message);
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Отправить SMS';
    }
}
    </script>
</body>
</html>