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

// Иконки для категорий (можно настроить через админ панель позже)
$categoryIcons = [
    'Печать документов' => 'fa-print',
    'Копировальные услуги' => 'fa-copy',
    'Сканирование' => 'fa-scanner',
    'Визитные карточки' => 'fa-id-card',
    'Визитки' => 'fa-id-card',
    'Листовки и флаеры' => 'fa-file-alt',
    'Флаеры' => 'fa-file-image',
    'Листовки' => 'fa-file-alt',
    'Брошюры и буклеты' => 'fa-book',
    'Брошюры' => 'fa-book',
    'Буклеты' => 'fa-book-open',
    'Плакаты и постеры' => 'fa-image',
    'Плакаты' => 'fa-image',
    'Наклейки и стикеры' => 'fa-sticky-note',
    'Наклейки' => 'fa-sticky-note',
    'Широкоформатная печать' => 'fa-flag',
    'Баннеры' => 'fa-flag',
    'Чертежи и схемы' => 'fa-drafting-compass',
    'Фотоуслуги' => 'fa-camera',
    'Календари' => 'fa-calendar-alt',
    'Дипломы и сертификаты' => 'fa-certificate',
    'Открытки и приглашения' => 'fa-envelope',
    'Упаковка' => 'fa-shopping-bag',
    'Пакеты' => 'fa-shopping-bag',
    'Сувенирная продукция' => 'fa-gift',
    'Сувениры' => 'fa-gift',
    'Интерьерная печать' => 'fa-palette',
    'Постпечатная обработка' => 'fa-stapler',
    'Дизайн-услуги' => 'fa-palette',
    'Дизайн' => 'fa-palette',
    'Дополнительные услуги' => 'fa-plus-circle',
    'Каталоги' => 'fa-folder-open',
    'Папки' => 'fa-folder'
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 4rem 2rem;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Search */
        .search-container {
            max-width: 600px;
            margin: 2rem auto 0;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 3rem 1rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            box-shadow: var(--shadow-lg);
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
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-btn:hover {
            background: var(--primary-hover);
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        .category-card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--dark);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .category-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .category-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .category-description {
            color: var(--gray);
            font-size: 0.9rem;
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

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }

            .container {
                padding: 0 1rem;
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
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Профессиональная типография онлайн</h1>
        <p>Качественная полиграфия с доставкой по всей России</p>

        <div class="search-container">
            <div class="search-box">
                <input
                    type="text"
                    class="search-input"
                    placeholder="Поиск услуг..."
                    id="searchInput"
                    onkeyup="handleSearch()"
                >
                <button class="search-btn" onclick="handleSearch()">
                    <i class="fas fa-search"></i>
                </button>
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
                        <div class="category-icon">
                            <i class="fas <?= $categoryIcons[$category] ?? 'fa-box' ?>"></i>
                        </div>
                        <div class="category-name"><?= htmlspecialchars($category) ?></div>
                        <div class="category-description">Смотреть услуги</div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
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
                <a href="catalog.php">Каталог услуг</a>
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
        // Поиск по категориям
        function handleSearch() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.category-card');

            cards.forEach(card => {
                const categoryName = card.querySelector('.category-name').textContent.toLowerCase();
                if (categoryName.includes(searchText)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Открыть чат
        function openChat() {
            window.location.href = 'chat.php';
        }

        // Показать модальное окно авторизации (если нужно)
        function showAuthModal() {
            // TODO: Реализовать модальное окно
            alert('Модальное окно авторизации в разработке');
        }
    </script>
</body>
</html>
