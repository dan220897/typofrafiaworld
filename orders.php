<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;

// Если не авторизован, перенаправляем на главную
if (!$isAuthenticated) {
    header('Location: /');
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
    <title>Мои заказы - <?= SITE_NAME ?></title>
    <meta name="description" content="Просмотр истории заказов">
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
            font-family: montserrat;
            color: var(--dark);
            background-color: var(--light-gray);
            line-height: 1.6;
            overflow-x: hidden;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            
            backdrop-filter: blur(10px);
            
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

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        

        .logo:hover img {
            transform: scale(1.05);
        }

        .nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
           color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link.active {
            color: var(--primary);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
        }

        .auth-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            font-weight: 400;
            color: var(--dark);
            font-size:0.9rem;
        }

        .logout-button {
            
            color: black;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-button:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .main-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 3rem 2rem;
            min-height: calc(100vh - 300px);
        }

        .page-header {
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            animation: fadeInUp 0.6s ease 0.1s backwards;
        }

        .filter-button {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e5e7eb;
            background: var(--white);
            color: var(--gray);
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-button:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .filter-button.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border-color: transparent;
        }

        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            animation: fadeInUp 0.6s ease 0.2s backwards;
        }

        .order-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .order-status {
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

        .order-details {
            margin-bottom: 1rem;
        }

        .order-detail {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            color: var(--gray);
            font-size: 0.95rem;
        }

        .order-detail strong {
            color: var(--dark);
        }

        .order-amount {
            font-size: 1.5rem;
            font-weight: 700;
            
            text-align: right;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--light-gray);
        }

        .order-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .order-action-btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            color:black;
        }

        

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            animation: fadeInUp 0.6s ease 0.2s backwards;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: var(--gray);
            margin-bottom: 2rem;
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

        /* Footer */
        .footer {
            background: var(--dark);
            color: var(--white);
            padding: 4rem 2rem 1.5rem;
            margin-top: 6rem;
        }

        .footer-content {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2.5rem;
        }

        .footer-locations-wrapper {
            max-width: 1280px;
            margin: 0 auto 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-locations-wrapper h3 {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .footer-section h3 {
            margin-bottom: 1.25rem;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            padding-left: 0;
        }

        .footer-section a:hover {
            color: var(--white);
            padding-left: 5px;
        }

        .footer-section p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }

        .footer-social {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--white);
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: var(--primary);
            transform: translateY(-3px);
            color: var(--white);
        }

        .footer-locations-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .footer-location {
            flex: 1;
            min-width: 200px;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border-left: 3px solid var(--primary);
            transition: all 0.3s ease;
        }

        .footer-location:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
        }

        .footer-location .location-name {
            color: var(--white);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .footer-location .location-name i {
            color: var(--primary);
            margin-right: 0.5rem;
        }

        .footer-location .location-address {
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-location .location-hours {
            font-size: 0.8rem;
            font-style: italic;
            color: rgba(255, 255, 255, 0.5);
        }

        .footer-location .location-hours i {
            margin-right: 0.4rem;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .nav { display: none; }
            .orders-grid { grid-template-columns: 1fr; }
            .page-title { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="/" class="logo">
                <img src="logo.png" height="22vw" alt="<?= SITE_NAME ?>">
            </a>
             <div class="user-info">
                <?php if ($isAuthenticated): ?>
                    <span class="user-name">
                        
                        <?php
                        if (!empty($currentUser['email'])) {
                            echo htmlspecialchars($currentUser['email']);
                        } elseif (!empty($currentUser['name'])) {
                            echo htmlspecialchars($currentUser['name']);
                        } else {
                            echo htmlspecialchars($currentUser['phone']);
                        }
                        ?>
                    </span>
                    
                <?php else: ?>
                    <button class="auth-button" onclick="openAuthModal()">
                        <i class="fas fa-sign-in-alt"></i> Войти
                    </button>
                <?php endif; ?>
            </div>
            <nav class="nav">
                <?php include 'components/mega-menu.php'; ?>
                <a href="/about.php" class="nav-link">О нас</a>
                <a href="/portfolio.php" class="nav-link">Портфолио</a>
                <a href="/contacts.php" class="nav-link">Контакты</a>
                <?php if ($isAuthenticated): ?>
                    <a href="/orders.php" class="nav-link active">Мои заказы</a>
                    <a href="/profile.php" class="nav-link"><i class="fas fa-user"></i></a>
                    <button class="logout-button" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> 
                    </button>
                    
                <?php endif; ?>
            </nav>
           
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Debug info -->
        <?php if ($currentUser): ?>
            <!-- User ID: <?= $currentUser['id'] ?> | Email: <?= $currentUser['email'] ?? 'not set' ?> | Phone: <?= $currentUser['phone'] ?? 'not set' ?> -->
        <?php endif; ?>

        

        <!-- Orders Grid -->
        <div id="ordersContainer">
            <div class="loading">
                <div class="spinner"></div>
                <p>Загрузка заказов...</p>
            </div>
        </div>
    </main>

    

    <script>
        let currentFilter = 'all';
        let allOrders = [];

        // Загрузка заказов
        async function loadOrders() {
            try {
                const response = await fetch('/api/orders.php', {
                    method: 'GET',
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error('Ошибка загрузки заказов');
                }

                const data = await response.json();
                console.log('Ответ API:', data); // Отладка

                if (data.success) {
                    allOrders = data.orders || [];
                    console.log('Количество заказов:', allOrders.length); // Отладка
                    console.log('Заказы:', allOrders); // Отладка
                    renderOrders();
                } else {
                    showError(data.error || 'Не удалось загрузить заказы');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showError('Произошла ошибка при загрузке заказов');
            }
        }

        // Рендер заказов
        function renderOrders() {
            const container = document.getElementById('ordersContainer');

            // Фильтрация заказов
            let filteredOrders = allOrders;
            if (currentFilter !== 'all') {
                filteredOrders = allOrders.filter(order => order.status === currentFilter);
            }

            // Если заказов нет
            if (filteredOrders.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                        <h3 class="empty-title">Заказов не найдено</h3>
                        <p class="empty-text">
                            ${currentFilter === 'all'
                                ? 'У вас пока нет заказов. Оформите первый заказ прямо сейчас!'
                                : 'Заказов с этим статусом пока нет'}
                        </p>
                        ${currentFilter === 'all' ? '<a href="/catalog.php" class="auth-button">Перейти в каталог</a>' : ''}
                    </div>
                `;
                return;
            }

            // Рендер заказов
            const ordersHtml = filteredOrders.map(order => `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">
                            <i class="fas fa-hashtag"></i> ${order.order_number || order.id}
                        </div>
                        
                    </div>
                    <div class="order-details">
                        <div class="order-detail">
                            <span><i class="fas fa-calendar"></i> Дата:</span>
                            <strong>${formatDate(order.created_at)}</strong>
                        </div>
                        ${order.deadline_at ? `
                        <div class="order-detail">
                            <span><i class="fas fa-clock"></i> Срок:</span>
                            <strong>${formatDate(order.deadline_at)}</strong>
                        </div>
                        ` : ''}
                        <div class="order-detail">
                            <span><i class="fas fa-truck"></i> Доставка:</span>
                            <strong>${getDeliveryLabel(order.delivery_method)}</strong>
                        </div>
                    </div>
                    <div class="order-amount">
                        ${formatPrice(order.final_amount)} ₽
                    </div>
                    <div class="order-actions">
                        <a href="/order-details.php?id=${order.id}" class="order-action-btn btn-primary">
                            <i class="fas fa-eye"></i> Подробнее
                        </a>
                    </div>
                </div>
            `).join('');

            container.innerHTML = `<div class="orders-grid">${ordersHtml}</div>`;
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

        // Показать ошибку
        function showError(message) {
            const container = document.getElementById('ordersContainer');
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <h3 class="empty-title">Ошибка</h3>
                    <p class="empty-text">${message}</p>
                    <button class="auth-button" onclick="loadOrders()">Попробовать снова</button>
                </div>
            `;
        }

        // Фильтры
        document.querySelectorAll('.filter-button').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.filter-button').forEach(b => b.classList.remove('active'));
                button.classList.add('active');
                currentFilter = button.dataset.status;
                renderOrders();
            });
        });

        // Выход
        async function logout() {
            try {
                const response = await fetch('/api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                });

                if (response.ok) {
                    window.location.href = '/';
                }
            } catch (error) {
                console.error('Ошибка выхода:', error);
            }
        }

        // Загрузка при старте
        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>
</html>
