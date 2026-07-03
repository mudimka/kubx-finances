<?php

namespace Legacy\API;

use Bitrix\Main\Application;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Legacy\Controller\Location as LocationController;
use Legacy\General\Constants;
use Legacy\General\Validation;
use Legacy\General\DataProcessor;
use Legacy\HighLoadBlock\OrganizationsTable;
use Legacy\IblockController\DeliveryGroupsTable;
use Legacy\Main\CLUser;
use Bitrix\Sale\Delivery\Restrictions\Manager as RestrictionsManager;
use \Saferoute\Widget\Common;
use \Saferoute\Widget\SafeRouteWidgetApi;
use Bitrix\Main\UserPhoneAuthTable;
use Legacy\Sale\Basket as LSBasket;

use Legacy\Sale\Order as LSOrder;

class Order
{
    public static function __callStatic($method, $arguments)
    {
        $CLUser = new CLUser();

        if ($CLUser->IsAuthorized()) {
            $class = OrderRegistered::class;
        } else {
            $class = OrderAnonymous::class;
        }

        if (method_exists($class, $method)) {
            return call_user_func($class . '::' . $method, $arguments[0]);
        } else {
            throw new \Exception('Метод не найден.');
        }
    }
}

abstract class AOrder
{
    private static $periodTypes = [
        'MIN' => 'minutes',
        'H' => 'hours',
        'D' => 'days',
        'M' => 'months',
    ];

    protected static function parseToB24XlmCodes($orderProperties, $userType)
    {
        $result = [];
        if ($userType === 'company') {
            $propertyMapping = [
                'COMMENT' => 'COMPANY_COMMENTS',
                'ORGANIZATION_NAME' => ['COMPANY_TITLE', 'COMPANY_RQ_COMPANY_NAME_1'],
                'INN' => 'COMPANY_RQ_INN_1',
                'KPP' => 'COMPANY_RQ_KPP_1',
                'OGRN' => 'COMPANY_RQ_OGRN_1',
                'DIRECTOR' => 'COMPANY_RQ_DIRECTOR_1',
//                'BIK' => 'COMPANY_RQ_BIK_1',
//                'BANK_NAME' => 'COMPANY_RQ_BANK_NAME_1',
                'EMAIL' => 'CONTACT_EMAIL_WORK',
                'PHONE' => 'CONTACT_PHONE_WORK',
                'CONTACT_PERSON' => 'CONTACT_FULL_NAME',
//                'CORRESPONDENT_SETTLEMENT' => 'COMPANY_RQ_COR_ACC_NUM_1',
//                'SETTLEMENT_INVOICE' => 'COMPANY_RQ_ACC_NUM_1',
                'SH_LOCATION' => 'LOCALITY',
                'ADDRESS' => ['STOCK_ID', '	COMPANY_ADDRESS_1_1_11'],
                'STOCK_ID' => '	STOCK_ID',
                'RECEIVING_TYPE' => 'RECEIVING_TYPE',
            ];

            foreach ($orderProperties as $key => $value) {
                $map = $propertyMapping[strtoupper($key)] ?? null;

                if (!$map) {
                    continue;
                }

                if (is_array($map)) {
                    foreach ($map as $field) {
                        $result[$field] = $value;
                    }
                } else {
                    $result[$map] = $value;
                }
            }
        } elseif ($userType === 'contact') {
            $propertyMapping = [
                'ADDRESS' => 'CONTACT_UF_CRM_1749039113',
                'STOCK_ID' => 'CONTACT_UF_CRM_1749039154',
                'RECEIVING_TYPE' => 'CONTACT_UF_CRM_1749039064',
                'EMAIL' => 'CONTACT_EMAIL_WORK',
                'PHONE' => 'CONTACT_PHONE_WORK',
                'CONTACT_PERSON' => 'CONTACT_FULL_NAME',
                'COMMENT' => 'CONTACT_COMMENTS',
                'SH_LOCATION' => 'CONTACT_UF_CRM_1749039176',
            ];

            foreach ($orderProperties as $key => $value) {
                if (!empty($propertyMapping[strtoupper($key)])) {
                    $result[$propertyMapping[strtoupper($key)]] = $value;
                }
            }
        }
        return $result;
    }

