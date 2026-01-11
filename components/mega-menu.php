<?php
// Получаем все категории с услугами для мега-меню
try {
    $db = Database::getInstance()->getConnection();

    // Получаем все категории
    $stmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL AND is_active = 1 ORDER BY category");
    $menuCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Получаем все услуги, сгруппированные по категориям
    $menuServices = [];
    foreach ($menuCategories as $cat) {
        $stmt = $db->prepare("
            SELECT id, label, icon
            FROM services
            WHERE category = ? AND is_active = 1
            ORDER BY sort_order, label
            LIMIT 8
        ");
        $stmt->execute([$cat]);
        $menuServices[$cat] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $menuCategories = [];
    $menuServices = [];
}

// Иконки для категорий
$categoryIcons = [
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
        display: none;
        position: fixed;
        top: 72px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .mega-menu-wrapper:hover .mega-menu-overlay {
        display: block;
        opacity: 1;
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
        top: 72px;
        left: 0;
        right: 0;
        width: 100%;
        background: var(--white);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border-radius: 0;
        padding: 2rem 0;
        display: none;
        z-index: 1000;
        max-height: calc(100vh - 72px);
        overflow-y: auto;
    }

    .mega-menu-wrapper:hover .mega-menu {
        display: block;
        animation: fadeInDown 0.3s ease;
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
                <?php foreach ($menuCategories as $category): ?>
                <div class="mega-menu-category">
                    <a href="/catalog.php?category=<?= urlencode($category) ?>" class="mega-menu-category-header">
                        <div class="mega-menu-category-icon">
                            <i class="fas <?= $categoryIcons[$category] ?? 'fa-folder' ?>"></i>
                        </div>
                        <h3 class="mega-menu-category-name"><?= htmlspecialchars($category) ?></h3>
                    </a>

                    <div class="mega-menu-services">
                        <?php
                        $services = $menuServices[$category] ?? [];
                        $displayedServices = array_slice($services, 0, 6);
                        foreach ($displayedServices as $service):
                        ?>
                            <a href="/service.php?id=<?= $service['id'] ?>" class="mega-menu-service">
                                <i class="fas fa-angle-right"></i>
                                <?= htmlspecialchars($service['label']) ?>
                            </a>
                        <?php endforeach; ?>

                        <?php if (count($services) > 6): ?>
                            <div class="mega-menu-view-all">
                                <a href="/catalog.php?category=<?= urlencode($category) ?>">
                                    Смотреть все (<?= count($services) ?>)
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
