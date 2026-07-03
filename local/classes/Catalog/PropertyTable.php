<?php

namespace Legacy\Catalog;

use Legacy\General\Constants;
use Legacy\Iblock\ElementPropertyTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\SectionPropertyTable;

class PropertyTable extends \Bitrix\Iblock\PropertyTable
{
    public static function setDefaultScope($query)
    {
        $query->setFilter(['ACTIVE' => true]);
    }

    public static function withProperties(Query $query)
    {
        $query->setSelect([
            'ID',
            'IBLOCK_ID',
            'CODE',
            'NAME',
            'SORT',
            'PROPERTY_TYPE',
            'MULTIPLE',
            'USER_TYPE',
            'USER_TYPE_SETTINGS',
        ]);
        $query->setFilter(['ACTIVE' => true]);
    }

    public static function withOfferPropsOnly(Query $query) {
        $query->setSelect([
            'ID',
            'CODE',
            'NAME',
            'SORT',
            'USER_TYPE_SETTINGS_LIST',
        ]);
        $query->setFilter(['ACTIVE' => true]);

        $query->registerRuntimeField(
            'PROPERTY_FEATURE',
            new ReferenceField(
                'PROPERTY_FEATURE',
                PropertyFeatureTable::class,
                [
                    'this.ID' => 'ref.PROPERTY_ID'
                ]
            )
        );

        $query->addFilter(null, [
            'LOGIC' => 'OR',
            [
                'LOGIC' => 'AND',
                [
                    'PROPERTY_FEATURE.FEATURE_ID' => 'KUBX_USE_TO_GROUP_OFFERS',
                    'PROPERTY_FEATURE.IS_ENABLED' => 'Y'
                ]
            ],
            [
                'LOGIC' => 'AND',
                [
                    'PROPERTY_FEATURE.FEATURE_ID' => 'KUBX_USE_TO_BUILD_OFFERS',
                    'PROPERTY_FEATURE.IS_ENABLED' => 'Y'
                ]
            ],
        ]);

        $query->addSelect(new ExpressionField('FEATURES', 'GROUP_CONCAT(%s)', ['PROPERTY_FEATURE.FEATURE_ID']));
    }

    public static function withFeatureDetail(Query $query) {
        $query->registerRuntimeField(
            'PROPERTY_FEATURE',
            new ReferenceField(
                'PROPERTY_FEATURE',
                PropertyFeatureTable::class,
                [
                    'this.ID' => 'ref.PROPERTY_ID'
                ]
            )
        );


        $query->addFilter(null, [
            'LOGIC' => 'OR',
            [
                'LOGIC' => 'AND',
                [
                    'PROPERTY_FEATURE.FEATURE_ID' => 'KUBX_USE_IN_DETAIL_TABLE',
                    'PROPERTY_FEATURE.IS_ENABLED' => 'Y'
                ]
            ],
            [
                'LOGIC' => 'AND',
                [
                    'PROPERTY_FEATURE.FEATURE_ID' => 'KUBX_USE_IN_DETAIL',
                    'PROPERTY_FEATURE.IS_ENABLED' => 'Y'
                ]
            ],
            [
                'LOGIC' => 'AND',
                [
                    'PROPERTY_FEATURE.FEATURE_ID' => 'KUBX_USE_TO_BUILD_OFFERS',
                    'PROPERTY_FEATURE.IS_ENABLED' => 'Y'
                ]
            ]
        ]);

        $query->addSelect(new ExpressionField('FEATURES', 'GROUP_CONCAT(DISTINCT CONCAT(%s,":",%s))', ['PROPERTY_FEATURE.FEATURE_ID', 'PROPERTY_FEATURE.IS_ENABLED']));
    }

    public static function withFeatureBasket(Query $query) {
        $query->registerRuntimeField(
            'PROPERTY_FEATURE',
            new ReferenceField(
                'PROPERTY_FEATURE',
                PropertyFeatureTable::class,
                [
                    'this.ID' => 'ref.PROPERTY_ID'
                ]
            )
        );


        $query->addFilter(null, [
            'LOGIC' => 'AND',
            [
                'PROPERTY_FEATURE.FEATURE_ID' => 'KUBX_USE_IN_BASKET',
                'PROPERTY_FEATURE.IS_ENABLED' => 'Y'

            ]
        ]);

        $query->addSelect(new ExpressionField('FEATURES', 'GROUP_CONCAT(DISTINCT CONCAT(%s,":",%s))', ['PROPERTY_FEATURE.FEATURE_ID', 'PROPERTY_FEATURE.IS_ENABLED']));
    }

    public static function withFeatureListing(Query $query) {
        $query->registerRuntimeField(
            'PROPERTY_FEATURE',
            new ReferenceField(
                'PROPERTY_FEATURE',
                PropertyFeatureTable::class,
                [
                    'this.ID' => 'ref.PROPERTY_ID'
                ]
            )
        );


        $query->addFilter(null, [
            'LOGIC' => 'AND',
            [
                'PROPERTY_FEATURE.FEATURE_ID' => 'KUBX_USE_IN_LISTING',
                'PROPERTY_FEATURE.IS_ENABLED' => 'Y'
            ]
        ]);

        $query->addSelect(new ExpressionField('FEATURES', 'GROUP_CONCAT(DISTINCT CONCAT(%s,":",%s))', ['PROPERTY_FEATURE.FEATURE_ID', 'PROPERTY_FEATURE.IS_ENABLED']));
    }


