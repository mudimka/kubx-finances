<?php

namespace Legacy\Catalog;

use Legacy\Iblock\ElementPropertyTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\PriceTable;
use Bitrix\Iblock\ElementTable;
use \Bitrix\Main\DB\SqlExpression;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Iblock\SectionElementTable;

class ProductElementTable extends ElementTable
{
    public static function withID(Query $query, int $id)
    {
        $query->addFilter('=ID', $id);
    }

    public static function withIDs(Query $query, $ids)
    {
        $query->whereIn('ID', $ids);
    }
    public static function withActive(Query $query)
    {
        $query->addFilter('ACTIVE', true);
    }

    public static function withCode(Query $query, string $code)
    {
        $query->addFilter('=CODE', $code);
    }
    public static function withCategory(Query $query, string $category)
    {
        $query->registerRuntimeField(
            'SECTION_ELEMENT',
            new ReferenceField(
                'SECTION_ELEMENT',
                SectionElementTable::class,
                [
                    'this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                ]
            )
        );
        $query->addFilter('=SECTION_ELEMENT.IBLOCK_SECTION.CODE', $category);

        $query->addSelect('SECTION_ELEMENT.IBLOCK_SECTION.CODE', 'SECTION_CODE');
        $query->addSelect('SECTION_ELEMENT.IBLOCK_SECTION.NAME', 'SECTION_NAME');
    }

    public static function withSelect(Query $query)
    {
        $query->setSelect([
            'ID',
            'NAME',
            'IBLOCK_ID',
            'PREVIEW_PICTURE',
            'PREVIEW_TEXT',
            'DETAIL_PICTURE',
            'DETAIL_TEXT',
        ]);
    }

    public static function withIDSelect(Query $query)
    {
        $query->setSelect([
            'ID',
        ]);
    }

    public static function withCatalog(Query $query)
    {
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
            'PRICE',
            new ReferenceField(
                'PRICE',
                PriceTable::class,
                [
                    'this.ID' => 'ref.PRODUCT_ID',
                ]
            )
        );

        $query->addSelect('PRODUCT.TYPE', 'PRODUCT_TYPE');
        $query->addSelect('PRODUCT.QUANTITY', 'PRODUCT_QUANTITY');
        $query->addSelect('PRODUCT.AVAILABLE', 'AVAILABLE');
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

    public static function withIblockFilter(Query $query, $iblockId)
    {
        $query->addFilter('IBLOCK_ID', $iblockId);
    }
}
