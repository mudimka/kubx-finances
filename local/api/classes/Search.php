<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Bitrix\Main\Loader;

class Search
{
    public static function getFromBar($arRequest)
    {
        if (!Loader::includeModule('search')) {
            throw new \Exception('Внутренняя ошибка.');
        }

        $result = ['items' => [], 'suggestions' => []];
        $query = $arRequest['q'];
        $page = (int)$arRequest['page'];
        $filter = $arRequest['filter'] ?? [];
        $limit = (int)$arRequest['limit'];

        $arParams = [
            'QUERY' => $query,
            'SITE_ID' => 's1',
            'MODULE_ID' => 'iblock',
            'PARAM1' => ['CRM_PRODUCT_CATALOG'],
            'PARAM2' => [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS],
        ];
        $obSearch = new \CSearch;
        $obSearch->SetOptions(['ERROR_ON_EMPTY_STEM' => false]);
        $obSearch->Search($arParams);
        if (!$obSearch->selectedRowsCount()) {
            $obSearch->Search($arParams, array(), array('STEMMING' => false));
        }

        $searchResult = [];
        while ($row = $obSearch->fetch()) {
            $searchResult []= $row;
        }

        if (!empty($searchResult)) {
            $catalog = Catalog::getSearchItems([
                'ids' => array_column($searchResult, 'ITEM_ID'),
                'page' => $page,
                'limit' => $limit,
                'filter' => $filter,
            ]);

            $result['items'] = $catalog;
            $result['suggestions'] = array_unique(array_column($searchResult, 'TITLE'));
        }

        return $result;
    }

    public static function get($arRequest)
    {
        if (!Loader::includeModule('search')) {
            throw new \Exception('Внутренняя ошибка.');
        }

        $result = ['filter' => [], 'items' => [], 'count' => 0, 'suggestions' => []];
        $query = $arRequest['q'];
        $page = (int)$arRequest['page'];
        $filter = $arRequest['filter'] ?? [];
        $limit = (int)$arRequest['limit'];
        $sortby = $arRequest['sortby'];

        $arParams = [
            'QUERY' => $query,
            'SITE_ID' => 's1',
            'MODULE_ID' => 'iblock',
            'PARAM1' => ['CRM_PRODUCT_CATALOG'],
            'PARAM2' => [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS],
        ];
        $obSearch = new \CSearch;
        $obSearch->SetOptions(['ERROR_ON_EMPTY_STEM' => false]);
        $obSearch->Search($arParams);
        if (!$obSearch->selectedRowsCount()) {
            $obSearch->Search($arParams, array(), array('STEMMING' => false));
        }

        $searchResult = [];
        while ($row = $obSearch->fetch()) {
            $searchResult []= $row;
        }

        if (!empty($searchResult)) {
            $catalog = Catalog::get([
                'ids' => array_column($searchResult, 'ITEM_ID'),
                'page' => $page,
                'limit' => $limit,
                'filter' => $filter,
                'sortby' => $sortby,
            ]);
            $result['filter'] = $catalog['filter'];

            $result['items'] = $catalog['items'];
            $result['count'] = $catalog['count'];
            $result['suggestions'] = array_unique(array_column($searchResult, 'TITLE'));
        }

        return $result;
    }

    public static function getSuggestions($arRequest)
    {
        if (!Loader::includeModule('search')) {
            throw new \Exception('Внутренняя ошибка.');
        }

        $result = [];
        $query = $arRequest['q'];

        $arParams = [
            'QUERY' => $query,
            'SITE_ID' => 's1',
            'MODULE_ID' => 'iblock',
            'PARAM1' => ['CRM_PRODUCT_CATALOG'],
            'PARAM2' => [Constants::IB_CATALOG_CRM, Constants::IB_CATALOG_CRM_OFFERS],
        ];
        $obSearch = new \CSearch;
        $obSearch->SetOptions(['ERROR_ON_EMPTY_STEM' => false]);
        $obSearch->Search($arParams);
        if (!$obSearch->selectedRowsCount()) {
            $obSearch->Search($arParams, array(), array('STEMMING' => false));
        }

        $searchResult = [];
        $obSearch->NavStart();
        while ($row = $obSearch->fetch()) {
            $searchResult []= $row;
        }

        if (!empty($searchResult)) {
            $result = array_values(array_unique(array_column($searchResult, 'TITLE')));
        }

        return $result;
    }
}
