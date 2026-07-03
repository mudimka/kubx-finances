<?php

namespace Legacy\Iblock;

use Legacy\General\Constants;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Type\DateTime;

class BlogTable extends \Bitrix\Iblock\ElementTable
{
    public static function setDefaultScope($query)
    {
        $query
            ->where("IBLOCK_ID", Constants::IB_BLOG)
            ->where("ACTIVE", true);
    }

    public static function withSelect(Query $query)
    {
        $query->registerRuntimeField(
            'P_TITLE',
            new ReferenceField(
                'P_TITLE',
                ElementPropertyTable::class,
                [
                    'ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_BLOG_TITLE),
                ]
            )
        );
        $query->registerRuntimeField(
            'P_PREVIEW_DESCRIPTION',
            new ReferenceField(
                'P_PREVIEW_DESCRIPTION',
                ElementPropertyTable::class,
                [
                    'ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_BLOG_PREVIEW_DESCRIPTION),
                ]
            )
        );

        $query->setSelect([
            'ID',
            'CODE',
            'ACTIVE_FROM',
            'PREVIEW_PICTURE',
            'TITLE' => 'P_TITLE.VALUE',
            'PREVIEW_DESCRIPTION' => 'P_PREVIEW_DESCRIPTION.VALUE'
        ]);
    }

    public static function withDetailSelect(Query $query)
    {
        $query->registerRuntimeField(
            'P_TITLE',
            new ReferenceField(
                'P_TITLE',
                ElementPropertyTable::class,
                [
                    'ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_BLOG_TITLE),
                ]
            )
        );
        $query->registerRuntimeField(
            'P_BLOCKS',
            new ReferenceField(
                'P_BLOCKS',
                ElementPropertyTable::class,
                [
                    'ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_BLOG_BLOCKS),
                ]
            )
        );
        $query->registerRuntimeField(
            'P_RECOMMENDED_POSTS',
            new ReferenceField(
                'P_RECOMMENDED_POSTS',
                ElementPropertyTable::class,
                [
                    'ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                    'ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?',Constants::IB_PROP_BLOG_RECOMMENDED_POSTS),
                ]
            )
        );

        $query->setSelect([
            'ID',
            'CODE',
            'ACTIVE_FROM',
            'DETAIL_PICTURE',
            'TITLE' => 'P_TITLE.VALUE',
            'BLOCKS' => 'P_BLOCKS.VALUE',
            new ExpressionField('RECOMMENDED_POSTS', 'GROUP_CONCAT(%s SEPARATOR "::")', ['P_RECOMMENDED_POSTS.VALUE']),
        ]);
    }

    public static function withFilterByСode(Query $query, $code)
    {
        $query->where('CODE', $code);
    }

    public static function withFilterByID(Query $query, $id)
    {
        if (!empty($id)) {
            $query->addFilter('ID', $id);
        }
    }

    public static function withExcludeID(Query $query, $id)
    {
        if (!empty($id)) {
            $query->addFilter('!=ID', $id);
        }
    }

    public static function withFilterDateActive(Query $query)
    {
        $currentDate = new DateTime();

        $query->addFilter(null, [
            'LOGIC' => 'OR',
            '<=ACTIVE_FROM' => $currentDate,
            'ACTIVE_FROM' => null,
        ]);
        $query->addFilter(null, [
            'LOGIC' => 'OR',
            '>=ACTIVE_TO' => $currentDate,
            'ACTIVE_TO' => null,
        ]);
    }

    public static function withSection(Query $query, $section)
    {
        if (isset($section) && $section !== 'all') {
            $query->addFilter('IBLOCK_SECTION.CODE', $section);
        }
    }

    public static function withOrderBySort(Query $query, $order)
    {
        $query->addOrder('SORT', $order);
    }

    public static function withOrderByDate(Query $query, $order)
    {
        $query->addOrder('ACTIVE_FROM', $order);
    }

    public static function withPage(Query $query, $page)
    {
        if ($page > 0) {
            $query->setOffset(($page - 1) * $query->getLimit());
        }
    }
}
