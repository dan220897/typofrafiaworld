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
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--white);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 2rem;
        width: 900px;
        max-width: 90vw;
        display: none;
        margin-top: 1rem;
        z-index: 1000;
        max-height: 80vh;
        overflow-y: auto;
    }

    .mega-menu-wrapper:hover .mega-menu {
        display: block;
        animation: fadeInDown 0.3s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    .mega-menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
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
        .mega-menu {
            width: 700px;
        }

        .mega-menu-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .mega-menu {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            width: 100%;
            max-width: 100%;
            transform: none;
            border-radius: 0;
            margin-top: 0;
            max-height: calc(100vh - 60px);
        }

        .mega-menu-wrapper:hover .mega-menu {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    }
</style>

<div class="mega-menu-wrapper">
    <a href="/" class="mega-menu-trigger nav-link">
        Каталог
        <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
    </a>

    <div class="mega-menu">
        <div class="mega-menu-grid">
            <?php foreach ($menuCategories as $category): ?>
                <div class="mega-menu-category">
                    <div class="mega-menu-category-header">
                        <div class="mega-menu-category-icon">
                            <i class="fas <?= $categoryIcons[$category] ?? 'fa-folder' ?>"></i>
                        </div>
                        <h3 class="mega-menu-category-name"><?= htmlspecialchars($category) ?></h3>
                    </div>

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
