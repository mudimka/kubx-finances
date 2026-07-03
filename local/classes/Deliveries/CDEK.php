<?php

namespace Legacy\Deliveries;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Catalog\PriceTable;
use Legacy\General\Constants;
use Legacy\General\DataProcessor;

class CDEK
{
    private static function getDeliveryProfiles($id = null)
    {
        $params = [
            'filter' => [
                'PARENT_ID' => Constants::DELIVERY_SDEK,
                'ACTIVE' => 'Y'
            ]
        ];
        if ($id) {
            $params['filter']['ID'] = $id;
        }

        $db = Services\Table::getList($params);
        $result = [];
        while ($res = $db->fetch()) {
            $result[] = [
                'id' => $res['ID'],
                'code' => $res['CODE'],
                'name' => $res['NAME'],
                'description' => $res['DESCRIPTION'],
                'profile' => $res['CONFIG']['MAIN']['PROFILE_ID']
            ];
        }
        return $result;
    }

    public static function countDeliveryOrder($arRequest)
    {
        if (Loader::includeModule('ipol.sdek') && Loader::includeModule('sale')) {
            if (!empty($arRequest['location']) && !empty($arRequest['delivery_service'])) {
                $id = $arRequest['delivery_service'];
                $delivProfile = self::getDeliveryProfiles($id)[0];

                $basketParams = [
                    'FUSER_ID' => Fuser::getId(),
                    'ORDER_ID' => 'NULL',
                    'LID' => SITE_ID
                ];
                $goods = \CDeliverySDEK::getBasketGoods($basketParams);
                \CDeliverySDEK::setGoods($goods);

                $arOrder = [
                    'CITY_TO' => \CDeliverySDEK::zajsonit($arRequest['location']),
                    'WEIGHT' => \CDeliverySDEK::$goods['W'] * 1000,
                    'PRICE' => \CDeliverySDEK::$orderPrice,
                    'DELIVERY' => $delivProfile['id'],
                    'GOODS' => $goods,
                ];
                $cntRes = \CDeliverySDEK::countDelivery($arOrder);

                $result = [];
                if (!empty($cntRes)) {
                    $price = (float)str_replace(['&nbsp;', ' '], '', $cntRes[$delivProfile['profile']]);
                    $date = DataProcessor::processDeliveryDates($cntRes[$delivProfile['profile'][0] . '_date']);

                    $result = [
                        'price' => $price,
                        'date' => $date,
                        'tariff' => $_SESSION['IPOLSDEK_CHOSEN'][$delivProfile['profile']]
                    ];
                }

                return $result;
            }
        }
        return false;
    }

    public static function getPvzList($arRequest)
    {
        $result = [];

        if (Loader::includeModule('ipol.sdek') && Loader::includeModule('sale')) {
            $basketParams = [
                'FUSER_ID' => Fuser::getId(),
                'ORDER_ID' => 'NULL',
                'LID' => SITE_ID
            ];
            $goods = \CDeliverySDEK::getBasketGoods($basketParams);
            \CDeliverySDEK::setGoods($goods);

            ob_start();
            \Ipolh\SDEK\pvzWidjetHandler::getCityPvz([
                'city' => $arRequest['location'],
                'mode' => 'PVZ',
                'weight' => \CDeliverySDEK::$orderWeight,
                'goods' => \CUtil::PhpToJSObject($goods)
            ]);
            $pvzRes = ob_get_clean();
            ob_end_clean();

            $pvzRes = json_decode($pvzRes, true)['POINTS'];
            foreach ($pvzRes as $pvz) {
                $result[] = [
                    'value' => '#S'.$pvz['Name'],
                    'address' => $pvz['Address'],
                    'work_time' => $pvz['WorkTime']
                ];
            }
        }

        return $result;
    }
}