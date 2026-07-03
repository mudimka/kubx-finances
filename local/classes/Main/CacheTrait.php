<?php

namespace Legacy\Main;

use Bitrix\Main\Data\Cache;

trait CacheTrait
{
    public static $cache_ttl = 2419200;

    static function createCache($ttl, $id, $data, $initDir = false, $baseDir = 'cache')
    {
        $cache = Cache::createInstance();
        if ($cache->initCache($ttl, $id, $initDir, $baseDir)) {
            return $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $cache->endDataCache($data);
        }
    }

    static function getCache($ttl, $id, $initDir = false, $baseDir = 'cache')
    {
        $result = null;
        $cache = Cache::createInstance();
        if ($cache->initCache($ttl, $id, $initDir, $baseDir)) {
            $result = $cache->getVars();
        }
        return $result;
    }
}