<?php

namespace Legacy\API;

use Bitrix\Main\Loader;
use Legacy\Catalog\PropertyTable;
use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;
use Legacy\IblockController\File;
use Legacy\IblockController\Settings as SettingsController;

class Properties
{
    private static function processData($query)
    {
        $propertiesViewInDetailProduct = SettingsController::getPropertiesViewInDetailProduct();
        $result = [];
        $db = $query->exec();

        while ($res = $db->fetch()) {
            $res['USER_TYPE_SETTINGS'] = unserialize($res['USER_TYPE_SETTINGS']);
            $tableName = $res['USER_TYPE_SETTINGS']['TABLE_NAME'];

            $features = [];
            if($res['FEATURES']){
                $features = array_reduce(
                    explode(',', $res['FEATURES']),
                    function ($res, $feature) {
                        [$key, $value] = explode(':', $feature);
                        $res[$key] = $value;
                        return $res;
                    }
                );
            }

            if(!$result[$res['CODE']]) {
                $result[$res['CODE']] = [
                    'ID' => $res['ID'],
                    'IBLOCK_ID' => $res['IBLOCK_ID'],
                    'CODE' => $res['CODE'],
                    'NAME' => $res['NAME'],
                    'TYPE' => $res['PROPERTY_TYPE'],
                    'USER_TYPE' => $res['USER_TYPE'],
                    'TABLE_NAME' => $tableName,
                    'MULTIPLE' =>  $res['MULTIPLE'] == 'Y',
                    'DISPLAY' => $res['SECTION_PROPERTY_DISPLAY'],
                    'OFFER_DISPLAY' => $propertiesViewInDetailProduct[$res['CODE']],
                    'FEATURES' => $features,
                    'SORT' => (int)$res['SORT'],
                    'VALUES_ORDER' => [],
                    'VALUES' => $result[$res['CODE']]['VALUES'] ?? [],
                ];
            }

            if ($res['VALUE']) {
                [$id, $value] = explode('::', $res['VALUE']);
                if (!in_array($value, $result[$res['CODE']]['VALUES_ORDER'])) {
                    $result[$res['CODE']]['VALUES_ORDER'][] = $value;
                }

                $result[$res['CODE']]['VALUES'][$value]['count'] = $result[$res['CODE']]['VALUES'][$value]['count'] > 0 ? $result[$res['CODE']]['VALUES'][$value]['count'] + 1 : 1;
                $result[$res['CODE']]['VALUES'][$value]['code'] = $value;

                if ($tableName) {
                    $result[$res['CODE']]['VALUES'][$value]['alias'] = $res[$tableName.'_NAME'];
                } elseif ($res['PROPERTY_TYPE'] == 'L') {
                    $result[$res['CODE']]['VALUES'][$value]['alias'] = $res['PROPERTY_ENUM_VALUE'];
                } elseif ($res['PROPERTY_TYPE'] == 'S' && $res['USER_TYPE'] == 'HTML'){
                    $result[$res['CODE']]['VALUES'][$value]['alias'] = unserialize($value)['TEXT'];
                } else {
                    $result[$res['CODE']]['VALUES'][$value]['alias'] = $value;
                }
            }
        }

        return $result;
    }

