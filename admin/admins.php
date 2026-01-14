<?php
// admin/admins.php - Управление администраторами
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Admin.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права (только супер-админ)
checkAdminAuth();

if (!isSuperAdmin()) {
    header('Location: /admin/403.php');
    exit;
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db);
$adminLog = new AdminLog($db);

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAjaxRequest()) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $admin->username = trim($_POST['username'] ?? '');
                $admin->full_name = trim($_POST['full_name'] ?? '');
                $admin->email = trim($_POST['email'] ?? '');
                $admin->role = $_POST['role'] ?? 'operator';
                $admin->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Валидация
                if (empty($admin->username) || empty($admin->full_name) || empty($admin->email)) {
                    throw new Exception('Заполните все обязательные поля');
                }
                
                if (!filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Неверный формат email');
                }
                
                // Генерируем временный пароль
                $temp_password = bin2hex(random_bytes(4));
                $admin->password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
                
                if ($admin->create()) {
                    $adminLog->log($_SESSION['admin_id'], 'create_admin', 
                        "Создан администратор: {$admin->username}", 'admin', $admin->id);
                    
                    echo json_encode([
                        'success' => true, 
                        'id' => $admin->id,
                        'temp_password' => $temp_password
                    ]);
                } else {
                    throw new Exception('Ошибка создания администратора');
                }
                exit;
                
            case 'update':
                $id = intval($_POST['id'] ?? 0);
                $admin->id = $id;
                $admin->username = trim($_POST['username'] ?? '');
                $admin->full_name = trim($_POST['full_name'] ?? '');
                $admin->email = trim($_POST['email'] ?? '');
                $admin->role = $_POST['role'] ?? 'operator';
                $admin->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Если указан новый пароль
                if (!empty($_POST['new_password'])) {
                    $admin->password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                }
                
                if ($admin->update()) {
                    $adminLog->log($_SESSION['admin_id'], 'update_admin', 
                        "Обновлен администратор ID: {$id}", 'admin', $id);
                    
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка обновления администратора');
                }
                exit;
                
            case 'delete':
                $id = intval($_POST['id'] ?? 0);
                
                // Нельзя удалить самого себя
                if ($id == $_SESSION['admin_id']) {
                    throw new Exception('Нельзя удалить свой аккаунт');
                }
                
                // Проверяем, что это не последний супер-админ
                $query = "SELECT COUNT(*) as count FROM admins 
                         WHERE role = 'super_admin' AND is_active = 1 AND id != :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetch();
                
                if ($result['count'] == 0) {
                    throw new Exception('Нельзя удалить последнего супер-администратора');
                }
                
                if ($admin->delete($id)) {
                    $adminLog->log($_SESSION['admin_id'], 'delete_admin', 
                        "Удален администратор ID: {$id}", 'admin', $id);
                    
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Ошибка удаления администратора');
                }
                exit;
                
            case 'reset_password':
                $id = intval($_POST['id'] ?? 0);
                
                // Генерируем новый пароль
                $new_password = bin2hex(random_bytes(4));
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $query = "UPDATE admins SET password_hash = :password_hash WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password_hash', $password_hash);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $adminLog->log($_SESSION['admin_id'], 'reset_admin_password', 
                        "Сброшен пароль администратора ID: {$id}", 'admin', $id);
                    
                    echo json_encode([
                        'success' => true,
                        'new_password' => $new_password
                    ]);
                } else {
                    throw new Exception('Ошибка сброса пароля');
                }
                exit;
                
            case 'update_permissions':
                $id = intval($_POST['id'] ?? 0);
                $permissions = $_POST['permissions'] ?? [];
                
                // Удаляем старые права
                $delete_query = "DELETE FROM admin_permissions WHERE admin_id = :admin_id";
                $delete_stmt = $db->prepare($delete_query);
                $delete_stmt->bindParam(':admin_id', $id);
                $delete_stmt->execute();
                
                // Добавляем новые права
                if (!empty($permissions)) {
                    $insert_query = "INSERT INTO admin_permissions (admin_id, permission, granted_by) 
                                   VALUES (:admin_id, :permission, :granted_by)";
                    $insert_stmt = $db->prepare($insert_query);
                    
                    foreach ($permissions as $permission) {
                        $insert_stmt->bindParam(':admin_id', $id);
                        $insert_stmt->bindParam(':permission', $permission);
                        $insert_stmt->bindParam(':granted_by', $_SESSION['admin_id']);
                        $insert_stmt->execute();
                    }
                }
                
                $adminLog->log($_SESSION['admin_id'], 'update_admin_permissions', 
                    "Обновлены права администратора ID: {$id}", 'admin', $id);
                
                echo json_encode(['success' => true]);
                exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Получаем список администраторов
