<?php

namespace Legacy\Sale;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Main\Entity\ExpressionField;

class OrderTable extends \Bitrix\Sale\Internals\OrderTable
{
    const DEFAULT_LIMIT = 10;

    public static function withSelect(Query $query)
    {
        $query->setSelect([
            'ID',
            'ACCOUNT_NUMBER',
            'DATE_INSERT',
            'DATE_STATUS',
            'STATUS_NAME' => 'STATUS.NAME',
            'STATUS_COLOR' => 'STATUS_INFO.COLOR',
            'STATUS_CODE' => 'STATUS_INFO.ID',
            'PAYMENT_PAY_SYSTEM_NAME' => 'PAYMENT.PAY_SYSTEM.NAME',
            'PAYED',
            'PAY_SYSTEM_ID',
            'DELIVERY_ID',
            'PRICE_DELIVERY',
            'PRICE',
            'DISCOUNT_ALL',
        ]);

        $query->registerRuntimeField(
            'STATUS_INFO',
            new ReferenceField(
                'STATUS_INFO',
                StatusTable::class,
                [
                    'this.STATUS_ID' => 'ref.ID',
                ]
            )
        );
    }

    public static function withOrderByID(Query $query)
    {
        $query->addOrder('ID', 'DESC');
    }

    public static function withProperties(Query $query)
    {
        $query->addSelect('PROPERTY.CODE', 'PROPERTY_CODE');
        $query->addSelect('PROPERTY.VALUE', 'PROPERTY_VALUE');
        $query->addFilter('!=PROPERTY.VALUE', null);
    }

    public static function withBasketLength(Query $query)
    {
        $query->addSelect(new ExpressionField(
            'BASKET_LENGTH',
            'COUNT(DISTINCT %s)',
            ['BASKET.PRODUCT_ID']
        ));
    }

    public static function withLimit(Query $query, int $limit = self::DEFAULT_LIMIT)
    {
        if ($limit > 0) {
            $query->setLimit($limit);
        } else{
            $query->setLimit(self::DEFAULT_LIMIT);
        }
    }

    public static function withPage(Query $query, int $page)
    {
        if ($page > 0) {
            $query->setOffset(($page - 1) * $query->getLimit());
        }
    }

    public static function withUserId(Query $query, $uid)
    {
        $query->where('USER_ID', $uid);
    }

    public static function withFilterById(Query $query, $oid)
    {
        $query->addFilter('ID', $oid);
    }

    public static function withOrderByDate(Query $query)
    {
        $query->addOrder('DATE_INSERT', 'DESC');
    }

    public static function withFilter(Query $query, $filter)
    {
        switch ($filter) {
            case 'new':
                $query->addFilter('PAYED', 'N');
                $query->addFilter('!=STATUS_CODE', 'F');
                break;
            case 'paid':
                $query->addFilter('PAYED', 'Y');
                $query->addFilter('!=STATUS_CODE', 'F');
                break;
            case 'archive':
                $query->addFilter('STATUS_CODE', 'F');
                break;
            default:
                break;
        }
    }
}
