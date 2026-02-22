<?php
/**
 * Telegram Widget — единая кнопка с подсказкой при наведении
 * Подключается на всех публичных страницах: <?php include 'components/telegram-widget.php'; ?>
 */
?>

<style>
.tg-widget {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 99;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: fadeInUp 0.6s ease;
}

.tg-tooltip {
    background: #fff;
    padding: 0.65rem 1.1rem;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    font-size: 0.85rem;
    font-weight: 500;
    color: #1f2937;
    white-space: nowrap;
    opacity: 0;
    transform: translateX(12px);
    transition: opacity 0.25s ease, transform 0.25s ease;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tg-tooltip::before {
    content: '';
    width: 7px;
    height: 7px;
    background: #22c55e;
    border-radius: 50%;
    flex-shrink: 0;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

.tg-widget:hover .tg-tooltip {
    opacity: 1;
    transform: translateX(0);
}

.tg-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #0088cc;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    box-shadow: 0 8px 20px rgba(0, 136, 204, 0.4);
    transition: all 0.3s ease;
    text-decoration: none;
    flex-shrink: 0;
}

.tg-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 28px rgba(0, 136, 204, 0.55);
    background: #0077bb;
}

@media (max-width: 768px) {
    .tg-widget {
        bottom: 1.5rem;
        right: 1.5rem;
    }
    .tg-btn {
        width: 54px;
        height: 54px;
        font-size: 1.5rem;
    }
    .tg-tooltip {
        display: none;
    }
}
</style>

<div class="tg-widget">
    <div class="tg-tooltip">Мы сейчас онлайн, напишите нам</div>
    <a href="<?php echo defined('MANAGER_TELEGRAM_LINK') ? MANAGER_TELEGRAM_LINK : 'https://t.me/Typografia_ru'; ?>"
       target="_blank" rel="noopener noreferrer"
       class="tg-btn" title="Написать в Telegram">
        <i class="fab fa-telegram-plane"></i>
    </a>
</div>
