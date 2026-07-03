<?php

namespace Legacy\Catalog;

use Legacy\API\User;
use Legacy\General\Constants;
use Legacy\Iblock\ElementPropertyTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Iblock\ElementTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\GroupAccessTable;
use Bitrix\Iblock\SectionElementTable;

class CatalogTable extends PriceTable
{
    const DEFAULT_LIMIT = 16;
    const ASCENDING = 'ASC';
    const DESCENDING = 'DESC';

    public static function withDefault(Query $query)
    {
        //todo если текущая строка ТП, то основной товар может быть неактивным
        $query->addFilter("ELEMENT.ACTIVE", true);
        $query->addFilter("@PRODUCT.TYPE", [ProductTable::TYPE_PRODUCT, ProductTable::TYPE_OFFER]);
        $query->addFilter("@ELEMENT.IBLOCK_ID", [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS]);

        $query->registerRuntimeField(
            'CML2_LINK',
            new ReferenceField(
                'CML2_LINK',
                ElementPropertyTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_CML2_LINK),
                ]
            )
        );
        $query->addSelect(new ExpressionField(
            'REAL_ID',
            'CASE WHEN %s IS NOT NULL THEN %s ELSE %s END',
            ['CML2_LINK.VALUE', 'CML2_LINK.VALUE', 'PRODUCT_ID'])
        );

        $query->registerRuntimeField(
            'SECTION_ELEMENT',
            new ReferenceField(
                'SECTION_ELEMENT',
                SectionElementTable::class,
                [
                    'this.REAL_ID' => 'ref.IBLOCK_ELEMENT_ID',
                ]
            )
        );
        //todo сделать ::
        $query->addSelect(new ExpressionField('SECTION_INFO', 'GROUP_CONCAT(DISTINCT CONCAT(%s, ":", %s))', [ 'SECTION_ELEMENT.IBLOCK_SECTION.CODE', 'SECTION_ELEMENT.IBLOCK_SECTION.NAME']));

        $query->registerRuntimeField(
            'REAL_ELEMENT',
            new ReferenceField(
                'REAL_ELEMENT',
                ElementTable::class,
                [
                    'this.REAL_ID' => 'ref.ID',
                ]
            )
        );

        $query->addSelect('PRODUCT.TYPE', 'PRODUCT_TYPE');
        $query->addSelect('PRODUCT.QUANTITY', 'PRODUCT_QUANTITY');
        $query->addSelect('PRODUCT.AVAILABLE', 'PRODUCT_AVAILABLE');
        $query->addSelect('REAL_ELEMENT.CODE', 'CODE');
        $query->addSelect('REAL_ELEMENT.NAME', 'NAME');
        $query->addSelect(new ExpressionField('MAX_DATE_CREATE', 'MAX(UNIX_TIMESTAMP(%s))', ['ELEMENT.DATE_CREATE']));
        $query->addSelect(new ExpressionField('MAX_SHOW_COUNTER', 'MAX(%s)', ['ELEMENT.SHOW_COUNTER']));
        $query->addSelect(new ExpressionField('MIN_PRICE', 'MIN(%s)', ['PRICE']));

        $query->addSelect(new ExpressionField(
            'OFFER_PRICE',
            'GROUP_CONCAT(DISTINCT CONCAT(%1$s, ":", %2$s))',
            ['PRODUCT_ID', 'PRICE']
        ));
    }

    public static function withPreviewPictures(Query $query)
    {
        $query->addSelect('ELEMENT.PREVIEW_PICTURE', 'PREVIEW_PICTURE');
        $query->addSelect('PRODUCT_ID');
    }

    public static function withSimpleDefault(Query $query)
    {
        //todo если текущая строка ТП, то основной товар может быть неактивным
        $query->addFilter("ELEMENT.ACTIVE", true);
        $query->addFilter("@PRODUCT.TYPE", [ProductTable::TYPE_PRODUCT, ProductTable::TYPE_OFFER]);
        $query->addFilter("@ELEMENT.IBLOCK_ID", [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS]);

        $query->addSelect('PRODUCT_ID', 'PRODUCT_ID');

        $query->registerRuntimeField(
            'CML2_LINK',
            new ReferenceField(
                'CML2_LINK',
                ElementPropertyTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID',
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
        $query->addSelect( new ExpressionField(
            'REAL_ID',
            'IF(%s IS NOT NULL, %s, %s)',
            ['CML2_LINK.VALUE', 'CML2_LINK.VALUE', 'PRODUCT_ID']
        ));
        $query->registerRuntimeField(
            'SECTION_ELEMENT',
            new ReferenceField(
                'SECTION_ELEMENT',
                SectionElementTable::class,
                [
                    'this.REAL_ID' => 'ref.IBLOCK_ELEMENT_ID',
                ]
            )
        );

        $query->addSelect('PRODUCT.TYPE', 'PRODUCT_TYPE');
    }

    public static function withPriceListDefault(Query $query)
    {
        $query->addFilter("ELEMENT.ACTIVE", true);
        $query->addFilter("@PRODUCT.TYPE", [ProductTable::TYPE_PRODUCT, ProductTable::TYPE_OFFER]);
        $query->addFilter("@ELEMENT.IBLOCK_ID", [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS]);

        $query->registerRuntimeField(
            'CML2_LINK',
            new ReferenceField(
                'CML2_LINK',
                ElementPropertyTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_CML2_LINK),
                ]
            )
        );
        $query->registerRuntimeField(
            'REAL_ID',
            new ExpressionField(
                'REAL_ID',
                'CASE WHEN %s IS NOT NULL THEN %s ELSE %s END',
                ['CML2_LINK.VALUE', 'CML2_LINK.VALUE', 'PRODUCT_ID']
            )
        );

        $query->registerRuntimeField(
            'SECTION_ELEMENT',
            new ReferenceField(
                'SECTION_ELEMENT',
                SectionElementTable::class,
                [
                    'this.REAL_ID' => 'ref.IBLOCK_ELEMENT_ID',
                ]
            )
        );

        $query->addSelect(new ExpressionField('SECTION_INFO', 'GROUP_CONCAT(DISTINCT CONCAT(%s, ":", %s))', [ 'SECTION_ELEMENT.IBLOCK_SECTION.CODE', 'SECTION_ELEMENT.IBLOCK_SECTION.NAME']));

        $query->registerRuntimeField(
            'REAL_ELEMENT',
            new ReferenceField(
                'REAL_ELEMENT',
                ElementTable::class,
                [
                    'this.REAL_ID' => 'ref.ID',
                ]
            )
        );

        $query->addSelect('PRODUCT.TYPE', 'PRODUCT_TYPE');
        $query->addSelect('REAL_ELEMENT.CODE', 'CODE');
        $query->addSelect('ELEMENT.NAME', 'NAME');

        $query->addSelect(new ExpressionField(
            'PRICES',
            'GROUP_CONCAT(DISTINCT CONCAT(%s, ":", %s))',
            ['CATALOG_GROUP.NAME', 'PRICE']
        ));

        $query->addSelect('PRODUCT_ID');
        $query->addGroup('PRODUCT_ID');
    }

    public static function withArticle(Query $query)
    {
        $query->registerRuntimeField(
            'PRODUCT_ARTICLE',
            new ReferenceField(
                'PRODUCT_ARTICLE',
                ElementPropertyTable::class,
                [
                    'this.REAL_ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_ARTICLE),
                ]
            )
        );

        $query->registerRuntimeField(
            'OFFER_ARTICLE',
            new ReferenceField(
                'OFFER_ARTICLE',
                ElementPropertyTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_ARTICLE),
                ]
            )
        );

        $query->addSelect(new ExpressionField(
                'ARTICLE',
                'CASE WHEN %s IS NOT NULL THEN %s ELSE %s END',
                ['OFFER_ARTICLE.VALUE', 'OFFER_ARTICLE.VALUE', 'PRODUCT_ARTICLE.VALUE'])
        );
    }


    public static function withPriceUserGroupFilter(Query $query)
    {
        $query->registerRuntimeField(
            'GROUP_ACCESS',
            new ReferenceField(
                'GROUP_ACCESS',
                GroupAccessTable::class,
                [
                    'this.CATALOG_GROUP.ID' => 'ref.CATALOG_GROUP_ID',
                ]
            )
        );

        $query->addFilter('GROUP_ACCESS.GROUP_ID', User::getUserGroups());
    }


    public static function withPricesDefault(Query $query)
    {
        $query->addFilter("ELEMENT.ACTIVE", true);
        $query->addFilter("@PRODUCT.TYPE", [ProductTable::TYPE_PRODUCT, ProductTable::TYPE_OFFER]);
        $query->addFilter("@ELEMENT.IBLOCK_ID", [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS]);

        $query->registerRuntimeField(
            'CML2_LINK',
            new ReferenceField(
                'CML2_LINK',
                ElementPropertyTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', Constants::IB_PROP_CATALOG_CRM_OFFERS_CML2_LINK),
                ]
            )
        );

        $query->registerRuntimeField('REAL_ID',
            new ExpressionField(
                'REAL_ID',
                'CASE WHEN %s IS NOT NULL THEN %s ELSE %s END',
                ['CML2_LINK.VALUE', 'CML2_LINK.VALUE', 'PRODUCT_ID']
            )
        );
        $query->addGroup('REAL_ID');

        $query->registerRuntimeField(
            'REAL_ELEMENT',
            new ReferenceField(
                'REAL_ELEMENT',
                ElementTable::class,
                [
                    'this.REAL_ID' => 'ref.ID',
                ]
            )
        );

        $query->addSelect(new ExpressionField('MIN_PRICE', 'MIN(%s)', ['PRICE']));
    }

    public static function withGroupByProperties(Query $query, $props, $favourite = false)
    {
        foreach ($props as $prop) {
            if ($favourite || in_array('KUBX_USE_TO_GROUP_OFFERS', $prop['FEATURES'])) {
                $id = $prop['ID'];
                $key = 'PROPERTY_'.$id;
                $query->registerRuntimeField(
                    $key,
                    new ReferenceField(
                        $key,
                        ElementPropertyTable::class,
                        [
                            'this.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                            'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', $id),
                        ]
                    )
                );
                $query->addGroup($key.'.VALUE');
            }
        }
    }

    public static function withIDs(Query $query, array $ids)
    {
        if (count($ids) > 0) {
            $query->addFilter(null, [
                'LOGIC' => 'OR',
                '=PRODUCT_ID' => $ids,
                '=REAL_ID' => $ids
            ]);
        }
    }

    public static function withExclude(Query $query, array $ids)
    {
        if (count($ids) > 0) {
            $query->addFilter('!=PRODUCT_ID', $ids);
        }
    }

    public static function withFilter(Query $query, array $filter)
    {
        foreach ($filter as $code => $value) {
            if ($code == 'price'){
                $query->addFilter(null, [
                    'LOGIC' => 'AND',
                    '>=PRICE' => $value[0],
                    '<=PRICE' => $value[1],
                ]);
            } else {
                if(defined('Legacy\General\Constants::IB_PROP_CATALOG_CRM_OFFERS_'.mb_strtoupper($code))){
                    $constant = constant('Legacy\General\Constants::IB_PROP_CATALOG_CRM_OFFERS_'.mb_strtoupper($code));
                    $ref = 'this.PRODUCT_ID';
                }
                elseif (defined('Legacy\General\Constants::IB_PROP_CATALOG_CRM_'.mb_strtoupper($code))){
                    $constant = constant('Legacy\General\Constants::IB_PROP_CATALOG_CRM_'.mb_strtoupper($code));
                    $ref = 'this.REAL_ELEMENT.ID';
                } else {
                    continue;
                }

                $key = 'FILTER_PROPERTY_'.mb_strtoupper($code);
                $query->registerRuntimeField(
                    $key,
                    new ReferenceField(
                        $key,
                        ElementPropertyTable::class,
                        [
                            $ref => 'ref.IBLOCK_ELEMENT_ID',
                            'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', $constant),
                        ]
                    )
                );

                $query->addFilter('@'.$key.'.VALUE', $value);
            }
        }
    }
    public static function withFromCategory(Query $query, $categories)
    {
        if (count($categories) && !in_array('all', $categories)) {
            $query->addFilter('=SECTION_ELEMENT.IBLOCK_SECTION.CODE', $categories);
        }
    }

    public static function withProperties(Query $query, $properties)
    {
        foreach($properties as $code => $property) {
            $idName = $property['IBLOCK_ID'] == Constants::IB_CATALOG_CRM ? 'REAL_ID' : 'PRODUCT_ID';

            $key = 'PROPERTY_'.$code;
            $query->registerRuntimeField(
                $key,
                new ReferenceField(
                    $key,
                    ElementPropertyTable::class,
                    [
                        'this.' . $idName => 'ref.IBLOCK_ELEMENT_ID',
                        'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', $property['ID']),
                    ]
                )
            );

            if ($property['MULTIPLE']) {
                $query->addSelect(new ExpressionField(
                    $key.'_VALUE',
                    "GROUP_CONCAT(distinct %s)",
                    [$key.'.VALUE']
                ));
            } else {
                $query->addSelect($key.'.VALUE', $key.'_VALUE');
            }
        }
    }

    public static function withSortBy(Query $query, $sortBy = 'new', $IDs = [])
    {
        $query->withOrderBy('PRODUCT_AVAILABLE', CatalogTable::DESCENDING);
        switch ($sortBy) {
            case 'expensive':
                $query->withOrderBy('MIN_PRICE', CatalogTable::DESCENDING);
                break;
            case 'ids':
                $idsString = implode(',', $IDs);
                $query->registerRuntimeField(
                    new ExpressionField(
                        'CUSTOM_SORT',
                        'FIELD(%s, ' . $idsString . ')',
                        ['PRODUCT_ID']
                    )
                );
                $query->addOrder('CUSTOM_SORT', 'ASC');
                break;
            case 'new':
                $query->withOrderBy('MAX_DATE_CREATE', CatalogTable::DESCENDING);
                break;
            case 'popular':
                $query->withOrderBy('MAX_SHOW_COUNTER', CatalogTable::DESCENDING);
            case 'cheap':
            default:
                $query->withOrderBy('MIN_PRICE', CatalogTable::ASCENDING);
                break;
        }
    }

    public static function withPage(Query $query, int $page)
    {
        if ($page > 0) {
            $query->setOffset(($page - 1) * $query->getLimit());
        }
    }

    public static function withOrderBy(Query $query, $def, $order)
    {
        $query->addOrder($def, $order);
    }

    public static function withLimit(Query $query, $limit = self::DEFAULT_LIMIT)
    {
        $query->setLimit($limit);
    }

    public static function withCache(Query $query, $mode, $time)
    {
        $query->cacheJoins($mode);
        $query->setCacheTtl($time);
    }

}
