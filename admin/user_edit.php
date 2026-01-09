<?php
// admin/user_edit.php - Редактирование пользователя
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('edit_users');

// Получаем ID пользователя
$user_id = intval($_GET['id'] ?? 0);
if (!$user_id) {
    header('Location: users.php');
    exit;
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$adminLog = new AdminLog($db);

// Получаем данные пользователя
$userData = $user->getUserById($user_id);
if (!$userData) {
    $_SESSION['error'] = 'Пользователь не найден';
    header('Location: users.php');
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? ''),
            'company_address' => trim($_POST['company_address'] ?? ''),
            'inn' => trim($_POST['inn'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_verified' => isset($_POST['is_verified']) ? 1 : 0
        ];
        
        // Валидация
        if (empty($data['phone'])) {
            throw new Exception('Телефон обязателен для заполнения');
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Неверный формат email');
        }
        
        // Обновляем пользователя
        if ($user->updateUser($user_id, $data)) {
            // Логируем действие
            $adminLog->log($_SESSION['admin_id'], 'update_user', 
                "Обновлен пользователь ID: {$user_id}", 'user', $user_id);
            
            $_SESSION['success'] = 'Пользователь успешно обновлен';
            header('Location: user_edit.php?id=' . $user_id);
            exit;
        } else {
            throw new Exception('Ошибка обновления пользователя');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Получаем историю заказов пользователя
$userOrders = $user->getUserOrders($user_id, 10);
$userStats = $user->getUserStats($user_id);

// Заголовок страницы
$page_title = 'Редактирование пользователя';
$current_page = 'users';
require_once 'includes/header.php';
?>

<style>
/* Основные стили страницы */
body {
    background-color: #f3f4f6;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container-fluid {
    padding: 2rem;
}

/* Навигация */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    font-size: 0.875rem;
}

.breadcrumb a {
    color: #6b7280;
    text-decoration: none;
}

.breadcrumb a:hover {
    color: #3b82f6;
}

.breadcrumb .separator {
    color: #9ca3af;
}

/* Заголовок страницы */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

/* Кнопки */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

/* Карточки */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

/* Форма */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control[readonly] {
    background-color: #f9fafb;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-check input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    cursor: pointer;
}

.form-check label {
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
}

/* Статистика */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-box {
    text-align: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* История заказов */
.orders-list {
    margin-top: 1rem;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.order-item:last-child {
    border-bottom: none;
}

.order-info {
    flex: 1;
}

.order-number {
    font-weight: 500;
    color: #3b82f6;
    text-decoration: none;
}

.order-number:hover {
    text-decoration: underline;
}

.order-date {
    font-size: 0.75rem;
    color: #6b7280;
}

.order-amount {
    font-weight: 600;
    color: #1f2937;
}

/* Алерты */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Адаптив */
@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container-fluid">
    <!-- Хлебные крошки -->
    <div class="breadcrumb">
        <a href="/admin/">Главная</a>
        <span class="separator">/</span>
        <a href="users.php">Пользователи</a>
        <span class="separator">/</span>
        <span>Редактирование</span>
    </div>
    
    <!-- Заголовок -->
    <div class="page-header">
        <h1 class="page-title">Редактирование пользователя #<?php echo $user_id; ?></h1>
        <div>
            <a href="users.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>
    
    <!-- Алерты -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>
    
    <div class="content-grid">
        <!-- Основная форма -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Основная информация</h2>
                </div>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Имя</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Телефон *</label>
                            <input type="tel" name="phone" class="form-control" required
                                   value="<?php echo htmlspecialchars($userData['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Название компании</label>
                        <input type="text" name="company_name" class="form-control" 
                               value="<?php echo htmlspecialchars($userData['company_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Адрес компании</label>
                        <textarea name="company_address" class="form-control" rows="3"><?php echo htmlspecialchars($userData['company_address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ИНН</label>
                            <input type="text" name="inn" class="form-control" maxlength="12"
                                   value="<?php echo htmlspecialchars($userData['inn'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Telegram ID</label>
                            <input type="text" class="form-control" readonly
                                   value="<?php echo htmlspecialchars($userData['telegram_id'] ?? 'Не указан'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-check">
                            <input type="checkbox" id="is_active" name="is_active" value="1" 
                                   <?php echo $userData['is_active'] ? 'checked' : ''; ?>>
                            <label for="is_active">Активный пользователь</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="is_verified" name="is_verified" value="1" 
                                   <?php echo $userData['is_verified'] ? 'checked' : ''; ?>>
                            <label for="is_verified">Подтвержденный аккаунт</label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        
                        <?php if (Admin::hasPermission('delete_users')): ?>
                        <button type="button" class="btn btn-danger" onclick="deleteUser()">
                            <i class="fas fa-trash"></i> Удалить пользователя
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- История заказов -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">История заказов</h2>
                    <a href="orders.php?user_id=<?php echo $user_id; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.75rem;">
                        Все заказы
                    </a>
                </div>
                
                <?php if (empty($userOrders)): ?>
                    <p style="text-align: center; color: #6b7280;">Заказов не найдено</p>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($userOrders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="order-number">
                                    #<?php echo $order['order_number']; ?>
                                </a>
                                <div class="order-date">
                                    <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                    <?php if ($order['items_summary']): ?>
                                        - <?php echo htmlspecialchars($order['items_summary']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="order-amount">
                                ₽<?php echo number_format($order['final_amount'], 0, '', ' '); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Боковая панель -->
        <div>
            <!-- Статистика -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Статистика</h2>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $userStats['orders_count'] ?? 0; ?></div>
                        <div class="stat-label">Заказов</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">₽<?php echo number_format($userStats['total_spent'] ?? 0, 0, '', ' '); ?></div>
                        <div class="stat-label">Сумма</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">₽<?php echo number_format($userStats['average_order'] ?? 0, 0, '', ' '); ?></div>
                        <div class="stat-label">Средний чек</div>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <p style="font-size: 0.875rem; color: #6b7280;">
                        <strong>Регистрация:</strong> <?php echo date('d.m.Y H:i', strtotime($userData['created_at'])); ?>
                    </p>
                    <p style="font-size: 0.875rem; color: #6b7280;">
                        <strong>Последняя активность:</strong> 
                        <?php echo $userData['last_active_at'] ? date('d.m.Y H:i', strtotime($userData['last_active_at'])) : 'Нет данных'; ?>
                    </p>
                </div>
            </div>
            
            <!-- Быстрые действия -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2 class="card-title">Быстрые действия</h2>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="chats.php?user=<?php echo $user_id; ?>" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-comments"></i> Открыть чат
                    </a>
                    <a href="order-create.php?user_id=<?php echo $user_id; ?>" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-plus"></i> Создать заказ
                    </a>
                    <?php if ($userData['email']): ?>
                    <button class="btn btn-secondary" style="width: 100%;" onclick="sendEmail()">
                        <i class="fas fa-envelope"></i> Отправить email
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Удаление пользователя
async function deleteUser() {
    if (!confirm('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить.')) {
        return;
    }
    
    try {
        const response = await fetch('users.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=<?php echo $user_id; ?>'
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = 'users.php';
        } else {
            alert(data.message || 'Ошибка удаления пользователя');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Отправка email
function sendEmail() {
    const email = '<?php echo $userData['email'] ?? ''; ?>';
    if (email) {
        window.location.href = `mailto:${email}`;
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>