    public static function withAddSelect(Query $query, $value)
    {
        $select = $query->getSelect();
        $query->setSelect(array_merge($select, $value));
    }

    public static function withAddRuntime(Query $query, $values)
    {
        foreach ($values as $key => $value) {
            $query->registerRuntimeField($key, new ReferenceField(
                $key,
                $value['data_type'],
                $value['reference'],
            ));
        }
    }

    public static function withAddOrder(Query $query, $value)
    {
        $order = $query->getOrder();
        $query->setOrder(array_merge($order, $value));
    }

    public static function withRuntimeHighloadBlocks(Query $query)
    {
        $query->setSelect([new ExpressionField('SETTINGS', 'DISTINCT %s', ['USER_TYPE_SETTINGS'])]);
        $query->registerRuntimeField(
            'SECTION_PROPERTY',
            new ReferenceField(
                'SECTION_PROPERTY',
                SectionPropertyTable::class,
                [
                    'this.ID' => 'ref.PROPERTY_ID',
                ]
            )
        );
        $query->setFilter([
            'ACTIVE' => true,
            'SECTION_PROPERTY.SMART_FILTER' => true,
        ]);
    }

    public static function withValues(Query $query, $offerProperties)
    {
        $query->registerRuntimeField(
            'CML2_LINK',
            new ReferenceField(
                'CML2_LINK',
                ElementPropertyTable::class,
                [
                    'this.ELEMENT_PROPERTY.IBLOCK_ELEMENT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_CML2_LINK),
                ]
            )
        );
        $query->addSelect(new ExpressionField(
            'VALUE',
            'CONCAT (CASE WHEN %1$s IS NOT NULL THEN %1$s ELSE %2$s END, "::", %3$s)',
            ['CML2_LINK.VALUE', 'ELEMENT_PROPERTY.IBLOCK_ELEMENT_ID', 'ELEMENT_PROPERTY.VALUE']
        ));
        $query->addGroup('VALUE');

        $query->registerRuntimeField(
            'ENUM_VALUE',
            new ReferenceField(
                'ENUM_VALUE',
                PropertyEnumerationTable::class,
                [
                    'this.ID' => 'ref.PROPERTY_ID',
                    'this.ELEMENT_PROPERTY.VALUE'=> 'ref.ID'
                ]
            )
        );
        $query->addOrder('ENUM_VALUE.SORT', 'ASC');
        $query->addSelect('ENUM_VALUE.VALUE', 'PROPERTY_ENUM_VALUE');
        
        $query->registerRuntimeField(
            'PROPERTY_OFFER_CML2_LINK',
            new ReferenceField(
                'PROPERTY_OFFER_CML2_LINK',
                ElementPropertyTable::class,
                [
                    'this.ELEMENT_PROPERTY.IBLOCK_ELEMENT_ID' => 'ref.VALUE',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_CML2_LINK),
                ]
            )
        );

        $query->registerRuntimeField(
            'OFFER_ID',
            new ExpressionField(
                'OFFER_ID',
                'CASE WHEN %s IS NOT NULL THEN %s ELSE %s END',
                ['PROPERTY_OFFER_CML2_LINK.VALUE', 'PROPERTY_OFFER_CML2_LINK.IBLOCK_ELEMENT_ID', 'ELEMENT_PROPERTY.IBLOCK_ELEMENT_ID']
            )
        );

        foreach ($offerProperties as $property) {
            if (in_array('KUBX_USE_TO_GROUP_OFFERS', $property['FEATURES'])) {
                $code = 'P_' . $property['CODE'];
                $query->registerRuntimeField(
                    $code,
                    new ReferenceField(
                        $code,
                        ElementPropertyTable::class,
                        [
                            'ref.IBLOCK_ELEMENT_ID' => 'this.OFFER_ID',
                            'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', $property['ID']),
                        ]
                    )
                );

                $query->addSelect($code . '.VALUE', $code . ' _VALUE');
            }
        }
    }

    public static function withSmartFilterOnly(Query $query)
    {
        $query->registerRuntimeField(
            'SECTION_PROPERTY',
            new ReferenceField(
                'SECTION_PROPERTY',
                SectionPropertyTable::class,
                [
                    'this.ID' => 'ref.PROPERTY_ID',
                ]
            )
        );
        $query->addFilter('SECTION_PROPERTY.SMART_FILTER', true);
        $query->addSelect('SECTION_PROPERTY.DISPLAY_TYPE', 'SECTION_PROPERTY_DISPLAY');
    }

    public static function withIblockFilter(Query $query, $iblockId)
    {
        $query->addFilter('IBLOCK_ID', $iblockId);
    }

    public static function withSort(Query $query)
    {
        $query->addOrder('SORT', 'ASC');
    }

    public static function withIDsFilter(Query $query, $ids=[])
    {
        $query->registerRuntimeField(
            'ELEMENT_PROPERTY',
            new ReferenceField(
                'ELEMENT_PROPERTY',
                ElementPropertyTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_PROPERTY_ID',
                ]
            )
        );

        if(count($ids) > 0){
            $query->addFilter('ELEMENT_PROPERTY.IBLOCK_ELEMENT_ID', $ids);
            $query->addFilter('!=ELEMENT_PROPERTY.VALUE', null);
        }
    }

    public static function withCache(Query $query, $mode, $time)
    {
        $query->cacheJoins($mode);
        $query->setCacheTtl($time);
    }
}