    protected static function order($arRequest)
    {
        $userType = $arRequest['user_type'] ?? 'contact';

        $userId = User::getID();
        if (is_null($userId)) {
            throw new \Exception('Ошибка при оформлении заказа.');
        }

        $managerID = Managers::getManagerID();

        $orderProperties = $arRequest['order_properties'];
        if (is_null($orderProperties) || empty($orderProperties)) {
            throw new \Exception('Отсутствуют свойства заказа.');
        }

        $parsedOrderProperties = self::parseToB24XlmCodes($orderProperties, $userType);

        if ($orderProperties['organization_id'] > 0 && $userType == 'company') {
            self::updateOrganizationOrderProps($orderProperties);
        }

        $personTypeID = constant('Legacy\General\Constants::PERSON_TYPE_CRM_' . strtoupper($userType));

        self::updateOrCreateProfile($userId, $personTypeID, $orderProperties);

        $basket = LSBasket::loadItems()->getBasket();
        if (!$basket->count()) {
            throw new \Exception('Товаров в корзине не найдено. Обновите страницу.');
        }

        $order = LSOrder::initOrder($basket, $personTypeID, $userId, $orderProperties, $parsedOrderProperties, $managerID);
        $order->setOrderProperties();
        $order->setShipment();
        $order->setPayment();

        $orderId = $order->createOrder();
        Basket::clearBasket([]);

        // $event = new \CEvent;
        // $event->Send('SALE_NEW_ORDER_MANAGER', SITE_ID, [
        //     'user_id' => $userId,
        //     'user_type' => $arRequest['user_type'],
        //     'order_properties' => $arRequest['order_properties'],
        //     'SITE_ID' => SITE_ID,
        //     'ORDER_NUM' => $orderId,
        // ]);

        return ['order_id' => $orderId];
    }

    private static function updateOrCreateProfile($userId, $personTypeID, $orderProperties)
    {
        $profileId = null;
        $profileProps = [];

        $user_profiles = \CSaleOrderUserProps::GetList(
            ['DATE_UPDATE' => 'DESC'],
            ['USER_ID' => $userId]
        );
        while ($user_profile = $user_profiles->fetch()) {
            $userProfileProps = [];

            $profileProperties = \CSaleOrderUserPropsValue::GetList(
                ['ID' => 'ASC'],
                ['USER_PROPS_ID' => $user_profile['ID']]
            );
            while ($arPropVals = $profileProperties->Fetch()) {
                $userProfileProps[strtolower($arPropVals['PROP_CODE'])] = $arPropVals;
            }

            if ($orderProperties['organization_id'] === $userProfileProps['organization_id']['VALUE']) {
                $profileId = $user_profile['ID'];
                $profileProps = $userProfileProps;
                break;
            }
        }

        if ($profileId) {
            foreach ($orderProperties as $property => $value) {
                if ($id = $profileProps[$property]['ID']) {
                    if ($value != $profileProps[$property]['VALUE']) {
                        $propToUpdate = [
                            'ID' => $id,
                            'USER_PROPS_ID' => $profileId,
                            'VALUE' => $value
                        ];
                        \CSaleOrderUserPropsValue::Update($id, $propToUpdate);
                    }
                }
            }
        } else {
            $arProfileFields = [
                'NAME' => 'Профиль ' . date('Y-m-d'),
                'USER_ID' => $userId,
                'PERSON_TYPE_ID' => $personTypeID
            ];
            $PROFILE_ID = \CSaleOrderUserProps::Add($arProfileFields);

            if ($PROFILE_ID) {
                $order = Sale\Order::create(Context::getCurrent()->getSite(), $userId);
                $propertyCollection = $order->getPropertyCollection()->getArray()['properties'];
                $propertyIds = [];

                foreach ($propertyCollection as $property) {
                    $propertyIds[strtolower($property['CODE'])] = [
                        'ID' => $property['ID'],
                        'NAME' => $property['NAME']
                    ];
                }

                foreach ($orderProperties as $property => $value) {
                    if ($propertyIds[$property]) {
                        $prop = [
                            "USER_PROPS_ID" => $PROFILE_ID,
                            "NAME" => $propertyIds[$property]['NAME'],
                            "ORDER_PROPS_ID" => $propertyIds[$property]['ID'],
                            "VALUE" => $value
                        ];
                        \CSaleOrderUserPropsValue::Add($prop);
                    }
                }
            }
        }
    }

