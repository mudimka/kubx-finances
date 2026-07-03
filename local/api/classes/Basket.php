<?php

namespace Legacy\API;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Legacy\General\Constants;
use Legacy\Main\CLUser;
use Legacy\Sale\BasketElementTable;
use Legacy\Sale\Basket as LSBasket;
use Legacy\HighLoadBlock\Entity;
use Bitrix\Catalog\ProductTable;
use Legacy\IblockController\Section;
use Legacy\IblockController\File;
use Legacy\IblockController\Settings as SettingsController;

class Basket
{
    public static function __callStatic($method, $arguments)
    {
        $CLUser = new CLUser();

        if ($CLUser->IsAuthorized()) {
            $class = BasketRegistered::class;
            $anotherClass = BasketAnonymous::class;
        } else {
            $class = BasketAnonymous::class;
            $anotherClass = BasketRegistered::class;
        }

        if (method_exists($class, $method)) {
            return call_user_func($class.'::'.$method, $arguments[0]);
        } else {
            if (method_exists($anotherClass, $method)) {
                if ($CLUser->IsAuthorized()) {
                    throw new \Exception('Метод недоступен авторизованному пользователю.');
                } else {
                    throw new \Exception('Метод недоступен неавторизованному пользователю.');
                }
            } else{
                throw new \Exception('Метод не найден.');
            }
        }
    }
}

abstract class ABasket
{
    private static function updateRecommendedProducts(&$recommendedProductIds, &$properties) {
        $keysToProcess = [
            'recommended_products',
            'recommended_products_offers',
            'offer_recommended_products',
            'offer_recommended_products_offers',
        ];

        $recommendedIds = [];

        foreach ($keysToProcess as $key) {
            if ($properties[$key] && !empty($properties[$key]['value'])) {
                $recommendedIds = array_merge($recommendedIds, $properties[$key]['value']);
            }
        }

        $recommendedProductIds = array_merge(
            $recommendedProductIds,
            array_column($recommendedIds, 'code')
        );

        foreach ($keysToProcess as $key) {
            unset($properties[$key]);
        }
    }

    public static function getRecommendedProducts($arRequest) {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $payload = [
                'ids' => $arRequest['recommended_ids'] ?? ['']
            ];
            $res = Catalog::getItemsAndCount($payload);
            $result = $res['items'];
        }

