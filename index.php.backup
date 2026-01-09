<?php
require_once 'config/config.php';
require_once 'classes/UserService.php';

$userService = new UserService();
$isAuthenticated = $userService->isAuthenticated();
$currentUser = $isAuthenticated ? $userService->getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= SITE_NAME ?> - Профессиональная типография онлайн</title>
    <meta name="description" content="Качественная полиграфия с доставкой. Визитки, баннеры, флаеры, дизайн. Быстрое изготовление, низкие цены.">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            /* Современные цвета 2025 */
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --primary-bg: rgba(99, 102, 241, 0.1);
            
            --secondary: #ec4899;
            --secondary-light: #f472b6;
            --secondary-dark: #db2777;
            
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            
            /* Нейтральные цвета */
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --gray-950: #030712;
            
            /* Темная тема */
            --dark-bg: #0a0a0a;
            --dark-surface: #141414;
            --dark-surface-2: #1f1f1f;
            --dark-border: rgba(255, 255, 255, 0.1);
            
            /* Светлая тема */
            --light-bg: #ffffff;
            --light-surface: #f9fafb;
            --light-surface-2: #f3f4f6;
            --light-border: rgba(0, 0, 0, 0.1);
            
            /* Активная тема */
            --bg: var(--dark-bg);
            --surface: var(--dark-surface);
            --surface-2: var(--dark-surface-2);
            --border: var(--dark-border);
            --text: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-tertiary: rgba(255, 255, 255, 0.5);
            
            /* Размеры */
            --header-height: 60px;
            --mobile-nav-height: 70px;
            --sidebar-width: 360px;
            --chat-max-width: 1400px;
            
            /* Анимации */
            --transition-fast: 150ms ease;
            --transition-base: 300ms ease;
            --transition-slow: 500ms ease;
            
            /* Тени */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Светлая тема */
        [data-theme="light"] {
            --bg: var(--light-bg);
            --surface: var(--light-surface);
            --surface-2: var(--light-surface-2);
            --border: var(--light-border);
            --text: var(--gray-900);
            --text-secondary: var(--gray-600);
            --text-tertiary: var(--gray-500);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Фоновый градиент */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(236, 72, 153, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* Header для десктопа */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: rgba(var(--bg), 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            display: none;
        }

        @media (min-width: 768px) {
            .header {
                display: block;
            }
        }

        .header-content {
            
            margin: 0 auto;
            height: 100%;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 800;
            font-size: 20px;
            color: var(--text);
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Основной контейнер */
        .main-container {
            flex: 1;
    display: flex;
    position: fixed;
    overflow: hidden;
    padding-top: 0;
    height: 100%;
    width: 100%;
        }

        @media (min-width: 768px) {
            .main-container {
                padding-top: var(--header-height);
            }
        }

        /* Сайдбар для десктопа */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: none;
            flex-direction: column;
            position: relative;
            z-index: 10;
        }

        @media (min-width: 1024px) {
            .sidebar {
                display: flex;
            }
        }

        /* Мобильный header */
        .mobile-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
        }

        @media (min-width: 768px) {
            .mobile-header {
                display: none;
            }
        }

        /* Чат контейнер */
        .chat-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            background: var(--bg);
            width: 100%;
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            
            margin: 0 auto;
            width: 100%;
            height: 100%;
            
        }

        /* Исправленная структура для мобильных */
@media (max-width: 768px) {
    .main-container {
        padding-top: var(--header-height);
        padding-bottom:0px;
    }
    
    .chat-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 0;
        
    }
    
    .chat-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 0;
        height: 100%;
    }
    
    .chat-header {
        flex-shrink: 0;
        margin-top:60px;
    }
    
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        min-height: 0;
    }
    
    .quick-actions {
        flex-shrink: 0;
    }
    
    .chat-input-wrapper {
        flex-shrink: 0;
        background: var(--surface);
    }
}

        /* Заголовок чата */
        .chat-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .chat-avatar {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            position: relative;
        }

        .chat-avatar::after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: var(--success);
            border-radius: 50%;
            border: 2px solid var(--surface);
        }

        .chat-info {
            flex: 1;
        }

        .chat-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 2px;
        }

        .chat-status {
            font-size: 13px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .chat-status-dot {
            width: 6px;
            height: 6px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.8); }
        }

        /* Область сообщений */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            scroll-behavior: smooth;
        }

        .message {
            display: flex;
            margin-bottom: 16px;
            animation: messageIn 0.3s ease-out;
        }

        @keyframes messageIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 18px;
            background: var(--surface-2);
            word-wrap: break-word;
        }

        @media (max-width: 640px) {
            .message-bubble {
                max-width: 85%;
            }
        }

        .message.sent .message-bubble {
            background: var(--primary);
            color: white;
        }

        .message-text {
            font-size: 15px;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .message-time {
            font-size: 12px;
            margin-top: 4px;
            opacity: 0.7;
        }

        /* Быстрые действия */
        .quick-actions {
            padding: 12px 20px;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            gap: 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .quick-actions::-webkit-scrollbar {
            display: none;
        }

        .quick-action {
            flex-shrink: 0;
            padding: 8px 16px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            white-space: nowrap;
            color: var(--text);
        }

        .quick-action:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        /* Форма ввода */
        .chat-input-wrapper {
            padding: 16px 20px;
            background: var(--surface);
            border-top: 1px solid var(--border);
        }

        .chat-input-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .input-group {
            flex: 1;
            position: relative;
        }

        .chat-input {
            width: 100%;
            padding: 12px 48px 12px 16px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 24px;
            font-size: 15px;
            font-family: inherit;
            resize: none;
            color: var(--text);
            transition: all var(--transition-fast);
            min-height: 44px;
            max-height: 120px;
        }

        .chat-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .chat-input::placeholder {
            color: var(--text-tertiary);
        }

        .input-actions {
            position: absolute;
            right: 20px;
            bottom: 8px;
            display: flex;
            gap: 4px;
            top:8px;
        }

        .input-btn {
            width: 28px;
            height: 28px;
            border: none;
            background: none;
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all var(--transition-fast);
        }

        .input-btn:hover {
            background: var(--primary-bg);
            color: var(--primary);
        }

        .send-btn {
            width: 44px;
            height: 44px;
            border: none;
            background: var(--primary);
            color: white;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
            flex-shrink: 0;
            position: relative;
            top: -7px;
            left: -3px;
        }

        .send-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Мобильная навигация */
        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--mobile-nav-height);
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 0 16px;
            z-index: 100;
        }

        @media (min-width: 768px) {
            .mobile-nav {
                display: none;
            }
        }

        .nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 8px;
            border-radius: 12px;
            cursor: pointer;
            transition: all var(--transition-fast);
            color: var(--text-secondary);
            text-decoration: none;
            -webkit-tap-highlight-color: transparent;
        }

        .nav-item:active {
            transform: scale(0.95);
        }

        .nav-item.active {
            color: var(--primary);
            background: var(--primary-bg);
        }

        .nav-item i {
            font-size: 20px;
        }

        .nav-item span {
            font-size: 11px;
            font-weight: 500;
        }

        /* Модалки и оверлеи */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--surface);
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalIn 0.3s ease-out;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            padding: 24px 24px 0;
            text-align: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .modal-subtitle {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .modal-body {
            padding: 24px;
        }

        /* Боковая панель мобильная */
        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            bottom: 0;
            width: 85%;
            max-width: 320px;
            background: var(--surface);
            z-index: 1000;
            transition: left var(--transition-base);
            overflow-y: auto;
        }

        .mobile-sidebar.active {
            left: 0;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Кнопки */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            line-height: 1;
            -webkit-tap-highlight-color: transparent;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: var(--surface-2);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--surface);
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 12px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-block {
            width: 100%;
        }

        /* Формы */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            color: var(--text);
            transition: all var(--transition-fast);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Табы сайдбара */
        .sidebar-tabs {
            display: flex;
            padding: 12px;
            gap: 8px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-tab {
            flex: 1;
            padding: 10px;
            background: none;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .sidebar-tab.active {
            background: var(--primary);
            color: white;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }

        /* Карточки услуг */
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        @media (max-width: 640px) {
            .service-grid {
                grid-template-columns: 1fr;
            }
        }

        .service-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            cursor: pointer;
            transition: all var(--transition-base);
        }

        .service-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .service-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-bg);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .service-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .service-description {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .service-price {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        /* Пустые состояния */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--text-secondary);
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }

        .empty-state-text {
            font-size: 14px;
            line-height: 1.5;
        }
        .form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

.form-text.text-muted {
    color: #6c757d;
}

.text-muted {
    color: #6c757d !important;
    font-size: 0.85rem;
    font-weight: normal;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
}
.form-text {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #6c757d;
}

.form-text.text-muted {
    color: #6c757d;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.code-inputs {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 20px 0;
}

.code-input {
    width: 50px;
    height: 60px;
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    border: 2px solid #ddd;
    border-radius: 8px;
    transition: all 0.3s;
}

.code-input:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
        /* Загрузчик */
        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loader-lg {
            width: 32px;
            height: 32px;
            border-width: 3px;
        }

        /* Уведомления */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: var(--shadow-xl);
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 360px;
            animation: notificationIn 0.3s ease-out;
            z-index: 1100;
        }

        @media (max-width: 640px) {
            .notification {
                right: 16px;
                left: 16px;
                max-width: none;
            }
        }

        @keyframes notificationIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .notification-icon {
            font-size: 20px;
        }

        .notification.success {
            border-left: 4px solid var(--success);
        }

        .notification.success .notification-icon {
            color: var(--success);
        }

        .notification.error {
            border-left: 4px solid var(--danger);
        }

        .notification.error .notification-icon {
            color: var(--danger);
        }

        /* Темы для переключателя */
        .theme-toggle {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--surface-2);
            color: var(--text);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
        }

        .theme-toggle:hover {
            background: var(--primary-bg);
            color: var(--primary);
        }

        /* Состояние онлайн/оффлайн */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: var(--surface-2);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.online {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        /* Кастомный скроллбар */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-tertiary);
        }

        /* Утилиты */
        .text-center { text-align: center; }
        .text-secondary { color: var(--text-secondary); }
        .text-sm { font-size: 13px; }
        .font-bold { font-weight: 700; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        
        /* Медиа запросы для планшетов */
        @media (min-width: 768px) and (max-width: 1023px) {
            .sidebar {
                display: flex;
                width: 300px;
            }
            
            .chat-messages {
                padding: 24px;
            }
        }

        /* Анимации для мобильных жестов */
        @supports (-webkit-touch-callout: none) {
            .mobile-sidebar {
                -webkit-overflow-scrolling: touch;
            }
            
            .btn:active {
                transform: scale(0.95);
            }
        }
        /* Поддержка iPhone X+ */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .mobile-nav {
        padding-bottom: env(safe-area-inset-bottom);
    }
    
    
}

