<?php

namespace Legacy\Catalog;

use Legacy\General\Constants;
use Legacy\Iblock\ElementPropertyTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\PriceTable;

class FilterTable extends ProductTable
{
    public static function withDefault(Query $query, $properties)
    {
        $query->addFilter("@TYPE", [ProductTable::TYPE_OFFER, ProductTable::TYPE_PRODUCT]);

        $query->registerRuntimeField(
            'ELEMENT',
            new ReferenceField(
                'ELEMENT',
                ElementTable::class,
                [
                    'this.ID' => 'ref.ID',
                ]
            )
        );
        $query->addFilter("ELEMENT.ACTIVE", true);

        $query->registerRuntimeField(
            'PRICE',
            new ReferenceField(
                'PRICE',
                PriceTable::class,
                [
                    'this.ID' => 'ref.PRODUCT_ID',
                ]
            )
        );

        $query->registerRuntimeField(
            'CML2_LINK',
            new ReferenceField(
                'CML2_LINK',
                ElementPropertyTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_CML2_LINK),
                ]
            )
        );
        $query->registerRuntimeField(
            'REAL_ELEMENT',
            new ReferenceField(
                'REAL_ELEMENT',
                ElementTable::class,
                [
                    'this.CML2_LINK.VALUE' => 'ref.ID',
                ]
            )
        );

        $propertyFilter = [
            'LOGIC'=>'OR',
        ];
        foreach ($properties as $pcode => $item) {
            $key = 'PROPERTY_'.$pcode;
            $query->registerRuntimeField(
                $key,
                new ReferenceField(
                    $key,
                    ElementPropertyTable::class,
                    [
                        'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                        'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', $item['ID']),
                    ]
                )
            );

            $query->registerRuntimeField(
                $key.'_REAL_ELEMENT',
                new ReferenceField(
                    $key.'_REAL_ELEMENT',
                    ElementPropertyTable::class,
                    [
                        'this.REAL_ELEMENT.ID' => 'ref.IBLOCK_ELEMENT_ID',
                        'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', $item['ID']),
                    ]
                )
            );
            $query->addSelect(new ExpressionField($pcode,
                'CASE WHEN %s IS NOT NULL THEN %s ELSE %s END',
                [$key.'.VALUE', $key.'.VALUE', $key.'_REAL_ELEMENT.VALUE'])
            );
            $propertyFilter['!=='.$pcode] = null;
        }

        $query->addFilter(null, $propertyFilter);
    }

    public static function withFilter(Query $query, array $filter)
    {
        foreach ($filter as $code => $value) {
            if($code == 'price'){
                $query->addFilter(null, [
                    'LOGIC' => 'AND',
                    '>=PRICE.PRICE' => $value[0],
                    '<=PRICE.PRICE' => $value[1],
                ]);
            }
            else{
                $query->addFilter('@'.mb_strtoupper($code), $value);
            }
        }
    }

    public static function withFromCategory(Query $query, $category)
    {
        if ($category !== 'all') {
            $query->registerRuntimeField(
                'CML2_LINK',
                new ReferenceField(
                    'CML2_LINK',
                    ElementPropertyTable::class,
                    [
                        'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                        'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_CML2_LINK),
                    ]
                )
            );
            $query->registerRuntimeField(
                'REAL_ELEMENT',
                new ReferenceField(
                    'REAL_ELEMENT',
                    ElementTable::class,
                    [
                        'this.CML2_LINK.VALUE' => 'ref.ID',
                    ]
                )
            );

            $query->addFilter(null, [
                'LOGIC' => 'OR',
                'ELEMENT.IBLOCK_SECTION.CODE' => $category,
                'REAL_ELEMENT.IBLOCK_SECTION.CODE' => $category
            ]);
        }
    }

    public static function withCache(Query $query, $mode, $time)
    {
        $query->cacheJoins($mode);
        $query->setCacheTtl($time);
    }
}
