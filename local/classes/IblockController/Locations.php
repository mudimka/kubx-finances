<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\IblockPropertiesTable;

class Locations
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['CONTACTS', 'LINKS', 'PREVIEW_CONTACTS'];
        $enumPropsCodes = ['TEMPLATE'];
        $filePropsCodes = ['IMAGE'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'filePropsCodes' => $filePropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as $id => &$block) {
            $block['id'] = $id;
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            $block['MAP'] = explode(',', $block['MAP']);
            $block['CONTACTS'] = Contacts::getByIds(['ids' => $block['CONTACTS']]);
            $block['PREVIEW_CONTACTS'] = Contacts::getByIds(['ids' => $block['PREVIEW_CONTACTS']]);
            $block['LINKS'] = Links::getByIds(['ids' => $block['LINKS']]);
            $block['NEARBY_LINK'] = Links::getById(['id' => $block['NEARBY_LINK']]);
        }

        return $result;
    }
    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_LOCATIONS)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
