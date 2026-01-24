<?php
// admin/order-details.php - Детали заказа в админке
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Order.php';

// Проверяем авторизацию администратора
checkAdminAuth('view_orders');

// Проверяем наличие ID заказа
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$orderId) {
    header('Location: orders.php');
    exit;
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Получаем данные заказа
$orderData = $order->getOrderById($orderId);
if (!$orderData) {
    $_SESSION['error'] = 'Заказ не найден';
    header('Location: orders.php');
    exit;
}

// Получаем элементы заказа
$orderItems = $order->getOrderItems($orderId);

// Получаем данные клиента
require_once 'classes/User.php';
$userClass = new User($db);
$userData = $userClass->getUserById($orderData['user_id']);

// Заголовок страницы
$page_title = 'Детали заказа #' . ($orderData['order_number'] ?? $orderId);
require_once 'includes/header.php';
?>

<div class="order-details-page">
    <div class="page-header">
        <h1>Заказ #<?php echo htmlspecialchars($orderData['order_number'] ?? $orderId); ?></h1>
        <div class="header-actions">
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад к заказам
            </a>
            <a href="order-edit.php?id=<?php echo $orderId; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Редактировать
            </a>
            <button onclick="printOrder()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Печать
            </button>
        </div>
    </div>

    <div class="order-info-grid">
        <!-- Основная информация -->
        <div class="card">
            <div class="card-header">
                <h2>Информация о заказе</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Номер заказа:</span>
                    <span class="info-value">#<?php echo htmlspecialchars($orderData['order_number'] ?? $orderId); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Дата создания:</span>
                    <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($orderData['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Статус:</span>
                    <span class="info-value">
                        <span class="badge badge-<?php echo $orderData['status']; ?>">
                            <?php echo ORDER_STATUSES[$orderData['status']] ?? $orderData['status']; ?>
                        </span>
                    </span>
                </div>
                <?php if (!empty($orderData['deadline_at'])): ?>
                <div class="info-row">
                    <span class="info-label">Срок выполнения:</span>
                    <span class="info-value"><?php echo date('d.m.Y', strtotime($orderData['deadline_at'])); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($orderData['delivery_method'])): ?>
                <div class="info-row">
                    <span class="info-label">Способ доставки:</span>
                    <span class="info-value">
                        <?php
                        $deliveryMethods = [
                            'pickup' => 'Самовывоз',
                            'delivery' => 'Доставка',
                            'courier' => 'Курьер'
                        ];
                        echo $deliveryMethods[$orderData['delivery_method']] ?? $orderData['delivery_method'];
                        ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($orderData['delivery_address'])): ?>
                <div class="info-row">
                    <span class="info-label">Адрес доставки:</span>
                    <span class="info-value"><?php echo nl2br(htmlspecialchars($orderData['delivery_address'])); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($orderData['notes'])): ?>
                <div class="info-row">
                    <span class="info-label">Примечания:</span>
                    <span class="info-value"><?php echo nl2br(htmlspecialchars($orderData['notes'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Информация о клиенте -->
        <?php if ($userData): ?>
        <div class="card">
            <div class="card-header">
                <h2>Информация о клиенте</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Имя:</span>
                    <span class="info-value"><?php echo htmlspecialchars($userData['name'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">
                        <a href="mailto:<?php echo htmlspecialchars($userData['email']); ?>">
                            <?php echo htmlspecialchars($userData['email']); ?>
                        </a>
                    </span>
                </div>
                <?php if (!empty($userData['phone'])): ?>
                <div class="info-row">
                    <span class="info-label">Телефон:</span>
                    <span class="info-value">
                        <a href="tel:<?php echo htmlspecialchars($userData['phone']); ?>">
                            <?php echo htmlspecialchars($userData['phone']); ?>
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Оплата -->
        <div class="card">
            <div class="card-header">
                <h2>Оплата</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Статус оплаты:</span>
                    <span class="info-value">
                        <select id="paymentStatusSelect" class="form-control" onchange="updatePaymentStatus()" style="width: auto; display: inline-block;">
                            <option value="pending" <?php echo $orderData['payment_status'] === 'pending' ? 'selected' : ''; ?>>Ожидает оплаты</option>
                            <option value="paid" <?php echo $orderData['payment_status'] === 'paid' ? 'selected' : ''; ?>>Оплачен</option>
                            <option value="partially_paid" <?php echo $orderData['payment_status'] === 'partially_paid' ? 'selected' : ''; ?>>Частично оплачен</option>
                            <option value="refunded" <?php echo $orderData['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Возврат</option>
                            <option value="failed" <?php echo $orderData['payment_status'] === 'failed' ? 'selected' : ''; ?>>Ошибка оплаты</option>
                        </select>
                    </span>
                </div>
                <?php if (!empty($orderData['tinkoff_payment_url'])): ?>
                <div class="info-row">
                    <span class="info-label">Ссылка на оплату:</span>
                    <span class="info-value">
                        <a href="<?php echo htmlspecialchars($orderData['tinkoff_payment_url']); ?>" target="_blank" class="btn btn-sm btn-success">
                            <i class="fas fa-external-link-alt"></i> Открыть ссылку
                        </a>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Отправка ссылки:</span>
                    <span class="info-value">
                        <button onclick="sendPaymentEmail()" class="btn btn-sm btn-info">
                            <i class="fas fa-envelope"></i> Отправить на email
                        </button>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Позиции заказа -->
        <div class="card">
            <div class="card-header">
                <h2>Позиции заказа</h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Услуга</th>
                            <th>Количество</th>
                            <th>Цена</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orderItems)): ?>
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['service_name'] ?? 'Услуга'); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['unit_price'], 0, '', ' '); ?> ₽</td>
                                <td><?php echo number_format($item['total_price'], 0, '', ' '); ?> ₽</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Нет позиций</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Итого:</strong></td>
                            <td><strong><?php echo number_format($orderData['total_amount'], 0, '', ' '); ?> ₽</strong></td>
                        </tr>
                        <?php if ($orderData['discount_amount'] > 0): ?>
                        <tr>
                            <td colspan="3" class="text-right">Скидка:</td>
                            <td><?php echo number_format($orderData['discount_amount'], 0, '', ' '); ?> ₽</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>К оплате:</strong></td>
                            <td><strong><?php echo number_format($orderData['final_amount'], 0, '', ' '); ?> ₽</strong></td>
                        </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function printOrder() {
    window.open('order-print.php?id=<?php echo $orderId; ?>', '_blank');
}

// Обновление статуса оплаты
async function updatePaymentStatus() {
    const newStatus = document.getElementById('paymentStatusSelect').value;
    const orderId = <?php echo $orderId; ?>;

    if (!confirm('Изменить статус оплаты заказа?')) {
        location.reload();
        return;
    }

    try {
        const response = await fetch(`api/orders.php?id=${orderId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'update_payment',
                payment_status: newStatus
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Статус оплаты обновлен', 'success');
        } else {
            showNotification(data.message || 'Ошибка обновления статуса', 'error');
            location.reload();
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка соединения', 'error');
        location.reload();
    }
}

// Отправка email с ссылкой на оплату
async function sendPaymentEmail() {
    const orderId = <?php echo $orderId; ?>;

    if (!confirm('Отправить email с ссылкой на оплату клиенту?')) {
        return;
    }

    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';

    try {
        const response = await fetch(`api/orders.php?id=${orderId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'send_payment_email'
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Email успешно отправлен', 'success');
        } else {
            // Показываем конкретную ошибку из ответа сервера
            console.error('Ошибка сервера:', data);
            showNotification(data.message || data.error || 'Ошибка отправки email', 'error');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка соединения: ' + error.message, 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = originalHTML;
    }
}

// Уведомления
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Добавляем стили анимации
const style = document.createElement('style');
style.textContent = `
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
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>

<style>
.order-details-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
    color: #1f2937;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 16px 20px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.card-body {
    padding: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #6b7280;
    flex-shrink: 0;
    margin-right: 20px;
}

.info-value {
    color: #1f2937;
    text-align: right;
    flex-grow: 1;
}

.info-value a {
    color: #3b82f6;
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

.badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-draft { background: #e0e7ff; color: #3730a3; }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-confirmed { background: #dbeafe; color: #1e40af; }
.badge-in_production { background: #fef3c7; color: #92400e; }
.badge-ready { background: #d1fae5; color: #065f46; }
.badge-delivered { background: #dcfce7; color: #166534; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }

.badge-payment-pending { background: #fef3c7; color: #92400e; }
.badge-payment-paid { background: #dcfce7; color: #166534; }
.badge-payment-partially_paid { background: #dbeafe; color: #1e40af; }
.badge-payment-refunded { background: #fee2e2; color: #991b1b; }
.badge-payment-failed { background: #fee2e2; color: #991b1b; }

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.table thead th {
    text-align: left;
    padding: 12px;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    color: #6b7280;
    font-size: 13px;
    text-transform: uppercase;
}

.table tbody td {
    padding: 12px;
    border-bottom: 1px solid #f3f4f6;
}

.table tfoot td {
    padding: 12px;
    font-weight: 500;
    border-top: 2px solid #e5e7eb;
}

.text-right {
    text-align: right !important;
}

.text-center {
    text-align: center !important;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-success {
    background: #10b981;
    color: white;
    border: none;
}

.btn-success:hover {
    background: #059669;
}

.btn-info {
    background: #3b82f6;
    color: white;
    border: none;
}

.btn-info:hover {
    background: #2563eb;
}

#paymentStatusSelect {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    background: white;
}

#paymentStatusSelect:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

@media (max-width: 1024px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .header-actions {
        width: 100%;
        flex-wrap: wrap;
    }

    .info-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-value {
        text-align: left;
        margin-top: 5px;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>
