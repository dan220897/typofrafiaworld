<?php
// admin/pickup_points.php - Управление пунктами самовывоза
require_once 'includes/auth_check.php';
require_once 'config/database.php';

// Проверяем авторизацию и права (только супер-админ)
checkAdminAuth();

if (!isSuperAdmin()) {
    header('Location: /admin/403.php');
    exit;
}

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();

// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'create':
                $stmt = $db->prepare("INSERT INTO pickup_points (name, address, working_hours, phone, email, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['address'],
                    $_POST['working_hours'] ?? '',
                    $_POST['phone'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['sort_order'] ?? 0
                ]);
                echo json_encode(['success' => true, 'message' => 'Пункт самовывоза создан']);
                break;

            case 'update':
                $stmt = $db->prepare("UPDATE pickup_points SET name = ?, address = ?, working_hours = ?, phone = ?, email = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['address'],
                    $_POST['working_hours'] ?? '',
                    $_POST['phone'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['sort_order'] ?? 0,
                    $_POST['id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Пункт самовывоза обновлен']);
                break;

            case 'toggle':
                $stmt = $db->prepare("UPDATE pickup_points SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Статус изменен']);
                break;

            case 'delete':
                $stmt = $db->prepare("DELETE FROM pickup_points WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Пункт самовывоза удален']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Получаем список пунктов самовывоза
$stmt = $db->query("SELECT * FROM pickup_points ORDER BY sort_order, name");
$pickupPoints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Заголовок страницы
$page_title = 'Пункты самовывоза';
require_once 'includes/header.php';
?>

<style>
.container {
    max-width: 1400px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-header h1 {
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
    text-decoration: none;
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

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}

.points-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 20px;
}

.point-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.point-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.point-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 16px;
}

.point-name {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
}

.point-status {
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

.point-info {
    margin: 16px 0;
}

.info-item {
    display: flex;
    align-items: start;
    gap: 12px;
    margin-bottom: 10px;
    color: #6b7280;
    font-size: 14px;
}

.info-item i {
    width: 20px;
    color: #9ca3af;
    margin-top: 2px;
}

.point-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
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
    max-width: 600px;
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

.form-label .required {
    color: #ef4444;
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

textarea.form-control {
    resize: vertical;
    min-height: 80px;
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

.sort-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}
</style>

<div class="container">
    <div class="page-header">
        <h1>Пункты самовывоза</h1>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus"></i>
            Добавить пункт
        </button>
    </div>

    <div id="alertContainer"></div>

    <div class="points-grid">
        <?php foreach ($pickupPoints as $point): ?>
            <div class="point-card" id="point-<?= $point['id'] ?>">
                <div class="point-header">
                    <div style="flex: 1;">
                        <div class="point-name"><?= htmlspecialchars($point['name']) ?></div>
                        <span class="sort-badge">Порядок: <?= $point['sort_order'] ?></span>
                    </div>
                    <span class="point-status <?= $point['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $point['is_active'] ? 'Активен' : 'Неактивен' ?>
                    </span>
                </div>

                <div class="point-info">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($point['address']) ?></span>
                    </div>

                    <?php if (!empty($point['working_hours'])): ?>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span><?= htmlspecialchars($point['working_hours']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($point['phone'])): ?>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <span><?= htmlspecialchars($point['phone']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($point['email'])): ?>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($point['email']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="point-actions">
                    <button class="btn btn-sm btn-primary" onclick="editPoint(<?= $point['id'] ?>)">
                        <i class="fas fa-edit"></i>
                        Редактировать
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="togglePoint(<?= $point['id'] ?>)">
                        <i class="fas fa-power-off"></i>
                        <?= $point['is_active'] ? 'Деактивировать' : 'Активировать' ?>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deletePoint(<?= $point['id'] ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($pickupPoints)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #9ca3af;">
                <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-bottom: 16px;"></i>
                <p style="font-size: 18px;">Пока нет пунктов самовывоза</p>
                <p style="margin-top: 8px;">Добавьте первый пункт, нажав кнопку "Добавить пункт"</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно -->
<div id="pointModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Добавить пункт самовывоза</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>

        <form id="pointForm">
            <input type="hidden" id="pointId" name="id">

            <div class="form-group">
                <label class="form-label">Название <span class="required">*</span></label>
                <input type="text" class="form-control" id="pointName" name="name" required>
            </div>

            <div class="form-group">
                <label class="form-label">Адрес <span class="required">*</span></label>
                <textarea class="form-control" id="pointAddress" name="address" required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Часы работы</label>
                <input type="text" class="form-control" id="pointHours" name="working_hours" placeholder="Например: Пн-Пт 9:00-18:00">
            </div>

            <div class="form-group">
                <label class="form-label">Телефон</label>
                <input type="tel" class="form-control" id="pointPhone" name="phone" placeholder="+7 (999) 123-45-67">
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="pointEmail" name="email" placeholder="point@example.com">
            </div>

            <div class="form-group">
                <label class="form-label">Порядок сортировки</label>
                <input type="number" class="form-control" id="pointSort" name="sort_order" value="0" min="0">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const pickupPoints = <?= json_encode($pickupPoints) ?>;

function showAlert(message, type = 'success') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    const container = document.getElementById('alertContainer');
    container.appendChild(alert);

    setTimeout(() => alert.remove(), 3000);
}

function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Добавить пункт самовывоза';
    document.getElementById('pointForm').reset();
    document.getElementById('pointId').value = '';
    document.getElementById('pointModal').classList.add('show');
}

function editPoint(id) {
    const point = pickupPoints.find(p => p.id == id);
    if (!point) return;

    document.getElementById('modalTitle').textContent = 'Редактировать пункт самовывоза';
    document.getElementById('pointId').value = point.id;
    document.getElementById('pointName').value = point.name;
    document.getElementById('pointAddress').value = point.address;
    document.getElementById('pointHours').value = point.working_hours || '';
    document.getElementById('pointPhone').value = point.phone || '';
    document.getElementById('pointEmail').value = point.email || '';
    document.getElementById('pointSort').value = point.sort_order || 0;
    document.getElementById('pointModal').classList.add('show');
}

function closeModal() {
    document.getElementById('pointModal').classList.remove('show');
}

async function togglePoint(id) {
    if (!confirm('Изменить статус пункта самовывоза?')) return;

    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', id);

    try {
        const response = await fetch('', {method: 'POST', body: formData});
        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Ошибка сети', 'error');
    }
}

async function deletePoint(id) {
    if (!confirm('Удалить пункт самовывоза? Это действие нельзя отменить!')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const response = await fetch('', {method: 'POST', body: formData});
        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            document.getElementById(`point-${id}`).remove();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Ошибка сети', 'error');
    }
}

document.getElementById('pointForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const pointId = document.getElementById('pointId').value;
    formData.append('action', pointId ? 'update' : 'create');

    try {
        const response = await fetch('', {method: 'POST', body: formData});
        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Ошибка сети', 'error');
    }
});

// Закрытие модального окна при клике вне его
document.getElementById('pointModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
