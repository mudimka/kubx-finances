<?php

namespace Legacy\Sale;

use Bitrix\Main\Loader;
use Bitrix\Sale;
use Bitrix\Sale\Delivery\Restrictions\Manager as RestrictionsManager;

class Delivery
{
    private static $allowedRestrictions = [
        'ByPaySystem',
        'ByLocation',
        'ExcludeLocation',
        'ByPrice'
    ];

    public static function getRestrictions()
    {
        $result = [];

        if (Loader::includeModule('sale')) {
            $deliveryServices = Sale\Delivery\Services\Manager::getActiveList();

            foreach ($deliveryServices as $service) {
                if ($service['ID'] == 1 || $service['RESTRICTIONS_EXIST'] == 0) {
                    continue;
                }

                $restrictions = RestrictionsManager::getRestrictionsList($service['ID']);
                foreach ($restrictions as $restriction) {
                    $restrictionType = substr($restriction['CLASS_NAME'], strrpos($restriction['CLASS_NAME'], '\\') + 1);

                    if (in_array($restrictionType, self::$allowedRestrictions)) {
                        switch ($restrictionType) {
                            case 'ByPaySystem':
                                if (empty($payments)) {
                                    $paymentsDB = Sale\PaySystem\Manager::getList([
                                        'filter' => [
                                            'ACTIVE' => 'Y',
                                            'ENTITY_REGISTRY_TYPE' => 'ORDER'
                                        ],
                                    ]);
                                    while ($res = $paymentsDB->fetch()) {
                                        $payments[] = (int)$res['ID'];
                                    }
                                }

                                $restrictionParams = $restriction['CLASS_NAME']::prepareParamsValues($restriction['PARAMS'] ?: [], $service['ID']);
                                $result[$service['ID']]['pay_systems_ids'] = array_values(array_intersect($payments, $restrictionParams['PAY_SYSTEMS']));
                                break;

                            case 'ByLocation':
                                $result = self::getDeliveryLocations($result, $service['ID']);
                                break;

                            case 'ExcludeLocation':
                                if (empty($result[$service['ID']]['exclude_location'])) {
                                    $result = self::getDeliveryLocations($result, $service['ID']);
                                }
                                break;

                            case 'ByPrice':
                                $result[$service['ID']]['price'] = [
                                    'min_price' => (float)$restriction['PARAMS']['MIN_PRICE'] ?: 0,
                                    'max_price' => (float)$restriction['PARAMS']['MAX_PRICE'] ?: PHP_FLOAT_MAX,
                                ];
                        }
                    }
                }
            }
        }
        return $result;
    }

    private static function getDeliveryLocations($result, $deliveryID)
    {
        $deliveryLocations = Sale\Delivery\DeliveryLocationTable::getList([
            'filter' => [
                '=DELIVERY_ID' => $deliveryID,
            ],
        ])->fetchAll();

        $locationCodes = array_column($deliveryLocations, 'LOCATION_CODE');
        $locations = Sale\Location\LocationTable::getList([
            'select' => ['*', 'NAME_' => 'NAME.*'],
            'filter' => [
                'NAME.LANGUAGE_ID' => 'ru',
                'CODE' => $locationCodes
            ]
        ])->fetchAll();

        foreach ($deliveryLocations as $location) {
            $index = array_search($location['LOCATION_CODE'], array_column($locations, 'CODE'));
            if (is_numeric($index)) {
                $type = $location['LOCATION_TYPE'] == 'LE' ? 'exclude_location' : 'location';
                $result[$deliveryID][$type][] = $locations[$index]['NAME_NAME'];
            }
        }
        return $result;
    }
}
