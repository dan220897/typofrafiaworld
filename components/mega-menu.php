<?php
// Получаем все категории с услугами для мега-меню
try {
    $db = Database::getInstance()->getConnection();

    // Получаем все категории
    $stmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL AND is_active = 1 ORDER BY category");
    $megaMenu_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Получаем все услуги, сгруппированные по категориям
    $megaMenu_services = [];
    foreach ($megaMenu_categories as $megaMenu_cat) {
        $stmt = $db->prepare("
            SELECT id, label, icon
            FROM services
            WHERE category = ? AND is_active = 1
            ORDER BY sort_order, label
            LIMIT 8
        ");
        $stmt->execute([$megaMenu_cat]);
        $megaMenu_services[$megaMenu_cat] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $megaMenu_categories = [];
    $megaMenu_services = [];
}

// Иконки для категорий
$megaMenu_categoryIcons = [
    'Визитки' => 'fa-address-card',
    'Баннеры' => 'fa-panorama',
    'Флаеры' => 'fa-layer-group',
    'Листовки' => 'fa-file-lines',
    'Буклеты' => 'fa-book-open-reader',
    'Брошюры' => 'fa-book-bookmark',
    'Календари' => 'fa-calendar-days',
    'Блокноты' => 'fa-book',
    'Наклейки' => 'fa-note-sticky',
    'Сувенирная продукция' => 'fa-gifts',
    'Вывески' => 'fa-sign-hanging',
    'Каталоги' => 'fa-books',
    'Копирование документов' => 'fa-copy',
    'Дизайн и дополнительные услуги' => 'fa-pen-nib'
];
?>

<style>
    /* Мега-меню */
    .mega-menu-wrapper {
        position: relative;
    }

    .mega-menu-overlay {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0s linear 0.2s;
        pointer-events: none;
    }

    .mega-menu-wrapper:hover .mega-menu-overlay {
        opacity: 1;
        visibility: visible;
        transition: opacity 0.2s ease, visibility 0s linear 0s;
    }

    .mega-menu-trigger {
        color: var(--gray);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mega-menu-trigger::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary);
        transition: width 0.3s ease;
    }

    .mega-menu-trigger:hover {
        color: var(--primary);
    }

    .mega-menu-trigger:hover::after {
        width: 100%;
    }

    .mega-menu {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        width: 100%;
        background: #f9fafb;
        
        border-radius: 0;
        padding: 2rem 0;
        padding-top: 3rem;
        z-index: 1000;
        max-height: calc(100vh - 60px);
        overflow-y: auto;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: opacity 0.2s ease, visibility 0s linear 0.2s, transform 0.2s ease;
    }

    .mega-menu::before {
        content: '';
        position: absolute;
        top: -20px;
        left: 0;
        right: 0;
        height: 20px;
        background: transparent;
    }

    .mega-menu-wrapper:hover .mega-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        transition: opacity 0.2s ease, visibility 0s linear 0s, transform 0.2s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mega-menu-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .mega-menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
    }

    .mega-menu-category {
        display: flex;
        flex-direction: column;
    }

    .mega-menu-category-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--light-gray);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .mega-menu-category-header:hover {
        border-bottom-color: var(--primary);
    }

    .mega-menu-category-header:hover .mega-menu-category-icon {
        background: var(--primary-hover);
        transform: scale(1.05);
    }

    .mega-menu-category-icon {
        width: 40px;
        height: 40px;
        background: var(--primary);
        color: var(--white);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .mega-menu-category-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark);
    }

    .mega-menu-services {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .mega-menu-service {
        color: var(--gray);
        text-decoration: none;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }

    .mega-menu-service:hover {
        background: var(--light-gray);
        color: var(--primary);
        transform: translateX(5px);
    }

    .mega-menu-service i {
        font-size: 0.875rem;
        opacity: 0.5;
    }

    .mega-menu-view-all {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--light-gray);
    }

    .mega-menu-view-all a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: gap 0.2s ease;
    }

    .mega-menu-view-all a:hover {
        gap: 0.75rem;
    }

    /* Адаптивность */
    @media (max-width: 1024px) {
        .mega-menu-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .mega-menu {
            top: 110px;
            max-height: calc(100vh - 110px);
            padding: 1.5rem 0;
        }

        .mega-menu-container {
            padding: 0 1rem;
        }

        .mega-menu-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .mega-menu-category-header {
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
        }

        .mega-menu-category-icon {
            width: 36px;
            height: 36px;
            font-size: 1.1rem;
        }

        .mega-menu-category-name {
            font-size: 1rem;
        }
    }
</style>

<div class="mega-menu-wrapper">
    <a href="/" class="mega-menu-trigger nav-link">
        Каталог
        <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
    </a>

    <div class="mega-menu-overlay"></div>

    <div class="mega-menu">
        <div class="mega-menu-container">
            <div class="mega-menu-grid">
                <?php foreach ($megaMenu_categories as $megaMenu_category): ?>
                <div class="mega-menu-category">
                    <a href="/catalog.php?category=<?= urlencode($megaMenu_category) ?>" class="mega-menu-category-header">
                        <div class="mega-menu-category-icon">
                            <i class="fas <?= $megaMenu_categoryIcons[$megaMenu_category] ?? 'fa-folder' ?>"></i>
                        </div>
                        <h3 class="mega-menu-category-name"><?= htmlspecialchars($megaMenu_category) ?></h3>
                    </a>

                    <div class="mega-menu-services">
                        <?php
                        $megaMenu_servicesList = $megaMenu_services[$megaMenu_category] ?? [];
                        $megaMenu_displayedServices = array_slice($megaMenu_servicesList, 0, 6);
                        foreach ($megaMenu_displayedServices as $megaMenu_service):
                        ?>
                            <a href="/service.php?id=<?= $megaMenu_service['id'] ?>" class="mega-menu-service">
                                <i class="fas fa-angle-right"></i>
                                <?= htmlspecialchars($megaMenu_service['label']) ?>
                            </a>
                        <?php endforeach; ?>

                        <?php if (count($megaMenu_servicesList) > 6): ?>
                            <div class="mega-menu-view-all">
                                <a href="/catalog.php?category=<?= urlencode($megaMenu_category) ?>">
                                    Смотреть все (<?= count($megaMenu_servicesList) ?>)
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
