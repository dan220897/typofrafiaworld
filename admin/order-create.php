<?php
// admin/order-create.php - Создание нового заказа
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'classes/Service.php';
require_once 'classes/User.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('edit_orders');

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$service = new Service($db);
$user = new User($db);
$adminLog = new AdminLog($db);

// Получаем ID пользователя, если передан
$user_id = intval($_GET['user_id'] ?? 0);
$userData = null;
if ($user_id) {
    $userData = $user->getUserById($user_id);
}

// Получаем список всех активных пользователей для выбора
$users = $user->getActiveUsers();

// Получаем список активных услуг
$services = $service->getActiveServices();

// Получаем список активных точек самовывоза
$query = "SELECT id, name, address, working_hours FROM pickup_points WHERE is_active = 1 ORDER BY sort_order, name";
$stmt = $db->prepare($query);
$stmt->execute();
$pickupPoints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка AJAX запроса для создания новой услуги
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'create_service') {
    header('Content-Type: application/json');
    
    try {
        $serviceData = [
            'name' => trim($_POST['service_name'] ?? ''),
            'base_price' => floatval($_POST['service_price'] ?? 0),
            'category' => trim($_POST['service_category'] ?? 'другое'),
            'description' => trim($_POST['service_description'] ?? ''),
            'min_quantity' => 1,
            'production_time_days' => 1,
            'is_active' => 1
        ];
        
        if (empty($serviceData['name'])) {
            throw new Exception('Название услуги обязательно');
        }
        
        $service_id = $service->createService($serviceData);
        
        if (!$service_id) {
            throw new Exception('Ошибка создания услуги');
        }
        
        // Логируем действие
        $adminLog->log($_SESSION['admin_id'], 'create_service', 
            "Создана новая услуга: {$serviceData['name']}", 
            'service', $service_id);
        
        echo json_encode([
            'success' => true,
            'service' => [
                'id' => $service_id,
                'name' => $serviceData['name'],
                'base_price' => $serviceData['base_price']
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_action'])) {
    try {
        // Валидация пользователя
        $selectedUserId = intval($_POST['user_id'] ?? 0);
        $isNewUser = false;
        $isAnonymous = isset($_POST['is_anonymous']) && $_POST['is_anonymous'] == '1';

        if (!$selectedUserId) {
            if ($isAnonymous) {
                // Создаем анонимного виртуального пользователя
                // Генерируем уникальный номер для анонимного клиента
                $query = "SELECT COUNT(*) as count FROM users WHERE name LIKE 'Клиент %'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch();
                $anonymousNumber = ($result['count'] ?? 0) + 1;

                $anonymousUserData = [
                    'phone' => null,
                    'name' => "Клиент {$anonymousNumber}",
                    'email' => "anonymous{$anonymousNumber}@typografia.local",
                    'company_name' => ''
                ];

                $selectedUserId = $user->createUser($anonymousUserData);
                $isNewUser = true;
            } else {
                // Создаем нового пользователя с реальными данными
                $newUserEmail = trim($_POST['new_user_email'] ?? '');
                $newUserPhone = trim($_POST['new_user_phone'] ?? '');

                // Валидация email (обязательно)
                if (empty($newUserEmail) || !filter_var($newUserEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Введите корректный email адрес');
                }

                // Обработка телефона (необязательно)
                if (!empty($newUserPhone)) {
                    // Очищаем номер от форматирования, оставляем только цифры
                    $newUserPhone = preg_replace('/\D/', '', $newUserPhone);

                    // Проверяем формат если телефон указан
                    if (strlen($newUserPhone) !== 11 || $newUserPhone[0] !== '7') {
                        throw new Exception('Некорректный номер телефона. Введите номер в формате +7 (XXX) XXX-XX-XX');
                    }

                    // Форматируем для сохранения в БД
                    $newUserPhone = '+' . $newUserPhone;
                } else {
                    $newUserPhone = null;
                }

                // Проверяем, может пользователь уже существует по email
                $existingUser = $user->getUserByEmail($newUserEmail);
                if ($existingUser) {
                    $selectedUserId = $existingUser['id'];
                } else {
                    // Создаем нового пользователя
                    $newUserData = [
                        'phone' => $newUserPhone,
                        'name' => trim($_POST['new_user_name'] ?? ''),
                        'email' => $newUserEmail,
                        'company_name' => trim($_POST['new_user_company'] ?? '')
                    ];

                    $selectedUserId = $user->createUser($newUserData);
                    $isNewUser = true;
                }
            }
        }
        
        // Получаем комментарий как строку
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;
        
        // Подготавливаем позиции заказа
        $orderItems = [];
        $totalAmount = 0;
        
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (empty($item['service_id']) || empty($item['quantity'])) {
                    continue;
                }
                
                $unitPrice = floatval($item['unit_price']);
                $quantity = intval($item['quantity']);
                $itemTotal = $unitPrice * $quantity;
                
                $orderItems[] = [
                    'service_id' => intval($item['service_id']),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'parameters' => !empty($item['parameters']) ? $item['parameters'] : [],
                    'notes' => $item['notes'] ?? ''
                ];
                
                $totalAmount += $itemTotal;
            }
        }
        
        // Проверяем, что есть хотя бы одна позиция
        if (empty($orderItems)) {
            throw new Exception('Добавьте хотя бы одну услугу в заказ');
        }
        
        // Создаем заказ БЕЗ генерации платежной ссылки
        $order_id = $order->createOrder($selectedUserId, $orderItems, $comment, false);
        
        if (!$order_id) {
            throw new Exception('Ошибка создания заказа');
        }
        
        // Применяем скидку если есть
        $discountAmount = floatval($_POST['discount_amount'] ?? 0);
        if ($discountAmount > 0) {
            $finalAmount = $totalAmount - $discountAmount;
            
            $query = "UPDATE orders SET 
                      discount_amount = :discount, 
                      final_amount = :final_amount 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                'discount' => $discountAmount,
                'final_amount' => $finalAmount,
                'id' => $order_id
            ]);
        }
        
        // Обновляем дополнительную информацию о заказе
        $updateData = [];
        $updateParams = ['id' => $order_id];

        // Обрабатываем пункт самовывоза
        if (!empty($_POST['pickup_point_id'])) {
            $pickupPointId = intval($_POST['pickup_point_id']);

            // Получаем информацию о пункте самовывоза
            $pickupQuery = "SELECT name, address FROM pickup_points WHERE id = ? AND is_active = 1";
            $pickupStmt = $db->prepare($pickupQuery);
            $pickupStmt->execute([$pickupPointId]);
            $pickupPoint = $pickupStmt->fetch(PDO::FETCH_ASSOC);

            if ($pickupPoint) {
                $updateData[] = "delivery_method = :delivery_method";
                $updateParams['delivery_method'] = 'pickup';

                $updateData[] = "delivery_address = :delivery_address";
                $updateParams['delivery_address'] = $pickupPoint['name'] . ' - ' . $pickupPoint['address'];

                // Сохраняем ID пункта самовывоза если поле есть в таблице
                try {
                    $checkColumn = $db->query("SHOW COLUMNS FROM orders LIKE 'pickup_point_id'");
                    if ($checkColumn->rowCount() > 0) {
                        $updateData[] = "pickup_point_id = :pickup_point_id";
                        $updateParams['pickup_point_id'] = $pickupPointId;
                    }
                } catch (Exception $e) {
                    // Поле не существует, пропускаем
                }
            }
        }

        if (!empty($_POST['deadline_at'])) {
            $updateData[] = "deadline_at = :deadline_at";
            $updateParams['deadline_at'] = $_POST['deadline_at'];
        }

        if (!empty($updateData)) {
            $query = "UPDATE orders SET " . implode(', ', $updateData) . " WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute($updateParams);
        }
        
        // Теперь генерируем платежную ссылку, когда сумма уже рассчитана
        try {
            // Получаем финальную сумму заказа
            $query = "SELECT final_amount FROM orders WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute(['id' => $order_id]);
            $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Генерируем ссылку только если сумма больше 0
            if ($orderData && $orderData['final_amount'] > 0) {
                $order->generatePaymentLink($order_id);
            }
        } catch (Exception $e) {
            // Логируем ошибку, но не прерываем создание заказа
            error_log('Ошибка создания платежной ссылки: ' . $e->getMessage());
            // Можно добавить уведомление для администратора
            $_SESSION['warning'] = 'Заказ создан, но не удалось сгенерировать платежную ссылку: ' . $e->getMessage();
        }
        
        // Получаем информацию о созданном заказе для логирования
        $orderInfo = $order->getOrderById($order_id);
        
        // Логируем действие
        $logMessage = "Создан заказ #{$orderInfo['order_number']}";
        if ($isNewUser) {
            $logMessage .= ' и новый пользователь';
        }
        
        $adminLog->log($_SESSION['admin_id'], 'create_order', $logMessage, 'order', $order_id);
        
        // Отправляем уведомление в Telegram если включено
        if (defined('TELEGRAM_NOTIFICATIONS_ENABLED') && TELEGRAM_NOTIFICATIONS_ENABLED) {
            try {
                $tgMessage = "🆕 Новый заказ #{$orderInfo['order_number']}\n";
                $tgMessage .= "💰 Сумма: " . number_format($orderInfo['final_amount'], 0, '', ' ') . " руб.\n";
                $tgMessage .= "👤 Клиент: " . ($orderInfo['user_name'] ?: 'Без имени') . "\n";
                $tgMessage .= "📱 Телефон: " . $orderInfo['user_phone'];
                
                // Здесь можно добавить отправку уведомления в Telegram
                // $telegram->sendMessage($tgMessage);
            } catch (Exception $e) {
                error_log('Ошибка отправки уведомления в Telegram: ' . $e->getMessage());
            }
        }
        
        $_SESSION['success'] = 'Заказ успешно создан';
        header('Location: order-details.php?id=' . $order_id);
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Ошибка создания заказа: ' . $e->getMessage();
        error_log('Ошибка создания заказа: ' . $e->getMessage());
    }
}

// Заголовок страницы
$page_title = 'Создание заказа';
$current_page = 'orders';
require_once 'includes/header.php';
?>

<style>
/* Основные стили страницы */
body {
    background-color: #f3f4f6;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container-fluid {
    padding: 2rem;
}

/* Навигация */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    font-size: 0.875rem;
}

