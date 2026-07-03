<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Bitrix\Main\UserTable;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Exception;
use Legacy\General\Validation;
use Legacy\HighLoadBlock\OrganizationsTable;
use Legacy\Main\CLUser;
use Bitrix\Main\IO\File;
use Bitrix\Main\UserPhoneAuthTable;

class User
{
    public static function get()
    {
        $CLUser = new CLUser();

        $result = null;
        if (Loader::includeModule('sale')) {
            if ($CLUser->IsAuthorized()) {
                $arrUser = \CUser::GetByID($CLUser->GetId())->GetNext();

                $id = $arrUser['ID'];

                $user_type = self::getUserGroup($id);

                $second_name = $arrUser['SECOND_NAME'] ? ' ' . $arrUser['SECOND_NAME'] : '';
                $result = [
                    'id' => $id,
                    'fid' => \Bitrix\Sale\Fuser::getId(),
                    'user_type' => $user_type['code'],
                    'FIO' => $arrUser['LAST_NAME'] . ' ' . $arrUser['NAME'] . $second_name,
                    'photo' => $arrUser['PERSONAL_PHOTO']? getFilePath($arrUser['PERSONAL_PHOTO']) : null,
                    'email' => $arrUser['EMAIL'],
                    'phone' => $arrUser['PERSONAL_PHONE'],
                    'manager' => Managers::getManagerByID(['id' => $arrUser['UF_MANAGER_ID']])
                ];

                if($user_type['code'] == 'company') {
                    $organization = OrganizationsTable::query()
                        ->withSelect()
                        ->withID($arrUser['UF_ACTIVE_ORGANIZATION'])
                        ->exec()
                        ->fetch()
                    ;

                    $result += [
                        'organization_name' => htmlspecialchars_decode($organization['UF_NAME']),
                        'inn' => $organization['UF_INN'],
                        'ogrn' => $organization['UF_OGRN'],
                        'kpp' => $organization['UF_KPP'],
                        'director' => $organization['UF_DIRECTOR'],
                        'address' => $organization['UF_ADDRESS'],

                        'access_level' => $user_type['access_level'],
                        'organizations' => $arrUser['UF_ORGANIZATION']
                    ];
                }
            }
        }

        return $result;
    }

    public static function getID()
    {
        $CLUser = new CLUser();

        if ($CLUser->IsAuthorized()) {
            return $CLUser->GetId();
        }

        return null;
    }

    public static function getUserGroups() {
        return UserTable::getUserGroupIds(self::getID());
    }

    public static function getUserGroup($uid)
    {
        $groupIDs = UserTable::getUserGroupIds($uid);

        $result = GroupTable::getList([
            'filter' => [
                'ID' => $groupIDs,
                'CODE' => ['CONTACT', 'COMPANY']
            ],
            'select' => [
                'CODE' => 'STRING_ID'
            ]
        ])->fetch();

        $user_type['code'] = strtolower($result['CODE']);
        if(!$user_type['code']) {
            $user_type = null;
        } elseif($user_type['code'] == 'company') {
            $isMainManager = in_array(Constants::GROUP_ORGANIZATION_MAIN_MANAGER, $groupIDs);
            $isManager = !$isMainManager && in_array(Constants::GROUP_ORGANIZATION_MANAGER, $groupIDs);

            $user_type['access_level'] = [
                'is_main_manager' => $isMainManager,
                'is_manager' => $isManager
            ];
        }
        return $user_type;
    }

    public static function getProfileInfo()
    {
        $CLUser = new CLUser();

        $result = null;
        if (Loader::includeModule('sale')) {
            if ($CLUser->IsAuthorized()) {

                $arrUser = \CUser::GetByID($CLUser->GetId())->GetNext();
                $id = $arrUser['ID'];
                $user_type = self::getUserGroup($id);

                $second_name = $arrUser['SECOND_NAME'] ? ' ' . $arrUser['SECOND_NAME'] : '';

                $result = [
                    'id' => $id,
                    'group' => $user_type['code'],
                    'phone' => $arrUser['PERSONAL_PHONE'],
                    'email' => $arrUser['EMAIL'],
                    'contact_person' => $arrUser['LAST_NAME'] . ' ' . $arrUser['NAME'] . $second_name,
                ];

                if ($user_type['code'] == 'company') {
                    $organization = OrganizationsTable::query()
                        ->withSelect()
                        ->withID($arrUser['UF_ACTIVE_ORGANIZATION'])
                        ->exec()
                        ->fetch()
                    ;

                    $result += [
                        'organization_id' => $organization['ID'],
                        'company' => htmlspecialchars_decode($organization['UF_NAME']),
                        'inn' => $organization['UF_INN'],
                        'ogrn' => $organization['UF_OGRN'],
                        'kpp' => $organization['UF_KPP'],
                        'director' => $organization['UF_DIRECTOR'],
                        'address' => $organization['UF_ADDRESS'],
                    ];
                }
            }
        }

        return $result;
    }

