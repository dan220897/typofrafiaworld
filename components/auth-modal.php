<!-- Login Modal -->
<div id="authModal" class="auth-modal">
    <div class="auth-modal-content">
        <div class="auth-modal-header">
            <h3>Вход в личный кабинет</h3>
            <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>
        </div>
        <div class="auth-modal-body">
            <p class="auth-description">Введите email для получения кода подтверждения</p>
            <form id="authForm" onsubmit="handleAuth(event)">
                <div class="form-group">
                    <label for="authEmail">Email</label>
                    <input
                        type="email"
                        id="authEmail"
                        name="email"
                        class="form-input"
                        placeholder="example@mail.com"
                        required
                    >
                </div>
                <div class="form-group" id="codeGroup" style="display: none;">
                    <label for="authCode">Код подтверждения</label>
                    <input
                        type="text"
                        id="authCode"
                        name="code"
                        class="form-input"
                        placeholder="Введите код из письма"
                    >
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="authSubmitBtn">
                    Получить код
                </button>
            </form>
            <div id="authMessage" class="auth-message"></div>
        </div>
    </div>
</div>

<style>
    /* Auth Modal */
    .auth-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }

    .auth-modal.active {
        display: flex;
    }

    .auth-modal-content {
        background: var(--white, #fff);
        border-radius: 20px;
        width: 90%;
        max-width: 450px;
        box-shadow: var(--shadow-xl, 0 25px 50px -12px rgba(0, 0, 0, 0.25));
        animation: fadeInUp 0.4s ease;
    }

    .auth-modal-header {
        padding: 2rem 2rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--light-gray, #e5e7eb);
    }

    .auth-modal-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark, #1f2937);
    }

    .auth-modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        color: var(--gray, #6b7280);
        cursor: pointer;
        transition: all 0.3s ease;
        line-height: 1;
        padding: 0;
        width: 30px;
        height: 30px;
    }

    .auth-modal-close:hover {
        color: var(--dark, #1f2937);
        transform: rotate(90deg);
    }

    .auth-modal-body {
        padding: 2rem;
    }

    .auth-description {
        color: var(--gray, #6b7280);
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    .auth-modal .form-group {
        margin-bottom: 1.5rem;
    }

    .auth-modal .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--dark, #1f2937);
        font-size: 0.9rem;
    }

    .auth-modal .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .auth-modal .form-input:focus {
        outline: none;
        border-color: var(--primary, #6366f1);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .auth-modal .btn-block {
        width: 100%;
    }

    .auth-message {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 12px;
        font-size: 0.9rem;
        display: none;
    }

    .auth-message.success {
        background: #d1fae5;
        color: #065f46;
        display: block;
    }

    .auth-message.error {
        background: #fee2e2;
        color: #991b1b;
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .auth-modal-content {
            width: 95%;
            max-width: none;
            margin: 1rem;
        }

        .auth-modal-header {
            padding: 1.5rem;
        }

        .auth-modal-body {
            padding: 1.5rem;
        }
    }
</style>

<script>
    // Показать модальное окно авторизации
    function showAuthModal() {
        document.getElementById('authModal').classList.add('active');
        document.getElementById('authEmail').focus();
    }

    // Закрыть модальное окно
    function closeAuthModal() {
        document.getElementById('authModal').classList.remove('active');
        document.getElementById('authForm').reset();
        document.getElementById('codeGroup').style.display = 'none';
        document.getElementById('authSubmitBtn').textContent = 'Получить код';
        document.getElementById('authMessage').className = 'auth-message';
        document.getElementById('authMessage').textContent = '';
        window.authStep = 'email';
    }

    // Закрыть модалку по клику вне её
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('authModal');
        if (event.target === modal) {
            closeAuthModal();
        }
    });

    // Обработка авторизации
    window.authStep = 'email'; // email или code

    async function handleAuth(event) {
        event.preventDefault();

        const email = document.getElementById('authEmail').value;
        const code = document.getElementById('authCode').value;
        const messageEl = document.getElementById('authMessage');
        const submitBtn = document.getElementById('authSubmitBtn');

        if (window.authStep === 'email') {
            // Отправка email для получения кода
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';

            try {
                const response = await fetch('/api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'send_code', email: email })
                });

                const data = await response.json();

                if (data.success) {
                    messageEl.className = 'auth-message success';
                    messageEl.textContent = 'Код отправлен на ' + email;
                    document.getElementById('codeGroup').style.display = 'block';
                    submitBtn.textContent = 'Войти';
                    window.authStep = 'code';
                } else {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = data.message || 'Ошибка отправки кода';
                    submitBtn.textContent = 'Получить код';
                }
            } catch (error) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'Ошибка соединения';
                submitBtn.textContent = 'Получить код';
            }

            submitBtn.disabled = false;
        } else {
            // Проверка кода
            submitBtn.disabled = true;
            submitBtn.textContent = 'Проверка...';

            try {
                const response = await fetch('/api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'verify_code', email: email, code: code })
                });

                const data = await response.json();

                if (data.success) {
                    messageEl.className = 'auth-message success';
                    messageEl.textContent = 'Вход выполнен успешно!';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = data.message || 'Неверный код';
                    submitBtn.textContent = 'Войти';
                }
            } catch (error) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'Ошибка соединения';
                submitBtn.textContent = 'Войти';
            }

            submitBtn.disabled = false;
        }
    }
</script>
