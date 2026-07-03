<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('kubx.chat', [
    'Kubx\\Chat\\Config' => 'lib/Config.php',
    'Kubx\\Chat\\Bitrix24Client' => 'lib/Bitrix24Client.php',
    'Kubx\\Chat\\ChatManager' => 'lib/ChatManager.php',
]);

