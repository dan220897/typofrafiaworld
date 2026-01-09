<?php
// admin/index.php - Главная страница админ-панели

// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Проверяем существование файлов
$files_to_check = [
    'includes/auth_check.php',
    'config/database.php',
    'classes/Dashboard.php'
];

foreach ($files_to_check as $file) {
    if (!file_exists($file)) {
        die("Ошибка: Файл {$file} не найден!");
    }
}

require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Dashboard.php';

// Проверяем авторизацию
checkAdminAuth();

// Получаем статистику
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Не удалось подключиться к базе данных");
    }
    
    $dashboard = new Dashboard($db);
    $stats = $dashboard->getStats();
    
    // Проверяем, что статистика получена
    if (!is_array($stats)) {
        throw new Exception("Ошибка получения статистики");
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee; color: #c00; padding: 20px; margin: 20px; border: 1px solid #fcc; border-radius: 5px;'>";
    echo "<h3>Ошибка:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    
    // Дополнительная отладочная информация
    echo "<h4>Отладочная информация:</h4>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    echo "<p>MySQL Client: " . mysqli_get_client_info() . "</p>";
    
    if (isset($db)) {
        try {
            $stmt = $db->query("SELECT VERSION() as version");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>MySQL Server: " . $row['version'] . "</p>";
        } catch (Exception $dbError) {
            echo "<p>Ошибка при получении версии MySQL: " . $dbError->getMessage() . "</p>";
        }
    }
    
    echo "</div>";
    exit;
}

// Генерируем CSRF токен
$csrf_token = generateCSRFToken();