.breadcrumb a {
    color: #6b7280;
    text-decoration: none;
}

.breadcrumb a:hover {
    color: #3b82f6;
}

.breadcrumb .separator {
    color: #9ca3af;
}

/* Заголовок страницы */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

/* Кнопки */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

/* Карточки */
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

/* Форма */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #1f2937 !important;
    background-color: #ffffff !important;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Стили для поля телефона */
input[type="tel"] {
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}

input[type="tel"]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

input[type="tel"]:invalid {
    border-color: #ef4444;
}

input[type="tel"]:invalid:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Выбор клиента */
.client-selector {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.radio-group {
    display: flex;
    gap: 1rem;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.client-forms {
    margin-top: 1rem;
}

.client-form-section {
    display: none;
}

.client-form-section.active {
    display: block;
}

/* Список клиентов */
.clients-search {
    margin-bottom: 1rem;
}

.search-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
}

.clients-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
}

.client-card {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background 0.2s;
}

.client-card:hover {
    background: #f9fafb;
}

.client-card.selected {
    background: #e0e7ff;
    border-color: #3b82f6;
}

.client-card:last-child {
    border-bottom: none;
}

.client-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.client-info {
    font-size: 0.75rem;
    color: #6b7280;
    display: flex;
    gap: 1rem;
}

.no-clients {
    padding: 2rem;
    text-align: center;
    color: #6b7280;
}

/* Таблица товаров */
.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    text-align: left;
    padding: 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #6b7280;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.items-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.items-table .form-control {
    margin: 0;
}

/* Стили для селектов */
.items-table select,
select.form-control {
    color: #1f2937 !important;
    background-color: #ffffff !important;
}

.items-table select option,
select.form-control option {
    color: #1f2937 !important;
    background-color: #ffffff !important;
    padding: 0.5rem;
}

