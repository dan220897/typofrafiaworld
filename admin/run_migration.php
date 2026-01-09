<?php
/**
 * Веб-интерфейс для запуска миграций
 * Доступ: http://your-domain.com/admin/run_migration.php
 */

// Простая защита паролем
$MIGRATION_PASSWORD = 'migration2026'; // ИЗМЕНИТЕ ЭТО!

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] !== $MIGRATION_PASSWORD) {
        die('❌ Неверный пароль!');
    }

    // Запускаем миграцию
    require_once __DIR__ . '/../config/config.php';

    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Миграция БД</title>
        <style>
            body {
                font-family: 'Courier New', monospace;
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 20px;
                line-height: 1.6;
            }
            .container {
                max-width: 1000px;
                margin: 0 auto;
                background: #252526;
                padding: 30px;
                border-radius: 8px;
            }
            h1 {
                color: #4ec9b0;
                border-bottom: 2px solid #4ec9b0;
                padding-bottom: 10px;
            }
            .success {
                color: #4ec9b0;
            }
            .error {
                color: #f48771;
            }
            .warning {
                color: #dcdcaa;
            }
            .info {
                color: #9cdcfe;
            }
            pre {
                background: #1e1e1e;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
            }
            hr {
                border: none;
                border-top: 1px solid #3e3e42;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 Миграция: Добавление каталога услуг</h1>
            <pre><?php

    try {
        $db = Database::getInstance()->getConnection();
        echo "✅ Подключение к БД установлено\n\n";

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Список миграций
        $migrations = [
            '../migrations/001_add_services_catalog.sql',
            '../migrations/002_add_services_catalog_part2.sql'
        ];

        foreach ($migrations as $index => $migration) {
            $migrationFile = __DIR__ . '/' . $migration;

            if (!file_exists($migrationFile)) {
                echo "<span class='error'>❌ Файл не найден: $migration</span>\n";
                continue;
            }

            echo "<span class='info'>📝 Выполнение: $migration</span>\n";
            echo str_repeat('-', 60) . "\n";

            $sql = file_get_contents($migrationFile);

            // Разбиваем на запросы
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
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
                    echo ".";
                    flush();
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        echo "<span class='warning'>⚠</span>";
                    } elseif (strpos($e->getMessage(), 'Duplicate column') !== false) {
                        echo "<span class='warning'>⚠</span>";
                    } else {
                        $errorCount++;
                        echo "\n<span class='error'>❌ " . $e->getMessage() . "</span>\n";
                    }
                }
            }

            echo "\n\n<span class='success'>✅ Завершено: $successCount запросов</span>\n";
            if ($errorCount > 0) {
                echo "<span class='error'>Ошибок: $errorCount</span>\n";
            }
            echo "\n";
        }

        echo str_repeat('=', 60) . "\n";
        echo "<span class='success'>✅ ВСЕ МИГРАЦИИ ЗАВЕРШЕНЫ</span>\n";
        echo str_repeat('=', 60) . "\n\n";

        // Статистика
        echo "<span class='info'>📊 Статистика услуг по категориям:</span>\n";
        echo str_repeat('-', 60) . "\n";

        $stmt = $db->query("SELECT category, COUNT(*) as count FROM services WHERE category IS NOT NULL GROUP BY category ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalServices = 0;
        foreach ($categories as $cat) {
            printf("  %-30s : <span class='success'>%3d услуг</span>\n", $cat['category'], $cat['count']);
            $totalServices += $cat['count'];
        }

        echo str_repeat('-', 60) . "\n";
        printf("  %-30s : <span class='success'>%3d услуг</span>\n", "ВСЕГО", $totalServices);
        echo "\n\n";

        // Параметры
        echo "<span class='info'>📋 Статистика параметров:</span>\n";
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
                printf("  %-30s : <span class='success'>%3d записей</span>\n", $label, $count);
            } catch (PDOException $e) {
                printf("  %-30s : <span class='warning'>таблица не существует</span>\n", $label);
            }
        }

        echo "\n\n";
        echo "<span class='success'>🎉 Миграция успешно завершена!</span>\n";
        echo "<span class='info'>📌 Теперь откройте главную страницу сайта и проверьте каталог.</span>\n";

    } catch (Exception $e) {
        echo "\n<span class='error'>❌ КРИТИЧЕСКАЯ ОШИБКА:</span>\n";
        echo "<span class='error'>" . $e->getMessage() . "</span>\n";
        echo "<span class='error'>" . $e->getTraceAsString() . "</span>\n";
    }

    ?></pre>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запуск миграции БД</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .warning strong {
            color: #856404;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }
        input[type="password"] {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Запуск миграции</h1>
        <p class="subtitle">Добавление каталога услуг типографии</p>

        <div class="warning">
            <strong>⚠️ Внимание!</strong><br>
            Эта операция добавит все услуги из прайс-листа в базу данных.<br>
            Дублирующиеся записи будут пропущены.
        </div>

        <form method="POST">
            <div>
                <label for="password">Пароль для запуска миграции:</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Введите пароль"
                    autocomplete="off"
                >
            </div>

            <button type="submit">
                ▶️ Запустить миграцию
            </button>
        </form>
    </div>
</body>
</html>
