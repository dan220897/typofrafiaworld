<?php
// admin/categories.php - Страница управления категориями
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Category.php';

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
$category = new Category($db);

// Получаем фильтры
$filters = [
    'search' => $_GET['search'] ?? null,
    'is_active' => isset($_GET['status']) ? ($_GET['status'] === 'active' ? 1 : ($_GET['status'] === 'inactive' ? 0 : null)) : null
];

// Получаем список категорий
try {
    $categories = $category->getAll($filters);
    $stats = $category->getStats();
} catch (Exception $e) {
    $_SESSION['error'] = 'Ошибка при загрузке категорий: ' . $e->getMessage();
    $categories = [];
    $stats = [];
}

// Заголовок страницы
$page_title = 'Управление категориями';
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
.categories-header {
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

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-link {
    background: none;
    color: #6b7280;
    padding: 0.5rem;
}

.btn-link:hover {
    color: #374151;
}

/* Статистика */
.categories-stats {
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

/* Таблица категорий */
.categories-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.categories-table table {
    width: 100%;
    border-collapse: collapse;
}

.categories-table th {
    background: #f9fafb;
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    border-bottom: 1px solid #e5e7eb;
}

.categories-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.category-name {
    font-weight: 500;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.category-icon {
    width: 32px;
    height: 32px;
    background: #e0e7ff;
    color: #4338ca;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
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

/* Icon picker */
.icon-picker {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
    gap: 0.5rem;
    max-height: 200px;
    overflow-y: auto;
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
}

.icon-option {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.icon-option:hover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.icon-option.selected {
    border-color: #3b82f6;
    background-color: #3b82f6;
    color: white;
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

/* Адаптивность */
@media (max-width: 768px) {
    .categories-header {
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

    .categories-table {
        overflow-x: auto;
    }

    .categories-table table {
        min-width: 700px;
    }

    .modal-content {
        max-width: 95%;
        margin: 1rem;
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
    <div class="categories-header">
        <h1 class="page-title">Управление категориями</h1>
        <div>
            <button class="btn btn-secondary" id="migrateBtn" title="Импортировать категории из услуг">
                <i class="fas fa-file-import"></i>
                Импорт из услуг
            </button>
            <button class="btn btn-primary" id="addCategoryBtn">
                <i class="fas fa-plus"></i>
                Добавить категорию
            </button>
        </div>
    </div>

    <!-- Статистика -->
    <?php if ($stats): ?>
    <div class="categories-stats">
        <div class="stat-box">
            <h3>Всего категорий</h3>
            <div class="value"><?php echo $stats['total'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
            <h3>Активных категорий</h3>
            <div class="value"><?php echo $stats['active'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
            <h3>Неактивных категорий</h3>
            <div class="value"><?php echo $stats['inactive'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
            <h3>Всего услуг</h3>
            <div class="value"><?php echo $stats['total_services'] ?? 0; ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Фильтры -->
    <div class="filters-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text"
                   id="searchInput"
                   placeholder="Поиск категорий..."
                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
        </div>

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

    <!-- Таблица категорий -->
    <div class="categories-table">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>Категории не найдены</p>
                <button class="btn btn-primary" id="addCategoryBtn2">
                    <i class="fas fa-plus"></i>
                    Добавить первую категорию
                </button>
            </div>
        <?php else: ?>
            <table id="categoriesTable">
                <thead>
                    <tr>
                        <th width="30"><i class="fas fa-sort"></i></th>
                        <th>Категория</th>
                        <th>Slug</th>
                        <th>Услуг</th>
                        <th>Статус</th>
                        <th width="120">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr data-id="<?php echo $cat['id']; ?>">
                        <td class="sortable-handle">
                            <i class="fas fa-grip-vertical"></i>
                        </td>
                        <td>
                            <div class="category-name">
                                <div class="category-icon">
                                    <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    <?php if ($cat['description']): ?>
                                        <div class="category-description"><?php echo htmlspecialchars($cat['description']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($cat['slug']); ?></code>
                        </td>
                        <td>
                            <?php echo $cat['services_count'] ?? 0; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $cat['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $cat['is_active'] ? 'Активна' : 'Неактивна'; ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <button class="btn-icon" onclick="editCategory(<?php echo $cat['id']; ?>)" title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="toggleCategoryStatus(<?php echo $cat['id']; ?>, <?php echo $cat['is_active']; ?>)"
                                    title="<?php echo $cat['is_active'] ? 'Деактивировать' : 'Активировать'; ?>">
                                <i class="fas fa-<?php echo $cat['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                            </button>
                            <button class="btn-icon" onclick="deleteCategory(<?php echo $cat['id']; ?>)" title="Удалить">
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

<!-- Модальное окно создания/редактирования категории -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Добавить категорию</h2>
            <button class="btn-icon" type="button" id="closeCategoryModalBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="categoryForm">
            <input type="hidden" name="category_id" id="category_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Название категории <span class="required">*</span></label>
                    <input type="text" class="form-control" name="name" id="category_name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Slug (URL)</label>
                    <input type="text" class="form-control" name="slug" id="category_slug" placeholder="Оставьте пустым для автогенерации">
                    <small style="color: #6b7280; font-size: 0.75rem;">Используется в URL адресах</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Описание</label>
                    <textarea class="form-control" name="description" id="category_description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Иконка</label>
                    <input type="text" class="form-control" name="icon" id="category_icon" value="fa-folder" readonly>
                    <div class="icon-picker" id="iconPicker">
                        <?php
                        $icons = ['fa-folder', 'fa-print', 'fa-palette', 'fa-cut', 'fa-image', 'fa-pen', 'fa-shapes', 'fa-gift', 'fa-star', 'fa-heart', 'fa-tag', 'fa-box', 'fa-cubes', 'fa-layer-group', 'fa-file', 'fa-bookmark'];
                        foreach ($icons as $icon):
                        ?>
                            <div class="icon-option" data-icon="<?php echo $icon; ?>">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Порядок сортировки</label>
                    <input type="number" class="form-control" name="sort_order" id="category_sort_order" value="0" min="0">
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="category_is_active" checked>
                        Категория активна
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelCategoryBtn">Отмена</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<!-- Скрипты -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Глобальные переменные
let currentCategoryId = null;

// Функции для работы с модальными окнами
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';

        if (modalId === 'categoryModal') {
            document.getElementById('categoryForm').reset();
            document.getElementById('category_id').value = '';
        }
    }
}

// Открытие формы категории
function openCategoryModal() {
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.querySelector('#categoryModal .modal-title').textContent = 'Добавить категорию';
    openModal('categoryModal');
}

// Применение фильтров
function applyFilters() {
    const params = new URLSearchParams();

    const search = document.getElementById('searchInput').value;
    if (search) {
        params.append('search', search);
    }

    const status = document.getElementById('statusFilter').value;
    if (status) {
        params.append('status', status);
    }

    const url = '/admin/categories.php' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// Сброс фильтров
function resetFilters() {
    window.location.href = '/admin/categories.php';
}

// Редактирование категории
async function editCategory(id) {
    try {
        const response = await fetch(`/admin/api/categories.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            const cat = data.category;
            const form = document.getElementById('categoryForm');

            form.category_id.value = cat.id;
            form.name.value = cat.name;
            form.slug.value = cat.slug;
            form.description.value = cat.description || '';
            form.icon.value = cat.icon;
            form.sort_order.value = cat.sort_order;
            form.is_active.checked = cat.is_active == 1;

            // Выделяем выбранную иконку
            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.dataset.icon === cat.icon) {
                    opt.classList.add('selected');
                }
            });

            document.querySelector('#categoryModal .modal-title').textContent = 'Редактирование категории';
            openModal('categoryModal');
        } else {
            showNotification('error', data.error || 'Ошибка загрузки категории');
        }
    } catch (error) {
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Сохранение категории
async function handleCategorySubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const categoryId = formData.get('category_id');
    const method = categoryId ? 'PUT' : 'POST';

    const data = {
        name: formData.get('name'),
        slug: formData.get('slug'),
        description: formData.get('description'),
        icon: formData.get('icon'),
        sort_order: parseInt(formData.get('sort_order')) || 0,
        is_active: formData.get('is_active') ? 1 : 0
    };

    try {
        const url = categoryId
            ? `/admin/api/categories.php?id=${categoryId}`
            : '/admin/api/categories.php';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            const message = categoryId ? 'Категория обновлена' : 'Категория создана';
            showNotification('success', message);
            closeModal('categoryModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', result.error || 'Ошибка сохранения');
        }
    } catch (error) {
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Переключение статуса категории
async function toggleCategoryStatus(categoryId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    const action = newStatus ? 'активировать' : 'деактивировать';

    if (!confirm(`Вы уверены, что хотите ${action} эту категорию?`)) {
        return;
    }

    try {
        const response = await fetch(`/admin/api/categories.php?id=${categoryId}&action=toggle`, {
            method: 'PUT'
        });

        const result = await response.json();

        if (result.success) {
            showNotification('success', `Категория ${newStatus ? 'активирована' : 'деактивирована'}`);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', result.error || 'Ошибка изменения статуса');
        }
    } catch (error) {
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Удаление категории
async function deleteCategory(categoryId) {
    if (!confirm('Вы уверены, что хотите удалить эту категорию?')) {
        return;
    }

    try {
        const response = await fetch(`/admin/api/categories.php?id=${categoryId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showNotification('success', 'Категория удалена');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', result.error || 'Ошибка удаления');
        }
    } catch (error) {
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Сохранение порядка категорий
async function saveCategoriesOrder() {
    const rows = document.querySelectorAll('#categoriesTable tbody tr');
    const categories = Array.from(rows).map((row, index) => row.dataset.id);

    try {
        const response = await fetch('/admin/api/categories.php?action=reorder', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ categories })
        });

        const result = await response.json();

        if (!result.success) {
            showNotification('error', result.error || 'Ошибка сохранения порядка');
        }
    } catch (error) {
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Миграция категорий из услуг
async function migrateCategories() {
    if (!confirm('Импортировать категории из услуг? Существующие категории не будут изменены.')) {
        return;
    }

    try {
        const response = await fetch('/admin/api/categories.php?action=migrate');
        const result = await response.json();

        if (result.success) {
            showNotification('success', `Импортировано категорий: ${result.migrated}`);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', result.error || 'Ошибка миграции');
        }
    } catch (error) {
        showNotification('error', 'Ошибка: ' + error.message);
    }
}

// Вспомогательные функции
function showNotification(type, message) {
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
    // Инициализация сортировки
    if (document.getElementById('categoriesTable')) {
        new Sortable(document.querySelector('#categoriesTable tbody'), {
            handle: '.sortable-handle',
            animation: 150,
            onEnd: function(evt) {
                saveCategoriesOrder();
            }
        });
    }

    // Обработчики фильтров
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    if (searchInput) {
        searchInput.addEventListener('input', debounce(applyFilters, 300));
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }

    // Обработчик кнопок добавления категории
    document.getElementById('addCategoryBtn')?.addEventListener('click', openCategoryModal);
    document.getElementById('addCategoryBtn2')?.addEventListener('click', openCategoryModal);

    // Обработчик кнопки миграции
    document.getElementById('migrateBtn')?.addEventListener('click', migrateCategories);

    // Обработчик кнопки сброса фильтров
    document.getElementById('resetFiltersBtn')?.addEventListener('click', resetFilters);

    // Обработчик формы категории
    document.getElementById('categoryForm')?.addEventListener('submit', handleCategorySubmit);

    // Обработчики закрытия модальных окон
    document.getElementById('closeCategoryModalBtn')?.addEventListener('click', () => closeModal('categoryModal'));
    document.getElementById('cancelCategoryBtn')?.addEventListener('click', () => closeModal('categoryModal'));

    // Закрытие модальных окон по клику на фон
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });

    // Icon picker
    document.querySelectorAll('.icon-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('category_icon').value = this.dataset.icon;
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