/* Стили для загрузки файлов */
        .file-upload-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        
        .file-upload-modal.active {
            display: flex;
        }
        
        .file-upload-container {
            background: var(--surface);
            border-radius: 24px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: modalIn 0.3s ease-out;
        }
        
        .file-upload-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .file-upload-title {
            font-size: 20px;
            font-weight: 700;
        }
        
        .file-upload-close {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--surface-2);
            color: var(--text);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
        }
        
        .file-upload-close:hover {
            background: var(--danger);
            color: white;
        }
        
        .file-dropzone {
            margin: 24px;
            border: 2px dashed var(--border);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            transition: all var(--transition-fast);
            cursor: pointer;
            position: relative;
            background: var(--surface-2);
        }
        
        .file-dropzone.dragover {
            border-color: var(--primary);
            background: var(--primary-bg);
            transform: scale(1.02);
        }
        
        .file-dropzone-icon {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 16px;
        }
        
        .file-dropzone-text {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .file-dropzone-hint {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .file-input-hidden {
            display: none;
        }
        
        .file-preview-list {
            max-height: 400px;
            overflow-y: auto;
            padding: 0 24px 24px;
        }
        
        .file-preview-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: var(--surface-2);
            border-radius: 12px;
            margin-bottom: 12px;
            position: relative;
            transition: all var(--transition-fast);
        }
        
        .file-preview-item:hover {
            background: var(--surface);
            transform: translateX(4px);
        }
        
        .file-preview-item.uploading {
            opacity: 0.7;
        }
        
        .file-preview-item.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
        }
        
        .file-preview-item.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
        }
        
        .file-preview-thumbnail {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
        }
        
        .file-preview-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .file-preview-icon {
            font-size: 24px;
            color: var(--text-secondary);
        }
        
        .file-preview-info {
            flex: 1;
            min-width: 0;
        }
        
        .file-preview-name {
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }
        
        .file-preview-details {
            font-size: 12px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .file-upload-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
        }
        /* Стили для карточек заказов */
.order-card {
    position: relative;
}

.order-card-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.order-statuses {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    flex-wrap: wrap;
}

.order-status-badge,
.payment-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.payment-status-badge i {
    font-size: 11px;
}