    public static function getPropertiesValues($productsValuesCodes, $properties)
    {
        $propertiesValuesCodes = self::collectPropertiesValuesCodes($productsValuesCodes, $properties);
        $propertyResult = [];
        foreach($propertiesValuesCodes as $pCode => $valueCodes) {
            $propertyValues = [];
            if ($properties[$pCode]) {
                switch ($properties[$pCode]['USER_TYPE']) {
                    case 'directory':
                        $hbID = constant('Legacy\General\Constants::HLBLOCK_'.mb_strtoupper($properties[$pCode]['TABLE_NAME']));
                        $hbPropTypes = Entity::getInstance()->getFields($hbID);

                        $params = [
                            'filter' => [
                                'UF_XML_ID' => $valueCodes,
                            ],
                            'order' => [
                                'UF_SORT' => 'ASC',
                            ],
                        ];
                        $hbValues = Entity::getInstance()->getList($hbID, $params);
                        foreach ($hbValues as $hbValue){
                            $value = null;

                            foreach ($hbValue as $hbValueKey => $hbValueValue) {
                                $key = mb_strtolower(
                                    mb_strpos($hbValueKey, 'UF_') !== false
                                        ? mb_substr($hbValueKey, 3)
                                        : $hbValueKey
                                );
                                $key = $key == 'xml_id' ? 'code' : $key;
                                $key = $key == 'name' ? 'alias' : $key;

                                $value[$key] =
                                    $hbPropTypes[$hbValueKey]['USER_TYPE_ID'] == 'file'
                                        ? getFilePath($hbValueValue)
                                        : $hbValueValue;
                            }

                            $propertyValues[$hbValue['UF_XML_ID']] = $value;
                        }
                        $propertyResult[$pCode] = $propertyValues;
                        break;
                    case 'HTML':
                        foreach ($valueCodes as $valueCode) {
                            $propertyValues[$valueCode] = [
                                'alias' => unserialize($valueCode)['TEXT'],
                                'code' => $valueCode
                            ];
                        }

                        $propertyResult[$pCode] = $propertyValues;
                        break;

                    default:
                        if ($properties[$pCode]['TYPE'] == PropertyTable::TYPE_FILE) {
                            $propertyValues = File::getFilesInfo($valueCodes, true);
                        }
                        elseif ($properties[$pCode]['TYPE'] == PropertyTable::TYPE_LIST) {
                            $listValues = \CIBlockPropertyEnum::GetList(
                                [], ["ID" => $valueCodes]
                            );
                            $propertyValues = [];
                            while ($listRes = $listValues->fetch()) {
                                $propertyValues[$listRes['ID']] = [
                                    'alias' => $listRes['VALUE'] == "Y" ? true : $listRes['VALUE'],
                                    'code' => $listRes['XML_ID'],
                                    'sort' => $listRes['SORT'],
                                ];
                            }
                        }
                        else {
                            foreach ($valueCodes as $valueCode) {
                                $propertyValues[$valueCode] = [
                                    'alias' => $valueCode,
                                    'code' => $valueCode
                                ];
                            }
                        }

                        $propertyResult[$pCode] = $propertyValues;
                        break;
                }
            }
        }
        return $propertyResult;
    }

    private static function collectPropertiesValuesCodes($productsValuesCodes, $elProperties)
    {
        $valuesCodes = [];

        foreach ($productsValuesCodes as $productValuesCodes) {
            foreach($productValuesCodes as $code => $values) {
                if (mb_strpos($code, 'PROPERTY_') !== false
                    && preg_match('/PROPERTY_(.*)_VALUE/', $code, $matches) && $values) {
                    $propertyCode = $matches[1];

                    switch ($elProperties[$propertyCode]['USER_TYPE']) {
                        case 'HTML':
                            $valuesCodes[$propertyCode][] = $values;
                            break;
                        default:
                            $propertiesCodeValues = $valuesCodes[$propertyCode] ?? [];
                            $valuesCodes[$propertyCode] = array_merge($propertiesCodeValues, explode(',', $values));
                            break;
                    }
                }
            }
        }

        return $valuesCodes;
    }

    public static function getPropertiesAliasesAndInfo($productValuesCodes, $propertiesValues, $propertiesInfo)
    {
        $properties = [];
        foreach($productValuesCodes as $code => $values) {
            if (mb_strpos($code, 'PROPERTY_') !== false
                && preg_match('/PROPERTY_(.*)_VALUE/', $code, $matches)) {
                $propertyCode = $matches[1];
                $preparedPropertyCode = mb_strtolower($matches[1]);

                if(!$values) {
                    $properties[$preparedPropertyCode] = null;
                    continue;
                }

                $properties[$preparedPropertyCode] = [
                    'id' => $propertiesInfo[$propertyCode]['ID'],
                    'code' => $propertyCode,
                    'name' => $propertiesInfo[$propertyCode]['NAME'],
                    'sort' => $propertiesInfo[$propertyCode]['SORT'],
                    'type' => $propertiesInfo[$propertyCode]['OFFER_DISPLAY'] ?: null,
                    'place' => array_keys($propertiesInfo[$propertyCode]['FEATURES'])
                ];

                $values = explode(',', $values);
                foreach ($values as $value) {
                    $properties[$preparedPropertyCode]['value'][] = $propertiesValues[$propertyCode][$value];
                }
                $propertyValue = $properties[$preparedPropertyCode]['value'];
                $properties[$preparedPropertyCode]['value'] =
                    $propertiesInfo[$propertyCode]['MULTIPLE']
                        ? $propertyValue
                        : current($propertyValue);
            }
        }

        return $properties;
    }


