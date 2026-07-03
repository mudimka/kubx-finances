<?php
use Legacy\General\Constants;

$settings = [
    'title' => 'Video_text',

    'block_settings' => [
        'my_video_with_text' => [
            'enabled_iblocks' => [
                'value' => [
                    Constants::IB_VIDEOS,
                ],
            ],
        ],
    ],

    'block_enabled' => [
        'my_video_with_text',
    ],

    'layout_enabled' => [
        'layout_none',
    ]
];

