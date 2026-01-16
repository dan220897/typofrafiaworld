<?php
require_once 'config/config.php';
require_once __DIR__ . '/../classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;

// Если не авторизован, перенаправляем на главную
if (!$isAuthenticated) {
    header('Location: /');
    exit;
}

// Проверяем наличие ID заказа
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$orderId) {
    header('Location: /orders.php');
    exit;
}

// Получаем категории услуг для футера
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

// Получаем все точки самовывоза для футера
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, name, address, phone, working_hours FROM pickup_points WHERE is_active = 1 ORDER BY sort_order, name");
    $pickupPoints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pickupPoints = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали заказа - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f9fafb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--dark);
            background-color: var(--light-gray);
            line-height: 1.6;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .back-button {
            background: var(--light-gray);
            color: var(--dark);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }

        .main-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 3rem 2rem;
            min-height: calc(100vh - 300px);
        }

        .order-header {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .order-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .order-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .order-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
        }

        .order-meta-item strong {
            color: var(--dark);
        }

        .order-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-new { background: #dbeafe; color: #1e40af; }
        .status-processing { background: #fef3c7; color: #92400e; }
        .status-ready { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-canceled { background: #fee2e2; color: #991b1b; }

        .order-details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .order-section {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .order-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .order-item {
            padding: 1rem;
            background: var(--light-gray);
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-info h4 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .item-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--gray);
        }

        .info-value {
            color: var(--dark);
            font-weight: 600;
        }

        .payment-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--white);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .payment-link:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .total-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-align: right;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--light-gray);
        }

        .loading {
            text-align: center;
            padding: 4rem 2rem;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--light-gray);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .order-details-grid { grid-template-columns: 1fr; }
            .order-meta { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="/" class="logo">
                <img src="logo.png" alt="<?= SITE_NAME ?>">
            </a>
            <a href="/orders.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Назад к заказам
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div id="orderContent">
            <div class="loading">
                <div class="spinner"></div>
                <p>Загрузка деталей заказа...</p>
            </div>
        </div>
    </main>

    <script>
        const orderId = <?= $orderId ?>;

        // Загрузка деталей заказа
        async function loadOrderDetails() {
            try {
                const response = await fetch(`/api/orders.php/${orderId}`, {
                    method: 'GET',
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error('Ошибка загрузки заказа');
                }

                const data = await response.json();

                if (data.success && data.order) {
                    renderOrder(data.order, data.items || []);
                } else {
                    showError(data.error || 'Заказ не найден');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showError('Произошла ошибка при загрузке заказа');
            }
        }

        // Рендер деталей заказа
        function renderOrder(order, items) {
            const content = `
                <div class="order-header">
                    <h1 class="order-title">
                        <i class="fas fa-shopping-bag"></i> Заказ #${order.order_number || order.id}
                    </h1>
                    <div class="order-meta">
                        <div class="order-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Дата: <strong>${formatDate(order.created_at)}</strong></span>
                        </div>
                        ${order.deadline_at ? `
                        <div class="order-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Срок: <strong>${formatDate(order.deadline_at)}</strong></span>
                        </div>
                        ` : ''}
                        <div class="order-meta-item">
                            <span class="order-status status-${order.status}">${getStatusLabel(order.status)}</span>
                        </div>
                    </div>
                </div>

                <div class="order-details-grid">
                    <div class="order-section">
                        <h2 class="section-title"><i class="fas fa-list"></i> Состав заказа</h2>
                        <div class="order-items">
                            ${items.length > 0 ? items.map(item => `
                                <div class="order-item">
                                    <div class="item-info">
                                        <h4>${item.service_name || 'Услуга'}</h4>
                                        ${item.quantity ? `<p>Количество: ${item.quantity}</p>` : ''}
                                        ${item.notes ? `<p>${item.notes}</p>` : ''}
                                    </div>
                                    <div class="item-price">${formatPrice(item.price)} ₽</div>
                                </div>
                            `).join('') : '<p>Позиции не найдены</p>'}
                        </div>
                        <div class="total-amount">
                            Итого: ${formatPrice(order.final_amount)} ₽
                        </div>
                    </div>

                    <div>
                        <div class="order-section">
                            <h2 class="section-title"><i class="fas fa-info-circle"></i> Информация</h2>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-truck"></i> Доставка:</span>
                                <span class="info-value">${getDeliveryLabel(order.delivery_method)}</span>
                            </div>
                            ${order.delivery_address ? `
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-map-marker-alt"></i> Адрес:</span>
                                <span class="info-value">${order.delivery_address}</span>
                            </div>
                            ` : ''}
                            ${order.notes ? `
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-comment"></i> Примечания:</span>
                                <span class="info-value">${order.notes}</span>
                            </div>
                            ` : ''}
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-credit-card"></i> Оплата:</span>
                                <span class="info-value">${getPaymentStatusLabel(order.payment_status)}</span>
                            </div>
                            ${order.tinkoff_payment_url ? `
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-link"></i> Ссылка на оплату:</span>
                                <span class="info-value">
                                    <a href="${order.tinkoff_payment_url}" target="_blank" class="payment-link">
                                        <i class="fas fa-external-link-alt"></i> Оплатить заказ
                                    </a>
                                </span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('orderContent').innerHTML = content;
        }

        // Показать ошибку
        function showError(message) {
            document.getElementById('orderContent').innerHTML = `
                <div class="order-section">
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> ${message}
                    </div>
                    <a href="/orders.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Вернуться к заказам
                    </a>
                </div>
            `;
        }

        // Получить название статуса
        function getStatusLabel(status) {
            const labels = {
                'new': 'Новый',
                'processing': 'В работе',
                'ready': 'Готов',
                'completed': 'Завершен',
                'canceled': 'Отменен'
            };
            return labels[status] || status;
        }

        // Получить название доставки
        function getDeliveryLabel(method) {
            const labels = {
                'pickup': 'Самовывоз',
                'delivery': 'Доставка',
                'courier': 'Курьер'
            };
            return labels[method] || method || 'Не указано';
        }

        // Получить статус оплаты
        function getPaymentStatusLabel(status) {
            const labels = {
                'pending': 'Ожидает оплаты',
                'paid': 'Оплачен',
                'failed': 'Ошибка оплаты',
                'refunded': 'Возвращен'
            };
            return labels[status] || 'Не указано';
        }

        // Форматировать дату
        function formatDate(dateString) {
            if (!dateString) return 'Не указано';
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Форматировать цену
        function formatPrice(price) {
            return new Intl.NumberFormat('ru-RU').format(price);
        }

        // Загрузка при старте
        document.addEventListener('DOMContentLoaded', loadOrderDetails);
    </script>
</body>
</html>
