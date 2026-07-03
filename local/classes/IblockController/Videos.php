<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\IblockPropertiesTable;
use Legacy\General\Constants;

class Videos
{
    private static function processData($query)
    {
        $filePropsCodes = ['IMAGE', 'FILE'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, [
            'filePropsCodes' => $filePropsCodes,
            'sprintEditorPropsCodes' => $sprintEditorPropsCodes,
        ]);

        foreach ($result as &$item) {
            $item['SUBTITLE'] = $item['SUBTITLE'][0]['value'];
        }
        return array_change_key_case_recursive($result);
    }

    public static function getById($arRequest)
    {
        $id = $arRequest['id'];

        if (!$id) return null;

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withSelect()
                ->withIblock(Constants::IB_VIDEOS)
                ->withFilterByIDs($id);
            $result = current(self::processData($q));
        }

        return $result;
    }

    public static function getByIds($arRequest)
    {

        $ids = $arRequest['ids'] ?? [''];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withSelect()
                ->withIblock(Constants::IB_VIDEOS)
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
