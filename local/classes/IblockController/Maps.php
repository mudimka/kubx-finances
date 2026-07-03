<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\Iblock\MapTable;

class Maps
{
    private static function processData($query)
    {
        $result = [];

        $db = $query->exec();

        while ($arr = $db->fetch()) {
            $result [] = explode(',', $arr['MAP_VALUE']) ;
        }

        return $result;
    }


    public static function getById($arRequest)
    {
        $id = $arRequest['id'];
        if(!$id) return false;
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = MapTable::query()
                ->withSelect()
                ->withFilterByID($id);
            $result = self::processData($q);

        }

        return current($result);
    }
}
