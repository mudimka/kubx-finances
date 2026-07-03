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
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    
    <p>
        ✅ Модуль <strong>KUBX Chat Manager</strong> успешно удален!
    </p>
    
    <p>
        <input type="submit" name="" value="Вернуться в список">
    </p>
</form>

