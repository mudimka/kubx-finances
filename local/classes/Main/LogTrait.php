<?php

namespace Legacy\Main;

trait LogTrait
{
    public static function createFile($className, $function, $requestData, $responseData)
    {
        $date = (new \DateTime())->format('c');
        $class = substr($className, strrpos($className, '\\') + 1);

        \Bitrix\Main\Diag\Debug::writeToFile(
            [
                'date' => $date,
                'class' => $className,
                'function' => $function,
                'request' => $requestData,
                'status' => $responseData['status'],
                'response' => $responseData['result']
            ],
            'Логирование от '.$date,
            "/$class.log"
        );
    }
}