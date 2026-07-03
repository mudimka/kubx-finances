<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\LinksTable;

class Links
{
    private static function processData($query)
    {
        $result = DataProcessor::processIBProperties($query, [],true);
        foreach ($result as &$block) {
            $block['img'] = $block['INFO']['PREVIEW_PICTURE'];
            $block['is_anchor'] = getBoolean($block['IS_ANCHOR']);
            $block['open_in_new_window'] = getBoolean($block['OPEN_IN_NEW_WINDOW']);
            unset($block['INFO']);
        }
        return array_change_key_case_recursive($result);
    }


    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = LinksTable::query()
                ->withSelect()
                ->withOrderBySort('ASC');
            $result = self::processData($q);
        }
        return $result;
    }

    public static function getById($arRequest)
    {
        $id = $arRequest['id'];
        if(!$id) return false;

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = LinksTable::query()
                ->withSelect()
                ->withFilterByIDs($id);
            $result = self::processData($q);

        }

        return current($result);
    }

    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [''];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = LinksTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);

        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
