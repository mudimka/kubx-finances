<?php

namespace Legacy\User;

use Legacy\Main\CLUser;

class UserFavourite
{
    public static function get($userId)
    {
        $result = [];
        $q = FavouriteTable::query()
            ->withUserId($userId)
            ->withSelect();
        $db = $q->exec();
        if ($res = $db->fetch()) {
            $result = $res['FAVOURITE_PRODUCT_IDS'] ?: [];
        }
        return $result;
    }
    public static function set($userId, $favouriteIDs)
    {
        $LUSER = new CLUser;
        $fields = [
            'UF_FAVOURITE_PRODUCT_IDS' => $favouriteIDs,
        ];
        $LUSER->Update($userId, $fields);
    }

    public static function updateAuthorizedIDs($userId, $unauthorizedFavouriteIDs)
    {
        $favouriteIDs = self::get($userId);
        array_push($favouriteIDs, ...$unauthorizedFavouriteIDs);
        self::set($userId, array_unique($favouriteIDs));
    }
}
