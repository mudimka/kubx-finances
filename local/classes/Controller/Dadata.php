<?php

namespace Legacy\Controller;

use Bitrix\Main\Config\Option;

class Dadata
{
    private static function getToken() {
        $token = Option::get('kubx.settings', 'dadata');

        if(empty($token)) {
            throw new \Exception('Непредвиденная ошибка!');
        }
        return $token;
    }


    public static function getUserCity()
    {
        $ip = Location::getIP();
        if (!$ip) {
            return null;
        }

        $token = self::getToken();
        $dadata = new \Dadata\DadataClient($token, null);

        $location = $dadata->iplocate($ip);

        return $location['data']['city'] ?? 'Москва';
    }

    public static function getAddressByIP($arRequest)
    {
        $ip = $arRequest['ip'];

        $token = self::getToken();
        $dadata = new \Dadata\DadataClient($token, null);

        return $dadata->iplocate($ip);
    }
}

