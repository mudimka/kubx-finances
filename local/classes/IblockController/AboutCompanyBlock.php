<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\IblockPropertiesTable;

class AboutCompanyBlock
{
    private static function processData($query)
    {
        $arrayPropsCodes = [];
        $enumPropsCodes = ['TEMPLATE', 'BACKGROUND_COLOR'];
        $filePropsCodes = ['IMAGE'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'filePropsCodes' => $filePropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$block) {
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            $block['LINK'] = Links::getById(['id' => $block['LINK']]);
            $block['FORM'] = Forms::getById(['id' => $block['FORM']]);
        }

        return $result;
    }
    public static function getByIds($arRequest)
    {
        $ids = empty($arRequest['ids']) ? [''] : $arRequest['ids'];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_ABOUT_COMPANY_BLOCK)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return array_values($result);
    }
}
