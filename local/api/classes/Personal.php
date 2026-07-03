<?php

namespace Legacy\API;

use Legacy\General\Helper;
use Legacy\IblockController\ReturnProductRequests;
use Legacy\Sale\OrderTable;
use Bitrix\Main\Loader;
use Legacy\Sale\Order as LSOrder;
use Legacy\Sale\BasketElementTable;
use Legacy\General\Constants;

class Personal
{
    private static function processList($query)
    {
        $result = [];

        $db = $query->exec();

        $result['count'] = $db->getCount();

        while ($arr = $db->fetch()) {
            $result['items'][] = [
                'id' => $arr['ID'],
                'number' => $arr['ACCOUNT_NUMBER'],
                'date' => $arr['DATE_INSERT']->format('c'),
                'price' => formatPrice($arr['PRICE']),
                'discount' => formatPrice($arr['DISCOUNT_ALL']),
                'base_price' => formatPrice($arr['PRICE'] + $arr['DISCOUNT_ALL']),
                'status' => [
                    'name' => $arr['STATUS_NAME'],
                    'color' => $arr['STATUS_COLOR'],
                    'code' => $arr['STATUS_CODE'],
                ],
                'basket_length' => $arr['BASKET_LENGTH'],
            ];
        }

        return $result;
    }

    private static function processOrder($query)
    {
        $props = [];
        $orderInfo = false;
        $db = $query->exec();

        $oid = null;
        while ($arr = $db->fetch()) {
            $props[strtolower($arr['PROPERTY_CODE'])] = $arr['PROPERTY_VALUE'];

            if(!$orderInfo) {
                $oid = $arr['ID'];
                $createDate = date_create($arr['DATE_STATUS'])->setTime(0,0);

                $currentDate = date_create()->setTime(0,0);

                $orderInfo = [
                    'id' => $oid,
                    'number' => $arr['ACCOUNT_NUMBER'],
                    'status' => [
                        'name' => $arr['STATUS_NAME'],
                        'color' => $arr['STATUS_COLOR'],
                        'code' => $arr['STATUS_CODE'],
                    ],
                    'date' => $arr['DATE_INSERT']->format('c'),
                    'return_allowed' => date_diff($currentDate, $createDate)->format('%a') <= 14,
                    'return_request_created' => ReturnProductRequests::isRequestWasCreated(['orderId' => $oid]),
                    'cancel_allowed' => self::isOrderCanBeCanceled($arr['STATUS_CODE']),
                    'total_price' => formatPrice($arr['PRICE']),
                    'delivery_price' => formatPrice($arr['PRICE_DELIVERY']),
                    'order_price' => formatPrice($arr['PRICE'] - $arr['PRICE_DELIVERY']),
                    'payed' => $arr['PAYED'] == 'Y',
                ];
            }
        }

        if($orderInfo) {
            $basketItems = Basket::getOrderItems(['oid' => $oid]);
            $orderInfo['items'] = $basketItems;
            $orderInfo['order_props'] = $props;
        }

        return $orderInfo;
    }

    public static function getStatuses()
    {
        $result = ['canceled' => [], 'order' => []];
        if (Loader::includeModule('sale')) {
            $db = \CSaleStatus::GetList(['SORT' => 'ASC'], ['LID' => 'ru']);

            while($res = $db->fetch()) {
                $name = strtolower($res['NAME']);

                if(is_numeric(stripos('Отменен', $name))
                    || is_numeric(stripos('Отменён', $name)))
                {
                    $result['canceled'][] = [
                        'id' => $res['ID'],
                        'alias' => $res['NAME'],
                    ];
                }
                else
                {
                    $result['order'][] = [
                        'id' => $res['ID'],
                        'alias' => $res['NAME'],
                    ];
                }
            }
        }

        return $result;
    }


    public static function getOrderList($arRequest)
    {
        $result = ['count' => 0, 'items' => []];

        $page = (int)$arRequest['page'];
        $limit = (int)$arRequest['limit'];
        $filter = $arRequest['filter'] ?? 'all';

        if ($user = User::get()) {
            $q = OrderTable::query()
                ->countTotal(true)
                ->withSelect()
                ->withBasketLength()
                ->withOrderByDate()
                ->withUserId($user['id'])
                ->withFilter($filter)
                ->withLimit($limit)
                ->withPage($page)
            ;

            $result = self::processList($q);
        }
        return $result;
    }

