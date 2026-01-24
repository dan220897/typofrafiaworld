<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/classes/UserService.php';
require_once dirname(__DIR__) . '/classes/SMSService.php';
require_once dirname(__DIR__) . '/classes/ChatService.php';
require_once dirname(__DIR__) . '/classes/TelegramNotifier.php';
require_once dirname(__DIR__) . '/classes/EmailService.php';
require_once dirname(__DIR__) . '/admin/classes/TinkoffPayment.php';

// Определяем действие из URL параметров или пути
$action = '';

// Сначала проверяем URL параметр action
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    // Если нет параметра, пытаемся извлечь из пути
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $requestPath = str_replace(dirname($scriptName), '', $requestUri);
    $requestPath = trim($requestPath, '/');
    $requestPath = explode('?', $requestPath)[0];
    
    $pathParts = explode('/', $requestPath);
    $action = end($pathParts);
    $action = str_replace('.php', '', $action);
}

// Если action пустой или равен 'orders', то это запрос списка заказов для GET
if (empty($action) || $action === 'orders') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = 'list'; // Переименуем для ясности
    }
}

$userService = new UserService();
$chatService = new ChatService();
$telegramNotifier = new TelegramNotifier();

// Публичные действия, не требующие авторизации
$publicActions = ['services', 'calculator'];

// Проверяем авторизацию только для приватных действий
if (!in_array($action, $publicActions) && !$userService->isAuthenticated()) {
    sendJsonResponse(['success' => false, 'error' => 'Необходима авторизация'], 401);
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetRequest($action, $userService, $chatService);
            break;
        case 'POST':
            handlePostRequest($action, $userService, $chatService, $telegramNotifier);
            break;
        case 'PUT':
            handlePutRequest($action, $userService, $chatService);
            break;
        default:
            sendJsonResponse(['success' => false, 'error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    logMessage("Ошибка в API orders: " . $e->getMessage(), 'ERROR');
    sendJsonResponse(['success' => false, 'error' => 'Внутренняя ошибка сервера'], 500);
}

/**
 * Обработка GET запросов
 */
function handleGetRequest($action, $userService, $chatService) {
    switch ($action) {
        case 'list':
        case 'orders':
        case '': // Для пустого action
            handleGetOrders($userService);
            break;
            
        case 'order':
            handleGetOrder($userService);
            break;
            
        case 'services':
            handleGetServices();
            break;
            
        case 'calculator':
            handleCalculatePrice();
            break;
            
        default:
            // Проверяем, не является ли action числом (ID заказа)
            if (is_numeric($action)) {
                handleGetOrderById($action, $userService);
            } else {
                sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 404);
            }
    }
}

/**
 * Обработка POST запросов
 */
function handlePostRequest($action, $userService, $chatService, $telegramNotifier) {
    switch ($action) {
        case 'create':
            handleCreateOrder($userService, $chatService, $telegramNotifier);
            break;
            
        case 'upload':
            handleOrderFileUpload($userService);
            break;
            
        case 'estimate':
            handleCreateEstimate($userService, $chatService);
            break;
            
        default:
            sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 404);
    }
}

/**
 * Обработка PUT запросов
 */
function handlePutRequest($action, $userService, $chatService) {
    switch ($action) {
        case 'status':
            handleUpdateOrderStatus($userService);
            break;
            
        default:
            // Проверяем, не является ли action числом (ID заказа для обновления)
            if (is_numeric($action)) {
                handleUpdateOrder($action, $userService);
            } else {
                sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 404);
            }
    }
}

/**
 * Получение списка заказов пользователя
 */
