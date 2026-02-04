<?php
// admin/api/tasks.php — API задач и смен
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/TelegramNotifier.php';

checkAdminAuth();

$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // ========== ЗАДАЧИ ==========

        case 'get_tasks':
            $locationId = intval($_GET['location_id'] ?? 0);
            $month = intval($_GET['month'] ?? date('n'));
            $year = intval($_GET['year'] ?? date('Y'));

            if (isLocationAdmin()) {
                $locationId = getCurrentLocationId();
            }
            if (!$locationId) {
                throw new Exception('Не указана точка');
            }

            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            $stmt = $db->prepare("
                SELECT t.*, tc.completed_at, tc.completed_by
                FROM tasks t
                LEFT JOIN task_completions tc ON t.id = tc.task_id
                WHERE t.location_id = :loc AND t.task_date BETWEEN :start AND :end
                ORDER BY t.task_date, t.sort_order, t.id
            ");
            $stmt->execute([':loc' => $locationId, ':start' => $startDate, ':end' => $endDate]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Группируем по дате
            $grouped = [];
            foreach ($tasks as $t) {
                $grouped[$t['task_date']][] = $t;
            }

            echo json_encode(['success' => true, 'tasks' => $grouped]);
            break;

        case 'add_task':
            if (!isSuperAdmin()) {
                throw new Exception('Только суперадминистратор может добавлять задачи');
            }
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $data = json_decode(file_get_contents('php://input'), true);
            $locationId = intval($data['location_id'] ?? 0);
            $taskDate = $data['task_date'] ?? '';
            $title = trim($data['title'] ?? '');

            if (!$locationId || !$taskDate || !$title) {
                throw new Exception('Заполните все поля');
            }

            $stmt = $db->prepare("INSERT INTO tasks (location_id, task_date, title, created_by) VALUES (:loc, :d, :t, :by)");
            $stmt->execute([
                ':loc' => $locationId,
                ':d' => $taskDate,
                ':t' => $title,
                ':by' => $_SESSION['admin_id']
            ]);

            echo json_encode(['success' => true, 'task_id' => $db->lastInsertId()]);
            break;

        case 'delete_task':
            if (!isSuperAdmin()) {
                throw new Exception('Только суперадминистратор может удалять задачи');
            }
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $data = json_decode(file_get_contents('php://input'), true);
            $taskId = intval($data['task_id'] ?? 0);
            if (!$taskId) throw new Exception('Не указан ID задачи');

            $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->execute([':id' => $taskId]);

            echo json_encode(['success' => true]);
            break;

        case 'toggle_task':
            if ($method !== 'POST') throw new Exception('Method not allowed');

            $data = json_decode(file_get_contents('php://input'), true);
            $taskId = intval($data['task_id'] ?? 0);
            $completed = !empty($data['completed']);

            if (!$taskId) throw new Exception('Не указан ID задачи');

            // Проверяем, что задача принадлежит точке этого админа
            if (isLocationAdmin()) {
                $stmt = $db->prepare("SELECT location_id FROM tasks WHERE id = :id");
                $stmt->execute([':id' => $taskId]);
                $task = $stmt->fetch();
                if (!$task || $task['location_id'] != getCurrentLocationId()) {
                    throw new Exception('Задача не найдена');
                }
            }

            if ($completed) {
                $stmt = $db->prepare("INSERT IGNORE INTO task_completions (task_id, completed_by) VALUES (:tid, :by)");
                $stmt->execute([':tid' => $taskId, ':by' => $_SESSION['admin_id']]);
            } else {
                $stmt = $db->prepare("DELETE FROM task_completions WHERE task_id = :tid");
                $stmt->execute([':tid' => $taskId]);
            }

            echo json_encode(['success' => true]);
            break;

        // ========== СМЕНЫ ==========

        case 'get_shift':
            $locationId = isLocationAdmin() ? getCurrentLocationId() : intval($_GET['location_id'] ?? 0);
            $shiftDate = $_GET['date'] ?? date('Y-m-d');

            if (!$locationId) throw new Exception('Не указана точка');

            $stmt = $db->prepare("SELECT * FROM shifts WHERE location_id = :loc AND shift_date = :d");
            $stmt->execute([':loc' => $locationId, ':d' => $shiftDate]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'shift' => $shift ?: null]);
            break;

        case 'open_shift':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            if (!isLocationAdmin()) throw new Exception('Только администратор точки может открыть смену');

            $locationId = getCurrentLocationId();
            $today = date('Y-m-d');

            // Проверяем, нет ли уже открытой смены
            $stmt = $db->prepare("SELECT id FROM shifts WHERE location_id = :loc AND shift_date = :d");
            $stmt->execute([':loc' => $locationId, ':d' => $today]);
            if ($stmt->fetch()) {
                throw new Exception('Смена на сегодня уже открыта');
            }

            $stmt = $db->prepare("INSERT INTO shifts (location_id, shift_date, opened_at, opened_by) VALUES (:loc, :d, NOW(), :by)");
            $stmt->execute([
                ':loc' => $locationId,
                ':d' => $today,
                ':by' => $_SESSION['admin_id']
            ]);

            // Telegram уведомление
            $locationName = $_SESSION['location_name'] ?? 'Точка #' . $locationId;
            $telegram = new TelegramNotifier();
            $telegram->sendMessage(
                "🟢 <b>Смена открыта</b>\n\n" .
                "📍 Точка: <b>" . htmlspecialchars($locationName) . "</b>\n" .
                "⏰ Время: " . date('d.m.Y H:i') . "\n" .
                "👤 Сотрудник: <b>" . htmlspecialchars($locationName) . "</b>"
            );

            echo json_encode(['success' => true, 'message' => 'Смена открыта']);
            break;

        case 'close_shift':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            if (!isLocationAdmin()) throw new Exception('Только администратор точки может закрыть смену');

            $locationId = getCurrentLocationId();
            $today = date('Y-m-d');

            $stmt = $db->prepare("SELECT id, closed_at FROM shifts WHERE location_id = :loc AND shift_date = :d");
            $stmt->execute([':loc' => $locationId, ':d' => $today]);
            $shift = $stmt->fetch();

            if (!$shift) throw new Exception('Смена ещё не открыта');
            if ($shift['closed_at']) throw new Exception('Смена уже закрыта');

            // Считаем статистику задач за день
            $stmt = $db->prepare("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN tc.id IS NOT NULL THEN 1 ELSE 0 END) as done
                FROM tasks t
                LEFT JOIN task_completions tc ON t.id = tc.task_id
                WHERE t.location_id = :loc AND t.task_date = :d
            ");
            $stmt->execute([':loc' => $locationId, ':d' => $today]);
            $stats = $stmt->fetch();

            $stmt = $db->prepare("UPDATE shifts SET closed_at = NOW(), closed_by = :by WHERE id = :id");
            $stmt->execute([':by' => $_SESSION['admin_id'], ':id' => $shift['id']]);

            // Telegram уведомление
            $locationName = $_SESSION['location_name'] ?? 'Точка #' . $locationId;
            $taskInfo = '';
            if ($stats['total'] > 0) {
                $taskInfo = "\n📋 Задачи: {$stats['done']}/{$stats['total']} выполнено";
            }

            $telegram = new TelegramNotifier();
            $telegram->sendMessage(
                "🔴 <b>Смена закрыта</b>\n\n" .
                "📍 Точка: <b>" . htmlspecialchars($locationName) . "</b>\n" .
                "⏰ Время: " . date('d.m.Y H:i') .
                $taskInfo
            );

            echo json_encode(['success' => true, 'message' => 'Смена закрыта']);
            break;

        case 'get_shifts_month':
            $locationId = isLocationAdmin() ? getCurrentLocationId() : intval($_GET['location_id'] ?? 0);
            $month = intval($_GET['month'] ?? date('n'));
            $year = intval($_GET['year'] ?? date('Y'));

            if (!$locationId) throw new Exception('Не указана точка');

            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            $stmt = $db->prepare("SELECT * FROM shifts WHERE location_id = :loc AND shift_date BETWEEN :s AND :e ORDER BY shift_date");
            $stmt->execute([':loc' => $locationId, ':s' => $startDate, ':e' => $endDate]);
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($shifts as $s) {
                $result[$s['shift_date']] = $s;
            }

            echo json_encode(['success' => true, 'shifts' => $result]);
            break;

        default:
            throw new Exception('Неизвестное действие: ' . $action);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
