<?php

namespace Legacy\API;

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

    public static function findOrganization($arRequest) {
        $result = [];

        if ($arRequest['query']) {
            $token = self::getToken();
            $dadata = new \Dadata\DadataClient($token, null);
            $suggestions = $dadata->suggest('party', $arRequest['query'], 20);

            foreach ($suggestions as $suggestion) {
                $result[] = [
                    'name' => $suggestion['value'],
                    'address' => $suggestion['data']['address']['value'],
                    'postal_code' => $suggestion['data']['address']['data']['postal_code'],
                    'inn' => $suggestion['data']['inn'],
                    'kpp' => $suggestion['data']['kpp'],
                    'ogrn' => $suggestion['data']['ogrn'],
                    'director' => $suggestion['data']['management']['name'],
                ];
            }
        }

        return $result;
    }


    public static function findCities($arRequest)
    {
        $result = [];
        $token = self::getToken();

        if ($arRequest['query']) {
            $dadata = new \Dadata\DadataClient($token, null);
            $suggestions = $dadata->suggest('address', $arRequest['query'], 20, [
                'language' => LANGUAGE_ID,
                'from_bound' => ['value' => 'city'],
                'to_bound' => ['value' => 'city'],
                'locations' => ['country_iso_code' => 'RU']
            ]);

            foreach ($suggestions as $res) {
                $result[] = [
                    'country' => $res['data']['country'],
                    'region' => $res['data']['region_with_type'],
                    'region_full' => $res['data']['region'].' '.$res['data']['region_type_full'],
                    'zip' => $res['data']['postal_code'],
                    'city' => $res['data']['city'],
                    'city_type' => $res['data']['city_type'],
                    'city_type_full' => $res['data']['city_type_full'],
                ];
            }
        } else {
            throw new \Exception('Не введено название города.');
        }

        return $result;
    }

    public static function findAddressDetail($arRequest)
    {
        $result = [];
        $token = self::getToken();

        if ($arRequest['query']) {
            $dadata = new \Dadata\DadataClient($token, null);
            $suggestions = $dadata->suggest('address', $arRequest['query'], 20);

            foreach ($suggestions as $res) {
                $result[] = [
                    'country' => $res['data']['country'],
                    'region' => $res['data']['region_with_type'],
                    'city' => $res['data']['city'],
                    'city_type' => $res['data']['city_type'],
                    'city_type_full' => $res['data']['city_type_full'],
                    'street' => $res['data']['street'],
                    'street_type' => $res['data']['street_type'],
                    'street_type_full' => $res['data']['street_type_full'],
                    'house' => $res['data']['house'],
                    'house_type' => $res['data']['house_type'],
                    'house_type_full' => $res['data']['house_type_full'],
                    'flat' => $res['data']['flat'],
                    'flat_type' => $res['data']['flat_type'],
                    'flat_type_full' => $res['data']['flat_type_full'],
                    'value' => $res['value'],
                    'unrestricted_value' => $res['unrestricted_value'],
                ];
            }
        }

        return $result;
    }

    public static function findAddress($arRequest)
    {
        $result = [];
        $token = self::getToken();

        if ($arRequest['query']) {
            $dadata = new \Dadata\DadataClient($token, null);
            $suggestions = $dadata->suggest('address', $arRequest['query'], 20);

            foreach ($suggestions as $res) {
                $house = $res['data']['house'];
                if ($house && !empty($res['data']['block'])) {
                    $house .= ' '.$res['data']['block_type'].' '.$res['data']['block'];
                }

                $result[] = [
                    'value' => $res['value'],
                    'unrestricted_value' => $res['unrestricted_value'],
                    'postal_code' => $res['data']['postal_code'],
                    'region' => $res['data']['region_with_type'],
                    'city' => $res['data']['city'],
                    'settlement' => $res['data']['settlement'],
                    'street' => $res['data']['street'],
                    'house' => $house,
                    'flat' => $res['data']['flat'],
                ];
            }
        }

        return $result;
    }
}

