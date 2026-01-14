<?php
// admin/api/locations.php - API для управления точками
session_start();
header('Content-Type: application/json');

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/Location.php';

// Проверяем авторизацию
checkAdminAuth();

if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$location = new Location($db);

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            // Получить список всех локаций
            $locations = $location->getAll();
            echo json_encode([
                'success' => true,
                'locations' => $locations
            ]);
            break;

        case 'get':
            // Получить одну локацию по ID
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID не указан');
            }

            $locationData = $location->getById($id);
            if (!$locationData) {
                throw new Exception('Локация не найдена');
            }

            echo json_encode([
                'success' => true,
                'location' => $locationData
            ]);
            break;

        case 'create':
            // Создать новую локацию
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($name) || empty($code) || empty($password)) {
                throw new Exception('Заполните все обязательные поля');
            }

            if (strlen($password) < 6) {
                throw new Exception('Пароль должен содержать минимум 6 символов');
            }

            // Проверяем уникальность кода
            $existingLocation = $db->prepare("SELECT id FROM locations WHERE code = ?");
            $existingLocation->execute([$code]);
            if ($existingLocation->rowCount() > 0) {
                throw new Exception('Точка с таким кодом уже существует');
            }

            $location->name = $name;
            $location->code = $code;
            $location->address = $address;
            $location->phone = $phone;
            $location->email = $email;
            $location->password_hash = password_hash($password, PASSWORD_DEFAULT);
            $location->is_active = 1;

            if ($location->create()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Точка успешно создана',
                    'location_id' => $location->id
                ]);
            } else {
                throw new Exception('Ошибка при создании точки');
            }
            break;

        case 'update':
            // Обновить локацию
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID не указан');
            }

            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($name) || empty($code)) {
                throw new Exception('Заполните все обязательные поля');
            }

            // Проверяем уникальность кода (кроме текущей локации)
            $existingLocation = $db->prepare("SELECT id FROM locations WHERE code = ? AND id != ?");
            $existingLocation->execute([$code, $id]);
            if ($existingLocation->rowCount() > 0) {
                throw new Exception('Точка с таким кодом уже существует');
            }

            $location->id = $id;
            $location->name = $name;
            $location->code = $code;
            $location->address = $address;
            $location->phone = $phone;
            $location->email = $email;
            $location->is_active = 1;

            // Обновляем пароль только если он указан
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    throw new Exception('Пароль должен содержать минимум 6 символов');
                }
                $location->password_hash = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($location->update()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Точка успешно обновлена'
                ]);
            } else {
                throw new Exception('Ошибка при обновлении точки');
            }
            break;

        case 'toggle':
            // Изменить статус локации
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                throw new Exception('ID не указан');
            }

            if ($location->toggleStatus($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Статус точки изменен'
                ]);
            } else {
                throw new Exception('Ошибка при изменении статуса');
            }
            break;

        case 'delete':
            // Удалить локацию
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                throw new Exception('ID не указан');
            }

            // Проверяем, нет ли связанных заказов
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE location_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception('Невозможно удалить точку, у которой есть заказы');
            }

            if ($location->delete($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Точка успешно удалена'
                ]);
            } else {
                throw new Exception('Ошибка при удалении точки');
            }
            break;

        case 'stats':
            // Получить статистику по локации
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID не указан');
            }

            $stats = $location->getStatistics($id);
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;

        default:
            throw new Exception('Неизвестное действие');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
