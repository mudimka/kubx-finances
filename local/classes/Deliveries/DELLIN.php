<?php

namespace Legacy\Deliveries;

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Sale;
use Bitrix\Main\Web\HttpClient;
use DellinShipping\NetworkService;
use Legacy\API\User;
use Legacy\General\DataProcessor;
use Legacy\Sale\Basket as LSBasket;
use Legacy\General\Constants;
use Bitrix\Sale\Delivery\Services\Manager;

class DELLIN
{
    public static function getMethods()
    {
        $methods = [];

        if (Loader::includeModule('sale')) {
            $deliveryServices = \Bitrix\Sale\Delivery\Services\Table::getList([
                'filter' => [
                    'ACTIVE' => 'Y',
                    '%XML_ID' => 'DELLIN_'
                ],
                'order' => ['SORT' => 'ASC']
            ]);

            while ($service = $deliveryServices->fetch()) {
                $methods[] = [
                    'code' => $service['XML_ID'],
                    'id' => $service['ID'],
                    'name' => $service['NAME'],
                    'description' => $service['DESCRIPTION'],
                    'logo' => getFilePath($service['LOGOTIP']),
                    'type' => substr($service['XML_ID'], strrpos($service['XML_ID'], '_') + 1),
                ];
            }
        }
        return $methods;
    }

    public static function countDeliveryOrder($arRequest)
    {
        if (Loader::IncludeModule('dellindev.shipment') && Loader::IncludeModule('sale')) {
            if (!empty($arRequest['location']) && !empty($arRequest['delivery_service'])) {
                $deliveryService = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($arRequest['delivery_service']);
                if (is_null($deliveryService)) {
                    return false;
                }

                $userID = User::getID();
                $order = Sale\Order::create(Context::getCurrent()->getSite(), User::getID());
                $basket = LSBasket::loadItems()->getBasket();
                $order->setBasket($basket);

                $userType = 'contact';
                if ($userID > 0) {
                    $userType = User::getUserGroup($userID)['code'] ?: 'contact';
                }
                $personTypeID = constant('Legacy\General\Constants::PERSON_TYPE_CRM_' . strtoupper($userType));
                $order->setPersonTypeId($personTypeID);

                $propertyCollection = $order->getPropertyCollection();
                $location = Sale\Location\LocationTable::getList([
                    'filter' =>  [
                        'NAME.LANGUAGE_ID' => 'ru',
                        'NAME.NAME' => $arRequest['location']
                    ]
                ])->fetch();
                $locationProp = $propertyCollection->getDeliveryLocation();
                $locationProp->setValue($location['CODE']);

                $shipmentCollection = $order->getShipmentCollection();
                $shipment = $shipmentCollection->createItem($deliveryService);
                $shipmentItemCollection = $shipment->getShipmentItemCollection();
                foreach ($basket as $basketItem) {
                    $item = $shipmentItemCollection->createItem($basketItem);
                    $item->setQuantity($basketItem->getQuantity());
                }

                $calc = Manager::calculateDeliveryPrice($shipment, $arRequest['delivery_service']);
                $price = (float)$calc->getDeliveryPrice();
                $calcDate = explode(' ', $calc->getPeriodDescription())[0];
                $date = DataProcessor::processDeliveryDates($calcDate);

                return [
                    'price' => $price,
                    'date' => $date
                ];
            }
        }
        return false;
    }

    public static function getPvzList($arRequest)
    {
        $result = [];

        if (Loader::IncludeModule('dellindev.shipment') && Loader::IncludeModule('sale')) {
            if (!empty($arRequest['location'])) {
                $deliveryService = \Bitrix\Sale\Delivery\Services\Table::getList([
                    'filter' => ['ID' => Constants::DELIVERY_DELOVYE_LINII_SAMOVYVOZ]
                ])->fetch();
                $key = $deliveryService['CONFIG']['MAIN']['APIKEY'];

                $citiesRes = NetworkService::SearchCity($arRequest['location'], $key);
                $service = new NetworkService();
                $cityKladr = $service->SelectCityByRegion($citiesRes, $arRequest['location'], '', $arRequest['zip'])->code;

                $terminals = NetworkService::GetTerminals($key, $cityKladr)['terminals'];
                foreach ($terminals as $terminal) {
                    if ($terminal->giveoutCargo == true && !empty($terminal->calcSchedule->arrival)) {
                        $result[] = [
                            'value' => $terminal->id,
                            'address' => $terminal->address,
                            'work_time' => $terminal->calcSchedule->arrival,
                        ];
                    }
                }
            }
        }
        return $result;
    }
}