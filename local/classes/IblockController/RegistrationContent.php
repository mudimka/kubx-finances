<?php

namespace Legacy\IblockController;

use Legacy\General\DataProcessor;
use Legacy\Iblock\RegistrationContentTable;
use Bitrix\Main\Loader;

class RegistrationContent
{

    private static function processData($query)
    {

        $arrayPropsCodes = ['ADVANTAGES'];
        $htmlPropsCodes = ['DESCRIPTION'];

        return DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'htmlPropsCodes' => $htmlPropsCodes]);
    }

    public static function get()
    {
        $requestItem = null;
        if (Loader::includeModule('iblock')) {
            $q = RegistrationContentTable::query()
                ->withSelect();
            $requestItem = current(self::processData($q));
        }
        return [
            'title' => $requestItem['TITLE'],
            'description' => $requestItem['DESCRIPTION'],
            'advantages' => Advantages::getByIds(['ids' => $requestItem['ADVANTAGES']]),
        ];
    }
}