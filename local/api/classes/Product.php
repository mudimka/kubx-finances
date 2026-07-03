<?php

namespace Legacy\API;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Loader;
use Bitrix\Main\UserFieldTable;
use Legacy\Catalog\ProductElementTable;
use Legacy\General\Constants;
use Legacy\IblockController\File;
use Legacy\IblockController\Forms;
use Legacy\IblockController\Offers;
use Legacy\IblockController\SEO;
use Legacy\IblockController\Section;
use Legacy\IblockController\Services;
use Legacy\IblockController\Videos;

class Product
{
    public static function processCatalog($query)
    {
        $result = null;

        $db = $query->exec();

        if ($res = $db->fetch()) {
            //todo посмотреть еще варианты
            $measure = ProductTable::getCurrentRatioWithMeasure($res['ID'])[$res['ID']];

            $stores_count = $res['STORES_AMOUNT'] ? array_map(function ($storeAmount) {
                [$store, $quantity] = explode('::', $storeAmount);
                return [
                    'address' => $store,
                    'quantity' => $quantity
                ];
            }, explode('<>', $res['STORES_AMOUNT'])) : null;
            $prices = explode(',', $res['PRICES']);
            $price = max($prices);
            $discount_price = min($prices);

            $hierarchy = Section::getHierarchy($res['SECTION_CODE']);

            $result = [
                'id' => $res['ID'],
                'name' => trim($res['NAME']),
                'available' => $res['AVAILABLE'] == 'Y',
                'measure' => [
                    'title' => $measure['MEASURE']['MEASURE_TITLE'],
                    'symbol' => $measure['MEASURE']['SYMBOL'],
                ],
                'type' => $res['PRODUCT_TYPE'],
                'quantity' => $res['PRODUCT_QUANTITY'],
                'preview_picture' => File::getFilesInfo($res['PREVIEW_PICTURE'], false, false),
                'preview_text' => $res['PREVIEW_TEXT'],
                'detail_picture' => File::getFilesInfo($res['DETAIL_PICTURE'], false, false),
                'detail_text' => $res['DETAIL_TEXT'],
                'price' => $price,
                'discount_price' => $price != $discount_price ? $discount_price : null,
                'discount_percent' => $price != $discount_price
                    ? round(($price - $discount_price)*100/$price)
                    : null,
                'stores' => $stores_count,
                'seo' => SEO::getProductSEO($res['ID']),
                'section' => [
                    'name' => $res['SECTION_NAME'],
                    'code' => $res['SECTION_CODE'],
                    'hierarchy' => $hierarchy,
                    'url' => $hierarchy[count($hierarchy) - 1]['url'],
                ],
                'form_one_click_purchase' => Forms::getByCode(['code' => 'one_click_purchase'])
//                'size_table' => Section::getSectionTableSize($res['SECTION_CODE']),
            ];
        }

        return $result;
    }

    private static function getProductValuesCodes($id, $properties)
    {
        $chunkSize = 2;
        $results = [];

        $keys = array_keys($properties);
        $total = count($keys);

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $chunkKeys = array_slice($keys, $i, $chunkSize);
            $chunk = array_intersect_key($properties, array_flip($chunkKeys));

            $q = ProductElementTable::query()
                ->withID($id)
                ->withProperties($chunk);
            $db = $q->exec();

            if ($row = $db->fetch()) {
                $results = array_merge($row, $results);
            }
        }

