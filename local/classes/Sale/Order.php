<?php

namespace Legacy\Sale;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Legacy\Deliveries\CDEK;
use Legacy\Deliveries\DELLIN;
use Legacy\General\Constants;

class Order
{
    /** @var Sale\Order $order */
    var $order;
    /** @var Sale\Basket $basket */
    var $basket;
    /** @var integer personTypeId */
    var $personTypeID;
    var $orderProperties;
    var $deliveryType;

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

    public static function initOrder($basket, $personTypeID, $userId, $orderProperties)
    {
        $self = new self;
        $self->basket = $basket;
        $self->personTypeID = $personTypeID;
        $self->orderProperties = $self->processOrderProperties($orderProperties);
        $self->order = Sale\Order::create(Context::getCurrent()->getSite(), $userId);
        $self->order->setPersonTypeId($self->personTypeID);
        $self->order->setBasket($self->basket);

        return $self;
    }

    private function processOrderProperties($orderProperties)
    {
        if (!empty($orderProperties['location'])) {
            $locationCode = Sale\Location\LocationTable::getList([
                'filter' =>  [
                    'NAME.LANGUAGE_ID' => 'ru',
                    'NAME.NAME' => $orderProperties['location']
                ]
            ])->fetch()['CODE'];

            $orderProperties['location_name'] = $orderProperties['location'];
            $orderProperties['location'] = $locationCode;
            $orderProperties['sdek_location'] = $locationCode;
        }
        if ($orderProperties['receiving_type'] == 'transport_company') {
            $deliveryService = $orderProperties['delivery_service'];
            $this->getDeliveryType($deliveryService);

            if ($this->deliveryType['code'] == 'dellin' && $this->deliveryType['code'] == 'pickup') {
                $orderProperties['terminal_id'] = $orderProperties['punkt'];
                unset($orderProperties['punkt']);
            }
        }

        return $orderProperties;
    }

    private function getDeliveryType($deliveryService)
    {
        if (defined('Legacy\\General\\Constants::DELIVERY_SDEK_SAMOVYVOZ') &&
            Constants::DELIVERY_SDEK_SAMOVYVOZ == $deliveryService) {
            $this->deliveryType = [
                'code' => 'cdek',
                'type' => 'pickup'
            ];
        } elseif (defined('Legacy\\General\\Constants::DELIVERY_SDEK_DOSTAVKA_KUREROM') &&
            Constants::DELIVERY_SDEK_DOSTAVKA_KUREROM == $deliveryService) {
            $this->deliveryType = [
                'code' => 'cdek',
                'type' => 'courier'
            ];
        } elseif (defined('Legacy\\General\\Constants::DELIVERY_DELOVYE_LINII_SAMOVYVOZ') &&
            Constants::DELIVERY_DELOVYE_LINII_SAMOVYVOZ == $deliveryService) {
            $this->deliveryType = [
                'code' => 'dellin',
                'type' => 'pickup'
            ];
        } elseif (defined('Legacy\\General\\Constants::DELIVERY_DELOVYE_LINII_DOSTAVKA_KUREROM') &&
            Constants::DELIVERY_DELOVYE_LINII_DOSTAVKA_KUREROM == $deliveryService) {
            $this->deliveryType = [
                'code' => 'dellin',
                'type' => 'courier'
            ];
        }
    }

    public function setCoupon($coupon)
    {
        Sale\DiscountCouponsManager::init(
            Sale\DiscountCouponsManager::MODE_ORDER, [
                "userId" => $this->order->getUserId(),
                "orderId" => $this->order->getId()
            ]
        );

        $res = Sale\DiscountCouponsManager::add($coupon);
        if (!$res) {
            $coupons = Sale\DiscountCouponsManager::get(true, [], true);
            $statusList = Sale\DiscountCouponsManager::getStatusList(true);
            throw new \Exception('Промокод '.$statusList[$coupons[$coupon]['STATUS']]);
        }

        $discounts = $this->order->getDiscount();
        $discounts->calculate();
        $this->save();
    }

    public function clearCoupon()
    {
        Sale\DiscountCouponsManager::clear(true);
        $discounts = $this->order->getDiscount();
        $discounts->calculate();
        $this->save();
    }

