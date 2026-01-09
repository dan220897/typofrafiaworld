<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Простой тест API
$response = [
    'success' => true,
    'message' => 'API работает!',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'script_name' => $_SERVER['SCRIPT_NAME'],
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown'
    ],
    'files_check' => [
        'config_exists' => file_exists(dirname(__DIR__) . '/config/config.php'),
        'classes_dir_exists' => is_dir(dirname(__DIR__) . '/classes'),
        'api_dir_exists' => is_dir(__DIR__)
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>