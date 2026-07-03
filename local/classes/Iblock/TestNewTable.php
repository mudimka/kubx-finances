<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Type\DateTime;

class TestNewTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where('IBLOCK_ID', Constants::IB_TEST_NEW)
            ->where('ACTIVE', true);
    }

    public static function withSelect(Query $query)
    {
        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'ACTIVE_FROM',
            'PREVIEW_TEXT',
        ]);
    }

    public static function withDetailSelect(Query $query)
    {
        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'ACTIVE_FROM',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
        ]);
    }

    public static function withFilterByIDs(Query $query, $ids)
    {
        $query->whereIn('ID', $ids);
    }

    public static function withFilterByCode(Query $query, $code)
    {
        $query->where('CODE', $code);
    }

    public static function withOrderByDate(Query $query, $order)
    {
        $query->addOrder('ACTIVE_FROM', $order);
    }

    public static function withPage(Query $query, int $page)
    {
        if ($page > 0) {
            $query->setOffset(($page - 1) * $query->getLimit());
        }
    }

    public static function withDateActive(Query $query)
    {
        $dt = new DateTime();
        $query->addFilter(null, [
            'LOGIC' => 'OR',
            '<=ACTIVE_FROM' => $dt,
            'ACTIVE_FROM' => null,
        ]);
        $query->addFilter(null, [
            'LOGIC' => 'OR',
            '>=ACTIVE_TO' => $dt,
            'ACTIVE_TO' => null,
        ]);
    }
}