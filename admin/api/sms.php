<?php
// admin/api/sms.php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/SMS.php';
require_once '../includes/auth_check.php';

// Проверяем авторизацию
checkAdminAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$user_id = intval($input['user_id'] ?? 0);
$phone = $input['phone'] ?? '';
$message = trim($input['message'] ?? '');

// Валидация
if (!$user_id || !$phone || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}

if (strlen($message) > 800) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Сообщение слишком длинное']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Сохраняем в историю
    $query = "INSERT INTO sms_history (user_id, phone, message, sent_by, status) 
              VALUES (:user_id, :phone, :message, :sent_by, 'pending')";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':sent_by', $_SESSION['admin_id']);
    $stmt->execute();
    
    $sms_id = $db->lastInsertId();
    
    // Отправляем SMS
    $sms = new SMS();
    $result = $sms->send($phone, $message);
    
    // Обновляем статус
    if ($result['success']) {
        $update_query = "UPDATE sms_history SET status = 'sent' WHERE id = :id";
    } else {
        $update_query = "UPDATE sms_history SET status = 'failed', error_message = :error WHERE id = :id";
    }
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':id', $sms_id);
    
    if (!$result['success']) {
        $error_msg = $result['error'] ?? 'Неизвестная ошибка';
        $update_stmt->bindParam(':error', $error_msg);
    }
    
    $update_stmt->execute();
    
    // Логируем действие
    $log_query = "INSERT INTO admin_logs (admin_id, action, details, entity_type, entity_id, created_at) 
                  VALUES (:admin_id, 'send_sms', :details, 'user', :user_id, NOW())";
    
    $log_stmt = $db->prepare($log_query);
    $log_stmt->bindParam(':admin_id', $_SESSION['admin_id']);
    $log_details = "SMS отправлено пользователю. Телефон: {$phone}";
    $log_stmt->bindParam(':details', $log_details);
    $log_stmt->bindParam(':user_id', $user_id);
    $log_stmt->execute();
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
?>