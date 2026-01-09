<?php
/**
 * Скрипт для запуска миграций базы данных
 * Запуск: php migrations/run_migrations.php
 */

// Подключаем конфигурацию
require_once __DIR__ . '/../config/config.php';

echo "==========================================\n";
echo "  МИГРАЦИЯ: Добавление каталога услуг\n";
echo "==========================================\n\n";

try {
    $db = Database::getInstance()->getConnection();

    // Отключаем автокоммит для транзакций
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Список файлов миграций
    $migrations = [
        '000_create_tables_structure.sql',
        '001_add_services_catalog.sql',
        '002_add_services_catalog_part2.sql'
    ];

    foreach ($migrations as $index => $migration) {
        $migrationFile = __DIR__ . '/' . $migration;

        if (!file_exists($migrationFile)) {
            echo "❌ Файл миграции не найден: $migration\n";
            continue;
        }

        echo "📝 Выполнение миграции: $migration\n";
        echo str_repeat('-', 60) . "\n";

        // Читаем SQL из файла
        $sql = file_get_contents($migrationFile);

        // Разбиваем на отдельные запросы
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                // Пропускаем пустые строки и комментарии
                $stmt = trim($stmt);
                return !empty($stmt) &&
                       !str_starts_with($stmt, '--') &&
                       !str_starts_with($stmt, '/*');
            }
        );

        $successCount = 0;
        $errorCount = 0;

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;

            try {
                $db->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                // Игнорируем ошибки дубликатов (если услуга уже существует)
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    echo "⚠️  Запись уже существует, пропускаем...\n";
                } elseif (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "⚠️  Столбец уже существует, пропускаем...\n";
                } else {
                    $errorCount++;
                    echo "❌ Ошибка: " . $e->getMessage() . "\n";
                    echo "   SQL: " . substr($statement, 0, 100) . "...\n\n";
                }
            }
        }

        echo "\n✅ Миграция $migration завершена\n";
        echo "   Успешно: $successCount запросов\n";
        if ($errorCount > 0) {
            echo "   Ошибок: $errorCount запросов\n";
        }
        echo "\n";
    }

    echo "==========================================\n";
    echo "  ✅ ВСЕ МИГРАЦИИ ЗАВЕРШЕНЫ\n";
    echo "==========================================\n\n";

    // Проверяем результат
    echo "📊 Статистика добавленных услуг:\n";
    echo str_repeat('-', 60) . "\n";

    $stmt = $db->query("SELECT category, COUNT(*) as count FROM services WHERE category IS NOT NULL GROUP BY category ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalServices = 0;
    foreach ($categories as $cat) {
        echo sprintf("  %-30s : %3d услуг\n", $cat['category'], $cat['count']);
        $totalServices += $cat['count'];
    }

    echo str_repeat('-', 60) . "\n";
    echo sprintf("  %-30s : %3d услуг\n", "ВСЕГО", $totalServices);
    echo "\n";

    // Проверяем параметры услуг
    echo "📋 Статистика параметров:\n";
    echo str_repeat('-', 60) . "\n";

    $tables = [
        'service_sizes' => 'Размеры',
        'service_density' => 'Плотности',
        'service_sides' => 'Стороны печати',
        'service_quantities' => 'Тиражи',
        'service_lamination' => 'Ламинация'
    ];

    foreach ($tables as $table => $label) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo sprintf("  %-30s : %3d записей\n", $label, $count);
        } catch (PDOException $e) {
            echo sprintf("  %-30s : таблица не существует\n", $label);
        }
    }

    echo "\n";
    echo "🎉 Миграция успешно завершена!\n";
    echo "📌 Теперь можно открыть сайт и проверить каталог услуг.\n\n";

} catch (Exception $e) {
    echo "\n❌ КРИТИЧЕСКАЯ ОШИБКА:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