    private static function updateOrganizationOrderProps(&$orderProperties)
    {
        $userOrganizations = array_column(Organizations::getUserOrganizations(), 'id');
        if (!in_array($orderProperties['organization_id'], $userOrganizations)) {
            throw new \Exception('Организация не найдена');
        }

        $organization = OrganizationsTable::query()
            ->withSelect()
            ->withID($orderProperties['organization_id'])
            ->exec()
            ->fetch();

        $orderProperties['company'] = htmlspecialchars_decode($organization['UF_NAME']);
        $orderProperties['inn'] = $organization['UF_INN'];
        $orderProperties['ogrn'] = $organization['UF_OGRN'];
        $orderProperties['kpp'] = $organization['UF_KPP'];
        $orderProperties['director'] = $organization['UF_DIRECTOR'];
    }

    public static function getDeliveries($arRequest)
    {
        $needAllDeliveries = $arRequest['all'];
        if (!$needAllDeliveries) {
            $location = current(Dadata::findCities(['query' => $arRequest['location']]));
            $arLocation = [$location['country'], $location['region_full'], $location['city']];
        }

        $result = [];
        if (Loader::includeModule('sale')) {
            $deliveryServices = \Bitrix\Sale\Delivery\Services\Table::getList(['filter' => ['ACTIVE' => 'Y'], 'order' => ['SORT' => 'ASC']]);

            $deliveryRestrictions = \Legacy\Sale\Delivery::getRestrictions();

            $haveConstantDelivType = defined('Legacy\\General\\Constants::DELIVERY_TIP_DOSTAVKA');
            $groupClassName = '\Bitrix\Sale\Delivery\Services\Group';

            $transportCompanies = [];
            $transportCompanyMethods = [];
            $transportCompaniesSort = [];
            $otherCompanies = [];
            $otherCompaniesSort = [];
            while ($service = $deliveryServices->fetch()) {
                $id = $service['ID'];

                if ($id == Constants::DELIVERY_BEZ_DOSTAVKI) {
                    continue;
                } elseif (!$needAllDeliveries) {
                    if (!empty($deliveryRestrictions[$id]['location'])) {
                        if (empty(array_intersect($deliveryRestrictions[$id]['location'], $arLocation))) {
                            continue;
                        }
                    } elseif (!empty($deliveryRestrictions[$id]['exclude_location'])) {
                        if (!empty(array_intersect($deliveryRestrictions[$id]['exclude_location'], $arLocation))) {
                            continue;
                        }
                    } elseif (!empty($deliveryRestrictions[$id]['price'])) {
                        $restriction = $deliveryRestrictions[$id]['price'];
                        $basket = Basket::get([]);
                        $price = getPriceFromFormatString($basket['discount_price'] ?: $basket['price']);
                        if ($price < $restriction['min_price'] || $price > $restriction['max_price']) {
                            continue;
                        }
                    }
                }

                if ($id == Constants::DELIVERY_SAMOVYVOZ) {
                    $dbResult = \CCatalogStore::GetList([], ['ISSUING_CENTER' => 'Y', 'ACTIVE' => 'Y']);

                    $stocks = [];
                    while ($stock = $dbResult->fetch()) {
                        $stocks[] = [
                            'id' => $stock['ID'],
                            'name' => $stock['TITLE'],
                            'address' => $stock['ADDRESS'],
                            'description' => $stock['DESCRIPTION'],
                            'payment_ids' => $deliveryRestrictions[$id]['pay_systems_ids'] ?: null,
                            'stocks' => $stocks
                        ];
                    }

                    $result[] = [
                        'code' => $service['XML_ID'],
                        'id' => $service['ID'],
                        'name' => $service['NAME'],
                        'description' => $service['DESCRIPTION'],
                        'stocks' => $stocks,
                    ];
                } elseif (str_contains($service['XML_ID'], 'transport_company') ||
                    ($haveConstantDelivType && $service['PARENT_ID'] == Constants::DELIVERY_TIP_DOSTAVKA))
                {
                    $transportCompaniesSort[] = $service['ID'];

                    $group = substr($service['XML_ID'], strrpos($service['XML_ID'], '_') + 1);
                    $haveCountMethods = class_exists("Legacy\\Deliveries\\".$group);
                    $company = [
                        'code' => $service['XML_ID'],
                        'id' => $service['ID'],
                        'name' => $service['NAME'],
                        'description' => $service['DESCRIPTION'],
                        'logo' => getFilePath($service['LOGOTIP']),
                        'type' => $group,
                        'have_count_methods' => $haveCountMethods,
                        'payment_ids' => $deliveryRestrictions[$id]['pay_systems_ids'] ?: null
                    ];
                    if ($haveCountMethods && method_exists("Legacy\\Deliveries\\$group", 'getMethods')) {
                        $methods = call_user_func("Legacy\\Deliveries\\".$group."::getMethods", []);
                        if (!empty($methods)) {
                            $company['methods'] = $methods;
                        }
                    }

                    $transportCompanies[$service['ID']] = $company;
                } elseif ($service['PARENT_ID'] > 0) {
                    $transportCompanyMethods[(int)$service['PARENT_ID']][] = [
                        'code' => $service['CODE'],
                        'id' => $service['ID'],
                        'name' => $service['NAME'],
                        'description' => $service['DESCRIPTION'],
                        'logo' => getFilePath($service['LOGOTIP']),
                        'type' => $service['CONFIG']['MAIN']['PROFILE_ID'],
                    ];
                } elseif ($service['CLASS_NAME'] !== $groupClassName && !str_contains($service['XML_ID'], 'DELLIN'))
                {
                    $otherCompaniesSort = $service['ID'];
                    $otherCompanies[] = [
                        'code' => $service['XML_ID'],
                        'id' => $service['ID'],
                        'name' => $service['NAME'],
                        'description' => $service['DESCRIPTION'],
                        'payment_ids' => $deliveryRestrictions[$id]['pay_systems_ids'] ?: null,
                        'period' => [
                            'from' => $service['CONFIG']['MAIN']['PERIOD']['FROM'],
                            'to' => $service['CONFIG']['MAIN']['PERIOD']['TO'],
                            'type' => self::$periodTypes[$service['CONFIG']['MAIN']['PERIOD']['TYPE']],
                        ],
                        'price' => $service['CONFIG']['MAIN']['PRICE'],
                    ];
                }
            }

            foreach ($transportCompanies as $key => &$item) {
                if ($item['have_count_methods']) {
                    if (array_key_exists($key, $transportCompanyMethods)) {
                        $item['methods'] = $transportCompanyMethods[$key];
                    }
                    if (empty($item['methods'])) {
                        $item['have_count_methods'] = false;
                    }
                }
            }
            unset($item);

            if (!empty($transportCompanies)) {
                $result[] = [
                    'code' => 'transport_company',
                    'name' => 'Транспортная компания',
                    'companies' => DataProcessor::sortResultByIDs($transportCompanies, $transportCompaniesSort, true)
                ];
            }

            if (!empty($otherCompanies)) {
                $result[] = [
                    'code' => 'other',
                    'name' => 'Другое',
                    'items' => DataProcessor::sortResultByIDs($otherCompanies, $otherCompaniesSort, true)
                ];
            }
        }
        return array_values($result);
    }

