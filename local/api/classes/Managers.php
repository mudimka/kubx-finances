<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Bitrix\Main\UserTable;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Exception;
use Legacy\Main\CLUser;
use Bitrix\Main\IO\File;


class Managers
{
    public static function getManagerIDs()
    {
        $result = [];
        $filter = Array
        (
            "GROUPS_ID" => [Constants::GROUP_CRM_SHOP_MANAGER]
        );

        $arParams = [
            'FIELDS' =>
            [
                "ID"
            ]
        ];

        $order = [
            'ID' => 'ASC'
        ];
        $rsUsers = \CUser::GetList($order, null, $filter, $arParams);
        while ($user = $rsUsers->fetch()){
            $result[] = $user['ID'];
        }

        return $result;
    }

    public static function getManagerID()
    {
        $managersId = self::getManagerIDs();

        if (count($managersId)){
            $lastIndex = count($managersId)-1;
            return $managersId[rand(0,$lastIndex)];
        } else{
            return '';
        }
    }

    public static function getManagerByID($arRequest)
    {
        $manager = \CUser::GetByID($arRequest['id'])->GetNext();
        return [
            'FIO' => $manager['LAST_NAME'] . ' ' . $manager['NAME'],
            'email' => $manager['EMAIL'],
            'phone' => $manager['PERSONAL_PHONE'],
            'photo' =>getFilePath($manager['PERSONAL_PHOTO'])
        ];
    }


}