$admins = $admin->getAll();

// Получаем доступные роли и права
$roles = Admin::getRoles();
$available_permissions = Admin::getPermissions();

// Заголовок страницы
$page_title = 'Управление администраторами';
$current_page = 'admins';
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

<div class="admins-page">
    <!-- Заголовок и действия -->
    <div class="page-header">
        <h1>Администраторы</h1>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-user-plus"></i> Добавить администратора
        </button>
    </div>
    
    <!-- Список администраторов -->
    <div class="admins-grid">
        <?php foreach ($admins as $admin_item): ?>
        <div class="admin-card" data-admin-id="<?php echo $admin_item['id']; ?>">
            <div class="admin-header">
                <div class="admin-avatar">
                    <?php echo mb_substr($admin_item['full_name'], 0, 1); ?>
                </div>
                <div class="admin-info">
                    <h3><?php echo htmlspecialchars($admin_item['full_name']); ?></h3>
                    <p class="admin-username">@<?php echo htmlspecialchars($admin_item['username']); ?></p>
                    <p class="admin-email"><?php echo htmlspecialchars($admin_item['email']); ?></p>
                </div>
                <?php if ($admin_item['is_active']): ?>
                    <span class="status-badge active">Активен</span>
                <?php else: ?>
                    <span class="status-badge inactive">Неактивен</span>
                <?php endif; ?>
            </div>
            
            <div class="admin-details">
                <div class="detail-row">
                    <span class="detail-label">Роль:</span>
                    <span class="detail-value"><?php echo $roles[$admin_item['role']] ?? $admin_item['role']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Последний вход:</span>
                    <span class="detail-value">
                        <?php echo $admin_item['last_login_at'] 
                            ? date('d.m.Y H:i', strtotime($admin_item['last_login_at'])) 
                            : 'Не входил'; ?>
                    </span>
                </div>
            </div>
            
            <div class="admin-actions">
                <button class="btn btn-sm btn-info" onclick="viewAdminLogs(<?php echo $admin_item['id']; ?>)">
                    <i class="fas fa-history"></i> Логи
                </button>
                
                <?php if ($admin_item['id'] != $_SESSION['admin_id']): ?>
                    <button class="btn btn-sm btn-warning" onclick="editAdmin(<?php echo $admin_item['id']; ?>)">
                        <i class="fas fa-edit"></i> Изменить
                    </button>
                    
                    <button class="btn btn-sm btn-secondary" onclick="resetPassword(<?php echo $admin_item['id']; ?>)">
                        <i class="fas fa-key"></i> Сброс пароля
                    </button>
                    
                    <?php if ($admin_item['role'] !== 'super_admin'): ?>
                    <button class="btn btn-sm btn-primary" onclick="editPermissions(<?php echo $admin_item['id']; ?>)">
                        <i class="fas fa-shield-alt"></i> Права
                    </button>
                    <?php endif; ?>
                    
                    <button class="btn btn-sm btn-danger" onclick="deleteAdmin(<?php echo $admin_item['id']; ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Модальное окно создания/редактирования -->
