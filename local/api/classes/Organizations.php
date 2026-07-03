<?php

namespace Legacy\API;

use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Bitrix\Main\UserTable;
use Legacy\HighLoadBlock\OrganizationsTable;
use Legacy\Sale\OrderTable;

class Organizations
{
    private static function processQuery($uid, $isManager, $userOrganizations, $withCounts = false, $parent = null, $withInfo = false)
    {
        $query = OrganizationsTable::query()
            ->withSelect()
        ;

        if ($withCounts) {
            $query->withOrderCount();
        }
        if ($isManager) {
            $query->withChildOrganizationsCount();
        }

        if ($parent) {
            $query->withParent($parent, $uid, $userOrganizations);
        } else {
            if ($isManager) {
                $query->withManagerOrganizations($uid, $userOrganizations);
            } else {
                $query->withID($userOrganizations);
            }
        }

        $result = [];
        $db = $query->exec();
        while ($res = $db->fetch()) {
            $organization = [
                'id' => $res['ID'],
                'name' => $res['UF_NAME'],
            ];
            if ($withCounts) {
                $organization['employees_count'] = UserTable::getList([
                    'filter' => ['UF_ORGANIZATION' => $res['ID']],
                    'count_total' => true
                ])->getCount();
                $organization['orders_count'] = (int)$res['ORDERS_COUNT'];
            }
            if ($withInfo) {
                $organization['inn'] = $res['UF_INN'];
                $organization['ogrn'] = $res['UF_OGRN'];
                $organization['kpp'] = $res['UF_KPP'];
                $organization['director'] = $res['UF_DIRECTOR'];
                $organization['postal_code'] = $res['UF_POSTAL_CODE'];
                $organization['address'] = $res['UF_ADDRESS'];
                $organization['parent_id'] = $res['UF_PARENT_ORGANIZATION'];
            }

            $result[] = $organization;
            if ($res['CHILDS_COUNT'] > 0) {
                $childOrganizations = self::processQuery($uid, $isManager, $userOrganizations, $withCounts, $res['ID']);
                if (count($childOrganizations) > 0) {
                    $result = array_merge($result, $childOrganizations);
                }
            }
        }
        return $result;
    }

    public static function getList()
    {
        if (!Loader::IncludeModule('sale')) {
            throw new \Exception('Произошла неизвестная ошибка');
        }

        $result = [];
        if ($user = User::get()) {
            $uid = (int)$user['id'];
            $isManager = in_array(Constants::GROUP_ORGANIZATION_MANAGER, UserTable::getUserGroupIds($uid));
            $userOrganizations = UserTable::getRow([
                'select' => ['UF_ORGANIZATION'],
                'filter' => ['ID' => $uid],
            ])['UF_ORGANIZATION'];

            $result = self::processQuery($uid, $isManager, $userOrganizations, true, null, true);
        }
        return $result;
    }

    public static function getUserOrganizations()
    {
        $result = [
            'user_id' => null,
            'user_type' => null,
            'is_main_manager' => null,
            'is_manager' => null,
            'items' => []
        ];
        if ($user = User::get()) {
            $result['user_id'] = (int)$user['id'];
            $result['user_type'] = $user['user_type'];

            if ($result['user_type'] == 'company') {
                $result['is_main_manager'] = $user['access_level']['is_main_manager'];
                $result['is_manager'] = $user['access_level']['is_manager'];

                $userOrganizations = $user['organizations'];
                $result['items'] = self::processQuery($result['user_id'], ($result['is_main_manager'] || $result['is_manager']), $userOrganizations);
            }
        }
        return $result;
    }

    public static function getByID($arRequest)
    {
        $result = [];

        $userOrganizations = self::getUserOrganizations();
        if ($arRequest['id'] > 0) {
            if(!in_array($arRequest['id'], array_column($userOrganizations['items'], 'id'))) {
                throw new \Exception('Недостаточно прав');
            }

            $query = OrganizationsTable::query()
                ->withSelect()
                ->withID($arRequest['id'])
                ->withOrderCount()
                ->exec()
            ;

            if ($res = $query->fetch()) {
                $parent = null;
                $resParent = OrganizationsTable::query()
                    ->withSelect()
                    ->withID($res['UF_PARENT_ORGANIZATION'])
                    ->exec()
                    ->fetch()
                ;
                if (!empty($res['UF_PARENT_ORGANIZATION'])) {

                    $parent = [
                        'id' => $resParent['ID'],
                        'name' => $resParent['UF_NAME'],
                        'inn' => $resParent['UF_INN'],
                        'ogrn' => $resParent['UF_OGRN'],
                        'kpp' => $resParent['UF_KPP'],
                        'director' => $resParent['UF_DIRECTOR'],
                        'postal_code' => $resParent['UF_POSTAL_CODE'],
                        'address' => $resParent['UF_ADDRESS'],
                    ];
                }

                $result = [
                    'id' => $res['ID'],
                    'name' => $res['UF_NAME'],
                    'inn' => $res['UF_INN'],
                    'ogrn' => $res['UF_OGRN'],
                    'kpp' => $res['UF_KPP'],
                    'director' => $res['UF_DIRECTOR'],
                    'postal_code' => $res['UF_POSTAL_CODE'],
                    'address' => $res['UF_ADDRESS'],
                    'parent' => $parent
                ];
                $result['employees_count'] = UserTable::getList([
                    'filter' => ['UF_ORGANIZATION' => $res['ID']],
                    'count_total' => true
                ])->getCount();
                $result['orders_count'] = (int)$res['ORDERS_COUNT'];
            }
        }
        return $result;
    }

