<?php

namespace Legacy\API;

use Legacy\Main\CLUser;
use Legacy\User\UserFavourite;
use Legacy\IblockController\Settings as SettingsController;

class Favourite
{
    public static function __callStatic($method, $arguments)
    {
        $CLUser = new CLUser();

        if ($CLUser->IsAuthorized()) {
            $class = FavouriteRegistered::class;
        } else {
            $class = FavouriteAnonymous::class;
        }

        if (method_exists($class, $method)) {
            return call_user_func($class.'::'.$method, $arguments[0]);
        } else {
            throw new \Exception('Метод не найден.');
        }
    }
}

abstract class AFavourite
{
    public static function get($arRequest)
    {
        $result = [
            'count' => 0,
            'items' => [],
        ];

        $instance = new static();
        $favouriteIDs = $instance->getIDs();

        if (!empty($favouriteIDs)) {
            $arRequest['ids'] = array_reverse($favouriteIDs);
            $arRequest['sortby'] = $arRequest['sortby'] ?? 'ids';
            $arRequest['needDivideCards'] = true;
            $catalog = Catalog::getItemsAndCount($arRequest);

            $result['items'] = $catalog['items'];
            $result['count'] = $catalog['count'];
            $result['sort'] = SettingsController::getFavouriteSort();
        }

        return $result;
    }

    protected static function checkProduct($id)
    {
        if(!Product::checkIsProductActive($id)){
            throw new \Exception('Товар не найден');
        }
    }

    abstract static function checkAndUpdateFavouriteIDs($ids);
    abstract static function getIDs();
    abstract static function add($arRequest);
    abstract static function delete($arRequest);
}

class FavouriteAnonymous extends AFavourite
{
    public static function add($arRequest) {
        global $APPLICATION;
        $id = (int)$arRequest['id'];
        self::checkProduct($id);

        $favouriteIDs = self::getCookie();
        if (!in_array($id, $favouriteIDs)) {
            $favouriteIDs[] = $id;
            $_SESSION['favourites'] = $favouriteIDs;
            $APPLICATION->set_cookie('favourites', serialize($favouriteIDs));
        }
        return self::getIDs();
    }

    public static function delete($arRequest)
    {
        global $APPLICATION;
        $id = (int)$arRequest['id'];
        self::checkProduct($id);

        $favouriteIDs = self::getCookie();
        if (in_array($id, $favouriteIDs)) {
            $favouriteIDs = array_diff($favouriteIDs, [$id]);
            $_SESSION['favourites'] = $favouriteIDs;
            $APPLICATION->set_cookie('favourites', serialize($favouriteIDs));
        }

        return self::getIDs();
    }

    public static function getIDs()
    {
        if (isset($_SESSION['favourites'])) {
            $favouriteIDs = $_SESSION['favourites'];
        } else {
            $favouriteIDs = self::getCookie();
            $_SESSION['favourites'] = $favouriteIDs;
        }
        return self::checkAndUpdateFavouriteIDs($favouriteIDs);
    }

    public static function getCookie()
    {
        global $APPLICATION;
        return empty($APPLICATION->get_cookie("favourites"))
            ? []
            : unserialize($APPLICATION->get_cookie("favourites"));
    }

    public static function checkAndUpdateFavouriteIDs($favouriteIDs)
    {
        global $APPLICATION;
        $activeFavouriteIDs = Product::getActiveProductIds($favouriteIDs);
        sort($favouriteIDs);
        sort($activeFavouriteIDs);
        if($activeFavouriteIDs != $favouriteIDs){
            $_SESSION['favourites'] = $activeFavouriteIDs;
            $APPLICATION->set_cookie('favourites', serialize($activeFavouriteIDs));
        }
        return $activeFavouriteIDs;
    }
}

class FavouriteRegistered extends AFavourite
{
    public static function add($arRequest) {
        $id = (int)$arRequest['id'];
        self::checkProduct($id);

        if ($userData = User::get()) {
            $favouriteIDs = self::getIDs();
            if (!in_array($id, $favouriteIDs)) {
                $favouriteIDs[] = $id;
                UserFavourite::set($userData['id'], $favouriteIDs);
            }
        }
        return self::getIDs();
    }

    public static function delete($arRequest)
    {
        $id = (int)$arRequest['id'];
        self::checkProduct($id);

        if ($userData = User::get()) {
            $favouriteIDs = self::getIDs();
            if (in_array($id, $favouriteIDs)) {
                UserFavourite::set($userData['id'], array_diff($favouriteIDs, [$id]));
            }
        }
        return self::getIDs();
    }

    public static function getIDs()
    {
        $favouriteIDs = [];
        if ($userData = User::get()) {
            $favouriteIDs = UserFavourite::get($userData['id']);
        }

        return self::checkAndUpdateFavouriteIDs($favouriteIDs);
    }

    public static function mergeIDs($unauthorizedFavouriteIDs)
    {
        global $APPLICATION;
        if ($userData = User::get()) {
            UserFavourite::updateAuthorizedIDs($userData['id'], $unauthorizedFavouriteIDs);
            $_SESSION['favourites'] = [];
            $APPLICATION->set_cookie('favourites', serialize([]));
        }
    }

    public static function checkAndUpdateFavouriteIDs($ids)
    {
        $activeFavouriteIDs = Product::getActiveProductIds($ids);
        sort($ids);
        sort($activeFavouriteIDs);
        if($activeFavouriteIDs != $ids){
            if ($userData = User::get()) {
                UserFavourite::set($userData['id'], $activeFavouriteIDs);
            }
        }
        return $activeFavouriteIDs;
    }
}
