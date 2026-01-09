<?php
// admin/login-sms.php - SMS авторизация для администраторов
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Если уже авторизован - на дашборд
if (isset($_SESSION['admin_id']) && isset($_SESSION['login_time']) && 
    (time() - $_SESSION['login_time']) < SESSION_LIFETIME) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель - <?php echo SITE_NAME; ?></title>
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
        
        .login-container {
            background: white;
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
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
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
        
        .phone-input-group {
            display: flex;
            gap: 8px;
        }
        
        .phone-prefix {
            width: 80px;
            text-align: center;
            flex-shrink: 0;
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
            color: white;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(59, 130, 246, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .code-inputs {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .code-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.3s;
            background: #f9fafb;
        }
        
        .code-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
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
        
        .alert-error {
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
        
        .resend-link {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: var(--gray);
        }
        
        .resend-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .resend-link a:hover {
            text-decoration: underline;
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
        
        #timer {
            color: var(--primary);
            font-weight: 600;
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

            .code-inputs {
                gap: 6px;
            }

            .code-input {
                width: 45px;
                height: 45px;
                font-size: 20px;
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
            <button class="auth-method" onclick="location.href='login.php'">
                <i class="fas fa-key"></i>
                С паролем
            </button>
            <button class="auth-method active" onclick="location.href='login-sms.php'">
                <i class="fas fa-mobile-alt"></i>
                По SMS
            </button>
        </div>

        <div class="login-body">
            <div id="alert-container"></div>
            
            <!-- Шаг 1: Ввод телефона -->
            <div id="phoneStep">
                <form id="phoneForm">
                    <div class="form-group">
                        <label class="form-label">Номер телефона администратора</label>
                        <div class="phone-input-group">
                            <input type="text" class="form-control phone-prefix" value="+7" readonly>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="phoneInput"
                                placeholder="999 123-45-67"
                                maxlength="15"
                                required
                            >
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="sendCodeBtn">
                        Получить код
                        <span class="spinner"></span>
                    </button>
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <span>SMS-код будет отправлен на ваш номер</span>
                    </div>
                </form>
            </div>
            
            <!-- Шаг 2: Ввод кода -->
            <div id="codeStep" style="display: none;">
                <form id="codeForm">
                    <div class="form-group">
                        <label class="form-label">Введите код из SMS</label>
                        <div class="code-inputs">
                            <input type="text" class="code-input" maxlength="1" data-index="0">
                            <input type="text" class="code-input" maxlength="1" data-index="1">
                            <input type="text" class="code-input" maxlength="1" data-index="2">
                            <input type="text" class="code-input" maxlength="1" data-index="3">
                            <input type="text" class="code-input" maxlength="1" data-index="4">
                            <input type="text" class="code-input" maxlength="1" data-index="5">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="verifyCodeBtn">
                        Войти
                        <span class="spinner"></span>
                    </button>
                    <div class="resend-link">
                        Не получили код? 
                        <a href="#" id="resendLink" style="display: none;">Отправить снова</a>
                        <span id="timer"></span>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="login-footer">
            <p>Нужна помощь? <a href="#">Связаться с поддержкой</a></p>
        </div>
    </div>
    
    <script>
        let currentPhone = '';
        let resendTimer = null;
        let resendSeconds = 60;
        
        // Маска для телефона
        document.getElementById('phoneInput').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            
            if (value.length > 0) {
                formattedValue = value.substring(0, 3);
            }
            if (value.length > 3) {
                formattedValue += ' ' + value.substring(3, 6);
            }
            if (value.length > 6) {
                formattedValue += '-' + value.substring(6, 8);
            }
            if (value.length > 8) {
                formattedValue += '-' + value.substring(8, 10);
            }
            
            e.target.value = formattedValue;
        });
        
        // Навигация между полями кода
        document.querySelectorAll('.code-input').forEach((input, index) => {
            input.addEventListener('input', function(e) {
                if (e.target.value && index < 5) {
                    document.querySelectorAll('.code-input')[index + 1].focus();
                }
                
                // Проверяем, все ли поля заполнены
                const code = getCode();
                document.getElementById('verifyCodeBtn').disabled = code.length !== 6;
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    document.querySelectorAll('.code-input')[index - 1].focus();
                }
            });
            
            // Вставка из буфера
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = e.clipboardData.getData('text').replace(/\D/g, '');
                if (paste.length === 6) {
                    document.querySelectorAll('.code-input').forEach((inp, i) => {
                        inp.value = paste[i] || '';
                    });
                    document.getElementById('verifyCodeBtn').disabled = false;
                }
            });
        });
        
        // Получение кода из полей
        function getCode() {
            let code = '';
            document.querySelectorAll('.code-input').forEach(input => {
                code += input.value;
            });
            return code;
        }
        
        // Показ уведомления
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
                    ${message}
                </div>
            `;
        }
        
        // Таймер повторной отправки
        function startResendTimer() {
            resendSeconds = 60;
            const resendLink = document.getElementById('resendLink');
            const timer = document.getElementById('timer');
            
            resendLink.style.display = 'none';
            timer.style.display = 'inline';
            
            resendTimer = setInterval(() => {
                resendSeconds--;
                timer.textContent = `(${resendSeconds} сек)`;
                
                if (resendSeconds <= 0) {
                    clearInterval(resendTimer);
                    resendLink.style.display = 'inline';
                    timer.style.display = 'none';
                }
            }, 1000);
        }
        
        // Отправка телефона
        document.getElementById('phoneForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const phone = '+7' + document.getElementById('phoneInput').value.replace(/\D/g, '');
            
            if (phone.length !== 12) {
                showAlert('Введите корректный номер телефона');
                return;
            }
            
            const btn = document.getElementById('sendCodeBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            
            try {
                const response = await fetch('api/admin-sms-auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'send_code',
                        phone: phone
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentPhone = phone;
                    document.getElementById('phoneStep').style.display = 'none';
                    document.getElementById('codeStep').style.display = 'block';
                    document.querySelectorAll('.code-input')[0].focus();
                    startResendTimer();
                    
                    // Для тестирования - показываем код (УДАЛИТЬ В PRODUCTION!)
                    if (data.demo_code) {
                        showAlert(`Демо-режим: код ${data.demo_code}`, 'success');
                    }
                } else {
                    showAlert(data.message || 'Ошибка отправки кода');
                }
            } catch (error) {
                showAlert('Ошибка соединения с сервером');
            } finally {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        });
        
        // Проверка кода
        document.getElementById('codeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const code = getCode();
            if (code.length !== 6) {
                showAlert('Введите 6-значный код');
                return;
            }
            
            const btn = document.getElementById('verifyCodeBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            
            try {
                const response = await fetch('api/admin-sms-auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'verify_code',
                        phone: currentPhone,
                        code: code
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect || 'index.php';
                } else {
                    showAlert(data.message || 'Неверный код');
                    // Очищаем поля
                    document.querySelectorAll('.code-input').forEach(input => {
                        input.value = '';
                    });
                    document.querySelectorAll('.code-input')[0].focus();
                }
            } catch (error) {
                showAlert('Ошибка соединения с сервером');
            } finally {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        });
        
        // Повторная отправка
        document.getElementById('resendLink').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('phoneForm').dispatchEvent(new Event('submit'));
        });

        // Автофокус на первое поле
        document.getElementById('phoneInput').focus();
    </script>
</body>
</html>