<?php

namespace Legacy\API;

use \Bitrix\Main\Loader;
use Legacy\General\Constants;
use Bitrix\Iblock\SectionTable;
use Legacy\HighLoadBlock\Entity;
use Legacy\Iblock\ConstructorTable;


class Constructor
{
    private static function processElements($query){
        $result = [];

        $db = $query->exec();
        while ($arr = $db->fetch()) {
            $result[] = [
                'id' => $arr['ID'],
                'name' => $arr['NAME'],
                'code' => $arr['CODE'],
                'img' => getFilePath($arr['PREVIEW_PICTURE']),
                'section' => [
                    'code' => $arr['SECTION_CODE'],
                    'name' => $arr['SECTION_NAME'],
                ],
            ];
        }

        return $result;
    }


    private static function processElementProps($query){
        $result = [
            'visual' => [],
            'additional' => []
        ];

        //Получаем все цвета, Табы, Характеристики табов из ХБ
        $colors = Entity::getInstance()->getList(Constants::HLBLOCK_COLORS, []);
        $tabs = Entity::getInstance()->getList(Constants::HLBLOCK_CONSTRUCTOR_TABS, ['order' => ['UF_SORT'=>'ASC']]);
        $characteristicsTabs = Entity::getInstance()->getList(Constants::HLBLOCK_CONSTRUCTOR_CHARACTERISTICS_TABS, [ 'order' => ['UF_TAB'=>'ASC','UF_SORT'=>'ASC']]);

        $tabsInfo = &$result['visual'];
        $db = $query->exec();
        while ($arr = $db->fetch()) {
            //Ищем индекс свойства в ХБ характеристики табов, если есть
            $propertyIndex = array_search($arr['PROPERTY_CODE'], array_column($characteristicsTabs, 'UF_PROPERTY_CODE'));

            $prop = [
                'PROPERTY_CODE' => $arr['PROPERTY_CODE'],
                'PROPERTY_NAME' => $arr['PROPERTY_NAME'],
                'PROPERTY_SORT' => is_numeric($propertyIndex) ? $characteristicsTabs[$propertyIndex]['UF_SORT'] : $arr['PROPERTY_SORT'],
            ];
            //Смотрим, является ли свойство ХБ, то есть цветом
            if ($arr['PROPERTY_USER_TYPE'] == 'directory') {
                //ищем цвет
                $colorIndex = array_search($arr['PROPERTY_VALUE'], array_column($colors, 'UF_XML_ID'));
                $prop['code'] = $colors[$colorIndex]['UF_XML_ID'];
                $prop['color'] = $colors[$colorIndex]['UF_COLOR'];
                $prop['name'] = $colors[$colorIndex]['UF_NAME'];
            } else {
                $prop['PROPERTY_DESCRIPTION'] = $arr['PROPERTY_DESCRIPTION'];
                $prop['PROPERTY_TYPE'] = $arr['PROPERTY_TYPE'];
                $prop['value'] = $arr['PROPERTY_VALUE'];
            }

            //Если характеристика табов
            if(is_numeric($propertyIndex)) {
                //получаем ID свойства
                $tabId = $characteristicsTabs[$propertyIndex]['UF_TAB'];
                //получаем индекс таба в ХБ табов
                $tabIndex = array_search($tabId, array_column($tabs, 'ID'));
                $tab = $tabs[$tabIndex];
                //если сортировки нет в массиве табов - складываем в массив по сортировке название таба
                if(!in_array($tab['UF_SORT'], array_keys($tabsInfo))){
                    $tabsInfo[$tab['UF_SORT']]['tab_name']= $tab['UF_NAME'];
                }

                //делаем активным таб из ХБ
                $currentTab = &$tabsInfo[$tab['UF_SORT']]['props'];
            } else {
                //делаем активным таб "additional"
                $currentTab = &$result['additional'];
            }

            //если свойства в табе нет - добавляем по сортировке
            if (!$currentTab[$prop['PROPERTY_SORT']]) {
                $currentTab[$prop['PROPERTY_SORT']] =
                [
                    'property_name' => $prop['PROPERTY_NAME'],
                    'property_code' => $prop['PROPERTY_CODE'],
                ];
            }

            //если свойство - да/нет
            if($prop['PROPERTY_TYPE'] === 'L'){
                $currentTab[$prop['PROPERTY_SORT']]['property_type'] = 'toggle';
            }
            //если свойство - список
            elseif ($prop['PROPERTY_TYPE'] === 'S'){
                $currentTab[$prop['PROPERTY_SORT']]['property_type'] = 'select';

                $value = [
                    'value' => $prop['value'],
                    //есть ли свойство зависящее от данного
                    'additional_prop_values' => $prop['PROPERTY_DESCRIPTION'] ? explode(';', $prop['PROPERTY_DESCRIPTION']) : null,
                ];

                $currentTab[$prop['PROPERTY_SORT']]['values'][] = $value;
            }
            //если свойство - цвет
            else {
                $currentTab[$prop['PROPERTY_SORT']]['property_type'] = 'radio';
                $currentTab[$prop['PROPERTY_SORT']]['values'][] = [
                    'code' => $prop['code'],
                    'color' => $prop['color'],
                    'name' => $prop['name'],
                ];
            }
        }

        return $result;
    }

    public static function getCategories()
    {
        $result = [];

        $db = SectionTable::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'PICTURE'
            ],
            'filter' => [
                'IBLOCK_ID' => Constants::IB_CLOTHES_CONSTRUCTOR,
                'ACTIVE' => true,
            ],
            'order' => [
                'SORT' => 'ASC',
            ],
        ]);

        while ($res = $db->fetch()) {
            $result[] = [
                'id' => $res['ID'],
                'name' => $res['NAME'],
                'code' => $res['CODE'],
                'picture' => getFilePath($res['PICTURE'])
            ];
        }

        return $result;
    }

    public static function getCategoryElements($arRequest)
    {
        $tag = $arRequest['tag'] ?? '';

        $q = ConstructorTable::query()
            ->withElementsSelect()
            ->withSectionCode($tag)
        ;

        return self::processElements($q);
    }

    public static function getElementById($arRequest)
    {
        $result = [
            'element_info' => [],
            'tabs' => []
        ];
        $id = (int) $arRequest['id'];
        $code = trim($arRequest['code']);
        if (Loader::includeModule('iblock') && ($id > 0 || mb_strlen($code) > 0)) {
            $qElement = ConstructorTable::query()
                ->withElementsSelect()
            ;

            $qProps = ConstructorTable::query()
                ->withPropertiesSelect()
            ;

            if ($id > 0) {
                $qElement->where('ID', $id);
                $qProps->where('ID', $id);
            } elseif (mb_strlen($code) > 0) {
                $qElement->where('CODE', $code);
                $qProps->where('CODE', $code);
            }

            $result['element_info'] = current(self::processElements($qElement));
            $result['tabs'] = self::processElementProps($qProps);

            return $result;
        }
    }
}