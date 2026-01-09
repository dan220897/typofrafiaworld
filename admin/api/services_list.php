<?php
// admin/api/services_list.php - API для получения списка активных услуг
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Service.php';
require_once '../includes/auth_check.php';

// Проверяем авторизацию
checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $db = $database->getConnection();
    $service = new Service($db);
    
    // Получаем только активные услуги
    $services = $service->getActiveServices();
    
    echo json_encode([
        'success' => true,
        'services' => $services
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>