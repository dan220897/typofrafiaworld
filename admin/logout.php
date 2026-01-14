<?php
session_start();

// Сохраняем тип админа для редиректа
$admin_type = $_SESSION['admin_type'] ?? 'location';

// Уничтожаем все данные сессии
$_SESSION = array();

// Удаляем сессионную cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на соответствующую страницу логина
if ($admin_type === 'super') {
    header("Location: login-superadmin.php?logout=1");
} else {
    header("Location: login-location.php?logout=1");
}
exit();
?>