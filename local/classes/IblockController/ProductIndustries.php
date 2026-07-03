<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\ProductIndustriesTable;

class ProductIndustries
{
    private static function processData($query)
    {
        $result = [];
        $arrayPropsCodes = ['EXAMPLES'];
        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes], true);
        foreach ($result as &$block) {
            $block['id'] = $block['INFO']['ID'];
            $block['name'] = $block['INFO']['NAME'];
            $block['img'] = $block['INFO']['PREVIEW_PICTURE'];
            $block['items'] = $block['EXAMPLES'];
            unset($block['INFO'], $block['EXAMPLES']);
        }
        return array_change_key_case_recursive($result, CASE_LOWER);
    }

    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = ProductIndustriesTable::query()
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
            $q = ProductIndustriesTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);

        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
