<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class kubx_chat extends CModule
{
    public $MODULE_ID = 'kubx.chat';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = 'KUBX Chat Manager';
        $this->MODULE_DESCRIPTION = 'Интеграция чата с менеджерами через Bitrix24';

        $this->PARTNER_NAME = 'KUBX';
        $this->PARTNER_URI = 'https://kubx.tech';
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallDB();
            $this->InstallFiles();
        } else {
            $APPLICATION->ThrowException(
                'Требуется версия главного модуля не ниже 14.00.00'
            );
        }

        $APPLICATION->IncludeAdminFile(
            'Установка модуля ' . $this->MODULE_NAME,
            __DIR__ . '/step.php'
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallDB();
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            'Удаление модуля ' . $this->MODULE_NAME,
            __DIR__ . '/unstep.php'
        );
    }

    public function InstallDB()
    {
        // Сохраняем дефолтные настройки в БД
        \Bitrix\Main\Config\Option::set(
            $this->MODULE_ID,
            'bitrix24_webhook_url',
            'https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/'
        );
        
        \Bitrix\Main\Config\Option::set(
            $this->MODULE_ID,
            'default_manager_id',
            '1'
        );
        
        \Bitrix\Main\Config\Option::set(
            $this->MODULE_ID,
            'enable_logging',
            'Y'
        );
        
        return true;
    }

    public function UnInstallDB()
    {
        // Очистка настроек
        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
        return true;
    }

    public function InstallFiles()
    {
        // Копируем AJAX файлы
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/ajax/',
            $_SERVER['DOCUMENT_ROOT'] . '/local/ajax/kubx.chat/',
            true,
            true
        );
        
        // Копируем административные файлы
        if (is_dir(__DIR__ . '/../admin/')) {
            CopyDirFiles(
                $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/admin/',
                $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/',
                true,
                true
            );
        }
        
        return true;
    }

    public function UnInstallFiles()
    {
        // Удаляем AJAX файлы
        DeleteDirFilesEx('/local/ajax/kubx.chat/');
        
        // Удаляем административные файлы
        if (is_dir(__DIR__ . '/../admin/')) {
            DeleteDirFiles(
                $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/admin/',
                $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/'
            );
        }
        
        return true;
    }
}

