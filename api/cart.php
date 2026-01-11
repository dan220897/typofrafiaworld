<?php
/**
 * API для работы с корзиной
 *
 * Endpoints:
 * GET    /api/cart.php - получить содержимое корзины
 * POST   /api/cart.php?action=add - добавить товар в корзину
 * PUT    /api/cart.php?action=update - обновить количество товара
 * DELETE /api/cart.php?action=remove - удалить товар из корзины
 * DELETE /api/cart.php?action=clear - очистить всю корзину
 * GET    /api/cart.php?action=pickup_points - получить точки самовывоза
 * POST   /api/cart.php?action=checkout - оформить заказ
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/UserService.php';

header('Content-Type: application/json; charset=utf-8');

// Запускаем сессию если не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Получаем session_id для корзины
$sessionId = session_id();

// Проверяем авторизацию
$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$userId = $isAuthenticated ? $userService->getCurrentUser()['id'] : null;

$db = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'pickup_points') {
                getPickupPoints($db);
            } else {
                getCart($db, $sessionId, $userId);
            }
            break;

        case 'POST':
            if ($action === 'add') {
                addToCart($db, $sessionId, $userId);
            } elseif ($action === 'checkout') {
                checkout($db, $sessionId, $userId);
            } else {
                sendError('Invalid action', 400);
            }
            break;

        case 'PUT':
            if ($action === 'update') {
                updateCartItem($db, $sessionId, $userId);
            } else {
                sendError('Invalid action', 400);
            }
            break;

        case 'DELETE':
            if ($action === 'remove') {
                removeFromCart($db, $sessionId, $userId);
            } elseif ($action === 'clear') {
                clearCart($db, $sessionId, $userId);
            } else {
                sendError('Invalid action', 400);
            }
            break;

        default:
            sendError('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Cart API Error: " . $e->getMessage());
    sendError('Internal server error: ' . $e->getMessage(), 500);
}

/**
 * Получить содержимое корзины
 */
function getCart($db, $sessionId, $userId) {
    $items = [];

    if ($userId) {
        // Для авторизованных пользователей
        $stmt = $db->prepare("
            SELECT c.*, s.label as service_name, s.category
            FROM cart c
            LEFT JOIN services s ON c.service_id = s.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$userId]);
    } else {
        // Для неавторизованных пользователей
        $stmt = $db->prepare("
            SELECT c.*, s.label as service_name, s.category
            FROM cart c
            LEFT JOIN services s ON c.service_id = s.id
            WHERE c.session_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$sessionId]);
    }

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Декодируем JSON параметры
    foreach ($items as &$item) {
        $item['parameters'] = json_decode($item['parameters'], true);
    }

    $totalAmount = array_sum(array_column($items, 'total_price'));
    $totalItems = count($items);

    sendSuccess([
        'items' => $items,
        'total_amount' => $totalAmount,
        'total_items' => $totalItems
    ]);
}

/**
 * Добавить товар в корзину
 */
function addToCart($db, $sessionId, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['service_id']) || !isset($data['quantity']) || !isset($data['unit_price'])) {
        sendError('Missing required fields: service_id, quantity, unit_price', 400);
    }

    $serviceId = $data['service_id'];
    $quantity = (int)$data['quantity'];
    $unitPrice = (float)$data['unit_price'];
    $parameters = isset($data['parameters']) ? json_encode($data['parameters'], JSON_UNESCAPED_UNICODE) : '{}';
    $totalPrice = $unitPrice * $quantity;

    // Проверяем существование услуги
    $stmt = $db->prepare("SELECT id FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    if (!$stmt->fetch()) {
        sendError('Service not found', 404);
    }

    // Проверяем, есть ли уже такой товар в корзине
    if ($userId) {
        $stmt = $db->prepare("
            SELECT id FROM cart
            WHERE user_id = ? AND service_id = ? AND parameters = ?
        ");
        $stmt->execute([$userId, $serviceId, $parameters]);
    } else {
        $stmt = $db->prepare("
            SELECT id FROM cart
            WHERE session_id = ? AND service_id = ? AND parameters = ?
        ");
        $stmt->execute([$sessionId, $serviceId, $parameters]);
    }

    $existingItem = $stmt->fetch();

    if ($existingItem) {
        // Обновляем количество
        $stmt = $db->prepare("
            UPDATE cart
            SET quantity = quantity + ?,
                total_price = (quantity + ?) * unit_price,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$quantity, $quantity, $existingItem['id']]);
    } else {
        // Добавляем новый товар
        $stmt = $db->prepare("
            INSERT INTO cart (session_id, user_id, service_id, quantity, parameters, unit_price, total_price, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId ? null : $sessionId,
            $userId,
            $serviceId,
            $quantity,
            $parameters,
            $unitPrice,
            $totalPrice
        ]);
    }

    // Возвращаем обновленную корзину
    getCart($db, $sessionId, $userId);
}

/**
 * Обновить количество товара в корзине
 */
