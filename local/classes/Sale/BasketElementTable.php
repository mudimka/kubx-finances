<?php
namespace Legacy\Sale;

use Legacy\Iblock\ElementPropertyTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Sale\Internals\BasketTable;
use Legacy\General\Constants;
use Bitrix\Iblock\SectionElementTable;

class BasketElementTable extends BasketTable
{
    const DEFAULT_LIMIT = 10;

    public static function withSelect(Query $query, $FUID, $OID = null)
    {
        $query->setSelect([
            'BASKET_ID' => 'ID',
            'PRICE',
            'QUANTITY',
            'ORDER_ID',
            'MEASURE_NAME',
            'PRODUCT_ID',
        ]);

        if (is_null($OID)) {
            $query->addFilter('=FUSER_ID', $FUID);
        }

        $query->addFilter('=ORDER_ID', $OID);
        $query->addFilter('!=BASKET_ID', null);
    }

    public static function withProduct(Query $query)
    {
        $query->registerRuntimeField(
            'PRODUCT',
            new ReferenceField(
                'PRODUCT',
                ProductTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.ID'
                ]
            )
        );

        $query->addSelect('PRODUCT.TYPE', 'PRODUCT_TYPE');

        $query->registerRuntimeField(
            'PRODUCT_INFO',
            new ReferenceField(
                'PRODUCT_INFO',
                \Bitrix\Iblock\ElementTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.ID',
                ]
            )
        );
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
            'IF(%s IS NOT NULL, %s, %s)',
            ['CML2_LINK.VALUE', 'CML2_LINK.VALUE', 'PRODUCT_ID']
        ));

        $query->registerRuntimeField(
            'REAL_ELEMENT',
            new ReferenceField(
                'REAL_ELEMENT',
                \Bitrix\Iblock\ElementTable::class,
                [
                    'this.REAL_ID' => 'ref.ID',
                ]
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
        $query->addSelect(
            new ExpressionField(
                'SECTION_INFO',
                'GROUP_CONCAT(DISTINCT CONCAT(%s, ":", %s))',
                ['SECTION_ELEMENT.IBLOCK_SECTION.CODE', 'SECTION_ELEMENT.IBLOCK_SECTION.NAME']
            )
        );

        $query->addSelect('PRODUCT_INFO.NAME', 'PRODUCT_NAME');
        $query->addSelect('PRODUCT_INFO.PREVIEW_PICTURE', 'PREVIEW_PICTURE');
        $query->addSelect('REAL_ELEMENT.CODE', 'PRODUCT_CODE');
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

    public static function withPrices(Query $query)
    {
        $query->registerRuntimeField(
            'PRICE_INFO',
            new ReferenceField(
                'PRICE_INFO',
                PriceTable::class,
                [
                    'this.PRODUCT_ID' => 'ref.PRODUCT_ID',
                ]
            )
        );

        $query->addSelect(new ExpressionField(
            'PRICES',
            'GROUP_CONCAT(%s, ":", %s)',
            ['PRICE_INFO.CATALOG_GROUP.NAME', 'PRICE_INFO.PRICE']
        ));
    }

    public static function withLimit(Query $query, $limit = self::DEFAULT_LIMIT)
    {
        $query->setLimit($limit);
    }

    public static function withPage(Query $query, int $page)
    {
        if ($page > 0) {
            $query->setOffset(($page - 1) * $query->getLimit());
        }
    }

    public static function withFilterByProductIds(Query $query, $ids)
    {
        $query->whereIn('PRODUCT_ID', $ids);
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

}
