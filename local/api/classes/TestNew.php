<?php

namespace Legacy\API;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\TestNewTable;

class TestNew
{
    private static function processData($query)
    {
        $result = [];

        while ($arr = $query->fetch()) {
            $result[] = [
                'id'          => $arr['ID'],
                'code'        => $arr['CODE'],
                'title'       => $arr['NAME'],
                'date'        => $arr['ACTIVE_FROM'] ? $arr['ACTIVE_FROM']->format('c') : null,
                'description' => $arr['PREVIEW_TEXT'],
            ];
        }

        return $result;
    }

    private static function processDetailData($query)
    {
        $result = [];

        if ($arr = $query->fetch()) {
            $result = [
                'id'          => $arr['ID'],
                'code'        => $arr['CODE'],
                'title'       => $arr['NAME'],
                'date'        => $arr['ACTIVE_FROM'] ? $arr['ACTIVE_FROM']->format('c') : null,
                'description' => $arr['PREVIEW_TEXT'],
                'detail'      => $arr['DETAIL_TEXT'],
            ];
        }

        return array_change_key_case_recursive($result);
    }

    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $page  = (int)$arRequest['page'];
            $limit = (int)$arRequest['limit'];

            $q = TestNewTable::query()
                ->countTotal(true)
                ->withSelect()
                ->setLimit($limit)
                ->withPage($page)
                ->withOrderByDate('DESC')
                ->withDateActive()
                ->exec();

            $result['count'] = $q->getCount();
            $result['items'] = self::processData($q);
        }

        return $result;
    }

    public static function getByCode($arRequest)
    {
        $code = $arRequest['code'];

        if (!$code) {
            throw new \Exception('Не передан код элемента');
        }

        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = TestNewTable::query()
                ->withDetailSelect()
                ->withFilterByCode($code)
                ->exec();

            $result = self::processDetailData($q);
        }

        return $result;
    }
}