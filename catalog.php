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
        SELECT id, label as name, icon, description,
               (SELECT base_price FROM service_base_prices WHERE service_id = services.id LIMIT 1) as base_price
        FROM services
        WHERE category = ? AND is_active = 1
        ORDER BY sort_order, label
    ");
    $stmt->execute([$category]);
    $services = $stmt->fetchAll();
} catch (Exception $e) {
    $services = [];
}

// Получаем все категории услуг для футера
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

// Цвета для категорий
$categoryColors = [
    'Визитки' => '#6366f1',
    'Баннеры' => '#ec4899',
    'Флаеры' => '#8b5cf6',
    'Листовки' => '#06b6d4',
    'Буклеты' => '#10b981',
    'Календари' => '#f59e0b',
    'Наклейки' => '#ef4444',
    'Брошюры' => '#14b8a6',
    'Каталоги' => '#3b82f6',
    'Пакеты' => '#a855f7',
    'Папки' => '#84cc16',
    'Плакаты' => '#f97316',
    'Сувениры' => '#ec4899',
    'Дизайн' => '#6366f1'
];

$currentColor = $categoryColors[$category] ?? '#6366f1';
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
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #ec4899;
            --success: #10b981;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f9fafb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--dark);
            background-color: var(--light-gray);
            line-height: 1.6;
            overflow-x: hidden;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            animation: fadeIn 0.3s ease;
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
            color: var(--primary);
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .logo:hover { transform: scale(1.05); }

        .header-nav {
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

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-link:hover { color: var(--primary); }
        .nav-link:hover::after { width: 100%; }

        .btn {
            padding: 0.625rem 1.5rem;
            border-radius: 12px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .breadcrumbs {
            max-width: 1280px;
            margin: 2rem auto 0;
            padding: 0 2rem;
            font-size: 0.9rem;
            animation: fadeIn 0.5s ease;
        }

        .breadcrumbs a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumbs a:hover { color: var(--primary); }

        .breadcrumbs span {
            margin: 0 0.5rem;
            color: var(--gray);
        }

        .page-header {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 3rem 2rem;
            background: var(--light-gray);
            border-radius: 20px;
            animation: fadeInUp 0.6s ease;
        }

        .page-title {
            font-size: 2.75rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .page-description {
            color: var(--gray);
            font-size: 1.15rem;
        }

        .container {
            max-width: 1280px;
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
            border-radius: 20px;
            padding: 2.5rem 2rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--dark);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(99, 102, 241, 0.05);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .service-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-xl);
            border-color: <?= $currentColor ?>;
        }

        .service-card:hover::before { opacity: 1; }

        .service-icon {
            font-size: 3.5rem;
            color: <?= $currentColor ?>;
            margin-bottom: 1.25rem;
            transition: all 0.4s ease;
            position: relative;
        }

        .service-card:hover .service-icon {
            transform: scale(1.15) rotateY(180deg);
        }

        .service-name {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            transition: color 0.3s ease;
            position: relative;
        }

        .service-card:hover .service-name {
            color: <?= $currentColor ?>;
        }

        .service-description {
            color: var(--gray);
            margin-bottom: 1rem;
            flex-grow: 1;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .service-price {
            color: var(--success);
            font-size: 1.35rem;
            font-weight: 700;
            margin-top: auto;
            position: relative;
        }

        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            color: var(--gray);
            animation: fadeInUp 0.6s ease;
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.2;
            color: <?= $currentColor ?>;
        }

        .empty-state h2 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .telegram-widget {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 99;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: fadeInUp 1s ease;
        }

        .telegram-info {
            background: var(--white);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
            max-width: 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .telegram-widget:hover .telegram-info {
            opacity: 1;
            transform: translateX(0);
            max-width: 250px;
        }

        .telegram-info-text {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .telegram-info-status {
            font-size: 0.75rem;
            color: var(--success);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .telegram-info-status::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .telegram-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #0088cc;
            color: var(--white);
            border: none;
            font-size: 1.75rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 136, 204, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .telegram-btn:hover {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 12px 28px rgba(0, 136, 204, 0.5);
        }

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

        @media (max-width: 968px) {
            .header-nav { gap: 1rem; }
            .nav-link { font-size: 0.9rem; }
            .page-title { font-size: 2.25rem; }
            .page-header { padding: 2rem 1.5rem; }
        }

        @media (max-width: 768px) {
            .header-container { padding: 1rem; }
            .header-nav { gap: 0.75rem; font-size: 0.85rem; }
            .logo { font-size: 1.25rem; }
            .breadcrumbs { padding: 0 1rem; }
            .page-header { margin: 1.5rem auto; padding: 2rem 1rem; }
            .page-title { font-size: 1.75rem; }
            .page-description { font-size: 1rem; }
            .container { padding: 0 1rem; margin: 2rem auto; }
            .services-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .service-card { padding: 2rem 1.5rem; }
            .telegram-widget { bottom: 1.5rem; right: 1.5rem; }
            .telegram-btn { width: 56px; height: 56px; font-size: 1.5rem; }
            .telegram-widget:hover .telegram-info { display: none; }
        }

        @media (max-width: 480px) {
            .header-nav a:not(.btn) { display: none; }
            .page-title { font-size: 1.5rem; }
            .btn { padding: 0.5rem 1.25rem; font-size: 0.85rem; }
            .service-icon { font-size: 2.75rem; }
            .service-name { font-size: 1.15rem; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="/" class="logo">
                <img src="logo.png" height="22vw" />
            </a>
            <nav class="header-nav">
                <?php include 'components/mega-menu.php'; ?>
                <a href="/about.php" class="nav-link">О нас</a>
                <a href="/portfolio.php" class="nav-link">Портфолио</a>
                <a href="/contacts.php" class="nav-link">Контакты</a>
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

    <div class="breadcrumbs">
        <a href="/">Главная</a>
        <span>/</span>
        <span><?= htmlspecialchars($category) ?></span>
    </div>

    <div class="page-header">
        <h1 class="page-title"><?= htmlspecialchars($category) ?></h1>
        <p class="page-description">
            Выберите услугу для расчета стоимости и оформления заказа
        </p>
    </div>

    <main class="container">
        <?php if (empty($services)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>Услуги не найдены</h2>
                <p>В категории "<?= htmlspecialchars($category) ?>" пока нет услуг</p>
                <br>
                <a href="/" class="btn btn-primary">Вернуться на главную</a>
            </div>
        <?php else: ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <a href="service.php?id=<?= urlencode($service['id']) ?>" class="service-card">
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

    <div class="telegram-widget">
        <div class="telegram-info">
            <div class="telegram-info-text">Свяжитесь с нами в Telegram</div>
            <div class="telegram-info-status">Мы сейчас в сети</div>
        </div>
        <button class="telegram-btn" onclick="window.open('https://t.me/your_bot', '_blank')" title="Написать в Telegram">
            <i class="fab fa-telegram-plane"></i>
        </button>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>О компании</h3>
                <a href="/about.php">О нас</a>
                <a href="/portfolio.php">Портфолио</a>
                <a href="/contacts.php">Контакты</a>
            </div>
            <div class="footer-section">
                <h3>Услуги</h3>
                <?php
                $firstHalf = array_slice($categories, 0, ceil(count($categories) / 2));
                foreach ($firstHalf as $cat):
                ?>
                    <a href="/catalog.php?category=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a>
                <?php endforeach; ?>
            </div>

            <div class="footer-section">
                <h3>&nbsp;</h3>
                <?php
                $secondHalf = array_slice($categories, ceil(count($categories) / 2));
                foreach ($secondHalf as $cat):
                ?>
                    <a href="/catalog.php?category=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a>
                <?php endforeach; ?>
            </div>

            <div class="footer-section">
                <h3>Контакты</h3>
                <p><i class="fas fa-phone"></i> +7 (985) 315-20-05</p>
                <p><i class="fas fa-envelope"></i> <?= ADMIN_EMAIL ?></p>
                <p><i class="fas fa-map-marker-alt"></i> Москва, Россия</p>
                <div class="footer-social">
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://t.me/typografia" target="_blank" rel="noopener noreferrer" title="Telegram">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <a href="https://vk.com" target="_blank" rel="noopener noreferrer" title="VKontakte">
                        <i class="fab fa-vk"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-locations-wrapper">
            <h3>Наши точки</h3>
            <?php if (!empty($pickupPoints)): ?>
                <div class="footer-locations-container">
                    <?php foreach ($pickupPoints as $point): ?>
                        <div class="footer-location">
                            <p class="location-name"><i class="fas fa-map-marker-alt"></i> <strong><?= htmlspecialchars($point['name']) ?></strong></p>
                            <p class="location-address"><?= htmlspecialchars($point['address']) ?></p>
                            <?php if (!empty($point['working_hours'])): ?>
                                <p class="location-hours"><i class="far fa-clock"></i> <?= htmlspecialchars($point['working_hours']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Информация о точках скоро появится</p>
            <?php endif; ?>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 <?= SITE_NAME ?>. Все права защищены.</p>
        </div>
    </footer>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.service-card');
            cards.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
