<?php

namespace Legacy\API;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Legacy\General\Constants;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\UserTable;
use Legacy\HighLoadBlock\Entity;
use Legacy\HighLoadBlock\OrganizationsTable;
use Legacy\Main\CLUser;

class Employees
{
    public static function getList($arRequest)
    {
        $result = [
            'count' => 0,
            'items' => []
        ];

        $userOrganizations = Organizations::getUserOrganizations();
        if ($userOrganizations['is_main_manager'] || $userOrganizations['is_manager'])
        {
            $organizationIDs = array_column($userOrganizations['items'], 'id');
            $params = [
                'select' => [
                    '*',
                    'UF_ORGANIZATION',
                    new ExpressionField('GROUP_IDS', 'GROUP_CONCAT(%s)', ['GROUPS.GROUP_ID']),
                ],
                'order' => ['ID' => 'DESC'],
                'count_total' => true,
            ];
            if ($arRequest['organization']) {
                if (in_array($arRequest['organization'], $organizationIDs)) {
                    $params['filter']['UF_ORGANIZATION'] = $arRequest['organization'];
                } else {
                    return $result;
                }
            } else {
                $params['filter']['UF_ORGANIZATION'] = $organizationIDs;
            }
            if ($arRequest['limit']) {
                $params['limit'] = (int)$arRequest['limit'];

                if ($arRequest['page'] > 1) {
                    $params['offset'] = ((int)$arRequest['page'] - 1) * $params['limit'];
                }
            }

            $db = UserTable::getList($params);
            $result['count'] = $db->getCount();
            while ($res = $db->fetch()) {
                $organizations = [];
                foreach ($res['UF_ORGANIZATION'] as $id) {
                    $orgID = array_search($id, $organizationIDs);
                    if (is_numeric($orgID)) {
                        $organizations[] = $userOrganizations['items'][$orgID];
                    }
                }

                $groups = explode(',', $res['GROUP_IDS']);

                $result['items'][] = [
                    'id' => $res['ID'],
                    'active' => $res['ACTIVE'],
                    'blocked' => $res['BLOCKED'],
                    'fio' => implode(' ', [$res['LAST_NAME'], $res['NAME'], $res['SECOND_NAME']]),
                    'position' => $res['WORK_POSITION'],
                    'email' => $res['EMAIL'],
                    'phone' => $res['PERSONAL_PHONE'],
                    'organizations' => $organizations,
                    'is_main_manager' => in_array(Constants::GROUP_ORGANIZATION_MAIN_MANAGER, $groups),
                    'is_manager' => in_array(Constants::GROUP_ORGANIZATION_MANAGER, $groups),
                ];
            }
        }
        return $result;
    }

    public static function getByID($arRequest)
    {
        $result = [];

        $userOrganizations = Organizations::getUserOrganizations();
        if (($userOrganizations['is_main_manager'] || $userOrganizations['is_manager']))
        {
            if ($arRequest['id'] > 0) {
                $organizationIDs = array_column($userOrganizations['items'], 'id');
                $res = UserTable::getRow([
                    'filter' => [
                        'ID' => $arRequest['id'],
                        'UF_ORGANIZATION' => $organizationIDs
                    ],
                    'select' => [
                        '*',
                        new ExpressionField('GROUP_IDS', 'GROUP_CONCAT(%s)', ['GROUPS.GROUP_ID']),
                        'UF_ORGANIZATION'
                    ]
                ]);

                if (!empty($res)) {
                    $organizations = [];
                    foreach ($userOrganizations['items'] as &$org) {
                        $org['chosed'] = false;
                        if (in_array($org['id'], $res['UF_ORGANIZATION'])) {
                            $org['chosed'] = true;
                        }
                        $organizations[] = $org;
                    }
                    $groups = explode(',', $res['GROUP_IDS']);

                    $result = [
                        'id' => $res['ID'],
                        'active' => $res['ACTIVE'],
                        'blocked' => $res['BLOCKED'],
                        'fio' => implode(' ', [$res['LAST_NAME'], $res['NAME'], $res['SECOND_NAME']]),
                        'position' => $res['WORK_POSITION'],
                        'email' => $res['EMAIL'],
                        'phone' => $res['PERSONAL_PHONE'],
                        'organizations' => $organizations,
                        'is_main_manager' => in_array(Constants::GROUP_ORGANIZATION_MAIN_MANAGER, $groups),
                        'is_manager' => in_array(Constants::GROUP_ORGANIZATION_MANAGER, $groups),
                    ];
                }
            }
        } else {
            throw new \Exception('Недостаточно прав');
        }
        return $result;
    }

