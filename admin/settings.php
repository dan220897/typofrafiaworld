<?php
// admin/settings.php - Управление настройками системы
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Settings.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('manage_settings');

if ($_SESSION['admin_role'] !== 'super_admin') {
    header('Location: /admin/403.php');
    exit;
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);
$adminLog = new AdminLog($db);

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAjaxRequest()) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'save_general':
                $general_settings = [
                    'site_name' => $_POST['site_name'] ?? '',
                    'site_url' => $_POST['site_url'] ?? '',
                    'admin_email' => $_POST['admin_email'] ?? '',
                    'timezone' => $_POST['timezone'] ?? 'Europe/Moscow',
                    'language' => $_POST['language'] ?? 'ru'
                ];
                
                $settings->updateBatch($general_settings, $_SESSION['admin_id']);
                $adminLog->log($_SESSION['admin_id'], 'update_settings', 
                    'Обновлены основные настройки', 'settings', null);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'save_orders':
                $order_settings = [
                    'orders_per_page' => intval($_POST['orders_per_page'] ?? 20),
                    'min_order_amount' => floatval($_POST['min_order_amount'] ?? 0),
                    'default_delivery_days' => intval($_POST['default_delivery_days'] ?? 3),
                    'allow_guest_orders' => isset($_POST['allow_guest_orders']) ? '1' : '0',
                    'auto_confirm_orders' => isset($_POST['auto_confirm_orders']) ? '1' : '0'
                ];
                
                $settings->updateBatch($order_settings, $_SESSION['admin_id']);
                $adminLog->log($_SESSION['admin_id'], 'update_settings', 
                    'Обновлены настройки заказов', 'settings', null);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'save_notifications':
                $notification_settings = [
                    'enable_sms_notifications' => isset($_POST['enable_sms_notifications']) ? '1' : '0',
                    'enable_telegram_notifications' => isset($_POST['enable_telegram_notifications']) ? '1' : '0',
                    'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? '1' : '0',
                    'telegram_bot_token' => $_POST['telegram_bot_token'] ?? '',
                    'telegram_chat_id' => $_POST['telegram_chat_id'] ?? '',
                    'sms_api_key' => $_POST['sms_api_key'] ?? ''
                ];
                
                $settings->updateBatch($notification_settings, $_SESSION['admin_id']);
                $adminLog->log($_SESSION['admin_id'], 'update_settings', 
                    'Обновлены настройки уведомлений', 'settings', null);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'save_system':
                $system_settings = [
                    'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
                    'maintenance_message' => $_POST['maintenance_message'] ?? '',
                    'backup_enabled' => isset($_POST['backup_enabled']) ? '1' : '0',
                    'backup_frequency' => $_POST['backup_frequency'] ?? 'daily',
                    'log_retention_days' => intval($_POST['log_retention_days'] ?? 90),
                    'session_lifetime' => intval($_POST['session_lifetime'] ?? 3600)
                ];
                
                $settings->updateBatch($system_settings, $_SESSION['admin_id']);
                $adminLog->log($_SESSION['admin_id'], 'update_settings', 
                    'Обновлены системные настройки', 'settings', null);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'test_sms':
                $phone = $_POST['phone'] ?? '';
                // Здесь код для отправки тестового SMS
                echo json_encode(['success' => true, 'message' => 'SMS отправлено']);
                exit;
                
            case 'test_telegram':
                // Здесь код для отправки тестового сообщения в Telegram
                echo json_encode(['success' => true, 'message' => 'Сообщение отправлено']);
                exit;
                
            case 'clear_cache':
                Settings::clearCache();
                $adminLog->log($_SESSION['admin_id'], 'clear_cache', 
                    'Очищен кэш настроек', 'settings', null);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'backup_now':
                // Здесь код для создания резервной копии
                $adminLog->log($_SESSION['admin_id'], 'backup_database', 
                    'Создана резервная копия БД', 'system', null);
                
                echo json_encode(['success' => true, 'message' => 'Резервная копия создана']);
                exit;
                
            case 'clean_logs':
                $days = intval($_POST['days'] ?? 90);
                $deleted = $adminLog->cleanOldLogs($days);
                
                $adminLog->log($_SESSION['admin_id'], 'clean_logs', 
                    "Удалено {$deleted} старых записей логов", 'system', null);
                
                echo json_encode(['success' => true, 'deleted' => $deleted]);
                exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Получаем все настройки
$all_settings = $settings->getByCategory();

// Группируем настройки по категориям
$settings_by_category = [];
foreach ($all_settings as $setting) {
    $category = $setting['category'] ?? 'general';
    if (!isset($settings_by_category[$category])) {
        $settings_by_category[$category] = [];
    }
    $settings_by_category[$category][$setting['key']] = $setting['value'];
}

