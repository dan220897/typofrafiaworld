<?php
// admin/view_logs.php - Просмотр логов отладки
require_once 'config/config.php';

// Проверка авторизации
checkAuth();

// Только для суперадминов
if ($_SESSION['admin_role'] !== 'super_admin') {
    die('Access denied');
}

$log_files = [
    'API Debug Log' => __DIR__ . '/logs/api_debug.log',
    'Service Debug Log' => __DIR__ . '/logs/service_debug.log'
];

$selected_log = $_GET['log'] ?? 'API Debug Log';
$lines = isset($_GET['lines']) ? intval($_GET['lines']) : 100;
$clear = isset($_GET['clear']);

$log_file = $log_files[$selected_log] ?? null;

// Очистка лога
if ($clear && $log_file && file_exists($log_file)) {
    file_put_contents($log_file, '');
    header('Location: view_logs.php?log=' . urlencode($selected_log));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр логов - Административная панель</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .back-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .controls {
            padding: 20px 30px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .controls label {
            font-weight: 500;
            color: #374151;
        }

        .controls select,
        .controls input {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .log-content {
            padding: 20px 30px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
            background: #1f2937;
            color: #f3f4f6;
            max-height: 70vh;
            overflow-y: auto;
        }

        .log-content::-webkit-scrollbar {
            width: 10px;
        }

        .log-content::-webkit-scrollbar-track {
            background: #111827;
        }

        .log-content::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 5px;
        }

        .log-content::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        .no-log {
            padding: 40px;
            text-align: center;
            color: #6b7280;
        }

        .log-info {
            padding: 15px 30px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }

        .success-indicator {
            color: #10b981;
        }

        .error-indicator {
            color: #ef4444;
        }

        .warning-indicator {
            color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Просмотр логов отладки</h1>
            <a href="services.php" class="back-link">← Назад к услугам</a>
        </div>

        <div class="controls">
            <label>Выберите лог:</label>
            <select id="logSelect" onchange="changeLog()">
                <?php foreach ($log_files as $name => $path): ?>
                    <option value="<?php echo htmlspecialchars($name); ?>"
                            <?php echo $selected_log === $name ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Показать строк:</label>
            <input type="number" id="linesInput" value="<?php echo $lines; ?>" min="10" max="10000" step="10">

            <button class="btn btn-primary" onclick="applyFilters()">Применить</button>
            <button class="btn btn-success" onclick="refreshLog()">🔄 Обновить</button>
            <button class="btn btn-danger" onclick="clearLog()">🗑️ Очистить лог</button>
        </div>

        <?php if ($log_file && file_exists($log_file)): ?>
            <?php
            $file_size = filesize($log_file);
            $file_size_kb = round($file_size / 1024, 2);
            $last_modified = date('Y-m-d H:i:s', filemtime($log_file));
            ?>
            <div class="log-info">
                📄 Файл: <strong><?php echo basename($log_file); ?></strong> |
                📊 Размер: <strong><?php echo $file_size_kb; ?> KB</strong> |
                🕒 Последнее изменение: <strong><?php echo $last_modified; ?></strong>
            </div>

            <div class="log-content" id="logContent">
<?php
                if ($file_size > 0) {
                    // Читаем последние N строк
                    $file_content = file($log_file);
                    $total_lines = count($file_content);
                    $start_line = max(0, $total_lines - $lines);
                    $log_lines = array_slice($file_content, $start_line);

                    foreach ($log_lines as $line) {
                        // Подсветка специальных символов
                        if (strpos($line, '✓') !== false) {
                            echo '<span class="success-indicator">' . htmlspecialchars($line) . '</span>';
                        } elseif (strpos($line, '❌') !== false) {
                            echo '<span class="error-indicator">' . htmlspecialchars($line) . '</span>';
                        } elseif (strpos($line, '⚠️') !== false) {
                            echo '<span class="warning-indicator">' . htmlspecialchars($line) . '</span>';
                        } else {
                            echo htmlspecialchars($line);
                        }
                    }
                } else {
                    echo "Лог пуст\n";
                }
?>
            </div>
        <?php else: ?>
            <div class="no-log">
                <p>Файл лога не найден или не выбран</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function changeLog() {
            const logSelect = document.getElementById('logSelect');
            const lines = document.getElementById('linesInput').value;
            window.location.href = `view_logs.php?log=${encodeURIComponent(logSelect.value)}&lines=${lines}`;
        }

        function applyFilters() {
            const logSelect = document.getElementById('logSelect');
            const lines = document.getElementById('linesInput').value;
            window.location.href = `view_logs.php?log=${encodeURIComponent(logSelect.value)}&lines=${lines}`;
        }

        function refreshLog() {
            window.location.reload();
        }

        function clearLog() {
            if (confirm('Вы уверены, что хотите очистить этот лог?')) {
                const logSelect = document.getElementById('logSelect');
                window.location.href = `view_logs.php?log=${encodeURIComponent(logSelect.value)}&clear=1`;
            }
        }

        // Автообновление каждые 5 секунд
        let autoRefresh = setInterval(refreshLog, 5000);

        // Остановка автообновления при клике
        document.getElementById('logContent')?.addEventListener('click', function() {
            clearInterval(autoRefresh);
            console.log('Auto-refresh stopped');
        });

        // Автопрокрутка вниз при загрузке
        window.addEventListener('load', function() {
            const logContent = document.getElementById('logContent');
            if (logContent) {
                logContent.scrollTop = logContent.scrollHeight;
            }
        });
    </script>
</body>
</html>
