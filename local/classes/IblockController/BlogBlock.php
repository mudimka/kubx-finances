<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\IblockPropertiesTable;
use Legacy\General\Constants;

class BlogBlock
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['BLOG_POSTS'];
        $enumPropsCodes = ['TEMPLATE', 'BACKGROUND_COLOR'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$block) {
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            $block['items'] = !empty($block['BLOG_POSTS']) ? Blog::getByIDs(['ids' => $block['BLOG_POSTS']]) : [];
            $block['LINK'] = Links::getById(['id' => $block['LINK']]);
            $block['FORM'] = Forms::getById(['id' => $block['FORM']]);
            unset($block['BLOG_POSTS']);
        }

        return array_values($result);
    }

    public static function getByIds($arRequest)
    {
        $ids = empty($arRequest['ids']) ? [''] : $arRequest['ids'];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_BLOG_BLOCK)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return $result;
    }
}