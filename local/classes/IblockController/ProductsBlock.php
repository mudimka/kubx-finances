<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\IblockPropertiesTable;
use Legacy\General\Constants;

class ProductsBlock
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['PRODUCTS', 'PRODUCTS_OFFERS'];
        $enumPropsCodes = ['TEMPLATE', 'BACKGROUND_COLOR'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$block) {
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            $block['ITEMS'] = [
                ...($block['PRODUCTS'] ?? []),
                ...($block['PRODUCTS_OFFERS'] ?? [])
            ];
            $block['LINK'] = Links::getById(['id' => $block['LINK']]);
            $block['FORM'] = Forms::getById(['id' => $block['FORM']]);
            unset($block['PRODUCTS']);
            unset($block['PRODUCTS_OFFERS']);
        }

        return array_values($result);
    }
    public static function getByIds($arRequest)
    {
        $ids = empty($arRequest['ids']) ? [''] : $arRequest['ids'];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_PRODUCTS_BLOCK)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return $result;
    }
}
