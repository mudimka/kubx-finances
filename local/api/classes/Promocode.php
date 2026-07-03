<?php

namespace Legacy\API;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Discount;
use Bitrix\Sale;
use Legacy\General\Constants;
use CCatalogProduct;

class Promocode
{
    public static function get($arRequest)
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули');
        }

        $result = null;

        $fuid = Sale\Fuser::getId();
        $basket = Sale\Basket::loadItemsForFUser($fuid, Context::getCurrent()->getSite());
        $basket->refreshData();

        $discountContext = new Discount\Context\Fuser($fuid);

        $discounts = Discount::buildFromBasket($basket, $discountContext);

        if ($discounts) {
            $discounts->calculate();

            $promocode = $discounts->getApplyResult(true);

            if (!empty($promocode['COUPON_LIST'])) {
                $discountPrices = [];
                foreach ($promocode['PRICES']['BASKET'] as $key => $item) {
                    $discountPrices[$key] = $item['DISCOUNT'];
                }

                $coupon = current($promocode['COUPON_LIST']);

                $discount = [];
                foreach ($promocode['DISCOUNT_LIST'] as $item) {
                    if ($item['REAL_DISCOUNT_ID'] == $coupon['DATA']['DISCOUNT_ID']) {
                        $discount = $item;
                        break;
                    }
                }

                $result = [
                    'name' => $discount['NAME'],
                    'coupon' => $coupon['COUPON'],
                    'discount_id' => $coupon['DATA']['DISCOUNT_ID'],
                    'discounts' => $discountPrices
                ];
            }
        }

        return $result;
    }


    public static function apply($arRequest)
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули');
        }

        $coupon = $arRequest['code'];
        if(!$coupon){
            throw new \Exception(json_encode(['promocode' => 'Введите промокод.']));
        }

        $couponResult = Sale\DiscountCouponsManager::add($coupon);

        if ($couponResult) {
            return Basket::get($arRequest);
        } else {
            throw new \Exception(json_encode(['promocode' => 'Промокод не действителен.']));
        }
    }

    public static function cancel($arRequest)
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули');
        }

        $coupon = $arRequest['code'];
        if ($coupon) {
            $result = Sale\DiscountCouponsManager::delete($coupon);

            if ($result) {
                return Basket::get($arRequest);
            } else {
                throw new \Exception(json_encode(['promocode' => 'Ошибка при отмене промокода.']));
            }
        }
    }
}
