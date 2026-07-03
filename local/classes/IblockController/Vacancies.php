<?php

namespace Legacy\IblockController;

use \Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\HighLoadBlock\FormFields;
use Legacy\Iblock\VacanciesTable;

class Vacancies
{
    private static function processData($query)
    {
        $result = [];
        $result = DataProcessor::processIBProperties($query, [], true);
        foreach ($result as &$block) {
            $block['id'] = $block['INFO']['ID'];
            $block['name'] = $block['VACANCY_TITLE'];
            $block['preview_description'] = \Bitrix\Main\Web\Json::decode($block['DESCRIPTION_ANNOUNCEMENT'] ?? '{}')['blocks']['0']['value'] ?? '';
            $block['description'] = \Bitrix\Main\Web\Json::decode($block['DESCRIPTION'] ?? '{}')['blocks'][0]['value'] ?? '';
            $block['FORM'] = Forms::getById(['id'=>$block['FORM']]);
            unset($block['INFO'], $block['VACANCY_TITLE'], $block['DESCRIPTION_ANNOUNCEMENT'], $block['DESCRIPTION']);
        }
        return array_change_key_case_recursive($result, CASE_LOWER);
    }


    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = VacanciesTable::query()
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
            $q = VacanciesTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
