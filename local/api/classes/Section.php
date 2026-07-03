<?php

namespace Legacy\API;

use Legacy\IblockController\Section as ControllerSection;
use Legacy\IblockController\SEO;

class Section
{
    public static function getCatalogCategories()
    {
        return [
            'items' => ControllerSection::getCatalogCategories(),
            'seo' => SEO::get(['page' => 'catalog'])
        ];
    }


    public static function getSEO($arRequest)
    {
        return SEO::getCategorySEO($arRequest['category']);
    }
}
