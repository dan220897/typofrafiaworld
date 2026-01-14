<?php
// admin/orders.php - Управление заказами
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Order.php';



// Проверяем авторизацию и права
checkAdminAuth('view_orders');

// Получаем параметры фильтрации
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$order = new Order($db);



// Получаем заказы
$filters = [
    'status' => $status !== 'all' ? $status : null,
    'search' => $search ?: null,
    'date_from' => $date_from ?: null,
    'date_to' => $date_to ?: null
];

// Добавляем фильтр по локации для location admins
if (isLocationAdmin()) {
    $filters['location_id'] = getCurrentLocationId();
}

$offset = ($page - 1) * $per_page;
$result = $order->getOrders($filters, $per_page, $offset);
$orders = $result['data'];
$total_orders = $result['total'];
$total_pages = ceil($total_orders / $per_page);

// Получаем статистику по статусам
if (isLocationAdmin()) {
    $status_stats = $order->getStatusStats(getCurrentLocationId());
} else {
    $status_stats = $order->getStatusStats();
}

// Заголовок страницы
$page_title = 'Управление заказами';
require_once 'includes/header.php';
?>

<div class="orders-page">
    <!-- Заголовок и действия -->
    <div class="page-header">
        <h1>Заказы</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="createNewOrder()">
                <i class="fas fa-plus"></i> Новый заказ
            </button>
            <button class="btn btn-secondary" onclick="exportOrders()">
                <i class="fas fa-download"></i> Экспорт
            </button>
        </div>
    </div>
    
    <!-- Статистика по статусам -->
    <div class="status-stats">
        <a href="?status=all" class="stat-item <?php echo $status === 'all' ? 'active' : ''; ?>">
            <span class="stat-value"><?php echo $total_orders; ?></span>
            <span class="stat-label">Все заказы</span>
        </a>
        <?php foreach ($status_stats as $stat): ?>
        <a href="?status=<?php echo $stat['status']; ?>" class="stat-item <?php echo $status === $stat['status'] ? 'active' : ''; ?>">
            <span class="stat-value"><?php echo $stat['count']; ?></span>
            <span class="stat-label"><?php echo ORDER_STATUSES[$stat['status']] ?? $stat['status']; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Фильтры -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" class="form-control" placeholder="Поиск по номеру, клиенту..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="filter-group">
                <input type="date" name="date_from" class="form-control" 
                       value="<?php echo $date_from; ?>" placeholder="От">
            </div>
            
            <div class="filter-group">
                <input type="date" name="date_to" class="form-control" 
                       value="<?php echo $date_to; ?>" placeholder="До">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Найти
            </button>
            
            <?php if ($search || $date_from || $date_to): ?>
            <a href="orders.php" class="btn btn-link">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Таблица заказов -->
    <div class="orders-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Номер</th>
                    <th>Клиент</th>
                    <th>Услуги</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Оплата</th>
                    <th>Срок</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders['data'])): ?>
                <tr>
                    <td colspan="9" class="text-center">Заказы не найдены</td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders['data'] as $order): ?>
                <tr data-order-id="<?php echo $order['id']; ?>" class="order-row">
                    <td>
                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="order-number">
                            #<?php echo $order['order_number']; ?>
                        </a>
                    </td>
                    <td>
                        <div class="customer-info">
                            <strong><?php echo htmlspecialchars($order['user_name'] ?? 'Гость'); ?></strong>
                            <br>
                            <small><?php echo htmlspecialchars($order['user_phone']); ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="services-list">
                            <?php echo htmlspecialchars($order['items_summary']); ?>
                            <?php if ($order['items_count'] > 2): ?>
                            <br><small>и еще <?php echo $order['items_count'] - 2; ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <strong><?php echo number_format($order['final_amount'], 0, '', ' '); ?> ₽</strong>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <br><small class="text-muted">Скидка: <?php echo number_format($order['discount_amount'], 0, '', ' '); ?> ₽</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <select class="status-select" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                            <?php foreach (ORDER_STATUSES as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <span class="payment-status <?php echo $order['payment_status']; ?>">
                            <?php echo getPaymentStatusLabel($order['payment_status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($order['deadline_at']): ?>
                        <span class="deadline <?php echo strtotime($order['deadline_at']) < time() ? 'overdue' : ''; ?>">
                            <?php echo date('d.m.Y', strtotime($order['deadline_at'])); ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <small><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></small>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="Подробнее">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="order-edit.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn-icon" onclick="printOrder(<?php echo $order['id']; ?>)" title="Печать">
                                <i class="fas fa-print"></i>
                            </button>
                            <?php if (Admin::hasPermission('delete_orders')): ?>
                            <button class="btn-icon text-danger" onclick="deleteOrder(<?php echo $order['id']; ?>)" title="Удалить">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Пагинация -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($filters)); ?>" class="page-link">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($filters)); ?>" 
           class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($filters)); ?>" class="page-link">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.orders-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 28px;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #f3f4f6;
    color: #333;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.status-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    overflow-x: auto;
}

