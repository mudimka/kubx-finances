<?php

namespace Legacy\API;

use Bitrix\Main\Loader;
use Legacy\Catalog\CatalogTable;
use Legacy\General\Constants;
use Bitrix\Catalog\ProductTable;
use Legacy\IblockController\Section;

class PriceList
{
    public static function get($arRequest)
    {
        $items = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $categories = $arRequest['categories'];

            $categoriesCodes = Section::getCategoryChildrenCodes($categories);

            if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
                $q = CatalogTable::query()
                    ->withPriceListDefault()
                    ->withFromCategory($categoriesCodes)
                    ->withPriceUserGroupFilter()
                    ->withArticle()
                ;

                $items = self::processPriceListData($q);
            }
        }

        XLS::getPriceList(['catalog_items' => $items]);
    }

    public static function getBasket($arRequest)
    {
        $basketItems = XLS::uploadPriceList($arRequest);

        $product_ids = [];
        $countData = [];
        foreach ($basketItems as $key => $value) {
            if ($key && $value[count($value) - 1]) {
                $product_ids[] = $value[0];
                $countData[$value[0]] = $value[count($value)-1];
            }
        }

        if(empty($product_ids)) {
            return [];
        }

        $catalog = [];
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $properties = Properties::get(['iblock_id' => [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS], 'is_listing' => true]);
            $q = CatalogTable::query()
                ->withPriceListDefault()
                ->withIDs($product_ids)
                ->withPriceUserGroupFilter()
                ->withProperties($properties)
                ->withArticle()
                ->withCache(true, 3600)
            ;

            $catalog = self::processPriceListItemsData($q, $properties);
        }

        foreach ($catalog as &$item) {
            $item['quantity'] = $countData[$item['id']];
        }

        return $catalog;
    }

    public static function addToBasket($arRequest)
    {
        $items = $arRequest['items'];

        foreach ($items as $item) {
            Basket::add(['id' => $item['id'], 'quantity' => $item['quantity']]);
        }

        return Basket::get([]);
    }

    private static function processPriceListData($query)
    {
        $result = [];

        $db = $query->exec();

        while ($arr = $db->fetch()) {
            $prices = [];
            $pricesData = explode(',', $arr['PRICES']);
            foreach ($pricesData as $priceData) {
                $item = explode(':', $priceData);
                $prices[$item[0]] = $item[1];
            }

            $sectionInfo = Section::extractSectionInfo($arr['SECTION_INFO']);

            $result[] = [
                'ID' => $arr['PRODUCT_ID'],
                'NAME' => $arr['NAME'],
                'BASE_PRICE' => $prices['BASE'],
                'PRICE' => min($prices),
                'SECTION_NAME' => $sectionInfo['name'] ?? '',
                'ARTICLE' => $arr['ARTICLE'],
            ];
        }

        return array_change_key_case_recursive($result);
    }

    private static function processPriceListItemsData($q, $properties)
    {
        $items = [];

        $db = $q->exec();

        $productsValuesCodes = [];
        while ($arr = $db->fetch()) {
            $productsValuesCodes[] = $arr;

            $pricesData = explode(',', $arr['PRICES']);
            $prices = [];
            foreach ($pricesData as $priceData) {
                $item = explode(':', $priceData);
                $prices[$item[0]] = $item[1];
            }

            $urlOfferId = $arr['PRODUCT_TYPE'] == ProductTable::TYPE_OFFER ? '/?offerId=' . $arr['PRODUCT_ID'] : '';
            $sectionInfo = Section::extractSectionInfo($arr['SECTION_INFO']);

            $items[$arr['PRODUCT_ID']] = [
                'id' => $arr['PRODUCT_ID'],
                'name' => trim($arr['NAME']),
                'price' => formatPrice(min($prices)),
                'section' => $sectionInfo,
                'article' => $arr['ARTICLE'],
                'url' => $sectionInfo['url'] . '/product/' . $arr['CODE'] . $urlOfferId,
            ];
        }

        $propertiesValues = Properties::getPropertiesValues($productsValuesCodes, $properties);

        foreach ($productsValuesCodes as $productValuesCodes) {
            $id = $productValuesCodes['PRODUCT_ID'];
            $propertiesData = Properties::getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $properties);
            if($propertiesData['offer_images']){
                $items[$id]['IMAGES'] = $propertiesData['offer_images']['value'];
            } else {
                $items[$id]['IMAGES'] = $propertiesData['images']['value'];
            }

            unset($propertiesData['offer_images']);
            unset($propertiesData['images']);

            $items[$id]['PROPERTIES'] = $propertiesData;
        }

        return array_change_key_case_recursive(array_values($items));
    }
}
