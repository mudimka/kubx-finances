<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}

?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="kubx.chat">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">
    
    <p>
        ✅ Модуль <strong>KUBX Chat Manager</strong> успешно установлен!
    </p>
    
    <p>
        📝 <strong>Следующие шаги:</strong>
    </p>
    <ol>
        <li>Добавьте пользовательское поле <code>UF_MANAGER_ID</code> для пользователей (необязательно)</li>
        <li>Проверьте настройки в <code>.settings.php</code> модуля</li>
        <li>Добавьте компонент чата в личный кабинет</li>
        <li>Протестируйте: <code>/local/modules/kubx.chat/test-chat.php</code></li>
    </ol>
    
    <p>
        <input type="submit" name="" value="Вернуться в список">
    </p>
</form>