        return $result;
    }

    private static function getImages(&$product) {
        if ($product['properties']['offer_images']
            && count($product['properties']['offer_images']['value']) > 0){
            $product['image'] = $product['properties']['offer_images']['value'][0];
        }
        elseif ($product['properties']['more_photo']
            && count($product['properties']['more_photo']['value']) > 0) {
            $product['image'] = $product['properties']['more_photo']['value'][0];
        } else  {
            $product['image'] = File::getFilesInfo($product['preview_picture'], false, false);
        }

        unset($product['properties']['offer_images']);
        unset($product['properties']['more_photo']);
        unset($product['preview_picture']);
    }

    protected static function processData($q, $properties)
    {
        $recommendedIds = [];
        $totalDiscountPrice = 0;
        $totalPrice = 0;
        $totalDiscount = 0;
        $items = [];

        $promocode = Promocode::get([]);

        $db = $q->exec();

        $productsValuesCodes = [];
        while ($arr = $db->fetch()) {
            $productsValuesCodes[] = $arr;
            $prices = [];
            if ($arr['PRICES']) {
                $priceData = explode(',', $arr['PRICES']);
                foreach ($priceData as &$item) {
                    $item = explode(':', $item);
                    $prices[$item[0]] = $item[1];
                }
            }

            $promocodeDiscount = (float)$promocode['discounts'][$arr['BASKET_ID']];

            $basePrice = (float)$prices['BASE'];
            $price = $prices['SALE_PRICE'] ? (float)$prices['SALE_PRICE'] : (float)$prices['BASE'];
            $discountPrice = $price - $promocodeDiscount;
            $discount = $basePrice - $discountPrice;
            $quantity = (int)$arr['QUANTITY'];

            $totalDiscountPrice += $discountPrice * $quantity;
            $totalPrice += $basePrice * $quantity;
            $totalDiscount += $discount * $quantity;

            $urlOfferId = $arr['PRODUCT_TYPE'] == ProductTable::TYPE_OFFER ? '/?offerId=' . $arr['PRODUCT_ID'] : '';
            $sectionInfo = Section::extractSectionInfo($arr['SECTION_INFO']);

            $items[$arr['PRODUCT_ID']] = [
                'oid' => $arr['ORDER_ID'],
                'id' => $arr['PRODUCT_ID'],
                'bid' => $arr['BASKET_ID'],
                'preview_picture' => $arr['PREVIEW_PICTURE'],
                'name' => trim($arr['PRODUCT_NAME']),
                'price' => formatPrice($basePrice),
                'discount_price' => $discount ? formatPrice($discountPrice) : null,
                'discount' => $discount ? formatPrice($discount) : null,
                'quantity' => $quantity,
                'measure' => $arr['MEASURE_NAME'],
                'sum_price' => formatPrice($basePrice * $quantity),
                'sum_discount_price' => $discount ? formatPrice($discountPrice * $quantity) : null,
                'section' => $sectionInfo,
                'url' => '/catalog' . $sectionInfo['url'] . '/product/' . $arr['PRODUCT_CODE'] . $urlOfferId,
            ];
        }

        $propertiesValues = Properties::getPropertiesValues($productsValuesCodes, $properties);

        foreach ($productsValuesCodes as $productValuesCodes) {
            $id = $productValuesCodes['PRODUCT_ID'];
            $propertiesData = Properties::getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $properties);
            $items[$id]['properties'] = $propertiesData;

            self::getImages($items[$id]);
            self::updateRecommendedProducts($recommendedIds, $items[$id]['properties']);
        }

        unset($promocode['discounts']);
        return [
            'items' => $items,
            'price' => formatPrice($totalPrice),
            'discount_price' => $totalDiscount ? formatPrice($totalDiscountPrice) : null,
            'discount' => $totalDiscount ? formatPrice($totalDiscount) : null,
            'count' => count($items),
            'recommended_ids' => $recommendedIds,
            'promocode_info' => $promocode,
        ];
    }

    protected static function processOrderItems($q, $properties)
    {
        $items = [];
        $db = $q->exec();

        $productsValuesCodes = [];
        while ($arr = $db->fetch()) {
            $productsValuesCodes[] = $arr;
            $prices = [];
            if ($arr['PRICES']) {
                $priceData = explode(',', $arr['PRICES']);
                foreach ($priceData as &$item) {
                    $item = explode(':', $item);
                    $prices[$item[0]] = $item[1];
                }
            }

            $discountPrice = $arr['PRICE'];
            $basePrice = (float)$prices['BASE'];
            $discount = $basePrice - $discountPrice;
            $quantity = (int)$arr['QUANTITY'];

            $urlOfferId = $arr['PRODUCT_TYPE'] == ProductTable::TYPE_OFFER ? '/?offerId=' . $arr['PRODUCT_ID'] : '';
            $sectionInfo = Section::extractSectionInfo($arr['SECTION_INFO']);

            $items[$arr['PRODUCT_ID']] = [
                'oid' => $arr['ORDER_ID'],
                'id' => $arr['PRODUCT_ID'],
                'bid' => $arr['BASKET_ID'],
                'preview_picture' => $arr['PREVIEW_PICTURE'],
                'name' => trim($arr['PRODUCT_NAME']),
                'price' => formatPrice($basePrice),
                'discount_price' => $discount ? formatPrice($discountPrice) : null,
                'discount' => $discount ? formatPrice($discount) : null,
                'quantity' => $quantity,
                'measure' => $arr['MEASURE_NAME'],
                'sum_price' => formatPrice($basePrice * $quantity),
                'sum_discount_price' => $discount ? formatPrice($discountPrice * $quantity) : null,
                'section' => $sectionInfo,
                'url' => '/catalog' . $sectionInfo['url'] . '/product/' . $arr['PRODUCT_CODE'] . $urlOfferId,
            ];
        }

        $propertiesValues = Properties::getPropertiesValues($productsValuesCodes, $properties);

        foreach ($productsValuesCodes as $productValuesCodes) {
            $id = $productValuesCodes['PRODUCT_ID'];
            $propertiesData = Properties::getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $properties);
            $items[$id]['properties'] = $propertiesData;

            self::getImages($items[$id]);
        }

        return $items;
    }

    protected static function addToBitrixBasket($arRequest)
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули.');
        }

        $id = $arRequest['id'];

        if(!$id) {
            throw new \Exception('Не передан id товара.');
        }

        $fields = [
            'PRODUCT_ID' => $id,
            'QUANTITY' => $arRequest['quantity'] ?? 1,
        ];
        $result = \Bitrix\Catalog\Product\Basket::addProduct($fields);
        if ($result->isSuccess()) {
            return $result->getData();
        } else {
            if(!$arRequest['is_change']) {
                throw new \Exception(implode('. ', $result->getErrorMessages()));
            }
        }
    }

    protected static function clearBitrixBasket()
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули.');
        }

        if ($fuid = Sale\Fuser::getId()) {
            \CSaleBasket::DeleteAll(
                $fuid,
                false
            );
        }
    }

    abstract static function get($arRequest);
    abstract static function add($arRequest);
    abstract static function setQuantity($arRequest);
    abstract static function remove($arRequest);
    abstract static function clearBasket($arRequest);
}


