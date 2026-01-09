<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналитика - Админ панель</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        /* Базовые стили */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --gray: #6b7280;
            --light: #f9fafb;
            --white: #ffffff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--light);
            color: var(--dark);
        }

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
        }

        /* Страница аналитики */
        .analytics-page {
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .period-selector {
            display: flex;
            background: var(--white);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .period-btn {
            padding: 10px 16px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .period-btn.active {
            background: var(--primary);
            color: var(--white);
        }

        /* Сводная статистика */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .summary-card.success::before { background: var(--success); }
        .summary-card.warning::before { background: var(--warning); }
        .summary-card.danger::before { background: var(--danger); }

        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .summary-title {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }

        .summary-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .summary-comparison {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .comparison-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 500;
        }

        .comparison-badge.positive {
            background: #d1fae5;
            color: #065f46;
        }

        .comparison-badge.negative {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Графики */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .chart-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
        }

        .chart-actions {
            display: flex;
            gap: 8px;
        }

        .chart-action-btn {
            padding: 6px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: var(--white);
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .chart-action-btn:hover {
            background: #f3f4f6;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Детальные метрики */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .metric-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
        }

        .metric-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .metric-title {
            font-size: 16px;
            font-weight: 600;
        }

        .metric-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .metric-item:last-child {
            border-bottom: none;
        }

        .metric-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .metric-value {
            font-weight: 600;
            font-size: 14px;
        }

        .progress-bar {
            width: 100px;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-left: 12px;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        /* Таблица топ-клиентов */
        .table-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 16px;
            font-weight: 600;
            color: var(--gray);
            background: #f9fafb;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Кнопки */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--dark);
            border: 1px solid #e5e7eb;
        }

        /* Легенда */
        .legend {
            display: flex;
            gap: 20px;
            margin-top: 16px;
            font-size: 13px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        /* Карта активности */
        .activity-heatmap {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-top: 16px;
        }

        .heatmap-cell {
            aspect-ratio: 1;
            background: #e5e7eb;
            border-radius: 4px;
            position: relative;
            cursor: pointer;
        }

        .heatmap-cell:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }

        .heatmap-cell.low { background: #dbeafe; }
        .heatmap-cell.medium { background: #60a5fa; }
        .heatmap-cell.high { background: #2563eb; }

        /* Фильтры экспорта */
        .export-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .export-modal.active {
            display: flex;
        }

        .export-content {
            background: var(--white);
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 500px;
        }

        .export-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .export-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .export-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
        }

        .export-option:hover {
            background: #f3f4f6;
        }

        .export-option input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .period-selector {
                flex-wrap: wrap;
            }

            .period-btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Основной контент -->
    <main class="main-content">
        <div class="analytics-page">
            <!-- Заголовок страницы -->
            <div class="page-header">
                <h1 class="page-title">Аналитика и статистика</h1>
                <div class="header-controls">
                    <div class="period-selector">
                        <button class="period-btn" onclick="setPeriod('today')">Сегодня</button>
                        <button class="period-btn active" onclick="setPeriod('week')">Неделя</button>
                        <button class="period-btn" onclick="setPeriod('month')">Месяц</button>
                        <button class="period-btn" onclick="setPeriod('year')">Год</button>
                        <button class="period-btn" onclick="setPeriod('custom')">
                            <i class="fas fa-calendar"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary" onclick="exportReport()">
                        <i class="fas fa-download"></i>
                        Экспорт отчета
                    </button>
                </div>
            </div>

            <!-- Сводная статистика -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-header">
                        <div>
                            <div class="summary-title">Выручка</div>
                            <div class="summary-value">₽1,234,567</div>
                            <div class="summary-comparison">
                                <span>По сравнению с прошлым периодом</span>
                                <span class="comparison-badge positive">
                                    <i class="fas fa-arrow-up"></i>
                                    +23.5%
                                </span>
                            </div>
                        </div>
                        <div class="summary-icon">
                            <i class="fas fa-ruble-sign"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card success">
                    <div class="summary-header">
                        <div>
                            <div class="summary-title">Заказы</div>
                            <div class="summary-value">342</div>
                            <div class="summary-comparison">
                                <span>По сравнению с прошлым периодом</span>
                                <span class="comparison-badge positive">
                                    <i class="fas fa-arrow-up"></i>
                                    +15.8%
                                </span>
                            </div>
                        </div>
                        <div class="summary-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card warning">
                    <div class="summary-header">
                        <div>
                            <div class="summary-title">Средний чек</div>
                            <div class="summary-value">₽3,607</div>
                            <div class="summary-comparison">
                                <span>По сравнению с прошлым периодом</span>
                                <span class="comparison-badge negative">
                                    <i class="fas fa-arrow-down"></i>
                                    -5.2%
                                </span>
                            </div>
                        </div>
                        <div class="summary-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-header">
                        <div>
                            <div class="summary-title">Новые клиенты</div>
                            <div class="summary-value">48</div>
                            <div class="summary-comparison">
                                <span>По сравнению с прошлым периодом</span>
                                <span class="comparison-badge positive">
                                    <i class="fas fa-arrow-up"></i>
                                    +12.3%
                                </span>
                            </div>
                        </div>
                        <div class="summary-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--secondary);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Графики -->
            <div class="charts-grid">
                <!-- График продаж -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Динамика продаж</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn active">Выручка</button>
                            <button class="chart-action-btn">Заказы</button>
                            <button class="chart-action-btn">Клиенты</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: var(--primary);"></div>
                            <span>Текущий период</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #e5e7eb;"></div>
                            <span>Прошлый период</span>
                        </div>
                    </div>
                </div>

                <!-- Популярные услуги -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Популярные услуги</h3>
                        <button class="chart-action-btn">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="servicesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Детальные метрики -->
            <div class="metrics-grid">
                <!-- Статистика по услугам -->
                <div class="metric-card">
                    <div class="metric-header">
                        <h3 class="metric-title">Топ услуги по выручке</h3>
                        <a href="#" style="color: var(--primary); font-size: 14px;">Все услуги</a>
                    </div>
                    <div class="metric-list">
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-circle" style="color: var(--primary); font-size: 8px;"></i>
                                Визитки
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="metric-value">₽234,500</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 75%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-circle" style="color: var(--secondary); font-size: 8px;"></i>
                                Баннеры
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="metric-value">₽189,200</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 60%; background: var(--secondary);"></div>
                                </div>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-circle" style="color: var(--success); font-size: 8px;"></i>
                                Флаеры
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="metric-value">₽156,800</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 50%; background: var(--success);"></div>
                                </div>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-circle" style="color: var(--warning); font-size: 8px;"></i>
                                Дизайн
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="metric-value">₽98,500</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 30%; background: var(--warning);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Источники трафика -->
                <div class="metric-card">
                    <div class="metric-header">
                        <h3 class="metric-title">Источники клиентов</h3>
                        <button class="chart-action-btn">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                    <div class="metric-list">
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-globe" style="color: var(--primary);"></i>
                                Прямые заходы
                            </div>
                            <span class="metric-value">45%</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-search" style="color: var(--success);"></i>
                                Поисковые системы
                            </div>
                            <span class="metric-value">28%</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-share-alt" style="color: var(--secondary);"></i>
                                Социальные сети
                            </div>
                            <span class="metric-value">18%</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">
                                <i class="fas fa-users" style="color: var(--warning);"></i>
                                Рекомендации
                            </div>
                            <span class="metric-value">9%</span>
                        </div>
                    </div>
                </div>

                <!-- Конверсия -->
                <div class="metric-card">
                    <div class="metric-header">
                        <h3 class="metric-title">Показатели конверсии</h3>
                    </div>
                    <div class="metric-list">
                        <div class="metric-item">
                            <div class="metric-label">Посетители → Регистрации</div>
                            <span class="metric-value">12.5%</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">Регистрации → Первый заказ</div>
                            <span class="metric-value">34.2%</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">Заказы → Повторные заказы</div>
                            <span class="metric-value">67.8%</span>
                        </div>
                        <div class="metric-item">
                            <div class="metric-label">Средний LTV клиента</div>
                            <span class="metric-value">₽45,600</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Активность по дням -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Карта активности</h3>
                    <span style="font-size: 14px; color: var(--gray);">Заказы по дням недели и времени</span>
                </div>
                <div style="display: grid; grid-template-columns: 60px 1fr; gap: 12px; margin-top: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 4px; padding-top: 20px;">
                        <div style="height: 30px; display: flex; align-items: center; font-size: 12px; color: var(--gray);">Пн</div>
                        <div style="height: 30px; display: flex; align-items: center; font-size: 12px; color: var(--gray);">Вт</div>
                        <div style="height: 30px; display: flex; align-items: center; font-size: 12px; color: var(--gray);">Ср</div>
                        <div style="height: 30px; display: flex; align-items: center; font-size: 12px; color: var(--gray);">Чт</div>
                        <div style="height: 30px; display: flex; align-items: center; font-size: 12px; color: var(--gray);">Пт</div>
                        <div style="height: 30px; display: flex; align-items: center; font-size: 12px; color: var(--gray);">Сб</div>
                        <div style="height: 30px; display: flex; align-items: center; font-size: 12px; color: var(--gray);">Вс</div>
                    </div>
                    <div>
                        <div style="display: flex; gap: 4px; margin-bottom: 4px;">
                            <div style="flex: 1; text-align: center; font-size: 12px; color: var(--gray);">00</div>
                            <div style="flex: 1; text-align: center; font-size: 12px; color: var(--gray);">06</div>
                            <div style="flex: 1; text-align: center; font-size: 12px; color: var(--gray);">12</div>
                            <div style="flex: 1; text-align: center; font-size: 12px; color: var(--gray);">18</div>
                            <div style="flex: 1; text-align: center; font-size: 12px; color: var(--gray);">24</div>
                        </div>
                        <div class="activity-heatmap">
                            <!-- Генерация ячеек для карты активности -->
                            <script>
                                // Будет заполнено через JS
                            </script>
                        </div>
                    </div>
                </div>
                <div class="legend" style="justify-content: center;">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #e5e7eb;"></div>
                        <span>Низкая</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #dbeafe;"></div>
                        <span>Средняя</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #60a5fa;"></div>
                        <span>Высокая</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #2563eb;"></div>
                        <span>Пиковая</span>
                    </div>
                </div>
            </div>

            <!-- Топ клиенты -->
            <div class="table-card" style="margin-top: 24px;">
                <div class="table-header">
                    <h3 class="table-title">Топ-10 клиентов по выручке</h3>
                    <a href="users.php" class="btn btn-secondary">Все клиенты</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Клиент</th>
                                <th>Заказов</th>
                                <th>Общая сумма</th>
                                <th>Средний чек</th>
                                <th>Последний заказ</th>
                                <th>Тренд</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">РО</div>
                                        <div>
                                            <div style="font-weight: 500;">ООО "Ромашка"</div>
                                            <div style="font-size: 12px; color: var(--gray);">ИНН: 7707123456</div>
                                        </div>
                                    </div>
                                </td>
                                <td>24</td>
                                <td style="font-weight: 600;">₽385,600</td>
                                <td>₽16,067</td>
                                <td>2 дня назад</td>
                                <td>
                                    <span class="comparison-badge positive">
                                        <i class="fas fa-arrow-up"></i>
                                        +15%
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar" style="background: var(--secondary);">АБ</div>
                                        <div>
                                            <div style="font-weight: 500;">ИП Абрамов Б.В.</div>
                                            <div style="font-size: 12px; color: var(--gray);">+7 (495) 234-56-78</div>
                                        </div>
                                    </div>
                                </td>
                                <td>18</td>
                                <td style="font-weight: 600;">₽276,300</td>
                                <td>₽15,350</td>
                                <td>5 дней назад</td>
                                <td>
                                    <span class="comparison-badge positive">
                                        <i class="fas fa-arrow-up"></i>
                                        +8%
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar" style="background: var(--success);">СК</div>
                                        <div>
                                            <div style="font-weight: 500;">ООО "СтройКомплект"</div>
                                            <div style="font-size: 12px; color: var(--gray);">ИНН: 7712345678</div>
                                        </div>
                                    </div>
                                </td>
                                <td>15</td>
                                <td style="font-weight: 600;">₽198,500</td>
                                <td>₽13,233</td>
                                <td>1 неделю назад</td>
                                <td>
                                    <span class="comparison-badge negative">
                                        <i class="fas fa-arrow-down"></i>
                                        -5%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Модальное окно экспорта -->
    <div class="export-modal" id="exportModal">
        <div class="export-content">
            <h3 class="export-title">Экспорт отчета</h3>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Период</label>
                <input type="date" class="form-control" style="width: 48%; display: inline-block;"> 
                <span style="margin: 0 4px;">—</span>
                <input type="date" class="form-control" style="width: 48%; display: inline-block;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Включить в отчет</label>
                <div class="export-options">
                    <label class="export-option">
                        <input type="checkbox" checked>
                        <span>Сводная статистика</span>
                    </label>
                    <label class="export-option">
                        <input type="checkbox" checked>
                        <span>Графики продаж</span>
                    </label>
                    <label class="export-option">
                        <input type="checkbox" checked>
                        <span>Статистика по услугам</span>
                    </label>
                    <label class="export-option">
                        <input type="checkbox" checked>
                        <span>Топ клиентов</span>
                    </label>
                    <label class="export-option">
                        <input type="checkbox">
                        <span>Детальный список заказов</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Формат</label>
                <select class="form-control" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <option>PDF отчет</option>
                    <option>Excel таблица</option>
                    <option>CSV данные</option>
                </select>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeExportModal()">Отмена</button>
                <button class="btn btn-primary" onclick="downloadReport()">
                    <i class="fas fa-download"></i>
                    Скачать отчет
                </button>
            </div>
        </div>
    </div>

    <script>
        // Инициализация графиков
        const ctx1 = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
                datasets: [{
                    label: 'Текущая неделя',
                    data: [165000, 189000, 175000, 210000, 198000, 145000, 152000],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Прошлая неделя',
                    data: [145000, 165000, 155000, 180000, 175000, 125000, 135000],
                    borderColor: '#e5e7eb',
                    backgroundColor: 'rgba(229, 231, 235, 0.3)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₽' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });

        const ctx2 = document.getElementById('servicesChart').getContext('2d');
        const servicesChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Визитки', 'Баннеры', 'Флаеры', 'Дизайн', 'Прочее'],
                datasets: [{
                    data: [35, 25, 20, 12, 8],
                    backgroundColor: [
                        '#3b82f6',
                        '#6366f1',
                        '#10b981',
                        '#f59e0b',
                        '#e5e7eb'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Генерация карты активности
        function generateHeatmap() {
            const heatmapContainer = document.querySelector('.activity-heatmap');
            heatmapContainer.innerHTML = '';
            
            for (let day = 0; day < 7; day++) {
                for (let hour = 0; hour < 24; hour++) {
                    const cell = document.createElement('div');
                    cell.className = 'heatmap-cell';
                    
                    // Случайные данные для демонстрации
                    const activity = Math.random();
                    if (activity > 0.7) {
                        cell.classList.add('high');
                        cell.setAttribute('data-tooltip', `${Math.floor(activity * 50)} заказов`);
                    } else if (activity > 0.4) {
                        cell.classList.add('medium');
                        cell.setAttribute('data-tooltip', `${Math.floor(activity * 30)} заказов`);
                    } else if (activity > 0.1) {
                        cell.classList.add('low');
                        cell.setAttribute('data-tooltip', `${Math.floor(activity * 10)} заказов`);
                    } else {
                        cell.setAttribute('data-tooltip', '0 заказов');
                    }
                    
                    heatmapContainer.appendChild(cell);
                }
            }
        }

        // Переключение периода
        function setPeriod(period) {
            const buttons = document.querySelectorAll('.period-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            console.log('Изменение периода на:', period);
            // Здесь будет загрузка данных для выбранного периода
        }

        // Экспорт отчета
        function exportReport() {
            document.getElementById('exportModal').classList.add('active');
        }

        function closeExportModal() {
            document.getElementById('exportModal').classList.remove('active');
        }

        function downloadReport() {
            console.log('Скачивание отчета...');
            // Здесь будет генерация и скачивание отчета
            closeExportModal();
        }

        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            generateHeatmap();
            
            // Анимация чисел при загрузке
            const summaryValues = document.querySelectorAll('.summary-value');
            summaryValues.forEach(el => {
                const finalValue = el.textContent;
                const isRuble = finalValue.includes('₽');
                const numericValue = parseInt(finalValue.replace(/[^\d]/g, ''));
                let currentValue = 0;
                const increment = numericValue / 50;
                
                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= numericValue) {
                        currentValue = numericValue;
                        clearInterval(counter);
                    }
                    
                    if (isRuble) {
                        el.textContent = '₽' + currentValue.toLocaleString('ru-RU');
                    } else {
                        el.textContent = Math.floor(currentValue).toLocaleString('ru-RU');
                    }
                }, 20);
            });
        });

        // Обновление данных каждые 30 секунд
        setInterval(() => {
            console.log('Обновление данных аналитики...');
            // Здесь будет обновление данных через AJAX
        }, 30000);
    </script>
</body>
</html>