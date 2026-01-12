<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;

// Получаем категории услуг
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

// Получаем все услуги для поиска
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, label, category FROM services WHERE is_active = 1 ORDER BY category, label");
    $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allServices = [];
}

// Уникальные иконки для каждой категории
$categoryIcons = [
    'Визитки' => 'fa-address-card',
    'Баннеры' => 'fa-panorama',
    'Флаеры' => 'fa-layer-group',
    'Листовки' => 'fa-file-lines',
    'Буклеты' => 'fa-book-open-reader',
    'Календари' => 'fa-calendar-days',
    'Наклейки' => 'fa-note-sticky',
    'Брошюры' => 'fa-book-bookmark',
    'Каталоги' => 'fa-books',
    'Пакеты' => 'fa-bag-shopping',
    'Папки' => 'fa-folder-tree',
    'Плакаты' => 'fa-panorama',
    'Сувениры' => 'fa-gifts',
    'Дизайн' => 'fa-pen-nib'
];

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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Профессиональная типография онлайн</title>
    <meta name="description" content="Качественная полиграфия с доставкой. Визитки, баннеры, флаеры, дизайн. Быстрое изготовление, низкие цены.">
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
            --light-gray: #f9fafb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--dark);
            background-color: var(--light-gray);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Animations */
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

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .animate-on-scroll {
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Header */
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

        /* Hero Section */
        .hero {
            background: var(--primary);
            color: var(--white);
            padding: 6rem 2rem;
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
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: slideInLeft 0.8s ease;
        }

        .hero p {
            font-size: 1.35rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            animation: fadeInUp 0.8s ease 0.2s backwards;
        }

        /* Search */
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease 0.4s backwards;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1.125rem 3.5rem 1.125rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            box-shadow: var(--shadow-xl);
            transition: all 0.3s ease;
            background: var(--white);
        }

        .search-input:focus {
            outline: none;
            transform: translateY(-2px);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
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
            width: 45px;
            height: 45px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-50%) scale(1.1);
        }

        /* Main Content */
        .container {
            max-width: 1280px;
            margin: 4rem auto;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 2rem;
        }

        .category-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--dark);
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .category-card::before {
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

        .category-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .category-card:hover::before {
            opacity: 1;
        }

        .category-icon {
            font-size: 3.5rem;
            margin-bottom: 1.25rem;
            transition: all 0.4s ease;
            position: relative;
        }

        .category-card:hover .category-icon {
            transform: scale(1.15) rotateY(180deg);
        }

        .category-name {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .category-card:hover .category-name {
            color: var(--primary);
        }

        .category-description {
            color: var(--gray);
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .category-card:hover .category-description {
            color: var(--dark);
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

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .header-nav {
                gap: 1rem;
            }

            .nav-link {
                font-size: 0.9rem;
            }

            .hero {
                padding: 4rem 1.5rem;
            }

            .hero h1 {
                font-size: 2.25rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 1rem;
            }

            .header-nav {
                gap: 0.75rem;
                font-size: 0.85rem;
            }

            .logo {
                font-size: 1.25rem;
            }

            .hero {
                padding: 3rem 1rem;
            }

            .hero h1 {
                font-size: 1.75rem;
            }

            .hero p {
                font-size: 1rem;
                margin-bottom: 2rem;
            }

            .search-container {
                margin-top: 0;
            }

            .search-input {
                padding: 1rem 3rem 1rem 1.25rem;
                font-size: 0.9rem;
            }

            .categories-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .category-card {
                padding: 2rem 1.5rem;
            }

            .container {
                padding: 0 1rem;
                margin: 2.5rem auto;
            }

            .section-title {
                font-size: 1.75rem;
                margin-bottom: 2rem;
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

            .telegram-widget:hover .telegram-info {
                display: none;
            }

            .footer {
                padding: 3rem 1.5rem 1rem;
                margin-top: 4rem;
            }

            .footer-content {
                gap: 2rem;
            }
        }

        @media (max-width: 480px) {
            .header-nav a:not(.btn) {
                display: none;
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .hero p {
                font-size: 0.9rem;
            }

            .btn {
                padding: 0.5rem 1.25rem;
                font-size: 0.85rem;
            }

            .category-icon {
                font-size: 2.75rem;
            }

            .category-name {
                font-size: 1.15rem;
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Профессиональная типография онлайн</h1>
            <p>Качественная полиграфия с доставкой по всей России. Быстро, надёжно, по лучшим ценам.</p>

            <div class="search-container">
                <div class="search-box">
                    <input
                        type="text"
                        class="search-input"
                        placeholder="Найти услугу или продукт..."
                        id="searchInput"
                        onkeyup="handleSearch()"
                    >
                    <button class="search-btn" onclick="handleSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <main class="container">
        <h2 class="section-title">Наши услуги</h2>

        <div class="categories-grid" id="categoriesGrid">
            <?php if (empty($categories)): ?>
                <!-- Если категорий нет в БД, показываем примеры -->
                <a href="catalog.php?category=Визитки" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="category-name">Визитки</div>
                    <div class="category-description">От 500 руб за тираж</div>
                </a>

                <a href="catalog.php?category=Баннеры" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="category-name">Баннеры</div>
                    <div class="category-description">Любые размеры</div>
                </a>

                <a href="catalog.php?category=Флаеры" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-file-image"></i>
                    </div>
                    <div class="category-name">Флаеры</div>
                    <div class="category-description">Быстрая печать</div>
                </a>

                <a href="catalog.php?category=Листовки" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="category-name">Листовки</div>
                    <div class="category-description">От 1000 шт</div>
                </a>

                <a href="catalog.php?category=Буклеты" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="category-name">Буклеты</div>
                    <div class="category-description">Различные форматы</div>
                </a>

                <a href="catalog.php?category=Каталоги" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="category-name">Каталоги</div>
                    <div class="category-description">Премиум качество</div>
                </a>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <a href="catalog.php?category=<?= urlencode($category) ?>" class="category-card" data-category="<?= strtolower($category) ?>">
                        <div class="category-icon" style="color: <?= $categoryColors[$category] ?? 'var(--primary)' ?>;">
                            <i class="fas <?= $categoryIcons[$category] ?? 'fa-box' ?>"></i>
                        </div>
                        <div class="category-name"><?= htmlspecialchars($category) ?></div>
                        <div class="category-description">Смотреть услуги</div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Telegram Widget -->
    <div class="telegram-widget">
        <div class="telegram-info">
            <div class="telegram-info-text">Свяжитесь с нами в Telegram</div>
            <div class="telegram-info-status">Мы сейчас в сети</div>
        </div>
        <button class="telegram-btn" onclick="openTelegram()" title="Написать в Telegram">
            <i class="fab fa-telegram-plane"></i>
        </button>
    </div>

    <!-- Footer -->
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
                <a href="/">Каталог услуг</a>
                <a href="/catalog.php?category=Визитки">Визитки</a>
                <a href="/catalog.php?category=Баннеры">Баннеры</a>
                <a href="/catalog.php?category=Дизайн">Дизайн</a>
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
