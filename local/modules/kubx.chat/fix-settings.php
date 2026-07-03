<?php
/**
 * Скрипт для исправления настроек kubx.chat
 * Запускать: php local/modules/kubx.chat/fix-settings.php
 */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║       🔧 ИСПРАВЛЕНИЕ НАСТРОЕК KUBX.CHAT                  ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Шаг 1: Сохранение настроек
echo "📝 Шаг 1: Сохранение настроек в БД...\n";

Option::set('kubx.chat', 'bitrix24_webhook_url', 'https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/');
Option::set('kubx.chat', 'default_manager_id', '1');
Option::set('kubx.chat', 'enable_logging', 'Y');

echo "✅ Настройки сохранены!\n\n";

// Шаг 2: Проверка настроек
echo "📋 Шаг 2: Проверка сохраненных настроек...\n";
echo str_repeat('-', 60) . "\n";

$webhookUrl = Option::get('kubx.chat', 'bitrix24_webhook_url');
$managerId = Option::get('kubx.chat', 'default_manager_id');
$logging = Option::get('kubx.chat', 'enable_logging');

echo "  Webhook URL: " . ($webhookUrl ?: '(не задан)') . "\n";
echo "  Manager ID: " . ($managerId ?: '(не задан)') . "\n";
echo "  Logging: " . ($logging === 'Y' ? 'Enabled' : 'Disabled') . "\n\n";

if (empty($webhookUrl)) {
    echo "❌ ОШИБКА: Настройки не сохранились!\n";
    exit(1);
}

// Шаг 3: Тест подключения
echo "🔗 Шаг 3: Проверка подключения к Bitrix24...\n";
echo str_repeat('-', 60) . "\n";

if (!Loader::includeModule('kubx.chat')) {
    echo "❌ Модуль kubx.chat не установлен!\n";
    exit(1);
}

use Kubx\Chat\ChatManager;
use Kubx\Chat\Bitrix24Client;

$client = new Bitrix24Client();

// Тест app.info
$testUrl = rtrim($webhookUrl, '/') . '/app.info.json';
echo "Тестируем: {$testUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";

if ($httpCode == 200) {
    echo "✅ Подключение к Bitrix24 успешно!\n\n";
    
    $result = json_decode($response, true);
    if (isset($result['result'])) {
        echo "Информация о портале:\n";
        echo "  - Status: " . ($result['result']['status'] ?? 'N/A') . "\n";
        echo "  - Language: " . ($result['result']['language_id'] ?? 'N/A') . "\n";
    }
} else {
    echo "❌ Ошибка подключения!\n";
    echo "Error: {$error}\n";
    echo "Response: {$response}\n\n";
    
    echo "🔍 Возможные причины:\n";
    echo "  1. Неправильный webhook URL\n";
    echo "  2. Вебхук удален или неактивен в Bitrix24\n";
    echo "  3. Firewall блокирует исходящие HTTPS запросы\n";
    echo "  4. Проблемы с SSL сертификатом\n";
    exit(1);
}

// Шаг 4: Тест отправки сообщения
echo "\n📤 Шаг 4: Тест отправки сообщения...\n";
echo str_repeat('-', 60) . "\n";

$chatManager = new ChatManager();
$result = $chatManager->sendMessage(1, "🧪 Тестовое сообщение после исправления настроек\nВремя: " . date('Y-m-d H:i:s'));

if ($result['success']) {
    echo "✅ Сообщение отправлено! ID: " . $result['messageId'] . "\n";
    echo "\n📱 Проверьте мессенджер в Bitrix24:\n";
    echo "   https://crm.kubx.tech/online/\n";
} else {
    echo "❌ Ошибка отправки: " . ($result['error'] ?? 'Unknown error') . "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ ГОТОВО! Настройки исправлены.\n";
echo "\nТеперь проверьте API:\n";
echo "  curl \"https://demo.kubx.tech/local/ajax/kubx.chat/chat.php?action=test\"\n";
echo "\nОжидаемый результат:\n";
echo "  {\"success\":true,\"connection\":true}\n";
echo str_repeat('=', 60) . "\n";