    private static function validateFields($arRequest)
    {
        if (empty($arRequest['organizations'])) {
            throw new \Exception('Не выбрана организация');
        }
        if (empty($arRequest['position'])) {
            throw new \Exception('Не указана должность');
        }
        if (empty($arRequest['phone'])) {
            throw new \Exception('Телефон не может быть пустым');
        }

        if (empty($arRequest['fio'][0]) || empty($arRequest['fio'][1])) {
            throw new \Exception('Неверный формат ФИО');
        }

        $email = filter_var($arRequest['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new \Exception('Неверный формат email');
        }

        return true;
    }

    public static function add($arRequest)
    {
        $userOrganizations = Organizations::getUserOrganizations();
        if ($userOrganizations['is_main_manager'] || $userOrganizations['is_manager']) {
            $organizations = array_values(array_intersect($arRequest['organizations'], array_column($userOrganizations['items'], 'id')));
            if (empty($organizations)) {
                throw new \Exception('Организации не найдены');
            }

            $arRequest['fio'] = explode(' ', $arRequest['fio']);
            if (self::validateFields($arRequest)) {
                $groups = [Constants::GROUP_COMPANY];
                if ($arRequest['is_manager']) {
                    $groups[] = Constants::GROUP_ORGANIZATION_MANAGER;
                }

                $phone = Main\UserPhoneAuthTable::normalizePhoneNumber($arRequest['phone']);
                $pwd = \CUser::GeneratePasswordByPolicy([Constants::GROUP_RATING_VOTE]);

                $arFields = [
                    'NAME' =>  $arRequest['fio'][1],
                    'LAST_NAME' =>  $arRequest['fio'][0],
                    'SECOND_NAME' =>  $arRequest['fio'][2] ?: null,

                    'LOGIN' => $arRequest['email'],
                    'EMAIL' => $arRequest['email'],
                    'PHONE_NUMBER' => $phone,
                    'PERSONAL_PHONE' => $phone,

                    'PASSWORD' => $pwd,
                    'CONFIRM_PASSWORD' => $pwd,

                    'UF_ORGANIZATION' => $organizations,
                    'UF_ACTIVE_ORGANIZATION' => $organizations[0],
                    'WORK_POSITION' => $arRequest['position'],
                    'GROUP_ID' => $groups,
                    'UF_MANAGER_ID' => Managers::getManagerID(),

                    'ACTIVE' => 'N'
                ];

                $user = new \CUser;
                $uid = $user->add($arFields);
                if ((int)$uid > 0) {
                    $arEventFields = [
                        'SITE_ID' => SITE_ID,
                        'EMAIL' => $arRequest['email'],
                        'EMAIL_CODE' => CLUser::GenerateCode($arRequest['email'], $uid)
                    ];

                    $event = new \CEvent;
                    $event->Send('NEW_EMPLOYEE_CONFIRM', $arEventFields["SITE_ID"], $arEventFields);

                    return true;
                } else {
                    throw new \Exception($user->LAST_ERROR);
                }
            }
            return false;
        } else {
            throw new \Exception('Недостаточно прав');
        }
    }

    public static function update($arRequest)
    {
        $userOrganizations = Organizations::getUserOrganizations();
        if ($userOrganizations['is_main_manager'] || $userOrganizations['is_manager']) {
            if (empty($arRequest['position'])) {
                throw new \Exception('Укажите должность');
            }

            $userOrganizationsIDs = array_column($userOrganizations['items'], 'id');
            $employee = UserTable::getRow([
                'filter' => [
                    'ID' => $arRequest['id'],
                    'UF_ORGANIZATION' => $userOrganizationsIDs
                ],
                'select' => [
                    '*',
                    new ExpressionField('GROUP_IDS', 'GROUP_CONCAT(%s)', ['GROUPS.GROUP_ID']),
                    'UF_ORGANIZATION'
                ]
            ]);
            if (!empty($employee)) {
                $updateOrganizations = array_diff($employee['UF_ORGANIZATION'], $userOrganizationsIDs);
                if (!empty($arRequest['organizations'])) {
                    $updateOrganizations = array_merge($updateOrganizations, array_intersect($arRequest['organizations'], $userOrganizationsIDs));
                }

                $fields = [
                    'BLOCKED' => $arRequest['is_active'] ? 'N' : 'Y',
                    'WORK_POSITION' => $arRequest['position'],
                    'UF_ORGANIZATION' => $updateOrganizations,
                ];

                $employee['GROUP_IDS'] = explode(',', $employee['GROUP_IDS']);
                $is_manager = in_array(Constants::GROUP_ORGANIZATION_MANAGER, $employee['GROUP_IDS']);
                if ($arRequest['is_manager'] && !$is_manager) {
                    $employee['GROUP_IDS'][] = Constants::GROUP_ORGANIZATION_MANAGER;
                }
                elseif (!$arRequest['is_manager'] && $is_manager) {
                    $index = array_search(Constants::GROUP_ORGANIZATION_MANAGER, $employee['GROUP_IDS']);
                    unset($employee['GROUP_IDS'][$index]);
                }
                $fields['GROUP_ID'] = $employee['GROUP_IDS'];

                $LUSER = new CLUser;
                if ($LUSER->Update($employee['ID'], $fields)) {
                    return true;
                } else {
                    throw new \Exception($LUSER->LAST_ERROR);
                }
            } else {
                throw new \Exception('Пользователь не найден');
            }
        } else {
            throw new \Exception('Недостаточно прав');
        }
    }
}
