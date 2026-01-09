<?php
// Проверка доступности webhook для Telegram

header('Content-Type: application/json');

$checks = [];

// 1. Проверка HTTPS
$checks['https'] = [
    'status' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'message' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' 
        ? '✅ HTTPS активен' 
        : '❌ HTTPS не активен (Telegram требует HTTPS!)'
];

// 2. Проверка домена
$checks['domain'] = [
    'status' => $_SERVER['HTTP_HOST'] === 'typo-grafia.ru',
    'message' => '🌐 Домен: ' . $_SERVER['HTTP_HOST']
];

// 3. Проверка webhook файла
$webhookPath = __DIR__ . '/telegram_webhook.php';
$checks['webhook_file'] = [
    'status' => file_exists($webhookPath),
    'message' => file_exists($webhookPath) 
        ? '✅ Файл telegram_webhook.php найден' 
        : '❌ Файл telegram_webhook.php не найден'
];

// 4. Проверка прав доступа
if (file_exists($webhookPath)) {
    $perms = substr(sprintf('%o', fileperms($webhookPath)), -4);
    $checks['permissions'] = [
        'status' => is_readable($webhookPath),
        'message' => "📋 Права доступа: {$perms}"
    ];
}

// 5. Проверка SSL сертификата
$checks['ssl'] = [
    'status' => true,
    'message' => '🔒 SSL: ' . (isset($_SERVER['SSL_PROTOCOL']) ? $_SERVER['SSL_PROTOCOL'] : 'Информация недоступна')
];

// 6. Проверка доступности из внешней сети
$externalCheck = @file_get_contents('https://typo-grafia.ru/telegram_webhook.php');
$checks['external_access'] = [
    'status' => $externalCheck !== false,
    'message' => $externalCheck !== false 
        ? '✅ Webhook доступен из внешней сети' 
        : '❌ Webhook недоступен из внешней сети'
];

// Результат
$allOk = true;
foreach ($checks as $check) {
    if (!$check['status']) {
        $allOk = false;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Проверка Webhook</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .check {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .check.ok {
            background: #d4edda;
            border-color: #28a745;
        }
        .check.error {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .summary {
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .summary.ok {
            background: #d4edda;
            color: #155724;
        }
        .summary.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Диагностика Telegram Webhook</h1>
        
        <?php foreach ($checks as $name => $check): ?>
            <div class="check <?= $check['status'] ? 'ok' : 'error' ?>">
                <?= $check['message'] ?>
            </div>
        <?php endforeach; ?>
        
        <div class="summary <?= $allOk ? 'ok' : 'error' ?>">
            <?= $allOk 
                ? '✅ Все проверки пройдены! Webhook готов к работе.' 
                : '❌ Обнаружены проблемы. Исправьте их и попробуйте снова.' 
            ?>
        </div>
        
        <?php if (!$allOk): ?>
        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>💡 Рекомендации:</h3>
            <ul>
                <li>Убедитесь, что домен typo-grafia.ru доступен извне</li>
                <li>Проверьте, что SSL сертификат валиден</li>
                <li>Убедитесь, что файл telegram_webhook.php существует в корне сайта</li>
                <li>Проверьте права доступа к файлу (должны быть 644)</li>
                <li>Проверьте firewall и не блокирует ли он Telegram сервера</li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>