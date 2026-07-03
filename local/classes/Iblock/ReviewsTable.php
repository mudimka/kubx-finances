<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;

class ReviewsTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("IBLOCK_ID", Constants::IB_REVIEWS)
            ->where("ACTIVE", true)
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
}
