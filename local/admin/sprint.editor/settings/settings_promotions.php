<?php

use Legacy\General\Constants;

$settings = [
    'title' => 'promotions',
    'block_settings' => [
        'iblock_elements' => [
            'enabled_iblocks' => [
                'value' => [
                    Constants::IB_PROMOCODES,
                ],
            ],
        ],
    ],

    //Разрешить добавление указанных блоков
    'block_enabled' => [
        'iblock_elements',
        'text',
        'image'
    ],

    'layout_enabled' => [
        'layout_none',
    ]
];
