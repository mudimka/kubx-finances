<?php

namespace Legacy\API;

use Legacy\IblockController\KnowledgeBase as KnowledgeBaseController;

class KnowledgeBase
{
    public static function get($arRequest)
    {
        return KnowledgeBaseController::get($arRequest);
    }

    public static function getById($arRequest){
        return KnowledgeBaseController::getByIds($arRequest);
    }
}
