<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;

class ProductsTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("IBLOCK_ID", Constants::IB_PRODUCTS)
            ->where("ACTIVE", true)
        ;
    }

    public static function withSelect(Query $query)
    {
        $query->registerRuntimeField(
            'IMAGES',
            new ReferenceField(
                'BUTTON',
                ElementPropertyTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_PRODUCTS_IMAGES),
                ]
            )
        );

        $query->setSelect([
            'ID',
            'NAME',
            'PREVIEW_TEXT',
            'SECTION_ID' => 'IBLOCK_SECTION.ID',
            'SECTION_CODE' => 'IBLOCK_SECTION.CODE',
            'SECTION_NAME' => 'IBLOCK_SECTION.NAME',
            'GALLERY' => 'IMAGES.VALUE',
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