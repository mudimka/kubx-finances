<?php

namespace Legacy\Iblock;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;

class TestNewTable extends ElementTable
{
    public static function getTableName()
    {
        return 'b_iblock_element';
    }

    public static function getIblockId()
    {
        return Constants::IB_TEST_NEW;
    }

    public static function query()
    {
        Loader::includeModule('iblock');
        return new \Bitrix\Main\Entity\Query(static::class);
    }

    public static function withSelect($query = null)
    {
        if (!$query) {
            $query = static::query();
        }

        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'ACTIVE_FROM',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'IMAGE_VALUE' => 'PREVIEW_PICTURE',
            'TITLE_VALUE' => 'NAME',
            'BADGE_VALUE' => 'PREVIEW_TEXT',
            'PREVIEW_DESCRIPTION_VALUE' => 'PREVIEW_TEXT',
            'DETAIL_CONTENT_VALUE' => 'DETAIL_TEXT',
        ]);

        return $query;
    }

    public static function withDetailSelect($query = null)
    {
        if (!$query) {
            $query = static::query();
        }

        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'ACTIVE_FROM',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'IMAGE_VALUE' => 'PREVIEW_PICTURE',
            'TITLE_VALUE' => 'NAME',
            'BADGE_VALUE' => 'PREVIEW_TEXT',
            'PREVIEW_DESCRIPTION_VALUE' => 'PREVIEW_TEXT',
            'DETAIL_CONTENT_VALUE' => 'DETAIL_TEXT',
        ]);

        return $query;
    }

    public static function withFilterByСode($query, $code)
    {
        $query->where('CODE', $code);
        $query->where('ACTIVE', 'Y');
        return $query;
    }

    public static function withOrderByDate($query, $order = 'DESC')
    {
        $query->setOrder(['ACTIVE_FROM' => $order, 'ID' => $order]);
        return $query;
    }

    public static function withDateActive($query)
    {
        $query->where('ACTIVE', 'Y');
        return $query;
    }

    public static function withPage($query, $page, $limit)
    {
        if ($page > 0 && $limit > 0) {
            $query->setLimit($limit);
            $query->setOffset(($page - 1) * $limit);
        }
        return $query;
    }

    public static function withFilterByIDs($query, $ids)
    {
        if (!empty($ids)) {
            $query->whereIn('ID', $ids);
        }
        return $query;
    }
}
