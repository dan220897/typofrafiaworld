<?php
// admin/api/orders.php - API для работы с заказами
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../classes/AdminLog.php';

// Проверяем авторизацию
checkAdminAuth();

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$adminLog = new AdminLog($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Получить детали заказа
                $order_id = intval($_GET['id']);
                $order_data = $order->getOrderById($order_id);
                
                if (!$order_data) {
                    throw new Exception('Заказ не найден');
                }
                
                echo json_encode([
                    'success' => true,
                    'order' => $order_data
                ]);
            } else if ($action == 'stats') {
                // Получить статистику
                $period = $_GET['period'] ?? 'month';
                $stats = $order->getOrderStats($period);
                
                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
            } else {
                // Получить список заказов
                $filters = [
                    'search' => $_GET['search'] ?? null,
                    'status' => $_GET['status'] ?? null,
                    'payment_status' => $_GET['payment_status'] ?? null,
                    'date_from' => $_GET['date_from'] ?? null,
                    'date_to' => $_GET['date_to'] ?? null,
                    'user_id' => $_GET['user_id'] ?? null
                ];
                
                $limit = intval($_GET['limit'] ?? 50);
                $offset = intval($_GET['offset'] ?? 0);
                
                $orders = $order->getOrders($filters, $limit, $offset);
                
                echo json_encode([
                    'success' => true,
                    'orders' => $orders
                ]);
            }
            break;
            
        case 'POST':
            if ($action == 'create') {
                // Создать новый заказ
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($data['user_id'])) {
                    throw new Exception('Не указан пользователь');
                }
                
                $order_id = $order->createOrder(
                    $data['user_id'],
                    $data['items'] ?? [],
                    $data['notes'] ?? null
                );
                
                echo json_encode([
                    'success' => true,
                    'order_id' => $order_id
                ]);
            } else if ($action == 'add_item') {
                // Добавить позицию в заказ
                $data = json_decode(file_get_contents('php://input'), true);
                $order_id = intval($_GET['id']);
                
                $order->addOrderItem($order_id, $data);
                $order->recalculateOrderTotal($order_id);
                
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'PUT':
            $order_id = intval($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Проверяем action из тела запроса
            if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'update_status':
                        // Изменить статус заказа
                        if (!isset($data['status'])) {
                            throw new Exception('Не указан статус');
                        }
                        
                        $order->updateOrderStatus(
                            $order_id,
                            $data['status'],
                            $_SESSION['admin_id'],
                            $data['comment'] ?? null
                        );
                        
                        // Логируем действие
                        $orderData = $order->getOrderById($order_id);
                        $adminLog->log($_SESSION['admin_id'], 'update_order_status', 
                            "Изменен статус заказа #{$orderData['order_number']} на {$data['status']}", 
                            'order', $order_id);
                        
                        echo json_encode(['success' => true]);
                        break;
                        
                    case 'update_payment':
                        // Изменить статус оплаты
                        if (!isset($data['payment_status'])) {
                            throw new Exception('Не указан статус оплаты');
                        }
                        
                        $order->updatePaymentStatus($order_id, $data['payment_status']);
                        
                        echo json_encode(['success' => true]);
                        break;
                        
                    default:
                        throw new Exception('Неизвестное действие');
                }
            } else {
                throw new Exception('Не указано действие');
            }
            break;
            
        case 'DELETE':
            
            
            $order_id = intval($_GET['id'] ?? 0);
            
            if (!$order_id) {
                throw new Exception('Не указан ID заказа');
            }
            
            // Получаем информацию о заказе перед удалением
            $orderData = $order->getOrderById($order_id);
            if (!$orderData) {
                throw new Exception('Заказ не найден');
            }
            
            // Удаляем заказ
            $result = $order->deleteOrder($order_id);
            
            if ($result) {
                // Логируем действие
                $adminLog->log($_SESSION['admin_id'], 'delete_order', 
                    "Удален заказ #{$orderData['order_number']}", 
                    'order', $order_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Заказ успешно удален'
                ]);
            } else {
                throw new Exception('Ошибка при удалении заказа');
            }
            break;
            
        default:
            throw new Exception('Метод не поддерживается');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}