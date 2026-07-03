<?php

namespace Legacy\General;

use Bitrix\Main\Config\Option;

class Helper
{
    public static function CurlBitrix24($method, $arData=array()){
        $queryUrl = Option::get('kubx.settings', 'b24_address') . $method;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $queryUrl,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ));
        if(!empty($arData)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($arData));
        }
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result,true);
    }

}
