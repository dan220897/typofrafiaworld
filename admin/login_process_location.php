<?php
session_start();
header('Content-Type: application/json');

require_once 'config/database.php';
require_once 'classes/Location.php';
require_once 'classes/Admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $location_code = $_POST['location_code'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($location_code) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Пожалуйста, заполните все поля'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $location = new Location($db);
    $locationData = $location->authenticate($location_code, $password);

    if (!$locationData) {
        echo json_encode([
            'success' => false,
            'message' => 'Неверная точка или пароль'
        ]);
        exit;
    }

    // Set session variables for location admin
    $_SESSION['admin_id'] = 'location_' . $locationData['id'];
    $_SESSION['admin_role'] = 'location_admin';
    $_SESSION['admin_type'] = 'location';
    $_SESSION['location_id'] = $locationData['id'];
    $_SESSION['location_name'] = $locationData['name'];
    $_SESSION['location_code'] = $locationData['code'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Log login
    try {
        $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, details, location_id) VALUES (?, 'login', 'Location admin login', ?)");
        $admin_id_log = 0; // Special ID for location admins
        $stmt->execute([$admin_id_log, $locationData['id']]);
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
