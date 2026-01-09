<?php
// admin/order-edit.php - Редактирование заказа
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'classes/Service.php';
require_once 'classes/User.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('edit_orders');

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
$service = new Service($db);
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

// Получаем список услуг для выбора
$services = $service->getActiveServices();

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Обновляем основную информацию о заказе
        $orderUpdateData = [
            'delivery_type' => $_POST['delivery_type'] ?? '',
            'delivery_address' => $_POST['delivery_address'] ?? '',
            'comment' => $_POST['comment'] ?? '',
            'deadline_at' => !empty($_POST['deadline_at']) ? $_POST['deadline_at'] : null
        ];
        
        $order->updateOrder($order_id, $orderUpdateData);
        
        // Обновляем элементы заказа
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            // Удаляем старые элементы
            $order->deleteOrderItems($order_id);
            
            $totalAmount = 0;
            
            // Добавляем новые элементы
            foreach ($_POST['items'] as $item) {
                if (empty($item['service_id']) || empty($item['quantity'])) {
                    continue;
                }
                
                $itemData = [
                    'order_id' => $order_id,
                    'service_id' => intval($item['service_id']),
                    'quantity' => intval($item['quantity']),
                    'unit_price' => floatval($item['unit_price']),
                    'total_price' => floatval($item['unit_price']) * intval($item['quantity']),
                    'parameters' => !empty($item['parameters']) ? json_encode($item['parameters']) : null,
                    'notes' => $item['notes'] ?? ''
                ];
                
                $order->addOrderItem($itemData);
                $totalAmount += $itemData['total_price'];
            }
            
            // Обновляем общую сумму заказа
            $discountAmount = floatval($_POST['discount_amount'] ?? 0);
            $finalAmount = $totalAmount - $discountAmount;
            
            $order->updateOrderAmounts($order_id, $totalAmount, $discountAmount, $finalAmount);
        }
        
        $db->commit();
        
        // Логируем действие
        $adminLog->log($_SESSION['admin_id'], 'update_order', 
            "Обновлен заказ #{$orderData['order_number']}", 'order', $order_id);
        
        $_SESSION['success'] = 'Заказ успешно обновлен';
        header('Location: order-details.php?id=' . $order_id);
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = 'Ошибка сохранения: ' . $e->getMessage();
    }
}

// Заголовок страницы
$page_title = 'Редактирование заказа #' . $orderData['order_number'];
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

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
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
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
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

