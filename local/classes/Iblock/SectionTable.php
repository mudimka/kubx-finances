<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Type\DateTime;

class SectionTable extends \Bitrix\Iblock\SectionTable
{
    public static function setDefaultScope($query)
    {
        $query->where("ACTIVE", true);
    }

    public static function withDefault(Query $query, $iblockID)
    {
        $query->where('IBLOCK_ID', $iblockID);

        $query->setSelect([
            'ID',
            'NAME',
            'CODE',
            'IBLOCK_SECTION_ID',
            'DEPTH_LEVEL',
        ]);
    }

    public static function withOrderByDepth(Query $query, $order)
    {
        $query->addOrder('DEPTH_LEVEL', $order);
    }

    public static function withOrderBySort(Query $query, $order)
    {
        $query->addOrder('SORT', $order);
    }

    public static function withElementsCount(Query $query)
    {
        $query->registerRuntimeField(
            'ELEMENT',
            new ReferenceField(
                'ELEMENT',
                ElementTable::class,
                ['this.ID' => 'ref.IBLOCK_SECTION_ID']
            )
        );

        $query->addSelect(new ExpressionField('ELEMENTS_COUNT', 'COUNT(%s)', ['ELEMENT.ID']));
    }
}