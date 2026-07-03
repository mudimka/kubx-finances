<?php

namespace Legacy\User;

use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\DB\SqlExpression;

class FavouriteTable extends \Bitrix\Main\UserTable
{

    public static function withSelect(Query $query)
    {
        $query->setSelect([
            'ID',
            'FAVOURITE_PRODUCT_IDS' => 'UF_FAVOURITE_PRODUCT_IDS',
        ]);
    }

    public static function withUserId(Query $query, $uid)
    {
        if ($uid > 0) {
            $query->addFilter('ID', $uid);
        }
    }
}
