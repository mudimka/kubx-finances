<?php

namespace Legacy\HighLoadBlock;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\OrderPropsValueTable;

class OrganizationsTable extends \Legacy\HighLoadBlock\Organizations
{
    public static function withSelect(Query $query)
    {
        $query->setSelect([
            'ID',
            'UF_NAME',
            'UF_INN',
            'UF_OGRN',
            'UF_KPP',
            'UF_DIRECTOR',
            'UF_PARENT_ORGANIZATION',
            'UF_POSTAL_CODE',
            'UF_ADDRESS',
        ]);
        $query->setOrder(['ID' => 'ASC']);
        $query->setFilter(['UF_ACTIVE' => true]);
    }

    public static function withID(Query $query, $id)
    {
        $query->addFilter('ID', $id);
    }

    public static function withManagerOrganizations(Query $query, $uid, $ids)
    {
        $query->addFilter(null, [
            'LOGIC' => 'OR',
            'UF_CREATE_BY' => $uid,
            'ID' => $ids
        ]);
    }

    public static function withParent(Query $query, $parentID, $uid, $userOrganizations)
    {
        $query->addFilter('UF_PARENT_ORGANIZATION', $parentID);

        $query->addFilter('!=UF_CREATE_BY', $uid);
        if (!empty($userOrganizations)) {
            $query->addFilter('!=ID', $userOrganizations);
        }
    }

    public static function withOrderCount(Query $query)
    {
        $query->registerRuntimeField(
            'ORDER_PROP_ORGANIZATION',
            new ReferenceField(
                'ORDER_PROP_ORGANIZATION',
                OrderPropsValueTable::class,
                [
                    'this.ID' => 'ref.VALUE',
                    'ref.CODE' => new SqlExpression('?', 'ORGANIZATION_ID'),
                ]
            )
        );
        $query->registerRuntimeField(
            'ORDER',
            new ReferenceField(
                'ORDER',
                OrderTable::class,
                ['this.ORDER_PROP_ORGANIZATION.ORDER_ID' => 'ref.ID']
            )
        );

        $query->addFilter('!=ORDER.STATUS_ID', ['F', 'D']);
        $query->addSelect(new ExpressionField('ORDERS_COUNT', 'COUNT(DISTINCT %s)', ['ORDER.ID']));
    }

    public static function withChildOrganizationsCount(Query $query)
    {
        $query->registerRuntimeField(
            'CHILD',
            new ReferenceField(
                'CHILD',
                Organizations::class,
                [
                    'this.ID' => 'ref.UF_PARENT_ORGANIZATION',
                ]
            )
        );
        $query->addSelect(new ExpressionField('CHILDS_COUNT', 'COUNT(DISTINCT %s)', ['CHILD.ID']));
    }
}