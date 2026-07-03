<?php
/**
 * Страница настроек модуля в админке
 * Путь: Настройки → Настройки продукта → Настройки модулей → KUBX Chat Manager
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

if (!$USER->IsAdmin()) {
    return;
}

$moduleId = 'kubx.chat';

Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl('tabControl', [
    [
        'DIV' => 'edit1',
        'TAB' => 'Настройки',
        'TITLE' => 'Настройки подключения к Bitrix24',
    ],
    [
        'DIV' => 'edit2',
        'TAB' => 'Тест',
        'TITLE' => 'Тестирование подключения',
    ],
]);

// Сохранение настроек
if ($REQUEST_METHOD === 'POST' && strlen($_POST['save']) > 0 && check_bitrix_sessid()) {
    Option::set($moduleId, 'bitrix24_webhook_url', $_POST['bitrix24_webhook_url']);
    Option::set($moduleId, 'default_manager_id', $_POST['default_manager_id']);
    Option::set($moduleId, 'enable_logging', isset($_POST['enable_logging']) ? 'Y' : 'N');
    
    CAdminMessage::ShowNote('Настройки сохранены');
}

// Тест подключения
$testResult = '';
if ($REQUEST_METHOD === 'POST' && strlen($_POST['test']) > 0 && check_bitrix_sessid()) {
    if (Loader::includeModule($moduleId)) {
        $chatManager = new \Kubx\Chat\ChatManager();
        
        if ($chatManager->testConnection()) {
            $testResult = '<div style="color: green; font-weight: bold;">✅ Подключение успешно!</div>';
        } else {
            $testResult = '<div style="color: red; font-weight: bold;">❌ Ошибка подключения. Проверьте логи.</div>';
        }
    }
}

// Получение текущих настроек
$bitrix24WebhookUrl = Option::get($moduleId, 'bitrix24_webhook_url', '');
$defaultManagerId = Option::get($moduleId, 'default_manager_id', '1');
$enableLogging = Option::get($moduleId, 'enable_logging', 'Y');

?>

<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($moduleId) ?>&lang=<?= LANGUAGE_ID ?>">
    <?= bitrix_sessid_post() ?>
    
    <?php $tabControl->Begin(); ?>
    
    <?php $tabControl->BeginNextTab(); ?>
    
    <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <label for="bitrix24_webhook_url">Webhook URL Bitrix24:</label>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
            <input type="text" 
                   id="bitrix24_webhook_url"
                   name="bitrix24_webhook_url" 
                   value="<?= htmlspecialchars($bitrix24WebhookUrl) ?>" 
                   size="80"
                   placeholder="https://your-portal.bitrix24.ru/rest/1/xxxxx/">
            <br>
            <small style="color: #666;">
                Получите вебхук в Bitrix24: Настройки → Разработчикам → Другое → Входящий вебхук<br>
                Необходимые права: <strong>im</strong> (Мессенджер), <strong>user</strong> (Пользователи)
            </small>
        </td>
    </tr>
    
    <tr>
        <td class="adm-detail-content-cell-l">
            <label for="default_manager_id">ID менеджера по умолчанию:</label>
        </td>
        <td class="adm-detail-content-cell-r">
            <input type="number" 
                   id="default_manager_id"
                   name="default_manager_id" 
                   value="<?= htmlspecialchars($defaultManagerId) ?>" 
                   size="10"
                   min="1">
            <br>
            <small style="color: #666;">
                ID пользователя в Bitrix24, который будет получать сообщения от клиентов
            </small>
        </td>
    </tr>
    
    <tr>
        <td class="adm-detail-content-cell-l">
            <label for="enable_logging">Включить логирование:</label>
        </td>
        <td class="adm-detail-content-cell-r">
            <input type="checkbox" 
                   id="enable_logging"
                   name="enable_logging" 
                   <?= $enableLogging === 'Y' ? 'checked' : '' ?>>
            <br>
            <small style="color: #666;">
                Логи сохраняются в <code>/local/logs/kubx_chat.log</code>
            </small>
        </td>
    </tr>
    
    <?php $tabControl->BeginNextTab(); ?>
    
    <tr>
        <td colspan="2" style="padding: 20px;">
            <h3>Тестирование подключения</h3>
            
            <?php if ($testResult): ?>
                <div style="padding: 10px; margin: 10px 0; background: #f5f5f5; border-left: 4px solid #0066ff;">
                    <?= $testResult ?>
                </div>
            <?php endif; ?>
            
            <p>
                <input type="submit" name="test" value="Проверить подключение" class="adm-btn">
            </p>
            
            <hr style="margin: 20px 0;">
            
            <h4>Информация о настройках:</h4>
            <ul>
                <li><strong>Webhook URL:</strong> <?= htmlspecialchars($bitrix24WebhookUrl ?: '(не задан)') ?></li>
                <li><strong>Manager ID:</strong> <?= htmlspecialchars($defaultManagerId) ?></li>
                <li><strong>Logging:</strong> <?= $enableLogging === 'Y' ? 'Включено' : 'Выключено' ?></li>
            </ul>
            
            <hr style="margin: 20px 0;">
            
            <h4>AJAX API endpoints:</h4>
            <ul>
                <li><code>GET /local/ajax/kubx.chat/chat.php?action=test</code> - Тест подключения</li>
                <li><code>GET /local/ajax/kubx.chat/chat.php?action=init</code> - Инициализация чата</li>
                <li><code>POST /local/ajax/kubx.chat/chat.php</code> (action=send) - Отправка сообщения</li>
            </ul>
            
            <hr style="margin: 20px 0;">
            
            <h4>Полезные ссылки:</h4>
            <ul>
                <li><a href="/local/modules/kubx.chat/README.md" target="_blank">📚 Документация</a></li>
                <li><a href="/local/modules/kubx.chat/QUICKSTART.md" target="_blank">🚀 Быстрый старт</a></li>
                <li><a href="https://dev.1c-bitrix.ru/rest_help/im/index.php" target="_blank">📖 Bitrix24 REST API</a></li>
            </ul>
        </td>
    </tr>
    
    <?php $tabControl->Buttons(); ?>
    
    <input type="submit" 
           name="save" 
           value="Сохранить" 
           class="adm-btn-save">
    
    <input type="reset" 
           name="reset" 
           value="Сбросить">
    
    <?php $tabControl->End(); ?>
</form>