/* Стили для выделенных опций в селекте */
.items-table select option:hover,
select.form-control option:hover,
.items-table select option:focus,
select.form-control option:focus {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
}

.items-table select option:checked,
select.form-control option:checked {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
}

.btn-remove-item {
    background: #fee2e2;
    color: #991b1b;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove-item:hover {
    background: #fecaca;
}

/* Модальное окно */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.close {
    color: #6b7280;
    font-size: 1.5rem;
    cursor: pointer;
    background: none;
    border: none;
}

.close:hover {
    color: #1f2937;
}

/* Добавление услуги */
.add-service-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.add-custom-service {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.add-custom-service:hover {
    text-decoration: underline;
}

/* Итоги */
.totals-section {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 6px;
    margin-top: 1.5rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.total-label {
    color: #6b7280;
}

.total-value {
    font-weight: 500;
    color: #1f2937;
    font-size: 1rem;
}

.total-row.final {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
}

/* Алерты */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Выбор услуги - новый дизайн */
.service-display {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn-select-service {
    padding: 0.75rem 1rem;
    background: #3b82f6;
    color: #ffffff;
    border: 2px dashed #3b82f6;
    border-radius: 6px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
}

.btn-select-service:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.selected-service-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f0f9ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
}

.service-name-display {
    flex: 1;
    font-weight: 500;
    color: #1e40af;
}

.btn-change-service {
    padding: 0.25rem 0.5rem;
    background: #3b82f6;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-change-service:hover {
    background: #2563eb;
}

/* Модальное окно выбора услуг */
.service-search-box {
    position: relative;
    margin-bottom: 1.5rem;
}

.service-search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.service-search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #1f2937;
    background-color: #ffffff;
    transition: all 0.2s;
}

.service-search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
    max-height: 500px;
    overflow-y: auto;
    padding: 0.5rem;
}

.service-card {
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    position: relative;
}

.service-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
}

.service-card:hover .btn-select-this-service {
    background: #2563eb;
    transform: scale(1.05);
}

.service-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 0.5rem;
}

.service-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.service-card-id {
    padding: 0.125rem 0.375rem;
    background: #e0e7ff;
    color: #4f46e5;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.service-card-slug {
    font-size: 0.75rem;
    color: #9ca3af;
    font-family: 'Courier New', monospace;
}

.service-card-category {
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 4px;
    font-size: 0.75rem;
    white-space: nowrap;
}

.service-card-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.4;
}

.service-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.service-card-price {
    font-size: 1rem;
    font-weight: 600;
    color: #059669;
}

.btn-select-this-service {
    padding: 0.5rem 1rem;
    background: #3b82f6;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-select-this-service:hover {
    background: #2563eb;
}

.no-services-found {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
}

.no-services-found i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

