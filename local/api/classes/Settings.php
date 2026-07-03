<?php

namespace Legacy\API;

use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Iblock\SectionTable;
use Legacy\General\Constants;
use Bitrix\Main\Application;
use Legacy\IblockController\Settings as SettingsController;

class Settings
{
    public static function get($arRequest)
    {
        return SettingsController::get();
    }

    public static function getSwagger($arRequest)
    {
        return SettingsController::getSwagger();
    }
}