function updateCartItem($db, $sessionId, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['cart_id']) || !isset($data['quantity'])) {
        sendError('Missing required fields: cart_id, quantity', 400);
    }

    $cartId = (int)$data['cart_id'];
    $quantity = (int)$data['quantity'];

    if ($quantity <= 0) {
        sendError('Quantity must be greater than 0', 400);
    }

    // Проверяем принадлежность товара корзине пользователя
    if ($userId) {
        $stmt = $db->prepare("SELECT id, unit_price FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartId, $userId]);
    } else {
        $stmt = $db->prepare("SELECT id, unit_price FROM cart WHERE id = ? AND session_id = ?");
        $stmt->execute([$cartId, $sessionId]);
    }

    $item = $stmt->fetch();
    if (!$item) {
        sendError('Cart item not found', 404);
    }

    // Обновляем количество
    $totalPrice = $item['unit_price'] * $quantity;
    $stmt = $db->prepare("
        UPDATE cart
        SET quantity = ?, total_price = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$quantity, $totalPrice, $cartId]);

    getCart($db, $sessionId, $userId);
}

/**
 * Удалить товар из корзины
 */
function removeFromCart($db, $sessionId, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['cart_id'])) {
        sendError('Missing required field: cart_id', 400);
    }

    $cartId = (int)$data['cart_id'];

    // Удаляем товар
    if ($userId) {
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartId, $userId]);
    } else {
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
        $stmt->execute([$cartId, $sessionId]);
    }

    getCart($db, $sessionId, $userId);
}

/**
 * Очистить всю корзину
 */
function clearCart($db, $sessionId, $userId) {
    if ($userId) {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("DELETE FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }

    sendSuccess(['message' => 'Cart cleared successfully']);
}

/**
 * Получить точки самовывоза
 */
function getPickupPoints($db) {
    $stmt = $db->query("
        SELECT id, name, address, latitude, longitude, phone, working_hours, description
        FROM pickup_points
        WHERE is_active = 1
        ORDER BY sort_order, name
    ");

    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendSuccess(['pickup_points' => $points]);
}

/**
 * Оформить заказ без авторизации
 */
function checkout($db, $sessionId, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Валидация
    if (!isset($data['name']) || !isset($data['phone']) || !isset($data['pickup_point_id'])) {
        sendError('Missing required fields: name, phone, pickup_point_id', 400);
    }

    $name = trim($data['name']);
    $phone = trim($data['phone']);
    $pickupPointId = (int)$data['pickup_point_id'];
    $email = isset($data['email']) ? trim($data['email']) : null;
    $notes = isset($data['notes']) ? trim($data['notes']) : '';

    // Валидация имени
    if (empty($name)) {
        sendError('Name is required', 400);
    }

    // Валидация телефона (простая проверка)
    if (!preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $phone)) {
        sendError('Invalid phone number format', 400);
    }

    // Проверяем точку самовывоза
    $stmt = $db->prepare("SELECT id, name, address FROM pickup_points WHERE id = ? AND is_active = 1");
    $stmt->execute([$pickupPointId]);
    $pickupPoint = $stmt->fetch();

    if (!$pickupPoint) {
        sendError('Invalid pickup point', 404);
    }

    // Получаем товары из корзины
    if ($userId) {
        $stmt = $db->prepare("SELECT * FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("SELECT * FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }

    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        sendError('Cart is empty', 400);
    }

    // Начинаем транзакцию
    $db->beginTransaction();

    try {
        // Создаем или находим пользователя
        if (!$userId) {
            // Для неавторизованных пользователей ищем по email или телефону
            if ($email) {
                // Ищем по email или телефону одновременно
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
                $stmt->execute([$email, $phone]);
            } else {
                // Если email не указан, ищем только по телефону
                $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
                $stmt->execute([$phone]);
            }

            $user = $stmt->fetch();

            if ($user) {
                // Пользователь найден
                $userId = $user['id'];
            } else {
                // Пользователь не найден - создаем нового
                $stmt = $db->prepare("
                    INSERT INTO users (name, phone, email, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $phone, $email]);
                $userId = $db->lastInsertId();
            }
        }

        // Рассчитываем сумму заказа
        $totalAmount = array_sum(array_column($cartItems, 'total_price'));

        // Создаем номер заказа
        $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Создаем заказ
        $deliveryAddress = $pickupPoint['name'] . ', ' . $pickupPoint['address'];

        $stmt = $db->prepare("
            INSERT INTO orders (
                order_number, user_id, status, total_amount, final_amount,
                delivery_method, delivery_address, payment_status,
                created_at
            ) VALUES (?, ?, 'pending', ?, ?, 'pickup', ?, 'pending', NOW())
        ");
        $stmt->execute([$orderNumber, $userId, $totalAmount, $totalAmount, $deliveryAddress]);
        $orderId = $db->lastInsertId();

        // Добавляем товары в заказ
        foreach ($cartItems as $item) {
            $stmt = $db->prepare("
                INSERT INTO order_items (
                    order_id, service_id, quantity, parameters,
                    unit_price, total_price, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $orderId,
                $item['service_id'],
                $item['quantity'],
                $item['parameters'],
                $item['unit_price'],
                $item['total_price']
            ]);
        }

        // Очищаем корзину
        if ($userId) {
            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("DELETE FROM cart WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        }

        // Добавляем запись в историю статусов
        $stmt = $db->prepare("
            INSERT INTO order_status_history (order_id, status, comment, created_at)
            VALUES (?, 'pending', 'Заказ создан', NOW())
        ");
        $stmt->execute([$orderId]);

        $db->commit();

        // Отправляем уведомления (TODO: Telegram, Email, SMS)

        sendSuccess([
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'message' => 'Order created successfully'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Отправить успешный ответ
 */
function sendSuccess($data) {
    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Отправить ошибку
 */
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
