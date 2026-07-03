<?php

namespace Legacy\IblockController;

use Legacy\General\Constants;
use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;
use Legacy\HighLoadBlock\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;

class Section
{
    public static function getContentSections($query, $onlyFirstLevel = true)
    {
        if (empty($query)) return null;

        $result = [];
        $filterResult = [];
        $subcategories = [];
        while ($res = $query->fetch()) {
            $count = !empty($subcategories[$res['ID']])
                ? array_sum(array_column($subcategories[$res['ID']], 'elements_count'))
                : 0;
            $count += (int)$res['ELEMENTS_COUNT'];

            $sectionInfo = [
                'id' => $res['ID'],
                'name' => $res['NAME'],
                'code' => $res['CODE'],
                'elements_count' => $count,
            ];

            if ($onlyFirstLevel) {
                $filterResult[$res['CODE']][] = $res['CODE'];

                if ($subcategories[$res['ID']]) {
                    foreach ($subcategories[$res['ID']] as $subcategory) {
                        $filterResult[$res['CODE']] = array_merge(
                            $filterResult[$res['CODE']],
                            $filterResult[$subcategory['code']]
                        );
                    }
                }
            } else {
                $sectionInfo['subcategories'] = $subcategories[$res['ID']];
            }

            if ($count > 0) {
                if ($res['DEPTH_LEVEL'] == 1) {
                    $result[] = $sectionInfo;
                } else {
                    $subcategories[$res['IBLOCK_SECTION_ID']][] = $sectionInfo;
                }
            }
        }
        return [$result, $filterResult];
    }

    public static function getHierarchy($code)
    {
        $db = SectionTable::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'DEPTH_LEVEL',
                'IBLOCK_SECTION_ID',
            ],
            'filter' => [
                'IBLOCK_ID' => Constants::IB_CATALOG_CRM
            ],
            'order' => [
                'DEPTH_LEVEL' => 'DESC',
                'SORT' => 'ASC',
            ],
        ]);

        $categories = $db->fetchAll();

        $index = array_search($code, array_column($categories, 'CODE'));

        $result = self::findUpperCategories($index, $categories);

        return $result;
    }

    private static function findUpperCategories($index, $categories){
        $result = [];

        $id = $categories[$index]['IBLOCK_SECTION_ID'];
        $parent_index = array_search($id, array_column($categories, 'ID'));

        if($parent_index){
            $result = array_merge($result, self::findUpperCategories($parent_index, $categories));
        }

        $previous_codes = array_column($result, 'code');
        $url = (empty($previous_codes) ? '' : '/') . implode('/', $previous_codes) . '/' . $categories[$index]['CODE'];

        $result[] = [
            'code' => $categories[$index]['CODE'],
            'name' => $categories[$index]['NAME'],
            'url'  => $url,
        ];

        return $result;
    }

    public static function getByIds($ids)
    {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $db = SectionTable::getList([
                'select' => [
                    'ID',
                    'NAME',
                    'CODE',
                    'PICTURE',
                    'DESCRIPTION',
                    'IBLOCK_SECTION_ID',
                ],
                'filter' => [
                    'IBLOCK_ID' => Constants::IB_CATALOG_CRM,
                    'ACTIVE' => true,
                    'ID' => $ids
                ]
            ]);

            while ($res = $db->fetch()) {
                $hierarchy = self::getHierarchy($res['CODE']);
                $result [] = [
                    'id' => $res['ID'],
                    'name' => $res['NAME'],
                    'code' => $res['CODE'],
                    'description' => $res['DESCRIPTION'],
                    'picture' => getFilePath($res['PICTURE']),
                    'url' => $hierarchy[count($hierarchy) - 1]['url'],
                ];
            }
        }

        return $result;
    }

    public static function getIdByCode($code)
    {
        if (!$code || !Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
            return false;
        }

        $res = SectionTable::getList([
            'select' => ['ID'],
            'filter' => ['CODE' => $code],
        ])->fetch();

        return $res['ID'] ?? false;
    }

    public static function getByCode($code)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $db = SectionTable::getList([
                'select' => [
                    'NAME',
                    'CODE'
                ],
                'filter' => [
                    'CODE' => $code
                ]
            ]);
            $res = $db->fetch();
            return [
                'CODE' => $res['CODE'],
                'NAME' => $res['NAME'],
                'HIERARCHY' => self::getHierarchy($code)
            ];
        }

        return false;
    }

    public static function getSeoDescriptionByCode($code)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $db = \CIBlockSection::GetList(
                [],
                [
                    'IBLOCK_ID'=>Constants::IB_CATALOG_CRM,
                    'CODE' => $code
                ],
                false,
                [
                    "UF_SEO_DESCRIPTION"
                ]
            );

            $res = $db->fetch();
            return $res['UF_SEO_DESCRIPTION'];
        }

        return false;
    }

