<?php

namespace Legacy\Catalog;

use Legacy\General\Constants;
use Legacy\Iblock\ElementPropertyTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Iblock\ElementTable;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Catalog\StoreProductTable;

class OfferElementTable extends ElementTable
{
    public static function withDefault(Query $query, int $id)
    {
        $query->addFilter('ACTIVE', true);
        $query->addFilter('CML2_LINK.VALUE', $id);

        $query->registerRuntimeField(
            'PRODUCT',
            new ReferenceField(
                'PRODUCT',
                ProductTable::class,
                [
                    'this.ID' => 'ref.ID',
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
            'PRICE',
            new ReferenceField(
                'PRICE',
                PriceTable::class,
                [
                    'this.ID' => 'ref.PRODUCT_ID',
                ]
            )
        );

        $query->setSelect([
            'ID',
            'NAME',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'AVAILABLE' => 'PRODUCT.AVAILABLE',
            'PRODUCT_QUANTITY' => 'PRODUCT.QUANTITY',
        ]);

        $query->addSelect(new ExpressionField(
            'PRICES',
            'GROUP_CONCAT(DISTINCT %1$s)',
            ['PRICE.PRICE']
        ));
    }

    public static function withStores(Query $query)
    {
        $query->registerRuntimeField(
            'STORES',
            new ReferenceField(
                'STORES',
                StoreProductTable::class,
                [
                    'this.ID' => 'ref.PRODUCT_ID',
                ]
            )
        );

        $query->addSelect(new ExpressionField(
            'STORES_AMOUNT',
            'GROUP_CONCAT(DISTINCT CONCAT(%1$s, "::", %2$s) SEPARATOR "<>")',
            ['STORES.STORE.ADDRESS', 'STORES.AMOUNT']
        ));
    }

    public static function withProperties(Query $query, $properties)
    {
        foreach($properties as $code => $property) {
            $key = 'PROPERTY_'.$code;
            $query->registerRuntimeField(
                $key,
                new ReferenceField(
                    $key,
                    ElementPropertyTable::class,
                    [
                        'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                        'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?', $property['ID']),
                    ]
                )
            );
            $query->addSelect(new \Bitrix\Main\Entity\ExpressionField(
                $key.'_VALUE',
                "GROUP_CONCAT(distinct %s)",
                [$key.'.VALUE']
            ));
        }
    }
}
