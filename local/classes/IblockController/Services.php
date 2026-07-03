<?php

namespace Legacy\IblockController;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Legacy\General\DataProcessor;
use Legacy\Iblock\IblockPropertiesTable;

class Services
{
    private static function processData($query)
    {
        $sprintEditorPropsCodes = ['PAGE_CONTENT_BLOCKS', 'PREVIEW_DESCRIPTION'];
        $result = DataProcessor::processIBProperties($query, [
            'sprintEditorPropsCodes' => $sprintEditorPropsCodes
        ], true);

        foreach ($result as &$item) {
            $item['id'] = $item['INFO']['ID'];
            $item['code'] = !empty($item['PAGE_CONTENT_BLOCKS']) ? $item['INFO']['CODE'] : null;
            $item['image'] = $item['INFO']['DETAIL_PICTURE'];
            $item['preview_description'] = $item['PREVIEW_DESCRIPTION'][0]['value'];
            $item['form'] = Forms::getById(['id' =>$item['FORM']]);
            unset($item['INFO'], $item['DETAIL_DESCRIPTION'], $item['PREVIEW_DESCRIPTION'], $item['PAGE_CONTENT_BLOCKS']);
        }

        return array_change_key_case_recursive($result);
    }

    private static function processDetail($query)
    {
        $sprintEditorPropsCodes = ['PAGE_CONTENT_BLOCKS', 'DETAIL_DESCRIPTION'];
        $result = DataProcessor::processIBProperties($query, [
            'sprintEditorPropsCodes' => $sprintEditorPropsCodes
        ], true);

        foreach ($result as &$item) {
            $item['id'] = $item['INFO']['ID'];
            $item['image'] = $item['INFO']['DETAIL_PICTURE'];
            $item['detail_description'] = $item['DETAIL_DESCRIPTION'][0]['value'];

            $items = [];
            foreach ($item['PAGE_CONTENT_BLOCKS'] as $blockInfo) {
                array_push(
                    $items,
                    ...Pages::getBlocksInfo($blockInfo['iblock_id'], $blockInfo['element_ids'])
                );
            }
            $item['blocks'] = $items;

            $item['form'] = Forms::getById(['id' =>$item['FORM']]);
            unset($item['PAGE_CONTENT_BLOCKS'], $item['INFO'], $item['DETAIL_DESCRIPTION'], $item['PREVIEW_DESCRIPTION']);
        }

        return array_change_key_case_recursive($result);
    }

    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_SERVICES)
                ->withSelect()
                ->withOrderBySort('ASC')
            ;

            $result = self::processData($q);
        }

        return array_values($result);
    }

    public static function getByCode($arRequest)
    {
        $code = $arRequest['pageCode'];
        if(!$code) return false;
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_SERVICES)
                ->withSelect()
                ->withFilterByCode($code);

            $result = current(self::processDetail($q));

            if(!$result) {
                return false;
            }

            $result['seo'] = SEO::getElementSEO(Constants::IB_SERVICES, $result['id']);
        }

        return $result;
    }

    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [];
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_SERVICES)
                ->withSelect()
                ->withFilterByIDs($ids);

            $result = self::processData($q);
        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }
}
