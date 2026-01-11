<!-- Компонент корзины -->
<style>
    /* Иконка корзины */
    .cart-icon-container {
        position: relative;
        cursor: pointer;
    }

    .cart-icon {
        font-size: 1.5rem;
        color: var(--gray);
        transition: color 0.2s;
    }

    .cart-icon:hover {
        color: var(--primary);
    }

    .cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--secondary);
        color: var(--white);
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .cart-badge.hidden {
        display: none;
    }

    /* Оверлей попапа */
    .cart-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 99998;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(3px);
    }

    .cart-overlay.active {
        display: block;
        opacity: 1;
        height: 100vh;
    }

    /* Попап корзины */
    .cart-popup {
        position: fixed;
        top: 0;
        right: -100%;
        width: 500px;
        max-width: 100vw;
        height: 100vh;
        background: var(--white);
        z-index: 99999;
        box-shadow: -4px 0 30px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        visibility: hidden;
        opacity: 0;
    }

    .cart-popup.active {
        right: 0;
        visibility: visible;
        opacity: 1;
    }

    .cart-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--light-gray);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark);
    }

    .cart-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--gray);
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .cart-close:hover {
        background: var(--light-gray);
    }

    .cart-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
    }

    .cart-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--gray);
    }

    .cart-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .cart-item {
        background: var(--light-gray);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .cart-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.5rem;
    }

    .cart-item-name {
        font-weight: 600;
        color: var(--dark);
        flex: 1;
    }

    .cart-item-remove {
        background: none;
        border: none;
        color: var(--gray);
        cursor: pointer;
        padding: 0.25rem;
        font-size: 1.25rem;
        transition: color 0.2s;
    }

    .cart-item-remove:hover {
        color: var(--secondary);
    }

    .cart-item-params {
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: 0.5rem;
    }

    .cart-item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cart-item-qty-btn {
        background: var(--white);
        border: 1px solid var(--light-gray);
        border-radius: 4px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .cart-item-qty-btn:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: var(--white);
    }

    .cart-item-qty-input {
        width: 50px;
        text-align: center;
        border: 1px solid var(--light-gray);
        border-radius: 4px;
        padding: 0.25rem;
    }

    .cart-item-price {
        font-weight: 600;
        color: var(--success);
        font-size: 1.125rem;
    }

    .cart-footer {
        border-top: 2px solid var(--light-gray);
        padding: 1.5rem;
        margin-bottom:25px;
    }

    .cart-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .cart-total-label {
        color: var(--dark);
    }

    .cart-total-price {
        color: var(--success);
        font-size: 1.5rem;
    }

    .cart-checkout-btn {
        width: 100%;
        padding: 1rem;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-size: 1.125rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .cart-checkout-btn:hover {
        background: var(--primary-hover);
    }

    .cart-checkout-btn:disabled {
        background: var(--gray);
        cursor: not-allowed;
    }

    /* Форма оформления заказа */
    .checkout-form {
        display: none;
    }

    .checkout-form.active {
        display: block;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark);
    }

    .form-label.required::after {
        content: ' *';
        color: var(--secondary);
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--light-gray);
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-input.error {
        border-color: var(--secondary);
    }

    .form-error {
        color: var(--secondary);
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }

    .form-error.active {
        display: block;
    }

    /* Карта с точками самовывоза */
    #pickupMap {
        width: 100%;
        height: 300px;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .pickup-points-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .pickup-point-item {
        padding: 0.75rem;
        border: 2px solid var(--light-gray);
        border-radius: 8px;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pickup-point-item:hover {
        border-color: var(--primary);
        background: var(--light-gray);
    }

    .pickup-point-item.selected {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.1);
    }

    .pickup-point-name {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .pickup-point-address {
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: 0.25rem;
    }

    .pickup-point-hours {
        font-size: 0.75rem;
        color: var(--gray);
    }

    .checkout-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .btn-back {
        flex: 1;
        padding: 0.75rem;
        background: var(--white);
        color: var(--gray);
        border: 2px solid var(--light-gray);
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-back:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-submit {
        flex: 2;
        padding: 0.75rem;
        background: var(--success);
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-submit:hover {
        background: #059669;
    }

    .btn-submit:disabled {
        background: var(--gray);
        cursor: not-allowed;
    }

    /* Адаптивность */
    @media (max-width: 768px) {
        .cart-popup {
            width: 100vw;
        }
    }
</style>

<!-- Иконка корзины -->
<div class="cart-icon-container hidden" id="cartIconContainer" onclick="openCartPopup()">
    <i class="fas fa-shopping-cart cart-icon"></i>
    <span class="cart-badge hidden" id="cartBadge">0</span>
</div>

<!-- Оверлей -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCartPopup()"></div>

<!-- Попап корзины -->
<div class="cart-popup" id="cartPopup">
    <!-- Шапка -->
    <div class="cart-header">
        <h2 class="cart-title" id="cartPopupTitle">Корзина</h2>
        <button class="cart-close" onclick="closeCartPopup()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Содержимое корзины -->
    <div class="cart-body" id="cartContent">
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>Корзина пуста</p>
        </div>
    </div>

    <!-- Форма оформления заказа -->
    <div class="cart-body checkout-form" id="checkoutForm">
        <form id="checkoutFormElement" onsubmit="submitCheckout(event)">
            <div class="form-group">
                <label class="form-label required" for="checkoutName">Имя</label>
                <input type="text" id="checkoutName" class="form-input" required>
                <div class="form-error" id="errorName">Введите ваше имя</div>
            </div>

            <div class="form-group">
                <label class="form-label required" for="checkoutPhone">Телефон</label>
                <input type="tel" id="checkoutPhone" class="form-input" placeholder="+7 (XXX) XXX-XX-XX" required>
                <div class="form-error" id="errorPhone">Введите корректный номер телефона</div>
            </div>

            <div class="form-group">
                <label class="form-label required" for="checkoutEmail">Email</label>
                <input type="email" id="checkoutEmail" class="form-input" required>
                <div class="form-error" id="errorEmail">Введите корректный email</div>
            </div>

            <div class="form-group">
                <label class="form-label required">Точка самовывоза</label>
                <div id="pickupMap"></div>
                <div class="pickup-points-list" id="pickupPointsList"></div>
                <div class="form-error" id="errorPickupPoint">Выберите точку самовывоза</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="checkoutNotes">Комментарий к заказу</label>
                <textarea id="checkoutNotes" class="form-input" rows="3"></textarea>
            </div>

            <div class="checkout-buttons">
                <button type="button" class="btn-back" onclick="showCart()">Назад</button>
                <button type="submit" class="btn-submit" id="submitBtn">Оформить заказ</button>
            </div>
        </form>
    </div>

    <!-- Подвал с итогом и кнопкой -->
    <div class="cart-footer" id="cartFooter">
        <div class="cart-total">
            <span class="cart-total-label">Итого:</span>
            <span class="cart-total-price" id="cartTotalPrice">0 ₽</span>
        </div>
        <button class="cart-checkout-btn" id="checkoutBtn" onclick="showCheckoutForm()">
            Оформить заказ
        </button>
    </div>
</div>

<!-- Скрипт для работы с корзиной -->
<script src="https://api-maps.yandex.ru/2.1/?apikey=8dfcf1c7-a203-4c41-96f2-b99ce403dba2
&lang=ru_RU"></script>
<script>
    // Глобальное состояние корзины
    let cartState = {
        items: [],
        total: 0,
        count: 0,
        pickupPoints: [],
        selectedPickupPoint: null,
        map: null,
        placemarks: []
    };

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        loadCart();
        loadPickupPoints();
        initScrollHandler();
    });

    // Обработчик горизонтального скролла
    function initScrollHandler() {
        let lastScrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

        window.addEventListener('scroll', function() {
            const currentScrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

            // Если произошел горизонтальный скролл
            if (currentScrollLeft !== lastScrollLeft) {
                // Закрываем корзину, если она открыта
                const cartPopup = document.getElementById('cartPopup');
                if (cartPopup && cartPopup.classList.contains('active')) {
                    closeCartPopup();
                }
            }

            lastScrollLeft = currentScrollLeft;
        });
    }

    // Загрузить корзину
    async function loadCart() {
        try {
            const response = await fetch('/api/cart.php');
            const data = await response.json();

            if (data.success) {
                cartState.items = data.data.items;
                cartState.total = data.data.total_amount;
                cartState.count = data.data.total_items;
                updateCartUI();
            }
        } catch (error) {
            console.error('Error loading cart:', error);
        }
    }

    // Загрузить точки самовывоза
    async function loadPickupPoints() {
        try {
            const response = await fetch('/api/cart.php?action=pickup_points');
            const data = await response.json();

            if (data.success) {
                cartState.pickupPoints = data.data.pickup_points;
            }
        } catch (error) {
            console.error('Error loading pickup points:', error);
        }
    }

    // Добавить товар в корзину
    async function addToCart(serviceId, quantity, unitPrice, parameters = {}) {
        try {
            const response = await fetch('/api/cart.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    quantity: quantity,
                    unit_price: unitPrice,
                    parameters: parameters
                })
            });

            const data = await response.json();

            if (data.success) {
                cartState.items = data.data.items;
                cartState.total = data.data.total_amount;
                cartState.count = data.data.total_items;
                updateCartUI();
                showNotification('Товар добавлен в корзину');
                return true;
            } else {
                showNotification('Ошибка: ' + data.error, 'error');
                return false;
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            showNotification('Ошибка при добавлении товара', 'error');
            return false;
        }
    }

    // Обновить количество товара
    async function updateCartItemQuantity(cartId, quantity) {
        try {
            const response = await fetch('/api/cart.php?action=update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (data.success) {
                cartState.items = data.data.items;
                cartState.total = data.data.total_amount;
                cartState.count = data.data.total_items;
                updateCartUI();
            }
        } catch (error) {
            console.error('Error updating cart:', error);
        }
    }

    // Удалить товар из корзины
    async function removeFromCart(cartId) {
        try {
            const response = await fetch('/api/cart.php?action=remove', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cart_id: cartId
                })
            });

            const data = await response.json();

            if (data.success) {
                cartState.items = data.data.items;
                cartState.total = data.data.total_amount;
                cartState.count = data.data.total_items;
                updateCartUI();
                showNotification('Товар удален из корзины');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
        }
    }

    // Обновить UI корзины
    function updateCartUI() {
        // Обновляем контейнер иконки корзины
        const cartIconContainer = document.getElementById('cartIconContainer');

        // Обновляем бейдж
        const badge = document.getElementById('cartBadge');
        if (cartState.count > 0) {
            // Показываем иконку корзины и бейдж
            cartIconContainer.classList.remove('hidden');
            badge.textContent = cartState.count;
            badge.classList.remove('hidden');
        } else {
            // Скрываем иконку корзины
            cartIconContainer.classList.add('hidden');
            badge.classList.add('hidden');
        }

        // Обновляем содержимое корзины
        const cartContent = document.getElementById('cartContent');
        const cartFooter = document.getElementById('cartFooter');
        const checkoutBtn = document.getElementById('checkoutBtn');

        if (cartState.items.length === 0) {
            cartContent.innerHTML = `
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Корзина пуста</p>
                </div>
            `;
            checkoutBtn.disabled = true;
        } else {
            let html = '';
            cartState.items.forEach(item => {
                const params = item.parameters ? formatParameters(item.parameters) : '';
                html += `
                    <div class="cart-item">
                        <div class="cart-item-header">
                            <div class="cart-item-name">${item.service_name}</div>
                            <button class="cart-item-remove" onclick="removeFromCart(${item.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        ${params ? `<div class="cart-item-params">${params}</div>` : ''}
                        <div class="cart-item-footer">
                            <div class="cart-item-quantity">
                                <button class="cart-item-qty-btn" onclick="updateCartItemQuantity(${item.id}, ${item.quantity - 1})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span>${item.quantity}</span>
                                <button class="cart-item-qty-btn" onclick="updateCartItemQuantity(${item.id}, ${item.quantity + 1})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="cart-item-price">${formatPrice(item.total_price)} ₽</div>
                        </div>
                    </div>
                `;
            });
            cartContent.innerHTML = html;
            checkoutBtn.disabled = false;
        }

        // Обновляем итоговую сумму
        document.getElementById('cartTotalPrice').textContent = formatPrice(cartState.total) + ' ₽';
    }

    // Форматировать параметры
    function formatParameters(params) {
        if (!params || typeof params !== 'object') return '';

        const parts = [];
        if (params.size) parts.push(params.size);
        if (params.density) parts.push(params.density);
        if (params.sides) parts.push(params.sides);
        if (params.quantity) parts.push(params.quantity);

        return parts.join(', ');
    }

    // Форматировать цену
    function formatPrice(price) {
        return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    // Открыть попап корзины
    function openCartPopup() {
        document.getElementById('cartOverlay').classList.add('active');
        document.getElementById('cartPopup').classList.add('active');
        document.body.style.overflow = 'hidden';
        showCart();
    }

    // Закрыть попап корзины
    function closeCartPopup() {
        document.getElementById('cartOverlay').classList.remove('active');
        document.getElementById('cartPopup').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Показать корзину
    function showCart() {
        document.getElementById('cartContent').style.display = 'block';
        document.getElementById('checkoutForm').classList.remove('active');
        document.getElementById('cartFooter').style.display = 'block';
        document.getElementById('cartPopupTitle').textContent = 'Корзина';
    }

    // Показать форму оформления
    function showCheckoutForm() {
        document.getElementById('cartContent').style.display = 'none';
        document.getElementById('checkoutForm').classList.add('active');
        document.getElementById('cartFooter').style.display = 'none';
        document.getElementById('cartPopupTitle').textContent = 'Оформление заказа';

        // Инициализируем карту
        initMap();
        renderPickupPoints();
    }

    // Инициализировать карту Яндекс
    function initMap() {
        if (cartState.map) return; // Карта уже инициализирована

        ymaps.ready(function() {
            cartState.map = new ymaps.Map('pickupMap', {
                center: [55.751244, 37.618423], // Москва
                zoom: 10
            });

            // Добавляем метки точек самовывоза
            cartState.pickupPoints.forEach((point, index) => {
                const placemark = new ymaps.Placemark(
                    [point.latitude, point.longitude],
                    {
                        balloonContent: `
                            <strong>${point.name}</strong><br>
                            ${point.address}<br>
                            ${point.working_hours}<br>
                            <a href="#" onclick="selectPickupPoint(${point.id}); return false;">Выбрать</a>
                        `
                    },
                    {
                        preset: 'islands#blueDotIcon'
                    }
                );

                cartState.map.geoObjects.add(placemark);
                cartState.placemarks.push(placemark);

                // При клике на метку выбираем точку
                placemark.events.add('click', function() {
                    selectPickupPoint(point.id);
                });
            });
        });
    }

    // Отобразить список точек самовывоза
    function renderPickupPoints() {
        const list = document.getElementById('pickupPointsList');
        let html = '';

        cartState.pickupPoints.forEach(point => {
            const selected = cartState.selectedPickupPoint === point.id ? 'selected' : '';
            html += `
                <div class="pickup-point-item ${selected}" onclick="selectPickupPoint(${point.id})">
                    <div class="pickup-point-name">${point.name}</div>
                    <div class="pickup-point-address">${point.address}</div>
                    <div class="pickup-point-hours">${point.working_hours}</div>
                </div>
            `;
        });

        list.innerHTML = html;
    }

    // Выбрать точку самовывоза
    function selectPickupPoint(pointId) {
        cartState.selectedPickupPoint = pointId;
        renderPickupPoints();

        // Центрируем карту на выбранной точке
        const point = cartState.pickupPoints.find(p => p.id === pointId);
        if (point && cartState.map) {
            cartState.map.setCenter([point.latitude, point.longitude], 14);
        }

        // Убираем ошибку
        document.getElementById('errorPickupPoint').classList.remove('active');
    }

    // Оформить заказ
    async function submitCheckout(event) {
        event.preventDefault();

        const name = document.getElementById('checkoutName').value.trim();
        const phone = document.getElementById('checkoutPhone').value.trim();
        const email = document.getElementById('checkoutEmail').value.trim();
        const notes = document.getElementById('checkoutNotes').value.trim();

        // Валидация
        let hasError = false;

        if (!name) {
            document.getElementById('errorName').classList.add('active');
            hasError = true;
        } else {
            document.getElementById('errorName').classList.remove('active');
        }

        if (!phone || !/^\+?[0-9\s\-\(\)]{10,20}$/.test(phone)) {
            document.getElementById('errorPhone').classList.add('active');
            hasError = true;
        } else {
            document.getElementById('errorPhone').classList.remove('active');
        }

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById('errorEmail').classList.add('active');
            hasError = true;
        } else {
            document.getElementById('errorEmail').classList.remove('active');
        }

        if (!cartState.selectedPickupPoint) {
            document.getElementById('errorPickupPoint').classList.add('active');
            hasError = true;
        } else {
            document.getElementById('errorPickupPoint').classList.remove('active');
        }

        if (hasError) return;

        // Отправляем заказ
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Оформляем...';

        try {
            const response = await fetch('/api/cart.php?action=checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    phone: phone,
                    email: email,
                    pickup_point_id: cartState.selectedPickupPoint,
                    notes: notes
                })
            });

            const data = await response.json();

            if (data.success) {
                // Очищаем корзину
                cartState.items = [];
                cartState.total = 0;
                cartState.count = 0;
                updateCartUI();

                // Показываем сообщение об успехе
                alert(`Заказ ${data.data.order_number} успешно оформлен!\n\nМы свяжемся с вами в ближайшее время.`);

                // Закрываем попап
                closeCartPopup();

                // Сбрасываем форму
                document.getElementById('checkoutFormElement').reset();
                cartState.selectedPickupPoint = null;
            } else {
                alert('Ошибка при оформлении заказа: ' + data.error);
            }
        } catch (error) {
            console.error('Error submitting checkout:', error);
            alert('Произошла ошибка при оформлении заказа');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Оформить заказ';
        }
    }

    // Показать уведомление
    function showNotification(message, type = 'success') {
        // Простое уведомление через alert (можно заменить на тост)
        console.log(`[${type}] ${message}`);
    }
</script>

