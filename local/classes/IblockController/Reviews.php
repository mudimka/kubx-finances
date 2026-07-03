<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\AdvantagesBlockTable;
use Legacy\Iblock\ReviewsTable;

class Reviews
{
    private static function processData($query)
    {
        $arrayPropsCodes = [];
        $filePropsCodes = ['CLIENT_PHOTO'];
        $filesPropsCodes = ['IMAGES'];
        $sprintEditorPropsCodes = ['REVIEW'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'filePropsCodes' => $filePropsCodes, 'filesPropsCodes' => $filesPropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$reviewItem) {
            $reviewItem['REVIEW'] = $reviewItem['REVIEW'][0]['value'];;
        }

        return array_values($result);
    }
    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [''];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = ReviewsTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return $result;
    }
}
