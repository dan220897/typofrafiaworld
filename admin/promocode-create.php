<?php
// admin/promocode-create.php - Создание нового промокода
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/Promocode.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('edit_promocodes');

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$promocode = new Promocode($db);
$adminLog = new AdminLog($db);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Валидация
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $discount_type = $_POST['discount_type'] ?? 'percent';
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $min_order_amount = floatval($_POST['min_order_amount'] ?? 0);
        $max_discount_amount = !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : null;
        $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
        $user_usage_limit = intval($_POST['user_usage_limit'] ?? 1);
        $valid_from = $_POST['valid_from'] ?? date('Y-m-d');
        $valid_until = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
        
        // Проверка обязательных полей
        if (empty($code)) {
            throw new Exception('Код промокода обязателен для заполнения');
        }
        
        if (!preg_match('/^[A-Z0-9\-_]+$/', $code)) {
            throw new Exception('Код может содержать только латинские буквы, цифры, дефис и подчеркивание');
        }
        
        if ($discount_value <= 0) {
            throw new Exception('Размер скидки должен быть больше 0');
        }
        
        if ($discount_type === 'percent' && $discount_value > 100) {
            throw new Exception('Процент скидки не может быть больше 100');
        }
        
        if ($min_order_amount < 0) {
            throw new Exception('Минимальная сумма заказа не может быть отрицательной');
        }
        
        // Проверка дат
        if ($valid_until && strtotime($valid_until) < strtotime($valid_from)) {
            throw new Exception('Дата окончания не может быть раньше даты начала');
        }
        
        // Проверка существования промокода
        if ($promocode->codeExists($code)) {
            throw new Exception('Промокод с таким кодом уже существует');
        }
        
        // Создаем промокод
        $promocodeData = [
            'code' => $code,
            'description' => $description,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'min_order_amount' => $min_order_amount,
            'max_discount_amount' => $max_discount_amount,
            'usage_limit' => $usage_limit,
            'user_usage_limit' => $user_usage_limit,
            'valid_from' => $valid_from . ' 00:00:00',
            'valid_until' => $valid_until ? $valid_until . ' 23:59:59' : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_by' => $_SESSION['admin_id']
        ];
        
        $promocode_id = $promocode->createPromocode($promocodeData);
        
        if ($promocode_id) {
            // Логируем действие
            $adminLog->log($_SESSION['admin_id'], 'create_promocode', 
                "Создан промокод: {$code}", 'promocode', $promocode_id);
            
            $_SESSION['success'] = 'Промокод успешно создан';
            header('Location: promocodes.php');
            exit;
        } else {
            throw new Exception('Ошибка создания промокода');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Генерируем случайный код для примера
$generated_code = $promocode->generateCode(8);

// Заголовок страницы
$page_title = 'Создание промокода';
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

.btn-outline {
    background-color: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
}

.btn-outline:hover {
    background-color: #f9fafb;
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

/* Код промокода */
.code-input-wrapper {
    display: flex;
    gap: 0.5rem;
}

.code-input {
    flex: 1;
    text-transform: uppercase;
    font-family: monospace;
    font-size: 1rem;
    letter-spacing: 0.1em;
}

/* Выбор типа скидки */
.discount-type-selector {
    display: flex;
    gap: 1rem;
}

.discount-type-option {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
}

.discount-type-option:hover {
    border-color: #3b82f6;
    background: #f0f9ff;
}

.discount-type-option input[type="radio"] {
    display: none;
}

.discount-type-option input[type="radio"]:checked + .discount-type-content {
    color: #3b82f6;
}

.discount-type-option input[type="radio"]:checked ~ .discount-type-content {
    color: #3b82f6;
}

.discount-type-option.selected {
    border-color: #3b82f6;
    background: #f0f9ff;
}

.discount-type-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.discount-type-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.discount-type-desc {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Примеры */
.examples-box {
    background: #fef3c7;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1rem;
}

.examples-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #92400e;
    margin-bottom: 0.5rem;
}

.example-code {
    font-family: monospace;
    background: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    margin: 0 0.25rem;
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
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .discount-type-selector {
        flex-direction: column;
    }
}
</style>

<div class="container-fluid">
    <!-- Хлебные крошки -->
    <div class="breadcrumb">
        <a href="/admin/">Главная</a>
        <span class="separator">/</span>
        <a href="promocodes.php">Промокоды</a>
        <span class="separator">/</span>
        <span>Создание промокода</span>
    </div>
    
    <!-- Заголовок -->
    <div class="page-header">
        <h1 class="page-title">Новый промокод</h1>
        <div>
            <a href="promocodes.php" class="btn btn-secondary">
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
                <h2 class="card-title">Основные параметры</h2>
            </div>
            
            <div class="form-group">
                <label class="form-label">Код промокода <span class="required">*</span></label>
                <div class="code-input-wrapper">
                    <input type="text" name="code" class="form-control code-input" required
                           placeholder="SUMMER2024" maxlength="20"
                           value="<?php echo htmlspecialchars($_POST['code'] ?? ''); ?>">
                    <button type="button" class="btn btn-outline" onclick="generateCode()">
                        <i class="fas fa-dice"></i> Сгенерировать
                    </button>
                </div>
                <div class="form-help">Только латинские буквы, цифры, дефис и подчеркивание</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Описание</label>
                <input type="text" name="description" class="form-control"
                       placeholder="Летняя скидка для постоянных клиентов"
                       value="<?php echo htmlspecialchars($_POST['description'] ?? ''); ?>">
                <div class="form-help">Внутреннее описание для администраторов</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Параметры скидки</h2>
            </div>
            
            <div class="form-group">
                <label class="form-label">Тип скидки <span class="required">*</span></label>
                <div class="discount-type-selector">
                    <label class="discount-type-option <?php echo ($_POST['discount_type'] ?? 'percent') === 'percent' ? 'selected' : ''; ?>">
                        <input type="radio" name="discount_type" value="percent" 
                               <?php echo ($_POST['discount_type'] ?? 'percent') === 'percent' ? 'checked' : ''; ?>>
                        <div class="discount-type-content">
                            <div class="discount-type-icon">%</div>
                            <div class="discount-type-title">Процент</div>
                            <div class="discount-type-desc">Скидка в процентах от суммы</div>
                        </div>
                    </label>
                    
                    <label class="discount-type-option <?php echo ($_POST['discount_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>">
                        <input type="radio" name="discount_type" value="fixed"
                               <?php echo ($_POST['discount_type'] ?? '') === 'fixed' ? 'checked' : ''; ?>>
                        <div class="discount-type-content">
                            <div class="discount-type-icon">₽</div>
                            <div class="discount-type-title">Фиксированная</div>
                            <div class="discount-type-desc">Фиксированная сумма скидки</div>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Размер скидки <span class="required">*</span></label>
                    <input type="number" name="discount_value" class="form-control" required
                           min="0" step="0.01" placeholder="10"
                           value="<?php echo $_POST['discount_value'] ?? ''; ?>">
                    <div class="form-help" id="discountHelp">
                        <?php if (($_POST['discount_type'] ?? 'percent') === 'percent'): ?>
                            Процент скидки (от 0 до 100)
                        <?php else: ?>
                            Сумма скидки в рублях
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Минимальная сумма заказа</label>
                    <input type="number" name="min_order_amount" class="form-control"
                           min="0" step="0.01" placeholder="0"
                           value="<?php echo $_POST['min_order_amount'] ?? ''; ?>">
                    <div class="form-help">0 = без ограничений</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Максимальная скидка (₽)</label>
                    <input type="number" name="max_discount_amount" class="form-control"
                           min="0" step="0.01" placeholder="Без ограничений"
                           value="<?php echo $_POST['max_discount_amount'] ?? ''; ?>">
                    <div class="form-help">Только для процентных скидок</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Использований на клиента</label>
                    <input type="number" name="user_usage_limit" class="form-control"
                           min="1" value="<?php echo $_POST['user_usage_limit'] ?? 1; ?>">
                    <div class="form-help">Сколько раз один клиент может использовать</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Ограничения</h2>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Общий лимит использований</label>
                    <input type="number" name="usage_limit" class="form-control"
                           min="0" placeholder="Без ограничений"
                           value="<?php echo $_POST['usage_limit'] ?? ''; ?>">
                    <div class="form-help">Пусто = неограниченно</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Статус</label>
                    <div style="margin-top: 0.75rem;">
                        <div class="form-check">
                            <input type="checkbox" id="is_active" name="is_active" value="1" 
                                   <?php echo ($_POST['is_active'] ?? '1') ? 'checked' : ''; ?>>
                            <label for="is_active">Промокод активен</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Действует с</label>
                    <input type="date" name="valid_from" class="form-control"
                           value="<?php echo $_POST['valid_from'] ?? date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Действует до</label>
                    <input type="date" name="valid_until" class="form-control"
                           value="<?php echo $_POST['valid_until'] ?? ''; ?>">
                    <div class="form-help">Пусто = бессрочно</div>
                </div>
            </div>
            
            <div class="examples-box">
                <div class="examples-title">Примеры промокодов:</div>
                <div style="font-size: 0.875rem; color: #92400e;">
                    <span class="example-code">WELCOME10</span> - скидка 10% для новых клиентов<br>
                    <span class="example-code">PRINT500</span> - скидка 500₽ на печать<br>
                    <span class="example-code">VIP2024</span> - скидка 20% до 1000₽ для VIP клиентов
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Создать промокод
            </button>
            <a href="promocodes.php" class="btn btn-secondary">
                Отмена
            </a>
        </div>
    </form>
</div>

<script>
// Генерация случайного кода
function generateCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.querySelector('input[name="code"]').value = code;
}

// Преобразование в верхний регистр
document.querySelector('input[name="code"]').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9\-_]/g, '');
});