<div class="modal" id="adminModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Добавить администратора</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="adminForm" onsubmit="saveAdmin(event)">
            <input type="hidden" id="adminId" name="id">
            
            <div class="form-group">
                <label>Логин *</label>
                <input type="text" name="username" class="form-control" required 
                       pattern="[a-zA-Z0-9_]{3,20}" title="Только латинские буквы, цифры и _, 3-20 символов">
            </div>
            
            <div class="form-group">
                <label>Полное имя *</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Роль *</label>
                <select name="role" class="form-control" required>
                    <?php foreach ($roles as $role_key => $role_name): ?>
                    <option value="<?php echo $role_key; ?>"><?php echo $role_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" id="passwordGroup" style="display: none;">
                <label>Новый пароль</label>
                <input type="password" name="new_password" class="form-control" 
                       minlength="6" placeholder="Оставьте пустым, чтобы не менять">
                <small class="form-help">Минимум 6 символов</small>
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

<!-- Модальное окно управления правами -->
<div class="modal" id="permissionsModal" style="display: none;">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>Управление правами</h3>
            <button class="modal-close" onclick="closePermissionsModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="permissionsForm" onsubmit="savePermissions(event)">
            <input type="hidden" id="permissionsAdminId" name="admin_id">
            
            <div class="permissions-grid">
                <?php 
                $permission_groups = [
                    'Заказы' => ['view_orders', 'edit_orders', 'delete_orders'],
                    'Пользователи' => ['view_users', 'edit_users', 'delete_users'],
                    'Чаты' => ['view_chats', 'send_messages'],
                    'Услуги' => ['view_services', 'edit_services'],
                    'Контент' => ['view_reviews', 'edit_reviews', 'delete_reviews', 
                                 'view_promocodes', 'edit_promocodes', 'delete_promocodes'],
                    'Система' => ['view_reports', 'manage_settings']
                ];
                
                foreach ($permission_groups as $group => $permissions): ?>
                <div class="permission-group">
                    <h4><?php echo $group; ?></h4>
                    <?php foreach ($permissions as $perm): ?>
                    <label class="permission-label">
                        <input type="checkbox" name="permissions[]" value="<?php echo $perm; ?>">
                        <?php echo $available_permissions[$perm] ?? $perm; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить права
                </button>
                <button type="button" class="btn btn-secondary" onclick="closePermissionsModal()">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно с временным паролем -->
<div class="modal" id="passwordModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Временный пароль</h3>
            <button class="modal-close" onclick="closePasswordModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="password-info">
            <p>Временный пароль для нового администратора:</p>
            <div class="password-display">
                <code id="tempPassword"></code>
                <button class="btn btn-sm btn-secondary" onclick="copyPassword()">
                    <i class="fas fa-copy"></i> Копировать
                </button>
            </div>
            <p class="warning-text">
                <i class="fas fa-exclamation-triangle"></i>
                Сохраните этот пароль! Он больше не будет показан.
            </p>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" onclick="closePasswordModal()">
                Понятно
            </button>
        </div>
    </div>
</div>

<style>
.admins-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.admins-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.admin-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.admin-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    position: relative;
}

.admin-avatar {
    width: 50px;
    height: 50px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
}

.admin-info {
    flex: 1;
}