    public static function getOrderById($arRequest)
    {
        $result = [
            'documents'=> [],
            'statuses' => self::getStatuses(),
            'deliveries' => Order::getDeliveries(['all' => true])
        ];

        $id = $arRequest['id'];
        if ($user = User::get()) {
            $q = OrderTable::query()
                ->withSelect()
                ->withOrderByID()
                ->withProperties()
                ->withUserId($user['id'])
                ->withFilterById($id)
            ;

            $res = self::processOrder($q);
            $result['order_info'] = $res;

            if($result['order_info']['order_props']['b24id']){
                $fields = [
                    'filter' => [
                        'entityId' => $result['order_info']['order_props']['b24id']
                    ],
                ];
                $documents = Helper::CurlBitrix24('crm.documentgenerator.document.list', $fields);
                $result['documents'] = $documents['result']['documents'];
            }
            return $result;
        }
        throw new \Exception('Пользователь не авторизован. Обновите страницу.');
    }

    public static function cancelOrder($arRequest)
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Произошла неизвестная ошибка');
        }
        $orderId = $arRequest['id'];
        if (!$orderId) {
            throw new \Exception('Не передан ID заказа.');
        }

        $order = LSOrder::load($orderId);
        if(self::isOrderCanBeCanceled($order->getField('STATUS_ID'))) {
            $order->cancel();
            return true;
        }
        throw new \Exception('Отмена заказа невозможна.');
    }

    private static function isOrderCanBeCanceled($statusCode)
    {
        //todo добавить коды статусов
        $statusesToCancel = ['N'];
        return in_array($statusCode, $statusesToCancel);
    }

    public static function returnProducts($arRequest)
    {
        if (!Loader::includeModule('sale') || !Loader::includeModule('catalog') || !Loader::includeModule('iblock')) {
            throw new \Exception('Произошла неизвестная ошибка');
        }

        $user = User::get();

        if (!$user) {
            throw new \Exception('Вы не авторизованы. Обновите страницу.');
        }

        $orderId = $arRequest['id'];
        $order = LSOrder::load($orderId);

        if ($order->getUserId() == $user['id'])
        {
            $createDate = date_create($order->getField('DATE_STATUS'))->setTime(0,0);
            $currentDate = date_create()->setTime(0,0);
            $productsQuantityInfo = $arRequest['products_quantity_info'];

            if (date_diff($currentDate, $createDate)->format('%a') > 14) {
                throw new \Exception('Срок возврата товара истек.');
            }
            if (empty($productsQuantityInfo)) {
                throw new \Exception('Не выбраны товары для возврата.');
            }
            if (empty($arRequest['reason'])) {
                throw new \Exception('Укажите причину возврата.');
            }
            if (empty($arRequest['product_photos'])) {
                throw new \Exception('Отсутствуют фото товара целиком.');
            }

            $isRequesWasCreated = ReturnProductRequests::isRequestWasCreated(['orderId' => $order->getId()]);

            if ($isRequesWasCreated) {
                throw new \Exception('Заявка на возврат товаров по данному заказу уже создана.');
            }

            $products = BasketElementTable::query()
                ->withSelect(null, $order->getId())
                ->withPrices()
                ->withProduct()
                ->withFilterByProductIds(array_keys($productsQuantityInfo))
                ->withArticle()
                ->exec()
            ;

            $returnedProducts = [];
            while ($res = $products->fetch()) {
                $quantityToReturn = $productsQuantityInfo[$res['PRODUCT_ID']];

                if($quantityToReturn > $res['QUANTITY']) {
                    throw new \Exception('Неправильное количество для возврата.');
                }

                $productId = $res['PRODUCT_ID'];
                $productName = $res['PRODUCT_NAME'];
                $article = $res['ARTICLE'];
                $measure = $res['MEASURE_NAME'];

                $returnedProducts[] = "$productName (ID товара $productId, артикул $article) в количестве $quantityToReturn $measure";
            }

            return ReturnProductRequests::createRequest([...$arRequest, 'user' => $user, 'orderId' => $order->getId(), 'orderDate' => $order->getField('DATE_INSERT'), 'returnedProducts' => $returnedProducts]);
        }
        throw new \Exception('Заказ с таким номером отсутствует. Обновите страницу.');
    }

}
