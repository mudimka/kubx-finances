<?php
namespace Legacy\General;

use Legacy\Main\CLUser;
class Validation
{
    public static function checkRegistrationFields(
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
    )
    {
        $errors = [];

        if (empty($userType)) {
            $errors['user_type'] = 'Не выбран тип пользователя.';
        } elseif (!defined('Legacy\General\Constants::GROUP_' . strtoupper($userType))){
            $errors['user_type'] = 'Неверный тип пользователя.';
        }

        if (empty($email)) {
            $errors['email'] = 'Введите email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректный email.';
        } elseif (CLUser::isEmailRegistered($email)) {
            $errors['email'] = 'Этот email уже зарегистрирован.';
        }

        if (empty($name . $surname)) {
            $errors['fio'] = 'Введите ваше ФИО.';
        }
//        elseif (!$name || !$surname  || strlen($name) < 2 || strlen($surname) < 2) {
//            $errors['FIO'] = 'Неверный формат ФИО.';
//        }

        if (empty($phone)) {
            $errors['phone'] = 'Введите номер телефона.';
        } elseif (!preg_match('/^\+\d{11}$/', $phone)) {
            $errors['phone'] = 'Введите корректный номер телефона.';
        } elseif (CLUser::isPhoneNumberRegistered($phone)) {
            $errors['phone'] = 'Этот номер телефона уже зарегистрирован.';
        }

        if (empty($password)) {
            $errors['password'] = 'Введите пароль.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Пароль должен содержать не менее 8 символов.';
        } elseif (strlen($password) > 32) {
            $errors['password'] = 'Пароль не должен превышать 32 символа.';
        } elseif (empty($passwordConfirm)) {
            $errors['password_confirm'] = 'Подтвердите пароль.';
        } elseif ($password != $passwordConfirm) {
            $errors['password'] = 'Пароли не совпадают.';
            $errors['password_confirm'] = 'Пароли не совпадают.';
        }

        if (defined('Legacy\General\Constants::GROUP_' . strtoupper($userType))
            && constant('Legacy\General\Constants::GROUP_' . strtoupper($userType)) == Constants::GROUP_COMPANY) {
            if (!$inn) {
                $errors['inn'] = 'ИНН организации не может быть пустым.';
            }

            if (!$ogrn) {
                $errors['ogrn'] = 'ОГРН организации не может быть пустым.';
            }

//            if (!$kpp) {
//                $errors['kpp'] = 'КПП организации не может быть пустым.';
//            }

            if (!$organizationName) {
                $errors['organization_name'] = 'Название организации не может быть пустым.';
            }

            if (!$organizationPostalCode) {
                $errors['organization_postal_code'] = 'Индекс организации не может быть пустым.';
            }

            if (!$organizationAddress) {
                $errors['organization_address'] = 'Адрес организации не может быть пустым.';
            }
        }

        if (count($errors) > 0) {
            $errMessage = json_encode($errors);
            throw new \Exception($errMessage);
        }
    }

    public static function checkLoginFields(
        $loginType,
        $login,
        $password,
    )
    {
        $errors = [];

        if($loginType === 'email') {
            if (empty($login)) {
                $errors['login'] = 'Введите email.';
            } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $errors['login'] = 'Укажите корректный email.';
            } elseif (!CLUser::isEmailRegistered($login)) {
                $errors['login'] = 'Пользователь с таким email не найден.';
            }
        }

        if($loginType === 'phone') {
            if (empty($login)) {
                $errors['login'] = 'Введите номер телефона.';
            } elseif (!preg_match('/^\+\d{11}$/', $login)) {
                $errors['login'] = 'Введите корректный номер телефона.';
            } elseif (!CLUser::isPhoneNumberRegistered($login)) {
                $errors['login'] = 'Пользователь с таким номером телефона не найден.';
            }
        }

        if (empty($password)) {
            $errors['password'] = 'Введите пароль.';
        }

