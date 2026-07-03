<?php

namespace Legacy\API;

use Legacy\General\Constants;

class Delivery
{
    public static function getPvzList($arRequest)
    {
        return self::callFunc($arRequest['delivery_type'], 'getPvzList', $arRequest);
    }

    public static function countDeliveryOrder($arRequest)
    {
        return self::callFunc($arRequest['delivery_type'], 'countDeliveryOrder', $arRequest);
    }

    private static function callFunc($className, $function, $params)
    {
        if (empty($className) || !class_exists("Legacy\\Deliveries\\".$className)) {
            throw new \Exception('Метод не найден');
        }

        $result = null;
        if (method_exists("Legacy\\Deliveries\\".$className, $function)) {
            $result = call_user_func("Legacy\\Deliveries\\".$className."::".$function, $params);
        }
        return $result;
    }
}