    public static function update($arRequest)
    {
        $CLUser = new CLUser();
        if ($userData = self::get()) {
            $FIO = explode(' ', $arRequest['FIO'] ?? '');
            $surname = $FIO[0] ?? '';
            $name = $FIO[1] ?? '';
            $second_name = $FIO[2] ?? '';
            $email = $arRequest['email'];

            $fields = [
                'NAME' => $name,
                'SECOND_NAME' => $second_name,
                'LAST_NAME' => $surname,
                'EMAIL' => $email,
            ];

            Validation::checkUpdateProfileFields($surname, $name, $email, $userData['email']);

            if($email === null) {
                unset($fields['EMAIL']);
            }

            if ($CLUser->Update($userData['id'], $fields)) {
                return self::get();
            } else {
                throw new \Exception($CLUser->LAST_ERROR);
            }
        }

        throw new Exception('Пользователь не авторизован. Обновите страницу.');
    }

    public static function updatePassword($arRequest)
    {
        $CLUser = new CLUser();
        if ($userData = self::get()) {
            $currenPassword = $arRequest['current_password'];
            $password = $arRequest['password'];
            $passwordConfirm = $arRequest['password_confirm'];
            $fields = [];

            Validation::checkUpdatePasswordFields($userData['id'], $currenPassword, $password, $passwordConfirm);

            $fields['PASSWORD'] = $password;
            $fields['CONFIRM_PASSWORD'] = $passwordConfirm;

            if ($CLUser->Update($userData['id'], $fields)) {
                return self::get();
            } else {
                throw new \Exception($CLUser->LAST_ERROR);
            }
        }

        throw new Exception('Пользователь не авторизован. Обновите страницу.');
    }

    public static function updatePhoto($arRequest)
    {
        $CLUser = new CLUser();

        if ($userData = self::get()) {
            $fields = [];

            $personalPhoto = $arRequest['photo'] ?? null;
            if(!isset($personalPhoto)){
                throw new \Exception('Фото отсутствует');
            }

            if (File::isFileExists($personalPhoto['tmp_name'])) {
                if (strlen($userData['photo']) > 0) {
                    self::deletePhoto($arRequest);
                }
                $arFile = \CFile::MakeFileArray($personalPhoto['tmp_name']);
                $arFile['name'] = $personalPhoto['name'];
                $fileID = \CFile::SaveFile($arFile, 'profilephoto');
                $fields += [
                    'PERSONAL_PHOTO' => \CFile::MakeFileArray($fileID),
                ];
            }

            if ($CLUser->Update($userData['id'], $fields)) {
                return self::get();
            } else {
                throw new \Exception($CLUser->LAST_ERROR);
            }
        }

        throw new Exception('Пользователь не авторизован. Обновите страницу.');
    }

    public static function deletePhoto($arRequest) {
        if ($userData = self::get()) {
            if (strlen($userData['photo']) > 0)  {
                $photoId = \CUser::GetList(['ID' => 'ASC'], null, ['ID' => $userData['id']], ['FIELDS' => ['PERSONAL_PHOTO']])->fetch()['PERSONAL_PHOTO'];
                \CFile::Delete($photoId);
            }

            return self::get();
        }

        throw new Exception('Пользователь не авторизован. Обновите страницу.');
    }

    public static function updateOrganization($arRequest)
    {
        $CLUser = new CLUser();

        $userOrganizations = Organizations::getUserOrganizations();
        if (!is_null($userOrganizations['user_type'])) {
            if ($userOrganizations['user_type'] == 'company') {
                $organizationIDs = array_column($userOrganizations['items'], 'id');

                if (in_array($arRequest['organization_id'], $organizationIDs)) {
                    $fields = [
                        'UF_ACTIVE_ORGANIZATION' => $arRequest['organization_id']
                    ];

                    if ($CLUser->Update($userOrganizations['user_id'], $fields)) {
                        return self::get();
                    } else {
                        throw new \Exception($CLUser->LAST_ERROR);
                    }
                } else {
                    throw new \Exception('Организация не найдена');
                }
            } else {
                throw new \Exception('Физическому лицу недоступно изменение организации.');
            }
        }
        throw new \Exception('Произошла неизвестная ошибка.');
    }

    public static function updateEmail($arRequest)
    {
        if ($userData = self::get()) {
            $email = $arRequest['email'];
            if (empty($email)){
                throw new \Exception('Почта не может быть пустой.');
            }

            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            if ($email === false) {
                throw new \Exception('Не верный формат email.');
            }

            $user = UserTable::getRow([
                'select' => [
                    'LOGIN',
                ],
                'filter' => [
                    'EMAIL' => $email,
                    'ACTIVE' => 'Y',
                ]
            ]);

            if($user){
                throw new \Exception('Пользователь с таким email ('.$email.') уже существует.');
            }

            return Auth::sendCodeAssignNew($arRequest);
        }

        throw new Exception('Пользователь не авторизован. Обновите страницу.');
    }

    public static function updatePhone($arRequest)
    {
        if ($userData = self::get()) {
            $phone = UserPhoneAuthTable::normalizePhoneNumber($arRequest['phone']);
            Validation::checkUpdatePhoneFields($phone, $userData['phone']);

            return Auth::sendCodeAssignNew(['login' => $phone, 'login_type' => 'phone']);
        }

        throw new Exception('Пользователь не авторизован. Обновите страницу.');
    }
}