        return $results;
    }

    public static function get($arRequest)
    {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $code = $arRequest['code'];
            $category = $arRequest['category'];
            $offerId = $arRequest['offer_id'];
            if(!$code){
                throw new \Exception('Не передан символьный код товара');
            }
            if(!$category){
                throw new \Exception('Не передан символьный код категории товара');
            }

            $q = ProductElementTable::query()
                ->withSelect()
                ->withCode($code)
                ->withCategory($category)
                ->withCatalog()
                ->withStores()
                ->withIblockFilter(Constants::IB_CATALOG_CRM);

            $result = self::processCatalog($q);

            if(!$result) {
                throw new \Exception('Товар не найден.', 404);
            }
            $id = $result['id'];

            $properties = Properties::get(['iblock_id' => Constants::IB_CATALOG_CRM, 'is_detail' => true]);

            if ($properties) {
                $productValuesCodes = self::getProductValuesCodes($id, $properties);

                $propertiesValues = Properties::getPropertiesValues([$productValuesCodes], $properties);

                $result['properties'] = Properties::getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $properties);

                self::getProductProperties($result);
            }

            $result['offers'] = null;
            //TYPE_SKU = 3 (товар с торговыми предложениями)
            if ($result['type'] == ProductTable::TYPE_SKU) {
                $result['offer_properties_order'] = Properties::getOrderedOfferPropertiesCodes();

                $result['offers'] = Offers::get(['id' => $id]);
                $offerId = $offerId ?: array_keys($result['offers'])[0];

                foreach ($result['offers'] as $key => $offer) {
                    self::getOfferProperties($result['offers'][$key]);
                }
            }
            \CIBlockElement::CounterInc($offerId ?? $id);
        }

        return $result;
    }

    private static function getProductProperties(&$data) {
        $data['is_new'] = self::getBoolean($data['properties'], 'new');
        $data['images'] = self::getImages($data, 'more_photo');
        $data['related_products_ids'] = self::getRelatedProductsIDs($data['properties']);
        $data['analogues_ids'] = self::getAnaloguesIDs($data['properties']);
        $data['services'] = Services::get([]);
        $data['videos'] = self::getVideos($data['properties']);
        $data['files'] = self::getFiles($data['properties']);
    }

    private static function getOfferProperties(&$data) {
        $data['images'] = self::getImages($data, 'offer_images');
        $data['related_products_ids'] = self::getRelatedProductsIDs($data['properties']);
        $data['analogues'] = self::getAnaloguesIDs($data['properties']);
        $data['files'] = self::getFiles($data['properties']);
    }

    private static function getBoolean(&$properties, $propertyName) {
        $result = (bool)$properties[$propertyName];
        unset($properties[$propertyName]);
        return $result;
    }

    private static function getValues(&$properties, $propertyName) {
        $result = $properties[$propertyName]['value'] ?? null;
        unset($properties[$propertyName]);
        return $result;
    }

    private static function getImages(&$data, $propertyName) {
        $images = self::getValues($data['properties'], $propertyName);

        $result = [];
        if($data['detail_picture']) {
            $result[] = $data['detail_picture'];
        }
        $result = array_merge($result, $images ?? []);

        return array_unique($result, SORT_REGULAR);
    }

    private static function getRelatedProductsIDs(&$properties) {
        $result =
            array_merge(
                $properties['related_products']['value'] ?? [],
                $properties['related_products_offers']['value'] ?? []
            );
        $result = array_map(function ($product) {
            return $product['code'];
        }, $result);
        unset($properties['related_products']);
        unset($properties['related_products_offers']);
        return $result;
    }

    private static function getAnaloguesIDs(&$properties)
    {
        $result =
            array_merge(
                $properties['analogues']['value'] ?? [],
                $properties['analogues_offers']['value'] ?? []
            );
        $result = array_map(function ($product) {
            return $product['code'];
        }, $result);
        unset($properties['analogues']);
        unset($properties['analogues_offers']);
        return $result;
    }

    private static function getVideos(&$properties)
    {
        $ids = $properties['videos']['value'] ? array_column(array_values($properties['videos']['value']), 'code') : null;

        $result = Videos::getByIds(['ids' => $ids]);

        unset($properties['videos']);
        return $result;
    }

    private static function getFiles(&$properties)
    {
        $result = $properties['files']['value'] ?? [];

        unset($properties['files']);
        return $result;
    }

    public static function getAnalogues($arRequest)
    {
        $result = [];
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $analoguesIds = $arRequest['analogues_ids'] ?? [];
            $category = $arRequest['category'];
            $excludeIds = $arRequest['exclude_ids'] ?? [];

            $payload = [];
            if(count($analoguesIds)) {
                $payload['ids'] = $analoguesIds;
            } elseif($category && count($excludeIds)) {
                $payload['category'] = $category;
                $payload['exclude'] = $excludeIds;
            } else {
                throw new \Exception('Невозможно получить аналоги.');
            }
            $res = Catalog::getItemsAndCount($payload);
            $result = $res['items'];
        }

        return $result;
    }

    public static function getRelatedProducts($arRequest)
    {
        $result = [];
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $relatedProductsIds = $arRequest['related_products_ids'] ?? [];

            $payload = [];
            if($relatedProductsIds && count($relatedProductsIds)) {
                $payload['ids'] = $relatedProductsIds;
            } else {
                return [];
            }
            $res = Catalog::getItemsAndCount($payload);
            $result = $res['items'];
        }

        return $result;
    }

    public static function checkIsProductActive($id)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $q = ProductElementTable::query()
                ->withID($id)
                ->withActive()
                ->withIblockFilter([Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS]);
            $db = $q->exec();
            return (bool)$db->fetch();
        }

        return false;
    }

    public static function getActiveProductIds($ids)
    {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            if(count($ids)){
                $q = ProductElementTable::query()
                    ->withActive()
                    ->withIDSelect()
                    ->withIDs($ids)
                    ->withIblockFilter([Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS]);
                $db = $q->exec();
                while ($row = $db->fetch()) {
                    $result[] = $row['ID'];
                }
            }
        }

        return $result;
    }
}
