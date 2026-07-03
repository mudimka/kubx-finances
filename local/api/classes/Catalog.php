<?php

namespace Legacy\API;

use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Loader;
use Legacy\Catalog\CatalogTable;
use Legacy\Catalog\FilterTable;
use Legacy\General\Constants;
use Legacy\General\DataProcessor;
use Legacy\IblockController\File;
use Legacy\IblockController\SEO;
use Legacy\IblockController\Section;
use Legacy\IblockController\Settings as SettingsController;

class Catalog
{
//    use CacheTrait;

    private static function processData($query, $catalogCategory)
    {
        $result = [];
        $elProperties = Properties::get(['iblock_id' => [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS], 'is_listing' => true]);

        $db = $query->exec();

        $result['count'] = $db->getCount();

        while ($arr = $db->fetch()) {
            $min_price = PHP_INT_MAX;
            $price = null;
            $discount = null;

            $arr['OFFER_PRICES'] = [];
            foreach(explode(',', $arr['OFFER_PRICE']) as $item) {
                [$id, $price] = explode(':', $item);

                $arr['OFFER_PRICES'][$id][] = $price;
            }

            if (count($arr['OFFER_PRICES'])) {
                foreach ($arr['OFFER_PRICES'] as $offerId => $prices) {
                    $offer = [
                        'ID' => $offerId,
                        'PRICE' => max($prices),
                        'DISCOUNT_PRICE' => min($prices),
                    ];

                    $offer['DISCOUNT'] = (float)$offer['PRICE']
                        ? round(($offer['PRICE'] - $offer['DISCOUNT_PRICE'])*100/$offer['PRICE'])
                        : null;

                    if($offer['DISCOUNT_PRICE'] < $min_price) {
                        $arr['OFFER_ID'] = $offer['ID'];
                        $min_price = $offer['DISCOUNT_PRICE'];
                        $price = $offer['PRICE'];
                        $discount = $offer['DISCOUNT'];
                    }
                }
            }

            $id = $arr['OFFER_ID'];

            if($id){
                $sectionInfo = Section::extractSectionInfo($arr['SECTION_INFO'], $catalogCategory);

                $urlOfferId = $arr['PRODUCT_TYPE'] == ProductTable::TYPE_OFFER ? '/?offerId=' . $id : '';

                $result['items'][$id] = [
                    'ID' => $id,
                    'CODE' => $arr['CODE'],
                    'NAME' => $arr['NAME'],
                    'QUANTITY' => $arr['PRODUCT_QUANTITY'],
                    'AVAILABLE' => $arr['PRODUCT_AVAILABLE'] === 'Y',
                    'REAL_ID' => $arr['REAL_ID'],
                    'DISCOUNT_PRICE' => $min_price != $price ? $min_price : null,
                    'DISCOUNT_PERCENT' => $min_price != $price ? $discount : null,
                    'PRICE' => $price,
                    'SECTION' => $sectionInfo,
                    'URL' => '/catalog' . $sectionInfo['url'] . '/product/' . $arr['CODE'] . $urlOfferId,
                    'PROPERTIES' => null,
                    'IMAGES' => null,
                    'HAS_OFFERS' => $arr['PRODUCT_TYPE'] == ProductTable::TYPE_OFFER
                ];
            }
        }

        $ids = array_keys($result['items'] ?? []);

        if(count($ids)){
            $measures = ProductTable::getCurrentRatioWithMeasure($ids);
            $preview_images = self::getPreviewsImages($ids);

            foreach ($measures as $productId => $val) {
                if (key_exists($productId, $result['items'])) {
                    $result['items'][$productId]['MEASURE']['RATIO'] = $val['RATIO'];
                    $result['items'][$productId]['MEASURE']['TITLE'] = $val['MEASURE']['MEASURE_TITLE'];
                    $result['items'][$productId]['MEASURE']['SYMBOL'] = $val['MEASURE']['SYMBOL'];
                }
            }

            $q = CatalogTable::query()
                ->withSimpleDefault()
                ->withIDs($ids)
                ->withProperties($elProperties)
                ->withCache(true, 3600)
            ;

            $dbProps = $q->exec();

            $productsValuesCodes = [];
            while ($res = $dbProps->fetch()) {
                $productsValuesCodes[] = $res;
            }

            $propertiesValues = Properties::getPropertiesValues($productsValuesCodes, $elProperties);

            foreach ($productsValuesCodes as $productValuesCodes) {
                $id = $productValuesCodes['PRODUCT_ID'];
                $propertiesData = Properties::getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $elProperties);

                $images = !empty($preview_images[$id]) ? [$preview_images[$id]] : [];
                if ($propertiesData['offer_images']) {
                    $images = array_merge($images, $propertiesData['offer_images']['value'] ?? []);
                } else {
                    $images = array_merge($images, $propertiesData['more_photo']['value'] ?? []);
                }
                $result['items'][$id]['IMAGES'] = array_slice($images, 0, 4);

                $result['items'][$id]['NEW'] = (bool)$propertiesData['new'];

                unset($propertiesData['offer_images']);
                unset($propertiesData['more_photo']);
                unset($propertiesData['new']);

                $result['items'][$id]['PROPERTIES'] = $propertiesData;
            }
        }

