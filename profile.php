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
    <title>Мой профиль - <?= SITE_NAME ?></title>
    <meta name="description" content="Личный профиль пользователя">
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

        .logo img {
            height: 50px;
            width: auto;
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
            color: black;
        }

        .hero {
            background: var(--primary);
            color: var(--white);
            padding: 3rem 2rem 2rem;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .profile-section {
            padding: 3rem 0;
        }

        .profile-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--light-gray);
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2.5rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .profile-header-info h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .profile-header-info p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        .profile-info-grid {
            display: grid;
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-gray);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #f3f4f6;
            transform: translateX(5px);
        }

        .info-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .info-value {
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 600;
        }

        .edit-notice {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-left: 4px solid var(--warning);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .edit-notice i {
            color: var(--warning);
            font-size: 1.5rem;
            margin-top: 0.25rem;
        }

        .edit-notice-content {
            flex: 1;
        }

        .edit-notice-content h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .edit-notice-content p {
            color: #78716c;
            margin-bottom: 0.75rem;
        }

        .telegram-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #0088cc, #229ED9);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .telegram-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 136, 204, 0.3);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-orders {
            background: var(--primary);
            color: var(--white);
        }

        .btn-orders:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }

        .btn-logout {
            background: var(--danger);
            color: var(--white);
        }

        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
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

        @media (max-width: 768px) {
            .header-container { padding: 1rem; }
            .header-nav { gap: 0.75rem; font-size: 0.85rem; }
            .logo { font-size: 1.25rem; }
            .hero { padding: 2rem 1rem 1.5rem; }
            .hero h1 { font-size: 1.75rem; }
            .profile-section { padding: 2rem 0; }
            .profile-card { padding: 1.5rem; }
            .profile-header { flex-direction: column; text-align: center; }
            .profile-avatar { width: 80px; height: 80px; font-size: 2rem; }
            .actions-grid { grid-template-columns: 1fr; }
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
                <a href="/orders.php" class="nav-link">Мои заказы</a>
                <a href="/profile.php" class="nav-link active">
                    <?php
                    if (!empty($currentUser['email'])) {
                        echo htmlspecialchars($currentUser['email']);
                    } elseif (!empty($currentUser['name'])) {
                        echo htmlspecialchars($currentUser['name']);
                    } else {
                        echo htmlspecialchars($currentUser['phone']);
                    }
                    ?>
                </a>
                <?php include 'components/cart.php'; ?>
            </nav>
        </div>
    </header>

    <section class="hero">
        <h1>Мой профиль</h1>
    </section>

    <section class="profile-section">
        <div class="container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php
                        $initial = '';
                        if (!empty($currentUser['name'])) {
                            $initial = mb_strtoupper(mb_substr($currentUser['name'], 0, 1));
                        } elseif (!empty($currentUser['email'])) {
                            $initial = mb_strtoupper(mb_substr($currentUser['email'], 0, 1));
                        } else {
                            $initial = '👤';
                        }
                        echo htmlspecialchars($initial);
                        ?>
                    </div>
                    <div class="profile-header-info">
                        <h2>
                            <?php
                            if (!empty($currentUser['name'])) {
                                echo htmlspecialchars($currentUser['name']);
                            } elseif (!empty($currentUser['email'])) {
                                echo htmlspecialchars($currentUser['email']);
                            } else {
                                echo htmlspecialchars($currentUser['phone']);
                            }
                            ?>
                        </h2>
                        <p>Личный кабинет клиента</p>
                    </div>
                </div>

                <div class="profile-info-grid">
                    <?php if (!empty($currentUser['name'])): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Имя</div>
                            <div class="info-value"><?= htmlspecialchars($currentUser['name']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($currentUser['phone'])): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Телефон</div>
                            <div class="info-value"><?= htmlspecialchars($currentUser['phone']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($currentUser['email'])): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?= htmlspecialchars($currentUser['email']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($currentUser['created_at'])): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Дата регистрации</div>
                            <div class="info-value">
                                <?php
                                $date = new DateTime($currentUser['created_at']);
                                echo $date->format('d.m.Y');
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="edit-notice">
                    <i class="fas fa-info-circle"></i>
                    <div class="edit-notice-content">
                        <h3>Изменение данных</h3>
                        <p>Для изменения контактных данных, пожалуйста, свяжитесь с нашим менеджером через Telegram</p>
                        <a href="<?= MANAGER_TELEGRAM_LINK ?>" target="_blank" class="telegram-link">
                            <i class="fab fa-telegram"></i>
                            Написать менеджеру
                        </a>
                    </div>
                </div>

                <div class="actions-grid">
                    <a href="/orders.php" class="action-btn btn-orders">
                        <i class="fas fa-shopping-bag"></i>
                        Мои заказы
                    </a>
                    <button class="action-btn btn-logout" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                        Выйти
                    </button>
                </div>
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

    <script>
        async function logout() {
            if (!confirm('Вы уверены, что хотите выйти?')) {
                return;
            }

            try {
                const response = await fetch('/api/auth.php?action=logout', {
                    method: 'POST',
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = '/';
                } else {
                    alert('Ошибка при выходе из системы');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при выходе из системы');
            }
        }
    </script>
</body>
</html>
