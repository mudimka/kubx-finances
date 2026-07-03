<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class kubx_settings extends CModule
{
    var $MODULE_ID = 'kubx.settings';

    function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__ . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('KUBX_SETTINGS_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('KUBX_SETTINGS_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('KUBX_SETTINGS_PARTNER_NAME');
        $this->PARTNER_URI = 'https://legacystudio.ru/';
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        Loader::includeModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->InstallEvents();
    }

    public function InstallEvents()
    {
        $eventManager = Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            'iblock',
            \Bitrix\Iblock\Model\PropertyFeature::class.'::OnPropertyFeatureBuildList',
            $this->MODULE_ID,
            'Kubx\Settings\Events\PropertyFeature',
            'OnPropertyFeatureBuildList',
            100,
            '',
            []
        );
    }

    public function DoUninstall()
    {
        Loader::includeModule($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallFiles();
    }

    public function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/kubx.settings/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
    }

    public function UnInstallFiles()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/kubx");
    }
}
