<?php

namespace Legacy\HighLoadBlock;

use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;

class Organizations extends DataManager
{
    public static function getTableName()
    {
        return 'organizations';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new StringField('UF_NAME', []),
            new StringField('UF_INN', []),
            new StringField('UF_OGRN', []),
            new StringField('UF_KPP', []),
            new StringField('UF_DIRECTOR', []),
            new StringField('UF_POSTAL_CODE', []),
            new StringField('UF_ADDRESS', []),
            new IntegerField('UF_PARENT_ORGANIZATION', []),
            new IntegerField('UF_CREATE_BY', []),
            new BooleanField('UF_ACTIVE', [])
        ];
    }
}