//     * @return string Имя пользователя.
    /**
     * Обновление ссылок категорий в каталоге.
     *
     * @param string $code Символьный код категории.
     * @param array &$subcategories Массив подкатегорий текущей категории.
     */
    private static function updateChildrenUrl($code, &$subcategories = [])
    {
        foreach ($subcategories as &$subcategory){
            $subcategory['url'] = '/' .  $code . $subcategory['url'];
            if($subcategory['subcategories']){
                self::updateChildrenUrl($code, $subcategory['subcategories']);
            }
        }
    }

    /**
     * Получение категорий Интернет магазина.
     */
    public static function getCatalogCategories()
    {
        $result = [];

        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $db = SectionTable::getList([
                'select' => [
                    'ID',
                    'NAME',
                    'CODE',
                    'PICTURE',
                    'DESCRIPTION',
                    'IBLOCK_SECTION_ID',
                    'DEPTH_LEVEL',
                ],
                'filter' => [
                    'IBLOCK_ID' => Constants::IB_CATALOG_CRM,
                    'ACTIVE' => true,
                ],
                'order' => [
                    'DEPTH_LEVEL' => 'DESC',
                    'SORT' => 'ASC',
                ],
            ]);

            $subcategories = [];
            while ($res = $db->fetch()) {
                self::updateChildrenUrl($res['CODE'], $subcategories[$res['ID']]);
                if($res['DEPTH_LEVEL'] == 1) {
                    $result [] = [
                        'id' => $res['ID'],
                        'name' => $res['NAME'],
                        'code' => $res['CODE'],
                        'description' => $res['DESCRIPTION'],
                        'picture' => getFilePath($res['PICTURE']),
                        'url' => '/' .  $res['CODE'],
                        'subcategories' => $subcategories[$res['ID']],
                    ];
                } else {
                    $subcategories[$res['IBLOCK_SECTION_ID']][] = [
                        'id' => $res['ID'],
                        'name' => $res['NAME'],
                        'code' => $res['CODE'],
                        'description' => $res['DESCRIPTION'],
                        'picture' => getFilePath($res['PICTURE']),
                        'url' => '/' .  $res['CODE'],
                        'subcategories' => $subcategories[$res['ID']],
                    ];
                }
            }
        }

        return $result;
    }

    public static function getCategoryChildrenCodes($codes) {
        if (empty($codes)) {
            return [];
        }

        if(!is_array($codes)) {
            $codes = [$codes];
        }

        $subSections = [];

        $sectionsDb = SectionTable::getList([
            'select' => [
                'LEFT_MARGIN',
                'RIGHT_MARGIN'
            ],
            'filter' => [
                'ACTIVE' => 'Y',
                'CODE' => $codes,
                'IBLOCK_ID' => [
                    Constants::IB_CATALOG_CRM,
                    Constants::IB_CATALOG_CRM_OFFERS
                ],
            ],
        ]);

        $sections = [];
        while ($section = $sectionsDb->fetch()){
            $sections[] = [
                'LEFT_MARGIN' => $section['LEFT_MARGIN'],
                'RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
            ];
        }

        if (empty($sections)) {
            return [];
        }

        $marginFilters = [
            'LOGIC' => 'OR'
        ];

        foreach ($sections as $section) {
            $marginFilters[] = [
                '>=LEFT_MARGIN' => $section['LEFT_MARGIN'],
                '<=RIGHT_MARGIN' => $section['RIGHT_MARGIN']
            ];
        }

        $res = SectionTable::getList([
            'select' => [
                'CODE'
            ],
            'filter' => [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => [
                    Constants::IB_CATALOG_CRM,
                    Constants::IB_CATALOG_CRM_OFFERS
                ],
                $marginFilters
            ],
            'order' => [
                'LEFT_MARGIN' => 'ASC'
            ]
        ]);

        while ($subSection = $res->fetch()) {
            $subSections[] = $subSection['CODE'];
        }

        return array_unique($subSections);
    }

    public static function getSectionTableSize($code)
    {
        $result = null;
        $db = \CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID'=>Constants::IB_CATALOG_CRM,
                'CODE' => $code
            ],
            false,
            [
                "NAME",
                "CODE",
                "UF_TABLE_SIZES"
            ]
        );

        $tableSizeID = $db->fetch()['UF_TABLE_SIZES'];
        $params = [
            'filter' => [
                'ID' => $tableSizeID,
            ],
        ];
        $table_sizes = Entity::getInstance()->getRow(Constants::HLBLOCK_TABLE_SIZES, $params);

        if ($table_sizes){
            $result = [
                'name' => $table_sizes['UF_NAME'],
                'code' => $table_sizes['UF_XML_ID'],
            ];
        }

        return $result;
    }

    public static function extractSectionInfo($sectionsInfo, $catalogCategory = null)
    {
        $sections = [];
        foreach (explode(',', $sectionsInfo) as $section) {
            [$code, $name] = explode(':', $section);
            $sections[] = ['CODE' => $code, 'NAME' => $name];
            if ($code === $catalogCategory || !$catalogCategory) {
                $hierarchy = self::getHierarchy($code);

                return [
                    'name' => $name,
                    'code' => $code,
                    'hierarchy' => $hierarchy,
                    'url' => $hierarchy[count($hierarchy) - 1]['url'],
                ];
            }
        }

        $hierarchy = self::getHierarchy($sections[0]['CODE']);
        return [
            'name' => implode(', ', array_column($sections, 'NAME')),
            'code' => $sections[0]['CODE'],
            'hierarchy' => $hierarchy,
            'url' => $hierarchy[count($hierarchy) - 1]['url'],
        ];
    }


    public static function isSectionExist($categoryCode, $iblockId = Constants::IB_CATALOG_CRM)
    {
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
            $db = SectionTable::getList([
                'select' => [
                    'ID',
                ],
                'filter' => [
                    'CODE' => $categoryCode,
                    'IBLOCK_ID' => $iblockId
                ]
            ]);
            $res = $db->fetch();
            if($res){
                return true;
            }
        }

        return false;
    }
}
