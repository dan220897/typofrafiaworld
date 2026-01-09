<?php
// admin/promocodes.php - Управление промокодами
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Promocode.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('view_promocodes');

// Получаем параметры фильтрации
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$discount_type = $_GET['discount_type'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$promocode = new Promocode($db);
$adminLog = new AdminLog($db);

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAjaxRequest()) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                if (!Admin::hasPermission('edit_promocodes')) {
                    throw new Exception('Недостаточно прав');
                }
                
                $data = [
                    'code' => strtoupper(trim($_POST['code'] ?? '')),
                    'description' => trim($_POST['description'] ?? ''),
                    'discount_type' => $_POST['discount_type'] ?? 'percent',
                    'discount_value' => floatval($_POST['discount_value'] ?? 0),
                    'min_order_amount' => floatval($_POST['min_order_amount'] ?? 0),
                    'max_discount_amount' => !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : null,
                    'usage_limit' => !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null,
                    'user_usage_limit' => intval($_POST['user_usage_limit'] ?? 1),
                    'valid_from' => $_POST['valid_from'] ?? date('Y-m-d H:i:s'),
                    'valid_until' => !empty($_POST['valid_until']) ? $_POST['valid_until'] : null,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'created_by' => $_SESSION['admin_id']
                ];
                
                // Валидация
                if (empty($data['code'])) {
                    throw new Exception('Код промокода обязателен');
                }
                
                if ($data['discount_value'] <= 0) {
                    throw new Exception('Размер скидки должен быть больше 0');
                }
                
                if ($data['discount_type'] === 'percent' && $data['discount_value'] > 100) {
                    throw new Exception('Процент скидки не может быть больше 100');
                }
                
                $promo_id = $promocode->createPromocode($data);
                
                if (!$promo_id) {
                    throw new Exception('Ошибка создания промокода');
                }
                
                // Логируем действие
                $adminLog->log($_SESSION['admin_id'], 'create_promocode', 
                    "Создан промокод: {$data['code']}", 'promocode', $promo_id);
                
                echo json_encode(['success' => true, 'id' => $promo_id]);
                exit;
                
            case 'update':
                if (!Admin::hasPermission('edit_promocodes')) {
                    throw new Exception('Недостаточно прав');
                }
                
                $id = intval($_POST['id'] ?? 0);
                $data = [];
                
                // Собираем только измененные поля
                foreach (['code', 'description', 'discount_type', 'discount_value', 
                         'min_order_amount', 'max_discount_amount', 'usage_limit', 
                         'user_usage_limit', 'valid_from', 'valid_until'] as $field) {
                    if (isset($_POST[$field])) {
                        $data[$field] = $_POST[$field];
                    }
                }
                
                if (isset($_POST['is_active'])) {
                    $data['is_active'] = $_POST['is_active'] ? 1 : 0;
                }
                
                if ($promocode->updatePromocode($id, $data)) {
                    $adminLog->log($_SESSION['admin_id'], 'update_promocode', 
                        "Обновлен промокод ID: {$id}", 'promocode', $id);
                    
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка обновления промокода');
                }
                exit;
                
            case 'toggle_active':
                if (!Admin::hasPermission('edit_promocodes')) {
                    throw new Exception('Недостаточно прав');
                }
                
                $id = intval($_POST['id'] ?? 0);
                $is_active = intval($_POST['is_active'] ?? 0);
                
                if ($promocode->toggleActive($id, $is_active)) {
                    $adminLog->log($_SESSION['admin_id'], 'toggle_promocode', 
                        ($is_active ? 'Активирован' : 'Деактивирован') . " промокод ID: {$id}", 
                        'promocode', $id);
                    
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка изменения статуса');
                }
                exit;
                
            case 'delete':
                if (!Admin::hasPermission('delete_promocodes')) {
                    throw new Exception('Недостаточно прав');
                }
                
                $id = intval($_POST['id'] ?? 0);
                
                if ($promocode->deletePromocode($id)) {
                    $adminLog->log($_SESSION['admin_id'], 'delete_promocode', 
                        "Удален промокод ID: {$id}", 'promocode', $id);
                    
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка удаления промокода');
                }
                exit;
                
            case 'generate_code':
                $length = intval($_POST['length'] ?? 8);
                $code = $promocode->generateCode($length);
                echo json_encode(['success' => true, 'code' => $code]);
                exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Получаем промокоды
