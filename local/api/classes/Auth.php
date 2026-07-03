<?php

namespace Legacy\API;

use Legacy\General\Validation;
use Legacy\HighLoadBlock\Entity;
use Legacy\Main\CLUser;
use Legacy\General\Constants;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Random;
use Bitrix\Main;
use Bitrix\Main\UserTable;
use Legacy\Sale\Basket as LSBasket;
use Bitrix\Main\UserPhoneAuthTable;
use Legacy\HighLoadBlock\Organizations;
use Legacy\General\SmsRu;
use Bitrix\Main\Config\Option;

class Auth
{
    protected static $MODE_REGISTER = 1;
    protected static $MODE_FORGOT = 2;
    protected static $MODE_ASSIGN_NEW = 3;
    protected static $MODE_EMPLOYEE_REGISTER = 4;
    public static $MODE_REGISTRATION_IN_ORDER = 5;

    public static function login($arRequest)
    {
        $loginType = $arRequest['login_type'] ?? 'email';
        $contact = $arRequest['login'];
        if ($loginType === 'phone') {
            $contact = UserPhoneAuthTable::normalizePhoneNumber($contact);
        }
        $password = $arRequest['password'];
        Validation::checkLoginFields($loginType, $contact, $password);

        $bitrixBasketItems = LSBasket::loadItems()->getBasket()->getBasketItems();
        $favouriteIDs = Favourite::getIDs();

        $LUSER = new CLUser;
        $LUSER->Login($contact, $password,'N', 'Y', $loginType);

        Basket::setMainBasketLogin($bitrixBasketItems);
        Favourite::mergeIDs($favouriteIDs);

        return User::get();
    }

