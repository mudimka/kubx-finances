<?php

namespace Legacy\IblockController;

use Legacy\General\DataProcessor;
use Legacy\Iblock\PromocodesTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Legacy\General\Constants;
use Bitrix\Iblock\ElementTable;

class Promocodes
{
    private static function processData($query)
    {
        $htmlPropsCodes = ['DESCRIPTION'];
        $result = DataProcessor::processIBProperties($query, ['htmlPropsCodes' => $htmlPropsCodes], true);
        foreach ($result as &$block) {
            $block['active_from'] = ($block['INFO']['ACTIVE_FROM']) ?
                $block['INFO']['ACTIVE_FROM']->format('c') : $block['INFO']['ACTIVE_FROM'];
            $block['active_to'] = ($block['INFO']['ACTIVE_TO']) ?
                $block['INFO']['ACTIVE_TO']->format('c') : $block['INFO']['ACTIVE_TO'];
            unset($block['INFO']);
        }
        return array_change_key_case_recursive($result, CASE_LOWER);
    }

    public static function get()
    {
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = PromocodesTable::query()
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

            $q = PromocodesTable::query()
                ->withSelect()
                ->withOrderBySort('ASC')
                ->withFilterByIDs($ids);
            $result = self::processData($q);

        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }

    public static function getById($arRequest)
    {
        $id = $arRequest['id'] ?? [''];
        $result = [];
        if (Loader::includeModule('iblock')) {

            $q = PromocodesTable::query()
                ->withSelect()
                ->withFilterByID($id);
            $result = current(self::processData($q));
        }

        return $result;
    }
}
