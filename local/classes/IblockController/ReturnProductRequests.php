<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\General\DataProcessor;
use Legacy\General\Constants;
use Legacy\Iblock\ReturnProductRequestsTable;
use Bitrix\Main\IO\File;

class ReturnProductRequests
{
    private static function processData($query)
    {
        $arrayPropsCodes = [];
        $enumPropsCodes = ['TEMPLATE'];
        $filePropsCodes = ['IMAGE'];
        $sprintEditorPropsCodes = ['SUBTITLE'];

        $result = DataProcessor::processIBProperties($query, ['arrayPropsCodes' => $arrayPropsCodes, 'enumPropsCodes' => $enumPropsCodes, 'filePropsCodes' => $filePropsCodes, 'sprintEditorPropsCodes' => $sprintEditorPropsCodes]);

        foreach ($result as &$block) {
            $block['SUBTITLE'] = $block['SUBTITLE'][0]['value'];
            $block['FORM'] = Forms::getById(['id' => $block['FORM']]);
        }

        return $result;
    }
    public static function createRequest($arRequest)
    {
        $user = $arRequest['user'];

        //получаем id статуса по умолчанию
        $propertyEnums = \CIBlockPropertyEnum::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => Constants::IB_RETURN_PRODUCTS, 'CODE' => 'STATUS', 'XML_ID' => 'in_process']
        );
        if ($enum = $propertyEnums->fetch()) {
            $defaultStatus = $enum['ID'];
        }

        //получаем id причины
        $propertyEnums = \CIBlockPropertyEnum::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => Constants::IB_RETURN_PRODUCTS, 'CODE' => 'REASON', 'XML_ID' => $arRequest['reason']]
        );
        if ($enum = $propertyEnums->fetch()) {
            $reasonValue = $enum['ID'];
        }

        $arElementFields = [
            'IBLOCK_ID' => Constants::IB_RETURN_PRODUCTS,
            'PROPERTY_VALUES' => [
                'BUYER_ID' => $user['id'],
                'BUYER_FIO' => $user['FIO'],
                'BUYER_PHONE' => $user['phone'],
                'BUYER_EMAIL' => $user['email'],

                'ORDER_ID' => $arRequest['orderId'],
                'ORDER_DATE' => $arRequest['orderDate'],

                'STATUS' => $defaultStatus ?? '',
                'REASON' => $reasonValue ?? '',
                'COMMENT' => $arRequest['comment'],
                'PRODUCTS' => $arRequest['returnedProducts'],

                'PRODUCTS_PHOTOS' => self::uploadPhotos($arRequest['product_photos'] ?? []),
                'DEFECT_PHOTOS' => self::uploadPhotos($arRequest['defect_photos'] ?? []),
                'PACKAGES_PHOTOS' => self::uploadPhotos($arRequest['package_photos'] ?? []),
            ],
            'NAME' => 'Заяка на возврат от '. date_create()->format('d.m.Y H:i:s'),
            'ACTIVE' => 'Y'
        ];
        $el = new \CIBlockElement;
        if ($el->add($arElementFields)) {
            return true;
        } else {
            throw new \Exception($el->LAST_ERROR);
        }
    }


    public static function isRequestWasCreated($arRequest)
    {
        if (Loader::includeModule('iblock')) {
            $q = ReturnProductRequestsTable::query()
                ->withFilterByOrderID($arRequest['orderId']);
            ;
            if($q->fetch()) {
                return true;
            }
        }

        return false;
    }

    public static function uploadPhotos($photos)
    {
        $photosIds = [];

        if(count($photos)) {
            $count = count($photos['error']);
            for ($i = 0; $i < $count; $i++) {
                if (!intval($photos['error'][$i])) {
                    if (File::isFileExists($photos['tmp_name'][$i])) {
                        $arFile = \CFile::MakeFileArray($photos['tmp_name'][$i]);
                        $arFile['name'] = $photos['name'][$i];

                        $fileID = \CFile::SaveFile($arFile, 'returnProductsRequestsPhotos');
                        $photosIds[] = \CFile::MakeFileArray($fileID);
                    }
                }
            }
        }

        return $photosIds;
    }

}
