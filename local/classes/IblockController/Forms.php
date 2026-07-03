<?php

namespace Legacy\IblockController;

use Bitrix\Main\Loader;
use Legacy\API\User;
use Legacy\General\Constants;
use Legacy\General\DataProcessor;
use Legacy\HighLoadBlock\FormFields;
use Legacy\Iblock\ButtonFormsTable;

class Forms
{
    private static function processData($query)
    {
        $arrayPropsCodes = ['FIELDS'];
        $htmlPropsCodes = ['DESCRIPTION'];

        $result = DataProcessor::processIBProperties($query, [
            'arrayPropsCodes' => $arrayPropsCodes,
            'htmlPropsCodes' => $htmlPropsCodes
        ]);

        $user = User::get();

        foreach ($result as $id => &$block) {
            $block['id'] = $id;
            $block['FIELDS'] = FormFields::getByXmlIds(['xml_ids' => $block['FIELDS']]);
            
            if (!empty($user)) {
                foreach ($block['FIELDS'] as &$field) {
                    $field['default_value'] = self::getDefaultValue($field['code'], $user);
                }
            }
        }
        return array_change_key_case_recursive($result);
    }

    public static function get()
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $q = ButtonFormsTable::query()
                ->withSelect()
                ->withOrderBySort('ASC');
           $result = self::processData($q);
        }

        return $result;
    }

    public static function getById($arRequest)
    {
        $id = $arRequest['id'];
        if(!$id) return false;
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = ButtonFormsTable::query()
                ->withSelect()
                ->withFilterByIDs($id);

            $result = current(self::processData($q));
        }

        return $result;
    }

    public static function getByCode($arRequest)
    {
        $code = $arRequest['code'];
        if(!$code) return false;
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = ButtonFormsTable::query()
                ->withSelect()
                ->withFilterByCode($code);

            $result = current(self::processData($q));
        }

        return $result;
    }

    public static function getByIds($arRequest)
    {
        $ids = $arRequest['ids'] ?? [''];
        $result = [];
        if (Loader::includeModule('iblock')) {
            $q = ButtonFormsTable::query()
                ->withSelect()
                ->withFilterByIDs($ids);
            $result = self::processData($q);
        }

        return DataProcessor::sortResultByIDs($result, $ids, true);
    }

    private static function getDefaultValue($code, $user)
    {
        return match ($code) {
            'name' => $user['FIO'] ?: null,
            'email' => $user['email'] ?: null,
            'phone' => $user['phone'] ?: null,
            default => null,
        };
    }
}
