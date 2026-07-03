<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Legacy\HighLoadBlock\Entity;

class Notifications
{
    public static function isNewsUpdated()
    {
        $userId = User::getID();

        $params = [
            'select' => [
                'ID'
            ],
            'filter' => [
                'UF_USER_ID' => $userId,
            ],
        ];

        $db = Entity::getInstance()->getList(Constants::HLBLOCK_NEWS_NOTIFICATIONS, $params);
        if ($db) {
            return true;
        }
        return false;
    }
}
