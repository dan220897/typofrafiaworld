<?php
// admin/api/users.php - API для управления пользователями

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/User.php';

// Проверка авторизации
checkAuth();

// Проверка прав доступа
if (!$_SESSION['admin_role'] == 'super_admin' && !hasPermission($_SESSION['admin_id'], 'manage_users')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Недостаточно прав']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Получить данные пользователя
                $user_id = intval($_GET['id']);
                $user_data = $user->getUserById($user_id);
                
                if (!$user_data) {
                    throw new Exception('Пользователь не найден');
                }
                
                // Получаем последние заказы
                $user_data['recent_orders'] = $user->getUserOrders($user_id, 5);
                
                // Получаем историю SMS
$sms_query = "SELECT sh.*, a.full_name as admin_name 
              FROM sms_history sh
              LEFT JOIN admins a ON sh.sent_by = a.id
              WHERE sh.user_id = :user_id
              ORDER BY sh.created_at DESC
              LIMIT 10";

$sms_stmt = $db->prepare($sms_query);
$sms_stmt->bindParam(':user_id', $user_id);
$sms_stmt->execute();

$user_data['sms_history'] = $sms_stmt->fetchAll(PDO::FETCH_ASSOC);

                // Получаем активность
                $user_data['activity'] = $user->getUserActivity($user_id, 30);
                
                echo json_encode([
                    'success' => true,
                    'user' => $user_data
                ]);
            } else if ($action == 'stats') {
                // Получить статистику пользователей
                $period = $_GET['period'] ?? 'month';
                $stats = $user->getUsersStats($period);
                
                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
            } else if ($action == 'search') {
                // Поиск пользователей
                $search = $_GET['q'] ?? '';
                
                if (strlen($search) < 2) {
                    echo json_encode([
                        'success' => true,
                        'users' => []
                    ]);
                    exit;
                }
                
                $results = $user->searchUsers($search);
                
                echo json_encode([
                    'success' => true,
                    'users' => $results
                ]);
            } else if ($action == 'export') {
                // Экспорт пользователей
                $filters = [
                    'search' => $_GET['search'] ?? null,
                    'verified' => $_GET['verified'] ?? null,
                    'blocked' => $_GET['blocked'] ?? null,
                    'date_from' => $_GET['date_from'] ?? null,
                    'date_to' => $_GET['date_to'] ?? null
                ];
                
                $format = $_GET['format'] ?? 'csv';
                $export_data = $user->exportUsers($filters, $format);
                
                if ($format == 'csv') {
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
                    echo "\xEF\xBB\xBF"; // UTF-8 BOM
                    echo $export_data;
                    exit;
                }
            } else {
                // Получить список пользователей
                $filters = [
                    'search' => $_GET['search'] ?? null,
                    'verified' => $_GET['verified'] ?? null,
                    'blocked' => $_GET['blocked'] ?? null,
                    'date_from' => $_GET['date_from'] ?? null,
                    'date_to' => $_GET['date_to'] ?? null
                ];
                
                $limit = intval($_GET['limit'] ?? 50);
                $offset = intval($_GET['offset'] ?? 0);
                
                $users = $user->getUsers($filters, $limit, $offset);
                
                // Считаем общее количество
                $count_query = "SELECT COUNT(*) as total FROM users WHERE 1=1";
                if (!empty($filters['search'])) {
                    $count_query .= " AND (name LIKE :search OR phone LIKE :search OR email LIKE :search)";
                }
                
                $count_stmt = $db->prepare($count_query);
                if (!empty($filters['search'])) {
                    $search = "%{$filters['search']}%";
                    $count_stmt->bindParam(":search", $search);
                }
                $count_stmt->execute();
                $total = $count_stmt->fetch()['total'];
                
                echo json_encode([
                    'success' => true,
                    'users' => $users,
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset
                ]);
            }
            break;
            
        case 'POST':
            // Создать нового пользователя
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Валидация
            if (empty($data['phone'])) {
                throw new Exception('Телефон обязателен');
            }
            
            if (!preg_match('/^\+7\d{10}$/', $data['phone'])) {
                throw new Exception('Неверный формат телефона');
            }
            
            // Проверка на существование
            if ($user->userExists($data['phone'], $data['email'] ?? null)) {
                throw new Exception('Пользователь с таким телефоном или email уже существует');
            }
            
            $user_id = $user->createUser($data);
            
            if (!$user_id) {
                throw new Exception('Ошибка создания пользователя');
            }
            
            // Логирование
            logAdminAction($_SESSION['admin_id'], 'create_user', "Создан пользователь ID: {$user_id}");
            
            echo json_encode([
                'success' => true,
                'user_id' => $user_id
            ]);
            break;
            
        case 'PUT':
            $user_id = intval($_GET['id'] ?? 0);
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$user_id) {
                throw new Exception('ID пользователя не указан');
            }
            
            if ($action == 'block') {
                // Блокировка/разблокировка
                $block = $data['block'] ?? true;
                
                if (!$user->toggleBlockUser($user_id, $block)) {
                    throw new Exception('Ошибка изменения статуса блокировки');
                }
                
                $action_text = $block ? 'заблокирован' : 'разблокирован';
                logAdminAction($_SESSION['admin_id'], 'block_user', "Пользователь ID: {$user_id} {$action_text}");
                
                echo json_encode(['success' => true]);
            } else {
                // Обновление данных
                if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Неверный формат email');
                }
                
                if (!$user->updateUser($user_id, $data)) {
                    throw new Exception('Ошибка обновления пользователя');
                }
                
                logAdminAction($_SESSION['admin_id'], 'update_user', "Обновлен пользователь ID: {$user_id}");
                
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'DELETE':
            // Удаление пользователя (только для super_admin)
            if ($_SESSION['admin_role'] !== 'super_admin') {
                throw new Exception('Недостаточно прав для удаления');
            }
            
            $user_id = intval($_GET['id'] ?? 0);
            
            if (!$user_id) {
                throw new Exception('ID пользователя не указан');
            }
            
            // Проверяем, есть ли заказы
            $check_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(":user_id", $user_id);
            $check_stmt->execute();
            $orders_count = $check_stmt->fetch()['count'];
            
            if ($orders_count > 0) {
                throw new Exception('Нельзя удалить пользователя с заказами. Используйте блокировку.');
            }
            
            // Удаляем связанные данные
            $db->beginTransaction();
            
            try {
                // Удаляем чаты и сообщения
                $delete_messages = "DELETE m FROM messages m 
                                   JOIN chats c ON m.chat_id = c.id 
                                   WHERE c.user_id = :user_id";
                $stmt = $db->prepare($delete_messages);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                
                $delete_chats = "DELETE FROM chats WHERE user_id = :user_id";
                $stmt = $db->prepare($delete_chats);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                
                // Удаляем уведомления
                $delete_notifications = "DELETE FROM notifications WHERE user_id = :user_id";
                $stmt = $db->prepare($delete_notifications);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                
                // Удаляем пользователя
                $delete_user = "DELETE FROM users WHERE id = :user_id";
                $stmt = $db->prepare($delete_user);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                
                $db->commit();
                
                logAdminAction($_SESSION['admin_id'], 'delete_user', "Удален пользователь ID: {$user_id}");
                
                echo json_encode(['success' => true]);
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Метод не поддерживается');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Функция логирования действий администратора
function logAdminAction($admin_id, $action, $description) {
    global $db;
    
    $query = "INSERT INTO admin_logs (admin_id, action, description, ip_address, user_agent, created_at) 
             VALUES (:admin_id, :action, :description, :ip, :user_agent, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":admin_id", $admin_id);
    $stmt->bindParam(":action", $action);
    $stmt->bindParam(":description", $description);
    $stmt->bindValue(":ip", $_SERVER['REMOTE_ADDR']);
    $stmt->bindValue(":user_agent", $_SERVER['HTTP_USER_AGENT']);
    
    $stmt->execute();
}

// Функция проверки прав
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
?>