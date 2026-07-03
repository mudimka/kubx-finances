<?php

namespace Legacy\API;

use Legacy\IblockController\Promotions as PromotionsController;

class Promotions
{
    public static function get($arRequest)
    {
        return PromotionsController::get($arRequest);
    }
    public static function getByCode($arRequest){
        return PromotionsController::getByCode($arRequest);
    }
}
