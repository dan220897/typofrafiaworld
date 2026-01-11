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
    $stmt = $db->prepare("SELECT * FROM service_density WHERE service_id = ? ORDER BY id");
    $stmt->execute([$serviceId]);
    $densities = $stmt->fetchAll();
    if ($densities) $params['densities'] = $densities;

    // Стороны печати
    $stmt = $db->prepare("SELECT * FROM service_sides WHERE service_id = ? ORDER BY id");
    $stmt->execute([$serviceId]);
    $sides = $stmt->fetchAll();
    if ($sides) $params['sides'] = $sides;

    // Количество
    $stmt = $db->prepare("SELECT * FROM service_quantities WHERE service_id = ? ORDER BY id");
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

        <?php
        // Определяем цвета для каждой категории
        $categoryColors = [
            'Визитки' => '#FF6B6B',
            'Баннеры' => '#4ECDC4',
            'Флаеры' => '#FFD93D',
            'Листовки' => '#95E1D3',
            'Буклеты' => '#A8E6CF',
            'Брошюры' => '#FF8B94',
            'Календари' => '#6C5CE7',
            'Блокноты' => '#74B9FF',
            'Наклейки' => '#FD79A8',
            'Сувенирная продукция' => '#FDCB6E',
            'Вывески' => '#00B894',
            'Каталоги' => '#A29BFE',
            'Копирование документов' => '#55EFC4',
            'Дизайн и дополнительные услуги' => '#FF7675'
        ];

        $currentColor = $categoryColors[$service['category']] ?? '#FF6B6B';
        ?>

        :root {
            --accent: <?= $currentColor ?>;
            --bg-dark: #0F172A;
            --bg-card: #1E293B;
            --bg-light: #F8FAFC;
            --text-dark: #0F172A;
            --text-light: #64748B;
            --text-white: #F8FAFC;
            --border: #E2E8F0;
            --success: #10B981;
            --white: #FFFFFF;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(60px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text-dark);
            background: var(--bg-light);
            line-height: 1.7;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.25rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-dark);
            text-decoration: none;
            letter-spacing: -0.5px;
            transition: color 0.2s;
        }

        .logo i {
            color: var(--accent);
            margin-right: 0.5rem;
        }

        .logo:hover {
            color: var(--accent);
        }

        .header-nav {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
            position: relative;
        }

        .nav-link:hover {
            color: var(--accent);
        }

        .breadcrumbs {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem 2.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumbs a {
            color: var(--text-light);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumbs a:hover {
            color: var(--accent);
        }

        .breadcrumbs span {
            color: var(--border);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 3rem 2.5rem 5rem;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 4rem;
            align-items: start;
        }

        .service-info {
            animation: slideUp 0.6s ease-out;
        }

        .service-info h1 {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .service-info h1::before {
            content: '';
            display: block;
            width: 60px;
            height: 6px;
            background: var(--accent);
            margin-bottom: 2rem;
            border-radius: 3px;
        }

        .service-info > p {
            color: var(--text-light);
            font-size: 1.25rem;
            margin-bottom: 3rem;
            line-height: 1.8;
        }

        /* Calculator Card */
        .calculator {
            background: var(--white);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: sticky;
            top: 120px;
            animation: scaleIn 0.6s ease-out 0.2s backwards;
        }

        .calculator h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 2rem;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--text-dark);
            transition: all 0.2s;
            background: var(--white);
            font-family: inherit;
        }

        .form-group select:hover,
        .form-group input:hover {
            border-color: var(--accent);
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1);
        }

        /* Price Display */
        .price-display {
            background: var(--bg-dark);
            padding: 2.5rem;
            border-radius: 20px;
            margin: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .price-display::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent);
        }

        .price-label {
            color: var(--text-white);
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .price-value {
            font-size: 3rem;
            font-weight: 900;
            color: var(--white);
            letter-spacing: -1px;
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 1.25rem;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(255, 107, 107, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.3);
        }

        .btn-success:active {
            transform: translateY(0);
        }

        /* Features Section */
        .features {
            margin-top: 5rem;
        }

        .features h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 3rem;
            letter-spacing: -0.5px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            border: 2px solid var(--border);
            transition: all 0.3s;
            opacity: 0;
        }

        .feature-card.visible {
            animation: slideUp 0.6s ease-out forwards;
        }

        .feature-card:nth-child(1).visible { animation-delay: 0.1s; }
        .feature-card:nth-child(2).visible { animation-delay: 0.2s; }
        .feature-card:nth-child(3).visible { animation-delay: 0.3s; }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            border-color: var(--accent);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--accent);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--white);
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Telegram Widget */
        .telegram-widget {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 99;
            animation: float 3s ease-in-out infinite;
        }

        .telegram-info {
            display: none;
        }

        .telegram-btn {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--white);
            border: none;
            font-size: 1.875rem;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .telegram-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
        }

        .telegram-btn:active {
            transform: scale(0.95);
        }

        /* Responsive - Tablets & Large Phones */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                gap: 3rem;
                padding: 2rem 1.5rem 4rem;
            }

            .calculator {
                position: relative;
                top: 0;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Responsive - Mobile */
        @media (max-width: 768px) {
            .header-container {
                padding: 1rem 1.25rem;
                flex-wrap: wrap;
            }

            .logo {
                font-size: 1.5rem;
            }

            .header-nav {
                width: 100%;
                justify-content: space-between;
                margin-top: 1rem;
                gap: 1rem;
            }

            .nav-link {
                font-size: 0.875rem;
            }

            .breadcrumbs {
                padding: 1rem 1.25rem;
                font-size: 0.8125rem;
            }

            .container {
                padding: 2rem 1.25rem 3rem;
            }

            .service-info h1 {
                font-size: 2.5rem;
            }

            .service-info h1::before {
                width: 50px;
                height: 5px;
                margin-bottom: 1.5rem;
            }

            .service-info > p {
                font-size: 1.125rem;
            }

            .calculator {
                padding: 2rem;
            }

            .calculator h3 {
                font-size: 1.5rem;
            }

            .price-value {
                font-size: 2.5rem;
            }

            .features {
                margin-top: 3rem;
            }

            .features h2 {
                font-size: 2rem;
            }

            .feature-card {
                padding: 2rem;
            }

            .telegram-widget {
                bottom: 1.5rem;
                right: 1.5rem;
            }

            .telegram-btn {
                width: 56px;
                height: 56px;
                font-size: 1.5rem;
            }
        }

        /* Responsive - Small Mobile */
        @media (max-width: 480px) {
            .header-container {
                padding: 1rem;
            }

            .logo {
                font-size: 1.25rem;
            }

            .header-nav {
                gap: 0.75rem;
            }

            .nav-link {
                font-size: 0.8125rem;
            }

            .breadcrumbs {
                padding: 0.875rem 1rem;
                font-size: 0.75rem;
            }

            .container {
                padding: 1.5rem 1rem 2.5rem;
                gap: 2.5rem;
            }

            .service-info h1 {
                font-size: 2rem;
            }

            .service-info h1::before {
                width: 40px;
                height: 4px;
                margin-bottom: 1.25rem;
            }

            .service-info > p {
                font-size: 1rem;
                margin-bottom: 2rem;
            }

            .calculator {
                padding: 1.5rem;
                border-radius: 20px;
            }

            .calculator h3 {
                font-size: 1.375rem;
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-group label {
                font-size: 0.8125rem;
                margin-bottom: 0.625rem;
            }

            .form-group select,
            .form-group input {
                padding: 0.875rem 1rem;
                font-size: 0.9375rem;
            }

            .price-display {
                padding: 2rem;
            }

            .price-label {
                font-size: 0.8125rem;
            }

            .price-value {
                font-size: 2.25rem;
            }

            .btn {
                padding: 1.125rem;
                font-size: 1rem;
            }

            .features {
                margin-top: 2.5rem;
            }

            .features h2 {
                font-size: 1.75rem;
                margin-bottom: 2rem;
            }

            .feature-card {
                padding: 1.75rem;
            }

            .feature-icon {
                width: 52px;
                height: 52px;
                font-size: 1.5rem;
                margin-bottom: 1.25rem;
            }

            .feature-card h3 {
                font-size: 1.125rem;
            }

            .feature-card p {
                font-size: 0.9375rem;
            }

            .telegram-widget {
                bottom: 1.25rem;
                right: 1.25rem;
            }

            .telegram-btn {
                width: 52px;
                height: 52px;
                font-size: 1.375rem;
            }
        }

        /* Responsive - Very Small Mobile */
        @media (max-width: 360px) {
            .service-info h1 {
                font-size: 1.75rem;
            }

            .calculator h3 {
                font-size: 1.25rem;
            }

            .price-value {
                font-size: 2rem;
            }

            .features h2 {
                font-size: 1.5rem;
            }
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
                <a href="/" class="nav-link">Каталог</a>
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

        async function addToCartFromService() {
            // Получаем данные из калькулятора
            const sizeSelect = document.getElementById('size');
            const densitySelect = document.getElementById('density');
            const sidesSelect = document.getElementById('sides');
            const quantitySelect = document.getElementById('quantity');

            // Собираем параметры
            const parameters = {};
            if (sizeSelect) parameters.size = sizeSelect.options[sizeSelect.selectedIndex].text;
            if (densitySelect) parameters.density = densitySelect.options[densitySelect.selectedIndex].text;
            if (sidesSelect) parameters.sides = sidesSelect.options[sidesSelect.selectedIndex].text;
            if (quantitySelect) {
                parameters.quantity = quantitySelect.options[quantitySelect.selectedIndex].text;
                parameters.quantityValue = parseInt(quantitySelect.getAttribute('data-quantity') || 1);
            }

            // Получаем итоговую цену
            const totalPriceText = document.getElementById('totalPrice').textContent;
            const totalPrice = parseFloat(totalPriceText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;

            if (totalPrice === 0) {
                alert('Пожалуйста, выберите параметры услуги');
                return;
            }

            // Добавляем в корзину
            const success = await addToCart(
                '<?= $serviceId ?>',
                parameters.quantityValue || 1,
                totalPrice / (parameters.quantityValue || 1),
                parameters
            );

            if (success) {
                // Открываем попап корзины
                openCartPopup();
            }
        }
    </script>
</body>
</html>
