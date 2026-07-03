<?php

namespace Legacy\API;

use Legacy\IblockController\Blog as BlogController;

class Blog
{
    public static function get($arRequest)
    {
        return BlogController::get($arRequest);
    }

    public static function getByCode($arRequest)
    {
        if (empty($arRequest['code'])) {
            throw new \Exception('Не передан код страницы');
        }

        $result = BlogController::getByCode($arRequest);
        if (empty($result)) {
            throw new \Exception('Страница не найдена.', 404);
        }
        return $result;
    }
}
