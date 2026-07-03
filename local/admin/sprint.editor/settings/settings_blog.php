<?php
use Legacy\General\Constants;

$settings = [
    'title' => 'Blog_settings',
    'block_settings' => [
        'iblock_elements' => [
            'enabled_iblocks' => [
                'value' => [
                    Constants::IB_FAQ_BLOCK,
                    Constants::IB_TEMPLATE_BLOCK,
                    Constants::IB_VIDEO_TEXT_BLOCK,
                    Constants::IB_QUOTE_BLOCK,
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
