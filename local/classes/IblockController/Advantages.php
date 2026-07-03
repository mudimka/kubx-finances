<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\AdvantagesTable;

class Advantages
{
    private static function processData($query)
    {
        $result = DataProcessor::processIBProperties($query, [], true);
        foreach ($result as &$block) {
            $block['id'] = $block['INFO']['ID'];
            $block['subtitle'] = $block['INFO']['PREVIEW_TEXT'];
            $block['img'] = $block['INFO']['PREVIEW_PICTURE'];
            unset($block['INFO']);
        }
        return array_change_key_case_recursive($result, CASE_LOWER);
    }


    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = AdvantagesTable::query()
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
            $q = AdvantagesTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);

        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}