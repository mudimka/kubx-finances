<?php

namespace Legacy\API;

use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Iblock\SectionTable;
use Legacy\General\Constants;
use Legacy\HighLoadBlock\Entity;
use Legacy\Iblock\TestTable;

class Helper
{
    public static function uploadImage($arRequest) {

        $res = [];
        foreach ($_FILES as &$file) {
            $file['name'] = preg_replace('/[^\p{L}\p{N}]+/u', '_', $file['name']);
            $fileID = \CFile::SaveFile(
                $file,
                'kubx.settings/' . $file['name'],
            );

            $res[$fileID] = \CFile::GetPath($fileID);
        }
        $jsonResult = ['pathes' => $res];

        return $jsonResult;
    }


    public static function sendMessage($arRequest)
    {
        $arEventFields = [
            'SITE_ID' => SITE_ID,
//            'EMAIL' => 'Dryppa90@gmail.com',
            'EMAIL' => 's.zhenihov@legacystudio.ru',
//            'EMAIL' => 'zhenihov01@gmail.com',
            'CONFIRM_CODE' => '123456'
        ];

        $event = new \CEvent;
        $event->Send('NEW_USER_CONFIRM', $arEventFields['SITE_ID'], $arEventFields);
    }

    public static function initMainBaskets($arRequest)
    {
        global $USER;
        if($USER->IsAdmin()){
            $result = \Bitrix\Main\UserTable::getList(array(
                'select' => array('ID'),
                'filter' => array('ACTIVE' => true)
            ));
            $ids = array_map(function ($a) {
                return $a['ID'];
            }, $result->fetchAll());
            $params = [
                'filter' => [
                    'UF_USER' => $ids,
                    'UF_MAIN' => true
                ],
                'order' => [
                    'ID' => 'ASC',
                ],
                'select' =>  [
                    'UF_USER'
                ]
            ];
            $mainBaskets = Entity::getInstance()->getList(Constants::HLBLOCK_MULTI_BASKET, $params);
            $idsWithMain = array_map(function ($a) {
                return $a['UF_USER'];
            }, $mainBaskets  ?: []);
            $idsNeedToCreateMainBasket = array_diff($ids, $idsWithMain);
            $count = 0;
            foreach ($idsNeedToCreateMainBasket as $id){
                $params = [
                    'UF_USER' => $id,
                    'UF_NAME' => 'Основная корзина',
                    'UF_DESCRIPTION' => '',
                    'UF_MAIN' => 1,
                    'UF_COLOR' => 'secondary'
                ];
                if(Entity::getInstance()->add(Constants::HLBLOCK_MULTI_BASKET, $params)){
                    $count++;
                };
            }
            return $count;
        } else {
            throw new \Exception('Недостаточно прав!');
        }
    }

}
