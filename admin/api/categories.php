<?php
// admin/api/categories.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/Category.php';

// Проверка авторизации
checkAdminAuth();

// Только для суперадмина
if (!isSuperAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$category = new Category($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Получить категорию по ID
                $result = $category->getById($id);
                if ($result) {
                    echo json_encode(['success' => true, 'category' => $result]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Категория не найдена']);
                }
            } elseif ($action === 'stats') {
                // Получить статистику
                $stats = $category->getStats();
                echo json_encode(['success' => true, 'stats' => $stats]);
            } elseif ($action === 'migrate') {
                // Миграция категорий из services
                $result = $category->migrateFromServices();
                echo json_encode($result);
            } else {
                // Получить все категории
                $filters = [
                    'search' => $_GET['search'] ?? null,
                    'is_active' => isset($_GET['is_active']) ? intval($_GET['is_active']) : null
                ];
                $categories = $category->getAll($filters);
                echo json_encode(['success' => true, 'categories' => $categories]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || empty($data['name'])) {
                echo json_encode(['success' => false, 'error' => 'Название категории обязательно']);
                exit;
            }

            $categoryData = [
                'name' => $data['name'],
                'slug' => $data['slug'] ?? '',
                'description' => $data['description'] ?? '',
                'icon' => $data['icon'] ?? 'fa-folder',
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
            ];

            $newId = $category->create($categoryData);

            if ($newId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Категория создана',
                    'id' => $newId
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Ошибка при создании категории']);
            }
            break;

        case 'PUT':
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID категории не указан']);
                exit;
            }

            if ($action === 'toggle') {
                // Переключить статус
                if ($category->toggleStatus($id)) {
                    echo json_encode(['success' => true, 'message' => 'Статус изменен']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Ошибка при изменении статуса']);
                }
            } elseif ($action === 'reorder') {
                // Изменить порядок
                $data = json_decode(file_get_contents('php://input'), true);
                if ($category->reorder($data['categories'])) {
                    echo json_encode(['success' => true, 'message' => 'Порядок изменен']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Ошибка при изменении порядка']);
                }
            } else {
                // Обновить категорию
                $data = json_decode(file_get_contents('php://input'), true);

                if (!$data || empty($data['name'])) {
                    echo json_encode(['success' => false, 'error' => 'Название категории обязательно']);
                    exit;
                }

                $categoryData = [
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?? '',
                    'description' => $data['description'] ?? '',
                    'icon' => $data['icon'] ?? 'fa-folder',
                    'sort_order' => $data['sort_order'] ?? 0,
                    'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
                ];

                if ($category->update($id, $categoryData)) {
                    echo json_encode(['success' => true, 'message' => 'Категория обновлена']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Ошибка при обновлении категории']);
                }
            }
            break;

        case 'DELETE':
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID категории не указан']);
                exit;
            }

            $result = $category->delete($id);
            echo json_encode($result);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
?>