    public static function getPayments($arRequest)
    {
        $result = [];

        if (Loader::includeModule('sale')) {
            $payments = \Bitrix\Sale\PaySystem\Manager::getList([
                'filter' => [
                    'ACTIVE' => 'Y',
                    'ENTITY_REGISTRY_TYPE' => 'ORDER'
                ],
                'order' => ['SORT' => 'ASC']
            ]);

            while ($payment = $payments->fetch()) {
                $result[] = [
                    'code' => $payment['CODE'],
                    'id' => $payment['ID'],
                    'name' => $payment['NAME'],
                    'description' => $payment['DESCRIPTION'],
                    'logo' => getFilePath($payment['LOGOTIP'])
                ];
            }
        }
        return $result;
    }

    public static function getOrderOptions($arRequest)
    {
        $result = [];

        $user_profile_properties = self::getProfileProps()['profile_properties'];
        $location = $user_profile_properties['location'];

        if (!$location) {
            $location = LocationController::getUserCityWithZip();
            $user_profile_properties['location'] = $location['city'];
            $user_profile_properties['zip'] = $location['zip'];
        }

        if (Loader::includeModule('sale')) {
            $result['order_receiving'] = self::getDeliveries(['location' => $location['city']]);
            $result['order_payment'] = self::getPayments($arRequest);
            $result['user_order_properties'] = $user_profile_properties;
        }

        return $result;
    }

