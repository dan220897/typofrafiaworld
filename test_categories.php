<?php
require_once 'config/config.php';

echo "<h2>Тест категорий</h2>";

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Подключение к БД успешно<br>";

    $stmt = $db->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Найдено категорий: " . count($categories) . "</h3>";

    if (empty($categories)) {
        echo "❌ Массив категорий пуст!<br>";
    } else {
        echo "<ul>";
        foreach ($categories as $cat) {
            echo "<li>" . htmlspecialchars($cat) . "</li>";
        }
        echo "</ul>";
    }

    // Проверим все записи
    $stmt2 = $db->query("SELECT id, label, category FROM services LIMIT 10");
    $services = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Первые 10 услуг:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Label</th><th>Category</th></tr>";
    foreach ($services as $s) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($s['id']) . "</td>";
        echo "<td>" . htmlspecialchars($s['label']) . "</td>";
        echo "<td>" . htmlspecialchars($s['category'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString();
}