.item-row {
    position: relative;
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

/* Параметры */
.parameters-grid {
    display: grid;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.parameter-item {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.parameter-item label {
    font-size: 0.75rem;
    color: #6b7280;
    min-width: 80px;
}

.parameter-item input {
    flex: 1;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
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

/* Клиент */
.client-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
}

.client-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.client-details {
    flex: 1;
}

.client-name {
    font-weight: 600;
    color: #1f2937;
}

.client-phone {
    font-size: 0.875rem;
    color: #6b7280;
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

/* Адаптив */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .items-table {
        font-size: 0.75rem;
    }
    
    .items-table th,
    .items-table td {
        padding: 0.5rem;
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
        <a href="order-details.php?id=<?php echo $order_id; ?>">#<?php echo $orderData['order_number']; ?></a>
        <span class="separator">/</span>
        <span>Редактирование</span>
    </div>
    
    <!-- Заголовок -->
    <div class="page-header">
        <h1 class="page-title">Редактирование заказа #<?php echo $orderData['order_number']; ?></h1>
        <div>
            <a href="order-details.php?id=<?php echo $order_id; ?>" class="btn btn-secondary">
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
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Информация о клиенте</h2>
            </div>
            
            <div class="client-info">
                <div class="client-avatar">
                    <?php echo mb_substr($userData['name'] ?: 'К', 0, 1); ?>
                </div>
                <div class="client-details">
                    <div class="client-name"><?php echo htmlspecialchars($userData['name'] ?: 'Клиент'); ?></div>
                    <div class="client-phone"><?php echo htmlspecialchars($userData['phone']); ?></div>
                    <?php if ($userData['email']): ?>
                    <div class="client-phone"><?php echo htmlspecialchars($userData['email']); ?></div>
                    <?php endif; ?>
                </div>
                <a href="user_edit.php?id=<?php echo $userData['id']; ?>" class="btn btn-secondary">
                    Профиль клиента
                </a>
            </div>
        </div>
        
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
                        <th width="35%">Услуга</th>
                        <th width="15%">Количество</th>
                        <th width="15%">Цена за ед.</th>
                        <th width="15%">Сумма</th>
                        <th width="15%">Примечание</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody id="itemsContainer">
                    <?php foreach ($orderItems as $index => $item): ?>
                    <tr class="item-row" data-index="<?php echo $index; ?>">
                        <td>
                            <select name="items[<?php echo $index; ?>][service_id]" class="form-control service-select" onchange="updateServicePrice(this)" required>
                                <option value="">Выберите услугу</option>
                                <?php foreach ($services as $srv): ?>
                                <option value="<?php echo $srv['id']; ?>" 
                                        data-price="<?php echo $srv['base_price']; ?>"
                                        <?php echo $item['service_id'] == $srv['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($srv['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <?php if (!empty($item['parameters'])): ?>
                                <?php $params = json_decode($item['parameters'], true); ?>
                                <?php if ($params): ?>
                                    <div class="parameters-grid">
                                        <?php foreach ($params as $key => $value): ?>
                                        <div class="parameter-item">
                                            <label><?php echo htmlspecialchars($key); ?>:</label>
                                            <input type="text" name="items[<?php echo $index; ?>][parameters][<?php echo htmlspecialchars($key); ?>]" 
                                                   value="<?php echo htmlspecialchars($value); ?>" class="form-control">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number" name="items[<?php echo $index; ?>][quantity]" 
                                   value="<?php echo $item['quantity']; ?>" 
                                   min="1" class="form-control quantity-input" 
                                   onchange="updateItemTotal(this)" required>
                        </td>
                        <td>
                            <input type="number" name="items[<?php echo $index; ?>][unit_price]" 
                                   value="<?php echo $item['unit_price']; ?>" 
                                   min="0" step="0.01" class="form-control price-input" 
                                   onchange="updateItemTotal(this)" required>
                        </td>
                        <td>
                            <div class="item-total" data-total="<?php echo $item['total_price']; ?>">
                                ₽<?php echo number_format($item['total_price'], 0, '', ' '); ?>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="items[<?php echo $index; ?>][notes]" 
                                   value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>" 
                                   class="form-control" placeholder="Примечание">
                        </td>
                        <td>
                            <button type="button" class="btn-remove-item" onclick="removeItem(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="totals-section">
                <div class="total-row">
                    <span class="total-label">Сумма:</span>
                    <span class="total-value" id="subtotalAmount">₽<?php echo number_format($orderData['amount'], 0, '', ' '); ?></span>
                    <input type="hidden" id="subtotalInput" value="<?php echo $orderData['amount']; ?>">
                </div>
                <div class="total-row">
                    <span class="total-label">Скидка:</span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>₽</span>
                        <input type="number" name="discount_amount" id="discountAmount" 
                               value="<?php echo $orderData['discount_amount']; ?>" 
                               min="0" step="0.01" class="form-control" style="width: 120px;"
                               onchange="updateTotals()">
                    </div>
                </div>
                <div class="total-row final">
                    <span>Итого к оплате:</span>
                    <span id="finalAmount">₽<?php echo number_format($orderData['final_amount'], 0, '', ' '); ?></span>
                    <input type="hidden" id="finalAmountInput" value="<?php echo $orderData['final_amount']; ?>">
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Дополнительная информация</h2>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Способ доставки</label>
                    <select name="delivery_type" class="form-control">
                        <option value="">Не указан</option>
                        <option value="pickup" <?php echo $orderData['delivery_type'] === 'pickup' ? 'selected' : ''; ?>>Самовывоз</option>
                        <option value="delivery" <?php echo $orderData['delivery_type'] === 'delivery' ? 'selected' : ''; ?>>Доставка</option>
                        <option value="courier" <?php echo $orderData['delivery_type'] === 'courier' ? 'selected' : ''; ?>>Курьер</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Срок выполнения</label>
                    <input type="date" name="deadline_at" class="form-control" 
                           value="<?php echo $orderData['deadline_at'] ? date('Y-m-d', strtotime($orderData['deadline_at'])) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Адрес доставки</label>
                <textarea name="delivery_address" class="form-control" rows="3"><?php echo htmlspecialchars($orderData['delivery_address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Комментарий к заказу</label>
                <textarea name="comment" class="form-control" rows="3"><?php echo htmlspecialchars($orderData['comment'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить изменения
            </button>
            <a href="order-details.php?id=<?php echo $order_id; ?>" class="btn btn-secondary">
                Отмена
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = <?php echo count($orderItems); ?>;
const services = <?php echo json_encode($services); ?>;

// Добавление новой позиции
function addItem() {
    const container = document.getElementById('itemsContainer');
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.dataset.index = itemIndex;
    
    const servicesOptions = services.map(srv => 
        `<option value="${srv.id}" data-price="${srv.base_price}">${escapeHtml(srv.name)}</option>`
    ).join('');
    
    row.innerHTML = `
        <td>
            <select name="items[${itemIndex}][service_id]" class="form-control service-select" onchange="updateServicePrice(this)" required>
                <option value="">Выберите услугу</option>
                ${servicesOptions}
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]" value="1" min="1" 
                   class="form-control quantity-input" onchange="updateItemTotal(this)" required>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][unit_price]" value="0" min="0" step="0.01" 
                   class="form-control price-input" onchange="updateItemTotal(this)" required>
        </td>
        <td>
            <div class="item-total" data-total="0">₽0</div>
        </td>
        <td>
            <input type="text" name="items[${itemIndex}][notes]" class="form-control" placeholder="Примечание">
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
    if (confirm('Удалить эту позицию?')) {
        button.closest('tr').remove();
        updateTotals();
    }
}

// Обновление цены при выборе услуги
function updateServicePrice(select) {
    const option = select.options[select.selectedIndex];
    const price = parseFloat(option.dataset.price || 0);
    const row = select.closest('tr');
    const priceInput = row.querySelector('.price-input');
    
    priceInput.value = price;
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
    const items = document.querySelectorAll('.item-row');
    if (items.length === 0) {
        e.preventDefault();
        alert('Добавьте хотя бы одну позицию в заказ');
        return false;
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>