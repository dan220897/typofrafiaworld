<?php
// admin/services.php - Страница управления услугами
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Service.php';

// Проверяем авторизацию
checkAdminAuth();

// Проверяем права доступа - только для суперадмина
if (!isSuperAdmin()) {
    header('Location: /admin/403.php');
    exit;
}

// Подключение к БД
$database = new Database();
$db = $database->getConnection();
$service = new Service($db);

// Получаем фильтры
$filters = [
    'search' => $_GET['search'] ?? null,
    'category' => $_GET['category'] ?? null,
    'is_active' => isset($_GET['status']) ? ($_GET['status'] === 'active' ? 1 : 0) : null
];

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Получаем список услуг
try {
    $services = $service->getServices($filters, $limit, $offset);
    $categories = $service->getCategories();
    $stats = $service->getServiceStats();
} catch (Exception $e) {
    $_SESSION['error'] = 'Ошибка при загрузке услуг: ' . $e->getMessage();
    $services = [];
    $categories = [];
    $stats = [];
}

// Заголовок страницы
$page_title = 'Управление услугами';
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

<div class="container-fluid">
    <!-- Заголовок страницы -->
    <div class="services-header">
        <h1 class="page-title">Управление услугами</h1>
        <button class="btn btn-primary" id="addServiceBtn">
            <i class="fas fa-plus"></i>
            Добавить услугу
        </button>
    </div>

    <!-- Статистика -->
    <?php if ($stats): ?>
    <div class="services-stats">
        <div class="stat-box">
            <h3>Всего услуг</h3>
            <div class="value"><?php echo $stats['total_services'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
            <h3>Активных услуг</h3>
            <div class="value"><?php echo $stats['active_services'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
            <h3>Категорий</h3>
            <div class="value"><?php echo $stats['categories_count'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
            <h3>Общий доход</h3>
            <div class="value">₽<?php echo number_format($stats['total_revenue'] ?? 0, 0, '.', ' '); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Фильтры -->
    <div class="filters-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" 
                   id="searchInput" 
                   placeholder="Поиск услуг..." 
                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
        </div>
        
        <select id="categoryFilter" class="form-control" style="width: 200px;">
            <option value="">Все категории</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                        <?php echo ($filters['category'] === $cat['category']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['category']); ?> (<?php echo $cat['count']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
        
        <select id="statusFilter" class="form-control" style="width: 150px;">
            <option value="">Все статусы</option>
            <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Активные</option>
            <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : ''; ?>>Неактивные</option>
        </select>
        
        <button class="btn btn-secondary" id="resetFiltersBtn">
            <i class="fas fa-times"></i>
            Сбросить
        </button>
    </div>

    <!-- Таблица услуг -->
    <div class="services-table">
        <?php if (empty($services)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Услуги не найдены</p>
            </div>
        <?php else: ?>
            <table id="servicesTable">
                <thead>
                    <tr>
                        <th width="30"><i class="fas fa-sort"></i></th>
                        <th>Название / Описание</th>
                        <th>Категория</th>
                        <th>Базовая цена</th>
                        <th>Мин. кол-во</th>
                        <th>Параметры</th>
                        <th>Использований</th>
                        <th>Статус</th>
                        <th width="120">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $srv): ?>
                    <tr data-id="<?php echo $srv['id']; ?>">
                        <td class="sortable-handle">
                            <i class="fas fa-grip-vertical"></i>
                        </td>
                        <td>
                            <div class="service-name"><?php echo htmlspecialchars($srv['name']); ?></div>
                            <?php if ($srv['description']): ?>
                                <div class="service-description"><?php echo htmlspecialchars($srv['description']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($srv['category']): ?>
                                <span class="category-badge"><?php echo htmlspecialchars($srv['category']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="price-info">
                            ₽<?php echo number_format($srv['base_price'], 0, '.', ' '); ?>
                        </td>
                        <td><?php echo $srv['min_quantity']; ?> шт.</td>
                        <td>
                            <span class="parameters-count">
                                <i class="fas fa-cog"></i>
                                <?php echo $srv['parameters_count'] ?? 0; ?>
                            </span>
                            /
                            <span class="rules-count">
                                <i class="fas fa-percentage"></i>
                                <?php echo $srv['rules_count'] ?? 0; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $srv['usage_count'] ?? 0; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $srv['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $srv['is_active'] ? 'Активна' : 'Неактивна'; ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <button class="btn-icon" onclick="editService(<?php echo $srv['id']; ?>)" title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="manageParameters(<?php echo $srv['id']; ?>)" title="Параметры">
                                <i class="fas fa-cog"></i>
                            </button>
                            <button class="btn-icon" onclick="managePricing(<?php echo $srv['id']; ?>)" title="Ценообразование">
                                <i class="fas fa-percentage"></i>
                            </button>
                            <button class="btn-icon" onclick="toggleServiceStatus(<?php echo $srv['id']; ?>, <?php echo $srv['is_active']; ?>)" 
                                    title="<?php echo $srv['is_active'] ? 'Деактивировать' : 'Активировать'; ?>">
                                <i class="fas fa-<?php echo $srv['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                            </button>
                            <button class="btn-icon" onclick="deleteService(<?php echo $srv['id']; ?>)" title="Удалить">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно создания/редактирования услуги -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Добавить услугу</h2>
            <button class="btn-icon" type="button" id="closeServiceModalBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="serviceForm">
            <input type="hidden" name="service_id" id="service_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Название услуги <span class="required">*</span></label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Категория <span class="required">*</span></label>
                    <select class="form-control" name="category" required>
                        <option value="">Выберите категорию</option>
                        <option value="печать">Печать</option>
                        <option value="дизайн">Дизайн</option>
                        <option value="постпечать">Постпечать</option>
                        <option value="широкоформат">Широкоформат</option>
                        <option value="сувенирка">Сувенирная продукция</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Описание</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Базовая цена <span class="required">*</span></label>
                            <input type="number" class="form-control" name="base_price" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Мин. количество <span class="required">*</span></label>
                            <input type="number" class="form-control" name="min_quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Срок производства (дней)</label>
                            <input type="number" class="form-control" name="production_time_days" min="0" value="1">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" checked>
                        Услуга активна
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelServiceBtn">Отмена</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно управления параметрами -->
<div id="parametersModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h2 class="modal-title">Параметры услуги</h2>
            <button class="btn-icon" type="button" id="closeParametersModalBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <button class="btn btn-primary btn-sm" onclick="addParameter()">
                    <i class="fas fa-plus"></i>
                    Добавить параметр
                </button>
            </div>
            <div id="parametersContainer">
                <!-- Параметры будут загружены через AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Скрипты -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Глобальные переменные
let currentServiceId = null;

// Функции для работы с модальными окнами
function openModal(modalId) {
    console.log(`[MODAL] Opening modal: ${modalId}`);
    const modal = document.getElementById(modalId);
    if (modal) {
        // Сначала закрываем все открытые модальные окна
        document.querySelectorAll('.modal.active').forEach(m => {
            m.classList.remove('active');
        });
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        console.log(`[MODAL] Modal ${modalId} opened successfully`);
        
        // Добавляем обработчик для закрытия по клику вне модального окна
        modal.addEventListener('click', function modalOutsideClickHandler(e) {
            if (e.target === modal) {
                console.log(`[MODAL] Click outside modal ${modalId}`);
                closeModal(modalId);
                modal.removeEventListener('click', modalOutsideClickHandler);
            }
        });
    } else {
        console.error(`[MODAL ERROR] Modal not found: ${modalId}`);
    }
}

function closeModal(modalId) {
    console.log(`[MODAL] Closing modal: ${modalId}`);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        console.log(`[MODAL] Modal ${modalId} closed successfully`);
        
        // Если это форма - сбрасываем ее
        if (modalId === 'serviceModal') {
            document.getElementById('serviceForm').reset();
        }
    } else {
        console.error(`[MODAL ERROR] Modal not found for closing: ${modalId}`);
    }
}

function setupModalCloseHandlers() {
    // Обработчик для крестика в serviceModal
    const closeServiceModalBtn = document.getElementById('closeServiceModalBtn');
    if (closeServiceModalBtn) {
        closeServiceModalBtn.addEventListener('click', () => {
            console.log('[HANDLER] Close button clicked in service modal');
            closeModal('serviceModal');
        });
    }

    // Обработчик для кнопки "Отмена" в serviceModal
    const cancelServiceBtn = document.getElementById('cancelServiceBtn');
    if (cancelServiceBtn) {
        cancelServiceBtn.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('[HANDLER] Cancel button clicked in service modal');
            closeModal('serviceModal');
        });
    }

    // Обработчик для крестика в parametersModal
    const closeParametersModalBtn = document.getElementById('closeParametersModalBtn');
    if (closeParametersModalBtn) {
        closeParametersModalBtn.addEventListener('click', () => {
            console.log('[HANDLER] Close button clicked in parameters modal');
            closeModal('parametersModal');
        });
    }

    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            console.log('[HANDLER] Escape key pressed');
            document.querySelectorAll('.modal.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
}

// Функция открытия формы услуги
function openServiceModal() {
    console.log('Opening service modal');
    document.getElementById('serviceForm').reset();
    document.getElementById('service_id').value = '';
    document.querySelector('#serviceModal .modal-title').textContent = 'Добавить услугу';
    openModal('serviceModal');
}

// Применение фильтров
function applyFilters() {
    console.log('Applying filters');
    const params = new URLSearchParams();
    
    const search = document.getElementById('searchInput').value;
    if (search) {
        console.log(`Search filter: ${search}`);
        params.append('search', search);
    }
    
    const category = document.getElementById('categoryFilter').value;
    if (category) {
        console.log(`Category filter: ${category}`);
        params.append('category', category);
    }
    
    const status = document.getElementById('statusFilter').value;
    if (status) {
        console.log(`Status filter: ${status}`);
        params.append('status', status);
    }
    
    const url = '/admin/services.php' + (params.toString() ? '?' + params.toString() : '');
    console.log(`Redirecting to: ${url}`);
    window.location.href = url;
}

// Сброс фильтров
function resetFilters() {
    console.log('Resetting filters');
    window.location.href = '/admin/services.php';
}

// Редактирование услуги
async function editService(id) {
    console.log(`Editing service ID: ${id}`);
    try {
        const url = `/admin/api/services.php?id=${id}`;
        console.log(`Fetching service data from: ${url}`);
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            console.log('Service data loaded successfully:', data.service);
            const service = data.service;
            const form = document.getElementById('serviceForm');
            
            form.service_id.value = service.id;
            form.name.value = service.name;
            form.category.value = service.category;
            form.description.value = service.description || '';
            form.base_price.value = service.base_price;
            form.min_quantity.value = service.min_quantity;
            form.production_time_days.value = service.production_time_days || 1;
            form.is_active.checked = service.is_active == 1;
            
            document.querySelector('#serviceModal .modal-title').textContent = 'Редактирование услуги';
            openModal('serviceModal');
        } else {
            console.error('Error loading service:', data.error);
            showNotification('error', data.error || 'Ошибка загрузки услуги');
        }
    } catch (error) {
        console.error('Error in editService:', error);
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Сохранение услуги
async function handleServiceSubmit(e) {
    console.log('Handling service form submit');
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const serviceId = formData.get('service_id');
    const method = serviceId ? 'PUT' : 'POST';
    
    const data = {
        name: formData.get('name'),
        category: formData.get('category'),
        description: formData.get('description'),
        base_price: parseFloat(formData.get('base_price')),
        min_quantity: parseInt(formData.get('min_quantity')),
        production_time_days: parseInt(formData.get('production_time_days')) || 1,
        is_active: formData.get('is_active') ? 1 : 0
    };
    
    console.log('Form data:', data);
    
    try {
        const url = serviceId 
            ? `/admin/api/services.php?id=${serviceId}` 
            : '/admin/api/services.php?action=create';
            
        console.log(`Sending request to: ${url} with method: ${method}`);
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        console.log('Server response:', result);
        
        if (result.success) {
            const message = serviceId ? 'Услуга обновлена' : 'Услуга создана';
            console.log(message);
            showNotification('success', message);
            closeModal('serviceModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            const error = result.error || 'Ошибка сохранения';
            console.error('Save error:', error);
            showNotification('error', error);
        }
    } catch (error) {
        console.error('Error in handleServiceSubmit:', error);
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Управление параметрами
async function manageParameters(serviceId) {
    console.log(`Managing parameters for service ID: ${serviceId}`);
    currentServiceId = serviceId;
    
    try {
        const url = `/admin/api/services.php?id=${serviceId}`;
        console.log(`Fetching service parameters from: ${url}`);
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            console.log('Parameters loaded successfully:', data.service.parameters);
            const service = data.service;
            document.querySelector('#parametersModal .modal-title').textContent = 
                `Параметры услуги: ${service.name}`;
            
            displayParameters(service.parameters || []);
            openModal('parametersModal');
        }
    } catch (error) {
        console.error('Error loading parameters:', error);
        showNotification('error', 'Ошибка загрузки параметров');
    }
}

// Отображение параметров
function displayParameters(parameters) {
    console.log(`Displaying ${parameters.length} parameters`);
    const container = document.getElementById('parametersContainer');
    
    if (parameters.length === 0) {
        console.log('No parameters found, showing empty state');
        container.innerHTML = '<p class="text-muted">Параметры не добавлены</p>';
        return;
    }
    
    container.innerHTML = parameters.map(param => `
        <div class="parameter-item" data-id="${param.id}">
            <div class="row align-items-center mb-2">
                <div class="col-md-3">
                    <select class="form-control" name="parameter_type">
                        <option value="material" ${param.parameter_type === 'material' ? 'selected' : ''}>Материал</option>
                        <option value="size" ${param.parameter_type === 'size' ? 'selected' : ''}>Размер</option>
                        <option value="color" ${param.parameter_type === 'color' ? 'selected' : ''}>Цвет</option>
                        <option value="finish" ${param.parameter_type === 'finish' ? 'selected' : ''}>Обработка</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="parameter_name" 
                           value="${param.parameter_name}" placeholder="Название">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="parameter_value" 
                           value="${param.parameter_value || ''}" placeholder="Значение">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="price_modifier" 
                           value="${param.price_modifier}" placeholder="Доп.цена" step="0.01">
                </div>
                <div class="col-md-1">
                    <input type="number" class="form-control" name="price_multiplier" 
                           value="${param.price_multiplier}" placeholder="x" step="0.01">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-danger btn-sm" onclick="deleteParameter(${param.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Добавление параметра
function addParameter() {
    console.log('Adding new parameter field');
    const container = document.getElementById('parametersContainer');
    
    if (container.querySelector('.text-muted')) {
        console.log('Removing empty state');
        container.innerHTML = '';
    }
    
    const paramHtml = `
        <div class="parameter-item new-param">
            <div class="row align-items-center mb-2">
                <div class="col-md-3">
                    <select class="form-control" name="parameter_type">
                        <option value="material">Материал</option>
                        <option value="size">Размер</option>
                        <option value="color">Цвет</option>
                        <option value="finish">Обработка</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="parameter_name" placeholder="Название">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="parameter_value" placeholder="Значение">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="price_modifier" 
                           placeholder="Доп.цена" step="0.01" value="0">
                </div>
                <div class="col-md-1">
                    <input type="number" class="form-control" name="price_multiplier" 
                           placeholder="x" step="0.01" value="1">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-success btn-sm" onclick="saveNewParameter(this)">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('afterbegin', paramHtml);
    console.log('New parameter field added');
}

// Сохранение нового параметра
async function saveNewParameter(btn) {
    console.log('Saving new parameter');
    const paramItem = btn.closest('.parameter-item');
    const inputs = paramItem.querySelectorAll('input, select');
    
    const data = {
        parameter_type: inputs[0].value,
        parameter_name: inputs[1].value,
        parameter_value: inputs[2].value,
        price_modifier: parseFloat(inputs[3].value) || 0,
        price_multiplier: parseFloat(inputs[4].value) || 1
    };
    
    console.log('Parameter data:', data);
    
    if (!data.parameter_name) {
        console.warn('Parameter name is empty');
        showNotification('error', 'Введите название параметра');
        return;
    }
    
    try {
        const url = `/admin/api/services.php?id=${currentServiceId}&action=parameter`;
        console.log(`Saving parameter to: ${url}`);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        console.log('Save parameter response:', result);
        
        if (result.success) {
            console.log('Parameter saved successfully');
            showNotification('success', 'Параметр добавлен');
            manageParameters(currentServiceId);
        } else {
            const error = result.error || 'Ошибка сохранения';
            console.error('Save parameter error:', error);
            showNotification('error', error);
        }
    } catch (error) {
        console.error('Error saving parameter:', error);
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Удаление параметра
async function deleteParameter(paramId) {
    console.log(`Deleting parameter ID: ${paramId}`);
    if (!confirm('Удалить этот параметр?')) {
        console.log('Parameter deletion canceled by user');
        return;
    }
    
    try {
        const url = `/admin/api/services.php?action=parameter&param_id=${paramId}`;
        console.log(`Deleting parameter with URL: ${url}`);
        
        const response = await fetch(url, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        console.log('Delete parameter response:', result);
        
        if (result.success) {
            console.log('Parameter deleted successfully');
            showNotification('success', 'Параметр удален');
            manageParameters(currentServiceId);
        } else {
            const error = result.error || 'Ошибка удаления';
            console.error('Delete parameter error:', error);
            showNotification('error', error);
        }
    } catch (error) {
        console.error('Error deleting parameter:', error);
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Переключение статуса услуги
async function toggleServiceStatus(serviceId, currentStatus) {
    console.log(`Toggling status for service ID: ${serviceId}, current status: ${currentStatus}`);
    const newStatus = currentStatus ? 0 : 1;
    const action = newStatus ? 'активировать' : 'деактивировать';
    
    if (!confirm(`Вы уверены, что хотите ${action} эту услугу?`)) {
        console.log('Status change canceled by user');
        return;
    }
    
    try {
        const url = `/admin/api/services.php?id=${serviceId}&action=toggle`;
        console.log(`Toggling status with URL: ${url}`);
        
        const response = await fetch(url, {
            method: 'PUT'
        });
        
        const result = await response.json();
        console.log('Toggle status response:', result);
        
        if (result.success) {
            const message = `Услуга ${newStatus ? 'активирована' : 'деактивирована'}`;
            console.log(message);
            showNotification('success', message);
            setTimeout(() => location.reload(), 1000);
        } else {
            const error = result.error || 'Ошибка изменения статуса';
            console.error('Toggle status error:', error);
            showNotification('error', error);
        }
    } catch (error) {
        console.error('Error toggling service status:', error);
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Удаление услуги
async function deleteService(serviceId) {
    console.log(`Deleting service ID: ${serviceId}`);
    if (!confirm('Вы уверены, что хотите удалить эту услугу?')) {
        console.log('Service deletion canceled by user');
        return;
    }
    
    try {
        const url = `/admin/api/services.php?id=${serviceId}`;
        console.log(`Deleting service with URL: ${url}`);
        
        const response = await fetch(url, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        console.log('Delete service response:', result);
        
        if (result.success) {
            console.log('Service deleted successfully');
            showNotification('success', 'Услуга удалена');
            setTimeout(() => location.reload(), 1000);
        } else {
            const error = result.error || 'Ошибка удаления';
            console.error('Delete service error:', error);
            showNotification('error', error);
        }
    } catch (error) {
        console.error('Error deleting service:', error);
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Сохранение порядка услуг
async function saveServicesOrder() {
    console.log('Saving services order');
    const rows = document.querySelectorAll('#servicesTable tbody tr');
    const services = Array.from(rows).map((row, index) => ({
        id: row.dataset.id,
        order: index
    }));
    
    console.log('New order:', services);
    
    try {
        const url = '/admin/api/services.php?action=reorder';
        console.log(`Saving order to: ${url}`);
        
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ services })
        });
        
        const result = await response.json();
        console.log('Save order response:', result);
        
        if (!result.success) {
            const error = result.error || 'Ошибка сохранения порядка';
            console.error('Save order error:', error);
            showNotification('error', error);
        }
    } catch (error) {
        console.error('Error saving order:', error);
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Вспомогательные функции
function showNotification(type, message) {
    console.log(`Notification: [${type}] ${message}`);
    if (window.showAdminNotification) {
        window.showAdminNotification(type, message);
    } else {
        alert(message);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Инициализация после загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded and parsed');
    setupModalCloseHandlers();
    // Инициализация сортировки
    if (document.getElementById('servicesTable')) {
        console.log('Initializing sortable table');
        new Sortable(document.querySelector('#servicesTable tbody'), {
            handle: '.sortable-handle',
            animation: 150,
            onEnd: function(evt) {
                console.log('Table order changed');
                saveServicesOrder();
            }
        });
    }
    
    // Обработчики фильтров
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        console.log('Adding search input listener');
        searchInput.addEventListener('input', debounce(applyFilters, 300));
    }
    if (categoryFilter) {
        console.log('Adding category filter listener');
        categoryFilter.addEventListener('change', applyFilters);
    }
    if (statusFilter) {
        console.log('Adding status filter listener');
        statusFilter.addEventListener('change', applyFilters);
    }
    
    // Обработчик кнопки добавления услуги
    const addServiceBtn = document.getElementById('addServiceBtn');
    if (addServiceBtn) {
        console.log('Adding add service button listener');
        addServiceBtn.addEventListener('click', openServiceModal);
    }
    
    // Обработчик кнопки сброса фильтров
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    if (resetFiltersBtn) {
        console.log('Adding reset filters button listener');
        resetFiltersBtn.addEventListener('click', resetFilters);
    }
    
    // Обработчик формы услуги
    const serviceForm = document.getElementById('serviceForm');
    if (serviceForm) {
        console.log('Adding service form submit listener');
        serviceForm.addEventListener('submit', handleServiceSubmit);
    }
    
    // Обработчики закрытия модальных окон
    const closeServiceModalBtn = document.getElementById('closeServiceModalBtn');
    if (closeServiceModalBtn) {
        console.log('Adding close service modal button listener');
        closeServiceModalBtn.addEventListener('click', function(e) {
            console.log('Close service modal button clicked');
            closeModal('serviceModal');
        });
    }
    
    const cancelServiceBtn = document.getElementById('cancelServiceBtn');
    if (cancelServiceBtn) {
        console.log('Adding cancel service button listener');
        cancelServiceBtn.addEventListener('click', function(e) {
            console.log('Cancel service button clicked');
            closeModal('serviceModal');
        });
    }
    
    const closeParametersModalBtn = document.getElementById('closeParametersModalBtn');
    if (closeParametersModalBtn) {
        console.log('Adding close parameters modal button listener');
        closeParametersModalBtn.addEventListener('click', function(e) {
            console.log('Close parameters modal button clicked');
            closeModal('parametersModal');
        });
    }
    
    // Закрытие модальных окон по клику на фон
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                console.log(`Modal background clicked: ${modal.id}`);
                closeModal(modal.id);
            }
        });
    });
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            console.log('Escape key pressed');
            document.querySelectorAll('.modal.active').forEach(modal => {
                console.log(`Closing modal: ${modal.id}`);
                closeModal(modal.id);
            });
        }
    });
    
    console.log('Initialization complete');
    
});


</script>

<?php require_once 'includes/footer.php'; ?>