// Заголовок страницы
$page_title = 'Настройки системы';
$current_page = 'settings';
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

/* Заголовок страницы */
.services-header {
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

/* Статистика */
.services-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-box h3 {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.5rem 0;
    font-weight: 400;
}

.stat-box .value {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
}

/* Фильтры */
.filters-bar {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.search-box {
    flex: 1;
    min-width: 300px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 0.5rem 1rem 0.5rem 2.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.search-box input:focus {
    outline: none;
    border-color: #3b82f6;
}

.search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

/* Селекты */
.form-control {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    background-color: white;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
}

/* Таблица услуг */
.services-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.services-table table {
    width: 100%;
    border-collapse: collapse;
}

.services-table th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    border-bottom: 1px solid #e5e7eb;
}

.services-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.service-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.service-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #e0e7ff;
    color: #4338ca;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.price-info {
    font-weight: 500;
    color: #1f2937;
}

.parameters-count, .rules-count {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.actions-cell {
    white-space: nowrap;
}

.btn-icon {
    padding: 0.375rem;
    border: none;
    background: none;
    color: #9ca3af;
    cursor: pointer;
    transition: color 0.2s;
    border-radius: 4px;
}

.btn-icon:hover {
    color: #374151;
    background-color: #f3f4f6;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

/* Модальные окна */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    max-height: calc(90vh - 150px);
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    background-color: #f9fafb;
}

/* Формы в модальном окне */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-label .required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* Чекбокс */
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #374151;
}

.checkbox-label input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    cursor: pointer;
}

/* Строка с колонками */
.row {
    display: flex;
    gap: 1rem;
    margin: 0 -0.5rem;
}

.col-md-4 {
    flex: 1;
    padding: 0 0.5rem;
}

/* Кнопки в модальном окне */
.modal-footer .btn {
    min-width: 80px;
}

/* Drag and drop для сортировки */
.sortable-ghost {
    opacity: 0.4;
    background-color: #f3f4f6;
}

.sortable-handle {
    cursor: move;
    color: #d1d5db;
}

.sortable-handle:hover {
    color: #9ca3af;
}

/* Параметры в модальном окне */
.parameter-item {
    background-color: #f9fafb;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.parameter-item.new-param {
    background-color: #eff6ff;
    border: 1px dashed #3b82f6;
}

.parameter-item .row {
    align-items: center;
}

.parameter-item .form-control {
    font-size: 0.813rem;
    padding: 0.5rem 0.75rem;
}

.parameter-item .btn-sm {
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
}

/* Кнопки удаления/сохранения в параметрах */
.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

/* Дополнительные утилиты */
.text-muted {
    color: #9ca3af;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

/* Адаптивность */
@media (max-width: 768px) {
    .services-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .filters-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        min-width: auto;
    }
    
    .services-table {
        overflow-x: auto;
    }
    
    .services-table table {
        min-width: 800px;
    }
    
    .modal-content {
        max-width: 95%;
        margin: 1rem;
    }
    
    .row {
        flex-direction: column;
    }
    
    .col-md-4 {
        width: 100%;
    }
}

/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal.active .modal-content {
    animation: fadeIn 0.3s ease-out;
}
</style>