        if (count($errors) > 0) {
            $errMessage = json_encode($errors);
            throw new \Exception($errMessage);
        }
    }

    public static function checkUpdatePhoneFields(
        $phone,
        $currentPhone
    )
    {
        $errors = [];

        if (empty($phone)) {
            $errors['phone'] = 'Введите номер телефона.';
        } elseif (!preg_match('/^\+\d{11}$/', $phone)) {
            $errors['phone'] = 'Введите корректный номер телефона.';
        } elseif ($phone === $currentPhone) {
            $errors['phone'] = 'Новый номер телефона соответствует старому.';
        } elseif (CLUser::isPhoneNumberRegistered($phone)) {
            $errors['phone'] = 'Этот номер телефона уже зарегистрирован.';
        }

        if (count($errors) > 0) {
            $errMessage = json_encode($errors);
            throw new \Exception($errMessage);
        }
    }

    public static function checkUpdatePasswordFields(
        $userId,
        $currenPassword,
        $password,
        $passwordConfirm
    )
    {
        $errors = [];

        $userData = \CUser::GetByID($userId)->Fetch();

        if(!\Bitrix\Main\Security\Password::equals($userData['PASSWORD'], $currenPassword)) {
            $errors['current_password'] = 'Неверный пароль.';
        } elseif (empty($password)) {
            $errors['password'] = 'Введите пароль.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Пароль должен содержать не менее 8 символов.';
        } elseif (strlen($password) > 32) {
            $errors['password'] = 'Пароль не должен превышать 32 символа.';
        } elseif (empty($passwordConfirm)) {
            $errors['password_confirm'] = 'Введите подтверждение пароля.';
        } elseif ($password != $passwordConfirm) {
            $errors['password'] = 'Пароли не совпадают.';
            $errors['password_confirm'] = 'Пароли не совпадают.';
        } elseif ($currenPassword == $password) {
            $errors['password'] = 'Пароль не должен совпадать с текущим.';
        }

        if (count($errors) > 0) {
            $errMessage = json_encode($errors);
            throw new \Exception($errMessage);
        }
    }


    public static function checkUpdateProfileFields(
        $surname,
        $name,
        $email,
        $currentEmail,
    )
    {
        $errors = [];

        if($email !== null && $email !== $currentEmail){
            if (empty($email)) {
                $errors['email'] = 'Введите email.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Укажите корректный email.';
            } elseif (CLUser::isEmailRegistered($email)) {
                $errors['email'] = 'Этот email уже зарегистрирован.';
            }
        }

        if (empty($name . $surname)) {
            $errors['FIO'] = 'Введите ваше ФИО.';
        }
//        elseif (!$name || !$surname  || strlen($name) < 2 || strlen($surname) < 2) {
//            $errors['FIO'] = 'Неверный формат ФИО.';
//        }

        if (count($errors) > 0) {
            $errMessage = json_encode($errors);
            throw new \Exception($errMessage);
        }
    }

    public static function checkOrderRegistrationFields(
        $userType,
        $email,
        $phone,
        $name,
        $surname,
        $inn,
        $ogrn,
        $organizationName,
        $organizationPostalCode,
        $organizationAddress
    )
    {
        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Введите email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректный email.';
        } elseif (CLUser::isEmailRegistered($email)) {
            $errors['email'] = 'Этот email уже зарегистрирован.';
        }

        if (empty($name . $surname)) {
            $errors['FIO'] = 'Введите ваше ФИО.';
        }
//        elseif (!$name || !$surname || strlen($name) < 2 || strlen($surname) < 2) {
//            $errors['FIO'] = 'Неверный формат ФИО.';
//        }

        if (empty($phone)) {
            $errors['phone'] = 'Введите номер телефона.';
        } elseif (!preg_match('/^\+\d{11}$/', $phone)) {
            $errors['phone'] = 'Введите корректный номер телефона.';
        } elseif (CLUser::isPhoneNumberRegistered($phone)) {
            $errors['phone'] = 'Этот номер телефона уже зарегистрирован.';
        }

        if (defined('Legacy\General\Constants::GROUP_' . strtoupper($userType))
            && constant('Legacy\General\Constants::GROUP_' . strtoupper($userType)) == Constants::GROUP_COMPANY) {
            if (!$inn) {
                $errors['inn'] = 'ИНН организации не может быть пустым.';
            }

            if (!$ogrn) {
                $errors['ogrn'] = 'ОГРН организации не может быть пустым.';
            }

//            if (!$kpp) {
//                $errors['kpp'] = 'КПП организации не может быть пустым.';
//            }

            if (!$organizationName) {
                $errors['organization_name'] = 'Название организации не может быть пустым.';
            }

            if (!$organizationPostalCode) {
                $errors['organization_postal_code'] = 'Индекс организации не может быть пустым.';
            }

            if (!$organizationAddress) {
                $errors['organization_address'] = 'Адрес организации не может быть пустым.';
            }
        }

        if (count($errors) > 0) {
            $errMessage = json_encode($errors);
            throw new \Exception($errMessage);
        }
    }
}
