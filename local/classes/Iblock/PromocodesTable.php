<?php

namespace Legacy\Iblock;
use Legacy\General\Constants;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
class PromocodesTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query){
        $query
            ->where("IBLOCK_ID", Constants::IB_PROMOCODES)
            ->where("ACTIVE", true);

    }

    public static function withSelect(Query $query)
    {
        $query->registerRuntimeField(
            'PROPERTY',
            new ReferenceField(
                'PROPERTY',
                ElementPropertyTable::class,
                ['this.ID' => 'ref.IBLOCK_ELEMENT_ID']
            ));
        $query->setSelect([
            'ID',
            'NAME',
            'ACTIVE_FROM',
            'ACTIVE_TO',
            'PROPERTY_' => 'PROPERTY',
            'PROPERTY_CODE' => 'PROPERTY.IBLOCK_PROPERTY.CODE',
        ]);
    }
    public static function withFilterByIDs(Query $query, $ids)
    {
        $query->whereIn('ID', $ids);
    }

    public static function withFilterByID(Query $query, $id)
    {
        $query->where('ID', $id);
    }
    public static function withOrderBySort($query, $sort)
    {
        $query->addOrder('SORT', $sort);
    }
}
