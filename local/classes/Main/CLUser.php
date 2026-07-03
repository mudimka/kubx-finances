<?php


namespace Legacy\Main;

use Legacy\General\Constants;
use \Bitrix\Main as BMain;
use Legacy\HighLoadBlock\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Legacy\HighLoadBlock\Organizations;
use Legacy\General\SmsRu;

class CLUser extends \CUser
{
    protected static $digits = 6;
    public const CODE_LIFE_TIME_IN_SECONDS = 180;
    public const REFRESHC_CODE_AFTER_TIME_IN_SECONDS = 120;

    public function Register($USER_LOGIN, $USER_NAME, $USER_LAST_NAME, $USER_PASSWORD, $USER_CONFIRM_PASSWORD, $USER_EMAIL, $SITE_ID = false, $captcha_word = "", $captcha_sid = 0, $bSkipConfirm = false, $USER_PHONE_NUMBER = "", $ORGANIZATION = null, $USER_SECOND_NAME = "", $USER_GROUP = [], $MANAGER_ID = "", $NEED_AUTHORIZE = false)
    {

        /**
         * @global CMain $APPLICATION
         * @global CUserTypeManager $USER_FIELD_MANAGER
         */
        global $APPLICATION, $DB, $USER_FIELD_MANAGER;

        $APPLICATION->ResetException();
        if(defined("ADMIN_SECTION") && ADMIN_SECTION===true && $SITE_ID!==false)
        {
            $APPLICATION->ThrowException(GetMessage("MAIN_FUNCTION_REGISTER_NA_INADMIN"));
            return ["MESSAGE"=>GetMessage("MAIN_FUNCTION_REGISTER_NA_INADMIN"), "TYPE"=>"ERROR"];
        }

        $strError = "";

        if (\COption::GetOptionString("main", "captcha_registration", "N") == "Y")
        {
            if (!($APPLICATION->CaptchaCheckCode($captcha_word, $captcha_sid)))
            {
                $strError .= GetMessage("MAIN_FUNCTION_REGISTER_CAPTCHA")."<br>";
            }
        }

        if($strError)
        {
            if(\COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
            {
                \CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", false, $strError);
            }

            $APPLICATION->ThrowException($strError);
            return ["MESSAGE"=>$strError, "TYPE"=>"ERROR"];
        }

        if($SITE_ID === false) {
            $SITE_ID = SITE_ID;
        }

        $emailRequired = \COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y"
            && \COption::GetOptionString("main", "new_user_email_required", "Y") <> "N";

        $phoneRequired = \COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y"
            && \COption::GetOptionString("main", "new_user_phone_required", "N") == "Y";

        $confirmRequired = !$bSkipConfirm && ($emailRequired || $phoneRequired);
        $checkword = md5(\CMain::GetServerUniqID().uniqid());
        $active = $confirmRequired ? "N" : "Y";

        $arFields = [
            "LOGIN" =>  $USER_LOGIN,
            "NAME" => $USER_NAME,
            "SECOND_NAME" => $USER_SECOND_NAME,
            "LAST_NAME" => $USER_LAST_NAME,
            "PASSWORD" => $USER_PASSWORD,
            "CHECKWORD" => $checkword,
            "~CHECKWORD_TIME" => $DB->CurrentTimeFunction(),
            "CONFIRM_PASSWORD" => $USER_CONFIRM_PASSWORD,
            "EMAIL" => $USER_EMAIL,
            //если зарегать неверную почту, то потом выдает ошибку "номер телефона уже зарегистрирован"
//            "PHONE_NUMBER" => $USER_PHONE_NUMBER,
            "PERSONAL_PHONE" => $USER_PHONE_NUMBER,
            "UF_ORGANIZATION" => [$ORGANIZATION],
            "UF_ACTIVE_ORGANIZATION" => $ORGANIZATION,
            "ACTIVE" => $active,
            "SITE_ID" => $SITE_ID,
            "LANGUAGE_ID" => LANGUAGE_ID,
            "USER_IP" => $_SERVER["REMOTE_ADDR"],
            "USER_HOST" => @gethostbyaddr($_SERVER["REMOTE_ADDR"]),
            "GROUP_ID" => $USER_GROUP,
            "UF_MANAGER_ID" => $MANAGER_ID,
        ];
        $USER_FIELD_MANAGER->EditFormAddFields("USER", $arFields);

        $def_group = \COption::GetOptionString("main", "new_user_registration_def_group", "");

        $def_group = explode(',', $def_group);
        if (defined('Legacy\\General\\Constants::GROUP_EMPLOYEES_S1') && in_array(Constants::GROUP_EMPLOYEES_S1, $def_group)) {
            unset($def_group[array_search(Constants::GROUP_EMPLOYEES_S1, $def_group)]);
        }
        $def_group = implode(',', $def_group);

        if($def_group!="") {
            $arFields["GROUP_ID"] = array_merge($arFields["GROUP_ID"], explode(",", $def_group));
        }

        $bOk = true;
        $result_message = true;
        foreach(GetModuleEvents("main", "OnBeforeUserRegister", true) as $arEvent)
        {
            if(ExecuteModuleEventEx($arEvent, [&$arFields]) === false)
            {
                if($err = $APPLICATION->GetException())
                {
                    $result_message = ["MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR"];
                }
                else
                {
                    $APPLICATION->ThrowException("Unknown error");
                    $result_message = ["MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR"];
                }

                $bOk = false;
                break;
            }
        }

        $ID = false;
        if($bOk)
        {
            if($arFields["SITE_ID"] === false)
            {
                $arFields["SITE_ID"] = \CSite::GetDefSite();
            }
            $arFields["LID"] = $arFields["SITE_ID"];

            if($ID = $this->Add($arFields)) {
                if($confirmRequired){
                    //создаем код в хайлоадблоке и складываем код в массив для событий
                    $code = self::GenerateCode($USER_LOGIN, $ID);
//                    $sms = new SmsRu('SMS_NEW_USER_CONFIRM_NUMBER', [
//                        'PHONE' => $USER_PHONE_NUMBER,
//                        'CODE' => $code,
//                    ]);
//                    $obResult = $sms->send();
//
//                    if($obResult->isSuccess()) {
//                        $result_message = array(
//                            "MESSAGE" => GetMessage("main_register_sms_sent"),
//                            "TYPE" => "OK",
//                            "ID" => $ID,
//                        );
//                    } else {
//                        $result_message = array(
//                            "MESSAGE" => implode('. ', $obResult->getErrorMessages()),
//                            "TYPE" => "ERROR",
//                            "ID" => $ID,
//                        );
//                    }

                    $arFields['CONFIRM_CODE'] = $code;

                    $arFields["USER_ID"] = $ID;

                    $arEventFields = $arFields;
                    unset($arEventFields["PASSWORD"]);
                    unset($arEventFields["CONFIRM_PASSWORD"]);
                    unset($arEventFields["~CHECKWORD_TIME"]);

                    $event = new \CEvent;

                    $event->Send("NEW_USER", $arEventFields["SITE_ID"], $arEventFields);
                    $event->Send("NEW_USER_CONFIRM", $arEventFields["SITE_ID"], $arEventFields);
                }
            }
            else {
                $APPLICATION->ThrowException($this->LAST_ERROR);
                $result_message = ["MESSAGE"=>$this->LAST_ERROR, "TYPE"=>"ERROR"];
            }
        }

        if(is_array($result_message))
        {
            if($result_message["TYPE"] == "OK")
            {
                if(\COption::GetOptionString("main", "event_log_register", "N") === "Y")
                {
                    $res_log["user"] = ($USER_NAME != "" || $USER_LAST_NAME != "") ? trim($USER_NAME." ".$USER_LAST_NAME) : $USER_LOGIN;
                    \CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID, serialize($res_log));
                }
            }
            else
            {
                if(\COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
                {
                    \CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, $result_message["MESSAGE"]);
                }
            }
        }

        //authorize succesfully registered user, except email or phone confirmation is required
        $isAuthorize = false;
        if($ID !== false && !$confirmRequired && $NEED_AUTHORIZE)
        {
            $isAuthorize = $this->Authorize($ID);
        }

        $agreementId = intval(\COption::getOptionString("main", "new_user_agreement", ""));
        if ($agreementId && $isAuthorize)
        {
            $agreementObject = new \Bitrix\Main\UserConsent\Agreement($agreementId);
            if ($agreementObject->isExist() && $agreementObject->isActive() && $_REQUEST["USER_AGREEMENT"] == "Y")
            {
                \Bitrix\Main\UserConsent\Consent::addByContext($agreementId, "main/reg", "register");
            }
        }

        $arFields["RESULT_MESSAGE"] = $result_message;
        foreach (GetModuleEvents("main", "OnAfterUserRegister", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, [&$arFields]);

        return $arFields["RESULT_MESSAGE"];
    }

    public static function GenerateCode($contact, $userID){
        //генерируется код подтверждения
        $code = rand(100001, 999999);
        //добавляем элемент в хайлоадблок
        $currentDateTime = new DateTime();
        $arElementFields = [
            'UF_CONTACT' => $contact,
            'UF_CODE' => $code,
            'UF_ATTEMPTS' => 0,
            'UF_USER_ID' => $userID,
            'UF_CONFIRMED' => 'N',
            'UF_DATETIME_SEND' => $currentDateTime->getTimestamp()
        ];
        Entity::getInstance()->add(Constants::HLBLOCK_CONFIRMATION_CODES, $arElementFields);

        return $code;
    }

    public static function VerifyCode($contact, $code)
    {
        $arFieldsFilter = [
            'UF_CONTACT' => $contact,
        ];

        $params = [
            'filter' => $arFieldsFilter,
            'order' => ['ID'=>'DESC'],
        ];

        $row = Entity::getInstance()->getRow(Constants::HLBLOCK_CONFIRMATION_CODES, $params);

        if($row)
        {
            $result = false;

            //если последняя запись - подтвержденная (код был использован успешно), то ошибка
            if($row["UF_CONFIRMED"] === 'Y')
            {
                throw new \Exception(json_encode(['code' => 'Код отсутствует. Запросите новый код подтверждения.']));
            }

            //если больше трех раз проверка - возвращаем ошибку количества
            if($row["UF_ATTEMPTS"] >= 3)
            {
                throw new \Exception(json_encode(['code' => 'Количество попыток израсходовано. Запросите новый код подтверждения.']));
            }

            $currentDateTime = new DateTime();
            if(($currentDateTime->getTimestamp() - $row['UF_DATETIME_SEND']) > self::CODE_LIFE_TIME_IN_SECONDS)
            {
                throw new \Exception(json_encode(['code' => 'Код подтверждения истёк. Отправьте запрос на новый код.']));
            }

            $data = [];
            //проверка на соответствие кода
            if($row['UF_CODE'] == $code){
                $result = true;
                $data["UF_CONFIRMED"]  = 'Y';
            }
            else{
                //увеличиваем количество попыток, если не соответствует
                $data["UF_ATTEMPTS"] = (int)$row["UF_ATTEMPTS"] + 1;
            }
            //обновляем инфу записи в хайдлоадблоке
            if(!empty($data))
            {
                Entity::getInstance()->update(Constants::HLBLOCK_CONFIRMATION_CODES, $row['ID'], $data);
            }
            //возвращаем ответ методу проверки кода
            if($result)
            {
                return $row["UF_USER_ID"];
            }
            //если дошли до этой строки, значит код не сошелся
            throw new \Exception(json_encode(['code' => 'Неверный код подтверждения.']));
        }
        //если дошли до этой строки, значит записи в ХБ нет
        throw new \Exception(json_encode(['code' => 'Код отсутствует. Запросите новый код подтверждения.']));
    }

    public function isUpdatedNotActiveRegistration($login, $email, $phone, $name, $second_name, $surname, $password, $passwordConfirm, $organizationID, $group, $userType)
    {
        $user = UserTable::getRow([
            'select' => ['ID', 'ACTIVE', 'UF_ACTIVE_ORGANIZATION'],
            'filter' => [
                'LOGIN' => $login,
                'ACTIVE' => 'N'
            ]
        ]);

        //todo сделать разделение на физ и юр
        $fields = [
            "NAME" => $name,
            "SECOND_NAME" => $second_name,
            "LAST_NAME" => $surname,

            "PASSWORD" => $password,
            "CONFIRM_PASSWORD" => $passwordConfirm,

            "EMAIL" => $email,
//            "PHONE_NUMBER" => $phone,
            "PERSONAL_PHONE" => $phone,

            "UF_ORGANIZATION" => [$organizationID],
            "UF_ACTIVE_ORGANIZATION" => $organizationID,

            "USER_IP" => $_SERVER["REMOTE_ADDR"],
            "USER_HOST" => @gethostbyaddr($_SERVER["REMOTE_ADDR"]),
            "GROUP_ID" => $group
        ];

        $def_group = \COption::GetOptionString("main", "new_user_registration_def_group", "");
        if($def_group!="")
            $fields["GROUP_ID"] = array_merge($fields["GROUP_ID"], explode(",", $def_group));

        if($user){
            if ($this->Update($user['ID'], $fields)){
                if($user['UF_ACTIVE_ORGANIZATION']){
                    Organizations::delete($user['UF_ACTIVE_ORGANIZATION']);
                }
                return true;
            } else {
                throw new \Exception($this->LAST_ERROR);
            }
        } else{
            return false;
        }
    }

    public static function isPhoneNumberRegistered($phone)
    {
        $user = UserTable::getRow([
            'select' => ['ID'],
            'filter' => [
                'PERSONAL_PHONE' => $phone,
                'ACTIVE' => 'Y'
            ]
        ]);

        return !!$user;
    }

    public static function isEmailRegistered($email)
    {
        $user = UserTable::getRow([
            'select' => ['ID'],
            'filter' => [
                'EMAIL' => $email,
                'ACTIVE' => 'Y'
            ]
        ]);
        return !!$user;
    }

    public function Login($contact, $password, $remember = 'N', $password_original = 'Y', $loginType='phone')
    {
        if($loginType === 'email') {
            $field = 'EMAIL';
        } else {
            $field = 'LOGIN';
        }

        $user = UserTable::getRow([
            'select' => [
                'LOGIN',
            ],
            'filter' => [$field => $contact, 'ACTIVE' => 'Y']
        ]);

        //вызов метода авторизации родителя
        if($user){
            $res = parent::Login($user['LOGIN'], $password, $remember, $password_original);
            if (is_array($res) && $res['TYPE'] == 'ERROR') {
                throw new \Exception(json_encode(['password' => 'Неверный пароль.']));
            } else {
                return $res;
            }
        } else{
            throw new \Exception(json_encode(['login' => 'Пользователь не найден.']));
        }
    }
}
