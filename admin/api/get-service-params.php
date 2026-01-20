<?php
// admin/api/get-service-params.php - API для получения параметров услуги
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['service_id'])) {
    echo json_encode(['error' => 'Service ID is required']);
    exit;
}

$serviceId = $_GET['service_id'];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Получаем параметры услуги
    $params = [];

    // Размеры
    $stmt = $db->prepare("SELECT * FROM service_sizes WHERE service_id = ? ORDER BY sort_order");
    $stmt->execute([$serviceId]);
    $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($sizes) $params['sizes'] = $sizes;

    // Плотность
    $stmt = $db->prepare("SELECT * FROM service_density WHERE service_id = ? ORDER BY CAST(SUBSTRING_INDEX(label, ' ', 1) AS UNSIGNED) ASC");
    $stmt->execute([$serviceId]);
    $densities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($densities) $params['densities'] = $densities;

    // Стороны печати
    $stmt = $db->prepare("SELECT * FROM service_sides WHERE service_id = ? ORDER BY id");
    $stmt->execute([$serviceId]);
    $sides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($sides) $params['sides'] = $sides;

    // Количество
    $stmt = $db->prepare("
        SELECT id, service_id, quantity, label, multiplier, price
        FROM service_quantities
        WHERE service_id = ?
        GROUP BY quantity, label
        ORDER BY CAST(quantity AS UNSIGNED) ASC
    ");
    $stmt->execute([$serviceId]);
    $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($quantities) $params['quantities'] = $quantities;

    // Базовая цена
    $stmt = $db->prepare("SELECT base_price FROM service_base_prices WHERE service_id = ? LIMIT 1");
    $stmt->execute([$serviceId]);
    $basePriceRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $params['base_price'] = $basePriceRow ? $basePriceRow['base_price'] : 0;

    // Базовый тираж (первый в списке quantities)
    if (!empty($params['quantities'])) {
        $params['base_quantity'] = $params['quantities'][0]['quantity'];
    } else {
        $params['base_quantity'] = 1;
    }

    echo json_encode(['success' => true, 'params' => $params]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
