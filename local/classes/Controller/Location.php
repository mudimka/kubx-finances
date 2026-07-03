<?php

namespace Legacy\Controller;

use Bitrix\Main\Service\GeoIp\Manager;
use Bitrix\Main\Service\GeoIp\SypexGeo;
use Legacy\General\Constants;
use Bitrix\Main\Config\Option;

class Location
{
    private static function getUserLocationOld() {
        //Определение координат по IP
        $ip = Manager::getRealIp();
        $geoData = new SypexGeo();
        $res = $geoData->getDataResult($ip, 'ru');

        return Array(
            'gpsN' => $res->getGeoData()->latitude,
            'gpsS' => $res->getGeoData()->longitude,
            'city' => $res->getGeoData()->cityName,
            'region' => $res->getGeoData()->regionName,
        );
    }

    public static function getIP() {
        $ip = Manager::getRealIp();
        return $ip;
    }

    public static function getUserLocation()
    {
        $ip = self::getIP();
        if (!$ip) {
            return null;
        }

        $location = Dadata::getAddressByIP(['ip' => $ip]);

        return $location['data']['city'] ?? 'Москва';
    }

    public static function getUserCityWithZip()
    {
        $ip = self::getIP();
        if (!$ip) {
            return null;
        }

        $location = Dadata::getAddressByIP(['ip' => $ip]);

        return [
            'city' => $location['data']['city'] ?? 'Москва',
            'zip' => $location['data']['postal_code'] ?? '101000',
        ];
    }
}
