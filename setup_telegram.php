<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/TelegramNotifier.php';

echo "🤖 Настройка Telegram бота...\n\n";

$telegram = new TelegramNotifier();

// 1. Проверяем информацию о боте
echo "1️⃣ Проверка информации о боте:\n";
$botInfo = $telegram->getBotInfo();
if ($botInfo['success']) {
    $bot = $botInfo['result'];
    echo "   ✅ Бот подключен!\n";
    echo "   📛 Имя: {$bot['first_name']}\n";
    echo "   🔤 Username: @{$bot['username']}\n";
    echo "   🆔 ID: {$bot['id']}\n\n";
} else {
    echo "   ❌ Ошибка: " . $botInfo['message'] . "\n";
    exit(1);
}

// 2. Удаляем старый webhook (если был)
echo "2️⃣ Удаление старого webhook:\n";
$deleteResult = $telegram->deleteWebhook();
if ($deleteResult['success']) {
    echo "   ✅ Старый webhook удален\n\n";
} else {
    echo "   ⚠️ " . $deleteResult['message'] . "\n\n";
}

// 3. Устанавливаем новый webhook
echo "3️⃣ Установка нового webhook:\n";
$webhookUrl = SITE_URL . '/telegram_webhook.php';
echo "   📍 URL: {$webhookUrl}\n";

$setResult = $telegram->setWebhook($webhookUrl);
if ($setResult['success']) {
    echo "   ✅ Webhook успешно установлен!\n\n";
} else {
    echo "   ❌ Ошибка: " . $setResult['message'] . "\n";
    exit(1);
}

// 4. Проверяем статус webhook
echo "4️⃣ Проверка статуса webhook:\n";
$webhookInfo = $telegram->getWebhookInfo();
if ($webhookInfo['success']) {
    $info = $webhookInfo['result'];
    echo "   URL: " . ($info['url'] ?: 'не установлен') . "\n";
    echo "   Pending updates: " . $info['pending_update_count'] . "\n";
    if (isset($info['last_error_message'])) {
        echo "   ⚠️ Последняя ошибка: " . $info['last_error_message'] . "\n";
        echo "   Время: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
    } else {
        echo "   ✅ Ошибок нет\n";
    }
    echo "\n";
}

// 5. Отправляем тестовое сообщение
echo "5️⃣ Отправка тестового сообщения:\n";
$testMessage = "🎉 <b>Бот успешно настроен!</b>\n\n";
$testMessage .= "✅ Webhook активен\n";
$testMessage .= "✅ Группа подключена\n";
$testMessage .= "✅ Готов к приему сообщений от клиентов\n\n";
$testMessage .= "📊 <b>Информация:</b>\n";
$testMessage .= "🤖 Бот: @{$bot['username']}\n";
$testMessage .= "🌐 Webhook: {$webhookUrl}\n";
$testMessage .= "📅 Дата: " . date('Y-m-d H:i:s');

$sendResult = $telegram->sendMessage($testMessage);
if ($sendResult['success']) {
    echo "   ✅ Тестовое сообщение отправлено в группу!\n";
    echo "   Проверьте Telegram группу.\n\n";
} else {
    echo "   ❌ Ошибка отправки: " . $sendResult['message'] . "\n\n";
}

echo "✨ Настройка завершена!\n";
echo "\n📋 Следующие шаги:\n";
echo "1. Проверьте тестовое сообщение в Telegram группе\n";
echo "2. Убедитесь, что Topics (топики) включены в группе\n";
echo "3. Проверьте, что бот является администратором\n";
echo "4. Попробуйте отправить тестовое сообщение от клиента\n";
?>