    public static function registration($arRequest)
    {
        $_SESSION['LEGACY_GET_CODE_MODE'] = self::$MODE_REGISTER;

        $userType = $arRequest['user_type'] ?? 'contact';

        $email = $arRequest['email'];
        $phone = UserPhoneAuthTable::normalizePhoneNumber($arRequest['phone']);

        $password = $arRequest['password'];
        $passwordConfirm = $arRequest['password_confirm'];

        $skipConfirm = $arRequest['skip_confirm'] && $_SESSION['ORDER_REGISTRATION'] === self::$MODE_REGISTRATION_IN_ORDER;
        $needAuthorize = $arRequest['need_authorize'] && $_SESSION['ORDER_REGISTRATION'] === self::$MODE_REGISTRATION_IN_ORDER;

        $FIO = explode(' ', $arRequest['FIO'] ?? '');
        $surname = $FIO[0] ?? '';
        $name = $FIO[1] ?? '';
        $second_name = $FIO[2] ?? '';

        $inn = $arRequest['inn'] ?? '';
        $ogrn = $arRequest['ogrn'] ?? '';
        $kpp = $arRequest['kpp'] ?? '';
        $organizationName = $arRequest['organization_name'] ?? '';
        $organizationPostalCode = $arRequest['organization_postal_code'] ?? '';
        $organizationAddress = $arRequest['organization_address'] ?? '';

        $organizationID = null;

        Validation::checkRegistrationFields(
            $userType,
            $email,
            $phone,
            $password,
            $passwordConfirm,
            $name,
            $surname,
            $inn,
            $ogrn,
            $kpp,
            $organizationName,
            $organizationPostalCode,
            $organizationAddress
        );

        $managerId = Managers::getManagerID();

        $group = [constant('Legacy\General\Constants::GROUP_'.strtoupper($userType))];
        if ($group[0] == Constants::GROUP_COMPANY) {
            $group[] = Constants::GROUP_ORGANIZATION_MANAGER;

            $organizationID = self::createOrganization($arRequest);
        }

        $USER = new CLUser;
        $arRegisterResult = $USER->Register(
            $email,
            $name,
            $surname,
            $password,
            $passwordConfirm,
            $email,
            false,
            '',
            0,
            $skipConfirm,
            $phone,
            $organizationID,
            $second_name,
            $group,
            $managerId,
            $needAuthorize
        );

        if (is_array($arRegisterResult) && $arRegisterResult['TYPE'] == 'ERROR') {
            $userUpdated = $USER->isUpdatedNotActiveRegistration($email,
                $email, $phone, $name, $second_name, $surname, $password, $passwordConfirm, $organizationID, $group, $userType
            );
            if ($userUpdated) {
                self::sendCode(['login' => $email, 'login_type' => 'email']);
                return true;
            } else {
                $errMessage = is_array($arRegisterResult['MESSAGE'])
                    ? implode('. ', $arRegisterResult['MESSAGE'])
                    : $arRegisterResult['MESSAGE'];
                if ($organizationID) {
                    Organizations::delete($organizationID);
                }
                throw new \Exception($errMessage);
            }
        }

        return true;
    }
    public static function orderRegistration($arRequest, $loginType = 'email')
    {
        if($_SESSION['ORDER_REGISTRATION'] !== self::$MODE_REGISTRATION_IN_ORDER) {
            throw new \Exception('Регистрация пользователя невозможна. Обновите страницу.');
        }

        $password = Random::getString(12, true);


        $user_data = [
            'user_type' => $arRequest['user_type'],
            ...$arRequest['order_properties'],
            'FIO' => $arRequest['order_properties']['contact_person'],
            'password' => $password,
            'password_confirm' => $password,
            'skip_confirm' => true,
            'need_authorize' => true,
        ];

        if(self::registration($user_data)){
            if ($loginType === 'email'){
                $arEventFields = [
                    'SITE_ID' => SITE_ID,
                    'EMAIL' => $arRequest['email'],
                    'PASSWORD' => $password
                ];

                $event = new \CEvent;
                $event->Send('ORDER_REGISTRATION', $arEventFields['SITE_ID'], $arEventFields);
            } else {
//                $smsTemplate = 'SMS_USER_SEND_PASSWORD';
//                $sms = new SmsRu($smsTemplate, [
//                    'PHONE' => $arRequest['phone'],
//                    'PASSWORD' => $password,
//                ]);
//                $sms->send();
            }

            if($user = self::loginWithCreateMainBasket()) {
                return $user;
            } else {
                throw new \Exception('Не удалось создать основную корзину.');
            }
        }

        throw new \Exception('Не удалось зарегистрировать пользователя');
    }

    public static function registerEmployee($arRequest)
    {
        $_SESSION['LEGACY_GET_CODE_MODE'] = self::$MODE_EMPLOYEE_REGISTER;

        $user = UserTable::getRow([
            'filter' => ['EMAIL' => $arRequest['email']]
        ]);
        if (empty($user)) {
            throw new \Exception('Пользователь не найден');
        } elseif ($user['ACTIVE'] == 'Y') {
            throw new \Exception('Вы уже зарегистрировались');
        }

        if (empty($arRequest['password']) || empty($arRequest['password_confirm'])) {
            throw new \Exception('Заполните все поля');
        }

        if ($arRequest['password'] != $arRequest['password_confirm']) {
            throw new \Exception('Пароли не совпадают.');
        }
        return self::checkCode($arRequest);
    }

    public static function sendCodeForgotPassword($arRequest)
    {
        $loginType = $arRequest['login_type'] ?? 'email';
        $contact = $arRequest['login'];
        if ($loginType === 'phone') {
            $contact = UserPhoneAuthTable::normalizePhoneNumber($contact);
        }

        if (empty($contact)) {
            throw new \Exception(json_encode(['login' => 'Поле с контактными данными не может быть пустым.']));
        }

        if($loginType === 'email') {
            $field = 'EMAIL';
        } else {
            $field = 'LOGIN';
        }

        $user = UserTable::getRow([
            'select' => [
                'LOGIN',
            ],
            'filter' => [$field => $contact]
        ]);
        if (!$user) {
            if($loginType === 'email') {
                throw new \Exception(json_encode(['login' => 'Почта не найдена, воспользуйтесь восстановлением пароля по номеру телефона']));
            } else {
                throw new \Exception(json_encode(['login' => 'Пользователь с таким номером телефона не найден.']));
            }
        }

        $_SESSION['LEGACY_GET_CODE_MODE'] = self::$MODE_FORGOT;
        if(self::sendCode($arRequest)){
            return true;
        } else {
            throw new \Error('Неизвестная ошибка.');
        }
    }

