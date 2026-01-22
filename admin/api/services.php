<?php
// admin/api/services.php - API для управления услугами

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Service.php';

// Функция логирования для API
function logApiRequest($message, $data = null) {
    $log_file = __DIR__ . '/../logs/api_debug.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}";

    if ($data !== null) {
        $log_entry .= "\nData: " . print_r($data, true);
    }

    $log_entry .= "\n" . str_repeat('-', 80) . "\n";

    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Проверка авторизации
checkAuth();

// Проверка прав доступа
if (!in_array($_SESSION['admin_role'], ['super_admin', 'manager']) && !hasPermission($_SESSION['admin_id'], 'manage_services')) {
    logApiRequest("⚠️ Access denied", [
        'admin_role' => $_SESSION['admin_role'] ?? 'N/A',
        'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
    ]);
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Недостаточно прав']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$service = new Service($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

logApiRequest("=== NEW API REQUEST ===", [
    'method' => $method,
    'action' => $action,
    'GET' => $_GET,
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'admin_id' => $_SESSION['admin_id'] ?? 'N/A',
    'admin_role' => $_SESSION['admin_role'] ?? 'N/A'
]);

header('Content-Type: application/json');

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Получить данные услуги
                $service_id = $_GET['id']; // ID может быть строкой или числом

                logApiRequest("GET service by ID", [
                    'service_id' => $service_id,
                    'service_id_type' => gettype($service_id)
                ]);

                $service_data = $service->getServiceById($service_id);

                if (!$service_data) {
                    logApiRequest("❌ Service not found", [
                        'service_id' => $service_id
                    ]);
                    throw new Exception('Услуга не найдена');
                }

                logApiRequest("✓ Service found, fetching stats", [
                    'service_id' => $service_id
                ]);

                // Получаем статистику услуги
                $service_data['stats'] = $service->getServiceStats($service_id);

                logApiRequest("✓ Returning service data", [
                    'service_id' => $service_id,
                    'service_name' => $service_data['name'] ?? 'N/A'
                ]);

                echo json_encode([
                    'success' => true,
                    'service' => $service_data
                ]);
            } else if ($action == 'categories') {
                // Получить список категорий
                $categories = $service->getCategories();
                
                echo json_encode([
                    'success' => true,
                    'categories' => $categories
                ]);
            } else if ($action == 'calculate') {
                // Расчет цены
                $service_id = intval($_GET['service_id']);
                $quantity = intval($_GET['quantity'] ?? 1);
                $parameters = $_GET['parameters'] ?? [];
                $options = [
                    'urgent' => $_GET['urgent'] ?? false
                ];
                
                $price_data = $service->calculatePrice($service_id, $quantity, $parameters, $options);
                
                echo json_encode([
                    'success' => true,
                    'price' => $price_data
                ]);
            } else if ($action == 'stats') {
                // Общая статистика услуг
                $stats = $service->getServiceStats();
                
                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
            } else {
                // Получить список услуг
                $filters = [
                    'search' => $_GET['search'] ?? null,
                    'category' => $_GET['category'] ?? null,
                    'is_active' => isset($_GET['is_active']) ? intval($_GET['is_active']) : null
                ];
                
                $limit = intval($_GET['limit'] ?? 50);
                $offset = intval($_GET['offset'] ?? 0);
                
                $services = $service->getServices($filters, $limit, $offset);
                
                echo json_encode([
                    'success' => true,
                    'services' => $services
                ]);
            }
            break;
            
        case 'POST':
            if ($action == 'create') {
                // Создать новую услугу
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Валидация
                if (empty($data['name'])) {
                    throw new Exception('Название услуги обязательно');
                }
                
                if (!isset($data['base_price']) || $data['base_price'] < 0) {
                    throw new Exception('Некорректная базовая цена');
                }
                
                if (!isset($data['min_quantity']) || $data['min_quantity'] < 1) {
                    $data['min_quantity'] = 1;
                }
                
                $service_id = $service->createService($data);
                
                if (!$service_id) {
                    throw new Exception('Ошибка создания услуги');
                }
                
                echo json_encode([
                    'success' => true,
                    'service_id' => $service_id
                ]);
            } else if ($action == 'copy') {
                // Копировать услугу
                $service_id = $_GET['id']; // ID может быть строкой
                
                $new_service_id = $service->copyService($service_id);
                
                echo json_encode([
                    'success' => true,
                    'service_id' => $new_service_id
                ]);
            } else if ($action == 'parameter') {
                // Добавить параметр к услуге
                $service_id = $_GET['id']; // ID может быть строкой
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (empty($data['parameter_type']) || empty($data['parameter_name'])) {
                    throw new Exception('Тип и название параметра обязательны');
                }
                
                if (!$service->addServiceParameter($service_id, $data)) {
                    throw new Exception('Ошибка добавления параметра');
                }
                
                echo json_encode(['success' => true]);
            } else if ($action == 'price_rule') {
                // Добавить правило ценообразования
                $service_id = $_GET['id']; // ID может быть строкой
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (empty($data['rule_type'])) {
                    throw new Exception('Тип правила обязателен');
                }
                
                if (!$service->addPriceRule($service_id, $data)) {
                    throw new Exception('Ошибка добавления правила');
                }
                
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'PUT':
            $service_id = $_GET['id'] ?? null; // ID может быть строкой

            if (!$service_id) {
                throw new Exception('ID услуги не указан');
            }
            
            if ($action == 'parameter') {
                // Обновить параметр
                $param_id = intval($_GET['param_id']);
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$service->updateServiceParameter($param_id, $data)) {
                    throw new Exception('Ошибка обновления параметра');
                }
                
                echo json_encode(['success' => true]);
            } else if ($action == 'toggle') {
                // Активировать/деактивировать услугу
                $current = $service->getServiceById($service_id);
                $new_status = $current['is_active'] ? 0 : 1;
                
                if (!$service->updateService($service_id, ['is_active' => $new_status])) {
                    throw new Exception('Ошибка изменения статуса');
                }
                
                echo json_encode(['success' => true]);
            } else if ($action == 'reorder') {
                // Изменить порядок сортировки
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($data['services']) || !is_array($data['services'])) {
                    throw new Exception('Неверные данные для сортировки');
                }
                
                foreach ($data['services'] as $index => $id) {
                    $update_query = "UPDATE services SET sort_order = :order WHERE id = :id";
                    $stmt = $db->prepare($update_query);
                    $stmt->bindParam(":order", $index);
                    $stmt->bindParam(":id", $id);
                    $stmt->execute();
                }
                
                echo json_encode(['success' => true]);
            } else {
                // Обновить услугу
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!empty($data['name']) && strlen($data['name']) < 3) {
                    throw new Exception('Название услуги слишком короткое');
                }
                
                if (isset($data['base_price']) && $data['base_price'] < 0) {
                    throw new Exception('Некорректная базовая цена');
                }
                
                if (!$service->updateService($service_id, $data)) {
                    throw new Exception('Ошибка обновления услуги');
                }
                
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'DELETE':
            if ($action == 'parameter') {
                // Удалить параметр
                $param_id = intval($_GET['param_id']);
                
                if (!$service->deleteServiceParameter($param_id)) {
                    throw new Exception('Ошибка удаления параметра');
                }
                
                echo json_encode(['success' => true]);
            } else {
                // Удалить услугу
                $service_id = $_GET['id'] ?? null; // ID может быть строкой

                if (!$service_id) {
                    throw new Exception('ID услуги не указан');
                }

                if (!$service->deleteService($service_id)) {
                    throw new Exception('Ошибка удаления услуги');
                }

                echo json_encode(['success' => true]);
            }
            break;
            
        default:
            throw new Exception('Метод не поддерживается');
    }
} catch (Exception $e) {
    logApiRequest("❌ EXCEPTION caught", [
        'error_message' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Функция проверки прав (если не определена в users.php)
if (!function_exists('hasPermission')) {
    function hasPermission($admin_id, $permission) {
        global $db;
        
        $query = "SELECT COUNT(*) as count 
                 FROM admin_permissions 
                 WHERE admin_id = :admin_id AND permission = :permission";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":admin_id", $admin_id);
        $stmt->bindParam(":permission", $permission);
        $stmt->execute();
        
        return $stmt->fetch()['count'] > 0;
    }
}
?>