function handleGetOrders($userService) {
    $userId = $_SESSION['user_id'];

    // Отладочная информация
    logMessage("Запрос списка заказов - User ID: {$userId}, Session: " . json_encode([
        'user_id' => $_SESSION['user_id'] ?? 'not set',
        'user_email' => $_SESSION['user_email'] ?? 'not set',
        'user_phone' => $_SESSION['user_phone'] ?? 'not set'
    ]), 'DEBUG');

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $offset = ($page - 1) * $limit;

    try {
        $db = Database::getInstance()->getConnection();

        // Получаем информацию о текущем пользователе
        $currentUser = $userService->getCurrentUser();
        $userEmail = $currentUser['email'] ?? null;
        $userPhone = $currentUser['phone'] ?? null;

        // Находим все связанные user_id (по email или phone)
        $relatedUserIds = [$userId];

        if ($userEmail) {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$userEmail, $userId]);
            while ($row = $stmt->fetch()) {
                $relatedUserIds[] = $row['id'];
            }
        }

        if ($userPhone) {
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
            $stmt->execute([$userPhone, $userId]);
            while ($row = $stmt->fetch()) {
                $relatedUserIds[] = $row['id'];
            }
        }

        $relatedUserIds = array_unique($relatedUserIds);
        logMessage("Поиск заказов для связанных user_id: " . implode(', ', $relatedUserIds), 'DEBUG');

        // Строим WHERE условие с учетом всех связанных user_id
        $placeholders = implode(',', array_fill(0, count($relatedUserIds), '?'));
        $whereConditions = ["o.user_id IN ({$placeholders})"];
        $params = $relatedUserIds;

        if ($status && array_key_exists($status, ORDER_STATUSES)) {
            $whereConditions[] = 'o.status = ?';
            $params[] = $status;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Получаем общее количество заказов
        $countStmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM orders o 
            {$whereClause}
        ");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch()['total'];
        
        // Получаем заказы с дополнительной информацией
        $stmt = $db->prepare("
            SELECT
                o.id,
                o.order_number,
                o.status,
                o.total_amount,
                o.final_amount,
                o.payment_status,
                o.delivery_method,
                o.created_at,
                o.updated_at,
                o.deadline_at,
                COUNT(oi.id) as items_count,
                GROUP_CONCAT(s.name SEPARATOR ', ') as services_preview
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN services s ON oi.service_id = s.id
            {$whereClause}
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");

        // Создаем копию params для добавления limit и offset
        $queryParams = $params;
        $queryParams[] = $limit;
        $queryParams[] = $offset;
        $stmt->execute($queryParams);
        $orders = $stmt->fetchAll();

        // Отладка: выводим количество найденных заказов
        logMessage("Найдено заказов для user_id {$userId}: " . count($orders) . " из {$totalCount} всего", 'DEBUG');

        // Форматируем заказы
        $formattedOrders = [];
        foreach ($orders as $order) {
            $formattedOrders[] = [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'status' => $order['status'],
                'status_text' => ORDER_STATUSES[$order['status']] ?? $order['status'],
                'total_amount' => (float)$order['total_amount'],
                'final_amount' => (float)$order['final_amount'],
                'payment_status' => $order['payment_status'],
                'delivery_method' => $order['delivery_method'],
                'items_count' => (int)$order['items_count'],
                'services_preview' => $order['services_preview'],
                'created_at' => $order['created_at'],
                'updated_at' => $order['updated_at'],
                'deadline_at' => $order['deadline_at']
            ];
        }
        
        logMessage("Получен список заказов для пользователя ID: {$userId}, страница: {$page}", 'INFO');
        
        sendJsonResponse([
            'success' => true,
            'orders' => $formattedOrders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        logMessage("Ошибка получения заказов для пользователя {$userId}: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка получения заказов']);
    }
}

/**
 * Получение детальной информации о заказе
 */
function handleGetOrderById($orderId, $userService) {
    $userId = $_SESSION['user_id'];

    try {
        $db = Database::getInstance()->getConnection();

        // Получаем информацию о текущем пользователе
        $currentUser = $userService->getCurrentUser();
        $userEmail = $currentUser['email'] ?? null;
        $userPhone = $currentUser['phone'] ?? null;

        // Находим все связанные user_id (по email или phone)
        $relatedUserIds = [$userId];

        if ($userEmail) {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$userEmail, $userId]);
            while ($row = $stmt->fetch()) {
                $relatedUserIds[] = $row['id'];
            }
        }

        if ($userPhone) {
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
            $stmt->execute([$userPhone, $userId]);
            while ($row = $stmt->fetch()) {
                $relatedUserIds[] = $row['id'];
            }
        }

        $relatedUserIds = array_unique($relatedUserIds);
        $placeholders = implode(',', array_fill(0, count($relatedUserIds), '?'));

        // Получаем заказ с проверкой владельца (по всем связанным user_id)
        $stmt = $db->prepare("
            SELECT o.*, u.phone, u.name as user_name, u.email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id IN ({$placeholders})
        ");
        $params = array_merge([$orderId], $relatedUserIds);
        $stmt->execute($params);
        $order = $stmt->fetch();
        
        if (!$order) {
            sendJsonResponse(['success' => false, 'error' => 'Заказ не найден'], 404);
        }
        
        // Получаем позиции заказа
        $stmt = $db->prepare("
            SELECT oi.*,
                   COALESCE(s.label, s.name) as service_name,
                   s.description as service_description
            FROM order_items oi
            LEFT JOIN services s ON oi.service_id = s.id
            WHERE oi.order_id = ?
            ORDER BY oi.created_at
        ");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        
        // Получаем файлы заказа
        $stmt = $db->prepare("
            SELECT * FROM order_files 
            WHERE order_id = ? 
            ORDER BY uploaded_at DESC
        ");
        $stmt->execute([$orderId]);
        $files = $stmt->fetchAll();
        
        // Получаем историю статусов
        $stmt = $db->prepare("
            SELECT osh.*, a.full_name as admin_name
            FROM order_status_history osh
            LEFT JOIN admins a ON osh.changed_by_id = a.id AND osh.changed_by_type = 'admin'
            WHERE osh.order_id = ?
            ORDER BY osh.created_at DESC
        ");
        $stmt->execute([$orderId]);
        $statusHistory = $stmt->fetchAll();
        
        // Форматируем данные
        $orderData = [
            'id' => $order['id'],
            'order_number' => $order['order_number'],
            'status' => $order['status'],
            'status_text' => ORDER_STATUSES[$order['status']] ?? $order['status'],
            'total_amount' => (float)$order['total_amount'],
            'discount_amount' => (float)$order['discount_amount'],
            'final_amount' => (float)$order['final_amount'],
            'payment_status' => $order['payment_status'],
            'tinkoff_payment_id' => $order['tinkoff_payment_id'],
            'tinkoff_payment_url' => $order['tinkoff_payment_url'],
            'delivery_method' => $order['delivery_method'],
            'delivery_address' => $order['delivery_address'],
            'notes' => $order['notes'],
            'admin_notes' => $order['admin_notes'],
            'deadline_at' => $order['deadline_at'],
            'created_at' => $order['created_at'],
            'updated_at' => $order['updated_at'],
            'confirmed_at' => $order['confirmed_at'],
            'completed_at' => $order['completed_at'],
            'items' => [],
            'files' => [],
            'status_history' => []
        ];
        
        // Форматируем позиции
        foreach ($items as $item) {
            $orderData['items'][] = [
                'id' => $item['id'],
                'service_id' => $item['service_id'],
                'service_name' => $item['service_name'],
                'service_description' => $item['service_description'],
                'quantity' => (int)$item['quantity'],
                'parameters' => $item['parameters'] ? json_decode($item['parameters'], true) : null,
                'unit_price' => (float)$item['unit_price'],
                'price' => (float)$item['total_price'], // Для совместимости с order-details.php
                'total_price' => (float)$item['total_price'],
                'design_status' => $item['design_status'],
                'notes' => $item['notes']
            ];
        }
        
        // Форматируем файлы
        foreach ($files as $file) {
            $orderData['files'][] = [
                'id' => $file['id'],
                'file_name' => $file['file_name'],
                'file_path' => $file['file_path'],
                'file_size' => (int)$file['file_size'],
                'file_type' => $file['file_type'],
                'uploaded_by_type' => $file['uploaded_by_type'],
                'uploaded_at' => $file['uploaded_at']
            ];
        }
        
        // Форматируем историю статусов
        foreach ($statusHistory as $history) {
            $orderData['status_history'][] = [
                'old_status' => $history['old_status'],
                'old_status_text' => ORDER_STATUSES[$history['old_status']] ?? $history['old_status'],
                'new_status' => $history['new_status'],
                'new_status_text' => ORDER_STATUSES[$history['new_status']] ?? $history['new_status'],
                'changed_by_type' => $history['changed_by_type'],
                'admin_name' => $history['admin_name'],
                'comment' => $history['comment'],
                'created_at' => $history['created_at']
            ];
        }
        
        logMessage("Получена детальная информация о заказе ID: {$orderId} для пользователя ID: {$userId}", 'INFO');

        // Извлекаем items из orderData для совместимости с order-details.php
        $items = $orderData['items'];
        unset($orderData['items']);

        sendJsonResponse([
            'success' => true,
            'order' => $orderData,
            'items' => $items
        ]);
        
    } catch (Exception $e) {
        logMessage("Ошибка получения заказа {$orderId} для пользователя {$userId}: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка получения заказа']);
    }
}

/**
 * Получение списка услуг
 */
function handleGetServices() {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT s.*, 
                   COUNT(sp.id) as parameters_count
            FROM services s
            LEFT JOIN service_parameters sp ON s.id = sp.service_id AND sp.is_active = 1
            WHERE s.is_active = 1
            GROUP BY s.id
            ORDER BY s.sort_order, s.name
        ");
        $stmt->execute();
        $services = $stmt->fetchAll();
        
        // Получаем параметры для каждой услуги
        $formattedServices = [];
        foreach ($services as $service) {
            $stmt = $db->prepare("
                SELECT * FROM service_parameters 
                WHERE service_id = ? AND is_active = 1
                ORDER BY parameter_type, parameter_name
            ");
            $stmt->execute([$service['id']]);
            $parameters = $stmt->fetchAll();
            
            $formattedServices[] = [
                'id' => $service['id'],
                'name' => $service['name'],
                'description' => $service['description'],
                'category' => $service['category'],
                'base_price' => (float)$service['base_price'],
                'min_quantity' => (int)$service['min_quantity'],
                'production_time_days' => (int)$service['production_time_days'],
                'parameters' => $parameters
            ];
        }
        
        sendJsonResponse([
            'success' => true,
            'services' => $formattedServices
        ]);
        
    } catch (Exception $e) {
        logMessage("Ошибка получения услуг: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка получения услуг']);
    }
}

/**
 * Создание нового заказа
 */
function handleCreateOrder($userService, $chatService, $telegramNotifier) {
    // Применяем rate limiting
    checkRateLimit('create_order', 5, 300); // 5 заказов в 5 минут
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'error' => 'Некорректные данные'], 400);
    }
    
    $userId = $_SESSION['user_id'];
    
    // Валидация данных
    $errors = validateOrderInput($input);
    if (!empty($errors)) {
        sendJsonResponse(['success' => false, 'error' => implode(', ', $errors)], 400);
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // Генерируем номер заказа
        $orderNumber = generateOrderNumber();
        
        // Рассчитываем стоимость
        $pricing = calculateOrderPricing($input['items']);

        // Получаем pickup_point_id если указан
        $pickupPointId = isset($input['pickup_point_id']) ? (int)$input['pickup_point_id'] : null;
        $locationId = null;

        // ЛОГИРОВАНИЕ: Проверяем входные данные
        logMessage("Создание заказа - входные данные: pickup_point_id=" . ($pickupPointId ?? 'NULL') . ", delivery_method=" . ($input['delivery_method'] ?? 'NULL'), 'INFO');

        // Если указана точка самовывоза, получаем её location_id
        if ($pickupPointId) {
            logMessage("Ищем location_id для pickup_point_id={$pickupPointId}", 'INFO');

            $stmt = $db->prepare("SELECT id, name, location_id FROM pickup_points WHERE id = ?");
            $stmt->execute([$pickupPointId]);
            $pickupPoint = $stmt->fetch();

            logMessage("Результат запроса pickup_points: " . json_encode($pickupPoint), 'INFO');

            if ($pickupPoint && isset($pickupPoint['location_id'])) {
                $locationId = $pickupPoint['location_id'];
                logMessage("Найден location_id={$locationId} для точки '{$pickupPoint['name']}'", 'INFO');
            } else {
                logMessage("WARNING: pickup_point найдена, но location_id=" . ($pickupPoint['location_id'] ?? 'NULL'), 'WARNING');
            }
        } else {
            logMessage("pickup_point_id не указан в запросе", 'INFO');
        }

        logMessage("Создание заказа с параметрами: pickup_point_id={$pickupPointId}, location_id={$locationId}", 'INFO');

        // Проверяем наличие столбцов в таблице orders
        $checkStmt = $db->prepare("SHOW COLUMNS FROM orders WHERE Field IN ('pickup_point_id', 'location_id')");
        $checkStmt->execute();
        $columns = $checkStmt->fetchAll();
        $columnNames = array_column($columns, 'Field');
        logMessage("Столбцы в orders: " . implode(', ', $columnNames) . " (pickup_point_id: " . (in_array('pickup_point_id', $columnNames) ? 'ДА' : 'НЕТ') . ", location_id: " . (in_array('location_id', $columnNames) ? 'ДА' : 'НЕТ') . ")", 'INFO');

        // Создаем заказ
        $stmt = $db->prepare("
            INSERT INTO orders
            (order_number, user_id, status, total_amount, final_amount, delivery_method,
             delivery_address, pickup_point_id, location_id, notes, deadline_at, created_at, updated_at, source)
            VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 'website')
        ");
        
        $deliveryMethod = isset($input['delivery_method']) ? $input['delivery_method'] : null;
        $deliveryAddress = isset($input['delivery_address']) ? $input['delivery_address'] : null;
        $notes = isset($input['notes']) ? $input['notes'] : null;
        $deadline = isset($input['deadline']) ? $input['deadline'] : null;

        $params = [
            $orderNumber,
            $userId,
            $pricing['total'],
            $pricing['final'],
            $deliveryMethod,
            $deliveryAddress,
            $pickupPointId,
            $locationId,
            $notes,
            $deadline
        ];

        logMessage("Параметры INSERT в orders: " . json_encode($params, JSON_UNESCAPED_UNICODE), 'INFO');

        try {
            $result = $stmt->execute($params);
        } catch (PDOException $e) {
            logMessage("ОШИБКА SQL при создании заказа: " . $e->getMessage(), 'ERROR');
            throw new Exception('Ошибка создания заказа: ' . $e->getMessage());
        }

        if (!$result) {
            logMessage("Execute вернул false при создании заказа", 'ERROR');
            throw new Exception('Ошибка создания заказа');
        }

        $orderId = $db->lastInsertId();

        // ЛОГИРОВАНИЕ: Проверяем что сохранилось в orders
        $stmt = $db->prepare("SELECT id, order_number, pickup_point_id, location_id, delivery_method FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $savedOrder = $stmt->fetch();
        logMessage("Заказ создан в БД: " . json_encode($savedOrder), 'INFO');
        
        // Добавляем позиции заказа
        foreach ($input['items'] as $item) {
            $stmt = $db->prepare("
                INSERT INTO order_items 
                (order_id, service_id, quantity, parameters, unit_price, total_price, notes, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $parameters = isset($item['parameters']) ? json_encode($item['parameters'], JSON_UNESCAPED_UNICODE) : null;
            $itemNotes = isset($item['notes']) ? $item['notes'] : null;
            
            $stmt->execute([
                $orderId,
                $item['service_id'],
                $item['quantity'],
                $parameters,
                $item['unit_price'],
                $item['total_price'],
                $itemNotes
            ]);
        }
        
        // Добавляем запись в историю статусов
        $stmt = $db->prepare("
            INSERT INTO order_status_history 
            (order_id, old_status, new_status, changed_by_type, changed_by_id, comment, created_at)
            VALUES (?, NULL, 'pending', 'user', ?, 'Заказ создан', NOW())
        ");
        $stmt->execute([$orderId, $userId]);
        
        $db->commit();

        // Получаем данные пользователя
        $user = $userService->getCurrentUser();

        // Создаем платеж через Tinkoff
        $tinkoffPayment = new TinkoffPayment();
        $paymentResult = $tinkoffPayment->createPayment(
            $orderId,
            $pricing['final'],
            "Оплата заказа №{$orderNumber}",
            [
                'email' => $user['email'] ?? null,
                'phone' => $user['phone'] ?? null
            ]
        );

        // Сохраняем ссылку на оплату в заказ
        if ($paymentResult['success']) {
            $stmt = $db->prepare("
                UPDATE orders
                SET tinkoff_payment_id = ?, tinkoff_payment_url = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $paymentResult['paymentId'],
                $paymentResult['paymentUrl'],
                $orderId
            ]);

            // Отправляем email клиенту с ссылкой на оплату
            if (!empty($user['email'])) {
                $emailService = new EmailService();
                $subject = "Заказ №{$orderNumber} - Ссылка на оплату";

                $body = getOrderEmailTemplate(
                    $orderNumber,
                    $pricing['final'],
                    $paymentResult['paymentUrl'],
                    getServicesNames($input['items'])
                );

                $altBody = "Здравствуйте!\n\n";
                $altBody .= "Ваш заказ №{$orderNumber} успешно создан.\n";
                $altBody .= "Сумма к оплате: {$pricing['final']} ₽\n\n";
                $altBody .= "Оплатите заказ по ссылке:\n{$paymentResult['paymentUrl']}\n\n";
                $altBody .= "С уважением,\nТипография";

                $emailService->sendEmail($user['email'], $subject, $body, $altBody);
            }
        } else {
            logMessage("Ошибка создания платежа для заказа {$orderId}: " . ($paymentResult['error'] ?? 'Unknown error'), 'ERROR');
        }

        // Отправляем уведомление в чат
        $chatResult = $chatService->getOrCreateUserChat($userId);
        if ($chatResult['success']) {
            $chatService->sendSystemMessage(
                $chatResult['chat_id'],
                "✅ Заказ #{$orderNumber} успешно создан! Мы свяжемся с вами для уточнения деталей.",
                'order_link',
                ['order_id' => $orderId, 'order_number' => $orderNumber]
            );
        }

        // Уведомление в Telegram
        $orderData = [
            'id' => $orderId,
            'client_name' => $user['name'] ?: $user['phone'],
            'client_phone' => $user['phone'],
            'service_name' => getServicesNames($input['items']),
            'description' => $notes,
            'price' => $pricing['final']
        ];
        $telegramNotifier->notifyNewOrder($orderData);
        
        logMessage("Создан новый заказ ID: {$orderId}, номер: {$orderNumber} пользователем ID: {$userId}", 'INFO');
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Заказ успешно создан',
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ]);
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        logMessage("Ошибка создания заказа пользователем {$userId}: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка создания заказа']);
    }
}

/**
 * Создание предварительной оценки
 */
function handleCreateEstimate($userService, $chatService) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'error' => 'Некорректные данные'], 400);
    }
    
    $userId = $_SESSION['user_id'];
    
    try {
        // Рассчитываем примерную стоимость
        $pricing = calculateOrderPricing($input['items']);
        
        // Отправляем сообщение в чат с оценкой
        $chatResult = $chatService->getOrCreateUserChat($userId);
        if ($chatResult['success']) {
            $servicesText = getServicesNames($input['items']);
            $message = "💰 Предварительная оценка заказа:\n\n";
            $message .= "📋 Услуги: {$servicesText}\n";
            $message .= "💵 Примерная стоимость: {$pricing['final']} ₽\n\n";
            $message .= "Для точного расчета и оформления заказа, пожалуйста, предоставьте дополнительную информацию.";
            
            $chatService->sendSystemMessage(
                $chatResult['chat_id'],
                $message,
                'system'
            );
        }
        
        logMessage("Создана предварительная оценка пользователем ID: {$userId}", 'INFO');
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Предварительная оценка отправлена в чат',
            'pricing' => $pricing
        ]);
        
    } catch (Exception $e) {
        logMessage("Ошибка создания предварительной оценки пользователем {$userId}: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка создания оценки']);
    }
}

/**
 * Загрузка файла к заказу
 */
function handleOrderFileUpload($userService) {
    // Применяем rate limiting
    checkRateLimit('order_upload', 10, 300); // 10 файлов в 5 минут
    
    $userId = $_SESSION['user_id'];
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;
    
    if (!$orderId) {
        sendJsonResponse(['success' => false, 'error' => 'Не указан ID заказа']);
    }
    
    // Проверяем права доступа к заказу
    if (!checkOrderAccess($orderId, $userId)) {
        sendJsonResponse(['success' => false, 'error' => 'Доступ к заказу запрещен'], 403);
    }
    
    // Проверяем наличие файла
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        sendJsonResponse(['success' => false, 'error' => 'Файл не загружен или произошла ошибка']);
    }
    
    $file = $_FILES['file'];
    
    // Проверяем файл
    if ($file['size'] > MAX_FILE_SIZE) {
        sendJsonResponse(['success' => false, 'error' => 'Файл слишком большой. Максимальный размер: ' . formatFileSize(MAX_FILE_SIZE)]);
    }
    
    $mimeType = getMimeType($file['tmp_name']);
    if (!isAllowedFileType($mimeType)) {
        sendJsonResponse(['success' => false, 'error' => 'Неподдерживаемый тип файла']);
    }
    
    try {
        // Создаем директорию для файлов заказов
        $uploadDir = UPLOADS_DIR . 'orders/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Генерируем безопасное имя файла
        $originalName = sanitizeFilename($file['name']);
        $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        
        $filePath = $uploadDir . $fileName;
        $fileUrl = UPLOADS_URL . 'orders/' . $fileName;
        
        // Перемещаем файл
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Ошибка сохранения файла');
        }
        
        // Сохраняем информацию о файле в БД
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO order_files 
            (order_id, file_name, file_path, file_size, file_type, uploaded_by_type, uploaded_by_id, uploaded_at)
            VALUES (?, ?, ?, ?, ?, 'user', ?, NOW())
        ");
        
        $result = $stmt->execute([
            $orderId,
            $originalName,
            $fileUrl,
            $file['size'],
            $mimeType,
            $userId
        ]);
        
        if (!$result) {
            unlink($filePath);
            throw new Exception('Ошибка сохранения информации о файле');
        }
        
        logMessage("Загружен файл '{$originalName}' к заказу {$orderId} пользователем {$userId}", 'INFO');
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Файл успешно загружен',
            'file_name' => $originalName,
            'file_path' => $fileUrl,
            'file_size' => $file['size']
        ]);
        
    } catch (Exception $e) {
        logMessage("Ошибка загрузки файла к заказу {$orderId}: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка загрузки файла']);
    }
}