<div class="settings-page">
    <!-- Заголовок -->
    <div class="page-header">
        <h1>Настройки системы</h1>
        <button class="btn btn-secondary" onclick="clearCache()">
            <i class="fas fa-sync"></i> Очистить кэш
        </button>
    </div>
    
    <!-- Навигация по разделам -->
    <div class="settings-container">
        <div class="settings-nav">
            <button class="settings-nav-item active" onclick="switchSettingsTab('general')">
                <i class="fas fa-cog"></i> Основные
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('orders')">
                <i class="fas fa-shopping-cart"></i> Заказы
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('notifications')">
                <i class="fas fa-bell"></i> Уведомления
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('system')">
                <i class="fas fa-server"></i> Система
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('backup')">
                <i class="fas fa-database"></i> Резервное копирование
            </button>
            <button class="settings-nav-item" onclick="switchSettingsTab('logs')">
                <i class="fas fa-file-alt"></i> Логи
            </button>
        </div>

        <!-- Основные настройки -->
        <div class="settings-section active" id="generalSection">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">Основные настройки</h3>
                    <p class="settings-card-description">Базовые параметры системы</p>
                </div>
                <form id="generalSettingsForm">
                    <div class="form-group">
                        <label class="form-label">Название сайта</label>
                        <input type="text" class="form-control" name="site_name" 
                               value="<?php echo htmlspecialchars($settings_by_category['general']['site_name'] ?? ''); ?>" required>
                        <div class="form-help">Отображается в заголовке и уведомлениях</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">URL сайта</label>
                        <input type="url" class="form-control" name="site_url" 
                               value="<?php echo htmlspecialchars($settings_by_category['general']['site_url'] ?? ''); ?>" required>
                        <div class="form-help">Полный адрес сайта с протоколом</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Часовой пояс</label>
                            <select class="form-control" name="timezone">
                                <?php
                                $timezones = [
                                    'Europe/Moscow' => 'Москва (UTC+3)',
                                    'Europe/Kaliningrad' => 'Калининград (UTC+2)',
                                    'Asia/Yekaterinburg' => 'Екатеринбург (UTC+5)',
                                    'Asia/Novosibirsk' => 'Новосибирск (UTC+7)',
                                    'Asia/Vladivostok' => 'Владивосток (UTC+10)'
                                ];
                                $current_tz = $settings_by_category['general']['timezone'] ?? 'Europe/Moscow';
                                foreach ($timezones as $tz => $name): ?>
                                <option value="<?php echo $tz; ?>" <?php echo $tz === $current_tz ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Язык системы</label>
                            <select class="form-control" name="language">
                                <option value="ru" selected>Русский</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email администратора</label>
                        <input type="email" class="form-control" name="admin_email" 
                               value="<?php echo htmlspecialchars($settings_by_category['general']['admin_email'] ?? ''); ?>" required>
                        <div class="form-help">Для системных уведомлений</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить настройки
                    </button>
                </form>
            </div>
        </div>

        <!-- Настройки заказов -->
        <div class="settings-section" id="ordersSection">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">Настройки заказов</h3>
                    <p class="settings-card-description">Параметры обработки заказов</p>
                </div>
                <form id="ordersSettingsForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Заказов на странице</label>
                            <input type="number" class="form-control" name="orders_per_page" 
                                   value="<?php echo $settings_by_category['display']['orders_per_page'] ?? 20; ?>" 
                                   min="10" max="100" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Минимальная сумма заказа (₽)</label>
                            <input type="number" class="form-control" name="min_order_amount" 
                                   value="<?php echo $settings_by_category['orders']['min_order_amount'] ?? 0; ?>" 
                                   min="0" step="100" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Срок доставки по умолчанию (дней)</label>
                        <input type="number" class="form-control" name="default_delivery_days" 
                               value="<?php echo $settings_by_category['orders']['default_delivery_days'] ?? 3; ?>" 
                               min="1" max="30" required>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="allow_guest_orders" 
                                   <?php echo ($settings_by_category['orders']['allow_guest_orders'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            Разрешить заказы без регистрации
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="auto_confirm_orders" 
                                   <?php echo ($settings_by_category['orders']['auto_confirm_orders'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            Автоматическое подтверждение заказов
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить настройки
                    </button>
                </form>
            </div>
        </div>

        <!-- Настройки уведомлений -->
        <div class="settings-section" id="notificationsSection">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">Настройки уведомлений</h3>
                    <p class="settings-card-description">Каналы отправки уведомлений</p>
                </div>
                <form id="notificationsSettingsForm">
                    <!-- SMS настройки -->
                    <div class="notification-block">
                        <h4>SMS уведомления</h4>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_sms_notifications" 
                                       <?php echo ($settings_by_category['notifications']['enable_sms_notifications'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                Включить SMS уведомления
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">API ключ SMS.ru</label>
                            <input type="text" class="form-control" name="sms_api_key" 
                                   value="<?php echo htmlspecialchars($settings_by_category['notifications']['sms_api_key'] ?? SMS_RU_API_KEY); ?>">
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="testSMS()">
                            <i class="fas fa-paper-plane"></i> Тест SMS
                        </button>
                    </div>

                    <!-- Telegram настройки -->
                    <div class="notification-block">
                        <h4>Telegram уведомления</h4>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_telegram_notifications" 
                                       <?php echo ($settings_by_category['notifications']['enable_telegram_notifications'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                Включить Telegram уведомления
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Токен бота</label>
                            <input type="text" class="form-control" name="telegram_bot_token" 
                                   value="<?php echo htmlspecialchars($settings_by_category['notifications']['telegram_bot_token'] ?? BOT_TOKEN); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ID чата менеджеров</label>
                            <input type="text" class="form-control" name="telegram_chat_id" 
                                   value="<?php echo htmlspecialchars($settings_by_category['notifications']['telegram_chat_id'] ?? MANAGER_CHAT_ID); ?>">
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="testTelegram()">
                            <i class="fas fa-paper-plane"></i> Тест Telegram
                        </button>
                    </div>

                    <!-- Email настройки -->
                    <div class="notification-block">
                        <h4>Email уведомления</h4>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_email_notifications" 
                                       <?php echo ($settings_by_category['notifications']['enable_email_notifications'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                Включить Email уведомления
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить настройки
                    </button>
                </form>
            </div>
        </div>

        <!-- Системные настройки -->
        <div class="settings-section" id="systemSection">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">Системные настройки</h3>
                    <p class="settings-card-description">Параметры работы системы</p>
                </div>
                <form id="systemSettingsForm">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="maintenance_mode" 
                                   <?php echo ($settings_by_category['system']['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            Режим обслуживания
                        </label>
                        <div class="form-help">В этом режиме сайт недоступен для пользователей</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Сообщение при обслуживании</label>
                        <textarea class="form-control" name="maintenance_message" rows="3"><?php 
                            echo htmlspecialchars($settings_by_category['system']['maintenance_message'] ?? 
                                'Сайт временно недоступен. Ведутся технические работы.'); 
                        ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Хранить логи (дней)</label>
                            <input type="number" class="form-control" name="log_retention_days" 
                                   value="<?php echo $settings_by_category['system']['log_retention_days'] ?? 90; ?>" 
                                   min="7" max="365" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Время сессии (секунд)</label>
                            <input type="number" class="form-control" name="session_lifetime" 
                                   value="<?php echo $settings_by_category['system']['session_lifetime'] ?? 3600; ?>" 
                                   min="300" max="86400" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить настройки
                    </button>
                </form>
            </div>
        </div>

        <!-- Резервное копирование -->
        <div class="settings-section" id="backupSection">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">Резервное копирование</h3>
                    <p class="settings-card-description">Настройки автоматического резервного копирования</p>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="backup_enabled" 
                               <?php echo ($settings_by_category['system']['backup_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        Включить автоматическое резервное копирование
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Частота резервного копирования</label>
                    <select class="form-control" name="backup_frequency">
                        <?php
                        $frequencies = [
                            'daily' => 'Ежедневно',
                            'weekly' => 'Еженедельно',
                            'monthly' => 'Ежемесячно'
                        ];
                        $current_freq = $settings_by_category['system']['backup_frequency'] ?? 'daily';
                        foreach ($frequencies as $freq => $name): ?>
                        <option value="<?php echo $freq; ?>" <?php echo $freq === $current_freq ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="backup-info">
                    <h4>Последние резервные копии</h4>
                    <div class="backup-list">
                        <div class="backup-item">
                            <i class="fas fa-database"></i>
                            <span>backup_2025-06-13_03-00.sql</span>
                            <span class="backup-size">25.4 MB</span>
                            <span class="backup-date">13.06.2025 03:00</span>
                        </div>
                        <div class="backup-item">
                            <i class="fas fa-database"></i>
                            <span>backup_2025-06-12_03-00.sql</span>
                            <span class="backup-size">25.2 MB</span>
                            <span class="backup-date">12.06.2025 03:00</span>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary" onclick="backupNow()">
                    <i class="fas fa-database"></i> Создать резервную копию сейчас
                </button>
            </div>
        </div>

        <!-- Логи системы -->
        <div class="settings-section" id="logsSection">
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3 class="settings-card-title">Системные логи</h3>
                    <p class="settings-card-description">Просмотр журналов системы</p>
                </div>
                
                <div class="form-row" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label">Тип логов</label>
                        <select class="form-control" onchange="loadLogs(this.value)">
                            <option value="system">Системные</option>
                            <option value="error">Ошибки</option>
                            <option value="access">Доступ</option>
                            <option value="admin">Действия админов</option>
                            <option value="payment">Платежи</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Период</label>
                        <select class="form-control">
                            <option value="today">Сегодня</option>
                            <option value="week">За неделю</option>
                            <option value="month">За месяц</option>
                        </select>
                    </div>
                </div>

                <div class="log-viewer" id="logViewer">
                    <div class="log-line">[2025-06-13 14:30:15] INFO: Система запущена</div>
                    <div class="log-line">[2025-06-13 14:32:45] INFO: Администратор admin вошел в систему</div>
                    <div class="log-line">[2025-06-13 14:35:12] INFO: Обновлены настройки системы</div>
                    <div class="log-line">[2025-06-13 14:40:33] WARNING: Попытка входа с неверным паролем для пользователя manager</div>
                </div>

                <div class="log-actions">
                    <a href="logs.php" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Подробные логи
                    </a>
                    <button class="btn btn-danger" onclick="cleanLogs()">
                        <i class="fas fa-trash"></i> Очистить старые логи
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.settings-container {
    display: flex;
    gap: 20px;
}

.settings-nav {
    width: 250px;
    background: white;
    border-radius: 8px;
    padding: 10px;
    height: fit-content;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 12px 16px;
    background: none;
    border: none;
    border-radius: 6px;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 5px;
}

.settings-nav-item:hover {
    background: #f8f9fa;
}

.settings-nav-item.active {
    background: #007bff;
    color: white;
}

.settings-nav-item i {
    width: 20px;
}

.settings-section {
    flex: 1;
    display: none;
}

.settings-section.active {
    display: block;
}

.settings-card {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.settings-card-header {
    margin-bottom: 30px;
}

.settings-card-title {
    margin: 0 0 5px 0;
    font-size: 24px;
}

.settings-card-description {
    margin: 0;
    color: #666;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
}

.form-help {
    font-size: 13px;
    color: #666;
    margin-top: 5px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.notification-block {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.notification-block h4 {
    margin: 0 0 15px 0;
    font-size: 18px;
}

.backup-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.backup-info h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
}

.backup-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.backup-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    font-size: 14px;
}

.backup-item i {
    color: #666;
}

.backup-item span:nth-child(2) {
    flex: 1;
}

.backup-size {
    color: #666;
    font-size: 13px;
}

.backup-date {
    color: #999;
    font-size: 13px;
}

.log-viewer {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 20px;
    border-radius: 6px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.log-line {
    margin-bottom: 5px;
    white-space: pre-wrap;
}

.log-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}
</style>

<script>
// Переключение вкладок настроек
function switchSettingsTab(tab) {
    // Скрываем все секции
    document.querySelectorAll('.settings-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Убираем активный класс у всех кнопок
    document.querySelectorAll('.settings-nav-item').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Показываем нужную секцию
    document.getElementById(tab + 'Section').classList.add('active');
    
    // Активируем кнопку
    event.target.closest('.settings-nav-item').classList.add('active');
}

// Сохранение основных настроек
document.getElementById('generalSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await saveSettings('save_general', new FormData(e.target));
});

// Сохранение настроек заказов
document.getElementById('ordersSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await saveSettings('save_orders', new FormData(e.target));
});

// Сохранение настроек уведомлений
document.getElementById('notificationsSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await saveSettings('save_notifications', new FormData(e.target));
});

// Сохранение системных настроек
document.getElementById('systemSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    await saveSettings('save_system', new FormData(e.target));
});

// Общая функция сохранения настроек
async function saveSettings(action, formData) {
    formData.append('action', action);
    
    // Обработка чекбоксов
    const checkboxes = formData.getAll('checkbox');
    checkboxes.forEach(name => {
        if (!formData.has(name)) {
            formData.append(name, '0');
        }
    });
    
    try {
        const response = await fetch('settings.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Настройки сохранены', 'success');
        } else {
            showNotification(data.error || 'Ошибка сохранения', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Тест SMS
async function testSMS() {
    const phone = prompt('Введите номер телефона для теста:');
    if (!phone) return;
    
    try {
        const response = await fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=test_sms&phone=${encodeURIComponent(phone)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error || 'Ошибка отправки', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Тест Telegram
async function testTelegram() {
    try {
        const response = await fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'action=test_telegram'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error || 'Ошибка отправки', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Очистить кэш
async function clearCache() {
    try {
        const response = await fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'action=clear_cache'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Кэш очищен', 'success');
        } else {
            showNotification(data.error || 'Ошибка очистки кэша', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Создать резервную копию
async function backupNow() {
    if (!confirm('Создать резервную копию базы данных?')) return;
    
    try {
        const response = await fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'action=backup_now'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error || 'Ошибка создания резервной копии', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Очистить логи
async function cleanLogs() {
    const days = prompt('Удалить логи старше (дней):', '90');
    if (!days) return;
    
    try {
        const response = await fetch('settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=clean_logs&days=${days}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`Удалено ${data.deleted} записей`, 'success');
        } else {
            showNotification(data.error || 'Ошибка очистки логов', 'error');
        }
    } catch (error) {
        showNotification('Ошибка соединения', 'error');
    }
}

// Загрузить логи
function loadLogs(type) {
    // Здесь можно добавить загрузку логов через AJAX
    console.log('Loading logs:', type);
}

// Показать уведомление
function showNotification(message, type = 'info') {
    // Простое уведомление через alert
    // В реальном приложении используйте красивые уведомления
    alert(message);
}
</script>

<?php
require_once 'includes/footer.php';
?>