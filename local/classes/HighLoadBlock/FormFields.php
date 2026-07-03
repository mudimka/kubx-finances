<?php

namespace Legacy\HighLoadBlock;

use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Legacy\General\DataProcessor;

class FormFields
{
    private static function processField($fieldsArray)
    {
        $fieldsArray = self::parseToApiCodes($fieldsArray);

        $fieldsArray['code'] = $fieldsArray['xml_id'];
        unset($fieldsArray['xml_id']);

        if (isset($fieldsArray['type'])) {
            $fieldsArray['type'] = self::getPropertyEnumXmlValues($fieldsArray['type']);
        }

        if (isset($fieldsArray['data_type'])) {
            $fieldsArray['data_type'] = self::getPropertyEnumXmlValues($fieldsArray['data_type']);
        }

        $fieldsArray['multiple'] = (bool)$fieldsArray['multiple'];
        $fieldsArray['has_other_option'] = (bool)$fieldsArray['has_other_option'];
        $fieldsArray['is_required'] = (bool)$fieldsArray['is_required'];

        $fieldsArray['items'] = array_map([self::class, 'parseItem'], $fieldsArray['items'] ?? []);

        return $fieldsArray;
    }

    private static function parseItem($item)
    {
        [$code, $name] = explode('::', $item, 2);
        return [
            'code' => $code,
            'name' => $name ?? $code,
        ];
    }

    private static function parseToApiCodes($data)
    {
        $result = [];

        foreach ($data as $property => $value) {
            $key = strtolower(str_replace('UF_', '', $property));
            $result[$key] = $value;
        }

        return $result;
    }

    private static function getPropertyEnumXmlValues($fieldId)
    {
        $arFilter = ['ID' => $fieldId];
        $property_enums = \CUserFieldEnum::GetList([], $arFilter);
        if ($enum_fields = $property_enums->GetNext()) {
            return $enum_fields['XML_ID'];
        }
        return null;
    }

    public static function getByXmlIds($arRequest)
    {
        $xml_ids = $arRequest['xml_ids'] ?? [];

        if (empty($xml_ids)) {
            return [];
        }

        $params = [
            'filter' => [
                '@UF_XML_ID' => $xml_ids
            ],
        ];

        $data = Entity::getInstance()->getList(Constants::HLBLOCK_FORM_FIELDS, $params);

        $data = array_map([self::class, 'processField'], $data);
        return DataProcessor::sortResultByCodes($data, $xml_ids);
    }
}