/**
 * Валидация данных заказа
 */
function validateOrderInput($input) {
    $errors = [];
    
    // Проверяем наличие позиций
    if (!isset($input['items']) || !is_array($input['items']) || empty($input['items'])) {
        $errors[] = 'Необходимо указать хотя бы одну услугу';
        return $errors;
    }
    
    // Проверяем каждую позицию
    foreach ($input['items'] as $index => $item) {
        $itemPrefix = "Позиция " . ($index + 1) . ": ";
        
        if (!isset($item['service_id']) || !is_numeric($item['service_id'])) {
            $errors[] = $itemPrefix . "не указан ID услуги";
        }
        
        if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
            $errors[] = $itemPrefix . "некорректное количество";
        }
        
        if (!isset($item['unit_price']) || !is_numeric($item['unit_price']) || $item['unit_price'] < 0) {
            $errors[] = $itemPrefix . "некорректная цена за единицу";
        }
        
        if (!isset($item['total_price']) || !is_numeric($item['total_price']) || $item['total_price'] < 0) {
            $errors[] = $itemPrefix . "некорректная общая стоимость";
        }
    }
    
    // Проверяем метод доставки
    if (isset($input['delivery_method'])) {
        $allowedMethods = ['pickup', 'delivery'];
        if (!in_array($input['delivery_method'], $allowedMethods)) {
            $errors[] = 'Недопустимый метод доставки';
        }
        
        if ($input['delivery_method'] === 'delivery' && empty($input['delivery_address'])) {
            $errors[] = 'Необходимо указать адрес доставки';
        }
    }
    
    // Проверяем дедлайн
    if (isset($input['deadline']) && !empty($input['deadline'])) {
        $deadline = strtotime($input['deadline']);
        if (!$deadline || $deadline <= time()) {
            $errors[] = 'Некорректная дата дедлайна';
        }
    }
    
    return $errors;
}