    private static function validateFields($arRequest)
    {
        if (empty($arRequest['name']) || empty($arRequest['director'])) {
            throw new \Exception('Заполните все поля');
        }
        if (strlen($arRequest['inn']) != 10) {
            throw new \Exception('Неправильно заполнен ИНН');
        }
        if (strlen($arRequest['kpp']) != 9) {
            throw new \Exception('Неправильно заполнен КПП');
        }
        if (strlen($arRequest['ogrn']) != 13) {
            throw new \Exception('Неправильно заполнен ОГРН');
        }

        return true;
    }

    public static function add($arRequest)
    {
        $userOrganizations = self::getUserOrganizations();
        if ($userOrganizations['is_main_manager'] || $userOrganizations['is_manager']) {
            if (empty($arRequest['parent']) && $userOrganizations['is_manager']) {
                throw new \Exception('Не указана родительская организация');
            }

            if ($arRequest['parent']) {
                $organizationsIDs = array_column($userOrganizations['items'], 'id');

                if (!in_array($arRequest['parent'], $organizationsIDs)) {
                    throw new \Exception('Родительская организация не найдена');
                }
            }

            if (self::validateFields($arRequest)) {
                $organization = OrganizationsTable::query()
                    ->addFilter('UF_INN', $arRequest['inn'])
                    ->addFilter('UF_KPP', $arRequest['kpp'])
                    ->exec()
                    ->fetch()
                ;
                if (!empty($organization)) {
                    throw new \Exception('Такая организация уже существует');
                }

                return \Legacy\HighLoadBlock\Organizations::add([
                    'UF_NAME' => $arRequest['name'],
                    'UF_INN' => $arRequest['inn'],
                    'UF_OGRN' => $arRequest['ogrn'],
                    'UF_KPP' => $arRequest['kpp'],
                    'UF_DIRECTOR' => $arRequest['director'],
                    'UF_POSTAL_CODE' => $arRequest['postal_code'],
                    'UF_ADDRESS' => $arRequest['address'],
                    'UF_PARENT_ORGANIZATION' => $arRequest['parent'] ?: null,
                    'UF_CREATE_BY' => $userOrganizations['user_id'],
                    'UF_ACTIVE' => true
                ])->getId();
            }
        } else {
            throw new \Exception('У Вас недостаточно прав на создание организации');
        }
        return false;
    }

    public static function update($arRequest)
    {
        $id = (int)$arRequest['id'];
        $userOrganizations = self::getUserOrganizations();

        if ($userOrganizations['is_main_manager'] || $userOrganizations['is_manager'])
        {
            $organizationsIDs = array_column($userOrganizations['items'], 'id');
            if (!in_array($id, $organizationsIDs)) {
                throw new \Exception('У Вас недостаточно прав на изменение организации');
            }

            $organization = OrganizationsTable::query()
                ->withSelect()
                ->withID($arRequest['id'])
                ->exec()
                ->fetch()
            ;
            if ($arRequest['parent'] != $organization['UF_PARENT_ORGANIZATION'] && !in_array($arRequest['parent'], $organizationsIDs)) {
                throw new \Exception('Родительская организация не найдена');
            }
            if ($userOrganizations['is_manager'] &&
                !empty($organization['UF_PARENT_ORGANIZATION']) && empty($arRequest['parent'])) {
                throw new \Exception('Не указана родительская организация');
            }

            if (self::validateFields($arRequest)) {
                return \Legacy\HighLoadBlock\Organizations::update($id, [
                    'UF_NAME' => $arRequest['name'],
                    'UF_INN' => $arRequest['inn'],
                    'UF_OGRN' => $arRequest['ogrn'],
                    'UF_KPP' => $arRequest['kpp'],
                    'UF_DIRECTOR' => $arRequest['director'],
                    'UF_POSTAL_CODE' => $arRequest['postal_code'],
                    'UF_ADDRESS' => $arRequest['address'],
                    'UF_PARENT_ORGANIZATION' => $arRequest['parent'] ?: null
                ])->isSuccess();
            }
        } else {
            throw new \Exception('У Вас недостаточно прав на изменение организации');
        }
        return false;
    }

    public static function delete($arRequest)
    {
        $id = (int)$arRequest['id'];
        $userOrganizations = self::getUserOrganizations();

        if ($userOrganizations['is_main_manager'] || $userOrganizations['is_manager']) {
            $organizationsIDs = array_column($userOrganizations['items'], 'id');

            if ($id > 0 && in_array($id, $organizationsIDs)) {
                $organization = OrganizationsTable::query()
                    ->withSelect()
                    ->withOrderCount()
                    ->withChildOrganizationsCount()
                    ->withID($id)
                    ->exec()
                    ->fetch()
                ;
                if (empty($organization)) {
                    throw new \Exception('Организация не найдена');
                }
                if ($organization['CHILDS_COUNT'] > 0 || $organization['ORDERS_COUNT'] > 0) {
                    throw new \Exception('Невозможно удалить организацию');
                }

                $employeeCount = UserTable::getList([
                    'filter' => ['UF_ORGANIZATION' => $id],
                    'count_total' => true
                ])->getCount();
                if ($employeeCount > 0) {
                    throw new \Exception('Невозможно удалить организацию');
                }

                return \Legacy\HighLoadBlock\Organizations::delete($id)->isSuccess();
            } else {
                throw new \Exception('Организация не найдена');
            }
        } else {
            throw new \Exception('У Вас недостаточно прав на удаление организации');
        }
    }
}