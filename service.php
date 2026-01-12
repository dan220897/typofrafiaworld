<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;

// Получаем ID услуги
$serviceId = $_GET['id'] ?? '';

if (empty($serviceId)) {
    header('Location: /');
    exit;
}

// Получаем информацию об услуге
try {
    $db = Database::getInstance()->getConnection();

    // Получаем базовую информацию
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        header('Location: /');
        exit;
    }

    // Получаем параметры услуги
    $params = [];

    // Размеры
    $stmt = $db->prepare("SELECT * FROM service_sizes WHERE service_id = ? ORDER BY sort_order");
    $stmt->execute([$serviceId]);
    $sizes = $stmt->fetchAll();
    if ($sizes) $params['sizes'] = $sizes;

    // Плотность
    $stmt = $db->prepare("SELECT * FROM service_density WHERE service_id = ? ORDER BY CAST(SUBSTRING_INDEX(label, ' ', 1) AS UNSIGNED) ASC");
    $stmt->execute([$serviceId]);
    $densities = $stmt->fetchAll();
    if ($densities) $params['densities'] = $densities;

    // Стороны печати
    $stmt = $db->prepare("SELECT * FROM service_sides WHERE service_id = ? ORDER BY id");
    $stmt->execute([$serviceId]);
    $sides = $stmt->fetchAll();
    if ($sides) $params['sides'] = $sides;

    // Количество
    $stmt = $db->prepare("
        SELECT id, service_id, quantity, label, multiplier, price
        FROM service_quantities
        WHERE service_id = ?
        GROUP BY quantity, label
        ORDER BY CAST(quantity AS UNSIGNED) ASC
    ");
    $stmt->execute([$serviceId]);
    $quantities = $stmt->fetchAll();
    if ($quantities) $params['quantities'] = $quantities;

    // Базовая цена
    $stmt = $db->prepare("SELECT base_price FROM service_base_prices WHERE service_id = ? LIMIT 1");
    $stmt->execute([$serviceId]);
    $basePriceRow = $stmt->fetch();
    $basePrice = $basePriceRow ? $basePriceRow['base_price'] : 0;

} catch (Exception $e) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['label']) ?> - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            overflow-x: hidden;
        }

        <?php
        // Определяем цвета для каждой категории
        $categoryColors = [
            'Визитки' => '#6366f1',
            'Баннеры' => '#ec4899',
            'Флаеры' => '#f59e0b',
            'Листовки' => '#10b981',
            'Буклеты' => '#8b5cf6',
            'Брошюры' => '#ef4444',
            'Календари' => '#3b82f6',
            'Блокноты' => '#14b8a6',
            'Наклейки' => '#f97316',
            'Сувенирная продукция' => '#06b6d4',
            'Вывески' => '#84cc16',
            'Каталоги' => '#a855f7',
            'Копирование документов' => '#6366f1',
            'Дизайн и дополнительные услуги' => '#ec4899'
        ];

        $currentColor = $categoryColors[$service['category']] ?? '#6366f1';
        ?>

        :root {
            --category-color: <?= $currentColor ?>;
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
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--dark);
            background: #f5f7fa;
            
            min-height: 100vh;
            overflow-x: hidden;
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

        .logo:hover {
            transform: scale(1.05);
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

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .breadcrumbs {
            max-width: 1200px;
            margin: 2rem auto 0;
            font-size: 0.9rem;
            animation: fadeInUp 0.6s ease-out;
        }

        .breadcrumbs a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumbs a:hover {
            color: var(--category-color);
        }

        .breadcrumbs span {
            margin: 0 0.5rem;
            color: var(--gray);
        }

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 3rem;
        }

        .service-info {
            animation: fadeInUp 0.8s ease-out;
        }

        .service-info h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
            line-height: 1.2;
        }

        .service-info > p {
            color: var(--gray);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .calculator {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            position: sticky;
            top: 120px;
            animation: slideInRight 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .calculator h3 {
            margin-bottom: 1.5rem;
            color: var(--dark);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .calculator h3::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 24px;
            background: var(--primary);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            animation: fadeInUp 1s ease-out backwards;
        }

        .form-group:nth-child(2) { animation-delay: 0.1s; }
        .form-group:nth-child(3) { animation-delay: 0.2s; }
        .form-group:nth-child(4) { animation-delay: 0.3s; }
        .form-group:nth-child(5) { animation-delay: 0.4s; }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-group select:hover,
        .form-group input:hover {
            border-color: var(--category-color);
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--category-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .price-display {
            background: var(--primary);
            padding: 2rem;
            border-radius: 16px;
            margin: 1.5rem 0;
            text-align: center;
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 1.2s ease-out backwards;
            animation-delay: 0.5s;
        }

        .price-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .price-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

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
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        
        .btn-success:active {
            transform: translateY(0);
        }

        .features {
            margin-top: 3rem;
        }

        .features h2 {
            margin-bottom: 2rem;
            font-size: 2rem;
            color: var(--dark);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.8);
            opacity: 0;
        }

        .feature-card.visible {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .feature-card:nth-child(1).visible { animation-delay: 0.1s; }
        .feature-card:nth-child(2).visible { animation-delay: 0.2s; }
        .feature-card:nth-child(3).visible { animation-delay: 0.3s; }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray);
            line-height: 1.6;
        }

        /* Telegram Widget */
        .telegram-widget {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 99;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: fadeIn 1s ease-out;
        }

        .telegram-info {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 1rem 1.5rem;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
            pointer-events: none;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .telegram-widget:hover .telegram-info {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
        }

        .telegram-info-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .telegram-info-status {
            font-size: 0.85rem;
            color: var(--success);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .telegram-info-status::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
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
            box-shadow: var(--shadow-xl);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .telegram-btn:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 30px rgba(0, 136, 204, 0.4);
        }

        .telegram-btn:active {
            transform: scale(0.95);
        }

        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding-bottom: 2rem;
            }

            .calculator {
                position: relative;
                top: 0;
                box-shadow: var(--shadow);
            }

            .service-info h1 {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .telegram-widget {
                bottom: 1.5rem;
                right: 1.5rem;
            }

            .telegram-info {
                display: none;
            }

            .telegram-btn {
                width: 56px;
                height: 56px;
                font-size: 1.5rem;
            }

            .header-nav {
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 1rem;
                flex-wrap: wrap;
            }

            .logo {
                font-size: 1.25rem;
            }

            .header-nav {
                
                justify-content: space-around;
                margin-top: 0.5rem;
                gap: 0.5rem;
                font-size: 0.85rem;
            }

            .nav-link {
                font-size: 0.85rem;
                padding: 0.25rem;
            }

            .nav-link::after {
                display: none;
            }

            .container {
                padding: 0 1rem 2rem;
                margin: 1.5rem auto;
            }

            .breadcrumbs {
                
               
                
            }

            .service-info h1 {
                font-size: 1.75rem;
                line-height: 1.3;
            }

            .service-info > p {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }

            .calculator {
                padding: 1.25rem;
                border-radius: 16px;
            }

            .calculator h3 {
                font-size: 1.25rem;
                margin-bottom: 1.25rem;
            }

            .form-group {
                margin-bottom: 1.25rem;
            }

            .form-group label {
                font-size: 0.9rem;
                margin-bottom: 0.4rem;
            }

            .form-group select,
            .form-group input {
                padding: 0.75rem 0.875rem;
                font-size: 0.95rem;
                border-radius: 10px;
            }

            .price-display {
                padding: 1.25rem;
                border-radius: 12px;
                margin: 1.25rem 0;
            }

            .price-label {
                font-size: 0.9rem;
            }

            .price-value {
                font-size: 2rem;
            }

            .btn {
                padding: 1rem;
                font-size: 1rem;
                border-radius: 10px;
            }

            .features {
                margin-top: 2rem;
            }

            .features h2 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .feature-card {
                padding: 1.5rem;
                border-radius: 12px;
            }

            .feature-icon {
                font-size: 2rem;
            }

            .feature-card h3 {
                font-size: 1.1rem;
            }

            .feature-card p {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }

            .logo {
                font-size: 1.1rem;
            }

            .header-container {
                padding: 0.875rem;
            }

            .header-nav {
                gap: 0.25rem;
                font-size: 0.8rem;
            }

            .nav-link {
                font-size: 0.8rem;
                padding: 0.2rem;
            }

            .breadcrumbs {
                
            }

            .container {
                padding: 0 0.875rem 1.5rem;
                margin: 1rem auto;
                gap: 1.5rem;
            }

            .service-info h1 {
                font-size: 1.5rem;
                line-height: 1.3;
            }

            .service-info > p {
                font-size: 0.95rem;
                margin-bottom: 1.25rem;
            }

            .calculator {
                padding: 1rem;
                border-radius: 14px;
            }

            .calculator h3 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .calculator h3::before {
                height: 20px;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                font-size: 0.85rem;
                margin-bottom: 0.35rem;
            }

            .form-group select,
            .form-group input {
                padding: 0.675rem 0.75rem;
                font-size: 0.9rem;
                border-radius: 8px;
            }

            .price-display {
                padding: 1rem;
                border-radius: 10px;
                margin: 1rem 0;
            }

            .price-label {
                font-size: 0.85rem;
            }

            .price-value {
                font-size: 1.75rem;
            }

            .btn {
                padding: 0.875rem;
                font-size: 0.95rem;
                border-radius: 8px;
            }

            .features {
                margin-top: 1.5rem;
            }

            .features h2 {
                font-size: 1.25rem;
                margin-bottom: 1.25rem;
            }

            .feature-card {
                padding: 1.25rem;
                border-radius: 10px;
            }

            .feature-icon {
                font-size: 1.75rem;
            }

            .feature-card h3 {
                font-size: 1rem;
            }

            .feature-card p {
                font-size: 0.9rem;
            }

            .telegram-widget {
                bottom: 0.875rem;
                right: 0.875rem;
            }

            .telegram-btn {
                width: 50px;
                height: 50px;
                font-size: 1.15rem;
            }
        }

        @media (max-width: 360px) {
            .header-nav {
                font-size: 0.75rem;
            }

            .nav-link {
                font-size: 0.75rem;
            }

            .service-info h1 {
                font-size: 1.35rem;
            }

            .calculator h3 {
                font-size: 1rem;
            }

            .price-value {
                font-size: 1.5rem;
            }

            .btn {
                font-size: 0.9rem;
            }
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
                <i class="fas fa-print"></i> <?= SITE_NAME ?>
            </a>
            <nav class="header-nav">
                <?php include 'components/mega-menu.php'; ?>
                <a href="/portfolio.php" class="nav-link">Портфолио</a>
                <a href="/about.php" class="nav-link">О нас</a>
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
        <a href="catalog.php?category=<?= urlencode($service['category']) ?>"><?= htmlspecialchars($service['category']) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($service['label']) ?></span>
    </div>

    <div class="container">
        <div class="service-info">
            <h1><?= htmlspecialchars($service['label']) ?></h1>
            <p>Рассчитайте стоимость и оформите заказ онлайн</p>

            <div class="features">
                <h2>Преимущества</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-clock"></i></div>
                        <h3>Быстрое изготовление</h3>
                        <p>От 1 рабочего дня</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                        <h3>Высокое качество</h3>
                        <p>Современное оборудование</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-truck"></i></div>
                        <h3>Доставка</h3>
                        <p>По всей России</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="calculator">
            <h3>Калькулятор стоимости</h3>

            <?php if (!empty($params['sizes'])): ?>
            <div class="form-group">
                <label>Размер</label>
                <select id="size" onchange="calculatePrice()">
                    <option value="">Выберите размер</option>
                    <?php foreach ($params['sizes'] as $size): ?>
                        <option value="<?= $size['id'] ?>" data-price="<?= $size['price'] ?>">
                            <?= htmlspecialchars($size['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if (!empty($params['densities'])): ?>
            <div class="form-group">
                <label>Плотность бумаги</label>
                <select id="density" onchange="calculatePrice()">
                    <option value="">Выберите плотность</option>
                    <?php foreach ($params['densities'] as $density): ?>
                        <option value="<?= $density['id'] ?>" data-price="<?= $density['price'] ?>">
                            <?= htmlspecialchars($density['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if (!empty($params['sides'])): ?>
            <div class="form-group">
                <label>Печать</label>
                <select id="sides" onchange="calculatePrice()">
                    <option value="">Выберите вариант</option>
                    <?php foreach ($params['sides'] as $side): ?>
                        <option value="<?= $side['id'] ?>" data-multiplier="<?= $side['multiplier'] ?>">
                            <?= htmlspecialchars($side['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if (!empty($params['quantities'])): ?>
            <div class="form-group">
                <label>Тираж</label>
                <select id="quantity" onchange="calculatePrice()">
                    <option value="">Выберите тираж</option>
                    <?php foreach ($params['quantities'] as $qty): ?>
                        <option value="<?= $qty['id'] ?>" data-quantity="<?= $qty['quantity'] ?>" data-multiplier="<?= $qty['multiplier'] ?? 1 ?>" data-price="<?= $qty['price'] ?>">
                            <?= htmlspecialchars($qty['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="price-display">
                <div class="price-label">Итоговая стоимость:</div>
                <div class="price-value" id="totalPrice">-</div>
            </div>

            <button class="btn btn-success" onclick="addToCartFromService()">
                <i class="fas fa-shopping-cart"></i> Добавить в корзину
            </button>
        </div>
    </div>

    <div class="telegram-widget">
        <div class="telegram-info">
            <div class="telegram-info-title">Свяжитесь с нами в Telegram</div>
            <div class="telegram-info-status">Мы онлайн сейчас</div>
        </div>
        <button class="telegram-btn" onclick="window.open('https://t.me/yourusername', '_blank')">
            <i class="fab fa-telegram-plane"></i>
        </button>
    </div>

    <script>
        // Intersection Observer для анимации карточек преимуществ
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card').forEach(card => {
            observer.observe(card);
        });

        const basePrice = <?= $basePrice ?>;

        // Определяем базовый тираж (первый в списке quantities)
        let baseQuantity = 1;
        const quantitySelect = document.getElementById('quantity');
        if (quantitySelect && quantitySelect.options.length > 1) {
            // Первый option - это "Выберите тираж", берем второй
            baseQuantity = parseFloat(quantitySelect.options[1].dataset.quantity || 1);
        }

        function calculatePrice() {
            let total = basePrice;

            // Размер
            const sizeSelect = document.getElementById('size');
            if (sizeSelect && sizeSelect.value) {
                const sizeOption = sizeSelect.options[sizeSelect.selectedIndex];
                total += parseFloat(sizeOption.dataset.price || 0);
            }

            // Плотность
            const densitySelect = document.getElementById('density');
            if (densitySelect && densitySelect.value) {
                const densityOption = densitySelect.options[densitySelect.selectedIndex];
                total += parseFloat(densityOption.dataset.price || 0);
            }

            // Стороны (множитель)
            const sidesSelect = document.getElementById('sides');
            let sidesMultiplier = 1;
            if (sidesSelect && sidesSelect.value) {
                const sidesOption = sidesSelect.options[sidesSelect.selectedIndex];
                sidesMultiplier = parseFloat(sidesOption.dataset.multiplier || 1);
            }

            total *= sidesMultiplier;

            // Количество
            if (quantitySelect && quantitySelect.value) {
                const quantityOption = quantitySelect.options[quantitySelect.selectedIndex];
                const qtyCount = parseFloat(quantityOption.dataset.quantity || 1);
                const qtyMultiplier = parseFloat(quantityOption.dataset.multiplier || 1);
                const qtyPrice = parseFloat(quantityOption.dataset.price || 0);

                // Формула: (базовая_цена + доп_цена) × (количество / базовый_тираж) × множитель_скидки
                // Пример для визиток: (500₽ + 0₽) × (500 / 100) × 0.80 = 500 × 5 × 0.80 = 2000₽
                // Пример для печати: (3₽ + 0₽) × (10 / 1) × 1.00 = 3 × 10 × 1.00 = 30₽
                total = (total + qtyPrice) * (qtyCount / baseQuantity) * qtyMultiplier;
            }

            // Отображаем цену
            document.getElementById('totalPrice').textContent =
                new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 })
                    .format(total);
        }

        // Вызываем calculatePrice при загрузке страницы для отображения базовой цены
        calculatePrice();

        async function addToCartFromService() {
            // Получаем данные из калькулятора
            const sizeSelect = document.getElementById('size');
            const densitySelect = document.getElementById('density');
            const sidesSelect = document.getElementById('sides');
            const quantitySelect = document.getElementById('quantity');

            // Собираем параметры
            const parameters = {};
            let quantityValue = 1; // По умолчанию 1

            if (sizeSelect && sizeSelect.value) {
                parameters.size = sizeSelect.options[sizeSelect.selectedIndex].text;
            }
            if (densitySelect && densitySelect.value) {
                parameters.density = densitySelect.options[densitySelect.selectedIndex].text;
            }
            if (sidesSelect && sidesSelect.value) {
                parameters.sides = sidesSelect.options[sidesSelect.selectedIndex].text;
            }
            if (quantitySelect && quantitySelect.value) {
                const selectedOption = quantitySelect.options[quantitySelect.selectedIndex];
                parameters.quantity = selectedOption.text;
                quantityValue = parseInt(selectedOption.dataset.quantity || 1);
            }

            // Получаем итоговую цену
            const totalPriceText = document.getElementById('totalPrice').textContent;
            const totalPrice = parseFloat(totalPriceText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;

            if (totalPrice === 0 || isNaN(totalPrice)) {
                alert('Пожалуйста, подождите расчета стоимости');
                return;
            }

            // Добавляем в корзину
            const success = await addToCart(
                '<?= $serviceId ?>',
                quantityValue,
                totalPrice / quantityValue,
                parameters
            );

            if (success) {
                // Открываем попап корзины
                openCartPopup();
            }
        }
    </script>
    
    <!-- Login Modal -->
    <div id="authModal" class="auth-modal">
        <div class="auth-modal-content">
            <div class="auth-modal-header">
                <h3>Вход в личный кабинет</h3>
                <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>
            </div>
            <div class="auth-modal-body">
                <p class="auth-description">Введите email для получения кода подтверждения</p>
                <form id="authForm" onsubmit="handleAuth(event)">
                    <div class="form-group">
                        <label for="authEmail">Email</label>
                        <input
                            type="email"
                            id="authEmail"
                            name="email"
                            class="form-input"
                            placeholder="example@mail.com"
                            required
                        >
                    </div>
                    <div class="form-group" id="codeGroup" style="display: none;">
                        <label for="authCode">Код подтверждения</label>
                        <input
                            type="text"
                            id="authCode"
                            name="code"
                            class="form-input"
                            placeholder="Введите код из письма"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="authSubmitBtn">
                        Получить код
                    </button>
                </form>
                <div id="authMessage" class="auth-message"></div>
            </div>
        </div>
    </div>

    <style>
        /* Auth Modal */
        .auth-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .auth-modal.active {
            display: flex;
        }

        .auth-modal-content {
            background: var(--white);
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            box-shadow: var(--shadow-xl);
            animation: fadeInUp 0.4s ease;
        }

        .auth-modal-header {
            padding: 2rem 2rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--light-gray);
        }

        .auth-modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .auth-modal-close {
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s ease;
            line-height: 1;
            padding: 0;
            width: 30px;
            height: 30px;
        }

        .auth-modal-close:hover {
            color: var(--dark);
            transform: rotate(90deg);
        }

        .auth-modal-body {
            padding: 2rem;
        }

        .auth-description {
            color: var(--gray);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-block {
            width: 100%;
        }

        .auth-message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            display: none;
        }

        .auth-message.success {
            background: #d1fae5;
            color: #065f46;
            display: block;
        }

        .auth-message.error {
            background: #fee2e2;
            color: #991b1b;
            display: block;
        }

        @media (max-width: 768px) {
            .auth-modal-content {
                width: 95%;
                max-width: none;
                margin: 1rem;
            }

            .auth-modal-header {
                padding: 1.5rem;
            }

            .auth-modal-body {
                padding: 1.5rem;
            }
        }
    </style>

    <script>
        // Все услуги для поиска
        const allServices = <?= json_encode($allServices, JSON_UNESCAPED_UNICODE) ?>;

        // Поиск по категориям и услугам (подкатегориям)
        function handleSearch() {
            const searchText = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.category-card');

            if (!searchText) {
                // Если поиск пустой, показываем все категории
                cards.forEach(card => {
                    card.style.display = 'block';
                });
                return;
            }

            // Создаем множество категорий, которые должны быть видны
            const visibleCategories = new Set();

            // 1. Ищем совпадения в названиях категорий
            cards.forEach(card => {
                const categoryName = card.querySelector('.category-name').textContent.toLowerCase();
                if (categoryName.includes(searchText)) {
                    visibleCategories.add(categoryName);
                }
            });

            // 2. Ищем совпадения в названиях услуг (подкатегорий)
            allServices.forEach(service => {
                const serviceLabel = service.label.toLowerCase();
                if (serviceLabel.includes(searchText)) {
                    // Если нашли совпадение в услуге, добавляем её категорию к видимым
                    visibleCategories.add(service.category.toLowerCase());
                }
            });

            // 3. Показываем/скрываем категории
            cards.forEach(card => {
                const categoryName = card.querySelector('.category-name').textContent.toLowerCase();
                if (visibleCategories.has(categoryName)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Открыть Telegram
        function openTelegram() {
            // TODO: Заменить на реальную ссылку на Telegram бота или группу
            window.open('https://t.me/your_bot', '_blank');
        }

        // Показать модальное окно авторизации
        function showAuthModal() {
            document.getElementById('authModal').classList.add('active');
            document.getElementById('authEmail').focus();
        }

        // Закрыть модальное окно
        function closeAuthModal() {
            document.getElementById('authModal').classList.remove('active');
            document.getElementById('authForm').reset();
            document.getElementById('codeGroup').style.display = 'none';
            document.getElementById('authSubmitBtn').textContent = 'Получить код';
            document.getElementById('authMessage').className = 'auth-message';
            document.getElementById('authMessage').textContent = '';
        }

        // Закрыть модалку по клику вне её
        window.onclick = function(event) {
            const modal = document.getElementById('authModal');
            if (event.target === modal) {
                closeAuthModal();
            }
        }

        // Обработка авторизации
        let authStep = 'email'; // email или code

        async function handleAuth(event) {
            event.preventDefault();

            const email = document.getElementById('authEmail').value;
            const code = document.getElementById('authCode').value;
            const messageEl = document.getElementById('authMessage');
            const submitBtn = document.getElementById('authSubmitBtn');

            if (authStep === 'email') {
                // Отправка email для получения кода
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправка...';

                try {
                    const response = await fetch('/api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'send_code', email: email })
                    });

                    const data = await response.json();

                    if (data.success) {
                        messageEl.className = 'auth-message success';
                        messageEl.textContent = 'Код отправлен на ' + email;
                        document.getElementById('codeGroup').style.display = 'block';
                        submitBtn.textContent = 'Войти';
                        authStep = 'code';
                    } else {
                        messageEl.className = 'auth-message error';
                        messageEl.textContent = data.message || 'Ошибка отправки кода';
                        submitBtn.textContent = 'Получить код';
                    }
                } catch (error) {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = 'Ошибка соединения';
                    submitBtn.textContent = 'Получить код';
                }

                submitBtn.disabled = false;
            } else {
                // Проверка кода
                submitBtn.disabled = true;
                submitBtn.textContent = 'Проверка...';

                try {
                    const response = await fetch('/api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'verify_code', email: email, code: code })
                    });

                    const data = await response.json();

                    if (data.success) {
                        messageEl.className = 'auth-message success';
                        messageEl.textContent = 'Вход выполнен успешно!';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        messageEl.className = 'auth-message error';
                        messageEl.textContent = data.message || 'Неверный код';
                        submitBtn.textContent = 'Войти';
                    }
                } catch (error) {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = 'Ошибка соединения';
                    submitBtn.textContent = 'Войти';
                }

                submitBtn.disabled = false;
            }
        }

        // Анимация элементов при прокрутке
        function animateOnScroll() {
            const cards = document.querySelectorAll('.category-card');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.animation = `fadeInUp 0.6s ease forwards`;
                            entry.target.style.opacity = '1';
                        }, index * 100);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            cards.forEach(card => {
                card.style.opacity = '0';
                observer.observe(card);
            });
        }

        // Запускаем анимацию при загрузке страницы
        window.addEventListener('DOMContentLoaded', () => {
            animateOnScroll();
        });
    </script>
</body>
</html>
