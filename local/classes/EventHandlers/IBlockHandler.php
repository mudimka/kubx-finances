<?php

namespace Legacy\EventHandlers;

use Legacy\HighLoadBlock\Notifications;
use Legacy\General\Constants;


class IBlockHandler
{
    public static function HandleElementsAfterAdd(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] == Constants::IB_NEWS) {
            Notifications::addNews($arFields['ID']);
        }
        ClearLegacyCacheHandler($arFields);
    }
}
