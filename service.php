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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--dark);
            background-color: var(--light-gray);
            line-height: 1.6;
        }

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
        }

        .nav-link:hover {
            color: var(--primary);
        }

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

        .breadcrumbs span {
            margin: 0 0.5rem;
            color: var(--gray);
        }

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
        }

        .service-info h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .service-info p {
            color: var(--gray);
            margin-bottom: 2rem;
        }

        .calculator {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 100px;
        }

        .calculator h3 {
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .price-display {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: center;
        }

        .price-label {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .price-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--success);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
            margin-top: 1rem;
        }

        .features {
            margin-top: 3rem;
        }

        .features h2 {
            margin-bottom: 1.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .feature-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

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
        }

        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
            }

            .calculator {
                position: relative;
                top: 0;
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
                <a href="#" class="nav-link">О нас</a>
                <a href="#" class="nav-link">Контакты</a>
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

    <div class="chat-widget">
        <button class="chat-btn" onclick="window.location.href='chat.php'">
            <i class="fas fa-comments"></i>
        </button>
    </div>

    <script>
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
