<?php

namespace Legacy\IblockController;

use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Iblock\SectionTable;
use Legacy\General\Constants;
use Bitrix\Main\Application;
use Legacy\HighLoadBlock\Entity;

class Settings
{
    public static function get()
    {
        return [
            'logos' => [
                'logo' => self::getImagePath('logo'),
                'mini_logo' => self::getImagePath('mini_logo'),
                'favicon_light' => self::getImagePath('favicon_light'),
                'favicon_dark' => self::getImagePath('favicon_dark'),
                'mobile_tile' => self::getImagePath('mobile_tile'),
                'light_logo' => self::getImagePath('light_logo'),
                'logo_height' => Option::get('kubx.settings', 'logo_height'),
            ],
            'colors' => [
                'primary' => self::getColors('primary'),
                'secondary' => self::getColors('secondary'),
                'gray' => self::getColors('gray'),
                'other' => [
                    'icon' => Option::get('kubx.settings', 'icon_background'),
                ]
            ],
            'metatags' => [
                'title' => Option::get('kubx.settings', 'title'),
                'description' => Option::get('kubx.settings', 'description'),
            ],
            'other' => [
                'need_mobile_navigation_text' => self::getNeedMobileNavigationText(),
                'scripts' => self::processScripts(),
                'cookies_link' => Option::get('kubx.settings', 'cookies_link'),
            ],
            'catalog' => [
                'need_preview_product_modal' => self::getNeedPreviewProductModal(),
                'show_filter_mode' => Option::get('kubx.settings', 'show_filter_mode'),
            ],
            'checkout' => [
                'allowed_unregistered_user_types' => Option::get('kubx.settings', 'allowed_unregistered_user_types'),
            ],
            'profile_navigation' => self::getProfileNavigation(),
        ];
    }

    private static function getImagePath($imageName){
        $context = Application::getInstance()->getContext();
        $server = $context->getServer();

        return Option::get('kubx.settings', $imageName)
            ? ($server->getServerPort() == '443' ? 'https' : 'http').'://'.$server->getServerName() . Option::get('kubx.settings', $imageName)
            : null;
    }

    private static function getColors($colorText)
    {
        return [
            '900' => Option::get('kubx.settings', $colorText. '_900'),
            '800' => Option::get('kubx.settings', $colorText. '_800'),
            '700' => Option::get('kubx.settings', $colorText. '_700'),
            '600' => Option::get('kubx.settings', $colorText. '_600'),
            '500' => Option::get('kubx.settings', $colorText . '_500'),
            '400' => Option::get('kubx.settings', $colorText . '_400'),
            '300' => Option::get('kubx.settings', $colorText . '_300'),
            '200' => Option::get('kubx.settings', $colorText . '_200'),
            '100' => Option::get('kubx.settings', $colorText . '_100'),
            '50' => Option::get('kubx.settings', $colorText . '_50'),
        ];
    }

    public static function getBasketColors()
    {
        return [
            Option::get('kubx.settings', 'basket_2'),
            Option::get('kubx.settings', 'basket_3'),
            Option::get('kubx.settings', 'basket_4'),
            Option::get('kubx.settings', 'basket_5'),
            Option::get('kubx.settings', 'basket_6'),
            Option::get('kubx.settings', 'basket_7'),
            Option::get('kubx.settings', 'basket_8'),
        ];
    }

    private static function getNeedMobileNavigationText()
    {
        $needMobileNavigationText = Option::get('kubx.settings', 'need_mobile_navigation_text') === 'Y';
        return $needMobileNavigationText;
    }

    private static function getNeedPreviewProductModal()
    {
        $needPreviewProductModal = Option::get('kubx.settings', 'need_preview_product_modal') === 'Y';
        return $needPreviewProductModal;
    }

    public static function getCatalogSort()
    {
        $catalogSort = Option::get('kubx.settings', 'catalog_sort');
        return self::processSort($catalogSort);
    }

    public static function getFavouriteSort()
    {
        $catalogSort = Option::get('kubx.settings', 'favourite_sort');
        return self::processSort($catalogSort);
    }

    private static function processSort($stringSort)
    {
        if(empty($scripts)) return [];

        return array_map(function ($storeAmount) {
            [$key, $value] = explode('::', $storeAmount);
            return [
                'code' => $key,
                'name' => $value];
            }, explode('<>', $stringSort));
    }

    private static function processScripts()
    {
        $scripts = Option::get('kubx.settings', 'scripts');

        if(empty($scripts)) return [];

        return array_map(function ($keyScript) {
            [$key, $script] = explode('::', $keyScript);
            return $script;
        }, explode('<>', $scripts));
    }

    public static function getPropertiesViewInDetailProduct()
    {
        $result = [];
        $propertiesViewInDetailProduct = Option::get('kubx.settings', 'properties_view_in_detail_product');
         foreach (explode('<>', $propertiesViewInDetailProduct) as $propertyView) {
             [$propertyCode, $view] = explode('::', $propertyView);
             $result[$propertyCode] = $view;
         }
        return $result;
    }
    private static function getProfileNavigation()
    {
        $result = [];
        $arFieldsFilter = [
            'UF_ACTIVE' => '1',
        ];
        $params = [
            'filter' => $arFieldsFilter,
            'order' => ['UF_SORT' => 'ASC'],
        ];
        $profileNavigationElements = Entity::getInstance()->getList(Constants::HLBLOCK_PROFILE_NAVIGATION, $params);

        foreach ($profileNavigationElements as $profileNavigationElement) {
            $result[] = [
                'id' => $profileNavigationElement['ID'],
                'name' => $profileNavigationElement['UF_NAME'],
                'image' => getFilePath($profileNavigationElement['UF_IMAGE']),
            ];
        }

        return $result;
    }

    public static function getSwagger()
    {
        return getServerName().Option::get('kubx.settings', 'swagger');
    }
}
