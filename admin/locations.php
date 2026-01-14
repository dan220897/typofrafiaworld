<?php
// admin/locations.php - Управление точками
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Location.php';

// Проверяем авторизацию и права (только супер-админ)
checkAdminAuth();

if (!isSuperAdmin()) {
    header('Location: /admin/403.php');
    exit;
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$location = new Location($db);

// Получаем список локаций
try {
    $locations = $location->getAll();
} catch (Exception $e) {
    $_SESSION['error'] = 'Ошибка при загрузке локаций: ' . $e->getMessage();
    $locations = [];
}

// Заголовок страницы
$page_title = 'Управление точками';
require_once 'includes/header.php';
?>

<style>
.locations-container {
    max-width: 1200px;
}

.locations-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.locations-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.location-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.location-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.location-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 16px;
}

.location-name {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.location-code {
    display: inline-block;
    padding: 4px 12px;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.location-status {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.location-info {
    margin: 16px 0;
}

.info-item {
    display: flex;
    align-items: start;
    gap: 12px;
    margin-bottom: 12px;
    color: #6b7280;
    font-size: 14px;
}

.info-item i {
    width: 20px;
    color: #9ca3af;
    margin-top: 2px;
}

.location-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
}

.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>

<div class="locations-container">
    <div class="locations-header">
        <h1>Управление точками</h1>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus"></i>
            Добавить точку
        </button>
    </div>

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

    <div class="locations-grid">
        <?php foreach ($locations as $loc): ?>
            <div class="location-card">
                <div class="location-header">
                    <div>
                        <div class="location-name"><?= htmlspecialchars($loc['name']) ?></div>
                        <span class="location-code"><?= htmlspecialchars($loc['code']) ?></span>
                    </div>
                    <span class="location-status <?= $loc['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $loc['is_active'] ? 'Активна' : 'Неактивна' ?>
                    </span>
                </div>

                <div class="location-info">
                    <?php if ($loc['address']): ?>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($loc['address']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($loc['phone']): ?>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <span><?= htmlspecialchars($loc['phone']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($loc['email']): ?>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($loc['email']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="location-actions">
                    <button class="btn btn-sm btn-primary" onclick="editLocation(<?= $loc['id'] ?>)">
                        <i class="fas fa-edit"></i>
                        Редактировать
                    </button>
                    <button class="btn btn-sm btn-<?= $loc['is_active'] ? 'secondary' : 'success' ?>"
                            onclick="toggleLocationStatus(<?= $loc['id'] ?>)">
                        <i class="fas fa-<?= $loc['is_active'] ? 'pause' : 'play' ?>"></i>
                        <?= $loc['is_active'] ? 'Деактивировать' : 'Активировать' ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal для создания/редактирования -->
<div class="modal" id="locationModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Добавить точку</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>

        <form id="locationForm">
            <input type="hidden" id="locationId" name="id">

            <div class="form-group">
                <label class="form-label">Название точки*</label>
                <input type="text" class="form-control" id="locationName" name="name" required>
            </div>

            <div class="form-group">
                <label class="form-label">Код точки*</label>
                <input type="text" class="form-control" id="locationCode" name="code" required
                       pattern="[a-z0-9_-]+" title="Только маленькие буквы, цифры, дефис и подчеркивание">
                <small>Используется для входа (только латиница, цифры, дефис и подчеркивание)</small>
            </div>

            <div class="form-group">
                <label class="form-label">Адрес</label>
                <input type="text" class="form-control" id="locationAddress" name="address">
            </div>

            <div class="form-group">
                <label class="form-label">Телефон</label>
                <input type="tel" class="form-control" id="locationPhone" name="phone">
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="locationEmail" name="email">
            </div>

            <div class="form-group">
                <label class="form-label">Пароль для входа <span id="passwordRequired">*</span></label>
                <input type="password" class="form-control" id="locationPassword" name="password">
                <small id="passwordHint">Минимум 6 символов. Оставьте пустым для сохранения текущего пароля.</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingLocationId = null;

function showCreateModal() {
    editingLocationId = null;
    document.getElementById('modalTitle').textContent = 'Добавить точку';
    document.getElementById('locationForm').reset();
    document.getElementById('locationId').value = '';
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('locationPassword').required = true;
    document.getElementById('passwordHint').textContent = 'Минимум 6 символов.';
    document.getElementById('locationModal').classList.add('show');
}

async function editLocation(id) {
    editingLocationId = id;
    document.getElementById('modalTitle').textContent = 'Редактировать точку';
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('locationPassword').required = false;
    document.getElementById('passwordHint').textContent = 'Оставьте пустым для сохранения текущего пароля.';

    try {
        const response = await fetch(`/admin/api/locations.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('locationId').value = data.location.id;
            document.getElementById('locationName').value = data.location.name;
            document.getElementById('locationCode').value = data.location.code;
            document.getElementById('locationAddress').value = data.location.address || '';
            document.getElementById('locationPhone').value = data.location.phone || '';
            document.getElementById('locationEmail').value = data.location.email || '';
            document.getElementById('locationPassword').value = '';

            document.getElementById('locationModal').classList.add('show');
        } else {
            alert('Ошибка при загрузке данных точки');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

function closeModal() {
    document.getElementById('locationModal').classList.remove('show');
}

document.getElementById('locationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const url = editingLocationId ? '/admin/api/locations.php?action=update' : '/admin/api/locations.php?action=create';

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при сохранении');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
});

async function toggleLocationStatus(id) {
    if (!confirm('Вы уверены что хотите изменить статус этой точки?')) {
        return;
    }

    try {
        const response = await fetch('/admin/api/locations.php?action=toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при изменении статуса');
        }
    } catch (error) {
        alert('Ошибка соединения');
    }
}

// Close modal on outside click
document.getElementById('locationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
