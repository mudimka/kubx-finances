<?php

namespace Legacy\HighLoadBlock;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\UserTable;
use Legacy\API\User;
use Legacy\General\Constants;
use Legacy\Main\CLUser;

class Notifications
{
    public static function addNews($newsId)
    {
        if (!empty($newsId)) {
            $receivers = self::getNewsReceivers();
            foreach ($receivers as $id) {
                $fields['UF_USER_ID'] = $id;
                $fields['UF_NEWS_ID'] = $newsId;
                Entity::getInstance()->add(Constants::HLBLOCK_NEWS_NOTIFICATIONS, $fields);
            }
        }
    }

    private static function getNewsReceivers()
    {
        $receivers = [];
        $params = [
            'select' => [
                'ID'
            ],
        ];
        $filter = [
            'ACTIVE' => 'Y',
            'BLOCKED' => 'N',
        ];
        $db = UserTable::getList($params, $filter);
        while ($res = $db->fetch()) {
            $receivers[] = $res['ID'];
        }
        return $receivers;
    }

    public static function clearUnreadNews()
    {
        $userId = User::getID();

        if ($userId) {
            $db = Entity::getInstance()->getList(Constants::HLBLOCK_NEWS_NOTIFICATIONS, ['filter' => ['UF_USER_ID' => $userId]]);
            foreach ($db as $res) {
                Entity::getInstance()->delete(Constants::HLBLOCK_NEWS_NOTIFICATIONS, $res['ID']);
            }
        }
    }
}
