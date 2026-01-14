<?php
// admin/includes/auth_check.php - Универсальная проверка авторизации


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Admin.php';

// Функция проверки авторизации
function checkAdminAuth($requiredPermission = null) {
    // Проверяем базовую авторизацию
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['login_time'])) {
        redirectToLogin();
    }

    // Проверяем истечение сессии
    if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
        session_destroy();
        redirectToLogin('Сессия истекла. Войдите снова.');
    }

    // Определяем тип администратора
    $admin_type = $_SESSION['admin_type'] ?? 'regular';

    // Для location admin проверяем наличие location_id
    if ($admin_type === 'location') {
        if (!isset($_SESSION['location_id'])) {
            session_destroy();
            redirectToLogin('Ошибка сессии.');
        }

        // Обновляем время сессии
        $_SESSION['login_time'] = time();

        // Проверяем права доступа для location admin
        if ($requiredPermission && !hasLocationAdminPermission($requiredPermission)) {
            header('Location: /admin/403.php');
            exit;
        }

        return;
    }

    // Для super admin проверяем активность администратора в БД
    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, is_active, role FROM admins WHERE id = :id AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_SESSION['admin_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            session_destroy();
            redirectToLogin('Ваш аккаунт деактивирован.');
        }

        $admin = $stmt->fetch();

        // Обновляем данные роли в сессии
        $_SESSION['admin_role'] = $admin['role'];

        // Проверяем права доступа если указаны
        if ($requiredPermission && !Admin::hasPermission($requiredPermission)) {
            header('Location: /admin/403.php');
            exit;
        }

        // Продлеваем сессию
        $_SESSION['login_time'] = time();

    } catch (Exception $e) {
        error_log('Auth check error: ' . $e->getMessage());
        redirectToLogin('Ошибка системы.');
    }
}

// Функция проверки прав location admin
function hasLocationAdminPermission($permission) {
    // Location admins have limited permissions
    $allowedPermissions = [
        'view_orders',
        'edit_orders',
        'view_users',
        'view_reports'
    ];

    return in_array($permission, $allowedPermissions);
}

// Функция проверки - является ли пользователь super admin
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

// Функция проверки - является ли пользователь location admin
function isLocationAdmin() {
    return isset($_SESSION['admin_type']) && $_SESSION['admin_type'] === 'location';
}

// Функция получения location_id текущего пользователя
function getCurrentLocationId() {
    return $_SESSION['location_id'] ?? null;
}

// Функция перенаправления на страницу входа
function redirectToLogin($message = null) {
    $redirect = '/admin/login-location.php';
    if ($message) {
        $redirect .= '?error=' . urlencode($message);
    }
    header('Location: ' . $redirect);
    exit;
}





// Функция проверки Ajax запроса
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}




?>