.stat-item {
    background: white;
    padding: 20px;
    border-radius: 12px;
    min-width: 120px;
    text-align: center;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-item.active {
    border-color: #3b82f6;
    background: #eff6ff;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #666;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.filters-form {
    display: flex;
    gap: 15px;
    align-items: center;
}

.filter-group {
    flex: 1;
    max-width: 250px;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

.orders-table-wrapper {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f9fafb;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #666;
    border-bottom: 1px solid #e5e7eb;
}

.data-table td {
    padding: 15px;
    border-bottom: 1px solid #f3f4f6;
}

.order-row:hover {
    background: #f9fafb;
}

.order-number {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
}

.order-number:hover {
    text-decoration: underline;
}

.customer-info strong {
    color: #333;
}

.customer-info small {
    color: #666;
}

.status-select {
    padding: 6px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
}

.payment-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.payment-status.paid {
    background: #d1fae5;
    color: #065f46;
}

.payment-status.pending {
    background: #fef3c7;
    color: #92400e;
}

.deadline {
    font-size: 13px;
}

.deadline.overdue {
    color: #dc2626;
    font-weight: 600;
}

.table-actions {
    display: flex;
    gap: 5px;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border: none;
    background: #f3f4f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-icon:hover {
    background: #e5e7eb;
}

.text-danger {
    color: #dc2626;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 30px;
}

.page-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.page-link:hover {
    background: #f3f4f6;
}

.page-link.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}
</style>

<script>
// Обновление статуса заказа
async function updateOrderStatus(orderId, newStatus) {
    try {
        const response = await fetch(`api/orders.php?id=${orderId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'update_status',
                status: newStatus
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Статус заказа обновлен', 'success');
        } else {
            showNotification(data.message || 'Ошибка обновления статуса', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Удаление заказа
async function deleteOrder(orderId) {
    if (!confirm('Вы уверены, что хотите удалить этот заказ?')) {
        return;
    }
    
    try {
        const response = await fetch(`api/orders.php?id=${orderId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.querySelector(`tr[data-order-id="${orderId}"]`).remove();
            showNotification('Заказ удален', 'success');
        } else {
            showNotification(data.message || 'Ошибка удаления', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Печать заказа
function printOrder(orderId) {
    window.open(`order-print.php?id=${orderId}`, '_blank');
}

// Создание нового заказа
function createNewOrder() {
    window.location.href = 'order-create.php';
}

// Экспорт заказов
function exportOrders() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', '1');
    window.location.href = `orders-export.php?${params.toString()}`;
}

// Уведомления
function showNotification(message, type = 'info') {
    // Здесь код для показа уведомления
    console.log(`${type}: ${message}`);
}
</script>

<?php
require_once 'includes/footer.php';

// Вспомогательные функции
function getPaymentStatusLabel($status) {
    $labels = [
        'pending' => 'Ожидает оплаты',
        'paid' => 'Оплачен',
        'partially_paid' => 'Частично оплачен',
        'refunded' => 'Возврат'
    ];
    return $labels[$status] ?? $status;
}
?>