class BasketAnonymous extends ABasket
{
    public static function get($arRequest)
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули.');
        }

        $result = ['items' => [], 'count' => 0];
        if ($fuid = Sale\Fuser::getId()) {
            $properties = Properties::get(['iblock_id' => [Constants::IB_CATALOG_CRM,
                Constants::IB_CATALOG_CRM_OFFERS], 'is_basket' => true]);

            $oid = $arRequest['oid'];

            $q = BasketElementTable::query()
                ->withSelect($fuid, $oid)
                ->withPrices()
                ->withProperties($properties)
                ->withProduct()
            ;
            $result = self::processData($q, $properties);
        }
        return $result;
    }

    public static function add($arRequest)
    {
        $addResult = self::addToBitrixBasket($arRequest);
        return array_merge($addResult, self::get($arRequest));
    }

    public static function setQuantity($arRequest)
    {
        $bid = intval($arRequest['bid']);
        if ($bid <= 0) {
            throw new ArgumentException('Неверный ID товара.');
        }

        $quantity = intval($arRequest['quantity']);
        if ($quantity <= 0) {
            throw new ArgumentException('Неверное количество товара.');
        }

        $basket = LSBasket::loadItems();
        $basketItem = $basket->getItemById($bid);
        if (!$basketItem) {
            throw new ArgumentException('Товар не найден.');
        }

        $basketItem->setField('QUANTITY', $quantity);
        $obRes = $basket->save();
        if ($obRes->isSuccess()) {
            return self::get($arRequest);
        }

        throw new \Exception('Ошибка обновления количества товара в корзине.');
    }

    public static function remove($arRequest)
    {
        $bids = $arRequest['bids'] ?? [];

        if (!$bids) {
            throw new ArgumentException('Не переданы ID товаров для удаления.');
        }

        if(!is_array($bids)){
            $bids = [$bids];
        }

        $basket = LSBasket::loadItems();
        foreach ($bids as $bid) {
            $basketItem = $basket->getItemById($bid);
            if (!$basketItem) {
                throw new ArgumentException('Товар для удаления не найден. Обновите страницу.');
            }
            $basketItem->delete();
        }
        $obRes = $basket->save();
        if ($obRes->isSuccess()) {
            return self::get($arRequest);
        } else {
            throw new \Exception('Ошибка удаления товара из корзины.');
        }
    }

    public static function clearBasket($arRequest)
    {
        self::clearBitrixBasket();
        return self::get($arRequest);
    }
}

class BasketRegistered extends ABasket
{
    const MAX_BASKETS = 8;

