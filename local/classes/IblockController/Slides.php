<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\SliderTable;

class Slides
{
    private static function processData($query)
    {
        $result = DataProcessor::processIBProperties($query, [], true);
        foreach ($result as &$block) {
            $block['id'] = $block['INFO']['ID'];
            $block['title'] = $block['INFO']['NAME'];
            $block['subtitle'] = $block['INFO']['PREVIEW_TEXT'];
            $block['image'] = [
                'desktop' => $block['INFO']['PREVIEW_PICTURE'],
                'mobile' => $block['INFO']['DETAIL_PICTURE'],
            ];
            $block['button_form'] = Forms::getById(['id'=>$block['BUTTON_FORM']]);
            $block['button_link'] = Links::getById(['id'=>$block['BUTTON_LINK']]);
            unset($block['INFO'], $block['BUTTON_FORM'], $block['BUTTON_LINK']);
        }

        return $result;
    }


    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = SliderTable::query()
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
            $q = SliderTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);

        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
