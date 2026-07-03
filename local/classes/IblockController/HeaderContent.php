<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\HeaderContentTable;

class HeaderContent
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['CONTACTS', 'FORMS', 'LINKS'];

        return DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes]);
    }

    public static function get()
    {
        $requestItem = null;
        if (Loader::includeModule('iblock')) {
            $q = HeaderContentTable::query()
                ->withSelect();
            $requestItem = current(self::processData($q));
        }

        return [
            'contacts' => Contacts::getByIds(['ids' => $requestItem['CONTACTS']]),
            'forms' => Forms::getByIds(['ids' => $requestItem['FORMS']]),
            'links' => Links::getByIds(['ids' => $requestItem['LINKS']]),
            'alert' => getBoolean($requestItem['IS_SHOW_ALERT']) ? [
                'text' => $requestItem['ALERT_TEXT'],
                'form' => Forms::getById(['id' => $requestItem['ALERT_FORM']]),
            ] : false,
        ];
    }
}
