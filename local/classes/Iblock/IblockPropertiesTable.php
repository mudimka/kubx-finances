<?php

namespace Legacy\Iblock;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;

class IblockPropertiesTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("ACTIVE", true)
        ;
    }

    public static function withIblock($query, $iblockId)
    {
        $query
            ->where("IBLOCK_ID", $iblockId)
        ;
    }
    public static function withSelect(Query $query)
    {
        $query->registerRuntimeField(
            'PROPERTY',
            new ReferenceField(
                'PROPERTY',
                ElementPropertyTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                ]
            )
        );

        $query->registerRuntimeField(
            'ENUM',
            new ReferenceField(
                'ENUM',
                PropertyEnumTable::class,
                [
                    'this.PROPERTY.VALUE_ENUM' => 'ref.ID',
                ]
            )
        );

        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'ACTIVE_FROM',
            'ACTIVE_TO',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'PROPERTY_' => 'PROPERTY',
            'PROPERTY_CODE' => 'PROPERTY.IBLOCK_PROPERTY.CODE',
            'ENUM_CODE' => 'ENUM.XML_ID',
        ]);
    }

    public static function withOrderBySort(Query $query, $sort)
    {
        $query->addOrder('SORT', $sort);
    }

    public static function withFilterByIDs(Query $query, $ids)
    {
        $query->whereIn('ID', $ids);
    }

    public static function withFilterByCode(Query $query, $code)
    {
        $query->where('CODE', $code);
    }

    public static function withPage(Query $query, int $page)
    {
        if ($page > 0) {
            $query->setOffset(($page - 1) * $query->getLimit());
        }
    }
    public static function withOrderByDate(Query $query, $order){
        $query->addOrder('ACTIVE_FROM', $order);
    }
    public static function withSectionCode(Query $query, $code)
    {
        if (isset($code)) {
            $query->addFilter('=IBLOCK_SECTION.CODE', $code);
        }
    }
}
