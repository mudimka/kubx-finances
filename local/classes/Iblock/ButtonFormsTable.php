<?php

namespace Legacy\Iblock;

use Legacy\Catalog\PropertyTable;
use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use Legacy\HighLoadBlock\Entity;

class ButtonFormsTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("IBLOCK_ID", Constants::IB_FORMS)
            ->where("ACTIVE", true);
    }

    public static function withSelect(Query $query)
    {
        $query->registerRuntimeField(
            'TYPE',
            new ReferenceField(
                'TYPE',
                ElementPropertyTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_FORMS_TYPE),
                ]
            )
        );

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
        $query->setSelect([
            'ID',
            'NAME',
            'FORM_TYPE' => 'TYPE.VALUE',
            'PROPERTY_' => 'PROPERTY',
            'PROPERTY_CODE' => 'PROPERTY.IBLOCK_PROPERTY.CODE',
        ]);

    }

    public static function withOrderBySort(Query $query, $sort)
    {
        $query->addOrder('SORT', $sort);
    }

    public static function withFilterByIDs(Query $query, $id)
    {
        $query->whereIn('ID', $id);
    }

    public static function withFilterByCode(Query $query, $code)
    {
        $query->where('CODE', $code);
    }
}