// Заголовок страницы
$page_title = 'Панель управления';
require_once 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Приветствие -->
    <div class="welcome-section">
        <h1>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h1>
        <p class="text-muted">Роль: <?php echo getAdminRoleLabel($_SESSION['admin_role']); ?></p>
        <p class="text-muted">Последний вход: <?php echo !empty($_SESSION['last_login']) ? formatTime($_SESSION['last_login']) : 'впервые'; ?></p>
    </div>
    
    <!-- Статистика -->
    <div class="stats-grid">
        <!-- Заказы -->
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo isset($stats['orders']['total']) ? number_format($stats['orders']['total']) : '0'; ?></h3>
                <p>Всего заказов</p>
                <div class="stat-details">
                    <?php if (isset($stats['orders']['today'])): ?>
                    <p>Сегодня: <strong><?php echo $stats['orders']['today']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['orders']['yesterday'])): ?>
                    <p>Вчера: <strong><?php echo $stats['orders']['yesterday']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['orders']['change'])): ?>
                    <p>Изменение: 
                        <span class="stat-change <?php echo $stats['orders']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-<?php echo $stats['orders']['change'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs($stats['orders']['change']); ?>%
                        </span>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Выручка -->
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-ruble-sign"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo isset($stats['revenue']['total']) ? number_format($stats['revenue']['total'], 0, '', ' ') : '0'; ?> ₽</h3>
                <p>Общая выручка</p>
                <div class="stat-details">
                    <?php if (isset($stats['revenue']['today'])): ?>
                    <p>Сегодня: <strong><?php echo number_format($stats['revenue']['today'], 0, '', ' '); ?> ₽</strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['revenue']['yesterday'])): ?>
                    <p>Вчера: <strong><?php echo number_format($stats['revenue']['yesterday'], 0, '', ' '); ?> ₽</strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['revenue']['change'])): ?>
                    <p>Изменение: 
                        <span class="stat-change <?php echo $stats['revenue']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-<?php echo $stats['revenue']['change'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs($stats['revenue']['change']); ?>%
                        </span>
                    </p>
                    <?php endif; ?>
                    <?php if (isset($stats['revenue']['average_order'])): ?>
                    <p>Средний чек: <strong><?php echo number_format($stats['revenue']['average_order'], 0, '', ' '); ?> ₽</strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Пользователи -->
        <div class="stat-card">
            <div class="stat-icon users">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo isset($stats['users']['total']) ? number_format($stats['users']['total']) : '0'; ?></h3>
                <p>Пользователей</p>
                <div class="stat-details">
                    <?php if (isset($stats['users']['new_today'])): ?>
                    <p>Новых сегодня: <strong><?php echo $stats['users']['new_today']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['users']['new_this_month'])): ?>
                    <p>Новых в этом месяце: <strong><?php echo $stats['users']['new_this_month']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['users']['active_today'])): ?>
                    <p>Активных сегодня: <strong><?php echo $stats['users']['active_today']; ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Активные чаты -->
        <div class="stat-card">
            <div class="stat-icon chats">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo isset($stats['chats']['active']) ? $stats['chats']['active'] : '0'; ?></h3>
                <p>Активных чатов</p>
                <div class="stat-details">
                    <?php if (isset($stats['chats']['unread'])): ?>
                    <p>Непрочитанных: <strong><?php echo $stats['chats']['unread']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['chats']['avg_response_time'])): ?>
                    <p>Среднее время ответа: <strong><?php echo $stats['chats']['avg_response_time']; ?> мин</strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['chats']['resolved_today'])): ?>
                    <p>Решено сегодня: <strong><?php echo $stats['chats']['resolved_today']; ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Товары -->
        <div class="stat-card">
            <div class="stat-icon products">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo isset($stats['products']['total']) ? number_format($stats['products']['total']) : '0'; ?></h3>
                <p>Товаров в каталоге</p>
                <div class="stat-details">
                    <?php if (isset($stats['products']['out_of_stock'])): ?>
                    <p>Нет в наличии: <strong><?php echo $stats['products']['out_of_stock']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['products']['low_stock'])): ?>
                    <p>Заканчиваются: <strong><?php echo $stats['products']['low_stock']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['products']['popular'])): ?>
                    <p>Популярных: <strong><?php echo $stats['products']['popular']; ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Посещаемость -->
        <div class="stat-card">
            <div class="stat-icon visitors">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo isset($stats['visitors']['today']) ? number_format($stats['visitors']['today']) : '0'; ?></h3>
                <p>Посетителей сегодня</p>
                <div class="stat-details">
                    <?php if (isset($stats['visitors']['yesterday'])): ?>
                    <p>Вчера: <strong><?php echo number_format($stats['visitors']['yesterday']); ?></strong></p>
                    <?php endif; ?>
                    <?php if (isset($stats['visitors']['change'])): ?>
                    <p>Изменение: 
                        <span class="stat-change <?php echo $stats['visitors']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-<?php echo $stats['visitors']['change'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs($stats['visitors']['change']); ?>%
                        </span>
                    </p>
                    <?php endif; ?>
                    <?php if (isset($stats['visitors']['online'])): ?>
                    <p>Сейчас онлайн: <strong><?php echo $stats['visitors']['online']; ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Детальная статистика -->
    <div class="detailed-stats">
        <div class="stat-block">
            <h3>Статистика заказов</h3>
            <div class="stat-row">
                <div class="stat-item">
                    <span class="stat-label">Новые</span>
                    <span class="stat-value"><?php echo isset($stats['orders']['status_new']) ? $stats['orders']['status_new'] : '0'; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">В обработке</span>
                    <span class="stat-value"><?php echo isset($stats['orders']['status_processing']) ? $stats['orders']['status_processing'] : '0'; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Доставляются</span>
                    <span class="stat-value"><?php echo isset($stats['orders']['status_shipping']) ? $stats['orders']['status_shipping'] : '0'; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Завершены</span>
                    <span class="stat-value"><?php echo isset($stats['orders']['status_completed']) ? $stats['orders']['status_completed'] : '0'; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Отменены</span>
                    <span class="stat-value"><?php echo isset($stats['orders']['status_canceled']) ? $stats['orders']['status_canceled'] : '0'; ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-block">
            <h3>Статистика пользователей</h3>
            <div class="stat-row">
                <div class="stat-item">
                    <span class="stat-label">Всего</span>
                    <span class="stat-value"><?php echo isset($stats['users']['total']) ? number_format($stats['users']['total']) : '0'; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Новых сегодня</span>
                    <span class="stat-value"><?php echo isset($stats['users']['new_today']) ? $stats['users']['new_today'] : '0'; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Активных</span>
                    <span class="stat-value"><?php echo isset($stats['users']['active']) ? $stats['users']['active'] : '0'; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Неактивных</span>
                    <span class="stat-value"><?php echo isset($stats['users']['inactive']) ? $stats['users']['inactive'] : '0'; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Последние действия -->
    <div class="recent-section">
        <!-- Последние заказы -->
        <div class="recent-card">
            <div class="recent-header">
                <h3>Последние заказы</h3>
                <a href="orders.php" class="view-all">Все заказы <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="recent-list">
                <?php if (isset($stats['recent_orders']) && !empty($stats['recent_orders'])): ?>
                    <?php foreach ($stats['recent_orders'] as $order): ?>
                    <div class="recent-item">
                        <div class="recent-info">
                            <strong>#<?php echo htmlspecialchars($order['order_number'] ?? ''); ?></strong>
                            <span class="text-muted"><?php echo htmlspecialchars($order['user_name'] ?? 'Гость'); ?></span>
                            <span class="text-muted"><?php echo number_format($order['amount'] ?? 0, 0, '', ' '); ?> ₽</span>
                        </div>
                        <div class="recent-meta">
                            <span class="order-status <?php echo htmlspecialchars($order['status'] ?? ''); ?>">
                                <?php echo getOrderStatusLabel($order['status'] ?? ''); ?>
                            </span>
                            <span class="recent-time"><?php echo formatTime($order['created_at'] ?? ''); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">Нет заказов</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Последние пользователи -->
        <div class="recent-card">
            <div class="recent-header">
                <h3>Новые пользователи</h3>
                <a href="users.php" class="view-all">Все пользователи <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="recent-list">
                <?php if (isset($stats['recent_users']) && !empty($stats['recent_users'])): ?>
                    <?php foreach ($stats['recent_users'] as $user): ?>
                    <div class="recent-item">
                        <div class="recent-info">
                            <strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong>
                            <span class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                            <span class="text-muted">Заказов: <?php echo $user['orders_count'] ?? 0; ?></span>
                        </div>
                        <div class="recent-meta">
                            <span class="recent-time">Зарегистрирован: <?php echo formatTime($user['created_at'] ?? ''); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">Нет новых пользователей</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.welcome-section {
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.welcome-section h1 {
    font-size: 28px;
    margin-bottom: 5px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    float: left;
    margin-right: 15px;
}

