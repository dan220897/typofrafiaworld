<?php
// Простой скрипт для тестирования getServiceById
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Service.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Test getServiceById ===\n\n";

$database = new Database();
$db = $database->getConnection();
$service = new Service($db);

$test_id = 'print_bw_a4';
echo "Testing service ID: $test_id\n\n";

// Тест 1: Прямой SQL запрос
echo "--- Test 1: Direct SQL query ---\n";
try {
    $query = "SELECT s.*, COALESCE(sbp.base_price, s.base_price, 0) as base_price
              FROM services s
              LEFT JOIN service_base_prices sbp ON s.id = sbp.service_id
              WHERE s.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $test_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "✓ Service found via direct query\n";
        echo "ID: " . $result['id'] . "\n";
        echo "Name: " . $result['name'] . "\n";
        echo "Base price: " . $result['base_price'] . "\n";
    } else {
        echo "✗ Service NOT found via direct query\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n--- Test 2: Using getServiceById() method ---\n";
$service_data = $service->getServiceById($test_id);

if ($service_data) {
    echo "✓ Service found via getServiceById()\n";
    echo "ID: " . $service_data['id'] . "\n";
    echo "Name: " . $service_data['name'] . "\n";
    echo "Base price: " . $service_data['base_price'] . "\n";
    echo "Parameters count: " . (is_array($service_data['parameters']) ? count($service_data['parameters']) : 0) . "\n";
    echo "Price rules count: " . (is_array($service_data['price_rules']) ? count($service_data['price_rules']) : 0) . "\n";
} else {
    echo "✗ Service NOT found via getServiceById()\n";
}

echo "\n--- Test 3: Check if service exists in services table ---\n";
try {
    $query = "SELECT id, name FROM services WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $test_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "✓ Service exists in services table\n";
        echo "ID: " . $result['id'] . "\n";
        echo "Name: " . $result['name'] . "\n";
    } else {
        echo "✗ Service does NOT exist in services table\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n--- Test 4: Check service_base_prices table ---\n";
try {
    $query = "SELECT * FROM service_base_prices WHERE service_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $test_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "✓ Price found in service_base_prices table\n";
        echo "Service ID: " . $result['service_id'] . "\n";
        echo "Base price: " . $result['base_price'] . "\n";
    } else {
        echo "ℹ No price in service_base_prices table (this is OK, will use services.base_price)\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End of tests ===\n";
?>
