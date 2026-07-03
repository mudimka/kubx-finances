<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Legacy\General\DataProcessor;
use Legacy\Iblock\CompositePagesTable;
use Legacy\Iblock\PagesTable;

class Pages
{
    private static function processPageData($query)
    {
        $sprintEditorPropsCodes = ['BLOCKS'];

        return DataProcessor::processIBProperties($query, ['sprintEditorPropsCodes' => $sprintEditorPropsCodes], true);
    }

    private static function processSimplePageData($query, $pagesIds)
    {
        $result = [];

        $db = $query->exec();

        while ($arr = $db->fetch()) {
            $result []= [
                'id' => $arr['ID'],
                'name' => $arr['PAGE_HEADER'],
                'code' => $arr['CODE'],
            ];
        }

        return DataProcessor::sortResultByIDs($result, $pagesIds);
    }


    private static function processCompositePageData($query)
    {
        $sprintEditorPropsCodes = ['PAGES'];

        return DataProcessor::processIBProperties($query, ['sprintEditorPropsCodes' => $sprintEditorPropsCodes], true);
    }

    public static function getPage($arRequest)
    {
        $code = $arRequest['code'];
        $requestItem = null;
        if (Loader::includeModule('iblock')) {
            $q = PagesTable::query()
                ->withSelect()
                ->withFilterByCode($code);
            $requestItem = current(self::processPageData($q));
        }

        if (!$requestItem) {
            return null;
        }

        $blocks = [];
        foreach ($requestItem['BLOCKS'] as $blockInfo) {
            array_push(
                $blocks,
                ...self::getBlocksInfo($blockInfo['iblock_id'], $blockInfo['element_ids'])
            );
        }

        return array_change_key_case_recursive([
            'page_header' => $requestItem['PAGE_HEADER'],
            'blocks' => $blocks,
            'seo' => SEO::getElementSEO(Constants::IB_PAGES, $requestItem['INFO']['ID'])
        ]);
    }

    public static function getPageCodeByID($id)
    {
        if (empty($id)) {
            return false;
        }

        $q = PagesTable::query()
            ->withSimpleSelect()
            ->withFilterByIDs($id);

        return $q->exec()->fetch()['CODE'] ?: false;
    }

    public static function getCompositePage($arRequest)
    {
        $code = $arRequest['code'];
        if(!$code) {
            throw new \Exception('Не передан код страницы');
        }

        $requestItem = null;
        if (Loader::includeModule('iblock')) {
            $q = CompositePagesTable::query()
                ->withSelect()
                ->withFilterByCode($code);
            $requestItem = current(self::processCompositePageData($q));
        }

        if (!$requestItem) {
            return null;
        }

        $pagesIds = $requestItem['PAGES'][0]['element_ids'];

        $q = PagesTable::query()
            ->withSimpleSelect()
            ->withFilterByIDs($pagesIds);
        $pages = self::processSimplePageData($q, $pagesIds);
        $pageCode = $arRequest['pageCode'] ?? $pages[0]['code'];

        $pageInfo = self::getPage(['code' => $pageCode]);
        if (!$pageInfo) {
            throw new \Exception('Страница не найдена.', 404);
        }

        return [
            'tabs' => $pages,
            'current_tab' => $pageCode,
            'banner_link' => isset($requestItem['BANNER_LINK']) ? Links::getById(['id' => $requestItem['BANNER_LINK']]) : null,
            'page_info' => $pageInfo,
            'seo' => SEO::getElementSEO(Constants::IB_COMPOSITE_PAGES, $requestItem['INFO']['ID'])
        ];
    }

    public static function getBlocksInfo($blockId, $ids)
    {
        switch ($blockId) {
            case Constants::IB_COMPOSITE_BLOCK:
                $blocks = CompositeBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'composite',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_SLIDER_BLOCK:
                $blocks = SliderBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'slider',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_ADVANTAGES_BLOCK:
                $blocks = AdvantagesBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'advantages',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_PRODUCTS_BLOCK:
                $blocks = ProductsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'products',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_ABOUT_COMPANY_BLOCK:
                $blocks = AboutCompanyBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'aboutCompany',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_PRODUCT_INDUSTRIES_BLOCK:
                $blocks = ProductIndustriesBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'productIndustries',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_WORK_STEPS_BLOCK:
                $blocks = WorkStepsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'workSteps',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_FORM_BLOCK:
                $blocks = FormBlock::getByIds(['ids' => $ids]);

                return array_map(function($block) {
                    return [
                        'type' => 'formBlock',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_FAQ_BLOCK:
                $blocks = FAQBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'faq',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_CONTACTS_BLOCK:
                $blocks = ContactsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'contacts',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_PARTNERS_BLOCK:
                $blocks = PartnersBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'partners',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_CERTIFICATES_BLOCK:
                $blocks = CertificatesBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'certificates',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_IMAGE_TEXT_BLOCK:
                $blocks = ImageTextBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'imageText',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_REQUISITES_BLOCK:
                $blocks = RequisitesBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'requisites',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_STAFF_CONTACTS_BLOCK:
                $blocks = StaffContactsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'staffContacts',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_REVIEWS_BLOCK:
                $blocks = ReviewsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'reviews',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_LINKS_BLOCK:
                $blocks = LinksBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'links',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);
            case Constants::IB_VACANCIES_BLOCK:
                $blocks = VacanciesBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'vacancies',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_TEMPLATE_BLOCK:
                $blocks = TemplateBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'templateBlock',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_PROMOTIONS_BLOCK:
                $blocks = PromotionsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'promotions',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_SERVICES_BLOCK:
                $blocks = ServicesBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'services',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_SECTIONS_BLOCK:
                $blocks = SectionsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'sections',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_LOCATIONS_BLOCK:
                $blocks = LocationsBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'locations',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_VIDEO_TEXT_BLOCK:
                $blocks = VideoTextBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'videoText',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_QUOTE_BLOCK:
                $blocks = QuoteBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'quote',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            case Constants::IB_BLOG_BLOCK:
                $blocks = BlogBlock::getByIds(['ids' => $ids]);
                return array_map(function($block) {
                    return [
                        'type' => 'blog',
                        'title' => '',
                        'subtitle' => '',
                        ...$block,
                    ];
                }, $blocks);

            default:
                return [];
        }
    }
}
