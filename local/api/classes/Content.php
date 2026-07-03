<?php

namespace Legacy\API;

use Bitrix\Main\Loader;
use Legacy\IblockController\FooterContent;
use Legacy\IblockController\HeaderContent;
use Legacy\IblockController\RegistrationContent;

class Content
{
    public static function getHeader()
    {
        return HeaderContent::get();
    }
    public static function getFooter()
    {
        return FooterContent::get();
    }

    public static function getPageProducts($arRequest)
    {
        $catalogItems = Catalog::getItemsAndCount([
            'ids' => $arRequest['ids'],
        ])['items'];

        $catalogCategories = [];

        foreach ($catalogItems as $item) {
            if (!in_array($item['section']['code'], array_column($catalogCategories, 'code'))) {
                $catalogCategories[] = [
                    'name' => $item['section']['name'],
                    'code' => $item['section']['code'],
                ];
            }
        }

        return [
            'tabs' => $catalogCategories,
            'items' => $catalogItems,
        ];
    }

    public static function getRegistration()
    {
        return RegistrationContent::get();
    }

}
