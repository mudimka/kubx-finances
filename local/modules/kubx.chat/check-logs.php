<?php
/**
 * Проверка логов kubx.chat
 * Запускать: php local/modules/kubx.chat/check-logs.php
 */

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/kubx_chat.log';

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              📝 ПРОВЕРКА ЛОГОВ KUBX.CHAT                 ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

if (!file_exists($logFile)) {
    echo "❌ Файл логов не найден: {$logFile}\n";
    echo "   Логирование отключено или модуль еще не использовался.\n";
    exit(1);
}

$fileSize = filesize($logFile);
$lastModified = date('Y-m-d H:i:s', filemtime($logFile));

echo "📁 Файл: {$logFile}\n";
echo "📊 Размер: " . round($fileSize / 1024, 2) . " KB\n";
echo "🕐 Последнее изменение: {$lastModified}\n\n";

echo str_repeat('=', 60) . "\n";
echo "ПОСЛЕДНИЕ 50 СТРОК ЛОГА:\n";
echo str_repeat('=', 60) . "\n\n";

$lines = file($logFile);
$lastLines = array_slice($lines, -50);

foreach ($lastLines as $line) {
    // Подсветка ошибок
    if (stripos($line, 'error') !== false) {
        echo "\033[31m" . $line . "\033[0m"; // Красный
    } elseif (stripos($line, 'success') !== false) {
        echo "\033[32m" . $line . "\033[0m"; // Зеленый
    } else {
        echo $line;
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "📝 Полный лог: {$logFile}\n";
echo str_repeat('=', 60) . "\n";

