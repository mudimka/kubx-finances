<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\Iblock\FooterContentTable;

class FooterContent
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['CONTACTS', 'DOCUMENTS_LINKS', 'LINKS', 'SOCIAL_LINKS'];
        $sprintEditorPropsCodes = ['SUBTITLE', 'CATALOG_SECTIONS'];

        return DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);
    }

    public static function get()
    {
        $requestItem = null;
        if (Loader::includeModule('iblock')) {
            $q = FooterContentTable::query()
                ->withSelect();

            $requestItem = current(self::processData($q));
        }

        $section_ids = $requestItem['CATALOG_SECTIONS'][0]['section_ids'];
        return [
            'catalog_sections' => DataProcessor::sortResultByIDs(Section::getByIds($section_ids), $section_ids),
            'contacts' => Contacts::getByIds(['ids' => $requestItem['CONTACTS']]),
            'documents_links' => Links::getByIds(['ids' => $requestItem['DOCUMENTS_LINKS']]),
            'links' => Links::getByIds(['ids' => $requestItem['LINKS']]),
            'social_links' => Links::getByIds(['ids' => $requestItem['SOCIAL_LINKS']]),
            'copyright' => $requestItem['COPYRIGHT_TEXT'],
            'description' => $requestItem['SUBTITLE'][0]['value'],
        ];
    }
}
