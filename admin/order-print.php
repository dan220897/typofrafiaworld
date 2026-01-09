<?php
// admin/order-print.php - Печать заказа
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'classes/User.php';
require_once 'classes/Settings.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию
checkAdminAuth('view_orders');

// Получаем ID заказа
$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) {
    die('Заказ не найден');
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$user = new User($db);
$settings = new Settings($db);
$adminLog = new AdminLog($db);

// Получаем данные заказа
$orderData = $order->getOrderById($order_id);
if (!$orderData) {
    die('Заказ не найден');
}

// Получаем элементы заказа
$orderItems = $order->getOrderItems($order_id);

// Получаем данные пользователя
$userData = $user->getUserById($orderData['user_id']);

// Получаем настройки компании
$companySettings = $settings->getByCategory('company');

// Логируем действие
$adminLog->log($_SESSION['admin_id'], 'print_order', 
    "Распечатан заказ #{$orderData['order_number']}", 'order', $order_id);

// Определяем статусы
$orderStatuses = [
    'new' => 'Новый',
    'processing' => 'В обработке',
    'in_production' => 'В производстве',
    'shipping' => 'Доставка',
    'completed' => 'Завершен',
    'canceled' => 'Отменен'
];

$paymentStatuses = [
    'pending' => 'Ожидает оплаты',
    'paid' => 'Оплачен',
    'partially_paid' => 'Частично оплачен',
    'refunded' => 'Возврат'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказ #<?php echo $orderData['order_number']; ?> - Печать</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }
            
            body {
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-after: always;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Шапка */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .company-info h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .company-details {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }
        
        .order-info {
            text-align: right;
        }
        
        .order-number {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .order-date {
            font-size: 14px;
            color: #666;
        }
        
        /* Информация о клиенте и доставке */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-block h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-content {
            font-size: 14px;
            line-height: 1.6;
        }
        
        .info-content p {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            display: inline-block;
            min-width: 100px;
        }
        
        /* Таблица товаров */
        .items-section {
            margin-bottom: 30px;
        }
        
        .items-section h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
        }
        
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
        }
        
        .item-params {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Итоги */
        .totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
        }
        
        .total-row.subtotal {
            font-weight: 500;
        }
        
        .total-row.discount {
            color: #d32f2f;
        }
        
        .total-row.final {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        /* Дополнительная информация */
        .additional-info {
            margin-top: 40px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .additional-info h4 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .additional-info p {
            font-size: 13px;
            line-height: 1.6;
            color: #666;
        }
        
        /* Подпись */
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 60px;
            padding-top: 30px;
        }
        
        .signature-block {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 10px;
            height: 40px;
        }
        
        .signature-label {
            font-size: 12px;
            color: #666;
        }
        
        /* Футер */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        /* Кнопки печати */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        
        .btn-print {
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-print:hover {
            background: #1565c0;
        }
        
        .btn-close {
            padding: 10px 20px;
            background: #666;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }
        
        .btn-close:hover {
            background: #555;
        }
        
        /* Статусы */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.paid {
            background: #4caf50;
            color: white;
        }
        
        .status-badge.pending {
            background: #ff9800;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Кнопки управления (не печатаются) -->
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn-print">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Печать
        </button>
        <button onclick="window.close()" class="btn-close">Закрыть</button>
    </div>
    
    <div class="container">
        <!-- Шапка документа -->
        <div class="header">
            <div class="company-info">
                <h1><?php echo htmlspecialchars($companySettings['company_name'] ?? 'Типография'); ?></h1>
                <div class="company-details">
                    <?php if (!empty($companySettings['company_address'])): ?>
                    <p><?php echo htmlspecialchars($companySettings['company_address']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($companySettings['company_phone'])): ?>
                    <p>Тел: <?php echo htmlspecialchars($companySettings['company_phone']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($companySettings['company_email'])): ?>
                    <p>Email: <?php echo htmlspecialchars($companySettings['company_email']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($companySettings['company_inn'])): ?>
                    <p>ИНН: <?php echo htmlspecialchars($companySettings['company_inn']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="order-info">
                <div class="order-number">ЗАКАЗ №<?php echo $orderData['order_number']; ?></div>
                <div class="order-date">от <?php echo date('d.m.Y', strtotime($orderData['created_at'])); ?></div>
                <div style="margin-top: 10px;">
                    <span class="status-badge <?php echo $orderData['payment_status']; ?>">
                        <?php echo $paymentStatuses[$orderData['payment_status']] ?? $orderData['payment_status']; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Информация о клиенте и доставке -->
        <div class="info-section">
            <div class="info-block">
                <h3>Заказчик</h3>
                <div class="info-content">
                    <p><span class="info-label">ФИО:</span> <?php echo htmlspecialchars($userData['name'] ?: 'Не указано'); ?></p>
                    <p><span class="info-label">Телефон:</span> <?php echo htmlspecialchars($userData['phone']); ?></p>
                    <?php if ($userData['email']): ?>
                    <p><span class="info-label">Email:</span> <?php echo htmlspecialchars($userData['email']); ?></p>
                    <?php endif; ?>
                    <?php if ($userData['company_name']): ?>
                    <p><span class="info-label">Компания:</span> <?php echo htmlspecialchars($userData['company_name']); ?></p>
                    <?php endif; ?>
                    <?php if ($userData['inn']): ?>
                    <p><span class="info-label">ИНН:</span> <?php echo htmlspecialchars($userData['inn']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="info-block">
                <h3>Информация о заказе</h3>
                <div class="info-content">
                    <p><span class="info-label">Статус:</span> <?php echo $orderStatuses[$orderData['status']] ?? $orderData['status']; ?></p>
                    <?php if ($orderData['deadline_at']): ?>
                    <p><span class="info-label">Срок:</span> <?php echo date('d.m.Y', strtotime($orderData['deadline_at'])); ?></p>
                    <?php endif; ?>
                    <?php if ($orderData['delivery_type']): ?>
                    <p><span class="info-label">Доставка:</span> 
                        <?php 
                        $deliveryTypes = [
                            'pickup' => 'Самовывоз',
                            'delivery' => 'Доставка',
                            'courier' => 'Курьер'
                        ];
                        echo $deliveryTypes[$orderData['delivery_type']] ?? $orderData['delivery_type']; 
                        ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($orderData['delivery_address']): ?>
                    <p><span class="info-label">Адрес:</span> <?php echo nl2br(htmlspecialchars($orderData['delivery_address'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Таблица товаров -->
        <div class="items-section">
            <h3>Товары и услуги</h3>
            <table>
                <thead>
                    <tr>
                        <th width="5%">№</th>
                        <th width="50%">Наименование</th>
                        <th width="10%" class="text-center">Кол-во</th>
                        <th width="15%" class="text-right">Цена</th>
                        <th width="20%" class="text-right">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $index => $item): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <div class="item-name"><?php echo htmlspecialchars($item['service_name']); ?></div>
                            <?php if (!empty($item['parameters'])): ?>
                                <?php $params = json_decode($item['parameters'], true); ?>
                                <?php if ($params): ?>
                                    <div class="item-params">
                                        <?php 
                                        $paramsList = [];
                                        foreach ($params as $key => $value) {
                                            $paramsList[] = htmlspecialchars($key . ': ' . $value);
                                        }
                                        echo implode(', ', $paramsList);
                                        ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($item['notes']): ?>
                                <div class="item-params">
                                    Примечание: <?php echo htmlspecialchars($item['notes']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo $item['quantity']; ?> шт.</td>
                        <td class="text-right"><?php echo number_format($item['unit_price'], 2, ',', ' '); ?> ₽</td>
                        <td class="text-right"><?php echo number_format($item['total_price'], 2, ',', ' '); ?> ₽</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="totals">
                <div class="total-row subtotal">
                    <span>Итого без скидки:</span>
                    <span><?php echo number_format($orderData['amount'], 2, ',', ' '); ?> ₽</span>
                </div>
                <?php if ($orderData['discount_amount'] > 0): ?>
                <div class="total-row discount">
                    <span>Скидка:</span>
                    <span>-<?php echo number_format($orderData['discount_amount'], 2, ',', ' '); ?> ₽</span>
                </div>
                <?php endif; ?>
                <div class="total-row final">
                    <span>ИТОГО К ОПЛАТЕ:</span>
                    <span><?php echo number_format($orderData['final_amount'], 2, ',', ' '); ?> ₽</span>
                </div>
            </div>
        </div>
        
        <!-- Дополнительная информация -->
        <?php if (!empty($orderData['comment'])): ?>
        <div class="additional-info">
            <h4>Комментарий к заказу:</h4>
            <p><?php echo nl2br(htmlspecialchars($orderData['comment'])); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Подписи -->
        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Исполнитель</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Заказчик</div>
            </div>
        </div>
        
        <!-- Футер -->
        <div class="footer">
            <p>Документ сформирован <?php echo date('d.m.Y H:i'); ?></p>
            <p>Спасибо за ваш заказ!</p>
        </div>
    </div>
    
    <script>
        // Автоматическая печать при загрузке (опционально)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>