    private static function getProfileProps()
    {
        $userInfo = User::getProfileInfo();
        $userId = $userInfo['id'];

        $user_profiles = \CSaleOrderUserProps::GetList(
            ['DATE_UPDATE' => 'DESC'],
            ['USER_ID' => $userId]
        );

        $result = [
            'profile_properties' => [],
            'profile_id' => null
        ];
        if ($user_profile = $user_profiles->fetch()) {
            $result['profile_id'] = $user_profile['ID'];

            $profileProperties = \CSaleOrderUserPropsValue::GetList(
                ['ID' => 'ASC'],
                ['USER_PROPS_ID' => $user_profile['ID']]
            );
            while ($arPropVals = $profileProperties->Fetch()) {
                $result['profile_properties'][strtolower($arPropVals['PROP_CODE'])] = htmlspecialchars_decode($arPropVals['VALUE']);
            }

            if ($result['profile_properties']['organization_id'] > 0) {
                $organization = OrganizationsTable::query()
                    ->withSelect()
                    ->withID($result['profile_properties']['organization_id'])
                    ->exec()
                    ->fetch();

                $result['profile_properties']['company'] = htmlspecialchars_decode($organization['UF_NAME']);
                $result['profile_properties']['inn'] = $organization['UF_INN'];
                $result['profile_properties']['ogrn'] = $organization['UF_OGRN'];
                $result['profile_properties']['kpp'] = $organization['UF_KPP'];
                $result['profile_properties']['director'] = $organization['UF_DIRECTOR'];
            }
        } else {
            foreach ($userInfo as $property => $value) {
                if ($property == 'id' || $property == 'group') {
                    continue;
                }
                $result['profile_properties'][$property] = htmlspecialchars_decode($value);
            }
        }
        return $result;
    }
}

class OrderAnonymous extends AOrder
{
    public static function checkUserUnregistered($arRequest)
    {
        $userType = $arRequest['user_type'] ?? 'contact';

        $email = $arRequest['email'];
        $phone = UserPhoneAuthTable::normalizePhoneNumber($arRequest['phone']);
        $FIO = explode(' ', $arRequest['fio'] ?? '');
        $surname = $FIO[0] ?? '';
        $name = $FIO[1] ?? '';

        $inn = $arRequest['inn'];
        $ogrn = $arRequest['ogrn'];
        $organizationName = $arRequest['organization_name'];
        $organizationPostalCode = $arRequest['organization_postal_code'];
        $organizationAddress = $arRequest['organization_address'];

        Validation::checkOrderRegistrationFields(
            $userType,
            $email,
            $phone,
            $name,
            $surname,
            $inn,
            $ogrn,
            $organizationName,
            $organizationPostalCode,
            $organizationAddress
        );
        return true;
    }

    public static function checkout($arRequest)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('sale')) {
            $basket = LSBasket::loadItems()->getBasket();
            if (!$basket->count()) {
                throw new \Exception('Товаров в корзине не найдено. Обновите страницу.');
            }

            $_SESSION['ORDER_REGISTRATION'] = Auth::$MODE_REGISTRATION_IN_ORDER;
            $user = Auth::orderRegistration($arRequest);
            $orderInfo = self::order($arRequest);
            return [...$orderInfo, 'user' => $user];
        }
    }
}

class OrderRegistered extends AOrder
{
    public static function checkout($arRequest)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('sale')) {
            return self::order($arRequest);
        }
    }
}