/**
 * Расчет стоимости заказа
 */
function calculateOrderPricing($items) {
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item['total_price'];
    }
    
    // Здесь можно добавить логику скидок, налогов и т.д.
    $discount = 0;
    $final = $total - $discount;
    
    return [
        'total' => $total,
        'discount' => $discount,
        'final' => $final
    ];
}

/**
 * Генерация номера заказа
 */
function generateOrderNumber() {
    $prefix = date('Y');
    $randomPart = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    return $prefix . $randomPart;
}

/**
 * Получение названий услуг
 */
function getServicesNames($items) {
    try {
        $db = Database::getInstance()->getConnection();
        $serviceIds = array_column($items, 'service_id');
        
        if (empty($serviceIds)) {
            return 'Не указано';
        }
        
        $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
        $stmt = $db->prepare("SELECT name FROM services WHERE id IN ({$placeholders})");
        $stmt->execute($serviceIds);
        $services = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return implode(', ', $services);
        
    } catch (Exception $e) {
        logMessage("Ошибка получения названий услуг: " . $e->getMessage(), 'ERROR');
        return 'Не удалось определить';
    }
}

/**
 * Проверка доступа к заказу
 */
function checkOrderAccess($orderId, $userId) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT user_id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        return $order && $order['user_id'] == $userId;
        
    } catch (Exception $e) {
        logMessage("Ошибка проверки доступа к заказу {$orderId}: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Калькулятор стоимости
 */
function handleCalculatePrice() {
    $input = $_GET;
    
    // Простой калькулятор для демонстрации
    $serviceId = isset($input['service_id']) ? (int)$input['service_id'] : 0;
    $quantity = isset($input['quantity']) ? max(1, (int)$input['quantity']) : 1;
    
    if (!$serviceId) {
        sendJsonResponse(['success' => false, 'error' => 'Не указан ID услуги']);
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Получаем базовую цену услуги
        $stmt = $db->prepare("SELECT base_price, min_quantity FROM services WHERE id = ? AND is_active = 1");
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch();
        
        if (!$service) {
            sendJsonResponse(['success' => false, 'error' => 'Услуга не найдена']);
        }
        
        $quantity = max($quantity, $service['min_quantity']);
        $totalPrice = $service['base_price'] * $quantity;
        
        sendJsonResponse([
            'success' => true,
            'calculation' => [
                'service_id' => $serviceId,
                'quantity' => $quantity,
                'unit_price' => (float)$service['base_price'],
                'total_price' => $totalPrice,
                'min_quantity' => (int)$service['min_quantity']
            ]
        ]);
        
    } catch (Exception $e) {
        logMessage("Ошибка калькулятора: " . $e->getMessage(), 'ERROR');
        sendJsonResponse(['success' => false, 'error' => 'Ошибка расчета стоимости']);
    }
}

/**
 * Проверка rate limiting
 */
function checkRateLimit($action, $limit = 10, $window = 3600) {
    $userId = $_SESSION['user_id'] ?? 'anonymous';
    $ip = getUserIP();
    $key = "rate_limit_{$action}_{$userId}_{$ip}";
    
    $rateFile = sys_get_temp_dir() . "/{$key}.json";
    
    $now = time();
    $data = ['count' => 1, 'window_start' => $now];
    
    if (file_exists($rateFile)) {
        $existing = json_decode(file_get_contents($rateFile), true);
        if ($existing) {
            if ($now - $existing['window_start'] > $window) {
                $data = ['count' => 1, 'window_start' => $now];
            } else {
                $data = $existing;
                $data['count']++;
            }
        }
    }
    
    file_put_contents($rateFile, json_encode($data));
    
    if ($data['count'] > $limit) {
        logMessage("Rate limit exceeded for user {$userId}, IP {$ip}, action {$action}", 'WARNING');
        sendJsonResponse([
            'success' => false, 
            'error' => 'Превышен лимит запросов. Попробуйте позже.'
        ], 429);
    }
    
    return true;
}

/**
 * HTML шаблон письма с ссылкой на оплату
 */
function getOrderEmailTemplate($orderNumber, $finalAmount, $paymentUrl, $servicesText) {
    $formattedAmount = number_format($finalAmount, 0, '', ' ');
    return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказ ' . htmlspecialchars($orderNumber) . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Типография</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; margin-top: 0; font-size: 24px;">Заказ №' . htmlspecialchars($orderNumber) . ' успешно создан!</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6;">
                                Здравствуйте! Ваш заказ успешно оформлен. Для начала работы необходимо оплатить заказ.
                            </p>
                            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0;">
                                <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">Номер заказа:</p>
                                <p style="margin: 0 0 20px 0; color: #333333; font-size: 18px; font-weight: bold;">№' . htmlspecialchars($orderNumber) . '</p>
                                <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">Услуги:</p>
                                <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px;">' . htmlspecialchars($servicesText) . '</p>
                                <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">Сумма к оплате:</p>
                                <p style="margin: 0; color: #667eea; font-size: 24px; font-weight: bold;">' . htmlspecialchars($formattedAmount) . ' ₽</p>
                            </div>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="' . htmlspecialchars($paymentUrl) . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold;">
                                    Оплатить заказ
                                </a>
                            </div>
                            <p style="color: #666666; font-size: 14px; line-height: 1.6;">
                                После успешной оплаты мы приступим к выполнению вашего заказа. Вы получите уведомление на этот email.
                            </p>
                            <p style="color: #999999; font-size: 13px; line-height: 1.6; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eeeeee;">
                                Если кнопка не работает, скопируйте и вставьте эту ссылку в браузер:<br>
                                <a href="' . htmlspecialchars($paymentUrl) . '" style="color: #667eea; word-break: break-all;">' . htmlspecialchars($paymentUrl) . '</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                © ' . date('Y') . ' Типография. Все права защищены.
                            </p>
                            <p style="color: #999999; font-size: 12px; margin: 10px 0 0 0;">
                                <a href="https://typo-grafia.ru" style="color: #667eea; text-decoration: none;">https://typo-grafia.ru</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
    ';
}
?>
