<?php
// admin/logs.php - Просмотр логов администраторов
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/AdminLog.php';
require_once 'classes/Admin.php';

// Проверяем авторизацию и права
checkAdminAuth();

if (!isSuperAdmin()) {
    header('Location: /admin/403.php');
    exit;
}

// Получаем параметры фильтрации
$admin_id = $_GET['admin_id'] ?? '';
$action = $_GET['action'] ?? '';
$entity_type = $_GET['entity_type'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 50;

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$adminLog = new AdminLog($db);
$admin = new Admin($db);

// Обработка экспорта
if (isset($_GET['export'])) {
    $filters = [
        'admin_id' => $admin_id ?: null,
        'action' => $action ?: null,
        'entity_type' => $entity_type ?: null,
        'date_from' => $date_from,
        'date_to' => $date_to . ' 23:59:59',
        'search' => $search ?: null
    ];
    
    $csv = $adminLog->exportLogs($filters, 'csv');
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="admin_logs_' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo $csv;
    exit;
}

// Получаем логи
$filters = [
    'admin_id' => $admin_id ?: null,
    'action' => $action ?: null,
    'entity_type' => $entity_type ?: null,
    'date_from' => $date_from,
    'date_to' => $date_to . ' 23:59:59',
    'search' => $search ?: null
];

$offset = ($page - 1) * $per_page;
$logs = $adminLog->getLogs($filters, $per_page, $offset);
$total_logs = $adminLog->getLogsCount($filters);
$total_pages = ceil($total_logs / $per_page);

// Получаем списки для фильтров
$admins = $admin->getAll();
$unique_actions = $adminLog->getUniqueActions();
$entity_types = $adminLog->getEntityTypes();

// Получаем статистику
$admin_stats = $adminLog->getAdminStats($date_from, $date_to . ' 23:59:59');
$action_stats = $adminLog->getActionStats($date_from, $date_to . ' 23:59:59');

// Заголовок страницы
$page_title = 'Логи администраторов';
$current_page = 'logs';
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

<div class="logs-page">
    <!-- Заголовок и действия -->
    <div class="page-header">
        <h1>Логи администраторов</h1>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="exportLogs()">
                <i class="fas fa-download"></i> Экспорт
            </button>
            <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
            <button class="btn btn-danger" onclick="showCleanModal()">
                <i class="fas fa-trash"></i> Очистить старые
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="stats-cards">
        <div class="stat-card">
            <h3>Активность администраторов</h3>
            <div class="admin-activity">
                <?php foreach (array_slice($admin_stats, 0, 5) as $stat): ?>
                <div class="activity-item">
                    <div class="admin-name">
                        <?php echo htmlspecialchars($stat['full_name']); ?>
                        <span class="username">@<?php echo htmlspecialchars($stat['username']); ?></span>
                    </div>
                    <div class="activity-count">
                        <?php echo $stat['actions_count']; ?> действий
                    </div>
                    <?php if ($stat['last_action']): ?>
                    <div class="last-action">
                        Последнее: <?php echo date('d.m.Y H:i', strtotime($stat['last_action'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="stat-card">
            <h3>Популярные действия</h3>
            <div class="action-stats">
                <?php foreach (array_slice($action_stats, 0, 10) as $stat): ?>
                <div class="action-stat-item">
                    <span class="action-name"><?php echo $stat['action']; ?></span>
                    <span class="action-count"><?php echo $stat['count']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Фильтры -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Администратор</label>
                    <select name="admin_id" class="form-control">
                        <option value="">Все</option>
                        <?php foreach ($admins as $admin_item): ?>
                        <option value="<?php echo $admin_item['id']; ?>" 
                                <?php echo $admin_id == $admin_item['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($admin_item['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Действие</label>
                    <select name="action" class="form-control">
                        <option value="">Все</option>
                        <?php foreach ($unique_actions as $action_item): ?>
                        <option value="<?php echo $action_item; ?>" 
                                <?php echo $action == $action_item ? 'selected' : ''; ?>>
                            <?php echo $action_item; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Тип объекта</label>
                    <select name="entity_type" class="form-control">
                        <option value="">Все</option>
                        <?php foreach ($entity_types as $type): ?>
                        <option value="<?php echo $type; ?>" 
                                <?php echo $entity_type == $type ? 'selected' : ''; ?>>
                            <?php echo $type; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label>Дата от</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="<?php echo $date_from; ?>">
                </div>
                
                <div class="filter-group">
                    <label>Дата до</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="<?php echo $date_to; ?>">
                </div>
                
                <div class="filter-group">
                    <label>Поиск</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Поиск в деталях..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Применить
                </button>
                <a href="logs.php" class="btn btn-link">Сбросить</a>
            </div>
        </form>
    </div>
    
    <!-- Таблица логов -->
    <div class="logs-table-wrapper">
        <table class="logs-table">
            <thead>
                <tr>
                    <th width="150">Дата и время</th>
                    <th width="150">Администратор</th>
                    <th width="150">Действие</th>
                    <th width="100">Объект</th>
                    <th>Детали</th>
                    <th width="120">IP адрес</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="text-center">Записи не найдены</td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr class="log-row <?php echo getLogRowClass($log['action']); ?>">
                    <td class="log-date">
                        <?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?>
                    </td>
                    <td class="log-admin">
                        <strong><?php echo htmlspecialchars($log['admin_name'] ?: 'Удален'); ?></strong>
                        <?php if ($log['username']): ?>
                        <br><small>@<?php echo htmlspecialchars($log['username']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="log-action">
                        <span class="action-badge <?php echo getActionBadgeClass($log['action']); ?>">
                            <?php echo formatAction($log['action']); ?>
                        </span>
                    </td>
                    <td class="log-entity">
                        <?php if ($log['entity_type']): ?>
                            <?php echo htmlspecialchars($log['entity_type']); ?>
                            <?php if ($log['entity_id']): ?>
                                #<?php echo $log['entity_id']; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="log-details">
                        <?php echo nl2br(htmlspecialchars($log['details'] ?: '-')); ?>
                    </td>
                    <td class="log-ip">
                        <small><?php echo htmlspecialchars($log['ip_address']); ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Пагинация -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php 
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        ?>
        
        <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="page-first">
            <i class="fas fa-angle-double-left"></i>
        </a>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-prev">
            <i class="fas fa-angle-left"></i>
        </a>
        <?php endif; ?>
        
        <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
           class="<?php echo $i == $page ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-next">
            <i class="fas fa-angle-right"></i>
        </a>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" class="page-last">
            <i class="fas fa-angle-double-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Модальное окно очистки -->
<div class="modal" id="cleanModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Очистка старых логов</h3>
            <button class="modal-close" onclick="closeCleanModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="cleanForm" onsubmit="cleanLogs(event)">
            <div class="form-group">
                <label>Удалить логи старше (дней):</label>
                <input type="number" name="days" class="form-control" 
                       value="90" min="7" max="365" required>
                <div class="form-help">
                    Будут удалены все записи старше указанного количества дней
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeCleanModal()">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.logs-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.stats-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-card h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #333;
}

.admin-activity {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: grid;
    grid-template-columns: 2fr 1fr 2fr;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 14px;
}

.admin-name {
    font-weight: 500;
}

.username {
    color: #666;
    font-weight: normal;
    font-size: 13px;
}

.activity-count {
    text-align: center;
    color: #007bff;
    font-weight: 500;
}

.last-action {
    text-align: right;
    color: #666;
    font-size: 13px;
}

.action-stats {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.action-stat-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 14px;
}

.action-name {
    color: #495057;
}

.action-count {
    color: #007bff;
    font-weight: 500;
}

.filters-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-size: 13px;
    color: #666;
    font-weight: 500;
}

.filter-actions {
    display: flex;
    gap: 10px;
    padding-top: 10px;
}

.logs-table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
}

.logs-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.logs-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: top;
}

.log-row:hover {
    background: #f8f9fa;
}

.log-row.danger {
    background: #fff5f5;
}

.log-row.warning {
    background: #fffbf0;
}

.log-date {
    font-size: 13px;
    color: #666;
}

.log-admin strong {
    color: #333;
}

.log-admin small {
    color: #999;
}

.action-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.action-badge.create {
    background: #d4edda;
    color: #155724;
}

.action-badge.update {
    background: #d1ecf1;
    color: #0c5460;
}

.action-badge.delete {
    background: #f8d7da;
    color: #721c24;
}

.action-badge.login {
    background: #e2e3e5;
    color: #383d41;
}

.log-entity {
    font-size: 13px;
    color: #666;
}

.log-details {
    font-size: 13px;
    line-height: 1.5;
    color: #495057;
}

.log-ip {
    font-family: monospace;
    color: #666;
}

.text-center {
    text-align: center;
    padding: 40px !important;
    color: #999;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    margin-top: 30px;
}

.pagination a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 12px;
    border: 1px solid #dee2e6;
    color: #007bff;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s;
}

.pagination a:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.pagination a.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #999;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.form-help {
    font-size: 13px;
    color: #666;
    margin-top: 5px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
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

.btn-link {
    background: none;
    color: #007bff;
    padding: 10px;
}

.btn-link:hover {
    color: #0056b3;
}
</style>

<script>
// Экспорт логов
function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', '1');
    window.location.href = `logs.php?${params.toString()}`;
}

