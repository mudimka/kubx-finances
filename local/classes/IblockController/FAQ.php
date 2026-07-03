<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\FAQTable;

class FAQ
{
    private static function processData($query)
    {
        $htmlPropsCodes = ['ANSWER'];
        return DataProcessor::processIBProperties($query, ['htmlPropsCodes' => $htmlPropsCodes]);
    }


    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = FAQTable::query()
                ->withSelect()
                ->withOrderBySort('ASC');
            $result = self::processData($q);
        }

        return $result;
    }

    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [''];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = FAQTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