    public static function sendCodeAssignNew($arRequest)
    {
        $_SESSION['LEGACY_GET_CODE_MODE'] = self::$MODE_ASSIGN_NEW;

        if (self::sendCode($arRequest)){
            return true;
        } else {
            throw new \Error('Неизвестная ошибка.');
        }
    }

    public static function sendCode($arRequest)
    {
        if (empty($_SESSION['LEGACY_GET_CODE_MODE'])) {
            throw new \Exception('Неизвестная ошибка.');
        }

        $loginType = $arRequest['login_type'] ?? 'email';
        $contact = $arRequest['login'];
        if ($loginType === 'phone') {
            $contact = UserPhoneAuthTable::normalizePhoneNumber($contact);
        }

        if (empty($contact)) {
            throw new \Exception(json_encode(['login' => 'Поле с контактными данными не может быть пустым.']));
        }

        //достаем из хайдлоадблока запись об отправке кода
        $arElementFields = [
            'UF_CONTACT' => $contact,
            'UF_CONFIRMED' => 'N',
        ];
        $params = [
            'filter' => $arElementFields,
            'order' => ['ID'=>'DESC'],
        ];
        $previousConfirmation = Entity::getInstance()->getRow(Constants::HLBLOCK_CONFIRMATION_CODES, $params);
        if($previousConfirmation){
            $currentDateTime = new DateTime();
            if(($currentDateTime->getTimestamp() - $previousConfirmation['UF_DATETIME_SEND']) < CLUser::REFRESHC_CODE_AFTER_TIME_IN_SECONDS)
            {
                throw new \Exception('Вы запрашиваете код слишком часто. Запросите код позже.');
            }
        }

        $userId = $previousConfirmation['UF_USER_ID'] ?? null;
        $USER = new CLUser;
        if ($_SESSION['LEGACY_GET_CODE_MODE'] === self::$MODE_ASSIGN_NEW) {
            $userId = $USER->getId();
        }

        if ($_SESSION['LEGACY_GET_CODE_MODE'] === self::$MODE_FORGOT) {
            if ($loginType === 'email') {
                $field = 'EMAIL';
            } else {
                $field = 'LOGIN';
            }

            $user = UserTable::getRow([
                'select' => [
                    'ID',
                ],
                'filter' => [$field => $contact]
            ]);

            $userId = $user['ID'];
        }

        $code = $USER::GenerateCode($contact, $userId);
        if ($loginType === 'email'){
            if ($_SESSION['LEGACY_GET_CODE_MODE'] == self::$MODE_FORGOT) {
                $emailTemplate = 'EMAIL_USER_RESTORE_PASSWORD';
            } elseif ($_SESSION['LEGACY_GET_CODE_MODE'] == self::$MODE_ASSIGN_NEW) {
                $emailTemplate = 'NEW_EMAIL_CONFIRM';
            } else {
                $emailTemplate = 'NEW_USER_CONFIRM';
            }

            $arEventFields = [
                'SITE_ID' => SITE_ID,
                'EMAIL' => $contact,
                //создаем код в хайлоадблоке и складываем код в массив для событий
                'CONFIRM_CODE' => $code
            ];

            $event = new \CEvent;
            $event->Send($emailTemplate, $arEventFields['SITE_ID'], $arEventFields);
        } else {
//            if ($_SESSION['LEGACY_GET_CODE_MODE'] == self::$MODE_FORGOT) {
//                $smsTemplate = 'SMS_USER_RESTORE_PASSWORD';
//            } elseif ($_SESSION['LEGACY_GET_CODE_MODE'] == self::$MODE_REGISTER) {
//                $smsTemplate = 'SMS_NEW_USER_CONFIRM_NUMBER';
//            } else {
//                $smsTemplate = 'SMS_USER_CONFIRM_NUMBER';
//            }
//
//            $sms = new SmsRu($smsTemplate, [
//                'PHONE' => $contact,
//                'CODE' => $code,
//            ]);
//
//            $obResult = $sms->send();
//            if (!$obResult->isSuccess()) {
//                throw new \Exception(implode('. ', $obResult->getErrorMessages()));
//            }
        }

        return true;
    }

