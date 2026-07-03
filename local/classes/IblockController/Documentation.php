<?php

namespace Legacy\IblockController;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Legacy\General\DataProcessor;
use Legacy\Iblock\DocumentationTable;

class Documentation
{
    private static function processData($query, $isSimple)
    {
        $result = DataProcessor::processIBProperties($query, [], true);
        foreach ($result as &$block) {
            $block['id'] = $block['INFO']['ID'];
            $block['name']=$block['INFO']['NAME'];
            $block['code']=$block['INFO']['CODE'];

            if(!$isSimple) {
                $block['text_information'] = \Bitrix\Main\Web\Json::decode($block['TEXT_INFORMATION'])['blocks'][0]['value'];
                $block['images'] = \Bitrix\Main\Web\Json::decode($block['IMAGES'])['blocks'][0]['images'];
                unset($block['']);
            }
            unset($block['INFO'],$block['TEXT_INFORMATION'],$block['IMAGES']);
        }
        return $result;

    }
    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = DocumentationTable::query()
                ->withSelect()
                ->withOrderBySort('ASC');
            $result['documentation_info'] = current(self::processData($q, false));
            $result['documentation'] = self::getDocumentation([]);
            $result['seo'] = SEO::get(['page' => 'docs', 'is_page' => true]);
        }

        return $result;
    }

    public static function getDocumentation($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = DocumentationTable::query()
                ->withSimpleSelect()
                ->withOrderBySort('ASC');
            $result = self::processData($q, true);
        }

        return $result;
    }

    public static function getById($arRequest)
    {

        $id = $arRequest['id'];
        if(!$id) return false;
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = DocumentationTable::query()
                ->withSelect()
                ->withFilterByIDs($id);
            $result['documentation_info'] = current(self::processData($q));
            $result['documentation'] = self::getDocumentation([]);
            $result['seo'] = SEO::get(['page' => 'docs', 'is_page' => true]);
        }

        return $result;
    }

    public static function getPreviewByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [];
        if(!$ids) return [];
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = DocumentationTable::query()
                ->withSimpleSelect()
                ->withFilterByIDs($ids);

            $result = self::processData($q,true);
        }

        return DataProcessor::sortResultByIDs($result,$ids,true);
    }
}
