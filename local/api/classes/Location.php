<?php

namespace Legacy\API;

use Bitrix\Main\Service\GeoIp\Manager;
use Bitrix\Main\Service\GeoIp\SypexGeo;
use Legacy\General\Constants;
use Legacy\HighLoadBlock\Entity;
use Legacy\Controller\Location as LocationController;
use Bitrix\Main\Config\Option;

class Location
{
    public static function get()
    {
        $location = $_COOKIE['city'];

        if (empty($location)) {
            $location = LocationController::getUserLocation();
            if (!$location) {
                $location = Entity::getInstance()->getRow(Constants::HLBLOCK_CITIES_LIST,
                    ['filter' => ['UF_DEFAULT' => 1]]
                )['UF_CITY'];
            }

            setcookie('city', $location, strtotime('+30 days'), '/');
        }
        return $location;
    }

    public static function change($arRequest)
    {
        if(empty($arRequest['city'])) {
            throw new \Exception('Выберите город!');
        } else {
            $cities = Dadata::findCities(['query' => $arRequest['city']]);

            $cityInfo = $cities[0];

            if(empty($cityInfo)) {
                throw new \Exception('Город не найден.');
            }

            setcookie('city', $cityInfo['city'], strtotime('+30 days'), '/');
            return true;
        }
    }

    public static function getDefaultCities()
    {
        $result = [];

        $db = Entity::getInstance()->getList(Constants::HLBLOCK_CITIES_LIST, [
            'order' => ['UF_SORT' => 'ASC']
        ]);

        foreach ($db as $res) {
            $result[] = $res['UF_CITY'];
        }
        return $result;
    }

    public static function findCities($arRequest)
    {
        if ($arRequest['query']) {
            $result = Dadata::findCities(['query' => $arRequest['query']]);
        } else {
            throw new \Exception('Введите название города!');
        }
        return $result;
    }
}
