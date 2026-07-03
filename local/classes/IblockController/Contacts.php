<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\ContactsTable;
use Legacy\Iblock\IblockPropertiesTable;
use Legacy\General\Constants;

class Contacts
{
    private static function processData($query)
    {
        $enumPropsCodes = ['TYPE'];
        $result = DataProcessor::processIBProperties($query, [
            'enumPropsCodes' => $enumPropsCodes
        ], true);

        foreach ($result as &$block) {
            $block['id'] = $block['INFO']['ID'];
            $block['name'] = $block['INFO']['NAME'];
            $block['value'] = $block['INFO']['PREVIEW_TEXT'];
            $block['image'] = $block['INFO']['PREVIEW_PICTURE'];
            $block['type'] = $block['TYPE'];
            $block['need_copy'] = getBoolean($block['NEED_COPY']);

            unset($block['INFO'], $block['TYPE'], $block['NEED_COPY']);
        }
        return $result;
    }


    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = ContactsTable::query()
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
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_CONTACTS)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);

        }

        return DataProcessor::sortResultByIDs($result, $ids,true);
    }
}