    public static function checkCode($arRequest)
    {
        if (empty($_SESSION['LEGACY_GET_CODE_MODE'])) {
            throw new \Exception('Неизвестная ошибка.');
        }

        $loginType = $arRequest['login_type'] ?? 'email';
        $contact = $arRequest['login'];
        if ($loginType === 'phone') {
            $contact = UserPhoneAuthTable::normalizePhoneNumber($contact);
        }

        if (empty($contact)) {
            throw new \Exception(json_encode(['login' => 'Поле с контактными данными не может быть пустым.']));
        }

        $code = $arRequest['code'];
        if (empty($code)) {
            throw new \Exception(json_encode(['code' => 'Введите код подтверждения.']));
        }

        $USER = new CLUser;
        $id = $USER::VerifyCode($contact, $code);
        if ($id) {
            switch ($_SESSION['LEGACY_GET_CODE_MODE']) {
                case self::$MODE_REGISTER:
                    $userId = intval($id);

                    $USER->Update($userId, ['ACTIVE' => 'Y']);
                    if ($error = $USER->LAST_ERROR) {
                        throw new \Exception($error);
                    }

                    $userOrganization = UserTable::getRow([
                        'select' => ['UF_ACTIVE_ORGANIZATION'],
                        'filter' => ['ID' => $id]
                    ])['UF_ACTIVE_ORGANIZATION'];
                    if($userOrganization) {
                        Organizations::update($userOrganization, [
                            'UF_ACTIVE' => true,
                            'UF_CREATE_BY' => $id
                        ]);
                    }

                    self::createMainBasket(['user_id' => $userId]);

                    $_SESSION['LEGACY_GET_CODE_MODE'] = null;
                    break;

                case self::$MODE_EMPLOYEE_REGISTER:
                    $userId = intval($id);

                    $USER->Update($userId, [
                        'ACTIVE' => 'Y',
                        'PASSWORD' => $arRequest['password'],
                        'CONFIRM_PASSWORD' => $arRequest['password_confirm'],
                    ]);
                    if ($error = $USER->LAST_ERROR) {
                        throw new \Exception($error);
                    }

                    self::createMainBasket(['user_id' => $userId]);
                    $_SESSION['LEGACY_GET_CODE_MODE'] = null;
                    break;

                case self::$MODE_FORGOT:
                    $_SESSION['LEGACY_CONFIRMED_CONTACT'] = $contact;
                    break;

                case self::$MODE_ASSIGN_NEW:
                    $userId = intval($id);
                    if ($loginType === 'phone') {
                        $USER->Update($userId, [
                            'PHONE_NUMBER' => $contact,
                            'PERSONAL_PHONE' => $contact,
                            'LOGIN' => $contact
                        ]);
                    } {
                        $USER->Update($userId, [
                            'EMAIL' => $contact,
                            'LOGIN' => $contact
                        ]);
                    }
                    if ($USER->LAST_ERROR) {
                        throw new \Exception($USER->LAST_ERROR);
                    }

                    $_SESSION['LEGACY_GET_CODE_MODE'] = null;
                    break;
            }
            return true;
        }
        throw new \Exception('Непредвиденная ошибка');
    }

