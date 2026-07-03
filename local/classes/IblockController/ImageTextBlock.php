<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\IblockPropertiesTable;
use Legacy\General\Constants;


class ImageTextBlock
{
    private static function processData($query)
    {
        $arrayPropsCodes = [''];
        $enumPropsCodes = ['TEMPLATE', 'BACKGROUND_COLOR'];
        $filePropsCodes = [];
        $sprintEditorPropsCodes = ['IMAGE_TEXT', 'SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'filePropsCodes' => $filePropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$block) {
            foreach ($block['IMAGE_TEXT'] as $imageText){
                $block['items'][] = [
                    'image' => $imageText['file']['ORIGIN_SRC'],
                    'text' => $imageText['description']
                ];
            }
            $block['LINK'] = Links::getById(['id' => $block['LINK']]);
            $block['FORM'] = Forms::getById(['id' => $block['FORM']]);
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            unset($block['IMAGE_TEXT']);
        }

        return $result;
    }
    public static function getByIds($arRequest)
    {
        $ids = empty($arRequest['ids']) ? [''] : $arRequest['ids'];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_IMAGE_TEXT_BLOCK)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return array_values($result);
    }
}
