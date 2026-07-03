<?php

namespace Legacy\API;

use Legacy\IblockController\News as NewsController;

class News
{
    public static function get($arRequest)
    {
        return NewsController::get($arRequest);
    }

    public static function getByCode($arRequest)
    {
        if (empty($arRequest['code'])) {
            throw new \Exception('Не передан код страницы');
        }

        $result = NewsController::getByCode($arRequest);
        if (empty($result)) {
            throw new \Exception('Страница не найдена.', 404);
        }
        return $result;
    }
}
