<?php

namespace Legacy\IblockController;

use Legacy\HighLoadBlock\Notifications;
use Legacy\Iblock\NewsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\SectionTable;

class News
{
    private static function processData($query)
    {
        $result = [];

        while ($arr = $query->fetch()) {
            $result[] = [
                'id' => $arr['ID'],
                'code' => $arr['CODE'],
                'title' => $arr['TITLE_VALUE'],
                'date' => $arr['ACTIVE_FROM'] ? $arr['ACTIVE_FROM']->format('c') : null,
                'image' => getFilePath($arr['IMAGE_VALUE']),
                'description' =>\Bitrix\Main\Web\Json::decode($arr['PREVIEW_DESCRIPTION_VALUE'] ?? '{}')['blocks'][0]['value'],
            ];
        }

        return $result;
    }

    private static function processDetailData($query)
    {
        $result = null;

        if ($arr = $query->fetch()) {
            $result = [
                'id' => $arr['ID'],
                'code' => $arr['CODE'],
                'title' => $arr['TITLE_VALUE'],
                'date' => $arr['ACTIVE_FROM'] ? $arr['ACTIVE_FROM']->format('c') : null,
                'image' => getFilePath($arr['IMAGE_VALUE']),
                'content' => [],
            ];

            $blocks = \Bitrix\Main\Web\Json::decode($arr['DETAIL_CONTENT_VALUE'] ?? '{}')['blocks'];
            foreach ($blocks as $blockInfo) {
                array_push(
                    $result['content'],
                    ...Pages::getBlocksInfo($blockInfo['iblock_id'], $blockInfo['element_ids'])
                );
            }
        }

        return is_array($result) ? array_change_key_case_recursive($result) : null;
    }

    public static function get($arRequest)
    {
        $result = [
            'count' => 0,
            'items' => [],
            'sections' => []
        ];

        if (Loader::includeModule('iblock')) {
            Notifications::clearUnreadNews();

            $page = (int)$arRequest['page'];
            $limit = (int)$arRequest['limit'];

            list($sections, $filterSections)= self::getSections();
            $currentSection = $arRequest['section'] ?: $sections[0]['code'];

            $q = NewsTable::query()
                ->countTotal(true)
                ->withSelect()
                ->withSection($filterSections[$currentSection])
                ->withOrderByDate('DESC')
                ->setLimit($limit)
                ->withPage($page)
                ->withDateActive()
                ->exec()
            ;

            $result['count'] = $q->getCount();
            $result['items'] = self::processData($q);
            $result['sections'] = $sections;
        }
        return $result;
    }

    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [''];
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = NewsTable::query()
                ->withSelect()
                ->withOrderByDate('ASC')
                ->withFilterByIDs($ids);
            $result = self::processData($q);

        }
        return DataProcessor::sortResultByIDs($result, $ids);
    }

    public static function getByCode($arRequest)
    {
        $code = $arRequest['code'];
        if(!$code) return false;

        $result = null;

        if (Loader::includeModule('iblock')) {
            $q = NewsTable::query()
                ->withDetailSelect()
                ->withFilterByСode($code)
            ;
            $result = self::processDetailData($q);
        }
        return $result;
    }

    private static function getSections()
    {
        $currentDate = new DateTime();

        $q = SectionTable::query()
            ->withDefault(Constants::IB_NEWS)
            ->addFilter(null, [
                'LOGIC' => 'OR',
                '<=ELEMENT.ACTIVE_FROM' => $currentDate,
                'ELEMENT.ACTIVE_FROM' => null,
            ])
            ->addFilter(null, [
                'LOGIC' => 'OR',
                '>=ELEMENT.ACTIVE_TO' => $currentDate,
                'ELEMENT.ACTIVE_TO' => null,
            ])
            ->withElementsCount()
            ->withOrderByDepth('DESC')
            ->withOrderBySort('ASC')
            ->exec()
        ;

        return Section::getContentSections($q);
    }
}
