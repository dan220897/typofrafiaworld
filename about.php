<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О нас - <?= SITE_NAME ?></title>
    <meta name="description" content="Узнайте больше о нашей типографии. Профессиональные полиграфические услуги с 2010 года.">
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

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
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

        .nav-link.active,
        .nav-link:hover {
            color: var(--primary);
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

        .nav-link.active::after,
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

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        /* Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* About Section */
        .about-section {
            padding: 5rem 0;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 5rem;
        }

        .about-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .about-content p {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .about-image {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .about-image i {
            font-size: 10rem;
            color: rgba(255, 255, 255, 0.3);
        }

        /* Stats */
        .stats {
            background: var(--white);
            padding: 4rem 0;
            margin: 4rem 0;
            border-radius: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3rem;
        }

        .stat-card {
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--gray);
        }

        /* Values */
        .values {
            margin: 5rem 0;
        }

        .values h2 {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark);
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .value-card {
            background: var(--white);
            padding: 3rem 2rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: var(--shadow);
        }

        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .value-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .value-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .value-card p {
            color: var(--gray);
            line-height: 1.7;
        }

        /* Team */
        .team {
            padding: 5rem 0;
        }

        .team h2 {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .team-member {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: var(--shadow);
        }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .team-avatar i {
            font-size: 3rem;
            color: var(--white);
        }

        .team-member h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .team-member p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
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
            background: linear-gradient(135deg, #0088cc 0%, #0066cc 100%);
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

        /* Responsive */
        @media (max-width: 968px) {
            .about-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: repeat(2, 1fr);
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
                padding: 3rem 1rem 2rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .about-section {
                padding: 3rem 0;
            }

            .about-content h2,
            .values h2,
            .team h2 {
                font-size: 1.75rem;
            }

            .about-content p {
                font-size: 1rem;
            }

            .stats {
                padding: 3rem 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }

            .team-grid {
                grid-template-columns: 1fr;
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
        }

        @media (max-width: 480px) {
            .header-nav a:not(.btn) {
                display: none;
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .btn {
                padding: 0.5rem 1.25rem;
                font-size: 0.85rem;
            }

            .about-image {
                height: 300px;
            }

            .about-image i {
                font-size: 6rem;
            }

            .value-icon {
                font-size: 2.5rem;
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
                <a href="/about.php" class="nav-link active">О нас</a>
                <a href="/portfolio.php" class="nav-link">Портфолио</a>
                <a href="/contacts.php" class="nav-link">Контакты</a>
                <?php if ($isAuthenticated): ?>
                    <a href="/orders.php" class="nav-link">Мои заказы</a>
                <?php else: ?>
                    <a href="/" class="btn btn-primary">Войти</a>
                <?php endif; ?>
                <?php include 'components/cart.php'; ?>
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>О нашей типографии</h1>
            <p>Профессиональные полиграфические услуги с 2010 года</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <h2>Кто мы</h2>
                    <p>
                        Мы — современная типография, которая объединяет передовые технологии и богатый опыт.
                        С 2010 года мы помогаем бизнесу создавать качественную полиграфическую продукцию.
                    </p>
                    <p>
                        Наша команда состоит из профессионалов, которые знают всё о печати и дизайне.
                        Мы используем только современное оборудование и качественные материалы.
                    </p>
                    <p>
                        Каждый заказ для нас важен. Мы гарантируем высокое качество, соблюдение сроков
                        и индивидуальный подход к каждому клиенту.
                    </p>
                </div>
                <div class="about-image">
                    <i class="fas fa-print"></i>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">14+</div>
                        <div class="stat-label">Лет на рынке</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Довольных клиентов</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Выполненных заказов</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Онлайн поддержка</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="values">
        <div class="container">
            <h2>Наши ценности</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Качество</h3>
                    <p>Используем только проверенные материалы и современное оборудование для достижения идеального результата.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Скорость</h3>
                    <p>Оперативно выполняем заказы любой сложности. Срочная печать — наша специализация.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Надежность</h3>
                    <p>Гарантируем соблюдение сроков и качество работы. Ваше доверие — наша репутация.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section class="team">
        <div class="container">
            <h2>Наша команда</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Алексей Иванов</h3>
                    <p>Генеральный директор</p>
                </div>
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Мария Петрова</h3>
                    <p>Арт-директор</p>
                </div>
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>Дмитрий Сидоров</h3>
                    <p>Технический директор</p>
                </div>
                <div class="team-member">
                    <div class="team-avatar">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Елена Смирнова</h3>
                    <p>Менеджер проектов</p>
                </div>
            </div>
        </div>
    </section>

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

    <script>
        function openTelegram() {
            window.open('https://t.me/your_bot', '_blank');
        }

        // Анимация при скролле
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.stat-card, .value-card, .team-member');
            elements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
