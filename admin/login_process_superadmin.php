<?php
session_start();
header('Content-Type: application/json');

require_once 'config/database.php';
require_once 'classes/Admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Пожалуйста, заполните все поля'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $admin = new Admin($db);
    $adminData = $admin->login($username, $password);

    if (!$adminData) {
        echo json_encode([
            'success' => false,
            'message' => 'Неверный логин или пароль'
        ]);
        exit;
    }

    // Check if user is super admin
    if ($adminData['role'] !== 'super_admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Доступ запрещен. Этот вход только для суперадминистратора'
        ]);
        exit;
    }

    // Set session variables
    $_SESSION['admin_id'] = $adminData['id'];
    $_SESSION['admin_username'] = $adminData['username'];
    $_SESSION['admin_name'] = $adminData['full_name'];
    $_SESSION['admin_email'] = $adminData['email'];
    $_SESSION['admin_role'] = $adminData['role'];
    $_SESSION['admin_type'] = 'super';
    $_SESSION['location_id'] = null; // Super admin has access to all locations
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('admin_token', $token, time() + (86400 * 30), '/'); // 30 days

        // Store token in database (you may want to create a table for this)
        try {
            $stmt = $db->prepare("UPDATE admins SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $adminData['id']]);
        } catch (Exception $e) {
            // Continue even if remember token fails
        }
    }

    // Log login
    try {
        $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'login', 'Super admin login')");
        $stmt->execute([$adminData['id']]);
    } catch (Exception $e) {
        // Continue even if logging fails
    }

    // Log to login_history table
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $db->prepare("INSERT INTO login_history (user_id, user_type, ip_address, user_agent, status) VALUES (?, 'admin', ?, ?, 'success')");
        $stmt->execute([$adminData['id'], $ip_address, $user_agent]);
    } catch (Exception $e) {
        // Continue even if logging fails
    }

    echo json_encode([
        'success' => true,
        'message' => 'Успешный вход',
        'redirect' => 'index.php'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
