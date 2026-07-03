<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\IblockPropertiesTable;

class AdvantagesBlock
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['ADVANTAGES'];
        $enumPropsCodes = ['TEMPLATE', 'BACKGROUND_COLOR', 'SHOW_ICON_BACKGROUND'];
        $filePropsCodes = ['IMAGE'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'filePropsCodes' => $filePropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$block) {
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            $block['ITEMS'] = Advantages::getByIds(['ids' => $block['ADVANTAGES']]);
            $block['LINK'] = Links::getById(['id' => $block['LINK']]);
            $block['FORM'] = Forms::getById(['id' => $block['FORM']]);
            $block['SHOW_ICON_BACKGROUND'] = getBoolean($block['SHOW_ICON_BACKGROUND']);
            unset($block['ADVANTAGES']);
        }

        return array_values($result);
    }
    public static function getByIds($arRequest)
    {
        $ids = empty($arRequest['ids']) ? [''] : $arRequest['ids'];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_ADVANTAGES_BLOCK)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return $result;
    }
}