$filters = [];
if ($status !== 'all') {
    $filters['status'] = $status;
}
if ($search) {
    $filters['search'] = $search;
}
if ($discount_type) {
    $filters['discount_type'] = $discount_type;
}

$offset = ($page - 1) * $per_page;
$promocodes = $promocode->getPromocodes($filters, $per_page, $offset);
$total_promocodes = $promocode->getPromocodesCount($filters);
$total_pages = ceil($total_promocodes / $per_page);

// Получаем статистику
$stats = $promocode->getStats();

// Заголовок страницы
$page_title = 'Управление промокодами';
$current_page = 'promocodes';
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

<div class="promocodes-page">
    <!-- Заголовок и действия -->
    <div class="page-header">
        <h1>Промокоды</h1>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus"></i> Создать промокод
        </button>
    </div>
    
    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Всего промокодов</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['active']; ?></h3>
                <p>Активных</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_usage']; ?></h3>
                <p>Использований</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ruble-sign"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_discount'], 0, ',', ' '); ?></h3>
                <p>Сумма скидок</p>
            </div>
        </div>
    </div>
    
    <!-- Фильтры -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <label>Статус</label>
                <select name="status" class="form-control">
                    <option value="all">Все</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Активные</option>
                    <option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Истекшие</option>
                    <option value="depleted" <?php echo $status === 'depleted' ? 'selected' : ''; ?>>Исчерпанные</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Тип скидки</label>
                <select name="discount_type" class="form-control">
                    <option value="">Любой</option>
                    <option value="percent" <?php echo $discount_type === 'percent' ? 'selected' : ''; ?>>Процент</option>
                    <option value="fixed" <?php echo $discount_type === 'fixed' ? 'selected' : ''; ?>>Фиксированная</option>
                </select>
            </div>
            
            <div class="filter-group">
                <input type="text" name="search" class="form-control" placeholder="Поиск по коду..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Найти
            </button>
        </form>
    </div>
    
    <!-- Таблица промокодов -->
    <div class="promocodes-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Код</th>
                    <th>Описание</th>
                    <th>Скидка</th>
                    <th>Мин. сумма</th>
                    <th>Использований</th>
                    <th>Период действия</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($promocodes)): ?>
                <tr>
                    <td colspan="8" class="text-center">Промокоды не найдены</td>
                </tr>
                <?php else: ?>
                <?php foreach ($promocodes as $promo): ?>
                <?php
                $now = time();
                $is_expired = $promo['valid_until'] && strtotime($promo['valid_until']) < $now;
                $is_depleted = $promo['usage_limit'] && $promo['usage_count'] >= $promo['usage_limit'];
                $is_not_started = strtotime($promo['valid_from']) > $now;
                ?>
                <tr data-promocode-id="<?php echo $promo['id']; ?>">
                    <td>
                        <strong><?php echo htmlspecialchars($promo['code']); ?></strong>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($promo['description'] ?: '-'); ?>
                    </td>
                    <td>
                        <?php if ($promo['discount_type'] === 'percent'): ?>
                            <?php echo $promo['discount_value']; ?>%
                            <?php if ($promo['max_discount_amount']): ?>
                                <br><small>макс. <?php echo number_format($promo['max_discount_amount'], 0, ',', ' '); ?> ₽</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo number_format($promo['discount_value'], 0, ',', ' '); ?> ₽
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo $promo['min_order_amount'] > 0 
                            ? number_format($promo['min_order_amount'], 0, ',', ' ') . ' ₽' 
                            : '-'; ?>
                    </td>
                    <td>
                        <?php echo $promo['usage_count']; ?>
                        <?php if ($promo['usage_limit']): ?>
                            / <?php echo $promo['usage_limit']; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <small>
                            с <?php echo date('d.m.Y', strtotime($promo['valid_from'])); ?><br>
                            <?php if ($promo['valid_until']): ?>
                                до <?php echo date('d.m.Y', strtotime($promo['valid_until'])); ?>
                            <?php else: ?>
                                бессрочно
                            <?php endif; ?>
                        </small>
                    </td>
                    <td>
                        <?php if (!$promo['is_active']): ?>
                            <span class="badge badge-secondary">Неактивен</span>
                        <?php elseif ($is_expired): ?>
                            <span class="badge badge-danger">Истек</span>
                        <?php elseif ($is_depleted): ?>
                            <span class="badge badge-warning">Исчерпан</span>
                        <?php elseif ($is_not_started): ?>
                            <span class="badge badge-info">Не начался</span>
                        <?php else: ?>
                            <span class="badge badge-success">Активен</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info" onclick="viewPromocode(<?php echo $promo['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <?php if (Admin::hasPermission('edit_promocodes')): ?>
                            <button class="btn btn-sm btn-warning" onclick="editPromocode(<?php echo $promo['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button class="btn btn-sm <?php echo $promo['is_active'] ? 'btn-danger' : 'btn-success'; ?>" 
                                    onclick="toggleActive(<?php echo $promo['id']; ?>, <?php echo $promo['is_active'] ? '0' : '1'; ?>)">
                                <i class="fas <?php echo $promo['is_active'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                            </button>
                            <?php endif; ?>
                            
                            <?php if (Admin::hasPermission('delete_promocodes') && $promo['usage_count'] == 0): ?>
                            <button class="btn btn-sm btn-danger" onclick="deletePromocode(<?php echo $promo['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
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
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
           class="<?php echo $i == $page ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Модальное окно создания/редактирования -->
<div class="modal" id="promocodeModal" style="display: none;">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="modalTitle">Создать промокод</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="promocodeForm" onsubmit="savePromocode(event)">
            <input type="hidden" id="promocodeId" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Код промокода *</label>
                    <div class="input-group">
                        <input type="text" name="code" class="form-control" required 
                               pattern="[A-Z0-9]{4,20}" title="Только заглавные буквы и цифры, 4-20 символов">
                        <button type="button" class="btn btn-secondary" onclick="generateCode()">
                            <i class="fas fa-dice"></i> Генерировать
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <input type="text" name="description" class="form-control" 
                           placeholder="Например: Скидка для новых клиентов">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Тип скидки *</label>
                    <select name="discount_type" class="form-control" onchange="updateDiscountType()" required>
                        <option value="percent">Процент</option>
                        <option value="fixed">Фиксированная сумма</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Размер скидки *</label>
                    <div class="input-group">
                        <input type="number" name="discount_value" class="form-control" 
                               min="0.01" step="0.01" required>
                        <span class="input-group-text" id="discountSuffix">%</span>
                    </div>
                </div>
                
                <div class="form-group" id="maxDiscountGroup">
                    <label>Макс. сумма скидки</label>
                    <input type="number" name="max_discount_amount" class="form-control" 
                           min="0" step="0.01" placeholder="Не ограничено">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Мин. сумма заказа</label>
                    <input type="number" name="min_order_amount" class="form-control" 
                           min="0" step="0.01" value="0">
                </div>
                
                <div class="form-group">
                    <label>Лимит использований</label>
                    <input type="number" name="usage_limit" class="form-control" 
                           min="1" placeholder="Не ограничено">
                </div>
                
                <div class="form-group">
                    <label>Лимит на пользователя</label>
                    <input type="number" name="user_usage_limit" class="form-control" 
                           min="1" value="1">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Начало действия *</label>
                    <input type="datetime-local" name="valid_from" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Окончание действия</label>
                    <input type="datetime-local" name="valid_until" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" checked>
                    Активен
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно просмотра -->
<div class="modal" id="viewModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Информация о промокоде</h3>
            <button class="modal-close" onclick="closeViewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="viewContent">
            <!-- Контент загружается динамически -->
        </div>
    </div>
</div>

<style>
.promocodes-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: #f5f5f5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #666;
}

.stat-icon.active {
    background: #d4edda;
    color: #28a745;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 28px;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    color: #666;
}

.promocodes-table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-secondary {
    background: #e2e3e5;
    color: #383d41;
}

.btn-group {
    display: flex;
    gap: 5px;
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
    max-height: 90vh;
    overflow-y: auto;
}

.modal-lg {
    max-width: 700px;
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

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.input-group {
    display: flex;
    gap: 5px;
}

.input-group .form-control {
    flex: 1;
}

.input-group-text {
    background: #e9ecef;
    border: 1px solid #ced4da;
    padding: 8px 12px;
    border-radius: 4px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 20px;
}

.pagination a {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    color: #007bff;
    text-decoration: none;
    border-radius: 4px;
}

.pagination a.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination a:hover:not(.active) {
    background: #f8f9fa;
}
</style>

<script>
// Показать модальное окно создания
function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Создать промокод';
    document.getElementById('promocodeForm').reset();
    document.getElementById('promocodeId').value = '';
    
    // Устанавливаем текущую дату
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.querySelector('input[name="valid_from"]').value = now.toISOString().slice(0, 16);
    
    document.getElementById('promocodeModal').style.display = 'flex';
}

// Закрыть модальное окно
function closeModal() {
    document.getElementById('promocodeModal').style.display = 'none';
}

// Генерировать код
async function generateCode() {
    try {
        const response = await fetch('promocodes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'action=generate_code&length=8'
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.querySelector('input[name="code"]').value = data.code;
        }
    } catch (error) {
        console.error('Error generating code:', error);
    }
}

// Обновить тип скидки
function updateDiscountType() {
    const type = document.querySelector('select[name="discount_type"]').value;
    const suffix = document.getElementById('discountSuffix');
    const maxGroup = document.getElementById('maxDiscountGroup');
    
    if (type === 'percent') {
        suffix.textContent = '%';
        maxGroup.style.display = 'block';
        document.querySelector('input[name="discount_value"]').max = '100';
    } else {
        suffix.textContent = '₽';
        maxGroup.style.display = 'none';
        document.querySelector('input[name="discount_value"]').removeAttribute('max');
    }
}

// Сохранить промокод
async function savePromocode(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('promocodeId').value;
    
    formData.append('action', id ? 'update' : 'create');
    
    // Преобразуем checkbox
    if (!formData.has('is_active')) {
        formData.append('is_active', '0');
    }
    
    try {
        const response = await fetch('promocodes.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Ошибка сохранения');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Редактировать промокод
async function editPromocode(id) {
    // Здесь нужно загрузить данные промокода
    // Для простоты используем данные из таблицы
    const row = document.querySelector(`tr[data-promocode-id="${id}"]`);
    if (!row) return;
    
    document.getElementById('modalTitle').textContent = 'Редактировать промокод';
    document.getElementById('promocodeId').value = id;
    
    // Заполняем форму данными
    // В реальном приложении лучше загрузить данные через API
    
    document.getElementById('promocodeModal').style.display = 'flex';
}

// Переключить активность
async function toggleActive(id, isActive) {
    try {
        const response = await fetch('promocodes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=toggle_active&id=${id}&is_active=${isActive}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Ошибка изменения статуса');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Удалить промокод
async function deletePromocode(id) {
    if (!confirm('Вы уверены, что хотите удалить этот промокод?')) {
        return;
    }
    
    try {
        const response = await fetch('promocodes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=delete&id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.querySelector(`tr[data-promocode-id="${id}"]`).remove();
        } else {
            alert(data.error || 'Ошибка удаления');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Просмотр промокода
async function viewPromocode(id) {
    // Здесь можно загрузить детальную информацию о промокоде
    document.getElementById('viewModal').style.display = 'flex';
}

// Закрыть окно просмотра
function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}
</script>

<?php
require_once 'includes/footer.php';
?>