    public static function changePassword($arRequest)
    {
        $loginType = $arRequest['login_type'] ?? 'email';
        $contact = $arRequest['login'];
        if ($loginType === 'phone') {
            $contact = UserPhoneAuthTable::normalizePhoneNumber($contact);
        }

        $password = $arRequest['password'];
        $passwordConfirm = $arRequest['password_confirm'];

        if (empty($contact)) {
            throw new \Exception('Контактная информация не может быть пустой.');
        }

        if($_SESSION['LEGACY_CONFIRMED_CONTACT'] != $contact) {
            throw new \Exception('Подтвержденная контактная информация не совпадает с переданной.');
        }
        if($_SESSION['LEGACY_GET_CODE_MODE'] != self::$MODE_FORGOT) {
            throw new \Exception('Истек срок давности подтверждения контактной информации. Запросите новый код подтверждения.');
        }

        $_SESSION['LEGACY_GET_CODE_MODE'] = null;
        $_SESSION['LEGACY_CONFIRMED_CONTACT'] = null;

        if($loginType === 'email') {
            $field = 'EMAIL';
        } else {
            $field = 'LOGIN';
        }

        $arUser = UserTable::getRow([
            'filter' => [$field => $contact]
        ]);

        if ($arUser && $arUser['ACTIVE'] == 'Y') {
            $user = new CLUser;
            $user->Update($arUser['ID'], [
                'PASSWORD' => $password,
                'CONFIRM_PASSWORD' => $passwordConfirm
            ]);
            if ($user->LAST_ERROR) {
                throw new \Exception($user->LAST_ERROR);
            }

            return true;
        } else {
            throw new \Exception('Пользователь не найден.');
        }
    }

    public static function logout() {
        $LUSER = new CLUser;
        Basket::unsetCurrentBasket();
        $LUSER->Logout();
    }

    private static function createMainBasket($arRequest) {
        $userID = $arRequest['user_id'];

        $params = [
            'filter' => [
                'UF_USER' => $userID
            ],
            'order' => [
                'ID' => 'ASC',
            ],
        ];
        $multiBaskets = Entity::getInstance()->getList(Constants::HLBLOCK_MULTI_BASKET, $params);

        if (!$multiBaskets){
            $params = [
                'UF_USER' => $userID,
                'UF_NAME' => 'Основная корзина',
                'UF_DESCRIPTION' => '',
                'UF_MAIN' => 1,
                'UF_COLOR' => 'secondary'
            ];
            if (Entity::getInstance()->add(Constants::HLBLOCK_MULTI_BASKET, $params))
            {
                return true;
            } else {
                return false;
            }
        }
    }

    private static function createOrganization($arRequest) {
        $active = $arRequest['skip_confirm'] && $_SESSION['ORDER_REGISTRATION'] === self::$MODE_REGISTRATION_IN_ORDER;
        return Organizations::add([
            'UF_NAME' => $arRequest['organization_name'],
            'UF_INN' => $arRequest['inn'],
            'UF_OGRN' => $arRequest['orgn'],
            'UF_KPP' => $arRequest['kpp'],
            'UF_DIRECTOR' => $arRequest['director'],
            'UF_ADDRESS' => $arRequest['organization_address'],
            'UF_POSTAL_CODE' => $arRequest['organization_postal_code'],
            'UF_PARENT_ORGANIZATION' => null,
            'UF_ACTIVE' => $active
        ])->getId();
    }

    private static function loginWithCreateMainBasket()
    {
        $bitrixBasketItems = LSBasket::loadItems()->getBasket()->getBasketItems();
        $favouriteIDs = Favourite::getIDs();

        $LUSER = new CLUser;

        self::createMainBasket(['user_id' => $LUSER->getId()]);
        Basket::setMainBasketLogin($bitrixBasketItems);
        Favourite::mergeIDs($favouriteIDs);

        return User::get();
    }
}
