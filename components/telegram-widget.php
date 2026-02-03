<?php
/**
 * Telegram Widget - Плавающая кнопка с выбором точки
 * Подключается на всех публичных страницах: <?php include 'components/telegram-widget.php'; ?>
 * Требует предварительного подключения config/config.php
 */

// Получаем точки с telegram-ссылками из БД
$tgPoints = [];
try {
    $tgDb = Database::getInstance()->getConnection();
    $tgStmt = $tgDb->query("SELECT name, telegram_link FROM pickup_points WHERE is_active = 1 AND telegram_link IS NOT NULL AND telegram_link != '' ORDER BY sort_order, name");
    $tgPoints = $tgStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Если не удалось получить точки, виджет просто не покажет список
}
?>

<!-- Telegram Widget -->
<style>
.tg-widget {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 99;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0;
}

.tg-widget-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #0088cc;
    color: #fff;
    border: none;
    font-size: 1.75rem;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0, 136, 204, 0.4);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.tg-widget-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 28px rgba(0, 136, 204, 0.5);
}

.tg-widget-btn.active {
    background: #006699;
    transform: scale(1.1);
}

/* Список точек */
.tg-points-list {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.75rem;
    margin-bottom: 1rem;
    pointer-events: none;
    position: relative;
    z-index: 1;
}

.tg-point-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    opacity: 0;
    transform: translateY(20px) scale(0.8);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none;
    flex-direction: row-reverse;
}

.tg-points-list.open {
    pointer-events: auto;
}

.tg-points-list.open .tg-point-item {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

.tg-point-item:nth-child(1) { transition-delay: 0.05s; }
.tg-point-item:nth-child(2) { transition-delay: 0.1s; }
.tg-point-item:nth-child(3) { transition-delay: 0.15s; }
.tg-point-item:nth-child(4) { transition-delay: 0.2s; }
.tg-point-item:nth-child(5) { transition-delay: 0.25s; }

.tg-point-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #0088cc;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 136, 204, 0.35);
    transition: all 0.2s ease;
    text-decoration: none;
    flex-shrink: 0;
}

.tg-point-circle:hover {
    transform: scale(1.15);
    box-shadow: 0 6px 18px rgba(0, 136, 204, 0.5);
    background: #006fa8;
}

.tg-point-label {
    background: #fff;
    color: #1f2937;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
    white-space: nowrap;
    opacity: 0;
    transform: translateX(10px);
    transition: all 0.2s ease;
    pointer-events: none;
}

.tg-point-item:hover .tg-point-label {
    opacity: 1;
    transform: translateX(0);
}

/* Overlay для закрытия при клике снаружи */
.tg-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 98;
}

.tg-overlay.active {
    display: block;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .tg-widget {
        bottom: 1.5rem;
        right: 1.5rem;
    }

    .tg-widget-btn {
        width: 56px;
        height: 56px;
        font-size: 1.5rem;
    }

    .tg-point-circle {
        width: 46px;
        height: 46px;
        font-size: 1.1rem;
    }

    .tg-point-label {
        opacity: 1;
        transform: translateX(0);
        font-size: 0.8rem;
        padding: 0.4rem 0.75rem;
    }
}
</style>

<div class="tg-overlay" id="tgOverlay" onclick="closeTgWidget()"></div>

<div class="tg-widget">
    <div class="tg-points-list" id="tgPointsList">
        <?php foreach ($tgPoints as $point): ?>
        <div class="tg-point-item">
            <a href="<?php echo htmlspecialchars($point['telegram_link']); ?>" target="_blank" rel="noopener noreferrer"
               class="tg-point-circle" title="<?php echo htmlspecialchars($point['name']); ?>">
                <i class="fab fa-telegram-plane"></i>
            </a>
            <span class="tg-point-label"><?php echo htmlspecialchars($point['name']); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <button class="tg-widget-btn" id="tgWidgetBtn" onclick="toggleTgWidget()" title="Написать в Telegram">
        <i class="fab fa-telegram-plane" id="tgBtnIcon"></i>
    </button>
</div>

<script>
function toggleTgWidget() {
    var list = document.getElementById('tgPointsList');
    var btn = document.getElementById('tgWidgetBtn');
    var overlay = document.getElementById('tgOverlay');
    var icon = document.getElementById('tgBtnIcon');

    var isOpen = list.classList.contains('open');
    if (isOpen) {
        closeTgWidget();
    } else {
        list.classList.add('open');
        btn.classList.add('active');
        overlay.classList.add('active');
        icon.className = 'fas fa-times';
    }
}

function closeTgWidget() {
    var list = document.getElementById('tgPointsList');
    var btn = document.getElementById('tgWidgetBtn');
    var overlay = document.getElementById('tgOverlay');
    var icon = document.getElementById('tgBtnIcon');

    list.classList.remove('open');
    btn.classList.remove('active');
    overlay.classList.remove('active');
    icon.className = 'fab fa-telegram-plane';
}
</script>
