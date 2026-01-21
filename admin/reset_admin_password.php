<?php
// admin/reset_admin_password.php - Скрипт для сброса пароля администратора
// ВАЖНО: Удалите этот файл после использования!

require_once 'config/database.php';

// Обработка формы
$success = false;
$error = null;
$admin_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');

    if (empty($username) || empty($new_password)) {
        $error = "Заполните все поля";
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();

            if (!$db) {
                throw new Exception("Не удалось подключиться к базе данных");
            }

            // Проверяем, существует ли пользователь
            $check_query = "SELECT id, username, email, role FROM admins WHERE username = :username";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':username', $username);
            $check_stmt->execute();

            if ($check_stmt->rowCount() === 0) {
                $error = "Пользователь '$username' не найден в базе данных!";
            } else {
                $user = $check_stmt->fetch(PDO::FETCH_ASSOC);

                // Хешируем новый пароль
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Обновляем пароль
                $update_query = "UPDATE admins SET password_hash = :password_hash WHERE username = :username";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':password_hash', $password_hash);
                $update_stmt->bindParam(':username', $username);

                if ($update_stmt->execute()) {
                    $success = true;
                    $admin_data = [
                        'username' => $username,
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'password' => $new_password
                    ];
                } else {
                    $error = "Ошибка при обновлении пароля!";
                }
            }
        } catch (Exception $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля администратора</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .header p {
            color: #6b7280;
            font-size: 14px;
        }

        .warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .success-box {
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .success-box h3 {
            color: #065f46;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #d1fae5;
        }

        .credential-item:last-child {
            border-bottom: none;
        }

        .credential-label {
            color: #6b7280;
            font-size: 14px;
        }

        .credential-value {
            color: #1f2937;
            font-weight: 500;
            font-size: 14px;
        }

        .delete-warning {
            background: #fef2f2;
            border: 2px solid #fca5a5;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Сброс пароля администратора</h1>
            <p>Введите логин и новый пароль</p>
        </div>

        <div class="warning">
            ⚠️ Этот скрипт должен быть удален после использования!
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ Пароль успешно обновлен!
            </div>

            <div class="success-box">
                <h3>Данные для входа:</h3>
                <div class="credential-item">
                    <span class="credential-label">URL:</span>
                    <span class="credential-value">/admin/login-superadmin.php</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Логин:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($admin_data['username']); ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($admin_data['email']); ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Роль:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($admin_data['role']); ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Пароль:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($admin_data['password']); ?></span>
                </div>
            </div>

            <div class="delete-warning">
                🗑️ Удалите файл reset_admin_password.php после входа!
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Логин администратора</label>
                    <input type="text" name="username" class="form-control" value="admin" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Новый пароль</label>
                    <input type="text" name="new_password" class="form-control" placeholder="Введите новый пароль" required>
                </div>

                <button type="submit" class="btn btn-primary">Сбросить пароль</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
