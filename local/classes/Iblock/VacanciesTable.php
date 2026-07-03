<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;

class VacanciesTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("IBLOCK_ID", Constants::IB_VACANCIES)
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
                ['this.ID' => 'ref.IBLOCK_ELEMENT_ID']
            ));
//        $query->registerRuntimeField(
//            'VACANCY_TITLE',
//            new ReferenceField(
//                'VACANCY_TITLE',
//                ElementPropertyTable::class,
//                [
//                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
//                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_VACANCIES_VACANCY_TITLE),
//                ]
//            )
//        );

//        $query->registerRuntimeField(
//            'DESCRIPTION_ANNOUNCEMENT',
//            new ReferenceField(
//                'DESCRIPTION_ANNOUNCEMENT',
//                ElementPropertyTable::class,
//                [
//                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
//                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_VACANCIES_DESCRIPTION_ANNOUNCEMENT),
//                ]
//            )
//        );
//
//        $query->registerRuntimeField(
//            'DESCRIPTION',
//            new ReferenceField(
//                'DESCRIPTION',
//                ElementPropertyTable::class,
//                [
//                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
//                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_VACANCIES_DESCRIPTION),
//                ]
//            )
//        );
//
//        $query->registerRuntimeField(
//            'FORM',
//            new ReferenceField(
//                'FORM',
//                ElementPropertyTable::class,
//                [
//                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
//                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_VACANCIES_FORM),
//                ]
//            )
//        );

        $query->setSelect([
            'ID',
            'NAME',
            'PROPERTY_' => 'PROPERTY',
            'PROPERTY_CODE' => 'PROPERTY.IBLOCK_PROPERTY.CODE',
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
