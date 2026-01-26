<?php
require_once 'config/config.php';

echo "<h2>Диагностика БД и категорий</h2>";

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Подключение к БД успешно<br><br>";

    // 1. Показываем к какой базе подключены
    $stmt = $db->query("SELECT DATABASE()");
    $current_db = $stmt->fetchColumn();
    echo "<strong>📊 Подключены к базе:</strong> " . htmlspecialchars($current_db) . "<br><br>";

    // 2. Показываем структуру таблицы services
    echo "<h3>Структура таблицы services:</h3>";
    $stmt = $db->query("DESCRIBE services");
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='margin-bottom: 20px;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($structure as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 3. Считаем общее количество услуг
    $stmt = $db->query("SELECT COUNT(*) FROM services");
    $total = $stmt->fetchColumn();
    echo "<strong>📦 Всего услуг в таблице:</strong> " . $total . "<br><br>";

    // 4. Получаем категории
    $stmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Найдено уникальных категорий: " . count($categories) . "</h3>";

    if (empty($categories)) {
        echo "❌ Массив категорий пуст!<br>";
    } else {
        echo "<ul>";
        foreach ($categories as $cat) {
            echo "<li>" . htmlspecialchars($cat) . "</li>";
        }
        echo "</ul>";
    }

    // 5. Показываем первые 10 услуг со ВСЕМИ полями
    echo "<h3>Первые 10 услуг (все поля):</h3>";
    $stmt2 = $db->query("SELECT * FROM services LIMIT 10");
    $services = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    if (empty($services)) {
        echo "❌ Таблица services пустая!<br>";
    } else {
        echo "<table border='1' style='font-size: 12px;'>";
        // Заголовки из первой записи
        echo "<tr>";
        foreach (array_keys($services[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";

        // Данные
        foreach ($services as $s) {
            echo "<tr>";
            foreach ($s as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
