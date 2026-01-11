<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;

// Получаем категорию из URL
$category = $_GET['category'] ?? '';

if (empty($category)) {
    header('Location: /');
    exit;
}

// Получаем услуги этой категории
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT id, label as name, icon, chat_image,
               (SELECT base_price FROM service_base_prices WHERE service_id = services.id LIMIT 1) as base_price
        FROM services
        WHERE category = ?
        ORDER BY label
    ");
    $stmt->execute([$category]);
    $services = $stmt->fetchAll();
} catch (Exception $e) {
    $services = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= htmlspecialchars($category) ?> - качественная печать с доставкой">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #ec4899;
            --success: #10b981;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--dark);
            background-color: var(--light-gray);
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .header-nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        /* Breadcrumbs */
        .breadcrumbs {
            max-width: 1200px;
            margin: 2rem auto 0;
            padding: 0 2rem;
            font-size: 0.9rem;
        }

        .breadcrumbs a {
            color: var(--gray);
            text-decoration: none;
        }

        .breadcrumbs a:hover {
            color: var(--primary);
        }

        .breadcrumbs span {
            margin: 0 0.5rem;
            color: var(--gray);
        }

        /* Page Header */
        .page-header {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .page-description {
            color: var(--gray);
            font-size: 1.1rem;
        }

        /* Search */
        .search-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .search-box {
            position: relative;
            max-width: 500px;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 1rem;
            border: 2px solid var(--light-gray);
            border-radius: 50px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .search-btn {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-btn:hover {
            background: var(--primary-hover);
        }

        /* Services Grid */
        .services-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--dark);
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .service-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .service-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .service-price {
            color: var(--success);
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: auto;
        }

        .service-description {
            color: var(--gray);
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        /* Chat Widget */
        .chat-widget {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        .chat-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: all 0.2s;
        }

        .chat-btn:hover {
            background: var(--primary-hover);
            transform: scale(1.1);
        }

        /* Footer */
        .footer {
            background: var(--dark);
            color: var(--white);
            padding: 3rem 2rem 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
        }

        .footer-section a:hover {
            color: var(--white);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-nav {
                gap: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="/" class="logo">
                <i class="fas fa-print"></i> <?= SITE_NAME ?>
            </a>
            <nav class="header-nav">
                <a href="/" class="nav-link">Каталог</a>
                <a href="#about" class="nav-link">О нас</a>
                <a href="#contacts" class="nav-link">Контакты</a>
                <?php if ($isAuthenticated): ?>
                    <a href="/orders.php" class="nav-link">Мои заказы</a>
                    <a href="/profile.php" class="nav-link"><?= htmlspecialchars($currentUser['name']) ?></a>
                <?php else: ?>
                    <a href="#login" class="btn btn-primary" onclick="showAuthModal()">Войти</a>
                <?php endif; ?>
                <?php include 'components/cart.php'; ?>
            </nav>
        </div>
    </header>

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <a href="/">Главная</a>
        <span>/</span>
        <span><?= htmlspecialchars($category) ?></span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title"><?= htmlspecialchars($category) ?></h1>
        <p class="page-description">
            Выберите услугу для расчета стоимости и оформления заказа
        </p>
    </div>

    <!-- Search -->
    <div class="search-container">
        <div class="search-box">
            <input
                type="text"
                class="search-input"
                placeholder="Поиск услуг в категории..."
                id="searchInput"
                onkeyup="handleSearch()"
            >
            <button class="search-btn" onclick="handleSearch()">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>

    <!-- Services -->
    <main class="services-container">
        <?php if (empty($services)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>Услуги не найдены</h2>
                <p>В категории "<?= htmlspecialchars($category) ?>" пока нет услуг</p>
                <br>
                <a href="/" class="btn btn-primary">Вернуться на главную</a>
            </div>
        <?php else: ?>
            <div class="services-grid" id="servicesGrid">
                <?php foreach ($services as $service): ?>
                    <a href="service.php?id=<?= urlencode($service['id']) ?>" class="service-card" data-name="<?= strtolower($service['name']) ?>">
                        <div class="service-icon">
                            <i class="fas <?= $service['icon'] ?? 'fa-box' ?>"></i>
                        </div>
                        <div class="service-name"><?= htmlspecialchars($service['name']) ?></div>
                        <div class="service-description">
                            <?= htmlspecialchars($service['description'] ?? 'Оформить заказ онлайн с расчетом стоимости') ?>
                        </div>
                        <?php if (!empty($service['base_price'])): ?>
                            <div class="service-price">
                                От <?= number_format($service['base_price'], 0, ',', ' ') ?> ₽
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Chat Widget -->
    <div class="chat-widget">
        <button class="chat-btn" onclick="openChat()" title="Открыть чат">
            <i class="fas fa-comments"></i>
        </button>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>О компании</h3>
                <a href="#about">О нас</a>
                <a href="#portfolio">Портфолио</a>
                <a href="#reviews">Отзывы</a>
            </div>
            <div class="footer-section">
                <h3>Услуги</h3>
                <a href="/">Каталог услуг</a>
                <a href="#prices">Цены</a>
                <a href="#delivery">Доставка</a>
            </div>
            <div class="footer-section">
                <h3>Контакты</h3>
                <p><i class="fas fa-phone"></i> +7 (XXX) XXX-XX-XX</p>
                <p><i class="fas fa-envelope"></i> <?= ADMIN_EMAIL ?></p>
                <p><i class="fas fa-map-marker-alt"></i> Москва, Россия</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 <?= SITE_NAME ?>. Все права защищены.</p>
        </div>
    </footer>

    <script>
        // Поиск по услугам
        function handleSearch() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.service-card');

            cards.forEach(card => {
                const serviceName = card.getAttribute('data-name');
                if (serviceName.includes(searchText)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Открыть чат
        function openChat() {
            window.location.href = 'chat.php';
        }

        // Показать модальное окно авторизации
        function showAuthModal() {
            alert('Модальное окно авторизации в разработке');
        }
    </script>
</body>
</html>