/* Адаптация для мобильных */
@media (max-width: 640px) {
    .order-statuses {
        position: absolute;
        bottom: 50px;
        left: 20px;
        right: 20px;
    }
}
        
        .file-upload-progress-bar {
            height: 100%;
            background: var(--primary);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .file-remove-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: var(--surface);
            color: var(--text-secondary);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
        }
        
        .file-remove-btn:hover {
            background: var(--danger);
            color: white;
        }
        
        .file-upload-actions {
            padding: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .message-file {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            margin-top: 8px;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .message-file:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: translateY(-1px);
        }
        
        .message-file-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .message-file-info {
            flex: 1;
            min-width: 0;
        }
        
        .message-file-name {
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-file-size {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .message-image {
            max-width: 300px;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 8px;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .message-image:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-lg);
        }
        
        .message-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .image-lightbox {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
            cursor: zoom-out;
        }
        
        .image-lightbox.active {
            display: flex;
        }
        
        .image-lightbox img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .image-lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            transition: all var(--transition-fast);
        }
        
        .image-lightbox-close:hover {
            background: var(--danger);
        }
        
        .file-count-badge {
            background: var(--primary);
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }
        
        .file-type-pdf { color: #ef4444; }
        .file-type-doc { color: #3b82f6; }
        .file-type-xls { color: #10b981; }
        .file-type-zip { color: #f59e0b; }
        .file-type-image { color: #8b5cf6; }
        
        @media (max-width: 640px) {
            .file-upload-container {
                border-radius: 24px 24px 0 0;
                max-height: 100vh;
            }
            
            .file-dropzone {
                padding: 24px;
            }
            
            .message-image {
                max-width: 200px;
            }
        }
        
        @keyframes uploadPulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
        
        .uploading .file-preview-thumbnail {
            animation: uploadPulse 1.5s infinite;
        }
        
        .file-types-hint {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
            justify-content: center;
        }
        
        .file-type-chip {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            background: var(--surface);
            border-radius: 20px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .file-type-chip i {
            font-size: 14px;
        }
        /* ============================================
   МОДАЛЬНОЕ ОКНО АВТОРИЗАЦИИ - ОБНОВЛЕННЫЙ СТИЛЬ
   ============================================ */

/* Overlay */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9998;
    animation: fadeIn 0.2s ease-out;
}

/* Модальное окно */
.modal-content {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 440px;
    background: #ffffff;
    border-radius: 24px;
    padding: 48px 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    animation: slideUp 0.3s ease-out;
}

@media (max-width: 640px) {
    .modal-content {
        padding: 40px 28px;
        max-width: 95%;
        border-radius: 20px;
    }
}

/* Кнопка закрытия */
.modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 36px;
    height: 36px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 12px;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #e5e7eb;
    color: #374151;
    transform: scale(1.05);
}

/* Заголовок секции */
.auth-header {
    text-align: center;
    margin-bottom: 32px;
}

.auth-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25);
}

.auth-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 12px 0;
    letter-spacing: -0.5px;
}

.auth-header p {
    font-size: 15px;
    color: #6b7280;
    margin: 0;
    line-height: 1.6;
}

/* Форма */
.form-group {
    margin-bottom: 24px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    letter-spacing: 0.2px;
}

.form-control {
    width: 100%;
    padding: 14px 18px;
    font-size: 16px;
    color: #111827;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.2s ease;
    font-family: inherit;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    background: #ffffff;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-control::placeholder {
    color: #9ca3af;
}

.form-control.is-invalid {
    border-color: #ef4444;
    background: #fef2f2;
}

.form-control.is-invalid:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

/* Текст подсказки */
.form-text {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
}

.text-muted {
    color: #9ca3af;
}

/* Сообщение об ошибке */
.error-message {
    margin-top: 12px;
    padding: 12px 16px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #dc2626;
    font-size: 14px;
    display: none;
}

.error-message:not(:empty) {
    display: block;
}

/* Кнопки */
.btn {
    padding: 14px 24px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:active:not(:disabled) {
    transform: translateY(0);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-block {
    width: 100%;
}

.btn-link {
    background: transparent;
    color: #667eea;
    padding: 12px;
    font-size: 14px;
    font-weight: 500;
}

.btn-link:hover {
    background: #f3f4f6;
    color: #5568d3;
}

/* Поля для кода */
.code-inputs {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin: 28px 0;
}

.code-input {
    width: 52px;
    height: 64px;
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    transition: all 0.2s ease;
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
}

.code-input:focus {
    outline: none;
    background: #ffffff;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: scale(1.05);
}

@media (max-width: 640px) {
    .code-inputs {
        gap: 8px;
    }
    
    .code-input {
        width: 44px;
        height: 56px;
        font-size: 24px;
    }
}

/* Loader */
.loader {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translate(-50%, -45%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Дополнительные утилиты */
.mt-3 {
    margin-top: 16px;
}

/* Адаптивность */
@media (max-width: 640px) {
    .auth-header h2 {
        font-size: 24px;
    }
    
    .auth-icon {
        width: 56px;
        height: 56px;
        font-size: 24px;
    }
}
/* Полоса с новостью */
.news-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: 12px 20px;
    z-index: 101;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.3s ease-out;
}

.news-banner-content {
    max-width: var(--chat-max-width);
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
}

.news-banner-content i.fa-info-circle {
    font-size: 18px;
    flex-shrink: 0;
}

.news-banner-link {
    color: white;
    text-decoration: underline;
    font-weight: 700;
    transition: opacity 0.2s ease;
    white-space: nowrap;
}

.news-banner-link:hover {
    opacity: 0.8;
}

.news-banner.hidden {
    display: none;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-100%);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Корректировка отступов для header и main-container */
.header {
    top: 44px;
}

.main-container {
    padding-top: 44px;
}

@media (min-width: 768px) {
    .main-container {
        padding-top: calc(var(--header-height) + 44px);
    }
}

/* Мобильная версия баннера */
@media (max-width: 640px) {
    .news-banner {
        padding: 8px 12px;
    }
    
    .news-banner-content {
        font-size: 12px;
        gap: 6px;
        line-height: 1.4;
    }
    
    .news-banner-content i.fa-info-circle {
        font-size: 14px;
        margin-top: 2px;
        align-self: flex-start;
    }
    
    .news-banner-content span {
        flex: 1;
    }
    
    .news-banner-link {
        white-space: normal;
        word-break: break-word;
    }
    
    .header {
        top: 50px;
    }
    
    .main-container {
        padding-top: 50px;
    }
    
    .mobile-header {
        top: 50px;
    }
}

/* Для очень маленьких экранов */
@media (max-width: 360px) {
    .news-banner {
        padding: 6px 10px;
    }
    
    .news-banner-content {
        font-size: 11px;
        gap: 4px;
    }
    
    .news-banner-content i.fa-info-circle {
        font-size: 12px;
    }
}
    </style>
</head>
<body data-theme="dark">
    <div class="news-banner">
    <div class="news-banner-content">
        <i class="fas fa-info-circle"></i>
        <span>Для подробного ознакомления с услугами перейдите на 
            <a href="https://online.typo-grafia.ru" class="news-banner-link">старую версию сайта</a>
        </span>
    </div>
</div>
    <!-- Header для десктопа -->
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-print"></i>
                </div>
                <span><?= SITE_NAME ?></span>
            </a>
            
            <div class="header-actions">
                <button class="theme-toggle" onclick="toggleTheme()" title="Сменить тему">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                
                <?php if ($isAuthenticated): ?>
                    <button class="btn btn-secondary btn-sm" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="d-none d-md-inline">Выйти</span>
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary btn-sm" onclick="showAuthModal()">
                        <i class="fas fa-user"></i>
                        Войти
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mobile Header -->
    <header class="mobile-header">
        <button class="btn btn-icon btn-secondary" onclick="toggleMobileSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-print"></i>
            </div>
        </div>
        
        <button class="theme-toggle" onclick="toggleTheme()">
            <i class="fas fa-moon" id="themeIconMobile"></i>
        </button>
    </header>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Desktop Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-tabs">
                <button class="sidebar-tab active" onclick="switchSidebarTab('services')">
                    Услуги
                </button>
                <button class="sidebar-tab" onclick="switchSidebarTab('orders')">
                    Заказы
                </button>
                <button class="sidebar-tab" onclick="switchSidebarTab('profile')">
                    Профиль
                </button>
            </div>
            
            <div class="sidebar-content">
                <!-- Services Tab -->
                <div class="sidebar-pane active" id="servicesPane">
                    <div class="service-grid" id="servicesList">
                        <!-- Загрузка услуг -->
                    </div>
                </div>
                
                <!-- Orders Tab -->
                <div class="sidebar-pane" id="ordersPane" style="display: none;">
                    <div id="ordersList">
                        <!-- Загрузка заказов -->
                    </div>
                </div>
                
                <!-- Profile Tab -->
                <div class="sidebar-pane" id="profilePane" style="display: none;">
                    <div id="profileContent">
                        <!-- Загрузка профиля -->
                    </div>
                </div>
            </div>
        </aside>

        <!-- Chat Wrapper -->
        <div class="chat-wrapper">
            <div class="chat-container">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-avatar">П</div>
                    <div class="chat-info">
                        <div class="chat-title">Поддержка <?= SITE_NAME ?></div>
                        <div class="chat-status">
                            <span class="chat-status-dot"></span>
                            Онлайн • Отвечаем быстро
                        </div>
                    </div>
                    <?php if ($isAuthenticated): ?>
                        <div class="status-badge online">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($currentUser['name'] ?: 'Клиент') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Messages -->
                <div class="chat-messages" id="chatMessages">
                    <!-- Сообщения загружаются здесь -->
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <button class="quick-action" onclick="sendQuickMessage('Хочу заказать визитки')">
                        💳 Визитки
                    </button>
                    <button class="quick-action" onclick="sendQuickMessage('Нужны баннеры')">
                        🎨 Баннеры
                    </button>
                    <button class="quick-action" onclick="sendQuickMessage('Интересуют флаеры')">
                        📄 Флаеры
                    </button>
                    <button class="quick-action" onclick="sendQuickMessage('Какие сроки изготовления?')">
                        ⏰ Сроки
                    </button>
                    <button class="quick-action" onclick="sendQuickMessage('Есть ли доставка?')">
                        🚚 Доставка
                    </button>
                    <button class="quick-action" onclick="sendQuickMessage('Нужна консультация')">
                        💬 Консультация
                    </button>
                    <button class="quick-action" onclick="sendQuickMessage('Прайс-лист')">
                        📋 Цены
                    </button>
                </div>

                <!-- Input -->
                <div class="chat-input-wrapper">
                    <form class="chat-input-form" onsubmit="sendMessage(event)">
                        <div class="input-group">
                            <textarea 
                                class="chat-input" 
                                id="messageInput"
                                placeholder="Напишите сообщение..." 
                                rows="1"
                                onkeypress="handleKeyPress(event)"
                                oninput="autoResize(this)"
                            ></textarea>
                            <div class="input-actions">
                                <button type="button" class="input-btn" onclick="attachFile()" title="Прикрепить файл">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="send-btn" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    

    <!-- Mobile Sidebar -->
    <!-- Mobile Sidebar -->
<div class="sidebar-overlay" onclick="closeMobileSidebar()"></div>
<div class="mobile-sidebar">
    <div class="sidebar-tabs">
        <button class="sidebar-tab active" onclick="switchMobileSidebarTab('services')">
            Услуги
        </button>
        <button class="sidebar-tab" onclick="switchMobileSidebarTab('orders')">
            Заказы
        </button>
        <button class="sidebar-tab" onclick="switchMobileSidebarTab('profile')">
            Профиль
        </button>
    </div>
    
    <div class="sidebar-content">
        <!-- Контент будет загружен при открытии -->
    </div>
</div>

    <!-- Auth Modal -->
    <div class="modal" id="authModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Добро пожаловать!</h2>
                <p class="modal-subtitle">Войдите для доступа ко всем функциям</p>
            </div>
            
            <div class="modal-body">
                <!-- Phone Step -->
                <div id="phoneStep">
                    <form onsubmit="sendSmsCode(event)">
                        <div class="form-group">
                            <label class="form-label">Номер телефона</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" class="form-control" value="+7" style="width: 60px;" readonly>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="phoneInput"
                                    placeholder="999 123-45-67"
                                    maxlength="15"
                                    required
                                >
                            </div>
                            <div class="error-text text-sm" id="phoneError" style="color: var(--danger); margin-top: 4px;"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block" id="sendCodeBtn">
                            Получить код
                        </button>
                    </form>
                </div>

                <!-- Code Step -->
                <div id="codeStep" style="display: none;">
                    <form onsubmit="verifyCode(event)">
                        <div class="form-group">
                            <label class="form-label">Код из SMS</label>
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <input type="text" class="form-control" maxlength="1" style="width: 45px; text-align: center; font-size: 20px; font-weight: bold;" oninput="moveToNext(this, 0)">
                                <input type="text" class="form-control" maxlength="1" style="width: 45px; text-align: center; font-size: 20px; font-weight: bold;" oninput="moveToNext(this, 1)">
                                <input type="text" class="form-control" maxlength="1" style="width: 45px; text-align: center; font-size: 20px; font-weight: bold;" oninput="moveToNext(this, 2)">
                                <input type="text" class="form-control" maxlength="1" style="width: 45px; text-align: center; font-size: 20px; font-weight: bold;" oninput="moveToNext(this, 3)">
                                <input type="text" class="form-control" maxlength="1" style="width: 45px; text-align: center; font-size: 20px; font-weight: bold;" oninput="moveToNext(this, 4)">
                                <input type="text" class="form-control" maxlength="1" style="width: 45px; text-align: center; font-size: 20px; font-weight: bold;" oninput="moveToNext(this, 5)">
                            </div>
                            <div class="error-text text-sm" id="codeError" style="color: var(--danger); margin-top: 4px;"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block" id="verifyBtn">
                            Подтвердить
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="#" onclick="resendCode()" style="color: var(--primary); text-decoration: none; font-size: 14px;">
                                Отправить код повторно
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно загрузки файлов -->
    <div class="file-upload-modal" id="fileUploadModal">
        <div class="file-upload-container">
            <div class="file-upload-header">
                <h3 class="file-upload-title">
                    Загрузка файлов
                    <span class="file-count-badge" id="fileCountBadge" style="display: none;">0</span>
                </h3>
                <button class="file-upload-close" onclick="closeFileUploadModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Зона загрузки -->
            <div class="file-dropzone" id="fileDropzone" onclick="triggerFileInput()">
                <input type="file" class="file-input-hidden" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                <div class="file-dropzone-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="file-dropzone-text">Перетащите файлы сюда или нажмите для выбора</div>
                <div class="file-dropzone-hint">Максимальный размер файла: 10 МБ</div>
                
                <div class="file-types-hint">
                    <div class="file-type-chip">
                        <i class="fas fa-image file-type-image"></i>
                        <span>Изображения</span>
                    </div>
                    <div class="file-type-chip">
                        <i class="fas fa-file-pdf file-type-pdf"></i>
                        <span>PDF</span>
                    </div>
                    <div class="file-type-chip">
                        <i class="fas fa-file-word file-type-doc"></i>
                        <span>Документы</span>
                    </div>
                    <div class="file-type-chip">
                        <i class="fas fa-file-excel file-type-xls"></i>
                        <span>Таблицы</span>
                    </div>
                    <div class="file-type-chip">
                        <i class="fas fa-file-archive file-type-zip"></i>
                        <span>Архивы</span>
                    </div>
                </div>
            </div>
            
            <!-- Список файлов для загрузки -->
            <div class="file-preview-list" id="filePreviewList"></div>
            
            <!-- Кнопки действий -->
            <div class="file-upload-actions">
                <button class="btn btn-secondary" onclick="closeFileUploadModal()">Отмена</button>
                <button class="btn btn-primary" id="uploadFilesBtn" onclick="uploadFiles()" disabled>
                    <i class="fas fa-upload"></i>
                    Загрузить <span id="uploadFileCount"></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Лайтбокс для просмотра изображений -->
    <div class="image-lightbox" id="imageLightbox" onclick="closeLightbox(event)">
        <button class="image-lightbox-close" onclick="closeLightbox()">
            <i class="fas fa-times"></i>
        </button>
        <img src="" alt="" id="lightboxImage">
    </div>

    <!-- Звук уведомления -->
<audio id="notificationSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZRQ0PVKzn77BdGAg+ltryxnMpBSuBzvLZizoIGWi78OScTgwNUrDn8LJoHgY2j9n0yXwsBS18zPLaizsIHW/A8eSaRAwMU6rm8LRoHwY2kdj0yH4tBSx+zPLajDsIHnC/8+OYQgwKVKzm77BeGQc9ltnyxXUpBSp/zvHYizwIHW++8+SZQwwLU6vl77FeGwc8ltryx3YqBSl+z/HYjD0IHW+/8+OYQgwLUqvm8LBfGgc9ltnyxHYqBSt+zfLXjT0IG2/A8+KYRQ0MUqrm8LBfGwc8ldrzx3YrBSt9zvLXjT4JG2+/8+SYRAoKU6vl8LFgGwc7lNrxxnYqBSt9z/HWjD0IG26/8+SZRAoKUqvl8LBgGwc8lNryx3YqBSh+zfHWjT4JGm+/8+OZRQwLUqrm8LFgGwc8lNryxnYrBSh9zvLXjD4JGm++8uSYRAoKUqzl77BeGwc8ldrzxnYqBSp9zvLXiz4JGm++8uOYRQwLUqrm77FeGgc9ltrxxnUrBSl9zvLWiz0JGm++8uOZRQwKUqvm77JfGgc9lNrxxXUrBSl9z/LWizwJGm++8uKYRAwKU6rm77JgGwc8ldrxx3QrBSl9zvLXizwIGm+/8uOYRQwLUqvm77BgGgc9lNryxXYrBSp9z/LXjD0IG2++8uOYRAwLUqvl77BfGwc9ltryx3YqBSl+zvLXjT4JGm+/8+OYRAwLUqvl8LFgGwc8lNrxxnYqBSp9z/HWjD0IG26/8+SZRQwLUqrl8LBfGwc8ltrxxnYrBSh+zvHXjD4JGm++8+OZRQwKU6rm8LFfGgc9lNrxxnUqBSp+zvHWjD0JGm++8+OYRQwLUqrm8LBfGgc9ldrzxnUqBSp9z/HWjT4JGm+/8+OZRQwLUqrm77FeGgc9ltrxxnUqBSl9zvLWjD4JGm++8+OYRQwLUqvm77FgGwc8ltryx3UrBSl9zvLXjD4JG2+/8+OYRQwLUqvm77FgGgc9lNrzxnUrBSl9z/LXjT0IG2++8+OZRAoKU6rm77BfGwc9ltryx3UrBSp9zvHXjD4JGm+/8+OYRQwLUqvm77FgGwc8ldrxxnYrBSt9zvLXjD4JGm++8+OZRAwLUqrm8LBfGgc9ltryx3YqBSp+z/HWjT4JGm++8+OYRQwLUqrm8LBfGgc9lNryx3YqBSp9z/LWjT4JGm++8+OZRAwKU6rl8LFgGgc9ldryx3YqBSl+z/HWjD4JGm++8+OYRQwLUqrm77BgGgc9lNrxxnYrBSp9zvHWjD4JGm++8+OYRQwLUqrl8LFgGgc9lNryxnYrBSl9zvHWjD0JGm++8+OYRAwLU6rm8LBfGgc9ldrzx3YqBSp9z/HWjT0JGm+/8+OYRQwLUqrl8LBfGgc9ldryx3YqBSp9z/HWjD4JGm++8+OYRQwLUqrm8LBfGgc9ltryx3YqBSp9z/HWjD4JGm+/8+OYRQwLUqrm8LBfGgc9ltryx3YqBSp9z/HWjD4JGm++8+OYRQwLUqrm8LBfGgc9ltryx3YqBSp9z/HWjD4JGm+/8+OYRQwLUqrm8LBfGgc9ltryx3YqBSp9z/HWjD4JGm+/8+OYRQwLUqrm8LBfGgc9ltryx3YqBSp9z/HWjD4JGm+/8+OYRQwLUqrm8LBfGgc9ltryx3YqBSp9z/HWjD4JGm+/8+OYRQwLUqrm8LBfGgc9ltryx3YqBSp9z/HWjD4JGm+/8+OYRQwLUqrm8LBfGg==" type="audio/wav">
</audio>

    <script>
        // Глобальные переменные
        let isAuthenticated = <?= $isAuthenticated ? 'true' : 'false' ?>;
        let currentUser = <?= $currentUser ? json_encode($currentUser, JSON_UNESCAPED_UNICODE) : 'null' ?>;
        let currentChatId = null;
        let lastMessageId = 0;
        let services = [];
        let activeView = 'chat';
        let messagePollingInterval = null;
        let isLoadingMessages = false;
        let displayedMessageIds = new Set();
        let lastActivityTime = Date.now();
        let messageCache = new Map();

        // После глобальных переменных в начале <script>
        let lastNotificationTime = 0;
        let notificationEnabled = false;

        // Переменные для управления файлами
        let selectedFiles = new Map();
        let uploadQueue = [];
        let isUploading = false;
        let currentOrderId = null;
        
        // Максимальный размер файла (10 МБ)
        const MAX_FILE_SIZE = 10 * 1024 * 1024;
        
        // Разрешенные типы файлов
        const ALLOWED_FILE_TYPES = {
            'image/jpeg': { ext: 'jpg', icon: 'image', color: 'file-type-image' },
            'image/jpg': { ext: 'jpg', icon: 'image', color: 'file-type-image' },
            'image/png': { ext: 'png', icon: 'image', color: 'file-type-image' },
            'image/gif': { ext: 'gif', icon: 'image', color: 'file-type-image' },
            'image/webp': { ext: 'webp', icon: 'image', color: 'file-type-image' },
            'application/pdf': { ext: 'pdf', icon: 'file-pdf', color: 'file-type-pdf' },
            'application/msword': { ext: 'doc', icon: 'file-word', color: 'file-type-doc' },
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': { ext: 'docx', icon: 'file-word', color: 'file-type-doc' },
            'application/vnd.ms-excel': { ext: 'xls', icon: 'file-excel', color: 'file-type-xls' },
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': { ext: 'xlsx', icon: 'file-excel', color: 'file-type-xls' },
            'application/zip': { ext: 'zip', icon: 'file-archive', color: 'file-type-zip' },
            'application/x-rar-compressed': { ext: 'rar', icon: 'file-archive', color: 'file-type-zip' }
        };

        // Добавьте эти функции после глобальных переменных

// Запрос разрешения на уведомления
async function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.log('Browser does not support notifications');
        return false;
    }
    
    if (Notification.permission === 'granted') {
        return true;
    }
    
    if (Notification.permission !== 'denied') {
        const permission = await Notification.requestPermission();
        return permission === 'granted';
    }
    
    return false;
}

// Показ браузерного уведомления
function showBrowserNotification(title, options = {}) {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
        return;
    }
    
    const defaultOptions = {
        icon: '/favicon.ico', // Укажите путь к иконке
        badge: '/favicon.ico',
        tag: 'chat-message',
        renotify: true,
        requireInteraction: false,
        ...options
    };
    
    try {
        const notification = new Notification(title, defaultOptions);
        
        // Фокус на вкладке при клике на уведомление
        notification.onclick = () => {
            window.focus();
            notification.close();
        };
        
        // Автоматическое закрытие через 5 секунд
        setTimeout(() => notification.close(), 5000);
        
    } catch (error) {
        console.error('Notification error:', error);
    }
}

        // API endpoints
        const API_BASE = '/api';
        const API_AUTH = `${API_BASE}/auth.php`;
        const API_CHAT = `${API_BASE}/chat.php`;
        const API_ORDERS = `${API_BASE}/orders.php`;

        // Инициализация
        document.addEventListener('DOMContentLoaded', async () => {
    loadTheme();

    // Включаем уведомления при первом взаимодействии
    const enableNotificationsOnFirstInteraction = async () => {
        if (!notificationEnabled) {
            enableNotifications();
            
            // Запрашиваем разрешение на браузерные уведомления
            const granted = await requestNotificationPermission();
            
            if (granted) {
                console.log('Browser notifications enabled');
            } else {
                console.log('Browser notifications denied or not supported');
            }
            
            console.log('Audio notifications enabled');
        }
    };

    // Слушаем любое взаимодействие для включения уведомлений
    ['click', 'keydown', 'touchstart'].forEach(event => {
        document.addEventListener(event, enableNotificationsOnFirstInteraction, { once: true });
    });
    
    // Информация о статусе уведомлений
    if ('Notification' in window) {
        console.log('Notification permission status:', Notification.permission);
    } else {
        console.log('Browser notifications not supported');
    }
    
    // Добавляем отладочную информацию
    console.log('Chat initialized', {
        isAuthenticated,
        currentUser: currentUser ? currentUser.phone : 'none'
    });
    
    await initializeApp();
    
    // Инициализация загрузки файлов
    initializeFileUpload();
    
    // Закрытие модалок по клику вне
    document.getElementById('authModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideAuthModal();
        }
    });
    
    // Отслеживание активности пользователя
    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, () => {
            lastActivityTime = Date.now();
        }, { passive: true });
    });
    
    // Отслеживание видимости вкладки
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            console.log('Tab hidden, slowing down polling');
        } else {
            console.log('Tab visible, resuming normal polling');
            lastActivityTime = Date.now();
            // Сразу загружаем новые сообщения при возврате на вкладку
            if (isAuthenticated) {
                loadMessages();
            }
        }
    });
    
    // Очистка при закрытии страницы
    window.addEventListener('beforeunload', () => {
        stopMessagePolling();
    });
    
    // Периодическая очистка для предотвращения утечек памяти
    setInterval(() => {
        if (document.hidden) return;
        
        console.log('Memory cleanup check:', {
            messages: document.querySelectorAll('.message').length,
            displayedIds: displayedMessageIds.size,
            cacheSize: messageCache.size
        });
        
        cleanupOldMessages();
    }, 60000); // каждую минуту

    // Закрытие модалок по Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (document.getElementById('imageLightbox').classList.contains('active')) {
                closeLightbox();
            } else if (document.getElementById('fileUploadModal').classList.contains('active')) {
                closeFileUploadModal();
            }
        }
    });

    // Обработка вставки файлов из буфера обмена
    document.addEventListener('paste', (e) => {
        if (!document.getElementById('fileUploadModal').classList.contains('active')) {
            return;
        }
        
        const items = Array.from(e.clipboardData.items);
        items.forEach(item => {
            if (item.type.indexOf('image') !== -1) {
                const file = item.getAsFile();
                if (file) {
                    handleFiles([file]);
                }
            }
        });
    });
});

        async function initializeApp() {
            await loadServices();
            
            if (!isAuthenticated) {
                await checkAuthStatus();
            }
            
            if (isAuthenticated) {
                await loadMessages();
                startMessagePolling();
                updateUI();
            } else {
                addWelcomeMessage();
            }
        }

        // Тема
        function toggleTheme() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Обновляем иконки
            document.getElementById('themeIcon').className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            const mobileIcon = document.getElementById('themeIconMobile');
            if (mobileIcon) {
                mobileIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        function loadTheme() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.body.setAttribute('data-theme', savedTheme);
            document.getElementById('themeIcon').className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            const mobileIcon = document.getElementById('themeIconMobile');
            if (mobileIcon) {
                mobileIcon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        // Мобильная навигация
        function mobileNavClick(view, event) {
            event.preventDefault();
            
            // Обновляем активный элемент
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Показываем соответствующее представление
            if (view === 'chat') {
                closeMobileSidebar();
            } else {
                showMobileSidebar(view);
            }
        }

        function toggleMobileSidebar() {
    const sidebar = document.querySelector('.mobile-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar.classList.contains('active')) {
        closeMobileSidebar();
    } else {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        // Загружаем контент активной вкладки при открытии
        switchMobileSidebarTab('services');
    }
}

        function showMobileSidebar(tab) {
    const sidebar = document.querySelector('.mobile-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    sidebar.classList.add('active');
    overlay.classList.add('active');
    
    // Загружаем контент активной вкладки при открытии
    const activeTab = tab || 'services';
    switchMobileSidebarTab(activeTab);
}

        function closeMobileSidebar() {
            document.querySelector('.mobile-sidebar').classList.remove('active');
            document.querySelector('.sidebar-overlay').classList.remove('active');
        }

        // Табы
        function switchSidebarTab(tab) {
            // Обновляем табы
            document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            // Обновляем панели
            document.querySelectorAll('.sidebar-pane').forEach(p => p.style.display = 'none');
            document.getElementById(tab + 'Pane').style.display = 'block';
            
            // Загружаем данные если нужно
            if (tab === 'orders' && isAuthenticated) {
                loadOrders();
            } else if (tab === 'profile') {
                loadProfile();
            }
        }

function switchMobileSidebarTab(tab) {
    // Обновляем табы сразу
    document.querySelectorAll('.mobile-sidebar .sidebar-tab').forEach(t => {
        t.classList.remove('active');
        const tabText = t.textContent.toLowerCase().trim();
        if ((tab === 'services' && tabText === 'услуги') ||
            (tab === 'orders' && tabText === 'заказы') ||
            (tab === 'profile' && tabText === 'профиль')) {
            t.classList.add('active');
        }
    });
    
    // Загружаем данные и обновляем контент
    if (tab === 'services') {
        // Копируем контент услуг сразу (они уже загружены при инициализации)
        const desktopContent = document.getElementById('servicesPane').innerHTML;
        document.querySelector('.mobile-sidebar .sidebar-content').innerHTML = desktopContent;
    } else if (tab === 'orders') {
        // Показываем загрузчик
        document.querySelector('.mobile-sidebar .sidebar-content').innerHTML = `
            <div class="text-center" style="padding: 40px;">
                <div class="loader loader-lg"></div>
            </div>
        `;
        // Загружаем заказы с флагом для мобильной версии
        loadOrders(true);
    } else if (tab === 'profile') {
        // Показываем загрузчик
        document.querySelector('.mobile-sidebar .sidebar-content').innerHTML = `
            <div class="text-center" style="padding: 40px;">
                <div class="loader loader-lg"></div>
            </div>
        `;
        // Загружаем профиль с флагом для мобильной версии
        loadProfile(true);
    }
}

        // Сервисы
        async function loadServices() {
            try {
                const response = await fetch(`${API_ORDERS}?action=services`);
                const data = await response.json();
                
                if (data.success) {
                    services = data.services;
                    renderServices();
                }
            } catch (error) {
                console.error('Error loading services:', error);
            }
        }

        function renderServices() {
            const container = document.getElementById('servicesList');
            
            if (services.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="empty-state-title">Услуги появятся скоро</div>
                        <div class="empty-state-text">Мы работаем над каталогом</div>
                    </div>
                `;
                return;
            }

            container.innerHTML = services.map(service => `
                <div class="service-card" onclick="selectService('${service.id}', '${service.name}')">
                    <div class="service-icon">
                        <i class="fas fa-${getServiceIcon(service.category)}"></i>
                    </div>
                    <div class="service-title">${service.name}</div>
                    <div class="service-description">${service.description || 'Профессиональное выполнение'}</div>
                    <div class="service-price">от ${formatPrice(service.base_price)}</div>
                </div>
            `).join('');
        }

        function getServiceIcon(category) {
            const icons = {
                'печать': 'print',
                'дизайн': 'palette',
                'баннеры': 'flag',
                'визитки': 'id-card',
                'флаеры': 'file-alt'
            };
            return icons[category] || 'cog';
        }

        function selectService(serviceId, serviceName) {
            closeMobileSidebar();
            sendQuickMessage(`Интересует услуга: ${serviceName}`);
        }

        // Чат
        function addWelcomeMessage() {
            addMessage('Добро пожаловать! 👋\n\nЯ помогу вам с заказом полиграфической продукции. Выберите интересующую услугу или задайте вопрос!', false);
        }

        async function checkAuthStatus() {
            try {
                const response = await fetch(`${API_AUTH}?action=check`);
                const data = await response.json();
                
                if (data.authenticated) {
                    isAuthenticated = true;
                    currentUser = data.user;
                    currentChatId = data.chat_id;
                    updateUI();
                }
            } catch (error) {
                console.error('Auth check error:', error);
            }
        }

async function loadMessages() {
    if (!isAuthenticated || isLoadingMessages) return;

    isLoadingMessages = true;
    let hasNewMessages = false;
    let newMessagesCount = 0;
    let lastMessageText = '';

    try {
        const response = await fetch(`${API_CHAT}?action=messages&after_id=${lastMessageId}`);
        const data = await response.json();

        if (data.success && data.messages && Array.isArray(data.messages)) {
            currentChatId = data.chat_id;
            
            // Обрабатываем каждое сообщение
            data.messages.forEach(msg => {
                // Убеждаемся что ID - это число
                const messageId = parseInt(msg.id);
                
                // Проверяем на дубликаты по ID
                if (displayedMessageIds.has(messageId)) {
                    return;
                }
                
                // Добавляем ID в набор отображенных
                displayedMessageIds.add(messageId);
                
                const time = new Date(msg.created_at).toLocaleTimeString('ru-RU', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                
                // Проверяем, является ли это сообщением с файлом
                let messageData = null;
                if (msg.file_name || msg.file_path) {
                    messageData = {
                        file_name: msg.file_name,
                        file_path: msg.file_path,
                        file_size: msg.file_size,
                        file_type: msg.file_type
                    };
                }
                
                // Добавляем сообщение в DOM
                addMessage(
                    msg.message_text, 
                    msg.sender_type === 'user',
                    time,
                    messageId,
                    messageData
                );
                
                // Проверяем, является ли это новым сообщением от поддержки
                if (msg.sender_type !== 'user') {
                    hasNewMessages = true;
                    newMessagesCount++;
                    lastMessageText = msg.message_text || 'Новое сообщение';
                }
                
                // Обновляем lastMessageId
                lastMessageId = Math.max(lastMessageId, messageId);
            });
            
            // Показываем уведомление если есть новые сообщения от поддержки
            if (hasNewMessages) {
                if (document.hidden) {
                    // Вкладка скрыта - показываем браузерное уведомление
                    const notificationTitle = newMessagesCount === 1 
                        ? 'Новое сообщение от поддержки'
                        : `Новых сообщений: ${newMessagesCount}`;
                    
                    const notificationBody = newMessagesCount === 1 && lastMessageText
                        ? (lastMessageText.length > 100 
                            ? lastMessageText.substring(0, 100) + '...' 
                            : lastMessageText)
                        : 'У вас есть непрочитанные сообщения';
                    
                    showBrowserNotification(notificationTitle, {
                        body: notificationBody,
                        icon: '/favicon.ico'
                    });
                } else {
                    // Вкладка активна - воспроизводим звук
                    if (notificationEnabled) {
                        playNotificationSound();
                    }
                }
            }
            
            // Очистка старых сообщений из DOM если их слишком много
            cleanupOldMessages();
        }
    } catch (error) {
        console.error('Messages load error:', error);
    } finally {
        isLoadingMessages = false;
    }
}

        function startMessagePolling() {
            // Останавливаем предыдущий интервал если есть
            stopMessagePolling();
            
            // Динамический интервал - чаще при активности, реже при простое
            let pollInterval = 10000; // Начинаем с 10 секунд
            
            const poll = async () => {
                // Проверяем, что polling не был остановлен
                if (!messagePollingInterval) return;
                
                const timeSinceLastActivity = Date.now() - lastActivityTime;
                
                // Если пользователь неактивен больше 5 минут, увеличиваем интервал
                if (timeSinceLastActivity > 5 * 60 * 1000) {
                    pollInterval = 30000; // 30 секунд
                } else if (timeSinceLastActivity > 2 * 60 * 1000) {
                    pollInterval = 15000; // 15 секунд
                } else {
                    pollInterval = 10000; // 10 секунд
                }
                
                // Если вкладка не активна, замедляем опрос
                if (document.hidden) {
                    pollInterval = 60000; // 1 минута
                }
                
                // Загружаем сообщения только если авторизованы и вкладка активна
                if (isAuthenticated && !isLoadingMessages) {
                    await loadMessages();
                }
                
                // Перезапускаем таймер с новым интервалом
                if (messagePollingInterval) {
                    messagePollingInterval = setTimeout(poll, pollInterval);
                }
            };
            
            // Первый запуск через 3 секунды
            messagePollingInterval = setTimeout(poll, 3000);
            console.log('Message polling started');
        }
        
        // Остановка опроса
        function stopMessagePolling() {
            if (messagePollingInterval) {
                clearTimeout(messagePollingInterval);
                messagePollingInterval = null;
                console.log('Message polling stopped');
            }
        }

        function addMessage(text, isSent = false, time = null, messageId = null, messageData = null) {
            // Проверяем на дубликат если есть ID
            if (messageId && !messageId.toString().startsWith('temp_')) {
                // Проверяем, не отображено ли уже это сообщение
                const existingMsg = document.querySelector(`[data-message-id="${messageId}"]`);
                if (existingMsg) {
                    console.log('Message already displayed:', messageId);
                    return;
                }
            }
            
            const container = document.getElementById('chatMessages');
            const messageEl = document.createElement('div');
            messageEl.className = `message ${isSent ? 'sent' : ''}`;
            
            // Генерируем уникальный ID для временных сообщений
            const uniqueId = messageId || `temp_${Date.now()}_${Math.random()}`;
            messageEl.setAttribute('data-message-id', uniqueId);
            
            if (!time) {
                time = new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            }
            
            // Проверяем, есть ли файлы в сообщении
            let fileContent = '';
            if (messageData && messageData.file_name) {
                if (messageData.file_type && messageData.file_type.startsWith('image/')) {
                    fileContent = `
                        <div class="message-image" onclick="openLightbox('${messageData.file_path}')">
                            <img src="${messageData.file_path}" alt="${messageData.file_name}">
                        </div>
                    `;
                } else {
                    const fileType = ALLOWED_FILE_TYPES[messageData.file_type] || { icon: 'file', color: '' };
                    fileContent = `
                        <div class="message-file" onclick="downloadFile('${messageData.file_path}', '${messageData.file_name}')">
                            <div class="message-file-icon">
                                <i class="fas fa-${fileType.icon}"></i>
                            </div>
                            <div class="message-file-info">
                                <div class="message-file-name">${escapeHtml(messageData.file_name)}</div>
                                <div class="message-file-size">${formatFileSize(messageData.file_size)}</div>
                            </div>
                        </div>
                    `;
                }
            }
            
            messageEl.innerHTML = `
                <div class="message-bubble">
                    <div class="message-text">${escapeHtml(text)}</div>
                    ${fileContent}
                    <div class="message-time">${time}</div>
                </div>
            `;

            container.appendChild(messageEl);
            
            // Плавная прокрутка к последнему сообщению
            requestAnimationFrame(() => {
                container.scrollTop = container.scrollHeight;
            });
        }
        
        // Функция очистки старых сообщений
        function cleanupOldMessages() {
            const container = document.getElementById('chatMessages');
            const messages = container.querySelectorAll('.message');
            
            // Если больше 100 сообщений, удаляем старые
            if (messages.length > 100) {
                const toRemove = messages.length - 100;
                for (let i = 0; i < toRemove; i++) {
                    const msgEl = messages[i];
                    const msgId = msgEl.getAttribute('data-message-id');
                    
                    // Не удаляем временные сообщения которые еще отправляются
                    if (msgId && msgId.startsWith('temp_')) {
                        continue;
                    }
                    
                    // Удаляем из displayedMessageIds если это не временное сообщение
                    if (msgId && !msgId.startsWith('temp_')) {
                        displayedMessageIds.delete(parseInt(msgId));
                    }
                    
                    msgEl.remove();
                }
            }
            
            // Очищаем кеш если он слишком большой
            if (messageCache.size > 500) {
                // Преобразуем в массив и оставляем только последние 300 записей
                const entries = Array.from(messageCache.entries());
                const toKeep = entries.slice(-300);
                messageCache.clear();
                toKeep.forEach(([key, value]) => messageCache.set(key, value));
            }
            
            // Очищаем displayedMessageIds если слишком много
            if (displayedMessageIds.size > 500) {
                // Оставляем только последние 300 ID
                const sortedIds = Array.from(displayedMessageIds).sort((a, b) => a - b);
                const toKeep = sortedIds.slice(-300);
                displayedMessageIds.clear();
                toKeep.forEach(id => displayedMessageIds.add(id));
            }
        }

        // Защита от двойной отправки
        let isSending = false;
        let lastSentMessage = '';
        let lastSentTime = 0;
        
        async function sendMessage(e) {
            e.preventDefault();
            
            if (!isAuthenticated) {
                showNotification('error', 'Войдите в систему');
                showAuthModal();
                return;
            }

            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            const sendBtn = document.getElementById('sendBtn');

            if (!message || sendBtn.disabled || isSending) return;
            
            // Проверка на дубликат сообщения
            const now = Date.now();
            if (message === lastSentMessage && (now - lastSentTime) < 2000) {
                console.log('Duplicate message prevented');
                return;
            }

            // Блокируем отправку
            isSending = true;
            sendBtn.disabled = true;
            lastSentMessage = message;
            lastSentTime = now;
            
            // Создаем временный ID для сообщения
            const tempId = `temp_${Date.now()}_${Math.random()}`;
            
            // Добавляем сообщение в UI сразу
            addMessage(message, true, null, tempId);
            
            // Очищаем поле ввода
            input.value = '';
            autoResize(input);
            
            // Обновляем время последней активности
            lastActivityTime = Date.now();

            try {
                const response = await fetch(`${API_CHAT}?action=send`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: message,
                        chat_id: currentChatId
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    // Удаляем временное сообщение при ошибке
                    const tempMsg = document.querySelector(`[data-message-id="${tempId}"]`);
                    if (tempMsg) tempMsg.remove();
                    
                    showNotification('error', data.error || 'Ошибка отправки');
                } else {
                    // Запоминаем ID отправленного сообщения чтобы не дублировать
                    if (data.message_id) {
                        displayedMessageIds.add(parseInt(data.message_id));
                        // Обновляем lastMessageId
                        lastMessageId = Math.max(lastMessageId, parseInt(data.message_id));
                    }
                }
            } catch (error) {
                console.error('Send error:', error);
                // Удаляем временное сообщение при ошибке
                const tempMsg = document.querySelector(`[data-message-id="${tempId}"]`);
                if (tempMsg) tempMsg.remove();
                
                showNotification('error', 'Ошибка соединения');
            } finally {
                // Разблокируем отправку
                isSending = false;
                setTimeout(() => {
                    sendBtn.disabled = false;
                }, 1000);
            }
        }

        function sendQuickMessage(text) {
            const input = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendBtn');
            
            // Проверяем, что не отправляется сообщение в данный момент
            if (sendBtn.disabled || isSending) {
                console.log('Cannot send quick message - already sending');
                return;
            }
            
            input.value = text;
            autoResize(input);
            sendMessage(new Event('submit'));
        }

        function handleKeyPress(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                // Добавляем проверку, что не отправляется в данный момент
                if (!isSending && !document.getElementById('sendBtn').disabled) {
                    sendMessage(e);
                }
            }
        }

        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        // Заказы
        async function loadOrders(updateMobile = false) {
    const container = updateMobile ? 
        document.querySelector('.mobile-sidebar .sidebar-content') : 
        document.getElementById('ordersList');
    
    if (!isAuthenticated) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="empty-state-title">Требуется авторизация</div>
                <button class="btn btn-primary mt-3" onclick="showAuthModal()">Войти</button>
            </div>
        `;
        return;
    }

    // Показываем загрузчик только для десктопа (для мобильного уже показан)
    if (!updateMobile) {
        container.innerHTML = `
            <div class="text-center">
                <div class="loader loader-lg"></div>
            </div>
        `;
    }

    try {
        const response = await fetch(`${API_ORDERS}?action=list`);
        const data = await response.json();

        const ordersHTML = data.success && data.orders.length > 0 ? 
            renderOrdersHTML(data.orders) : 
            renderEmptyOrdersHTML();
        
        // Обновляем оба контейнера
        document.getElementById('ordersList').innerHTML = ordersHTML;
        if (updateMobile || document.querySelector('.mobile-sidebar').classList.contains('active')) {
            const mobileContent = document.querySelector('.mobile-sidebar .sidebar-content');
            if (mobileContent) {
                mobileContent.innerHTML = ordersHTML;
            }
        }
    } catch (error) {
        console.error('Orders error:', error);
        const errorHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="empty-state-title">Ошибка загрузки</div>
            </div>
        `;
        container.innerHTML = errorHTML;
    }
}

// Вспомогательные функции для генерации HTML
function renderOrdersHTML(orders) {
    return orders.map(order => {
        // Определяем цвет статуса заказа
        const statusColors = {
            'draft': { bg: '#e5e7eb', color: '#374151', text: 'Черновик' },
            'pending': { bg: '#fef3c7', color: '#92400e', text: 'Ожидает' },
            'confirmed': { bg: '#dbeafe', color: '#1e40af', text: 'Подтвержден' },
            'in_production': { bg: '#e0e7ff', color: '#3730a3', text: 'В работе' },
            'ready': { bg: '#d1fae5', color: '#065f46', text: 'Готов' },
            'delivered': { bg: '#34d399', color: 'white', text: 'Доставлен' },
            'cancelled': { bg: '#fee2e2', color: '#991b1b', text: 'Отменен' }
        };
        
        // Определяем цвет статуса оплаты
        const paymentColors = {
            'pending': { bg: '#fef3c7', color: '#92400e', text: 'Не оплачен', icon: 'clock' },
            'paid': { bg: '#d1fae5', color: '#065f46', text: 'Оплачен', icon: 'check-circle' },
            'partially_paid': { bg: '#e0e7ff', color: '#3730a3', text: 'Частично', icon: 'exclamation-circle' },
            'refunded': { bg: '#fee2e2', color: '#991b1b', text: 'Возврат', icon: 'undo' }
        };
        
        const orderStatus = statusColors[order.status] || statusColors['pending'];
        const paymentStatus = paymentColors[order.payment_status] || paymentColors['pending'];
        
        return `
            <div class="service-card order-card" onclick="openOrder(${order.id})">
                <div class="service-icon" style="background: rgba(236, 72, 153, 0.1); color: var(--secondary);">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="order-card-content">
                    <div class="service-title">Заказ #${order.order_number}</div>
                    <div class="service-description">
                        ${order.items_count} позиций • ${formatDate(order.created_at)}
                    </div>
                    <div class="order-statuses">
                        <span class="order-status-badge" style="background: ${orderStatus.bg}; color: ${orderStatus.color};">
                            ${orderStatus.text}
                        </span>
                        <span class="payment-status-badge" style="background: ${paymentStatus.bg}; color: ${paymentStatus.color};">
                            <i class="fas fa-${paymentStatus.icon}"></i>
                            ${paymentStatus.text}
                        </span>
                    </div>
                    <div class="service-price">${formatPrice(order.final_amount)}</div>
                </div>
            </div>
        `;
    }).join('');
}

function renderEmptyOrdersHTML() {
    return `
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="empty-state-title">Нет заказов</div>
            <div class="empty-state-text">Выберите услугу для оформления заказа</div>
        </div>
    `;
}

        function renderOrders(orders) {
            document.getElementById('ordersList').innerHTML = orders.map(order => `
                <div class="service-card" onclick="openOrder(${order.id})">
                    <div class="service-icon" style="background: rgba(236, 72, 153, 0.1); color: var(--secondary);">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="service-title">Заказ #${order.order_number}</div>
                    <div class="service-description">
                        ${order.items_count} позиций • ${formatDate(order.created_at)}
                    </div>
                    <div class="service-price">${formatPrice(order.final_amount)}</div>
                </div>
            `).join('');
        }

        function renderEmptyOrders() {
            document.getElementById('ordersList').innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="empty-state-title">Нет заказов</div>
                    <div class="empty-state-text">Выберите услугу для оформления заказа</div>
                </div>
            `;
        }

        function openOrder(orderId) {
            closeMobileSidebar();
            sendQuickMessage(`Хочу узнать статус заказа #${orderId}`);
        }

        // Обновленная функция loadProfile для авторизации через Email
function loadProfile(updateMobile = false) {
    const container = updateMobile ? 
        document.querySelector('.mobile-sidebar .sidebar-content') : 
        document.getElementById('profileContent');
    
    if (!isAuthenticated) {
        const profileHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="empty-state-title">Личный кабинет</div>
                <button class="btn btn-primary mt-3" onclick="showAuthModal()">Войти</button>
            </div>
        `;
        
        document.getElementById('profileContent').innerHTML = profileHTML;
        if (updateMobile || document.querySelector('.mobile-sidebar').classList.contains('active')) {
            const mobileContent = document.querySelector('.mobile-sidebar .sidebar-content');
            if (mobileContent) {
                mobileContent.innerHTML = profileHTML;
            }
        }
        return;
    }

    const profileHTML = `
        <form onsubmit="updateProfile(event)">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="${currentUser.email || ''}" disabled style="opacity: 0.6;">
                <small class="form-text text-muted">Основной способ входа в систему</small>
            </div>
            <div class="form-group">
                <label class="form-label">Телефон <span class="text-muted">(опционально)</span></label>
                <input type="tel" class="form-control" id="profilePhone${updateMobile ? 'Mobile' : ''}" 
                       value="${currentUser.phone || ''}" placeholder="+7XXXXXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label">Имя</label>
                <input type="text" class="form-control" id="profileName${updateMobile ? 'Mobile' : ''}" 
                       value="${currentUser.name || ''}" placeholder="Ваше имя">
            </div>
            <div class="form-group">
                <label class="form-label">Компания <span class="text-muted">(опционально)</span></label>
                <input type="text" class="form-control" id="profileCompany${updateMobile ? 'Mobile' : ''}" 
                       value="${currentUser.company || ''}" placeholder="Название компании">
            </div>
            <div class="form-group">
                <label class="form-label">ИНН <span class="text-muted">(опционально)</span></label>
                <input type="text" class="form-control" id="profileInn${updateMobile ? 'Mobile' : ''}" 
                       value="${currentUser.inn || ''}" placeholder="ИНН компании" maxlength="12">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Сохранить</button>
        </form>
        <button class="btn btn-secondary btn-block mt-3" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Выйти
        </button>
    `;
    
    // Обновляем оба контейнера
    document.getElementById('profileContent').innerHTML = profileHTML;
    if (updateMobile || document.querySelector('.mobile-sidebar').classList.contains('active')) {
        const mobileContent = document.querySelector('.mobile-sidebar .sidebar-content');
        if (mobileContent) {
            mobileContent.innerHTML = profileHTML;
        }
    }
}

// Обновленная функция updateProfile
async function updateProfile(e) {
    e.preventDefault();
    
    // Определяем, откуда вызвана функция
    const isMobile = document.querySelector('.mobile-sidebar').classList.contains('active');
    const suffix = isMobile ? 'Mobile' : '';
    
    // Собираем данные из формы
    const name = document.getElementById('profileName' + suffix).value.trim();
    const phone = document.getElementById('profilePhone' + suffix).value.trim();
    const company = document.getElementById('profileCompany' + suffix).value.trim();
    const inn = document.getElementById('profileInn' + suffix).value.trim();
    
    // Формируем объект только с заполненными полями
    const updateData = {};
    if (name) updateData.name = name;
    if (phone) updateData.phone = phone;
    if (company) updateData.company = company;
    if (inn) updateData.inn = inn;
    
    // Проверяем, есть ли данные для отправки
    if (Object.keys(updateData).length === 0) {
        showNotification('warning', 'Нет данных для обновления');
        return;
    }
    
    try {
        const response = await fetch(`${API_AUTH}?action=update-profile`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updateData)
        });

        const data = await response.json();

        if (data.success) {
            // Обновляем локальные данные пользователя
            if (name) currentUser.name = name;
            if (phone) currentUser.phone = phone;
            if (company) currentUser.company = company;
            if (inn) currentUser.inn = inn;
            
            showNotification('success', 'Профиль успешно обновлен');
            
            // Обновляем UI
            updateUI();
            
            // Обновляем профиль в обеих версиях (desktop и mobile)
            loadProfile(false);
            loadProfile(true);
        } else {
            showNotification('error', data.error || 'Ошибка обновления профиля');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        showNotification('error', 'Ошибка соединения с сервером');
    }
}

// Дополнительная функция для форматирования телефона (опционально)
function formatPhoneInput(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 0) {
        if (value[0] === '8') {
            value = '7' + value.slice(1);
        }
        if (value[0] !== '7') {
            value = '7' + value;
        }
        
        let formatted = '+7';
        if (value.length > 1) {
            formatted += ' (' + value.slice(1, 4);
        }
        if (value.length >= 5) {
            formatted += ') ' + value.slice(4, 7);
        }
        if (value.length >= 8) {
            formatted += '-' + value.slice(7, 9);
        }
        if (value.length >= 10) {
            formatted += '-' + value.slice(9, 11);
        }
        
        input.value = formatted.slice(0, 18);
    }
}

        // Авторизация
        function showAuthModal() {
    const modal = document.getElementById('authModal');
    
    // Изменяем содержимое модального окна
    modal.innerHTML = `
        <div class="modal-overlay" onclick="hideAuthModal()"></div>
        <div class="modal-content">
            <button class="modal-close" onclick="hideAuthModal()">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Шаг 1: Ввод Email -->
            <div id="emailStep">
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h2>Вход в систему</h2>
                    <p>Введите ваш email для получения кода</p>
                </div>
                
                <form onsubmit="sendCode(event)">
                    <div class="form-group">
                        <label class="form-label">Email адрес</label>
                        <input 
                            type="email" 
                            id="emailInput" 
                            class="form-control" 
                            placeholder="example@mail.com"
                            autocomplete="email"
                            required
                        >
                        <small class="form-text text-muted">
                            Мы отправим код подтверждения на этот адрес
                        </small>
                    </div>
                    
                    <div id="emailError" class="error-message"></div>
                    
                    <button type="submit" id="sendCodeBtn" class="btn btn-primary btn-block">
                        Получить код
                    </button>
                </form>
            </div>
            
            <!-- Шаг 2: Ввод кода (без изменений) -->
            <div id="codeStep" style="display: none;">
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2>Введите код</h2>
                    <p>Код отправлен на <strong id="maskedEmail"></strong></p>
                    <p>Проверьте СПАМ или <a href="">напишите нам</a></p>
                </div>
                
                <form onsubmit="verifyCode(event)">
                    <div class="code-inputs">
                        <input type="text" maxlength="1" class="code-input" oninput="moveToNext(this, 0)" onkeydown="handleBackspace(event, 0)">
                        <input type="text" maxlength="1" class="code-input" oninput="moveToNext(this, 1)" onkeydown="handleBackspace(event, 1)">
                        <input type="text" maxlength="1" class="code-input" oninput="moveToNext(this, 2)" onkeydown="handleBackspace(event, 2)">
                        <input type="text" maxlength="1" class="code-input" oninput="moveToNext(this, 3)" onkeydown="handleBackspace(event, 3)">
                        <input type="text" maxlength="1" class="code-input" oninput="moveToNext(this, 4)" onkeydown="handleBackspace(event, 4)">
                        <input type="text" maxlength="1" class="code-input" oninput="moveToNext(this, 5)" onkeydown="handleBackspace(event, 5)">
                    </div>
                    
                    <div id="codeError" class="error-message"></div>
                    
                    <button type="submit" id="verifyBtn" class="btn btn-primary btn-block">
                        Подтвердить
                    </button>
                    
                    <button type="button" class="btn btn-link btn-block" onclick="resendCode()">
                        Отправить код повторно
                    </button>
                </form>
            </div>
        </div>
    `;
    
    modal.classList.add('active');
    
    // Фокус на поле email
    setTimeout(() => {
        const emailInput = document.getElementById('emailInput');
        if (emailInput) emailInput.focus();
    }, 100);
}

        function hideAuthModal() {
            document.getElementById('authModal').classList.remove('active');
        }

        async function sendCode(e) {
    e.preventDefault();
    
    const emailInput = document.getElementById('emailInput');
    const email = emailInput.value.trim();
    const btn = document.getElementById('sendCodeBtn');
    const errorDiv = document.getElementById('emailError');
    
    // Очищаем ошибки
    errorDiv.textContent = '';
    emailInput.classList.remove('is-invalid');
    
    // Валидация email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errorDiv.textContent = 'Введите корректный email адрес';
        emailInput.classList.add('is-invalid');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="loader"></span> Отправка...';
    
    try {
        const response = await fetch(`${API_AUTH}?action=send-code`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Показываем шаг ввода кода
            document.getElementById('emailStep').style.display = 'none';
            document.getElementById('codeStep').style.display = 'block';
            document.getElementById('maskedEmail').textContent = email;
            
            // Фокус на первое поле кода
            const firstInput = document.querySelector('.code-input');
            if (firstInput) firstInput.focus();
            
            showNotification('success', data.message || 'Код отправлен на email');
        } else {
            errorDiv.textContent = data.error || 'Ошибка отправки кода';
            emailInput.classList.add('is-invalid');
        }
    } catch (error) {
        console.error('Send code error:', error);
        errorDiv.textContent = 'Ошибка соединения с сервером';
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Получить код';
    }
}

function handleBackspace(event, index) {
    const inputs = document.querySelectorAll('.code-input');
    
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        inputs[index - 1].focus();
    }
}

        async function verifyCode(e) {
    e.preventDefault();
    
    const inputs = document.querySelectorAll('#codeStep input');
    const code = Array.from(inputs).map(i => i.value).join('');
    const btn = document.getElementById('verifyBtn');
    const errorDiv = document.getElementById('codeError');

    // Очищаем ошибки
    errorDiv.textContent = '';

    if (code.length !== 6) {
        errorDiv.textContent = 'Введите полный код';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="loader"></span> Проверяем...';

    try {
        const response = await fetch(`${API_AUTH}?action=verify-code`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: code })
        });

        const data = await response.json();

        if (data.success) {
            // Показываем уведомление об успешной авторизации
            showNotification('success', 'Вход выполнен успешно!');
            
            // Сохраняем флаг для показа уведомления после перезагрузки
            sessionStorage.setItem('justLoggedIn', 'true');
            sessionStorage.setItem('userName', data.user.name || data.user.email || 'Пользователь');
            
            // Даем время на показ уведомления, затем перезагружаем страницу
            setTimeout(() => {
                window.location.reload();
            }, 800);
            
        } else {
            errorDiv.textContent = data.error || 'Неверный код';
            inputs.forEach(i => i.value = '');
            inputs[0].focus();
            btn.disabled = false;
            btn.innerHTML = 'Подтвердить';
        }
    } catch (error) {
        console.error('Verify error:', error);
        errorDiv.textContent = 'Ошибка соединения с сервером';
        btn.disabled = false;
        btn.innerHTML = 'Подтвердить';
    }
}

// Добавьте это в блок инициализации при загрузке страницы (в DOMContentLoaded или в конец скрипта)

// Проверка после перезагрузки - показываем приветствие
if (sessionStorage.getItem('justLoggedIn') === 'true') {
    const userName = sessionStorage.getItem('userName') || 'Пользователь';
    sessionStorage.removeItem('justLoggedIn');
    sessionStorage.removeItem('userName');
    
    // Показываем приветствие
    setTimeout(() => {
        showNotification('success', `Добро пожаловать, ${userName}!`);
    }, 500);
    
    // Опционально: автоматически открываем чат через секунду
    setTimeout(() => {
        if (window.innerWidth >= 768) {
            showSection('chat');
        }
    }, 1500);
}

// Также убедитесь, что функция updateUI() существует и обновляет все элементы:

function updateUI() {
    // Обновляем состояние кнопки входа/профиля
    const authBtn = document.querySelector('.auth-btn');
    if (authBtn) {
        if (isAuthenticated) {
            authBtn.innerHTML = '<i class="fas fa-user"></i> <span>Профиль</span>';
            authBtn.onclick = () => showSection('profile');
        } else {
            authBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> <span>Войти</span>';
            authBtn.onclick = () => showAuthModal();
        }
    }
    
    // Обновляем имя пользователя в шапке (если есть)
    const userNameEl = document.querySelector('.user-name');
    if (userNameEl && isAuthenticated && currentUser) {
        userNameEl.textContent = currentUser.name || currentUser.email || 'Пользователь';
    }
    
    // Обновляем состояние секций
    if (isAuthenticated) {
        // Показываем секции для авторизованных
        document.querySelectorAll('.auth-required').forEach(el => {
            el.style.display = 'block';
        });
        
        // Скрываем секции для неавторизованных
        document.querySelectorAll('.guest-only').forEach(el => {
            el.style.display = 'none';
        });
    } else {
        // Обратная логика
        document.querySelectorAll('.auth-required').forEach(el => {
            el.style.display = 'none';
        });
        
        document.querySelectorAll('.guest-only').forEach(el => {
            el.style.display = 'block';
        });
    }
    
    // Обновляем чат интерфейс
    updateChatUI();
}

// Функция обновления чат интерфейса
function updateChatUI() {
    const chatMessages = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    
    if (isAuthenticated && currentChatId) {
        // Активируем чат
        if (messageForm) {
            messageForm.style.display = 'flex';
        }
        if (chatMessages && chatMessages.innerHTML.trim() === '') {
            chatMessages.innerHTML = '<div class="empty-state">Начните диалог с менеджером</div>';
        }
    } else {
        // Деактивируем чат
        if (messageForm) {
            messageForm.style.display = 'none';
        }
        if (chatMessages) {
            chatMessages.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-lock"></i>
                    <p>Войдите, чтобы начать диалог</p>
                    <button class="btn btn-primary" onclick="showAuthModal()">Войти</button>
                </div>
            `;
        }
    }
}



        function moveToNext(input, index) {
            const inputs = document.querySelectorAll('#codeStep input');
            if (input.value.length === 1 && index < 5) {
                inputs[index + 1].focus();
            }
        }

        function resendCode() {
    document.getElementById('codeStep').style.display = 'none';
    document.getElementById('emailStep').style.display = 'block';
    
    // Очищаем поля кода
    document.querySelectorAll('.code-input').forEach(input => {
        input.value = '';
    });
}

        // Замените функцию logout() на эту версию:

async function logout() {
    if (!confirm('Выйти из системы?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_AUTH}?action=logout`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Показываем уведомление
            showNotification('success', 'Вы вышли из системы');
            
            // Сохраняем флаг для показа уведомления после перезагрузки
            sessionStorage.setItem('justLoggedOut', 'true');
            
            // Перезагружаем страницу через небольшую задержку
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            showNotification('error', data.error || 'Ошибка выхода');
        }
    } catch (error) {
        console.error('Logout error:', error);
        showNotification('error', 'Ошибка соединения');
    }
}

// Добавьте проверку после загрузки страницы (вместе с проверкой justLoggedIn):

// Проверка после перезагрузки
if (sessionStorage.getItem('justLoggedIn') === 'true') {
    const userName = sessionStorage.getItem('userName') || 'Пользователь';
    sessionStorage.removeItem('justLoggedIn');
    sessionStorage.removeItem('userName');
    
    setTimeout(() => {
        showNotification('success', `Добро пожаловать, ${userName}!`);
    }, 500);
    
    setTimeout(() => {
        if (window.innerWidth >= 768) {
            showSection('chat');
        }
    }, 1500);
}

// Проверка выхода из системы
if (sessionStorage.getItem('justLoggedOut') === 'true') {
    sessionStorage.removeItem('justLoggedOut');
    
    setTimeout(() => {
        showNotification('info', 'До свидания!');
    }, 500);
}

        // Утилиты
        function updateUI() {
            loadProfile();
            
            const statusBadge = document.querySelector('.status-badge');
            if (statusBadge) {
                if (isAuthenticated && currentUser) {
                    statusBadge.style.display = 'inline-flex';
                    statusBadge.innerHTML = `
                        <i class="fas fa-check-circle"></i>
                        ${escapeHtml(currentUser.name || 'Клиент')}
                    `;
                } else {
                    statusBadge.style.display = 'none';
                }
            }
        }

        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} notification-icon"></i>
                <div>
                    <div class="font-bold">${message}</div>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0
            }).format(price);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU');
        }

        // Обновленная функция прикрепления файлов
        function attachFile() {
            if (!isAuthenticated) {
                showNotification('error', 'Войдите в систему');
                showAuthModal();
                return;
            }
            
            // Устанавливаем текущий контекст (чат)
            window.currentChatId = currentChatId;
            currentOrderId = null;
            
            openFileUploadModal();
        }

        // Функция для прикрепления файлов к заказу
        function attachOrderFile(orderId) {
            if (!isAuthenticated) {
                showNotification('error', 'Войдите в систему');
                showAuthModal();
                return;
            }
            
            // Устанавливаем текущий контекст (заказ)
            currentOrderId = orderId;
            window.currentChatId = null;
            
            openFileUploadModal();
        }

        // Обработка изменения размера окна
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                // Закрываем мобильное меню при переходе на десктоп
                if (window.innerWidth >= 768) {
                    closeMobileSidebar();
                }
            }, 250);
        });

        // Предотвращение закрытия клавиатуры на iOS
        document.addEventListener('touchstart', (e) => {
            if (e.target.classList.contains('chat-input')) {
                e.preventDefault();
                e.target.focus();
            }
        });

        // Функции для работы с файлами
        
        // Инициализация drag and drop
        function initializeFileUpload() {
            const dropzone = document.getElementById('fileDropzone');
            const fileInput = document.getElementById('fileInput');
            
            // Обработчики drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });
            
            dropzone.addEventListener('drop', handleDrop, false);
            
            // Обработчик выбора файлов через input
            fileInput.addEventListener('change', handleFileSelect);
        }
        
        // Предотвращение стандартного поведения
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Подсветка зоны при наведении
        function highlight(e) {
            document.getElementById('fileDropzone').classList.add('dragover');
        }
        
        function unhighlight(e) {
            document.getElementById('fileDropzone').classList.remove('dragover');
        }
        
        // Обработка drop файлов
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }
        
        // Обработка выбора файлов через input
        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }
        
        // Обработка файлов
        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (validateFile(file)) {
                    addFileToQueue(file);
                }
            });
            updateFileList();
            updateUploadButton();
        }
        
        // Валидация файла
        function validateFile(file) {
            // Проверка размера
            if (file.size > MAX_FILE_SIZE) {
                showNotification('error', `Файл "${file.name}" слишком большой. Максимальный размер: 10 МБ`);
                return false;
            }
            
            // Проверка типа
            const fileType = file.type || getMimeTypeByExtension(file.name);
            if (!ALLOWED_FILE_TYPES[fileType]) {
                showNotification('error', `Файл "${file.name}" имеет неподдерживаемый формат`);
                return false;
            }
            
            // Проверка на дубликаты
            if (selectedFiles.has(file.name)) {
                showNotification('warning', `Файл "${file.name}" уже добавлен`);
                return false;
            }
            
            return true;
        }
        
        // Определение MIME типа по расширению
        function getMimeTypeByExtension(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const mimeMap = {
                'jpg': 'image/jpeg',
                'jpeg': 'image/jpeg',
                'png': 'image/png',
                'gif': 'image/gif',
                'webp': 'image/webp',
                'pdf': 'application/pdf',
                'doc': 'application/msword',
                'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls': 'application/vnd.ms-excel',
                'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'zip': 'application/zip',
                'rar': 'application/x-rar-compressed'
            };
            return mimeMap[ext] || 'application/octet-stream';
        }
        
        // Добавление файла в очередь
        function addFileToQueue(file) {
            const fileId = generateFileId();
            const fileInfo = {
                id: fileId,
                file: file,
                name: file.name,
                size: file.size,
                type: file.type || getMimeTypeByExtension(file.name),
                status: 'pending',
                progress: 0,
                preview: null
            };
            
            // Создаем превью для изображений
            if (file.type.startsWith('image/')) {
                createImagePreview(file, (preview) => {
                    fileInfo.preview = preview;
                    updateFileList();
                });
            }
            
            selectedFiles.set(fileId, fileInfo);
        }
        
        // Генерация уникального ID для файла
        function generateFileId() {
            return `file_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        }
        
        // Создание превью изображения
        function createImagePreview(file, callback) {
            const reader = new FileReader();
            reader.onload = (e) => {
                callback(e.target.result);
            };
            reader.readAsDataURL(file);
        }
        
        // Обновление списка файлов
        function updateFileList() {
            const fileList = document.getElementById('filePreviewList');
            const fileCount = selectedFiles.size;
            
            if (fileCount === 0) {
                fileList.innerHTML = '';
                document.getElementById('fileCountBadge').style.display = 'none';
                return;
            }
            
            document.getElementById('fileCountBadge').textContent = fileCount;
            document.getElementById('fileCountBadge').style.display = 'inline-block';
            
            fileList.innerHTML = Array.from(selectedFiles.values()).map(fileInfo => {
                const fileType = ALLOWED_FILE_TYPES[fileInfo.type] || { icon: 'file', color: '' };
                
                return `
                    <div class="file-preview-item ${fileInfo.status}" data-file-id="${fileInfo.id}">
                        <div class="file-preview-thumbnail">
                            ${fileInfo.preview ? 
                                `<img src="${fileInfo.preview}" alt="${fileInfo.name}">` :
                                `<i class="fas fa-${fileType.icon} file-preview-icon ${fileType.color}"></i>`
                            }
                        </div>
                        <div class="file-preview-info">
                            <div class="file-preview-name">${escapeHtml(fileInfo.name)}</div>
                            <div class="file-preview-details">
                                <span>${formatFileSize(fileInfo.size)}</span>
                                ${fileInfo.status === 'uploading' ? 
                                    `<span>${fileInfo.progress}%</span>` :
                                    fileInfo.status === 'success' ?
                                    `<span style="color: var(--success)"><i class="fas fa-check"></i> Загружен</span>` :
                                    fileInfo.status === 'error' ?
                                    `<span style="color: var(--danger)"><i class="fas fa-times"></i> Ошибка</span>` :
                                    ''
                                }
                            </div>
                        </div>
                        <button class="file-remove-btn" onclick="removeFile('${fileInfo.id}')" 
                                ${fileInfo.status === 'uploading' ? 'disabled' : ''}>
                            <i class="fas fa-times"></i>
                        </button>
                        ${fileInfo.status === 'uploading' ? `
                            <div class="file-upload-progress">
                                <div class="file-upload-progress-bar" style="width: ${fileInfo.progress}%"></div>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }
        
        // Удаление файла из очереди
        function removeFile(fileId) {
            selectedFiles.delete(fileId);
            updateFileList();
            updateUploadButton();
        }
        
        // Обновление кнопки загрузки
        function updateUploadButton() {
            const uploadBtn = document.getElementById('uploadFilesBtn');
            const fileCount = selectedFiles.size;
            const uploadFileCount = document.getElementById('uploadFileCount');
            
            if (fileCount > 0) {
                uploadBtn.disabled = false;
                uploadFileCount.textContent = `(${fileCount})`;
            } else {
                uploadBtn.disabled = true;
                uploadFileCount.textContent = '';
            }
        }
        
        // Загрузка файлов
        async function uploadFiles() {
            if (isUploading || selectedFiles.size === 0) return;
            
            isUploading = true;
            const uploadBtn = document.getElementById('uploadFilesBtn');
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
            
            // Загружаем файлы последовательно
            for (const [fileId, fileInfo] of selectedFiles) {
                if (fileInfo.status === 'success') continue;
                
                await uploadSingleFile(fileId, fileInfo);
            }
            
            isUploading = false;
            
            // Проверяем, все ли файлы загружены успешно
            const allSuccess = Array.from(selectedFiles.values()).every(f => f.status === 'success');
            
            if (allSuccess) {
                showNotification('success', 'Все файлы успешно загружены');
                setTimeout(() => {
                    closeFileUploadModal();
                    selectedFiles.clear();
                }, 1500);
            } else {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Повторить загрузку';
                updateUploadButton();
            }
        }
        
        // Загрузка одного файла
        async function uploadSingleFile(fileId, fileInfo) {
    try {
        // Обновляем статус
        fileInfo.status = 'uploading';
        fileInfo.progress = 0;
        updateFileList();
        
        const formData = new FormData();
        formData.append('file', fileInfo.file);
        
        // Добавляем chat_id или order_id в зависимости от контекста
        if (window.currentChatId) {
            formData.append('chat_id', window.currentChatId);
        } else if (currentOrderId) {
            formData.append('order_id', currentOrderId);
        }
        
        // Определяем URL в зависимости от контекста
        const uploadUrl = currentOrderId ? 
            `${API_ORDERS}?action=upload` : 
            `${API_CHAT}?action=upload`;
        
        const xhr = new XMLHttpRequest();
        
        // Отслеживание прогресса
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const progress = Math.round((e.loaded / e.total) * 100);
                fileInfo.progress = progress;
                updateFileProgress(fileId, progress);
            }
        });
        
        // Промис для загрузки
        const uploadPromise = new Promise((resolve, reject) => {
            xhr.onload = () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            fileInfo.status = 'success';
                            fileInfo.uploadedData = response;
                            updateFileList();
                            
                            // ВАЖНО: НЕ добавляем сообщение здесь
                            // Сообщение будет получено через loadMessages()
                            
                            resolve(response);
                        } else {
                            throw new Error(response.error || 'Ошибка загрузки');
                        }
                    } catch (error) {
                        fileInfo.status = 'error';
                        updateFileList();
                        reject(error);
                    }
                } else {
                    fileInfo.status = 'error';
                    updateFileList();
                    reject(new Error(`HTTP Error: ${xhr.status}`));
                }
            };
            
            xhr.onerror = () => {
                fileInfo.status = 'error';
                updateFileList();
                reject(new Error('Ошибка сети'));
            };
        });
        
        xhr.open('POST', uploadUrl);
        xhr.send(formData);
        
        await uploadPromise;
        
    } catch (error) {
        console.error('Upload error:', error);
        showNotification('error', `Ошибка загрузки файла "${fileInfo.name}"`);
    }
}
        
        // Обновление прогресса загрузки
        function updateFileProgress(fileId, progress) {
            const fileItem = document.querySelector(`[data-file-id="${fileId}"]`);
            if (fileItem) {
                const progressBar = fileItem.querySelector('.file-upload-progress-bar');
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                }
                
                const progressText = fileItem.querySelector('.file-preview-details span:last-child');
                if (progressText) {
                    progressText.textContent = `${progress}%`;
                }
            }
        }
        
        // Добавление сообщения с файлом в чат
        function addFileMessageToChat(fileInfo) {
            const container = document.getElementById('chatMessages');
            const messageEl = document.createElement('div');
            messageEl.className = 'message sent';
            
            const time = new Date().toLocaleTimeString('ru-RU', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            let fileContent = '';
            
            // Если это изображение, показываем превью
            if (fileInfo.type.startsWith('image/')) {
                fileContent = `
                    <div class="message-image" onclick="openLightbox('${fileInfo.uploadedData.file_path}')">
                        <img src="${fileInfo.uploadedData.file_path}" alt="${fileInfo.name}">
                    </div>
                `;
            } else {
                // Для других файлов показываем карточку
                const fileType = ALLOWED_FILE_TYPES[fileInfo.type] || { icon: 'file', color: '' };
                fileContent = `
                    <div class="message-file" onclick="downloadFile('${fileInfo.uploadedData.file_path}', '${fileInfo.name}')">
                        <div class="message-file-icon">
                            <i class="fas fa-${fileType.icon}"></i>
                        </div>
                        <div class="message-file-info">
                            <div class="message-file-name">${escapeHtml(fileInfo.name)}</div>
                            <div class="message-file-size">${formatFileSize(fileInfo.size)}</div>
                        </div>
                    </div>
                `;
            }
            
            messageEl.innerHTML = `
                <div class="message-bubble">
                    <div class="message-text">📎 Файл отправлен</div>
                    ${fileContent}
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            container.appendChild(messageEl);
            container.scrollTop = container.scrollHeight;
        }
        
        // Открытие модального окна загрузки
        function openFileUploadModal() {
            document.getElementById('fileUploadModal').classList.add('active');
            selectedFiles.clear();
            updateFileList();
            updateUploadButton();
        }
        
        // Закрытие модального окна
        function closeFileUploadModal() {
            document.getElementById('fileUploadModal').classList.remove('active');
            // Очищаем input для возможности повторного выбора тех же файлов
            document.getElementById('fileInput').value = '';
        }
        
        // Триггер выбора файлов
        function triggerFileInput() {
            document.getElementById('fileInput').click();
        }
        
        // Открытие лайтбокса для изображений
        function openLightbox(imageSrc) {
            document.getElementById('lightboxImage').src = imageSrc;
            document.getElementById('imageLightbox').classList.add('active');
        }
        
        // Закрытие лайтбокса
        function closeLightbox(event) {
            if (!event || event.target.id === 'imageLightbox' || event.target.classList.contains('image-lightbox-close')) {
                document.getElementById('imageLightbox').classList.remove('active');
                document.getElementById('lightboxImage').src = '';
            }
        }
        
        // Скачивание файла
        function downloadFile(fileUrl, fileName) {
            const link = document.createElement('a');
            link.href = fileUrl;
            link.download = fileName;
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Форматирование размера файла
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Байт';
            
            const k = 1024;
            const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        // Функция воспроизведения звука уведомления
function playNotificationSound() {
    // Проверяем, прошло ли достаточно времени с последнего уведомления (минимум 2 секунды)
    const now = Date.now();
    if (now - lastNotificationTime < 2000) {
        return;
    }
    
    try {
        const audio = document.getElementById('notificationSound');
        if (audio && notificationEnabled) {
            audio.currentTime = 0;
            audio.volume = 0.5;
            audio.play().catch(err => {
                console.log('Cannot play notification sound:', err);
            });
            lastNotificationTime = now;
        }
    } catch (error) {
        console.error('Notification sound error:', error);
    }
}

// Функция включения уведомлений (нужно взаимодействие пользователя)
function enableNotifications() {
    notificationEnabled = true;
    const audio = document.getElementById('notificationSound');
    if (audio) {
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
        }).catch(err => {
            console.log('Audio initialization error:', err);
        });
    }
}
    </script>
</body>
</html>