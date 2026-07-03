<?php
/**
 * Простой тест отправки сообщений в Bitrix24
 * Запускать: php local/modules/kubx.chat/simple-test.php
 */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║         🧪 ПРОСТОЙ ТЕСТ BITRIX24 API                     ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$webhook = 'https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/';

// ============================================================
// ТЕСТ 1: Получение списка пользователей
// ============================================================
echo "📋 ТЕСТ 1: Получение списка активных пользователей\n";
echo str_repeat('-', 60) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook . 'user.get.json?ACTIVE=Y');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: {$error}\n\n";
    exit(1);
}

echo "HTTP Code: {$httpCode}\n\n";

$users = json_decode($response, true);

if (isset($users['result']) && is_array($users['result'])) {
    echo "✅ Найдено пользователей: " . count($users['result']) . "\n\n";
    
    foreach ($users['result'] as $user) {
        $id = $user['ID'] ?? '?';
        $name = ($user['NAME'] ?? '') . ' ' . ($user['LAST_NAME'] ?? '');
        $active = ($user['ACTIVE'] ?? false) ? '✅' : '❌';
        
        echo "  [{$active}] ID: {$id} - {$name}\n";
    }
    
    // Запоминаем ID первого пользователя для теста
    $firstUserId = $users['result'][0]['ID'] ?? null;
    
} else {
    echo "❌ Ошибка получения пользователей:\n";
    print_r($users);
    echo "\n";
    exit(1);
}

echo "\n";

// ============================================================
// ТЕСТ 2: Проверка прав вебхука
// ============================================================
echo "🔑 ТЕСТ 2: Проверка прав вебхука\n";
echo str_repeat('-', 60) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook . 'app.info.json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
curl_close($ch);

$appInfo = json_decode($response, true);

if (isset($appInfo['result'])) {
    echo "Информация о портале:\n";
    echo "  - Status: " . ($appInfo['result']['status'] ?? 'N/A') . "\n";
    echo "  - Language: " . ($appInfo['result']['language_id'] ?? 'N/A') . "\n";
    
    // Проверяем есть ли информация о правах
    if (isset($appInfo['result']['license'])) {
        echo "  - License: " . $appInfo['result']['license'] . "\n";
    }
} else {
    echo "⚠️  Не удалось получить информацию о портале\n";
}

echo "\n";

// ============================================================
// ТЕСТ 3: Отправка сообщения пользователю ID=1
// ============================================================
echo "📤 ТЕСТ 3: Отправка сообщения пользователю ID=1\n";
echo str_repeat('-', 60) . "\n";

$testUserId = 1;
echo "Получатель: USER ID = {$testUserId}\n";
echo "Сообщение: Простое тестовое сообщение\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook . 'im.message.add.json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'DIALOG_ID' => $testUserId,
    'MESSAGE' => 'Простое тестовое сообщение без форматирования',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";

$result = json_decode($response, true);

if (isset($result['result'])) {
    echo "✅ Сообщение отправлено успешно!\n";
    echo "   Message ID: {$result['result']}\n";
} else {
    echo "❌ Ошибка отправки сообщения:\n";
    
    if (isset($result['error'])) {
        echo "   Error Code: {$result['error']}\n";
        echo "   Error Description: " . ($result['error_description'] ?? 'N/A') . "\n";
    }
    
    echo "\nПолный ответ:\n";
    print_r($result);
}

echo "\n";

// ============================================================
// ТЕСТ 4: Отправка сообщения первому найденному пользователю
// ============================================================
if (isset($firstUserId) && $firstUserId != 1) {
    echo "📤 ТЕСТ 4: Отправка сообщения первому пользователю ID={$firstUserId}\n";
    echo str_repeat('-', 60) . "\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook . 'im.message.add.json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'DIALOG_ID' => $firstUserId,
        'MESSAGE' => 'Тестовое сообщение для пользователя ID=' . $firstUserId,
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['result'])) {
        echo "✅ Сообщение отправлено успешно!\n";
        echo "   Message ID: {$result['result']}\n";
    } else {
        echo "❌ Ошибка отправки:\n";
        if (isset($result['error_description'])) {
            echo "   {$result['error_description']}\n";
        }
    }
    
    echo "\n";
}

// ============================================================
// ТЕСТ 5: Отправка с форматированием (как в модуле)
// ============================================================
echo "📤 ТЕСТ 5: Отправка с форматированием (как в модуле)\n";
echo str_repeat('-', 60) . "\n";

$testMessage = "💬 *Тестовый клиент*:\nТестовое сообщение с форматированием";
echo "Сообщение:\n{$testMessage}\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook . 'im.message.add.json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'DIALOG_ID' => $firstUserId ?? 1,
    'MESSAGE' => $testMessage,
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['result'])) {
    echo "✅ Сообщение с форматированием отправлено!\n";
    echo "   Message ID: {$result['result']}\n";
} else {
    echo "❌ Ошибка отправки:\n";
    if (isset($result['error_description'])) {
        echo "   {$result['error_description']}\n";
    }
}

echo "\n";

// ============================================================
// РЕЗЮМЕ
// ============================================================
echo str_repeat('=', 60) . "\n";
echo "📊 РЕЗЮМЕ\n";
echo str_repeat('=', 60) . "\n\n";

echo "✅ Что проверили:\n";
echo "  1. Получение списка пользователей\n";
echo "  2. Информацию о портале\n";
echo "  3. Отправку простого сообщения\n";
echo "  4. Отправку с форматированием\n\n";

echo "💡 Рекомендации:\n";
echo "  - Если все тесты ✅ - проблема в модуле\n";
echo "  - Если ТЕСТ 3 ❌ - пользователь ID=1 не существует\n";
echo "  - Если все ❌ - проверьте права вебхука (im)\n\n";

echo "🔍 Проверьте мессенджер Bitrix24:\n";
echo "   https://crm.kubx.tech/online/\n\n";

echo str_repeat('=', 60) . "\n";