.admin-info h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.admin-username {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.admin-email {
    margin: 5px 0 0 0;
    color: #999;
    font-size: 13px;
}

.status-badge {
    position: absolute;
    top: 0;
    right: 0;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.admin-details {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.detail-row:last-child {
    margin-bottom: 0;
}

.detail-label {
    color: #666;
    font-size: 14px;
}

.detail-value {
    font-weight: 500;
    font-size: 14px;
}

.admin-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
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
    max-width: 800px;
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
    margin-bottom: 5px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-help {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
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

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.permission-group h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}

.permission-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    font-size: 14px;
}

.password-info {
    text-align: center;
    padding: 20px 0;
}

.password-display {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.password-display code {
    font-size: 18px;
    font-weight: bold;
    color: #007bff;
}

.warning-text {
    color: #dc3545;
    font-size: 14px;
    margin-top: 20px;
}

.warning-text i {
    margin-right: 5px;
}
</style>

<script>
// Данные администраторов для редактирования
const adminsData = <?php echo json_encode($admins); ?>;

// Показать модальное окно создания
function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Добавить администратора';
    document.getElementById('adminForm').reset();
    document.getElementById('adminId').value = '';
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('adminModal').style.display = 'flex';
}

// Закрыть модальное окно
function closeModal() {
    document.getElementById('adminModal').style.display = 'none';
}

// Редактировать администратора
function editAdmin(id) {
    const admin = adminsData.find(a => a.id == id);
    if (!admin) return;
    
    document.getElementById('modalTitle').textContent = 'Редактировать администратора';
    document.getElementById('adminId').value = id;
    document.getElementById('passwordGroup').style.display = 'block';
    
    // Заполняем форму
    document.querySelector('input[name="username"]').value = admin.username;
    document.querySelector('input[name="full_name"]').value = admin.full_name;
    document.querySelector('input[name="email"]').value = admin.email;
    document.querySelector('select[name="role"]').value = admin.role;
    document.querySelector('input[name="is_active"]').checked = admin.is_active == 1;
    
    document.getElementById('adminModal').style.display = 'flex';
}

// Сохранить администратора
async function saveAdmin(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('adminId').value;
    
    formData.append('action', id ? 'update' : 'create');
    
    if (!formData.has('is_active')) {
        formData.append('is_active', '0');
    }
    
    try {
        const response = await fetch('admins.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.temp_password) {
                // Показываем временный пароль
                document.getElementById('tempPassword').textContent = data.temp_password;
                closeModal();
                document.getElementById('passwordModal').style.display = 'flex';
            } else {
                location.reload();
            }
        } else {
            alert(data.error || 'Ошибка сохранения');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Сброс пароля
async function resetPassword(id) {
    if (!confirm('Вы уверены, что хотите сбросить пароль этому администратору?')) {
        return;
    }
    
    try {
        const response = await fetch('admins.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=reset_password&id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('tempPassword').textContent = data.new_password;
            document.getElementById('passwordModal').style.display = 'flex';
        } else {
            alert(data.error || 'Ошибка сброса пароля');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Удалить администратора
async function deleteAdmin(id) {
    if (!confirm('Вы уверены, что хотите удалить этого администратора?')) {
        return;
    }
    
    try {
        const response = await fetch('admins.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=delete&id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.querySelector(`[data-admin-id="${id}"]`).remove();
        } else {
            alert(data.error || 'Ошибка удаления');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Редактировать права
async function editPermissions(id) {
    document.getElementById('permissionsAdminId').value = id;
    
    // Загружаем текущие права администратора
    // В реальном приложении лучше загрузить через API
    
    document.getElementById('permissionsModal').style.display = 'flex';
}

// Закрыть модальное окно прав
function closePermissionsModal() {
    document.getElementById('permissionsModal').style.display = 'none';
}

// Сохранить права
async function savePermissions(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'update_permissions');
    formData.append('id', document.getElementById('permissionsAdminId').value);
    
    try {
        const response = await fetch('admins.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            closePermissionsModal();
            alert('Права успешно обновлены');
        } else {
            alert(data.error || 'Ошибка обновления прав');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Закрыть модальное окно с паролем
function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    location.reload();
}

// Копировать пароль
function copyPassword() {
    const password = document.getElementById('tempPassword').textContent;
    navigator.clipboard.writeText(password).then(() => {
        alert('Пароль скопирован в буфер обмена');
    });
}

// Просмотр логов администратора
function viewAdminLogs(id) {
    window.location.href = `logs.php?admin_id=${id}`;
}
</script>

<?php
require_once 'includes/footer.php';
?>