    public function getCoupons()
    {
        return Sale\DiscountCouponsManager::get(true, [], false);
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getProperty($propertyCollection, $code)
    {
        foreach ($propertyCollection as $property) {
            if ($property->getField('CODE') == $code && $property->getPersonTypeId() == $this->personTypeID) {
                return $property;
            }
        }

        return null;
    }

    public function setOrderProperties()
    {
        $propertyCollection = $this->order->getPropertyCollection();
        foreach ($this->orderProperties as $orderProperty => $value) {
            if ($property = $this->getProperty($propertyCollection, strtoupper($orderProperty))) {
                $property->setValue(trim($value));
            }
        }
    }

    public function setShipment()
    {
        $shipmentCollection = $this->order->getShipmentCollection();

        $deliveryService = $this->orderProperties['delivery_service'];

        $this->validateDelivery();

        if (is_null($deliveryService)) {
            throw new \Exception('Служба доставки не найдена.');
        }

        $shipment = $shipmentCollection->createItem(\Bitrix\Sale\Delivery\Services\Manager::getObjectById($deliveryService));

        if ($this->orderProperties['delivery_service'] == Constants::DELIVERY_SAMOVYVOZ) {
            $stockId = $this->orderProperties['stock_id'];
            if (!$stockId) {
                throw new \Exception('Склад самовывоза не найден');
            }
            $shipment->setStoreId($stockId);
        } elseif ($this->deliveryType['code'] == 'cdek' || $this->deliveryType['code'] == 'dellin') {
            if ($this->deliveryType['code'] == 'cdek') {
                $price = CDEK::countDeliveryOrder([
                    'location' => $this->orderProperties['location_name'],
                    'delivery_service' => $this->orderProperties['delivery_service'],
                ])['price'];
            } else {
                $price = DELLIN::countDeliveryOrder([
                    'location' => $this->orderProperties['location_name'],
                    'delivery_service' => $this->orderProperties['delivery_service'],
                ])['price'];
            }

            $shipment->setFields([
                'DELIVERY_ID' => $this->orderProperties['delivery_service'],
                'CUSTOM_PRICE_DELIVERY' => 'Y',
                'PRICE_DELIVERY' => $price
            ]);
        }

        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        foreach ($this->basket as $basketItem) {
            $item = $shipmentItemCollection->createItem($basketItem);
            $item->setQuantity($basketItem->getQuantity());
        }
    }

    private function validateDelivery()
    {
        $deliveryID = $this->orderProperties['delivery_service'];

        if ($this->orderProperties['receiving_type'] == 'transport_company' && empty($this->deliveryType)) {
            if (
                (defined('Legacy\\General\\Constants::DELIVERY_SDEK') && $deliveryID == Constants::DELIVERY_SDEK) ||
                (defined('Legacy\\General\\Constants::DELIVERY_DELOVYE_LINII') && $deliveryID == Constants::DELIVERY_DELOVYE_LINII)
            ) {
                throw new \Exception('Выберите тип доставки.');
            }
        }
    }

    public function setPayment()
    {
        $paysystem = \Bitrix\Sale\PaySystem\Manager::getObjectById($this->orderProperties['payment']);
        if (is_null($paysystem)) {
            throw new \Exception('Тип оплаты не найден.');
        }

        $paymentCollection = $this->order->getPaymentCollection();
        $payment = $paymentCollection->createItem($paysystem);
        $payment->setField("SUM", $this->order->getPrice());
        $payment->setField("CURRENCY", $this->order->getCurrency());
    }

    public function createOrder()
    {
        $obResult = $this->order->save();

        if (!$obResult->isSuccess()) {
            throw new \Exception(implode('. ', $obResult->getErrorMessages()));
        }
        return $obResult->getId();
    }

    public static function load($orderId)
    {
        $self = new self;
        $self->order = Sale\Order::load($orderId);

        return $self;
    }

    public function getField($code)
    {
        return $this->order->getField($code);
    }

    public function getUserId()
    {
        return $this->order->getUserId();
    }

    public function getId()
    {
        return $this->order->getId();
    }

    public function cancel()
    {
        $paymentCollection = $this->order->getPaymentCollection();
        if ($paymentCollection->hasPaidPayment()) {
            $payment = $paymentCollection[0];
            $payment->setPaid("N");
            $payment->setReturn("Y");
        }

        $this->order->setField('STATUS_ID', 'D');
        $this->order->setField('CANCELED', 'Y');

        $this->order->save();
    }
}
