<?php

namespace Kubx\Settings\Events;

class PropertyFeature
{
    public static function OnPropertyFeatureBuildList(\Bitrix\Main\Event $event)
    {
        $features = [];

        $features[] = [
            'MODULE_ID' => 'kubx.settings',
            'FEATURE_ID' => 'KUBX_USE_TO_BUILD_OFFERS',
            'FEATURE_NAME' => 'KUBX Использовать в построении торговых предложений',
        ];

        $features[] = [
            'MODULE_ID' => 'kubx.settings',
            'FEATURE_ID' => 'KUBX_USE_TO_GROUP_OFFERS',
            'FEATURE_NAME' => 'KUBX Использовать для группировки по свойству',
        ];

        $features[] = [
            'MODULE_ID' => 'kubx.settings',
            'FEATURE_ID' => 'KUBX_USE_IN_LISTING',
            'FEATURE_NAME' => 'KUBX Показывать в листинге каталога',
        ];

        $features[] = [
            'MODULE_ID' => 'kubx.settings',
            'FEATURE_ID' => 'KUBX_USE_IN_DETAIL',
            'FEATURE_NAME' => 'KUBX Показывать на детальной странице',
        ];

        $features[] = [
            'MODULE_ID' => 'kubx.settings',
            'FEATURE_ID' => 'KUBX_USE_IN_DETAIL_TABLE',
            'FEATURE_NAME' => 'KUBX Показывать на детальной странице в таблице',
        ];

        $features[] = [
            'MODULE_ID' => 'kubx.settings',
            'FEATURE_ID' => 'KUBX_USE_IN_BASKET',
            'FEATURE_NAME' => 'KUBX Показывать свойство в корзине',
        ];

        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, $features);
    }
}
