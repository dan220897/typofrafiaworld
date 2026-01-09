<?php
// admin/service-create.php - Создание новой услуги
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Service.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('edit_services');

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$service = new Service($db);
$adminLog = new AdminLog($db);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Валидация
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $base_price = floatval($_POST['base_price'] ?? 0);
        $min_quantity = intval($_POST['min_quantity'] ?? 1);
        $production_time_days = intval($_POST['production_time_days'] ?? 1);
        
        // Проверка обязательных полей
        if (empty($name)) {
            throw new Exception('Название услуги обязательно для заполнения');
        }
        
        if (empty($category)) {
            throw new Exception('Выберите категорию услуги');
        }
        
        if ($base_price < 0) {
            throw new Exception('Базовая цена не может быть отрицательной');
        }
        
        if ($min_quantity < 1) {
            throw new Exception('Минимальное количество должно быть больше 0');
        }
        
        if ($production_time_days < 0) {
            throw new Exception('Срок выполнения не может быть отрицательным');
        }
        
        // Создаем услугу
        $serviceData = [
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'base_price' => $base_price,
            'min_quantity' => $min_quantity,
            'production_time_days' => $production_time_days,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $service_id = $service->createService($serviceData);
        
        if ($service_id) {
            // Добавляем параметры, если они указаны
            if (!empty($_POST['parameters'])) {
                foreach ($_POST['parameters'] as $param) {
                    if (!empty($param['name']) && !empty($param['value'])) {
                        $paramData = [
                            'service_id' => $service_id,
                            'parameter_type' => $param['type'] ?? 'custom',
                            'parameter_name' => $param['name'],
                            'parameter_value' => $param['value'],
                            'price_modifier' => floatval($param['price_modifier'] ?? 0),
                            'is_active' => 1
                        ];
                        $service->addServiceParameter($paramData);
                    }
                }
            }
            
            // Логируем действие
            $adminLog->log($_SESSION['admin_id'], 'create_service', 
                "Создана услуга: {$name}", 'service', $service_id);
            
            $_SESSION['success'] = 'Услуга успешно создана';
            header('Location: services.php?id=' . $service_id);
            exit;
        } else {
            throw new Exception('Ошибка создания услуги');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Заголовок страницы
$page_title = 'Создание услуги';
$current_page = 'services';
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

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

/* Карточки */
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
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

.required {
    color: #ef4444;
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

.form-help {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-row-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
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

/* Параметры */
.parameters-section {
    margin-top: 1.5rem;
}

.parameter-row {
    display: grid;
    grid-template-columns: 150px 1fr 1fr 150px 60px;
    gap: 0.75rem;
    align-items: center;
    margin-bottom: 0.75rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 6px;
}

.parameter-row .form-control {
    margin: 0;
}

.btn-remove {
    background: #fee2e2;
    color: #991b1b;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove:hover {
    background: #fecaca;
}

/* Примеры */
.examples-section {
    background: #f0f9ff;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1rem;
}

.examples-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #0369a1;
    margin-bottom: 0.5rem;
}

.example-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #0c4a6e;
    margin-bottom: 0.25rem;
}

/* Алерты */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Адаптив */
@media (max-width: 768px) {
    .form-row,
    .form-row-3 {
        grid-template-columns: 1fr;
    }
    
    .parameter-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
}
</style>

<div class="container-fluid">
    <!-- Хлебные крошки -->
    <div class="breadcrumb">
        <a href="/admin/">Главная</a>
        <span class="separator">/</span>
        <a href="services.php">Услуги</a>
        <span class="separator">/</span>
        <span>Создание услуги</span>
    </div>
    
    <!-- Заголовок -->
    <div class="page-header">
        <h1 class="page-title">Новая услуга</h1>
        <div>
            <a href="services.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>
    
    <!-- Алерты -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Основная информация</h2>
            </div>
            
            <div class="form-group">
                <label class="form-label">Название услуги <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" required
                       placeholder="Например: Печать визиток"
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                <div class="form-help">Название должно быть понятным и информативным</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Категория <span class="required">*</span></label>
                    <select name="category" class="form-control" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach (SERVICE_CATEGORIES as $key => $label): ?>
                        <option value="<?php echo $key; ?>" 
                                <?php echo ($_POST['category'] ?? '') === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Базовая цена <span class="required">*</span></label>
                    <input type="number" name="base_price" class="form-control" required
                           min="0" step="0.01" placeholder="0.00"
                           value="<?php echo $_POST['base_price'] ?? ''; ?>">
                    <div class="form-help">Цена за единицу товара в рублях</div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-control" rows="4"
                          placeholder="Подробное описание услуги..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <div class="form-help">Опишите особенности услуги, материалы, технологию</div>
            </div>
            
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Минимальное количество</label>
                    <input type="number" name="min_quantity" class="form-control" 
                           min="1" value="<?php echo $_POST['min_quantity'] ?? 1; ?>">
                    <div class="form-help">Минимальный заказ</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Срок выполнения (дней)</label>
                    <input type="number" name="production_time_days" class="form-control" 
                           min="0" value="<?php echo $_POST['production_time_days'] ?? 1; ?>">
                    <div class="form-help">Стандартный срок</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Статус</label>
                    <div class="form-check" style="margin-top: 0.75rem;">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               <?php echo ($_POST['is_active'] ?? '1') ? 'checked' : ''; ?>>
                        <label for="is_active">Услуга активна</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Параметры и модификаторы цены</h2>
                <button type="button" class="btn btn-success" onclick="addParameter()">
                    <i class="fas fa-plus"></i> Добавить параметр
                </button>
            </div>
            
            <div class="examples-section">
                <div class="examples-title">Примеры параметров:</div>
                <div class="example-item">
                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                    <span><strong>Материал:</strong> Мелованная бумага 300г (+50₽), Дизайнерская бумага (+100₽)</span>
                </div>
                <div class="example-item">
                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                    <span><strong>Размер:</strong> A4 (+0₽), A3 (+20₽), A2 (+50₽)</span>
                </div>
                <div class="example-item">
                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                    <span><strong>Обработка:</strong> Ламинация (+30₽), УФ-лак (+40₽)</span>
                </div>
            </div>
            
            <div class="parameters-section" id="parametersContainer">
                <!-- Параметры будут добавляться динамически -->
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Создать услугу
            </button>
            <a href="services.php" class="btn btn-secondary">
                Отмена
            </a>
        </div>
    </form>
</div>

<script>
let parameterIndex = 0;

// Добавление параметра
function addParameter() {
    const container = document.getElementById('parametersContainer');
    const row = document.createElement('div');
    row.className = 'parameter-row';
    row.innerHTML = `
        <select name="parameters[${parameterIndex}][type]" class="form-control">
            <option value="material">Материал</option>
            <option value="size">Размер</option>
            <option value="color">Цвет</option>
            <option value="finish">Обработка</option>
            <option value="custom">Другое</option>
        </select>
        <input type="text" name="parameters[${parameterIndex}][name]" class="form-control" 
               placeholder="Название параметра">
        <input type="text" name="parameters[${parameterIndex}][value]" class="form-control" 
               placeholder="Значение">
        <input type="number" name="parameters[${parameterIndex}][price_modifier]" class="form-control" 
               placeholder="Наценка" step="0.01">
        <button type="button" class="btn-remove" onclick="removeParameter(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(row);
    parameterIndex++;
}

// Удаление параметра
function removeParameter(button) {
    button.closest('.parameter-row').remove();
}

// Валидация формы
document.querySelector('form').addEventListener('submit', function(e) {
    const basePrice = parseFloat(document.querySelector('input[name="base_price"]').value);
    if (isNaN(basePrice) || basePrice < 0) {
        e.preventDefault();
        alert('Укажите корректную базовую цену');
        return false;
    }
    
    const minQuantity = parseInt(document.querySelector('input[name="min_quantity"]').value);
    if (isNaN(minQuantity) || minQuantity < 1) {
        e.preventDefault();
        alert('Минимальное количество должно быть больше 0');
        return false;
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>