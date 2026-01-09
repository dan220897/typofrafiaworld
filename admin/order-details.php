<?php
// admin/order-details.php - Просмотр деталей заказа
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'classes/User.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('view_orders');

// Получаем ID заказа
$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$user = new User($db);
$adminLog = new AdminLog($db);

// Получаем данные заказа
$orderData = $order->getOrderById($order_id);
if (!$orderData) {
    $_SESSION['error'] = 'Заказ не найден';
    header('Location: orders.php');
    exit;
}

// Получаем элементы заказа
$orderItems = $order->getOrderItems($order_id);

// Получаем данные пользователя
$userData = $user->getUserById($orderData['user_id']);

// Получаем историю изменений заказа
$orderHistory = $order->getOrderHistory($order_id);

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!Admin::hasPermission('edit_orders')) {
        $_SESSION['error'] = 'Недостаточно прав для выполнения действия';
        header('Location: order-details.php?id=' . $order_id);
        exit;
    }
    
    try {
        switch ($_POST['action']) {
            case 'update_status':
                $new_status = $_POST['status'] ?? '';
                if ($order->updateOrderStatus($order_id, $new_status, $_SESSION['admin_id'])) {
                    $adminLog->log($_SESSION['admin_id'], 'update_order_status', 
                        "Изменен статус заказа #{$orderData['order_number']} на {$new_status}", 
                        'order', $order_id);
                    $_SESSION['success'] = 'Статус заказа обновлен';
                }
                break;
                
            case 'update_payment':
                $payment_status = $_POST['payment_status'] ?? '';
                $manual = isset($_POST['manual']) && $_POST['manual'] == '1';
                if ($order->updatePaymentStatus($order_id, $payment_status, $manual)) {
                    $adminLog->log($_SESSION['admin_id'], 'update_payment_status', 
                        "Изменен статус оплаты заказа #{$orderData['order_number']} на {$payment_status}", 
                        'order', $order_id);
                    $_SESSION['success'] = 'Статус оплаты обновлен';
                }
                break;
                
            case 'generate_payment_link':
                try {
                    $paymentUrl = $order->generatePaymentLink($order_id);
                    $adminLog->log($_SESSION['admin_id'], 'generate_payment_link', 
                        "Создана платежная ссылка для заказа #{$orderData['order_number']}", 
                        'order', $order_id);
                    $_SESSION['success'] = 'Платежная ссылка успешно создана';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Ошибка создания платежной ссылки: ' . $e->getMessage();
                }
                break;
                
            case 'send_payment_sms':
                $result = $order->sendPaymentLinkSMS($order_id);
                if ($result['success']) {
                    $adminLog->log($_SESSION['admin_id'], 'send_payment_sms', 
                        "Отправлено SMS с платежной ссылкой для заказа #{$orderData['order_number']}", 
                        'order', $order_id);
                    $_SESSION['success'] = 'SMS с платежной ссылкой успешно отправлено';
                } else {
                    $_SESSION['error'] = 'Ошибка отправки SMS: ' . $result['error'];
                }
                break;
                
            case 'check_payment_status':
                $result = $order->checkTinkoffPaymentStatus($order_id);
                if ($result['success']) {
                    $adminLog->log($_SESSION['admin_id'], 'check_payment_status', 
                        "Проверен статус платежа для заказа #{$orderData['order_number']}: {$result['status']}", 
                        'order', $order_id);
                    $_SESSION['success'] = 'Статус платежа обновлен: ' . $order->getPaymentStatusText($result['status']);
                } else {
                    $_SESSION['error'] = 'Ошибка проверки статуса: ' . $result['error'];
                }
                break;
                
            case 'add_note':
                $note = trim($_POST['note'] ?? '');
                if (!empty($note)) {
                    if ($order->addManagerNote($order_id, $note, $_SESSION['admin_id'])) {
                        $_SESSION['success'] = 'Заметка добавлена';
                    }
                }
                break;
        }
        
        header('Location: order-details.php?id=' . $order_id);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Определяем статусы
$orderStatuses = [
    'draft' => 'Черновик',
    'pending' => 'Ожидает подтверждения',
    'confirmed' => 'Подтвержден',
    'in_production' => 'В производстве',
    'ready' => 'Готов',
    'delivered' => 'Доставлен',
    'cancelled' => 'Отменен'
];

$paymentStatuses = [
    'pending' => 'Ожидает оплаты',
    'paid' => 'Оплачен',
    'partially_paid' => 'Частично оплачен',
    'refunded' => 'Возврат',
    'failed' => 'Ошибка оплаты'
];

// Заголовок страницы
$page_title = 'Заказ #' . $orderData['order_number'];
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
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.order-badges {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge.draft { background: #e5e7eb; color: #374151; }
.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.confirmed { background: #dbeafe; color: #1e40af; }
.status-badge.in_production { background: #e0e7ff; color: #3730a3; }
.status-badge.ready { background: #d1fae5; color: #065f46; }
.status-badge.delivered { background: #34d399; color: white; }
.status-badge.cancelled { background: #fee2e2; color: #991b1b; }

.payment-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.payment-badge.pending { background: #fef3c7; color: #92400e; }
.payment-badge.paid { background: #d1fae5; color: #065f46; }
.payment-badge.partially_paid { background: #e0e7ff; color: #3730a3; }
.payment-badge.refunded { background: #fee2e2; color: #991b1b; }
.payment-badge.failed { background: #ef4444; color: white; }

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

.btn-warning {
    background-color: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background-color: #d97706;
}

.btn-purple {
    background-color: #8b5cf6;
    color: white;
}

.btn-purple:hover {
    background-color: #7c3aed;
}

/* Лейаут */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
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

/* Блок платежной информации */
.payment-info-block {
    background-color: #f3f4f6;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.payment-link-container {
    background-color: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 6px;
    padding: 1rem;
    margin: 1rem 0;
}

.payment-link-label {
    font-size: 0.875rem;
    color: #1e40af;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.payment-link-wrapper {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.payment-link-input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.875rem;
    background-color: white;
    color: #374151;
}

.btn-copy {
    padding: 0.5rem 0.75rem;
    background-color: #374151;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background-color 0.2s;
}

.btn-copy:hover {
    background-color: #1f2937;
}

.payment-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
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
    border-bottom: 1px solid #e5e7eb;
}

.items-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}

.items-table tr:last-child td {
    border-bottom: none;
}

.item-name {
    font-weight: 500;
    color: #1f2937;
}

.item-params {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.price {
    font-weight: 500;
    color: #1f2937;
    white-space: nowrap;
}

/* Информационные блоки */
.info-block {
    margin-bottom: 1.5rem;
}

.info-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1rem;
    color: #1f2937;
    font-weight: 500;
}

/* Форма статуса */
.status-form {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.form-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    background: white;
}

/* История */
.history-item {
    display: flex;
    align-items: start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.history-item:last-child {
    border-bottom: none;
}

.history-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 0.875rem;
}

.history-content {
    flex: 1;
}

.history-action {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.history-details {
    font-size: 0.875rem;
    color: #6b7280;
}

.history-time {
    font-size: 0.75rem;
    color: #9ca3af;
}

/* Итоги */
.totals {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
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
}

.total-row.final {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

/* Заметки */
.notes-form {
    margin-top: 1rem;
}

.form-textarea {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    resize: vertical;
    min-height: 100px;
}

.note-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.note-author {
    font-weight: 500;
    color: #1f2937;
}

.note-time {
    font-size: 0.75rem;
    color: #9ca3af;
}

.note-text {
    font-size: 0.875rem;
    color: #4b5563;
}

/* Алерты */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Тост уведомление */
.toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background-color: #1f2937;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 6px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: none;
    z-index: 1000;
}

.toast.show {
    display: block;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Адаптив */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-badges {
        width: 100%;
    }
    
    .payment-actions {
        flex-direction: column;
    }
    
    .payment-actions .btn {
        width: 100%;
        justify-content: center;
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
        <span>#<?php echo $orderData['order_number']; ?></span>
    </div>
    
    <!-- Заголовок с бейджами -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Заказ #<?php echo $orderData['order_number']; ?></h1>
            <div class="order-badges" style="margin-top: 0.5rem;">
                <span class="status-badge <?php echo $orderData['status']; ?>">
                    <?php echo $orderStatuses[$orderData['status']] ?? $orderData['status']; ?>
                </span>
                <span class="payment-badge <?php echo $orderData['payment_status']; ?>">
                    <?php echo $paymentStatuses[$orderData['payment_status']] ?? $orderData['payment_status']; ?>
                    <?php if ($orderData['payment_status_manual'] ?? false): ?>
                        <span style="font-size: 0.75rem; opacity: 0.8;">(вручную)</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div>
            <a href="order-print.php?id=<?php echo $order_id; ?>" target="_blank" class="btn btn-secondary">
                <i class="fas fa-print"></i> Печать
            </a>
            <a href="order-edit.php?id=<?php echo $order_id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Редактировать
            </a>
        </div>
    </div>
    
    <!-- Алерты -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>
    
    <div class="content-grid">
        <!-- Основной контент -->
        <div>
            <!-- Товары и услуги -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Товары и услуги</h2>
                </div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Наименование</th>
                            <th>Количество</th>
                            <th>Цена</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="item-name"><?php echo htmlspecialchars($item['service_name']); ?></div>
                                <?php if (!empty($item['parameters'])): ?>
                                    <?php $params = json_decode($item['parameters'], true); ?>
                                    <?php if ($params): ?>
                                        <div class="item-params">
                                            <?php foreach ($params as $key => $value): ?>
                                                <?php echo htmlspecialchars($key . ': ' . $value); ?><br>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($item['notes']): ?>
                                    <div class="item-params">
                                        <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($item['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['quantity']; ?> шт.</td>
                            <td class="price">₽<?php echo number_format($item['unit_price'], 0, '', ' '); ?></td>
                            <td class="price">₽<?php echo number_format($item['total_price'], 0, '', ' '); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="totals">
                    <div class="total-row">
                        <span class="total-label">Сумма:</span>
                        <span class="total-value">₽<?php echo number_format($orderData['total_amount'] ?? 0, 0, '', ' '); ?></span>
                    </div>
                    <?php if ($orderData['discount_amount'] > 0): ?>
                    <div class="total-row">
                        <span class="total-label">Скидка:</span>
                        <span class="total-value" style="color: #ef4444;">-₽<?php echo number_format($orderData['discount_amount'], 0, '', ' '); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="total-row final">
                        <span>Итого к оплате:</span>
                        <span>₽<?php echo number_format($orderData['final_amount'], 0, '', ' '); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Платежная информация Тинькофф -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Платежная информация</h2>
                </div>
                
                <div class="payment-info-block">
                    <div class="info-block">
                        <div class="info-label">Статус оплаты</div>
                        <div class="info-value">
                            <span class="payment-badge <?php echo $orderData['payment_status']; ?>">
                                <?php echo $paymentStatuses[$orderData['payment_status']] ?? $orderData['payment_status']; ?>
                            </span>
                            <?php if ($orderData['payment_status_manual'] ?? false): ?>
                                <span style="font-size: 0.75rem; color: #6b7280; margin-left: 0.5rem;">(установлен вручную)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($orderData['tinkoff_payment_url'])): ?>
                        <div class="payment-link-container">
                            <div class="payment-link-label">Ссылка для оплаты Тинькофф</div>
                            <div class="payment-link-wrapper">
                                <input type="text" 
                                       id="paymentLink" 
                                       class="payment-link-input" 
                                       value="<?php echo htmlspecialchars($orderData['tinkoff_payment_url']); ?>" 
                                       readonly>
                                <button class="btn-copy" onclick="copyPaymentLink()">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="payment-actions">
                        <?php if (empty($orderData['tinkoff_payment_url'])): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="generate_payment_link">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-link"></i> Создать ссылку на оплату
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="send_payment_sms">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sms"></i> Отправить SMS
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="check_payment_status">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-sync-alt"></i> Проверить статус
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="generate_payment_link">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-redo"></i> Пересоздать ссылку
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Заметки менеджера -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Заметки</h2>
                </div>
                
                <?php if (!empty($orderData['manager_notes'])): ?>
                    <?php $notes = json_decode($orderData['manager_notes'], true) ?: []; ?>
                    <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <div class="note-header">
                            <span class="note-author"><?php echo htmlspecialchars($note['author'] ?? 'Администратор'); ?></span>
                            <span class="note-time"><?php echo date('d.m.Y H:i', strtotime($note['created_at'] ?? 'now')); ?></span>
                        </div>
                        <div class="note-text"><?php echo nl2br(htmlspecialchars($note['text'] ?? '')); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (Admin::hasPermission('edit_orders')): ?>
                <form method="POST" class="notes-form">
                    <input type="hidden" name="action" value="add_note">
                    <textarea name="note" class="form-textarea" placeholder="Добавить заметку..."></textarea>
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.75rem;">
                        <i class="fas fa-plus"></i> Добавить заметку
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- История заказа -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">История изменений</h2>
                </div>
                
                <div class="history-timeline">
                    <?php foreach ($orderHistory as $event): ?>
                    <div class="history-item">
                        <div class="history-icon">
                            <i class="fas fa-<?php echo getHistoryIcon($event['event_type'] ?? $event['action'] ?? ''); ?>"></i>
                        </div>
                        <div class="history-content">
                            <div class="history-action"><?php echo $event['description'] ?? $event['event_description'] ?? ''; ?></div>
                            <div class="history-details">
                                <?php echo htmlspecialchars($event['changed_by_name'] ?? $event['admin_name'] ?? 'Система'); ?>
                                <span class="history-time">• <?php echo date('d.m.Y H:i', strtotime($event['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Боковая панель -->
        <div>
            <!-- Информация о клиенте -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Клиент</h2>
                    <a href="user_edit.php?id=<?php echo $userData['id']; ?>" style="font-size: 0.875rem;">
                        Подробнее
                    </a>
                </div>
                
                <div class="info-block">
                    <div class="info-label">Имя</div>
                    <div class="info-value"><?php echo htmlspecialchars($userData['name'] ?: 'Не указано'); ?></div>
                </div>
                
                <div class="info-block">
                    <div class="info-label">Телефон</div>
                    <div class="info-value"><?php echo htmlspecialchars($userData['phone']); ?></div>
                </div>
                
                <?php if ($userData['email']): ?>
                <div class="info-block">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($userData['email']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($userData['company_name']): ?>
                <div class="info-block">
                    <div class="info-label">Компания</div>
                    <div class="info-value"><?php echo htmlspecialchars($userData['company_name']); ?></div>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 1rem;">
                    <a href="chats.php?user=<?php echo $userData['id']; ?>" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-comments"></i> Открыть чат
                    </a>
                </div>
            </div>
            
            <!-- Информация о доставке -->
            <?php if (!empty($orderData['delivery_address']) || !empty($orderData['delivery_type'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Доставка</h2>
                </div>
                
                <?php if (!empty($orderData['delivery_type'])): ?>
                <div class="info-block">
                    <div class="info-label">Способ доставки</div>
                    <div class="info-value"><?php echo htmlspecialchars($orderData['delivery_type']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($orderData['delivery_address'])): ?>
                <div class="info-block">
                    <div class="info-label">Адрес доставки</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($orderData['delivery_address'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Статусы -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Управление статусами</h2>
                </div>
                
                <?php if (Admin::hasPermission('edit_orders')): ?>
                <!-- Статус заказа -->
                <form method="POST" class="status-form" style="margin-bottom: 1rem;">
                    <input type="hidden" name="action" value="update_status">
                    <select name="status" class="form-select">
                        <?php foreach ($orderStatuses as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $orderData['status'] === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Обновить</button>
                </form>
                
                <!-- Статус оплаты -->
                <form method="POST" class="status-form">
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="manual" value="1">
                    <select name="payment_status" class="form-select">
                        <?php foreach ($paymentStatuses as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $orderData['payment_status'] === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-success">Обновить</button>
                </form>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #6b7280;">
                    <i class="fas fa-info-circle"></i> Ручное изменение статуса оплаты
                </div>
                <?php else: ?>
                <p style="color: #6b7280; font-size: 0.875rem;">У вас нет прав для изменения статусов</p>
                <?php endif; ?>
            </div>
            
            <!-- Даты -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Даты</h2>
                </div>
                
                <div class="info-block">
                    <div class="info-label">Создан</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($orderData['created_at'])); ?></div>
                </div>
                
                <?php if ($orderData['confirmed_at']): ?>
                <div class="info-block">
                    <div class="info-label">Подтвержден</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($orderData['confirmed_at'])); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($orderData['deadline_at']): ?>
                <div class="info-block">
                    <div class="info-label">Срок выполнения</div>
                    <div class="info-value" style="color: <?php echo strtotime($orderData['deadline_at']) < time() ? '#ef4444' : '#1f2937'; ?>">
                        <?php echo date('d.m.Y', strtotime($orderData['deadline_at'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($orderData['completed_at']): ?>
                <div class="info-block">
                    <div class="info-label">Завершен</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($orderData['completed_at'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Тост для уведомлений -->
<div id="toast" class="toast"></div>

<script>
function copyPaymentLink() {
    const input = document.getElementById('paymentLink');
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        showToast('Ссылка скопирована в буфер обмена');
    } catch (err) {
        console.error('Ошибка копирования:', err);
        showToast('Ошибка при копировании ссылки', 'error');
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    
    if (type === 'error') {
        toast.style.backgroundColor = '#ef4444';
    } else {
        toast.style.backgroundColor = '#1f2937';
    }
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}
</script>

<?php
require_once 'includes/footer.php';

// Вспомогательная функция для иконок истории
function getHistoryIcon($action) {
    $icons = [
        'created' => 'plus-circle',
        'status_changed' => 'exchange-alt',
        'status_change' => 'exchange-alt',
        'payment_updated' => 'dollar-sign',
        'note_added' => 'comment',
        'edited' => 'edit',
        'printed' => 'print',
        'generate_payment_link' => 'link',
        'send_payment_sms' => 'sms',
        'check_payment_status' => 'sync-alt',
        'update_payment_status' => 'dollar-sign'
    ];
    return $icons[$action] ?? 'info-circle';
}
?>