.stat-icon.orders { background: #e3f2fd; color: #1976d2; }
.stat-icon.revenue { background: #e8f5e9; color: #388e3c; }
.stat-icon.users { background: #f3e5f5; color: #7b1fa2; }
.stat-icon.chats { background: #fff3e0; color: #f57c00; }
.stat-icon.products { background: #e0f7fa; color: #00acc1; }
.stat-icon.visitors { background: #fce4ec; color: #c2185b; }

.stat-content h3 {
    font-size: 28px;
    margin: 0 0 10px 0;
    font-weight: 600;
}

.stat-content p {
    margin: 5px 0;
    color: #666;
}

.stat-details {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.stat-details p {
    margin: 5px 0;
    font-size: 14px;
}

.stat-change {
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.stat-change.positive { color: #388e3c; }
.stat-change.negative { color: #d32f2f; }

.detailed-stats {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.detailed-stats h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
}

.stat-block {
    margin-bottom: 20px;
}

.stat-block:last-child {
    margin-bottom: 0;
}

.stat-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.stat-item {
    flex: 1;
    min-width: 150px;
    background: #f9fafb;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.stat-label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 18px;
    font-weight: 600;
}

.recent-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.recent-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.recent-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.recent-header h3 {
    margin: 0;
    font-size: 18px;
}

.view-all {
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-all:hover {
    text-decoration: underline;
}

.recent-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.recent-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
}

.recent-info {
    flex: 1;
}

.recent-info strong {
    display: block;
    margin-bottom: 5px;
}

.recent-info .text-muted {
    font-size: 14px;
    color: #666;
    display: block;
    margin: 2px 0;
}

.recent-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.order-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.order-status.new { background: #e3f2fd; color: #1976d2; }
.order-status.in_progress { background: #fff3e0; color: #f57c00; }
.order-status.ready { background: #e8f5e9; color: #388e3c; }
.order-status.delivered { background: #f3e5f5; color: #7b1fa2; }
.order-status.canceled { background: #ffebee; color: #d32f2f; }

.recent-time {
    font-size: 13px;
    color: #999;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #666;
}
</style>

<script>
// Автообновление статистики каждые 30 секунд
setInterval(() => {
    fetch('api/dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.stats);
            }
        });
}, 30000);

function updateDashboardStats(stats) {
    // Здесь можно добавить логику обновления статистики на странице без перезагрузки
    console.log('Stats updated', stats);
}
</script>

<?php
require_once 'includes/footer.php';

// Вспомогательные функции
function getAdminRoleLabel($role) {
    $roles = [
        'super_admin' => 'Суперадминистратор',
        'admin' => 'Администратор',
        'manager' => 'Менеджер',
        'operator' => 'Оператор'
    ];
    return $roles[$role] ?? $role;
}

function getOrderStatusLabel($status) {
    $statuses = [
        'new' => 'Новый',
        'processing' => 'В обработке',
        'shipping' => 'Доставляется',
        'completed' => 'Завершен',
        'canceled' => 'Отменен'
    ];
    return $statuses[$status] ?? $status;
}

function formatTime($timestamp) {
    if (empty($timestamp)) {
        return '';
    }
    
    try {
        $time = strtotime($timestamp);
        if ($time === false) {
            return '';
        }
        
        $diff = time() - $time;
        
        if ($diff < 60) return 'только что';
        if ($diff < 3600) return floor($diff / 60) . ' мин назад';
        if ($diff < 86400) return floor($diff / 3600) . ' ч назад';
        if ($diff < 604800) return floor($diff / 86400) . ' д назад';
        
        return date('d.m.Y', $time);
    } catch (Exception $e) {
        return '';
    }
}
?>