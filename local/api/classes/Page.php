<?php


namespace Legacy\API;

use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Legacy\IblockController\Pages;
use Legacy\IblockController\Services;
use Legacy\IblockController\TestPages;

class Page
{
    public static function get($arRequest)
    {
        if (empty($arRequest['code'])) {
            throw new \Exception('Не передан код страницы');
        }

        $pageInfo = self::getAlternativePage($arRequest)
            ?: (Pages::getCompositePage($arRequest)
                ?? Pages::getPage($arRequest));

        if (empty($pageInfo)) {
            throw new \Exception('Страница не найдена.', 404);
        }

        return $pageInfo;
    }

    private static function getAlternativePage($arRequest)
    {
        $code = $arRequest['code'];

        switch ($code) {
            case 'services':
                $result = $arRequest['pageCode'] ?
                    Services::getByCode(['pageCode' => $arRequest['pageCode']]) :
                    Pages::getPage($arRequest);

                if (!$result) {
                    throw new \Exception('Страница не найдена.', 404);
                }
                return $result;

            default:
                return false;
        }
    }
}
