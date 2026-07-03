<?php
/**
 * Детальная диагностика подключения к Bitrix24
 * Запускать: php local/modules/kubx.chat/debug-connection.php
 */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Kubx\Chat\Config;
use Bitrix\Main\Loader;

echo "🔍 ДИАГНОСТИКА ПОДКЛЮЧЕНИЯ К BITRIX24\n";
echo str_repeat('=', 70) . "\n\n";

// Проверка модуля
if (!Loader::includeModule('kubx.chat')) {
    echo "❌ Модуль kubx.chat не установлен!\n";
    exit(1);
}

echo "✅ Модуль kubx.chat подключен\n\n";

// Шаг 1: Проверка конфигурации
echo "📋 ШАГ 1: Проверка конфигурации\n";
echo str_repeat('-', 70) . "\n";

$webhookUrl = Config::getWebhookUrl();
echo "Webhook URL: " . ($webhookUrl ?: '(не задан)') . "\n";

if (empty($webhookUrl)) {
    echo "\n❌ ОШИБКА: Webhook URL не настроен!\n";
    echo "Решение: Проверьте local/modules/kubx.chat/.settings.php\n";
    exit(1);
}

echo "Default Manager ID: " . Config::getDefaultManagerId() . "\n";
echo "Logging: " . (Config::isLoggingEnabled() ? 'Enabled' : 'Disabled') . "\n\n";

// Шаг 2: Парсинг URL
echo "📋 ШАГ 2: Парсинг webhook URL\n";
echo str_repeat('-', 70) . "\n";

$parsed = parse_url($webhookUrl);
echo "Scheme: " . ($parsed['scheme'] ?? '(нет)') . "\n";
echo "Host: " . ($parsed['host'] ?? '(нет)') . "\n";
echo "Path: " . ($parsed['path'] ?? '(нет)') . "\n\n";

// Шаг 3: Тест DNS
echo "📋 ШАГ 3: Проверка DNS\n";
echo str_repeat('-', 70) . "\n";

$host = $parsed['host'] ?? '';
if ($host) {
    $ip = gethostbyname($host);
    if ($ip === $host) {
        echo "❌ DNS не разрешается для {$host}\n";
        echo "Возможная причина: проблема с DNS или домен недоступен\n\n";
    } else {
        echo "✅ DNS OK: {$host} → {$ip}\n\n";
    }
}

// Шаг 4: Тест подключения (простой)
echo "📋 ШАГ 4: Тест HTTP подключения\n";
echo str_repeat('-', 70) . "\n";

$testUrl = rtrim($webhookUrl, '/') . '/app.info.json';
echo "URL: {$testUrl}\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Для самоподписанных сертификатов
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Захват verbose вывода
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$info = curl_getinfo($ch);

curl_close($ch);

echo "HTTP Code: " . ($httpCode ?: 'N/A') . "\n";
echo "Error: " . ($error ?: 'Нет') . "\n";
echo "Total Time: " . ($info['total_time'] ?? 0) . " sec\n";
echo "DNS Time: " . ($info['namelookup_time'] ?? 0) . " sec\n";
echo "Connect Time: " . ($info['connect_time'] ?? 0) . " sec\n\n";

if ($httpCode == 200) {
    echo "✅ HTTP подключение успешно!\n\n";
    echo "Ответ:\n";
    $result = json_decode($response, true);
    print_r($result);
} else {
    echo "❌ HTTP подключение не удалось!\n\n";
    
    if ($error) {
        echo "cURL Error: {$error}\n\n";
    }
    
    echo "Response:\n";
    echo $response . "\n\n";
    
    // Verbose вывод
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    echo "Детальная информация (cURL verbose):\n";
    echo str_repeat('-', 70) . "\n";
    echo $verboseLog . "\n";
}

// Шаг 5: Проверка прав вебхука
if ($httpCode == 200) {
    echo "\n📋 ШАГ 5: Проверка прав вебхука (im.message.add)\n";
    echo str_repeat('-', 70) . "\n";
    
    $testMessageUrl = rtrim($webhookUrl, '/') . '/im.message.add.json';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testMessageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'DIALOG_ID' => 1,
        'MESSAGE' => '🧪 Тест из debug-connection.php',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['result'])) {
        echo "✅ Отправка сообщений работает! ID: " . $result['result'] . "\n";
        echo "Проверьте мессенджер в Bitrix24\n";
    } else {
        echo "❌ Ошибка отправки сообщения:\n";
        print_r($result);
    }
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "📝 Логи сохранены в: /local/logs/kubx_chat.log\n";
echo str_repeat('=', 70) . "\n";

