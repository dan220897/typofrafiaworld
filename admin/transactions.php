<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Транзакции - Админ панель</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

        /* Страница транзакций */
        .transactions-page {
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

        .header-actions {
            display: flex;
            gap: 12px;
        }

        /* Статистика транзакций */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .stat-card.success::before { background: var(--success); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.danger::before { background: var(--danger); }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.yellow { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-icon.red { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: var(--primary); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray);
        }

        .stat-change {
            font-size: 13px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-change.positive { color: var(--success); }
        .stat-change.negative { color: var(--danger); }

        /* Фильтры */
        .filters-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }

        .filters-row {
            display: flex;
            gap: 16px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--gray);
        }

        .form-control {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        /* Таблица транзакций */
        .transactions-table-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
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

        tr:hover {
            background: #f9fafb;
        }

        /* Статусы транзакций */
        .transaction-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .transaction-status.success { background: #d1fae5; color: #065f46; }
        .transaction-status.pending { background: #fef3c7; color: #92400e; }
        .transaction-status.failed { background: #fee2e2; color: #991b1b; }
        .transaction-status.processing { background: #dbeafe; color: #1e40af; }
        .transaction-status.refunded { background: #e9d5ff; color: #6b21a8; }

        /* Методы оплаты */
        .payment-method {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .payment-method i {
            width: 24px;
            text-align: center;
            color: var(--gray);
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Модальное окно деталей */
        .modal {
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

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 12px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        /* Детали транзакции */
        .detail-section {
            margin-bottom: 24px;
        }

        .detail-title {
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray);
            font-size: 14px;
        }

        .detail-value {
            font-weight: 500;
            font-size: 14px;
        }

        /* График транзакций */
        .chart-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 24px;
            margin-bottom: 24px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Пагинация */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 20px;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: var(--white);
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .pagination-btn:hover {
            background: #f3f4f6;
        }

        .pagination-btn.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Экспорт */
        .export-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .export-option {
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .export-option:hover {
            background: #f3f4f6;
        }

        .export-option.selected {
            background: #eff6ff;
            border-color: var(--primary);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Основной контент -->
    <main class="main-content">
        <div class="transactions-page">
            <!-- Заголовок страницы -->
            <div class="page-header">
                <h1 class="page-title">Транзакции и платежи</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="exportTransactions()">
                        <i class="fas fa-download"></i>
                        Экспорт
                    </button>
                    <button class="btn btn-primary" onclick="addManualTransaction()">
                        <i class="fas fa-plus"></i>
                        Добавить платеж
                    </button>
                </div>
            </div>

            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">₽1,245,680</div>
                    <div class="stat-label">Успешные платежи</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        +23.5% за месяц
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">₽85,300</div>
                    <div class="stat-label">Ожидают оплаты</div>
                    <div class="stat-change">
                        12 транзакций
                    </div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-icon red">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value">₽12,450</div>
                    <div class="stat-label">Неудачные платежи</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i>
                        -5.2% за месяц
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="stat-value">₽28,900</div>
                    <div class="stat-label">Возвраты</div>
                    <div class="stat-change">
                        8 транзакций
                    </div>
                </div>
            </div>

            <!-- График транзакций -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="card-title">Динамика платежей</h3>
                    <div>
                        <button class="btn btn-sm btn-secondary" onclick="changeChartPeriod('week')">Неделя</button>
                        <button class="btn btn-sm btn-secondary active" onclick="changeChartPeriod('month')">Месяц</button>
                        <button class="btn btn-sm btn-secondary" onclick="changeChartPeriod('year')">Год</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="transactionsChart"></canvas>
                </div>
            </div>

            <!-- Фильтры -->
            <div class="filters-card">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Поиск</label>
                        <input type="text" class="form-control" placeholder="ID транзакции, заказ, клиент...">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Статус</label>
                        <select class="form-control">
                            <option value="">Все статусы</option>
                            <option value="success">Успешные</option>
                            <option value="pending">Ожидание</option>
                            <option value="processing">В обработке</option>
                            <option value="failed">Неудачные</option>
                            <option value="refunded">Возвраты</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Метод оплаты</label>
                        <select class="form-control">
                            <option value="">Все методы</option>
                            <option value="card">Банковская карта</option>
                            <option value="bank">Банковский перевод</option>
                            <option value="cash">Наличные</option>
                            <option value="online">Онлайн-платеж</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Период</label>
                        <select class="form-control">
                            <option value="today">Сегодня</option>
                            <option value="week">За неделю</option>
                            <option value="month" selected>За месяц</option>
                            <option value="custom">Выбрать период</option>
                        </select>
                    </div>
                    <button class="btn btn-primary">Применить</button>
                </div>
            </div>

            <!-- Таблица транзакций -->
            <div class="transactions-table-card">
                <div class="table-header">
                    <h3 class="table-title">История транзакций</h3>
                    <span style="color: var(--gray); font-size: 14px;">Найдено: 342 транзакции</span>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID транзакции</th>
                                <th>Дата и время</th>
                                <th>Заказ</th>
                                <th>Клиент</th>
                                <th>Сумма</th>
                                <th>Метод</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>TRX-2025061214350001</strong>
                                </td>
                                <td>
                                    <div>12.06.2025</div>
                                    <div style="font-size: 12px; color: var(--gray);">14:35:22</div>
                                </td>
                                <td>
                                    <a href="#" style="color: var(--primary); text-decoration: none;">
                                        #1234
                                    </a>
                                </td>
                                <td>
                                    <div>Иван Петров</div>
                                    <div style="font-size: 12px; color: var(--gray);">+7 999 123-45-67</div>
                                </td>
                                <td>
                                    <strong>₽15,500</strong>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Карта</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="transaction-status success">
                                        <i class="fas fa-check-circle"></i>
                                        Успешно
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="viewTransaction('TRX-2025061214350001')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>TRX-2025061214200002</strong>
                                </td>
                                <td>
                                    <div>12.06.2025</div>
                                    <div style="font-size: 12px; color: var(--gray);">14:20:15</div>
                                </td>
                                <td>
                                    <a href="#" style="color: var(--primary); text-decoration: none;">
                                        #1233
                                    </a>
                                </td>
                                <td>
                                    <div>ООО "Ромашка"</div>
                                    <div style="font-size: 12px; color: var(--gray);">ИНН: 7707123456</div>
                                </td>
                                <td>
                                    <strong>₽24,800</strong>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-university"></i>
                                        <span>Банк</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="transaction-status pending">
                                        <i class="fas fa-clock"></i>
                                        Ожидание
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="viewTransaction('TRX-2025061214200002')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>TRX-2025061213450003</strong>
                                </td>
                                <td>
                                    <div>12.06.2025</div>
                                    <div style="font-size: 12px; color: var(--gray);">13:45:10</div>
                                </td>
                                <td>
                                    <a href="#" style="color: var(--primary); text-decoration: none;">
                                        #1232
                                    </a>
                                </td>
                                <td>
                                    <div>Анна Сидорова</div>
                                    <div style="font-size: 12px; color: var(--gray);">anna@email.com</div>
                                </td>
                                <td>
                                    <strong>₽8,200</strong>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-globe"></i>
                                        <span>Онлайн</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="transaction-status failed">
                                        <i class="fas fa-times-circle"></i>
                                        Отклонено
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="viewTransaction('TRX-2025061213450003')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>TRX-2025061211300004</strong>
                                </td>
                                <td>
                                    <div>12.06.2025</div>
                                    <div style="font-size: 12px; color: var(--gray);">11:30:45</div>
                                </td>
                                <td>
                                    <a href="#" style="color: var(--primary); text-decoration: none;">
                                        #1230
                                    </a>
                                </td>
                                <td>
                                    <div>Михаил Козлов</div>
                                    <div style="font-size: 12px; color: var(--gray);">+7 916 555-12-34</div>
                                </td>
                                <td>
                                    <strong>₽5,600</strong>
                                </td>
                                <td>
                                    <div class="payment-method">
                                        <i class="fas fa-money-bill"></i>
                                        <span>Наличные</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="transaction-status success">
                                        <i class="fas fa-check-circle"></i>
                                        Успешно
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="viewTransaction('TRX-2025061211300004')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <div class="pagination">
                    <button class="pagination-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="pagination-btn active">1</button>
                    <button class="pagination-btn">2</button>
                    <button class="pagination-btn">3</button>
                    <span style="color: var(--gray);">...</span>
                    <button class="pagination-btn">18</button>
                    <button class="pagination-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Модальное окно деталей транзакции -->
    <div class="modal" id="transactionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="font-size: 20px;">Детали транзакции</h2>
                <button class="btn btn-secondary" onclick="closeModal('transactionModal')" style="padding: 8px 12px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="detail-section">
                    <h3 class="detail-title">
                        <i class="fas fa-info-circle"></i>
                        Основная информация
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">ID транзакции</span>
                        <span class="detail-value">TRX-2025061214350001</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Дата и время</span>
                        <span class="detail-value">12.06.2025 14:35:22</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Статус</span>
                        <span class="detail-value">
                            <span class="transaction-status success">
                                <i class="fas fa-check-circle"></i>
                                Успешно
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Сумма</span>
                        <span class="detail-value" style="font-size: 18px; font-weight: 700;">₽15,500</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3 class="detail-title">
                        <i class="fas fa-shopping-cart"></i>
                        Информация о заказе
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">Номер заказа</span>
                        <span class="detail-value">
                            <a href="#" style="color: var(--primary);">#1234</a>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Клиент</span>
                        <span class="detail-value">Иван Петров</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Телефон</span>
                        <span class="detail-value">+7 (999) 123-45-67</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">ivan@example.com</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3 class="detail-title">
                        <i class="fas fa-credit-card"></i>
                        Детали платежа
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">Метод оплаты</span>
                        <span class="detail-value">Банковская карта</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Платежная система</span>
                        <span class="detail-value">Visa **** 1234</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Банк-эмитент</span>
                        <span class="detail-value">Сбербанк</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">ID платежа в системе</span>
                        <span class="detail-value">PAY-123456789</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3 class="detail-title">
                        <i class="fas fa-server"></i>
                        Технические данные
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">IP адрес</span>
                        <span class="detail-value">192.168.1.100</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Страна</span>
                        <span class="detail-value">Россия</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Ответ платежной системы</span>
                        <span class="detail-value" style="font-family: monospace; font-size: 12px;">
                            {"status": "success", "code": "00"}
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 20px 24px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between;">
                <button class="btn btn-secondary" onclick="printTransaction()">
                    <i class="fas fa-print"></i>
                    Печать
                </button>
                <div>
                    <button class="btn btn-danger" onclick="refundTransaction('TRX-2025061214350001')">
                        <i class="fas fa-undo"></i>
                        Возврат
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // График транзакций
        const ctx = document.getElementById('transactionsChart').getContext('2d');
        const transactionsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1 июня', '2 июня', '3 июня', '4 июня', '5 июня', '6 июня', '7 июня', '8 июня', '9 июня', '10 июня', '11 июня', '12 июня'],
                datasets: [{
                    label: 'Успешные',
                    data: [45000, 52000, 48000, 61000, 58000, 42000, 55000, 67000, 71000, 64000, 69000, 73000],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Неудачные',
                    data: [2000, 1500, 2200, 1800, 2500, 1200, 1600, 2000, 1900, 2100, 1700, 1500],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₽' + context.parsed.y.toLocaleString('ru-RU');
                            }
                        }
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

        // Просмотр транзакции
        function viewTransaction(transactionId) {
            console.log('Просмотр транзакции:', transactionId);
            document.getElementById('transactionModal').classList.add('active');
            // Загрузка данных транзакции
        }

        // Закрытие модального окна
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Добавление ручного платежа
        function addManualTransaction() {
            console.log('Добавление ручного платежа');
            // Открытие формы добавления
        }

        // Экспорт транзакций
        function exportTransactions() {
            console.log('Экспорт транзакций');
            // Открытие модального окна экспорта
        }

        // Возврат средств
        function refundTransaction(transactionId) {
            if (confirm('Вы уверены, что хотите сделать возврат?')) {
                console.log('Возврат транзакции:', transactionId);
                // AJAX запрос на возврат
            }
        }

        // Печать детали транзакции
        function printTransaction() {
            window.print();
        }

        // Изменение периода графика
        function changeChartPeriod(period) {
            console.log('Изменение периода на:', period);
            // Обновление данных графика
            
            // Обновление активной кнопки
            document.querySelectorAll('.chart-header button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Фильтрация транзакций
        document.querySelectorAll('.filters-card select, .filters-card input').forEach(input => {
            input.addEventListener('change', function() {
                console.log('Применение фильтров');
                // AJAX запрос для фильтрации
            });
        });

        // Поиск по транзакциям
        let searchTimeout;
        document.querySelector('.filters-card input[type="text"]').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                console.log('Поиск:', e.target.value);
                // AJAX запрос для поиска
            }, 500);
        });

        // Загрузка данных при инициализации
        document.addEventListener('DOMContentLoaded', function() {
            loadTransactions();
            loadStatistics();
        });

        async function loadTransactions() {
            try {
                const response = await fetch('/admin/api/transactions.php');
                const data = await response.json();
                
                if (data.success) {
                    // Обновление таблицы транзакций
                    console.log('Транзакции загружены');
                }
            } catch (error) {
                console.error('Ошибка загрузки транзакций:', error);
            }
        }

        async function loadStatistics() {
            try {
                const response = await fetch('/admin/api/transactions.php?action=stats');
                const data = await response.json();
                
                if (data.success) {
                    // Обновление статистики
                    console.log('Статистика загружена');
                }
            } catch (error) {
                console.error('Ошибка загрузки статистики:', error);
            }
        }

        // Автообновление каждые 30 секунд
        setInterval(() => {
            loadTransactions();
            loadStatistics();
        }, 30000);
    </script>
</body>
</html>