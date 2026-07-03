<?php

use Bitrix\Main\EventManager;
use Legacy\EventHandlers\IBlockHandler;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler("iblock", "OnAfterIBlockElementAdd", [IBlockHandler::class, 'HandleElementsAfterAdd']);

$eventManager->addEventHandler("iblock", "OnAfterIBlockElementUpdate", 'ClearLegacyCacheHandler');
$eventManager->addEventHandler("iblock", "OnAfterIBlockSectionAdd", 'ClearLegacyCacheHandler');
$eventManager->addEventHandler("iblock", "OnAfterIBlockSectionUpdate", 'ClearLegacyCacheHandler');


function ClearLegacyCacheHandler(&$arFields)
{
    $cache = \Bitrix\Main\Data\Cache::createInstance();

    $alias = [
        'catalog_crm' => [
            'catalog',
            'product',
        ],
    ];

    $iblock = \Bitrix\Iblock\IblockTable::getById($arFields['IBLOCK_ID'])->fetch();
    $code = mb_strtolower($iblock['CODE']);

    if ($alias = $alias[$code]) {
        if (is_array($alias)) {
            foreach ($alias as $code) {
                $cache->cleanDir("legacy/$code");
            }
        } else {
            $cache->cleanDir("legacy/$alias");
        }
    } else {
        $cache->cleanDir("legacy/$code");
    }
}