<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\IblockPropertiesTable;

class ContactsBlock
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['CONTACTS'];
        $enumPropsCodes = ['TEMPLATE', 'BACKGROUND_COLOR', 'SHOW_ICON_BACKGROUND'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$block) {
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            $block['MAP'] = Maps::getById(['id' => $block['MAP']]);
            $block['ITEMS'] = Contacts::getByIds(['ids' => $block['CONTACTS']]);
            $block['LINK'] = Links::getById(['id' => $block['LINK']]);
            $block['FORM'] = Forms::getById(['id' => $block['FORM']]);
            $block['SHOW_ICON_BACKGROUND'] = getBoolean($block['SHOW_ICON_BACKGROUND']);
            unset($block['CONTACTS']);
        }

        return array_values($result);
    }
    public static function getByIds($arRequest)
    {
        $ids = empty($arRequest['ids']) ? [''] : $arRequest['ids'];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_CONTACTS_BLOCK)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return $result;
    }
}
