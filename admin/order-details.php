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
                <div class="info-row">
                    <span class="info-label">Статус оплаты:</span>
                    <span class="info-value">
                        <span class="badge badge-payment-<?php echo $orderData['payment_status']; ?>">
                            <?php
                            $paymentStatuses = [
                                'pending' => 'Ожидает оплаты',
                                'paid' => 'Оплачен',
                                'partially_paid' => 'Частично оплачен',
                                'refunded' => 'Возврат',
                                'failed' => 'Ошибка оплаты'
                            ];
                            echo $paymentStatuses[$orderData['payment_status']] ?? $orderData['payment_status'];
                            ?>
                        </span>
                    </span>
                </div>
                <?php if (!empty($orderData['deadline_at'])): ?>
                <div class="info-row">
                    <span class="info-label">Срок выполнения:</span>
                    <span class="info-value"><?php echo date('d.m.Y', strtotime($orderData['deadline_at'])); ?></span>
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
</script>

<style>
.order-details-page {
    padding: 20px;
}

.order-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #666;
}

.info-value {
    color: #333;
}

.badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
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

@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>
