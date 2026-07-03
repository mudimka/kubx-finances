<?php
use Bitrix\Main\Localization\Loc;

if (!defined('KUBX_SETTINGS')) {
    define('KUBX_SETTINGS', 'kubx.settings');
}

Loc::loadMessages(__FILE__);

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'main',
    'OnBuildGlobalMenu',
    function (&$arGlobalMenu) {

        if (!isset($arGlobalMenu['global_menu_kubx'])) {
            $arGlobalMenu['global_menu_kubx'] = [
                'menu_id' => 'global_menu_kubx',
                'text' => Loc::getMessage('KUBX_SETTINGS_MENU_MODULE_NAME'),
                'title' => Loc::getMessage('KUBX_SETTINGS_MENU_MODULE_FULL_NAME'),
                'sort' => 2000,
                'items_id' => 'global_menu_kubx_settings_items',
            ];
        } else {
            $arGlobalMenu['global_menu_kubx'] = [
                'text' => Loc::getMessage('KUBX_SETTINGS_MENU_KUBX'),
                'title' => Loc::getMessage('KUBX_SETTINGS_MENU_KUBX'),
            ];
        }

        if (isset($arMenu)) {
            $arGlobalMenu['global_menu_kubx']['items'][KUBX_SETTINGS] = $arMenu;
        }
    }
);

$aMenu = [
    [
        'parent_menu' => 'global_menu_kubx',
        'section' => 'KUBX_SETTINGS',
        'text' => Loc::getMessage('KUBX_SETTINGS_MENU_MODULE_NAME_LONG'),
        'title' => Loc::getMessage('KUBX_SETTINGS_MENU_MODULE_FULL_NAME'),
        'sort' => 500,
        'icon' => 'util_menu_icon',
        'page_icon' => 'KUBX_SETTINGS',
        'items_id' => 'menu_KUBX_SETTINGS_items',
        'module_id' => 'kubx.settings',
        'url' => sprintf('/bitrix/admin/settings.php?mid=%s&lang=%s&mid_menu=1&tabControl_active_tab=%s', KUBX_SETTINGS, urlencode(LANGUAGE_ID), 'settings'),
    ],
];

return $aMenu;
