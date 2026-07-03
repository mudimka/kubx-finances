<?php

namespace Legacy\IblockController;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Legacy\General\Constants;
use Legacy\Iblock\BlogTable;
use Legacy\Iblock\SectionTable;

class Blog
{
    private static function processData($query)
    {
        $result = [];

        while ($res = $query->fetch()) {
            $result[] = [
                'id' => $res['ID'],
                'code' => $res['CODE'],
                'title' => $res['TITLE'],
                'date' => $res['ACTIVE_FROM'] ? $res['ACTIVE_FROM']->format('c') : null,
                'image' => getFilePath($res['PREVIEW_PICTURE']),
                'description' => \Bitrix\Main\Web\Json::decode($res['PREVIEW_DESCRIPTION'] ?? '{}')['blocks'][0]['value'],
            ];
        }
        return $result;
    }

    private static function processDetailData($query)
    {
        $result = null;

        if ($res = $query->fetch()) {
            $result = [
                'id' => $res['ID'],
                'code' => $res['CODE'],
                'title' => $res['TITLE'],
                'date' => $res['ACTIVE_FROM'] ? $res['ACTIVE_FROM']->format('c') : null,
                'image' => getFilePath($res['DETAIL_PICTURE']),
                'blocks' => [],
            ];

            $blocks = \Bitrix\Main\Web\Json::decode($res['BLOCKS'] ?? '{}')['blocks'];
            foreach ($blocks as $blockInfo) {
                array_push(
                    $result['blocks'],
                    ...Pages::getBlocksInfo($blockInfo['iblock_id'], $blockInfo['element_ids'])
                );
            }

            $recommendedIDs = $res['RECOMMENDED_POSTS'] ? explode('::', $res['RECOMMENDED_POSTS']) : null;
            $result['recommended_posts'] = self::getByIDs(['ids' => $recommendedIDs, 'exclude_ids' => $res['ID']]);
            $result['recent_posts'] = self::getRecent(['exclude_ids' => $res['ID']]);
        }
        return $result;
    }

    public static function get($arRequest)
    {
        $result = [
            'count' => 0,
            'sections' => [],
            'items' => [],
        ];

        if (Loader::includeModule('iblock')) {
            $limit = (int)$arRequest['limit'] ?: 12;
            $page = (int)$arRequest['page'];

            list($sections, $filterSections)= self::getSections();
            $currentSection = $arRequest['section'] ? $filterSections[$arRequest['section']] : 'all';

            $q = BlogTable::query()
                ->countTotal(true)
                ->withSelect()
                ->withOrderBySort('ASC')
                ->withOrderByDate('DESC')
                ->setLimit($limit)
                ->withPage($page)
                ->withSection($currentSection)
                ->withFilterDateActive()
                ->exec()
            ;

            $result['count'] = $q->getCount();
            $result['sections'] = $sections;
            $result['items'] = self::processData($q);
            $result['seo'] = SEO::get(['page' => 'blog', 'is_page' => true]);
        }
        return $result;
    }

    private static function getRecent($arRequest)
    {
        $excludeIds = $arRequest['exclude_ids'] ?: [];
        $limit = 5;

        $result = null;

        if (Loader::includeModule('iblock')) {
            $q = BlogTable::query()
                ->withSelect()
                ->withExcludeID($excludeIds)
                ->withOrderByDate('DESC')
                ->withOrderBySort('ASC')
                ->setLimit($limit)
                ->withFilterDateActive()
                ->exec()
            ;

            $result = self::processData($q);
        }
        return $result;
    }

    public static function getByIDs($arRequest)
    {
        $ids = $arRequest['ids'] ?: [];
        $excludeIds = $arRequest['exclude_ids'] ?: [];
        $limit = !empty($ids) ? count($ids) : 4;

        $result = null;

        if (Loader::includeModule('iblock')) {
            $q = BlogTable::query()
                ->countTotal(true)
                ->withSelect()
                ->withFilterByID($ids)
                ->withExcludeID($excludeIds)
                ->withOrderBySort('ASC')
                ->withOrderByDate('DESC')
                ->setLimit($limit)
                ->withFilterDateActive()
                ->exec()
            ;

            $result = self::processData($q);
        }
        return $result;
    }

    public static function getByCode($arRequest)
    {
        $code = $arRequest['code'];
        if(!$code) return false;

        $result = null;
        if (Loader::includeModule('iblock')) {
            $q = BlogTable::query()
                ->withDetailSelect()
                ->withFilterByСode($code)
                ->withFilterDateActive()
                ->exec()
            ;

            $result = self::processDetailData($q);
            if (!empty($result)) {
                $result['seo'] = SEO::getElementSEO(Constants::IB_BLOG, $result['id']);
            }
        }
        return is_array($result) ? array_change_key_case_recursive($result) : null;
    }

    private static function getSections()
    {
        $currentDate = new DateTime();

        $q = SectionTable::query()
            ->withDefault(Constants::IB_BLOG)
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
