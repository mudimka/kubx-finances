<?php

Namespace Legacy\Main;

use Bitrix\Main\Web\JWT;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;

trait JWTTrait
{
    static $secret = '';

    static function jwt_decode()
    {
        if (empty(self::$secret)) {
            self::$secret = Option::get('legacy.settings', 'jwt_secret');
        }

        $server = Context::getCurrent()->getServer();
        $jwt = mb_substr($server->get('REMOTE_USER'), 7);
        $payload = JWT::decode($jwt, self::$secret, ['HS256']);
        return (array) $payload->data;
    }
}