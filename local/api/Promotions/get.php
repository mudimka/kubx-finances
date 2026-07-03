<?php
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!\Bitrix\Main\Loader::includeModule('iblock')) {
    echo json_encode(['error' => 'Модуль инфоблоков не загружен']);
    exit;
}

$iblockId = \Legacy\General\Constants::IB_PROMOTIONS;

$res = CIBlock::GetList([], ['ID' => $iblockId]);
if (!$res->SelectedRowsCount()) {
    echo json_encode(['error' => 'Инфоблок с ID ' . $iblockId . ' не найден']);
    exit;
}

$arSelect = ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'DETAIL_TEXT'];
$arFilter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

$items = [];
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $items[] = [
        'id' => $arFields['ID'],
        'name' => $arFields['NAME'],
        'code' => $arFields['CODE'],
        'description' => $arFields['PREVIEW_TEXT'],
        'detail' => $arFields['DETAIL_TEXT'],
    ];
}

echo json_encode(['status' => 'ok', 'count' => count($items), 'items' => $items]);
