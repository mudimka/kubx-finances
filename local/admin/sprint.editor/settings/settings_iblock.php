<?php
use Legacy\General\Constants;

$settings = [
    'title' => 'Iblock_elements',
    'block_settings' => [
        'iblock_elements' => [
            'enabled_iblocks' => [
                'value' => [
                    Constants::IB_SLIDER_BLOCK,
                    Constants::IB_ADVANTAGES_BLOCK,
                    Constants::IB_PRODUCTS_BLOCK,
                    Constants::IB_ABOUT_COMPANY_BLOCK,
                    Constants::IB_PRODUCT_INDUSTRIES_BLOCK,
                    Constants::IB_WORK_STEPS_BLOCK,
                    Constants::IB_FORM_BLOCK,
                    Constants::IB_FAQ_BLOCK,
                    Constants::IB_CONTACTS_BLOCK,
                    Constants::IB_PARTNERS_BLOCK,
                    Constants::IB_CERTIFICATES_BLOCK,
                    Constants::IB_IMAGE_TEXT_BLOCK,
                    Constants::IB_REQUISITES_BLOCK,
                    Constants::IB_STAFF_CONTACTS_BLOCK,
                    Constants::IB_REVIEWS_BLOCK,
                    Constants::IB_LINKS_BLOCK,
                    Constants::IB_VACANCIES_BLOCK,
                    Constants::IB_TEMPLATE_BLOCK,
                    Constants::IB_PROMOTIONS_BLOCK,
                    Constants::IB_SERVICES_BLOCK,
                    Constants::IB_SECTIONS_BLOCK,
                    Constants::IB_LOCATIONS_BLOCK,
                    Constants::IB_COMPOSITE_BLOCK,
                    Constants::IB_VIDEO_TEXT_BLOCK,
                    Constants::IB_QUOTE_BLOCK,
                    Constants::IB_BLOG_BLOCK,
                ],
            ],
        ],
    ],

    //Разрешить добавление указанных блоков
    'block_enabled'   => [
        'iblock_elements',
    ],

    'layout_enabled' => [
        'layout_none',
    ]
];
