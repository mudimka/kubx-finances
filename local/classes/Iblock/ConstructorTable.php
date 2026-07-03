<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Iblock\PropertyTable;

class ConstructorTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("IBLOCK_ID", Constants::IB_CLOTHES_CONSTRUCTOR)
            ->where("ACTIVE", true)
        ;
    }

    public static function withElementsSelect(Query $query)
    {
        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'PREVIEW_PICTURE',
            'SECTION_CODE' => 'IBLOCK_SECTION.CODE',
            'SECTION_NAME' => 'IBLOCK_SECTION.NAME',
        ]);

    }

    public static function withPropertiesSelect(Query $query)
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
            'PROPERTY_PROPERTY',
            new ReferenceField(
                'PROPERTY_PROPERTY',
                PropertyTable::class,
                [
                    'this.PROPERTY.IBLOCK_PROPERTY_ID' => 'ref.ID',
                ]
            )
        );

        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'PREVIEW_PICTURE',
            'PROPERTY_CODE' => 'PROPERTY.IBLOCK_PROPERTY.CODE',
            'PROPERTY_NAME' => 'PROPERTY.IBLOCK_PROPERTY.NAME',
            'PROPERTY_VALUE' => 'PROPERTY.VALUE',
            'PROPERTY_DESCRIPTION' => 'PROPERTY.DESCRIPTION',

            'PROPERTY_TYPE' => 'PROPERTY_PROPERTY.PROPERTY_TYPE',
            'PROPERTY_USER_TYPE' => 'PROPERTY_PROPERTY.USER_TYPE',
            'PROPERTY_SORT' => 'PROPERTY_PROPERTY.SORT',
        ]);

    }

    public static function withSectionCode(Query $query, string $code)
    {
        if (mb_strlen($code) > 0 && $code !== 'all') {
            $query->where('IBLOCK_SECTION.CODE', $code);
        }
    }

    public static function withOrderBySort(Query $query, $sort)
    {
        $query->addOrder('SORT', $sort);
    }
}