        $result['items'] = $result['items'] ? array_change_key_case_recursive(array_values($result['items'])) : null;

        return $result;
    }

    private static function processSearchData($query, $catalogCategory)
    {
        $result = [];
        $elProperties = Properties::get(['iblock_id' => [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS], 'is_listing' => true]);

        $db = $query->exec();

        while ($arr = $db->fetch()) {
            $min_price = PHP_INT_MAX;
            $price = null;
            $discount = null;

            $arr['OFFER_PRICES'] = [];
            foreach(explode(',', $arr['OFFER_PRICE']) as $item) {
                [$id, $price] = explode(':', $item);

                $arr['OFFER_PRICES'][$id][] = $price;
            }

            if (count($arr['OFFER_PRICES'])) {
                foreach ($arr['OFFER_PRICES'] as $offerId => $prices) {
                    $offer = [
                        'ID' => $offerId,
                        'PRICE' => max($prices),
                        'DISCOUNT_PRICE' => min($prices),
                    ];

                    $offer['DISCOUNT'] = (float)$offer['PRICE']
                        ? round(($offer['PRICE'] - $offer['DISCOUNT_PRICE'])*100/$offer['PRICE'])
                        : null;

                    if($offer['DISCOUNT_PRICE'] < $min_price) {
                        $arr['OFFER_ID'] = $offer['ID'];
                        $min_price = $offer['DISCOUNT_PRICE'];
                        $price = $offer['PRICE'];
                        $discount = $offer['DISCOUNT'];
                    }
                }
            }

            $id = $arr['OFFER_ID'];

            if($id){
                $sectionInfo = Section::extractSectionInfo($arr['SECTION_INFO'], $catalogCategory);

                $urlOfferId = $arr['PRODUCT_TYPE'] == ProductTable::TYPE_OFFER ? '/?offerId=' . $id : '';

                $result[$id] = [
                    'ID' => $id,
                    'CODE' => $arr['CODE'],
                    'NAME' => $arr['NAME'],
                    'QUANTITY' => $arr['PRODUCT_QUANTITY'],
                    'AVAILABLE' => $arr['PRODUCT_AVAILABLE'] === 'Y',
                    'REAL_ID' => $arr['REAL_ID'],
                    'DISCOUNT_PRICE' => $min_price != $price ? $min_price : null,
                    'DISCOUNT_PERCENT' => $min_price != $price ? $discount : null,
                    'PRICE' => $price,
                    'SECTION' => $sectionInfo,
                    'URL' => '/catalog' . $sectionInfo['url'] . '/product/' . $arr['CODE'] . $urlOfferId,
                    'PROPERTIES' => null,
                    'IMAGES' => null,
                    'HAS_OFFERS' => $arr['PRODUCT_TYPE'] == ProductTable::TYPE_OFFER
                ];
            }
        }

        $ids = array_keys($result ?? []);

        if(count($ids)){
            $measures = ProductTable::getCurrentRatioWithMeasure($ids);
            $preview_images = self::getPreviewsImages($ids);

            foreach ($measures as $productId => $val) {
                if (key_exists($productId, $result)) {
                    $result[$productId]['MEASURE']['RATIO'] = $val['RATIO'];
                    $result[$productId]['MEASURE']['TITLE'] = $val['MEASURE']['MEASURE_TITLE'];
                    $result[$productId]['MEASURE']['SYMBOL'] = $val['MEASURE']['SYMBOL'];
                }
            }

            $q = CatalogTable::query()
                ->withSimpleDefault()
                ->withIDs($ids)
                ->withProperties($elProperties)
                ->withCache(true, 3600)
            ;

            $dbProps = $q->exec();

            $productsValuesCodes = [];
            while ($res = $dbProps->fetch()) {
                $productsValuesCodes[] = $res;
            }

            $propertiesValues = Properties::getPropertiesValues($productsValuesCodes, $elProperties);

            foreach ($productsValuesCodes as $productValuesCodes) {
                $id = $productValuesCodes['PRODUCT_ID'];
                $propertiesData = Properties::getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $elProperties);

                $images = !empty($preview_images[$id]) ? [$preview_images[$id]] : [];
                if ($propertiesData['offer_images']) {
                    $images = array_merge($images, $propertiesData['offer_images']['value'] ?? []);
                } else {
                    $images = array_merge($images, $propertiesData['more_photo']['value'] ?? []);
                }
                $result[$id]['IMAGES'] = array_slice($images, 0, 4);

                $result[$id]['NEW'] = (bool)$propertiesData['new'];

                unset($propertiesData['offer_images']);
                unset($propertiesData['more_photo']);
                unset($propertiesData['new']);

                $result[$id]['PROPERTIES'] = $propertiesData;
            }
        }

        $result = $result ? array_change_key_case_recursive(array_values($result)) : null;

        return $result;
    }

    private static function getPreviewsImages($ids)
    {
        $result = [];
        $query = CatalogTable::query()
            ->withDefault()
            ->withPreviewPictures()
            ->withIds($ids)
            ->exec()
        ;

        while($arr = $query->fetch()) {
            $result[$arr['PRODUCT_ID']] = File::getFilesInfo($arr['PREVIEW_PICTURE'], false, false);
        }

        return $result;
    }

    private static function processIDsData($query)
    {
        $result = [];

        $db = $query->exec();

        while ($arr = $db->fetch()) {
            $result[] = $arr['REAL_ID'];
            $result[] = $arr['PRODUCT_ID'];
        }
        return array_values(array_unique($result));
    }

    private static function getFilter($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $categories = $arRequest['categories'] ?? '';
            $filter = $arRequest['filter'] ?? [];
            $ids = $arRequest['product_offer_ids'] ?? [];
            $properties = Properties::get([
                'iblock_id' => [Constants::IB_CATALOG_CRM_OFFERS, Constants::IB_CATALOG_CRM],
                'is_smart_filter' => true,
                'product_offer_ids' => $ids
            ]);

            foreach ($properties as $pcode => $property) {
                foreach ($property['VALUES'] as $value_code => $value){
                    $properties[$pcode]['VALUES'][$value_code]['is_available'] = false;
                }
            }

            $q = FilterTable::query()
                ->withDefault($properties)
                ->withFromCategory($categories)
                ->withFilter($filter)
                ->withCache(true, 3600)
            ;
            $db = $q->exec();
            while ($arr = $db->fetch()) {
                foreach ($arr as $code => $value) {
                    if ($properties[$code]['VALUES'][$value]) {
                        $properties[$code]['VALUES'][$value]['is_available'] = true;
                    }
                }
            }

            $lastKey = array_key_last($filter);
            unset($filter[$lastKey]);
            $qWithoutFilter = FilterTable::query()
                ->withDefault($properties)
                ->withFromCategory($categories)
                ->withFilter($filter)
                ->withCache(true, 3600)
            ;
            $db = $qWithoutFilter->exec();
            while ($arr = $db->fetch()) {
                foreach ($arr as $code => $value) {
                    if ($code == mb_strtoupper($lastKey) && $properties[$code]['VALUES'][$value]) {
                        $properties[$code]['VALUES'][$value]['is_available'] = true;
                    }
                }
            }

            $properties = array_merge(self::getPriceRange($arRequest), $properties);

            foreach ($properties as $property) {
                if($property['DISPLAY'] == 'A') {
                    $values = array_keys($property['VALUES']);
                    $min = min($values);
                    $max = max($values);
                    if($min != $max) {
                        $result[] = [
                            'code' => mb_strtolower($property['CODE']),
                            'name' => $property['NAME'],
                            'type' => Properties::getPropertyType($property['DISPLAY']),
                            'items' => [
                                $min,
                                $max,
                            ],
                        ];
                    }
                } else {
                    $result[] = [
                        'code' => mb_strtolower($property['CODE']),
                        'name' => $property['NAME'],
                        'type' => Properties::getPropertyType($property['DISPLAY']),
                        'items' => !empty($property['VALUES_ORDER']) ?
                            DataProcessor::sortResultByIDs($property['VALUES'], $property['VALUES_ORDER'], true) :
                            array_values($property['VALUES']),
                    ];
                }
            }
        }

        return $result;
    }

    private static function getPriceRange($arRequest)
    {
        $ids = $arRequest['product_offer_ids'] ?? '';

        $result = [
            'PRICE' => [
                'CODE' => 'PRICE',
                'NAME' => 'Цены',
                'DISPLAY' => 'range',
                'VALUES' => [
                    0 => 0,
                    1 => 0
                ]
            ]
        ];

        $offerProps = Properties::getOfferProperties();
        $q = CatalogTable::query()
            ->withPricesDefault()
            ->withGroupByProperties($offerProps)
            ->withIDs($ids)
            ->withCache(true, 3600)
        ;

        $db = $q->exec();
        $prices = [];
        while ($arr = $db->fetch()) {
            $prices[] = $arr['MIN_PRICE'];
        }
        $result['PRICE']['VALUES'][0] = min($prices);
        $result['PRICE']['VALUES'][1] = max($prices);

        return $result;
    }

    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $ids = $arRequest['ids'] ?? [];
            $filter = $arRequest['filter'] ?? [];

            $categoryCode = $arRequest['category'];
            if (!Section::isSectionExist($categoryCode) && !$ids) {
                throw new \Exception('Категория не существует', 404);
            }

            $result['seo_category'] = SEO::getCategorySEO($categoryCode);

            $categories = Section::getCategoryChildrenCodes($categoryCode);

            $qIDS = CatalogTable::query()
                ->withSimpleDefault()
                ->withFromCategory($categories)
                ->withIDs($ids)
                ->withExclude($arRequest['exclude'] ?? [])
                ->withCache(true, 3600)
            ;

            $ids = self::processIDsData($qIDS);

            $itemsAndCount = self::getItemsAndCount($arRequest);
            $result['items'] = $itemsAndCount['items'];
            $result['count'] = $itemsAndCount['count'];

            $result['filter'] = self::getFilter([
                'categories' => $categories,
                'filter' => $filter,
                'product_offer_ids' => $ids,
            ]);
            $result['sort'] = SettingsController::getCatalogSort();
        }

        return $result;
    }

    public static function getItemsAndCount($arRequest)
    {
        $result = ['count' => 0, 'items' => []];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $ids = $arRequest['ids'] ?? [];
            $page = (int)$arRequest['page'];
            $filter = $arRequest['filter'] ?? [];
            $limit = $arRequest['limit'] ? (int)$arRequest['limit'] : CatalogTable::DEFAULT_LIMIT;
            $category = $arRequest['category'];
            $categories = Section::getCategoryChildrenCodes($category);

            $sortBy = $arRequest['sortby'];
            $needDivideCards = (bool)$arRequest['needDivideCards'];

            $offerProps = Properties::getOfferProperties();
            $q = CatalogTable::query()
                ->countTotal(true)
                ->withDefault()
                ->withLimit($limit)
                ->withIDs($ids)
                ->withFromCategory($categories)
                ->withPriceUserGroupFilter()
                ->withFilter($filter)
                ->withGroupByProperties($offerProps, $needDivideCards)
                ->withPage($page)
                ->withExclude($arRequest['exclude'] ?? [])
                ->withSortBy($sortBy, $ids)
                ->withCache(true, 3600)
            ;

            $result = self::processData($q, $category);
        }

        return $result;
    }

    public static function getSearchItems($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $ids = $arRequest['ids'] ?? [];

            $page = (int)$arRequest['page'];
            $filter = $arRequest['filter'] ?? [];
            $limit = $arRequest['limit'] ? (int)$arRequest['limit'] : CatalogTable::DEFAULT_LIMIT;
            $category = $arRequest['category'];
            $categories = Section::getCategoryChildrenCodes($category);

            $sortBy = $arRequest['sortby'];
            $needDivideCards = (bool)$arRequest['needDivideCards'];

            $offerProps = Properties::getOfferProperties();
            $q = CatalogTable::query()
                ->withDefault()
                ->withLimit($limit)
                ->withIDs($ids)
                ->withFromCategory($categories)
                ->withPriceUserGroupFilter()
                ->withFilter($filter)
                ->withGroupByProperties($offerProps, $needDivideCards)
                ->withPage($page)
                ->withExclude($arRequest['exclude'] ?? [])
                ->withSortBy($sortBy, $ids)
                ->withCache(true, 3600)
            ;

            $result = self::processSearchData($q, $category);
        }

        return $result;
    }
}
