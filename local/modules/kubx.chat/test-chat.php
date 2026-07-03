<?php
/**
 * Тестовый файл для проверки работы модуля kubx.chat
 * Запускать из корня сайта: php local/modules/kubx.chat/test-chat.php
 */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Kubx\Chat\ChatManager;
use Kubx\Chat\Config;
use Bitrix\Main\Loader;

echo "🔍 KUBX Chat Manager - Тест подключения\n";
echo str_repeat('=', 60) . "\n\n";

// Проверка установки модуля
if (!Loader::includeModule('kubx.chat')) {
    echo "❌ Модуль kubx.chat не установлен!\n";
    echo "Установите модуль через /bitrix/admin/\n";
    exit(1);
}

echo "✅ Модуль kubx.chat подключен\n\n";

// Проверка конфигурации
echo "📋 Конфигурация:\n";
echo "  - Webhook URL: " . Config::getWebhookUrl() . "\n";
echo "  - Default Manager ID: " . Config::getDefaultManagerId() . "\n";
echo "  - Logging: " . (Config::isLoggingEnabled() ? 'Enabled' : 'Disabled') . "\n\n";

// Проверка подключения к Bitrix24
echo "🔗 Проверка подключения к Bitrix24...\n";

$chatManager = new ChatManager();

if ($chatManager->testConnection()) {
    echo "✅ Подключение успешно!\n\n";
} else {
    echo "❌ Ошибка подключения к Bitrix24\n";
    echo "Проверьте:\n";
    echo "  1. Правильность webhook URL\n";
    echo "  2. Права вебхука (im, user)\n";
    echo "  3. Доступность домена crm.kubx.tech\n";
    exit(1);
}

// Отправка тестового сообщения
echo "📤 Отправка тестового сообщения менеджеру...\n";

$result = $chatManager->sendMessage(1, "🧪 Тестовое сообщение из модуля kubx.chat\nВремя: " . date('Y-m-d H:i:s'));

if ($result['success']) {
    echo "✅ Сообщение отправлено! ID: " . $result['messageId'] . "\n";
    echo "Проверьте мессенджер в Bitrix24\n";
} else {
    echo "❌ Ошибка отправки: " . ($result['error'] ?? 'Unknown error') . "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Тест завершен!\n";
echo "\n📚 Документация: /local/modules/kubx.chat/README.md\n";
echo "📝 Логи: /local/logs/kubx_chat.log\n";

