<?php

namespace Legacy\General;

use Bitrix\Main\Context;
use Legacy\Main\CacheTrait;
use Bitrix\Main\Config\Option;
use Legacy\Main\CLUser;
use Legacy\Main\LogTrait;

final class Api
{
    /**
     * @var Api
     */
    private static $instance;
    private $context;
    private $request;
    private $data = [];
    private $cache_id;

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): Api
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        $this->context = Context::getCurrent();
        $this->request = $this->context->getRequest();
        $this->fetch();
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    private function __wakeup()
    {
    }

    private function fetchCookies()
    {
        $rawCookies = $this->request->getCookieRawList()->toArray();
        $cookies = [];
        foreach ($rawCookies as $key => $value) {
            $json = json_decode($value, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $cookies[$key] = $json;
            } else {
                $cookies[$key] = $value;
            }
        }
        $this->data['cookies'] = $cookies;
    }

    private function fetchPost()
    {
        $input = file_get_contents('php://input');
        $json = json_decode($input, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $data = $json;
        } else {
            $data = $this->request->getPostList()->toArray() ?? [];
            $files = $this->request->getFileList()->toArray() ?? [];
            $data = array_merge($data, $files);
        }

        $this->data = array_merge($this->data, $data);
    }

    private function fetchGet()
    {
        $this->data = array_merge($this->data, $this->request->toArray());
    }

    private function fetch()
    {
        $this->fetchGet();
        $this->fetchPost();
        $this->cache_id = md5(json_encode($this->data));
        $this->fetchCookies();
    }

    public function execute($class, $method, $data = [])
    {
        if(Option::get('kubx.settings', 'need_authorize') === 'Y') {
            $CLUser = new CLUser();
            $CLUser->Authorize(Option::get('kubx.settings', 'user_id_to_authorize') ?: 1);
        }

        header('Content-Type: application/json; charset=utf-8');
        try {
            $this->data = array_merge($this->data, $data);

            if (is_callable([$class, $method])) {
                if (in_array(CacheTrait::class, \class_uses($class))) {
                    $ttl = $class::$cache_ttl;
                    $id = $class.'\\'.$method.'\\'.$this->cache_id;
                    $className = mb_strtolower((new \ReflectionClass($class))->getShortName());
                    if (!($return = call_user_func($class.'::'.'getCache', $ttl, $id, "legacy/$className"))) {
                        $return = call_user_func($class.'::'.$method, $this->data);
                        call_user_func($class.'::'.'createCache', $ttl, $id, $return, "legacy/$className");
                        $result['cache_fresh'] = true;
                    }
                    $result['cache_used'] = true;
                } else {
                    $return = call_user_func([$class, $method], $this->data);
                }
            } else {
                throw new \Exception('Метод не найден.');
            }

            $result['status'] = 'ok';
            $result['result'] = $return;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorCode = (int)$e->getCode() ?: 400;
            if($errorCode == 100){
                $errorCode = 400;
            }

            http_response_code($errorCode);
            $result['status'] = 'error';

            if ($arr = json_decode($errorMessage, true)){
                $result['result'] = $arr;
            } else {
                $result['result'] = ['message' => $errorMessage];
            }
        }

        if (in_array(LogTrait::class, \class_uses($class))) {
            call_user_func($class.'::'.'createFile', $class, $method, $this->data, $result);
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
