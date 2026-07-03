<?php

namespace Legacy\Sale;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Legacy\Main\User;

class Basket
{
    /** @var Sale\Basket $basket */
    var $basket;

    private function __construct()
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить модуль "sale".');
        }

        if (!Loader::includeModule('catalog')) {
            throw new \Exception('Не удалось подключить модуль "catalog".');
        }

        if (!Loader::includeModule('iblock')) {
            throw new \Exception('Не удалось подключить модуль "iblock".');
        }
    }

    public static function loadItems()
    {
        $self = new self;
        $self->basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Context::getCurrent()->getSite());
        return $self;
    }

    public function getPrice()
    {
        return $this->basket->getPrice();
    }

    public function getBasePrice()
    {
        return $this->basket->getBasePrice();
    }

    public function getDiscount()
    {
        return self::getBasePrice() - self::getPrice();
    }

    public function getLength()
    {
        return $this->basket->count();
    }

    public function getCoupons()
    {
        return Sale\DiscountCouponsManager::get(true, [], false);
    }

    public function save()
    {
        return $this->basket->save();
    }

    public function delete($id)
    {
        $basketItem = $this->basket->getItemById($id);
        $basketItem->delete();
        return $this->save();
    }

    public function getItemById($id)
    {
        return $this->basket->getItemById($id);
    }

    public function getBasket()
    {
        return $this->basket;
    }
}
