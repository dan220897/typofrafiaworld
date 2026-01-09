<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ панель - Типография</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --gray: #6b7280;
            --light: #f9fafb;
            --white: #ffffff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Анимация фона */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%),
                radial-gradient(circle at 80% 20%, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            animation: backgroundMove 20s ease-in-out infinite;
        }

        @keyframes backgroundMove {
            0%, 100% { transform: translate(0, 0); }
            25% { transform: translate(-20px, -20px); }
            50% { transform: translate(20px, -20px); }
            75% { transform: translate(-20px, 20px); }
        }

        /* Контейнер входа */
        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 40px 30px;
            text-align: center;
            color: var(--white);
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }

        /* Переключатель методов входа */
        .auth-methods {
            display: flex;
            background: #f3f4f6;
            padding: 4px;
            border-radius: 12px;
            margin: -20px 30px 20px;
            position: relative;
            z-index: 10;
        }

        .auth-method {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: transparent;
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
        }

        .auth-method.active {
            background: var(--white);
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .auth-method:hover:not(.active) {
            color: var(--dark);
        }

        .auth-method i {
            font-size: 16px;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-control:hover {
            border-color: #d1d5db;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray);
            font-size: 18px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: var(--dark);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .checkbox:checked {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--white);
            font-size: 14px;
        }

        .checkbox-label {
            font-size: 14px;
            color: var(--gray);
            cursor: pointer;
            user-select: none;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-primary:active::before {
            width: 300px;
            height: 300px;
        }

        /* Сообщения об ошибках */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert i {
            font-size: 18px;
        }

        /* Индикатор загрузки */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid var(--white);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn.loading .spinner {
            display: inline-block;
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        /* Дополнительные опции */
        .login-footer {
            padding: 20px 30px;
            background: #f9fafb;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer p {
            color: var(--gray);
            font-size: 14px;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Безопасность */
        .security-notice {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 8px;
            font-size: 13px;
            color: var(--gray);
        }

        .security-notice i {
            color: var(--success);
            font-size: 16px;
        }

        /* Адаптивность */
        @media (max-width: 480px) {
            .login-header {
                padding: 30px 20px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .login-title {
                font-size: 24px;
            }

            .form-options {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .auth-methods {
                margin: -20px 20px 20px;
            }

            .auth-method {
                font-size: 13px;
                padding: 10px 16px;
            }

            .auth-method i {
                font-size: 14px;
            }
        }

        /* Анимация при загрузке */
        .login-container {
            animation: fadeInScale 0.5s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-print"></i>
            </div>
            <h1 class="login-title">Админ панель</h1>
            <p class="login-subtitle">Типография PrintHub</p>
        </div>

        <!-- Переключатель методов входа -->
        <div class="auth-methods">
            <button class="auth-method active" onclick="location.href='login.php'">
                <i class="fas fa-key"></i>
                С паролем
            </button>
            <button class="auth-method" onclick="location.href='login-sms.php'">
                <i class="fas fa-mobile-alt"></i>
                По SMS
            </button>
        </div>

        <div class="login-body">
            <!-- Сообщение об ошибке -->
            <div class="alert alert-danger" id="errorAlert" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorMessage">Неверный логин или пароль</span>
            </div>

            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label class="form-label">Логин</label>
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="username" 
                            id="username"
                            placeholder="Введите ваш логин"
                            required
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            name="password" 
                            id="password"
                            placeholder="Введите пароль"
                            required
                            autocomplete="current-password"
                        >
                        <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                    </div>
                </div>

                <div class="form-options">
                    <div class="checkbox-group">
                        <input type="checkbox" class="checkbox" id="remember" name="remember">
                        <label for="remember" class="checkbox-label">Запомнить меня</label>
                    </div>
                    <a href="#" class="forgot-link">Забыли пароль?</a>
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
                    Войти
                    <span class="spinner"></span>
                </button>

                <div class="security-notice">
                    <i class="fas fa-shield-alt"></i>
                    <span>Защищенное соединение. Ваши данные в безопасности.</span>
                </div>
            </form>
        </div>

        <div class="login-footer">
            <p>Нужна помощь? <a href="#">Связаться с поддержкой</a></p>
        </div>
    </div>

    <script>
        // Переключение видимости пароля
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');

        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Меняем иконку
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Обработка формы входа
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const errorAlert = document.getElementById('errorAlert');
        const errorMessage = document.getElementById('errorMessage');

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Скрываем ошибки
            errorAlert.style.display = 'none';
            
            // Показываем загрузку
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            
            const formData = new FormData(loginForm);
            
            try {
                const response = await fetch('login_process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Успешный вход
                    window.location.href = data.redirect || 'index.php';
                } else {
                    // Показываем ошибку
                    errorMessage.textContent = data.message || 'Неверный логин или пароль';
                    errorAlert.style.display = 'flex';
                    
                    // Встряхиваем форму
                    loginForm.style.animation = 'shake 0.5s';
                    setTimeout(() => {
                        loginForm.style.animation = '';
                    }, 500);
                }
            } catch (error) {
                errorMessage.textContent = 'Ошибка соединения. Попробуйте позже.';
                errorAlert.style.display = 'flex';
            } finally {
                loginBtn.classList.remove('loading');
                loginBtn.disabled = false;
            }
        });

        // Анимация встряхивания
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
                20%, 40%, 60%, 80% { transform: translateX(10px); }
            }
        `;
        document.head.appendChild(style);

        // Автофокус на первое поле
        document.getElementById('username').focus();

        // Проверка капслока
        passwordInput.addEventListener('keyup', function(e) {
            const capsLockOn = e.getModifierState && e.getModifierState('CapsLock');
            if (capsLockOn) {
                // Можно показать предупреждение о включенном Caps Lock
                console.log('Caps Lock включен');
            }
        });
    </script>
</body>
</html>