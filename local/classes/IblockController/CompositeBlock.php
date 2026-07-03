<?php


namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\IblockPropertiesTable;

class CompositeBlock
{
    private static function processData($query)
    {
        $arrayPropsCodes = [];
        $enumPropsCodes = ['TEMPLATE', 'BACKGROUND_COLOR'];
        $filePropsCodes = [];
        $sprintEditorPropsCodes = ['BLOCKS'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'filePropsCodes' => $filePropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes], true);

        foreach ($result as &$block) {
            $items = [];
            foreach ($block['BLOCKS'] as $blockInfo) {
                array_push(
                    $items,
                    ...Pages::getBlocksInfo($blockInfo['iblock_id'], $blockInfo['element_ids'])
                );
            }
            $block['items'] = $items;
            unset($block['BLOCKS']);
        }

        return $result;
    }
    public static function getByIds($arRequest)
    {
        $ids = empty($arRequest['ids']) ? [''] : $arRequest['ids'];

        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = IblockPropertiesTable::query()
                ->withIblock(Constants::IB_COMPOSITE_BLOCK)
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return array_values($result);
    }
}