    public static function get($arRequest)
    {
        $result = [];
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $query = self::getProperties($arRequest);
            if ($arRequest['is_smart_filter']) {
                $query = self::getSmartFilter($arRequest, $query);
            } else {
                $query = self::addFeaturesFilter($arRequest, $query);
            }
            $query = $query->withCache(true, 3600);

            $result = self::processData($query);
        }

        return $result;
    }

    private static function addFeaturesFilter($arRequest, $query)
    {
        if ($arRequest['is_detail']){
            return $query->withFeatureDetail();
        }
        elseif ($arRequest['is_basket']){
            return $query->withFeatureBasket();
        }
        elseif ($arRequest['is_listing']){
            return $query->withFeatureListing();
        }

        return $query;
    }

    private static function getProperties($arRequest)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $query = PropertyTable::query()
                ->withProperties()
                ->withIblockFilter($arRequest['iblock_id'])
                ->withSort()
            ;

            return $query;
        }

        throw new \Exception('Ошибка!');
    }

    private static function getSmartFilter($arRequest, $query)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $iblockId = $arRequest['iblock_id'];
            $HBQuery = PropertyTable::query()->withRuntimeHighloadBlocks()->withIblockFilter($iblockId);

            $select = [];
            $runtime = [];
            $order = [];
            $db = $HBQuery->exec();
            while ($res = $db->fetch()) {
                $res['SETTINGS'] = unserialize($res['SETTINGS']);
                $tableName = $res['SETTINGS']['TABLE_NAME'];
                if ($tableName) {
                    $select[$tableName.'_NAME'] = $tableName.'.UF_NAME';
                    $order[$tableName.'.UF_SORT'] = 'ASC';
                    $id = Entity::getInstance()->getId($tableName);
                    $runtime[$tableName] = [
                        'data_type' => Entity::getInstance()->getDataClass($id),
                        'reference' => [
                            'this.ELEMENT_PROPERTY.VALUE' => 'ref.UF_XML_ID',
                        ],
                    ];
                }
            }
            $query->withAddSelect($select)->withAddRuntime($runtime)->withAddOrder($order);

            if (isset($arRequest['product_offer_ids'])){
                if (count($arRequest['product_offer_ids'])){
                    $ids = $arRequest['product_offer_ids'];
                }
                else {
                    $ids = [''];
                }
            }
            else {
                $ids = [];
            }

            $offerProps = self::getOfferProperties();
            $query = $query
                ->withSmartFilterOnly()
                ->withIDsFilter($ids)
                ->withValues($offerProps)
            ;

            return $query;
        }

        throw new \Exception('Ошибка!');
    }

    public static function getOfferProperties() {
        $result = [];

        $rsProperty = PropertyTable::query()
            ->withOfferPropsOnly()
            ->withIblockFilter(Constants::IB_CATALOG_CRM_OFFERS)
        ;

        $db = $rsProperty->exec();

        while($arProperty = $db->fetch()) {
            $result[] = [
                'ID' => $arProperty['ID'],
                'CODE' => $arProperty['CODE'],
                'NAME' => $arProperty['NAME'],
                'SORT' => $arProperty['SORT'],
                'TABLE_NAME' => $arProperty['USER_TYPE_SETTINGS_LIST']['TABLE_NAME'],
                'FEATURES' => explode(',', $arProperty['FEATURES']),
            ];
        }
        return $result;
    }

    public static function getOrderedOfferPropertiesCodes() {
        $result = [];

        $rsProperty = PropertyTable::query()
            ->withOfferPropsOnly()
            ->withIblockFilter(Constants::IB_CATALOG_CRM_OFFERS)
            ->withSort()
        ;

        $db = $rsProperty->exec();

        while($arProperty = $db->fetch()) {
            $result[] = $arProperty['CODE'];
        }
        return $result;
    }

    public static function getPropertyType($type) {
        switch ($type){
            case 'F':
                return 'checkbox';
            case 'A':
                return 'range';
            case 'K':
                return 'toggle';
            case 'H':
                return 'checkbox_with_search';
            default:
                return $type;
        }
    }
}
