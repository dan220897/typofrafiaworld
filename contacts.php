<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;

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
    <title>Контакты - <?= SITE_NAME ?></title>
    <meta name="description" content="Свяжитесь с нами. Адреса офисов, телефоны, email, форма обратной связи.">
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
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .header {
            
           
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

        .nav-link.active,
        .nav-link:hover { color: var(--primary); }

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

        .nav-link.active::after,
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
            color:black;
        }

        .btn-primary:hover {
            
        }

        .hero {
            background: var(--primary);
            color: var(--white);
            padding: 5rem 2rem 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.4;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.25rem;
            opacity: 0.95;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .contacts-section {
            padding: 5rem 0;
        }

        .contacts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .location-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .location-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .location-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .location-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .location-name i {
            color: var(--primary);
        }

        .location-detail {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--gray);
        }

        .location-detail i {
            color: var(--primary);
            font-size: 1.1rem;
            margin-top: 0.25rem;
        }

        .location-detail-text {
            flex: 1;
        }

        .location-detail-text strong {
            color: var(--dark);
            display: block;
            margin-bottom: 0.25rem;
        }

        .location-detail a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .location-detail a:hover {
            color: var(--primary-hover);
        }

        .location-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light-gray);
        }

        .location-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-telegram {
            background: linear-gradient(135deg, #0088cc, #229ED9);
            color: var(--white);
        }

        .btn-telegram:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 136, 204, 0.3);
        }

        .btn-call {
            background: linear-gradient(135deg, var(--success), #059669);
            color: var(--white);
        }

        .btn-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
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
            .contacts-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
        }

        @media (max-width: 768px) {
            .header-container { padding: 1rem; }
            .header-nav { gap: 0.75rem; font-size: 0.85rem; }
            .logo { font-size: 1.25rem; }
            .hero { padding: 3rem 1rem 2rem; }
            .hero h1 { font-size: 2rem; }
            .hero p { font-size: 1rem; }
            .contacts-section { padding: 3rem 0; }
        }

        @media (max-width: 480px) {
            .header-nav a:not(.btn) { display: none; }
            .hero h1 { font-size: 1.5rem; }
            .btn { padding: 0.5rem 1.25rem; font-size: 0.85rem; }
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

    

    <section class="contacts-section">
        <div class="container">
            <div class="contacts-grid">
                <?php if (!empty($pickupPoints)): ?>
                    <?php foreach ($pickupPoints as $point): ?>
                        <div class="location-card">
                            <div class="location-header">
                                <h3 class="location-name">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($point['name']) ?>
                                </h3>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-location-dot"></i>
                                <div class="location-detail-text">
                                    <strong>Адрес</strong>
                                    <?= htmlspecialchars($point['address']) ?>
                                </div>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-phone"></i>
                                <div class="location-detail-text">
                                    <strong>Телефон</strong>
                                    <a href="tel:<?= htmlspecialchars($point['phone']) ?>"><?= htmlspecialchars($point['phone']) ?></a>
                                </div>
                            </div>

                            <div class="location-detail">
                                <i class="fas fa-clock"></i>
                                <div class="location-detail-text">
                                    <strong>Часы работы</strong>
                                    <?= htmlspecialchars($point['working_hours']) ?>
                                </div>
                            </div>

                            <div class="location-actions">
                                <a href="tel:<?= htmlspecialchars($point['phone']) ?>" class="location-btn btn-call">
                                    <i class="fas fa-phone"></i>
                                    Позвонить
                                </a>
                                <a href="<?= MANAGER_TELEGRAM_LINK ?>" target="_blank" class="location-btn btn-telegram">
                                    <i class="fab fa-telegram"></i>
                                    Telegram
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Точки самовывоза не найдены</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

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

</body>
</html>