// Изменение типа скидки
document.querySelectorAll('input[name="discount_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Обновляем выделение
        document.querySelectorAll('.discount-type-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        this.closest('.discount-type-option').classList.add('selected');
        
        // Обновляем подсказку
        const help = document.getElementById('discountHelp');
        if (this.value === 'percent') {
            help.textContent = 'Процент скидки (от 0 до 100)';
            document.querySelector('input[name="discount_value"]').max = '100';
        } else {
            help.textContent = 'Сумма скидки в рублях';
            document.querySelector('input[name="discount_value"]').removeAttribute('max');
        }
    });
});

// Валидация формы
document.querySelector('form').addEventListener('submit', function(e) {
    const code = document.querySelector('input[name="code"]').value;
    if (!code || !/^[A-Z0-9\-_]+$/.test(code)) {
        e.preventDefault();
        alert('Введите корректный код промокода');
        return false;
    }
    
    const discountValue = parseFloat(document.querySelector('input[name="discount_value"]').value);
    const discountType = document.querySelector('input[name="discount_type"]:checked').value;
    
    if (isNaN(discountValue) || discountValue <= 0) {
        e.preventDefault();
        alert('Укажите корректный размер скидки');
        return false;
    }
    
    if (discountType === 'percent' && discountValue > 100) {
        e.preventDefault();
        alert('Процент скидки не может быть больше 100');
        return false;
    }
    
    const validFrom = document.querySelector('input[name="valid_from"]').value;
    const validUntil = document.querySelector('input[name="valid_until"]').value;
    
    if (validUntil && new Date(validUntil) < new Date(validFrom)) {
        e.preventDefault();
        alert('Дата окончания не может быть раньше даты начала');
        return false;
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>