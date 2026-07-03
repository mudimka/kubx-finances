<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;

class ReturnProductRequestsTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("IBLOCK_ID", Constants::IB_RETURN_PRODUCTS)
            ->where("ACTIVE", true)
        ;
    }

    public static function withFilterByOrderID(Query $query, $id)
    {
        $query->registerRuntimeField(
            'ORDER_ID',
            new ReferenceField(
                'ORDER_ID',
                ElementPropertyTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_RETURN_PRODUCTS_ORDER_ID),
                ]
            )
        );

        $query->where('ORDER_ID.VALUE', $id);
    }
}