    public static function get($arRequest)
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули.');
        }

        $result = ['items' => [], 'count' => 0];
        if ($fuid = Sale\Fuser::getId()) {
            $properties = Properties::get(['iblock_id' => [Constants::IB_CATALOG_CRM,
                Constants::IB_CATALOG_CRM_OFFERS], 'is_basket' => true]);
            $oid = $arRequest['oid'];
            $q = BasketElementTable::query()
                ->withSelect($fuid, $oid)
                ->withPrices()
                ->withProperties($properties)
                ->withProduct()
            ;

            $result = self::processData($q, $properties);
            $currentBasket = self::getMultiBasket(self::ensureActiveBasketID());
            $result['current_basket'] = [
                'ID' => $currentBasket['ID'],
                'name' => $currentBasket['UF_NAME'],
                'description' => $currentBasket['UF_DESCRIPTION'],
                'color' => $currentBasket['UF_COLOR'],
            ];
        }
        return $result;
    }

    public static function getOrderItems($arRequest)
    {
        if (!Loader::includeModule('sale')) {
            throw new \Exception('Не удалось подключить необходимые модули.');
        }

        $result = ['items' => [], 'count' => 0];
        if (Sale\Fuser::getId()) {
            $properties = Properties::get(['iblock_id' => [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS], 'is_basket' => true]);
            $oid = $arRequest['oid'];
            $q = BasketElementTable::query()
                ->withSelect(null, $oid)
                ->withPrices()
                ->withProperties($properties)
                ->withProduct()
            ;

            $result = self::processOrderItems($q, $properties);
        }
        return $result;
    }

    public static function add($arRequest)
    {
        if (self::addToMultiBasket($arRequest)){
            $addResult = self::addToBitrixBasket($arRequest);
            $basket = self::get($arRequest);
            self::updateMultiBasketPrice($basket['price']);
            return array_merge($addResult, $basket);
        }
        throw new \Exception('Ошибка добавления товара в корзину.');
    }

    private static function addToMultiBasket($arRequest) {
        $multiBasketID = self::ensureActiveBasketID();
        $multiBasket = self::getMultiBasket($multiBasketID);

        $quantity = (int)($arRequest['quantity'] ?? 1);
        $productID = $arRequest['id'];

        $arGoods = [];
        $checkProduct = false;
        if ($multiBasket){
            foreach ($multiBasket['UF_GOODS'] as $goods) {
                [$goodsID, $goodsQuantity] = explode(';', $goods);
                if ($goodsID == $productID) {
                    $goodsQuantity = (int)$goodsQuantity + $quantity;
                    $arGoods[] = $goodsID . ';' . $goodsQuantity;
                    $checkProduct = true;
                } else {
                    $arGoods[] = $goods;
                }
            }
            if (!$checkProduct) {
                $arGoods[] = $productID . ';' . $quantity;
            }
        }

        $paramsToUpdate = [
            'UF_GOODS' => $arGoods
        ];
        $addedToBasket = Entity::getInstance()->update(Constants::HLBLOCK_MULTI_BASKET, $multiBasket['ID'], $paramsToUpdate);

        return $addedToBasket;
    }

    public static function setQuantity($arRequest)
    {
        $bid = intval($arRequest['bid']);
        if ($bid <= 0) {
            throw new ArgumentException('Неверный ID товара.');
        }

        $quantity = intval($arRequest['quantity']);
        if ($quantity <= 0) {
            throw new ArgumentException('Неверное количество товара.');
        }

        $basket = LSBasket::loadItems();
        $basketItem = $basket->getItemById($bid);
        if (!$basketItem) {
            throw new ArgumentException('Товар не найден.');
        }

        $productID = $basketItem->getField('PRODUCT_ID');

        if (self::setMultiBasketQuantity($arRequest, $productID)){
            $basketItem->setField('QUANTITY', $quantity);
            $obRes = $basket->save();
            if ($obRes->isSuccess()) {
                $basket = self::get($arRequest);
                self::updateMultiBasketPrice($basket['price']);
                return $basket;
            }
        }

        throw new \Exception('Ошибка обновления количества товара в корзине.');
    }

    private static function setMultiBasketQuantity($arRequest, $productID) {
        $multiBasketID = self::ensureActiveBasketID();
        $multiBasket = self::getMultiBasket($multiBasketID);

        $quantity = (int)$arRequest['quantity'];

        $arGoods = [];
        if ($multiBasket){
            foreach ($multiBasket['UF_GOODS'] as $goods) {
                [$goodsID, $goodsQuantity] = explode(';', $goods);
                if ($goodsID == $productID) {
                    $arGoods[] = $goodsID . ';' . $quantity;
                } else {
                    $arGoods[] = $goods;
                }
            }
        }

        $paramsToUpdate = [
            'UF_GOODS' => $arGoods
        ];
        $addedToBasket = Entity::getInstance()->update(Constants::HLBLOCK_MULTI_BASKET, $multiBasket['ID'], $paramsToUpdate);

        return $addedToBasket;
    }

    public static function remove($arRequest)
    {
        $bids = $arRequest['bids'];

        if (!$bids) {
            throw new ArgumentException('Не переданы ID товаров для удаления.');
        }

        if(!is_array($bids)){
            $bids = [$bids];
        }

        $productIDs = [];
        $basketItems = [];
        $basket = LSBasket::loadItems();
        foreach ($bids as $bid) {
            $basketItem = $basket->getItemById($bid);
            if (!$basketItem) {
                throw new ArgumentException('Товар для удаления не найден. Обновите страницу.');
            }
            $productIDs[] = $basketItem->getField('PRODUCT_ID');
            $basketItems[] = $basketItem;
        }

        if(self::removeFromMultiBasket($productIDs)){
            foreach ($basketItems as $basketItem) {
                $basketItem->delete();
            }
            $obRes = $basket->save();
            if ($obRes->isSuccess()) {
                $basket = self::get($arRequest);
                self::updateMultiBasketPrice($basket['price']);
                return $basket;
            }
        }

        throw new \Exception('Ошибка удаления товара из корзины.');
    }

    private static function removeFromMultiBasket($productIDs) {
        $multiBasketID = self::ensureActiveBasketID();
        $multiBasket = self::getMultiBasket($multiBasketID);

        $arGoods = [];
        if ($multiBasket){
            foreach ($multiBasket['UF_GOODS'] as $goods) {
                [$goodsID, $goodsQuantity] = explode(';', $goods);
                if (in_array($goodsID, $productIDs)) {
                    continue;
                } else {
                    $arGoods[] = $goods;
                }
            }
        }

        $paramsToUpdate = [
            'UF_GOODS' => $arGoods
        ];
        $addedToBasket = Entity::getInstance()->update(Constants::HLBLOCK_MULTI_BASKET, $multiBasket['ID'], $paramsToUpdate);

        return $addedToBasket;
    }

    private static function updateMultiBasketPrice($price = null) {
        $multiBasketID = self::ensureActiveBasketID();

        $priceToUpdate = $price ?? formatPrice(LSBasket::loadItems()->getBasePrice());
        $paramsToUpdate = [
            'UF_PRICE' => $priceToUpdate ?? formatPrice(0)
        ];
        return Entity::getInstance()->update(Constants::HLBLOCK_MULTI_BASKET, $multiBasketID, $paramsToUpdate);
    }

    public static function clearBasket($arRequest)
    {
        $multiBasketID = $_SESSION['ACTIVE_BASKET'];
        if($multiBasketID){
            $paramsToUpdate = [
                'UF_GOODS' => []
            ];
            $clearAllowed = Entity::getInstance()->update(Constants::HLBLOCK_MULTI_BASKET, $multiBasketID, $paramsToUpdate);
        } else {
            throw new \Exception('Корзина не найдена. Обновите страницу.');
        }

        if($clearAllowed){
            self::clearBitrixBasket();
            self::updateMultiBasketPrice();
        }

        return self::get($arRequest);
    }

    public static function createBasket($arRequest) {
        $userID = User::getID();
        $basketColors = SettingsController::getBasketColors();

        if (!$userID) {
            throw new \Exception('Пользователь не авторизован');
        }

        if (!$arRequest['name']) {
            throw new ArgumentException('Введите название');
        }

        $multiBaskets = self::getMultiBaskets();
        $multiBasketsCount = count($multiBaskets);

        if ($multiBasketsCount >= self::MAX_BASKETS) {
            throw new \Exception('Превышено максимальное количество корзин');
        }

        foreach ($multiBaskets as $multiBasket) {
            if ($multiBasket['name'] == $arRequest['name']) {
                throw new \Exception('Корзина с таким названием уже существует. Введите другое название.');
            }
        }

        $color = 'secondary';
        $userBasketColors = array_column($multiBaskets, 'color');
        foreach ($basketColors as $basketColor) {
            if (!in_array($basketColor, $userBasketColors)) {
                $color = $basketColor;
                break;
            }
        }

        $params = [
            'UF_USER' => $userID,
            'UF_NAME' => $arRequest['name'],
            'UF_DESCRIPTION' => $arRequest['description'],
            'UF_COLOR' => $color,
            'UF_PRICE' => formatPrice(0)
        ];

        if ($id = Entity::getInstance()->add(Constants::HLBLOCK_MULTI_BASKET, $params))
        {
            self::changeBasket(['basket_id' => $id]);
            return array_merge(self::get([]), ['baskets' => self::getMultiBaskets(), 'id' => $id]);
        } else {
            throw new \Exception('Ошибка создания корзины.');
        }
    }

    public static function updateBasket($arRequest)
    {
        $basketID = $arRequest['basket_id'];

        if (!$basketID){
            throw new ArgumentException('Отсутствует ID корзины.');
        }

        if (!$arRequest['name'] && !$arRequest['description']){
            throw new ArgumentException('Отсутствуют поля для обновления.');
        }

        $paramsToUpdate = [];
        if($arRequest['name']){
            $multiBaskets = self::getMultiBaskets();

            foreach ($multiBaskets as $multiBasket) {
                if ($multiBasket['name'] == $arRequest['name'] && $multiBasket['id'] != $basketID) {
                    throw new \Exception('Корзина с таким названием уже существует. Введите другое название.');
                }
            }
            $paramsToUpdate['UF_NAME'] = $arRequest['name'];
        }
        if($arRequest['description']){
            $paramsToUpdate['UF_DESCRIPTION'] = $arRequest['description'];
        }

        return Entity::getInstance()->update(Constants::HLBLOCK_MULTI_BASKET, $basketID, $paramsToUpdate);
    }

    public static function deleteBasket($arRequest) {
        $basketID = $arRequest['basket_id'];

        $multiBasket = self::getMultiBasket($basketID);

        if($multiBasket['UF_MAIN'] != '1') {
            $result = Entity::getInstance()->delete(Constants::HLBLOCK_MULTI_BASKET, $basketID);

            if ($_SESSION['ACTIVE_BASKET'] == $basketID && $result){
                self::setMainBasket();
            }

            return array_merge(['baskets' => self::getMultiBaskets()], self::get([]));
        }
        else
        {
            throw new \Exception('Удаление основной корзины невозможно.');
        }
    }

    public static function changeBasket($arRequest)
    {
        $basketID = $arRequest['basket_id'];
        $multiBasket = self::getMultiBasket($basketID);

        self::clearBitrixBasket();

        foreach ($multiBasket['UF_GOODS'] as $goods) {
            [$goodsID, $goodsQuantity] = explode(';', $goods);
            if($goodsID){
                self::addToBitrixBasket(['id' => $goodsID, 'quantity' => $goodsQuantity, 'is_change' => true]);
            }
        }
        $_SESSION['ACTIVE_BASKET'] = $basketID;
        return self::get([]);
    }

    public static function ensureActiveBasketID(){
        if (!isset($_SESSION['ACTIVE_BASKET'])){
            self::setMainBasket();
        }

        return $_SESSION['ACTIVE_BASKET'];
    }

    public static function unsetCurrentBasket(){
        self::clearBitrixBasket();
        $_SESSION['ACTIVE_BASKET'] = null;
    }

    private static function setMainBasket(){
        $mainMultiBasket = self::getMainBasket();
        self::changeBasket(['basket_id' => $mainMultiBasket['ID']]);
    }

    public static function setMainBasketLogin($bitrixBasketItems){
        $mainMultiBasket = self::getMainBasket();
        self::changeBasket(['basket_id' => $mainMultiBasket['ID']]);

        foreach ($bitrixBasketItems as $bitrixBasketItem){
            $item = $bitrixBasketItem->toArray();
            self::add(['id' => $item['PRODUCT_ID'], 'quantity' => (int)$item['QUANTITY']]);
        }
    }

    private static function getMainBasket(){
        $userID = User::getID();

        $params = [
            'filter' => [
                'UF_USER' => $userID,
                'UF_MAIN' => 1
            ],
            'order' => [
                'ID' => 'ASC',
            ],
        ];
        $mainMultiBasket = Entity::getInstance()->getRow(Constants::HLBLOCK_MULTI_BASKET, $params);

        return $mainMultiBasket;
    }

    private static function getMultiBasket($basketID)
    {
        $userID = User::getID();

        $params = [
            'filter' => [
                'UF_USER' => $userID,
                'ID' => $basketID
            ],
            'order' => [
                'ID' => 'ASC',
            ],
        ];

        $multiBasket = Entity::getInstance()->getRow(Constants::HLBLOCK_MULTI_BASKET, $params);
        if($multiBasket) {
            return $multiBasket;
        }
        else {
            throw new \Exception('Корзина не найдена. Обновите страницу.');
        }
    }

    public static function getMultiBaskets()
    {
        $userID = User::getID();

        $result = [];

        $params = [
            'filter' => [
                'UF_USER' => $userID
            ],
            'order' => [
                'ID' => 'ASC',
            ],
        ];

        $multiBaskets = Entity::getInstance()->getList(Constants::HLBLOCK_MULTI_BASKET, $params);

        foreach ($multiBaskets as $multiBasket){
            $result[] = [
                'id' => $multiBasket['ID'],
                'name' => $multiBasket['UF_NAME'],
                'description' => $multiBasket['UF_DESCRIPTION'],
                'count' => count($multiBasket['UF_GOODS']),
                'price' => $multiBasket['UF_PRICE'],
                'is_main' => (bool)$multiBasket['UF_MAIN'],
                'color' => $multiBasket['UF_COLOR'],
            ];
        }

        return $result;
    }
}