/* Калькулятор услуг */
.service-calculator {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.service-calculator select,
.service-calculator input {
    font-size: 0.875rem;
    color: #1f2937 !important;
    background-color: #ffffff !important;
}

.service-calculator select option {
    color: #1f2937 !important;
    background-color: #ffffff !important;
    padding: 0.5rem;
}

.service-calculator select option:hover,
.service-calculator select option:focus {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
}

.service-calculator select option:checked {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
}

/* Информационный блок для анонимного клиента */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.alert-info {
    background-color: #e0f2fe;
    border: 1px solid #0ea5e9;
    color: #075985;
}

.alert-info i {
    margin-right: 0.5rem;
}

/* Адаптив */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container-fluid">
    <!-- Хлебные крошки -->
    <div class="breadcrumb">
        <a href="/admin/">Главная</a>
        <span class="separator">/</span>
        <a href="orders.php">Заказы</a>
        <span class="separator">/</span>
        <span>Создание заказа</span>
    </div>
    
    <!-- Заголовок -->
    <div class="page-header">
        <h1 class="page-title">Новый заказ</h1>
        <div>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>
    
    <!-- Алерты -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" id="orderForm">
        <!-- Выбор клиента -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Клиент</h2>
            </div>
            
            <div class="client-selector">
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="client_type" value="existing"
                               <?php echo $user_id ? 'checked' : ''; ?>
                               onchange="toggleClientForm('existing')">
                        Существующий клиент
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="client_type" value="new"
                               <?php echo !$user_id ? 'checked' : ''; ?>
                               onchange="toggleClientForm('new')">
                        Новый клиент
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="client_type" value="anonymous"
                               onchange="toggleClientForm('anonymous')">
                        Отказался оставлять контактные данные
                    </label>
                </div>
            </div>
            
            <div class="client-forms">
                <!-- Выбор существующего клиента -->
                <div class="client-form-section <?php echo $user_id ? 'active' : ''; ?>" id="existingClientForm">
                    <div class="clients-search">
                        <input type="text" id="clientSearchInput" class="search-input" 
                               placeholder="Поиск по имени, телефону или email...">
                    </div>
                    
                    <div class="clients-list" id="clientsList">
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                            <div class="client-card" data-client-id="<?php echo $u['id']; ?>"
                                 data-search="<?php echo strtolower(($u['name'] ?? '') . ' ' . ($u['phone'] ?? '') . ' ' . ($u['email'] ?? '') . ' ' . ($u['company_name'] ?? '')); ?>">
                                <div class="client-name">
                                    <?php echo htmlspecialchars($u['name'] ?: 'Без имени'); ?>
                                    <?php if (!empty($u['company_name'])): ?>
                                        (<?php echo htmlspecialchars($u['company_name']); ?>)
                                    <?php endif; ?>
                                </div>
                                <div class="client-info">
                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($u['phone'] ?? ''); ?></span>
                                    <?php if (!empty($u['email'])): ?>
                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($u['email']); ?></span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-shopping-cart"></i> Заказов: <?php echo $u['orders_count'] ?? 0; ?></span>
                                    <?php if (!empty($u['created_at'])): ?>
                                        <span><i class="fas fa-calendar"></i> Регистрация: <?php echo date('d.m.Y', strtotime($u['created_at'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-clients">
                                <p>Клиенты не найдены</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="user_id" id="selectedUserId" value="<?php echo $user_id; ?>">
                </div>
                
                <!-- Форма нового клиента -->
                <div class="client-form-section <?php echo !$user_id ? 'active' : ''; ?>" id="newClientForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="new_user_email" id="newUserEmail" class="form-control"
                                   placeholder="email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Имя</label>
                            <input type="text" name="new_user_name" class="form-control"
                                   placeholder="Иван Иванов">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Телефон</label>
                            <input type="tel" name="new_user_phone" id="newUserPhone" class="form-control"
                                   placeholder="+7 (999) 123-45-67">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Компания</label>
                            <input type="text" name="new_user_company" class="form-control"
                                   placeholder="ООО Рога и копыта">
                        </div>
                    </div>
                </div>

                <!-- Форма анонимного клиента -->
                <div class="client-form-section" id="anonymousClientForm">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Будет создан виртуальный клиент с автоматически сгенерированными данными.
                    </div>
                    <input type="hidden" name="is_anonymous" id="isAnonymous" value="0">
                </div>
            </div>
        </div>
        
        <!-- Товары и услуги -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Товары и услуги</h2>
                <button type="button" class="btn btn-success" onclick="addItem()">
                    <i class="fas fa-plus"></i> Добавить позицию
                </button>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="40%">Услуга</th>
                        <th width="15%">Количество</th>
                        <th width="15%">Цена за ед.</th>
                        <th width="15%">Сумма</th>
                        <th width="10%">Примечание</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody id="itemsContainer">
                    <!-- Первая пустая строка -->
                    <tr class="item-row" data-index="0">
                        <td>
                            <div class="service-display">
                                <input type="hidden" name="items[0][service_id]" class="service-id-input" value="">
                                <button type="button" class="btn-select-service" onclick="openServiceSelector(0)">
                                    <i class="fas fa-plus-circle"></i> Выбрать услугу
                                </button>
                                <div class="selected-service-info" style="display: none;">
                                    <div class="service-name-display"></div>
                                    <button type="button" class="btn-change-service" onclick="openServiceSelector(0)">
                                        <i class="fas fa-edit"></i> Изменить
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" value="1" min="1" 
                                   class="form-control quantity-input" onchange="updateItemTotal(this)">
                        </td>
                        <td>
                            <input type="number" name="items[0][unit_price]" value="0" min="0" step="0.01" 
                                   class="form-control price-input" onchange="updateItemTotal(this)">
                        </td>
                        <td>
                            <div class="item-total" data-total="0">₽0</div>
                        </td>
                        <td class="item-notes-cell">
                            <input type="text" name="items[0][notes]" class="form-control" placeholder="...">
                        </td>
                        <td>
                            <button type="button" class="btn-remove-item" onclick="removeItem(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="totals-section">
                <div class="total-row">
                    <span class="total-label">Сумма:</span>
                    <span class="total-value" id="subtotalAmount">₽0</span>
                    <input type="hidden" id="subtotalInput" value="0">
                </div>
                <div class="total-row">
                    <span class="total-label">Скидка:</span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>₽</span>
                        <input type="number" name="discount_amount" id="discountAmount" 
                               value="0" min="0" step="0.01" class="form-control" style="width: 120px;"
                               onchange="updateTotals()">
                    </div>
                </div>
                <div class="total-row final">
                    <span>Итого к оплате:</span>
                    <span id="finalAmount">₽0</span>
                    <input type="hidden" id="finalAmountInput" value="0">
                </div>
            </div>
        </div>
        
        <!-- Дополнительная информация -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Дополнительная информация</h2>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Пункт самовывоза *</label>
                    <select name="pickup_point_id" id="pickupPointSelect" class="form-control" required>
                        <option value="">Выберите пункт самовывоза</option>
                        <?php foreach ($pickupPoints as $point): ?>
                        <option value="<?php echo $point['id']; ?>"
                                data-address="<?php echo htmlspecialchars($point['address']); ?>"
                                data-hours="<?php echo htmlspecialchars($point['working_hours'] ?? ''); ?>">
                            <?php echo htmlspecialchars($point['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;" id="pickupPointInfo"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Срок выполнения</label>
                    <input type="date" name="deadline_at" class="form-control"
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Комментарий к заказу</label>
                <textarea name="comment" class="form-control" rows="3"
                          placeholder="Дополнительная информация о заказе..."></textarea>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Создать заказ
            </button>
            <a href="orders.php" class="btn btn-secondary">
                Отмена
            </a>
        </div>
    </form>
</div>

<!-- Модальное окно для добавления новой услуги -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Добавить новую услугу</h3>
            <button class="close" onclick="closeServiceModal()">&times;</button>
        </div>
        
        <form id="serviceModalForm">
            <input type="hidden" id="serviceModalItemIndex" value="">
            
            <div class="form-group">
                <label class="form-label">Название услуги *</label>
                <input type="text" id="serviceModalName" class="form-control" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Категория</label>
                    <select id="serviceModalCategory" class="form-control">
                        <option value="печать">Печать</option>
                        <option value="дизайн">Дизайн</option>
                        <option value="постпечать">Постпечать</option>
                        <option value="другое">Другое</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Базовая цена</label>
                    <input type="number" id="serviceModalPrice" class="form-control" 
                           min="0" step="0.01" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Описание</label>
                <textarea id="serviceModalDescription" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeServiceModal()">
                    Отмена
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для выбора услуги -->
<div id="serviceSelectorModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3 class="modal-title">Выбрать услугу</h3>
            <button class="close" onclick="closeServiceSelectorModal()">&times;</button>
        </div>

        <input type="hidden" id="serviceSelectorItemIndex" value="">

        <!-- Поиск -->
        <div class="service-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="serviceSearchInput" class="service-search-input"
                   placeholder="Поиск по названию или категории услуги..."
                   onkeyup="filterServices()">
        </div>

        <!-- Список услуг -->
        <div class="services-grid" id="servicesGrid">
            <?php foreach ($services as $srv):
                $displayName = !empty($srv['label']) ? $srv['label'] : $srv['name'];
            ?>
            <div class="service-card"
                 data-service-id="<?php echo htmlspecialchars($srv['id']); ?>"
                 data-service-name="<?php echo htmlspecialchars($displayName); ?>"
                 data-service-category="<?php echo htmlspecialchars($srv['category'] ?? ''); ?>"
                 data-base-price="<?php echo floatval($srv['base_price']); ?>"
                 data-search="<?php echo strtolower(htmlspecialchars($displayName) . ' ' . htmlspecialchars($srv['category'] ?? '') . ' ' . htmlspecialchars($srv['description'] ?? '') . ' ' . $srv['id']); ?>">
                <div class="service-card-header">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <h4 class="service-card-title" style="margin: 0;"><?php echo htmlspecialchars($displayName); ?></h4>
                            <span class="service-card-id">ID: <?php echo htmlspecialchars($srv['id']); ?></span>
                        </div>
                        <?php if (!empty($srv['name']) && strtolower($srv['name']) !== strtolower($displayName)): ?>
                        <div class="service-card-slug"><?php echo htmlspecialchars($srv['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($srv['category'])): ?>
                    <span class="service-card-category"><?php echo htmlspecialchars($srv['category']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($srv['description'])): ?>
                <p class="service-card-description"><?php echo htmlspecialchars(mb_substr($srv['description'], 0, 100)); ?><?php echo mb_strlen($srv['description']) > 100 ? '...' : ''; ?></p>
                <?php endif; ?>
                <div class="service-card-footer">
                    <span class="service-card-price">от <?php echo number_format($srv['base_price'], 0, ',', ' '); ?> ₽</span>
                    <button type="button" class="btn-select-this-service" onclick="selectService('<?php echo htmlspecialchars($srv['id']); ?>'); return false;">
                        <i class="fas fa-check"></i> Выбрать
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="no-services-found" id="noServicesFound" style="display: none;">
            <i class="fas fa-search"></i>
            <p>Услуги не найдены</p>
        </div>
    </div>
</div>

<script>
let itemIndex = 1;
let services = <?php echo json_encode($services); ?>;
let selectedClientId = <?php echo $user_id ?: 'null'; ?>;

// Маска для телефона
function initPhoneMask() {
    const phoneInput = document.getElementById('newUserPhone');
    if (!phoneInput) return;
    
    // Автоматически добавляем +7 при фокусе
    phoneInput.addEventListener('focus', function() {
        if (this.value === '') {
            this.value = '+7 ';
        }
    });
    
    // Форматирование при вводе
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Оставляем только цифры
        
        // Если начинается не с 7, добавляем 7
        if (value.length > 0 && value[0] !== '7') {
            value = '7' + value;
        }
        
        // Ограничиваем длину
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        // Форматируем
        let formatted = '';
        if (value.length > 0) {
            formatted = '+' + value.substring(0, 1);
        }
        if (value.length > 1) {
            formatted += ' (' + value.substring(1, 4);
        }
        if (value.length > 4) {
            formatted += ') ' + value.substring(4, 7);
        }
        if (value.length > 7) {
            formatted += '-' + value.substring(7, 9);
        }
        if (value.length > 9) {
            formatted += '-' + value.substring(9, 11);
        }
        
        e.target.value = formatted;
    });
    
    // Валидация при потере фокуса
    phoneInput.addEventListener('blur', function() {
        const digits = this.value.replace(/\D/g, '');
        if (digits.length > 0 && digits.length !== 11) {
            this.setCustomValidity('Введите корректный номер телефона');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Разрешаем удаление
    phoneInput.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '+7 ') {
            e.preventDefault();
            this.value = '';
        }
    });
}

// Показать информацию о выбранном пункте самовывоза
function updatePickupPointInfo() {
    const select = document.getElementById('pickupPointSelect');
    const infoDiv = document.getElementById('pickupPointInfo');

    if (select.value) {
        const option = select.options[select.selectedIndex];
        const address = option.dataset.address;
        const hours = option.dataset.hours;

        let html = '<i class="fas fa-map-marker-alt"></i> ' + address;
        if (hours) {
            html += '<br><i class="fas fa-clock"></i> ' + hours;
        }
        infoDiv.innerHTML = html;
    } else {
        infoDiv.innerHTML = '';
    }
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем маску телефона
    initPhoneMask();

    // Добавляем обработчик изменения пункта самовывоза
    const pickupSelect = document.getElementById('pickupPointSelect');
    if (pickupSelect) {
        pickupSelect.addEventListener('change', updatePickupPointInfo);
    }
    
// Устанавливаем правильный required атрибут в зависимости от выбранного типа клиента
    const clientType = document.querySelector('input[name="client_type"]:checked');
    if (clientType && clientType.value === 'new') {
        const emailInput = document.getElementById('newUserEmail');
        if (emailInput) {
            emailInput.setAttribute('required', 'required');
        }
    }

    // Если клиент уже выбран
    if (selectedClientId) {
        const clientCard = document.querySelector(`.client-card[data-client-id="${selectedClientId}"]`);
        if (clientCard) {
            clientCard.classList.add('selected');
        }
    }
    
    // Обработчик поиска клиентов
    document.getElementById('clientSearchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const clientCards = document.querySelectorAll('.client-card');
        
        clientCards.forEach(card => {
            const searchData = card.dataset.search;
            if (searchData.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Обработчик выбора клиента
    document.querySelectorAll('.client-card').forEach(card => {
    card.addEventListener('click', function() {
        // Убираем выделение с других карточек
        document.querySelectorAll('.client-card').forEach(c => c.classList.remove('selected'));
        
        // Выделяем текущую карточку
        this.classList.add('selected');
        
        // Сохраняем ID выбранного клиента
        selectedClientId = this.dataset.clientId;
        document.getElementById('selectedUserId').value = selectedClientId;
        
        console.log('Выбран клиент с ID:', selectedClientId); // Для отладки
    });
    updateTotals();
});
});

// Переключение между формами клиента
function toggleClientForm(type) {
    const emailInput = document.getElementById('newUserEmail');
    const isAnonymousInput = document.getElementById('isAnonymous');
    const existingClientForm = document.getElementById('existingClientForm');
    const newClientForm = document.getElementById('newClientForm');
    const anonymousClientForm = document.getElementById('anonymousClientForm');

    // Скрываем все формы
    existingClientForm.classList.remove('active');
    newClientForm.classList.remove('active');
    anonymousClientForm.classList.remove('active');

    if (type === 'existing') {
        existingClientForm.classList.add('active');

        // Убираем required с полей нового клиента
        if (emailInput) {
            emailInput.removeAttribute('required');
        }

        // Сбрасываем флаг анонимности
        if (isAnonymousInput) {
            isAnonymousInput.value = '0';
        }

        // Очищаем поля нового клиента
        document.querySelectorAll('#newClientForm input').forEach(input => input.value = '');
    } else if (type === 'new') {
        newClientForm.classList.add('active');

        // Добавляем required к email
        if (emailInput) {
            emailInput.setAttribute('required', 'required');
        }

        // Сбрасываем флаг анонимности
        if (isAnonymousInput) {
            isAnonymousInput.value = '0';
        }

        // Сбрасываем выбор существующего клиента
        document.querySelectorAll('.client-card').forEach(card => card.classList.remove('selected'));
        document.getElementById('selectedUserId').value = '';
        selectedClientId = null;

        // Инициализируем маску телефона при переключении
        setTimeout(() => {
            initPhoneMask();
        }, 100);
    } else if (type === 'anonymous') {
        anonymousClientForm.classList.add('active');

        // Убираем required с полей нового клиента
        if (emailInput) {
            emailInput.removeAttribute('required');
        }

        // Устанавливаем флаг анонимности
        if (isAnonymousInput) {
            isAnonymousInput.value = '1';
        }

        // Очищаем поля нового клиента
        document.querySelectorAll('#newClientForm input').forEach(input => input.value = '');

        // Сбрасываем выбор существующего клиента
        document.querySelectorAll('.client-card').forEach(card => card.classList.remove('selected'));
        document.getElementById('selectedUserId').value = '';
        selectedClientId = null;
    }
}


// Добавление новой позиции
function addItem() {
    const container = document.getElementById('itemsContainer');
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.dataset.index = itemIndex;

    row.innerHTML = `
        <td>
            <div class="service-display">
                <input type="hidden" name="items[${itemIndex}][service_id]" class="service-id-input" value="">
                <button type="button" class="btn-select-service" onclick="openServiceSelector(${itemIndex})">
                    <i class="fas fa-plus-circle"></i> Выбрать услугу
                </button>
                <div class="selected-service-info" style="display: none;">
                    <div class="service-name-display"></div>
                    <button type="button" class="btn-change-service" onclick="openServiceSelector(${itemIndex})">
                        <i class="fas fa-edit"></i> Изменить
                    </button>
                </div>
            </div>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" value="1" min="1" 
                   class="form-control quantity-input" onchange="updateItemTotal(this)">
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][unit_price]" value="0" min="0" step="0.01" 
                   class="form-control price-input" onchange="updateItemTotal(this)">
        </td>
        <td>
            <div class="item-total" data-total="0">₽0</div>
        </td>
        <td class="item-notes-cell">
            <input type="text" name="items[${itemIndex}][notes]" class="form-control" placeholder="...">
        </td>
        <td>
            <button type="button" class="btn-remove-item" onclick="removeItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    
    container.appendChild(row);
    itemIndex++;
}

// Удаление позиции
function removeItem(button) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        button.closest('tr').remove();
        updateTotals();
    } else {
        alert('Должна остаться хотя бы одна позиция');
    }
}

// Открытие модального окна для добавления услуги
function openServiceModal(index) {
    document.getElementById('serviceModalItemIndex').value = index;
    document.getElementById('serviceModal').style.display = 'block';
    document.getElementById('serviceModalName').focus();
}

// Закрытие модального окна
function closeServiceModal() {
    document.getElementById('serviceModal').style.display = 'none';
    document.getElementById('serviceModalForm').reset();
}

// Обработка формы добавления услуги
document.getElementById('serviceModalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const itemIndex = document.getElementById('serviceModalItemIndex').value;
    const formData = new FormData();
    formData.append('ajax_action', 'create_service');
    formData.append('service_name', document.getElementById('serviceModalName').value);
    formData.append('service_category', document.getElementById('serviceModalCategory').value);
    formData.append('service_price', document.getElementById('serviceModalPrice').value);
    formData.append('service_description', document.getElementById('serviceModalDescription').value);
    
    // Отправляем AJAX запрос
    fetch('order-create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Добавляем новую услугу в массив
            const newService = data.service;
            services.push(newService);
            
            // Добавляем опцию во все селекты
            const option = `<option value="${newService.id}" data-price="${newService.base_price}">${escapeHtml(newService.name)}</option>`;
            document.querySelectorAll('.service-select').forEach(select => {
                select.insertAdjacentHTML('beforeend', option);
            });
            
            // Выбираем новую услугу в текущем селекте
            const row = document.querySelector(`.item-row[data-index="${itemIndex}"]`);
            if (row) {
                const select = row.querySelector('.service-select');
                select.value = newService.id;
                updateServicePrice(select);
            }
            
            closeServiceModal();
        } else {
            alert('Ошибка создания услуги: ' + data.error);
        }
    })
    .catch(error => {
        alert('Ошибка сети');
        console.error(error);
    });
});

// Открыть модальное окно выбора услуги
function openServiceSelector(itemIndex) {
    document.getElementById('serviceSelectorItemIndex').value = itemIndex;
    document.getElementById('serviceSelectorModal').style.display = 'block';
    document.getElementById('serviceSearchInput').value = '';
    filterServices();
}

// Закрыть модальное окно выбора услуги
function closeServiceSelectorModal() {
    document.getElementById('serviceSelectorModal').style.display = 'none';
}

// Закрыть модальное окно по клику на overlay
document.addEventListener('click', function(e) {
    const modal = document.getElementById('serviceSelectorModal');
    if (e.target === modal) {
        closeServiceSelectorModal();
    }
});

// Закрыть модальное окно по нажатию Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('serviceSelectorModal');
        if (modal.style.display === 'block') {
            closeServiceSelectorModal();
        }
    }
});

// Поиск услуг
function filterServices() {
    const searchTerm = document.getElementById('serviceSearchInput').value.toLowerCase();
    const serviceCards = document.querySelectorAll('.service-card');
    let visibleCount = 0;

    serviceCards.forEach(card => {
        const searchData = card.dataset.search;
        if (searchData.includes(searchTerm)) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Показываем сообщение "ничего не найдено"
    const noServicesFound = document.getElementById('noServicesFound');
    const servicesGrid = document.getElementById('servicesGrid');
    if (visibleCount === 0) {
        servicesGrid.style.display = 'none';
        noServicesFound.style.display = 'block';
    } else {
        servicesGrid.style.display = 'grid';
        noServicesFound.style.display = 'none';
    }
}

// Выбрать услугу
function selectService(serviceId) {
    console.log('=== selectService CALLED ===');
    console.log('serviceId received:', serviceId, 'type:', typeof serviceId);

    const itemIndex = document.getElementById('serviceSelectorItemIndex').value;
    console.log('itemIndex:', itemIndex);

    const row = document.querySelector(`.item-row[data-index="${itemIndex}"]`);
    console.log('row found:', !!row);

    const serviceCard = document.querySelector(`.service-card[data-service-id="${serviceId}"]`);
    console.log('serviceCard found:', !!serviceCard);

    if (serviceCard) {
        console.log('serviceCard data-service-id:', serviceCard.dataset.serviceId);
        console.log('serviceCard data-service-name:', serviceCard.dataset.serviceName);
        console.log('serviceCard data-base-price:', serviceCard.dataset.basePrice);
        console.log('serviceCard actual element:', serviceCard);
    }

    if (!row || !serviceCard) {
        console.log('ERROR: row or serviceCard not found!');
        return;
    }

    const serviceName = serviceCard.dataset.serviceName;
    const basePrice = parseFloat(serviceCard.dataset.basePrice || 0);

    console.log('serviceName to display:', serviceName);
    console.log('basePrice to use:', basePrice);

    // Обновляем скрытое поле
    const serviceIdInput = row.querySelector('.service-id-input');
    if (serviceIdInput) {
        serviceIdInput.value = serviceId;
        console.log('Set service-id-input to:', serviceId);
    }

    // Показываем выбранную услугу
    const selectButton = row.querySelector('.btn-select-service');
    const selectedInfo = row.querySelector('.selected-service-info');
    const serviceNameDisplay = row.querySelector('.service-name-display');

    if (selectButton && selectedInfo && serviceNameDisplay) {
        selectButton.style.display = 'none';
        selectedInfo.style.display = 'flex';
        serviceNameDisplay.textContent = serviceName;
        console.log('Updated UI with service name:', serviceName);
    }

    // Закрываем модальное окно
    closeServiceSelectorModal();

    // Загружаем параметры услуги и обновляем калькулятор
    console.log('Calling loadServiceParams with:', itemIndex, serviceId, basePrice);
    loadServiceParams(itemIndex, serviceId, basePrice);
}

// Загрузить параметры услуги
function loadServiceParams(itemIndex, serviceId, basePrice) {
    const row = document.querySelector(`.item-row[data-index="${itemIndex}"]`);
    const priceInput = row.querySelector('.price-input');
    const notesCell = row.querySelector('.item-notes-cell');

    // Загружаем параметры услуги
    fetch('/admin/api/get-service-params.php?service_id=' + serviceId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.params) {
                const params = data.params;

                // Создаем HTML для калькулятора параметров
                let calculatorHtml = '<div class="service-calculator" data-index="' + itemIndex + '" data-base-price="' + params.base_price + '" data-base-quantity="' + params.base_quantity + '">';

                // Размеры
                if (params.sizes && params.sizes.length > 0) {
                    calculatorHtml += '<select class="form-control calc-size" data-param="size" onchange="calculateServicePrice(' + itemIndex + ')">';
                    calculatorHtml += '<option value="">Размер</option>';
                    params.sizes.forEach(size => {
                        calculatorHtml += '<option value="' + size.id + '" data-price="' + size.price + '">' + escapeHtml(size.label) + '</option>';
                    });
                    calculatorHtml += '</select>';
                }

                // Плотность
                if (params.densities && params.densities.length > 0) {
                    calculatorHtml += '<select class="form-control calc-density" data-param="density" onchange="calculateServicePrice(' + itemIndex + ')">';
                    calculatorHtml += '<option value="">Плотность</option>';
                    params.densities.forEach(density => {
                        calculatorHtml += '<option value="' + density.id + '" data-price="' + density.price + '">' + escapeHtml(density.label) + '</option>';
                    });
                    calculatorHtml += '</select>';
                }

                // Стороны печати
                if (params.sides && params.sides.length > 0) {
                    calculatorHtml += '<select class="form-control calc-sides" data-param="sides" onchange="calculateServicePrice(' + itemIndex + ')">';
                    calculatorHtml += '<option value="">Печать</option>';
                    params.sides.forEach(side => {
                        calculatorHtml += '<option value="' + side.id + '" data-multiplier="' + side.multiplier + '">' + escapeHtml(side.label) + '</option>';
                    });
                    calculatorHtml += '</select>';
                }

                // Количество
                if (params.quantities && params.quantities.length > 0) {
                    calculatorHtml += '<select class="form-control calc-quantity" data-param="quantity" onchange="calculateServicePrice(' + itemIndex + ')">';
                    calculatorHtml += '<option value="">Тираж</option>';
                    params.quantities.forEach(qty => {
                        calculatorHtml += '<option value="' + qty.id + '" data-quantity="' + qty.quantity + '" data-multiplier="' + (qty.multiplier || 1) + '" data-price="' + (qty.price || 0) + '">' + escapeHtml(qty.label) + '</option>';
                    });
                    calculatorHtml += '<option value="custom">Свой тираж...</option>';
                    calculatorHtml += '</select>';
                    calculatorHtml += '<input type="number" class="form-control calc-custom-quantity" placeholder="Кол-во" style="display:none; margin-top:0.5rem;" onchange="calculateServicePrice(' + itemIndex + ')">';
                }

                calculatorHtml += '</div>';

                // Вставляем калькулятор
                notesCell.innerHTML = calculatorHtml;

                // Устанавливаем начальную цену
                priceInput.value = params.base_price || basePrice;
                updateItemTotal(priceInput);
            } else {
                // Нет параметров - используем базовую цену
                priceInput.value = basePrice;
                notesCell.innerHTML = '<input type="text" name="items[' + itemIndex + '][notes]" class="form-control" placeholder="...">';
                updateItemTotal(priceInput);
            }
        })
        .catch(error => {
            console.error('Error loading service params:', error);
            priceInput.value = basePrice;
            updateItemTotal(priceInput);
        });
}

// Рассчитать цену услуги с параметрами
function calculateServicePrice(index) {
    const row = document.querySelector(`.item-row[data-index="${index}"]`);
    const calculator = row.querySelector('.service-calculator');
    const priceInput = row.querySelector('.price-input');
    const quantityInput = row.querySelector('.quantity-input');

    if (!calculator) return;

    let basePrice = parseFloat(calculator.dataset.basePrice || 0);
    let baseQuantity = parseFloat(calculator.dataset.baseQuantity || 1);
    let total = basePrice;

    // Размер
    const sizeSelect = calculator.querySelector('.calc-size');
    if (sizeSelect && sizeSelect.value) {
        const option = sizeSelect.options[sizeSelect.selectedIndex];
        total += parseFloat(option.dataset.price || 0);
    }

    // Плотность
    const densitySelect = calculator.querySelector('.calc-density');
    if (densitySelect && densitySelect.value) {
        const option = densitySelect.options[densitySelect.selectedIndex];
        total += parseFloat(option.dataset.price || 0);
    }

    // Стороны (множитель)
    let sidesMultiplier = 1;
    const sidesSelect = calculator.querySelector('.calc-sides');
    if (sidesSelect && sidesSelect.value) {
        const option = sidesSelect.options[sidesSelect.selectedIndex];
        sidesMultiplier = parseFloat(option.dataset.multiplier || 1);
    }

    total *= sidesMultiplier;

    // Количество
    const quantitySelect = calculator.querySelector('.calc-quantity');
    const customQuantityInput = calculator.querySelector('.calc-custom-quantity');

    if (quantitySelect && quantitySelect.value) {
        if (quantitySelect.value === 'custom') {
            // Показываем поле для ввода
            customQuantityInput.style.display = 'block';
            const customQty = parseFloat(customQuantityInput.value || 0);
            if (customQty > 0) {
                // Для пользовательского тиража используем множитель 1.0
                total = total * (customQty / baseQuantity) * 1.0;
                quantityInput.value = customQty;
            }
        } else {
            // Скрываем поле для ввода
            customQuantityInput.style.display = 'none';

            const option = quantitySelect.options[quantitySelect.selectedIndex];
            const qtyCount = parseFloat(option.dataset.quantity || 1);
            const qtyMultiplier = parseFloat(option.dataset.multiplier || 1);
            const qtyPrice = parseFloat(option.dataset.price || 0);

            // Формула как в калькуляторе на сайте
            total = (total + qtyPrice) * (qtyCount / baseQuantity) * qtyMultiplier;
            quantityInput.value = qtyCount;
        }
    }

    // Обновляем цену
    priceInput.value = Math.max(0, total).toFixed(2);
    updateItemTotal(priceInput);
}

// Обновление суммы позиции
function updateItemTotal(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = quantity * price;
    
    const totalDiv = row.querySelector('.item-total');
    totalDiv.dataset.total = total;
    totalDiv.textContent = '₽' + formatNumber(total);
    
    updateTotals();
}

// Обновление общей суммы
function updateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-total').forEach(div => {
        subtotal += parseFloat(div.dataset.total) || 0;
    });
    
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const finalAmount = subtotal - discount;
    
    document.getElementById('subtotalAmount').textContent = '₽' + formatNumber(subtotal);
    document.getElementById('subtotalInput').value = subtotal;
    
    document.getElementById('finalAmount').textContent = '₽' + formatNumber(finalAmount);
    document.getElementById('finalAmountInput').value = finalAmount;
}

// Форматирование чисел
function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

// Экранирование HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Валидация формы
document.getElementById('orderForm').addEventListener('submit', function(e) {
    // Проверка клиента
    const clientType = document.querySelector('input[name="client_type"]:checked').value;

    if (clientType === 'existing') {
        const userId = document.getElementById('selectedUserId').value;
        if (!userId) {
            e.preventDefault();
            alert('Выберите клиента из списка');
            return false;
        }
    } else if (clientType === 'new') {
        // Валидация только для нового клиента с реальными данными
        const email = document.querySelector('input[name="new_user_email"]').value.trim();

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            e.preventDefault();
            alert('Введите корректный email адрес');
            document.getElementById('newUserEmail').focus();
            return false;
        }

        // Проверка телефона только если он заполнен
        const phone = document.querySelector('input[name="new_user_phone"]').value;
        if (phone) {
            const phoneDigits = phone.replace(/\D/g, '');
            if (phoneDigits.length !== 11) {
                e.preventDefault();
                alert('Введите корректный номер телефона или оставьте поле пустым');
                document.getElementById('newUserPhone').focus();
                return false;
            }
        }
    }
    // Для anonymous не проверяем ничего, виртуальный клиент создастся автоматически

    // Проверка товаров
    let hasValidItems = false;
    document.querySelectorAll('.item-row').forEach(row => {
        const serviceIdInput = row.querySelector('.service-id-input');
        const quantity = row.querySelector('.quantity-input').value;
        const serviceId = serviceIdInput ? serviceIdInput.value : '';

        if (serviceId && quantity > 0) {
            hasValidItems = true;
        }
    });

    if (!hasValidItems) {
        e.preventDefault();
        alert('Добавьте хотя бы одну услугу в заказ');
        return false;
    }
});

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    if (event.target == document.getElementById('serviceModal')) {
        closeServiceModal();
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>