// Показать модальное окно очистки
function showCleanModal() {
    document.getElementById('cleanModal').style.display = 'flex';
}

// Закрыть модальное окно
function closeCleanModal() {
    document.getElementById('cleanModal').style.display = 'none';
}

// Очистить логи
async function cleanLogs(event) {
    event.preventDefault();
    
    const days = event.target.days.value;
    
    if (!confirm(`Вы уверены, что хотите удалить все логи старше ${days} дней?`)) {
        return;
    }
    
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
            alert(`Удалено ${data.deleted} записей`);
            closeCleanModal();
            location.reload();
        } else {
            alert(data.error || 'Ошибка очистки логов');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}
</script>

<?php
require_once 'includes/footer.php';

// Вспомогательные функции
function getLogRowClass($action) {
    if (strpos($action, 'delete') !== false) {
        return 'danger';
    }
    if (strpos($action, 'error') !== false || strpos($action, 'fail') !== false) {
        return 'warning';
    }
    return '';
}

function getActionBadgeClass($action) {
    if (strpos($action, 'create') !== false || strpos($action, 'add') !== false) {
        return 'create';
    }
    if (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) {
        return 'update';
    }
    if (strpos($action, 'delete') !== false || strpos($action, 'remove') !== false) {
        return 'delete';
    }
    if (strpos($action, 'login') !== false || strpos($action, 'logout') !== false) {
        return 'login';
    }
    return '';
}

function formatAction($action) {
    // Можно добавить русские названия для действий
    $actions = [
        'login' => 'Вход',
        'logout' => 'Выход',
        'create_order' => 'Создание заказа',
        'update_order' => 'Обновление заказа',
        'delete_order' => 'Удаление заказа',
        'create_user' => 'Создание пользователя',
        'update_user' => 'Обновление пользователя',
        'delete_user' => 'Удаление пользователя',
        'create_admin' => 'Создание админа',
        'update_admin' => 'Обновление админа',
        'delete_admin' => 'Удаление админа',
        'update_settings' => 'Обновление настроек',
        'backup_database' => 'Резервное копирование',
        'clean_logs' => 'Очистка логов'
    ];
    
    return $actions[$action] ?? $action;
}
?>