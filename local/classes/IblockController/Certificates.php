<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\IblockPropertiesTable;
use Legacy\General\Constants;

class Certificates
{
    private static function processData($query)
    {
        $fileInfoPropsCodes = ['FILE'];
        $sprintEditorPropsCodes = ['DESCRIPTION'];

        $result = DataProcessor::processIBProperties($query, ['fileInfoPropsCodes' => $fileInfoPropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes], true);

        foreach ($result as &$item) {
            $title = null;
            if($item['TITLE']){
                $title = $item['TITLE'];
            }
            elseif($item['FILE']) {
                $title = $item['FILE']['name'].'.'.$item['FILE']['type'];
            } elseif($item['INFO']['PREVIEW_PICTURE_INFO']){
                $title = $item['INFO']['PREVIEW_PICTURE_INFO']['name'].'.'.$item['INFO']['PREVIEW_PICTURE_INFO']['type'];
            }
            $item['title'] = $title;
            $item['description'] = $item['DESCRIPTION'][0]['value'];

            $item['img'] = $item['INFO']['PREVIEW_PICTURE'];

            $item['file'] = $item['FILE'] ? $item['FILE']['url'] : $item['INFO']['PREVIEW_PICTURE_INFO']['url'];
            $item['file_size'] = $item['FILE'] ? $item['FILE']['size'] : $item['INFO']['PREVIEW_PICTURE_INFO']['size'];

            unset($item['INFO']);
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
                ->withIblock(Constants::IB_CERTIFICATES)
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
