<?php
// admin/user-create.php - Создание нового пользователя
require_once 'includes/auth_check.php';

require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/AdminLog.php';

// Проверяем авторизацию и права
checkAdminAuth('edit_users');

// Подключаемся к БД
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$adminLog = new AdminLog($db);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Валидация
        $phone = trim($_POST['phone'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $company_address = trim($_POST['company_address'] ?? '');
        $inn = trim($_POST['inn'] ?? '');
        
        // Проверка обязательного поля
        if (empty($phone)) {
            throw new Exception('Телефон обязателен для заполнения');
        }
        
        // Форматирование телефона
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
            throw new Exception('Неверный формат телефона');
        }
        
        // Проверка email
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Неверный формат email');
        }
        
        // Проверка ИНН
        if (!empty($inn) && !preg_match('/^\d{10}$|^\d{12}$/', $inn)) {
            throw new Exception('ИНН должен содержать 10 или 12 цифр');
        }
        
        // Проверка существования телефона
        if ($user->phoneExists($phone)) {
            throw new Exception('Пользователь с таким телефоном уже существует');
        }
        
        // Проверка существования email
        if (!empty($email) && $user->emailExists($email)) {
            throw new Exception('Пользователь с таким email уже существует');
        }
        
        // Создаем пользователя
        $userData = [
            'phone' => $phone,
            'name' => $name,
            'email' => $email ?: null,
            'company_name' => $company_name ?: null,
            'company_address' => $company_address ?: null,
            'inn' => $inn ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_verified' => isset($_POST['is_verified']) ? 1 : 0
        ];
        
        $user_id = $user->createUser($userData);
        
        if ($user_id) {
            // Логируем действие
            $adminLog->log($_SESSION['admin_id'], 'create_user', 
                "Создан пользователь: {$name} ({$phone})", 'user', $user_id);
            
            $_SESSION['success'] = 'Пользователь успешно создан';
            
            // Если нужно сразу создать заказ для пользователя
            if (isset($_POST['create_order'])) {
                header('Location: order-create.php?user_id=' . $user_id);
            } else {
                header('Location: user_edit.php?id=' . $user_id);
            }
            exit;
        } else {
            throw new Exception('Ошибка создания пользователя');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Заголовок страницы
$page_title = 'Создание пользователя';
$current_page = 'users';
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

/* Алерты */
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Телефонный ввод */
.phone-input-wrapper {
    position: relative;
}

.phone-prefix {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 0.875rem;
    pointer-events: none;
}

.phone-input {
    padding-left: 2.5rem;
}

/* Адаптив */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container-fluid">
    <!-- Хлебные крошки -->
    <div class="breadcrumb">
        <a href="/admin/">Главная</a>
        <span class="separator">/</span>
        <a href="users.php">Пользователи</a>
        <span class="separator">/</span>
        <span>Создание пользователя</span>
    </div>
    
    <!-- Заголовок -->
    <div class="page-header">
        <h1 class="page-title">Новый пользователь</h1>
        <div>
            <a href="users.php" class="btn btn-secondary">
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
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Телефон <span class="required">*</span></label>
                    <div class="phone-input-wrapper">
                        <input type="tel" name="phone" class="form-control phone-input" 
                               placeholder="+7 (999) 123-45-67" required
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-help">Основной способ связи с клиентом</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Имя</label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="Иван Иванов"
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    <div class="form-help">ФИО или название организации</div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" 
                       placeholder="email@example.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <div class="form-help">Для отправки уведомлений и чеков</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Данные компании</h2>
            </div>
            
            <div class="form-group">
                <label class="form-label">Название компании</label>
                <input type="text" name="company_name" class="form-control" 
                       placeholder="ООО Рога и копыта"
                       value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">ИНН</label>
                    <input type="text" name="inn" class="form-control" 
                           placeholder="1234567890" maxlength="12"
                           value="<?php echo htmlspecialchars($_POST['inn'] ?? ''); ?>">
                    <div class="form-help">10 цифр для ИП, 12 для организаций</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Адрес</label>
                    <input type="text" name="company_address" class="form-control" 
                           placeholder="г. Москва, ул. Ленина, д. 1"
                           value="<?php echo htmlspecialchars($_POST['company_address'] ?? ''); ?>">
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Дополнительные параметры</h2>
            </div>
            
            <div class="form-row">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                    <label for="is_active">Активный пользователь</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="is_verified" name="is_verified" value="1">
                    <label for="is_verified">Подтвержденный аккаунт</label>
                </div>
            </div>
            
            <div class="form-check" style="margin-top: 1rem;">
                <input type="checkbox" id="create_order" name="create_order" value="1">
                <label for="create_order">Сразу создать заказ для этого пользователя</label>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Создать пользователя
            </button>
            <a href="users.php" class="btn btn-secondary">
                Отмена
            </a>
        </div>
    </form>
</div>

<script>
// Форматирование телефона при вводе
document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    let formattedValue = '';
    
    if (value.length > 0) {
        if (value[0] === '7') {
            // Российский номер
            if (value.length >= 1) formattedValue = '+7';
            if (value.length >= 2) formattedValue += ' (' + value.substring(1, 4);
            if (value.length >= 5) formattedValue += ') ' + value.substring(4, 7);
            if (value.length >= 8) formattedValue += '-' + value.substring(7, 9);
            if (value.length >= 10) formattedValue += '-' + value.substring(9, 11);
        } else {
            // Другой номер
            formattedValue = '+' + value;
        }
    }
    
    e.target.value = formattedValue;
});

// Валидация ИНН
document.querySelector('input[name="inn"]').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

// Валидация формы перед отправкой
document.querySelector('form').addEventListener('submit', function(e) {
    const phone = document.querySelector('input[name="phone"]').value;
    const phoneDigits = phone.replace(/\D/g, '');
    
    if (phoneDigits.length < 10) {
        e.preventDefault();
        alert('Введите корректный номер телефона');
        return false;
    }
    
    const inn = document.querySelector('input[name="inn"]').value;
    if (inn && ![10, 12].includes(inn.length)) {
        e.preventDefault();
        alert('ИНН должен содержать 10 или 12 цифр');
        return false;
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>