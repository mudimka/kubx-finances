<?php

namespace Legacy\IblockController;

use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Legacy\API\Properties;
use Legacy\Catalog\OfferElementTable;
use Legacy\General\Constants;

class Offers
{
    private static function processOffers($query, $properties)
    {
        $result = [];
        $db = $query->exec();

        $productsValuesCodes = [];
        while ($res = $db->fetch()) {
            $productsValuesCodes[] = $res;
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

            $result[$res['ID']] = [
                'id' => $res['ID'],
                'name' => trim($res['NAME']),
                'available' => $res['AVAILABLE'] == 'Y',
                'measure' => [
                    'title' => $measure['MEASURE']['MEASURE_TITLE'],
                    'symbol' => $measure['MEASURE']['SYMBOL'],
                ],
                'quantity' => $res['PRODUCT_QUANTITY'],
                'preview_picture' => File::getFilesInfo($res['PREVIEW_PICTURE'],false, false),
                'preview_text' => $res['PREVIEW_TEXT'],
                'detail_picture' => File::getFilesInfo($res['DETAIL_PICTURE'],false, false),
                'detail_text' => $res['DETAIL_TEXT'],
                'price' => $price,
                'discount_price' => $price != $discount_price ? $discount_price : null,
                'discount_percent' => $price != $discount_price
                    ? round(($price - $discount_price) * 100 / $price)
                    : null,
                'stores' => $stores_count,
                'seo' => SEO::getOfferSEO($res['ID']),
            ];
        }

        $propertiesValues = Properties::getPropertiesValues($productsValuesCodes, $properties);

        foreach ($productsValuesCodes as $productValuesCodes) {
            $id = $productValuesCodes['ID'];
            $propertiesData = Properties::getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $properties);
            $result[$id]['properties'] = $propertiesData;
        }

        $offerProps = Properties::getOfferProperties();

        $offerPropsCodes = [];
        foreach ($offerProps as $prop) {
            $offerPropsCodes[] = mb_strtolower($prop['CODE']);
        }

        foreach ($result as $key => $item) {
            $is_valid_offer = false;
            foreach ($offerPropsCodes as $code) {
                if ($item['properties'][$code] && $item['properties'][$code]['value']) {
                    $is_valid_offer = true;
                    break;
                }
            }
            if (!$is_valid_offer) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    public static function get($arRequest)
    {
        $result = [];
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $id = (int)$arRequest['id'];
            if ($id > 0) {
                $properties = Properties::get(['iblock_id' => Constants::IB_CATALOG_CRM_OFFERS, 'is_detail' => true]);
                $q = OfferElementTable::query()
                    ->withDefault($id)
                    ->withStores()
                    ->withProperties($properties);

                $result = self::processOffers($q, $properties);
            }
        }

        return $result;
    }
}
