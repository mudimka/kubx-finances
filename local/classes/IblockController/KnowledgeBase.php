<?php

namespace Legacy\IblockController;

use Legacy\Iblock\KnowledgeBaseTable;
use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\SectionTable;

class KnowledgeBase
{
    private static function processData($query)
    {
        $sprintEditorPropsCodes = ['CONTENT'];
        $result = DataProcessor::processIBProperties($query, ['sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        return array_change_key_case_recursive($result);
    }

    public static function get($arRequest)
    {
        $result = [
            'sections' => [],
            'items' => []
        ];

        if (Loader::includeModule('iblock')) {
            list($sections, $filterSections)= self::getSections();
            $currentSection = $arRequest['section'] ?? $sections[0]['code'];

            $q = KnowledgeBaseTable::query()
                ->withSelect()
                ->withSectionCode($filterSections[$currentSection])
                ->withOrderBySort('ASC');

            $result['sections'] = $sections;
            $result['items'] = self::processData($q);
        }
        return $result;
    }

    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [''];
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = KnowledgeBaseTable::query()
                ->withSelect()
                ->withFilterByIDs($ids)
                ->withOrderBySort('ASC');
            $result = self::processData($q);

        }
        return DataProcessor::sortResultByIDs($result, $ids, true);
    }

    private static function getSections()
    {
        $q = SectionTable::query()
            ->withDefault(Constants::IB_KNOWLEDGE_BASE)
            ->withElementsCount()
            ->withOrderByDepth('DESC')
            ->withOrderBySort('ASC')
            ->exec()
        ;

        return Section::getContentSections($q);
    }
}
