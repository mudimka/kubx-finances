<?php
/**
 * Created by PhpStorm.
 * User: ilyagorodeckiy
 * Date: 2019-03-12
 * Time: 12:22
 */

namespace Legacy\API;


use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CFile;
use CFormCrm;
use CFormResult;
use Exception;
use Legacy\General\GoogleCaptcha;
use Legacy\General\Constants;
use Legacy\General\Helper;

class Webform
{
    public static function getFields($arRequest)
    {
        if (Loader::includeModule("form")) {
            if ($id = self::getFormId($arRequest['form_name'])) {
                $connection = Application::getConnection();
                $sqlHelper = $connection->getSqlHelper();
                $sql = 'select bff.SID,
                        case
                            when bfa.FIELD_TYPE in ("radio", "dropbox") then CONCAT("form_", bfa.FIELD_TYPE, "_", LOWER(bff.SID))
                            when bfa.FIELD_TYPE in ("checkbox", "multiselect") then CONCAT("form_", bfa.FIELD_TYPE, "_", LOWER(bff.SID), "[]")
                            else CONCAT("form_", bfa.FIELD_TYPE, "_", bfa.ID)
                        end as CODE
                        from b_form_field bff
                        left join b_form_answer bfa ON bfa.FIELD_ID = bff.ID
                        where bff.FORM_ID='.$sqlHelper->forSql($id);

                $recordset = $connection->query($sql);
                $values = [];
                while ($record = $recordset->fetch()) {
                    if ($record['SID'] == 'file') {
                        $file = CFile::MakeFileArray($arRequest['file']['tmp_name']);
                        $file['name'] = $arRequest['file']['name'];
                        $values[$record['CODE']] = $file;
                    }
                    else {
                        $values[$record['CODE']] = htmlspecialchars($arRequest[$record['SID']]);
                    }
                }
                return $values;
            } else {
                throw new Exception('Неизвестная форма.');
            }
        } else {
            throw new Exception('Не удалось загрузить модули.');
        }
    }

    /**
     * @param $arRequest
     * @return bool
     * @throws LoaderException
     * @throws Exception
     */
    public static function sendMessage($arRequest)
    {
        if (Loader::includeModule("form")) {
            if ($id = self::getFormId($arRequest['form_name'])) {
                $values = self::getFields($arRequest);
                if ($resultId = CFormResult::Add($id, $values)) {
                    $mailSent = CFormResult::Mail($resultId);
                    $leadId = CFormCrm::AddLead($id, $resultId);
                    self::sendFormToBX24($arRequest);
                    if (!$mailSent)
                        throw new Exception('Не удалось отправить письмо');
                    return true;
                } else {
                    global $strError;
                    throw new Exception($strError);
                }
            } else {
                throw new Exception('Неизвестная форма.');
            }
        } else {
            throw new Exception('Не удалось загрузить модули.');
        }
    }

    public static function getFormId($form_name)
    {
        return constant('Legacy\General\Constants::' . $form_name);
    }

    public static function sendFormToBX24($arRequest)
    {
        $fields = [];
        switch ($arRequest['form_name']){
            case 'WEBFORM_BATCH_COST_CALCULATION':
                $fields = [
                    'fields' => [
                        'TITLE' => 'Расчет стоимости партии',
                        'EMAIL' => [ [ "VALUE" => $arRequest['email'], "VALUE_TYPE" =>"WORK" ] ] ,
                        'PHONE' => [ [ "VALUE" => $arRequest['phone'], "VALUE_TYPE" =>"WORK" ] ] ,
                        'NAME' => $arRequest['name'],
                    ],
                ];
                if($arRequest['file']){
                    $fields['fields']['UF_CRM_1695900185369'] = [
                        'fileData' =>
                            [
                                $arRequest['file']['name'],
                                base64_encode(file_get_contents($arRequest['file']['tmp_name']))
                            ]
                    ];
                }
                Helper::CurlBitrix24('crm.lead.add', $fields);
                break;
            case 'WEBFORM_CALL_ME':
                $fields = [
                    'fields' => [
                        'TITLE' => 'Перезвонить мне',
                        'PHONE' => [
                            [ "VALUE" => $arRequest['phone'], "VALUE_TYPE" =>"WORK" ]
                        ],
                        'NAME' => $arRequest['name'],
                    ],
                ];
                Helper::CurlBitrix24('crm.lead.add', $fields);
                break;
            case 'WEBFORM_REQUEST_RECONCILIATION_ACT':
                $fields = [
                    'fields' => [
                        'TITLE' => 'Запрос акта сверки',
                        'EMAIL' => [ [ "VALUE" => $arRequest['email'], "VALUE_TYPE" =>"WORK" ] ] ,
                        'NAME' => $arRequest['person_id'],
                    ],
                ];
                Helper::CurlBitrix24('crm.lead.add', $fields);
                break;
            case 'WEBFORM_MESSAGE_TO_MANAGER':
                $fields = [
                    'fields' => [
                        'TITLE' => 'Сообщение менеджеру',
                        'EMAIL' => [ [ "VALUE" => $arRequest['email'], "VALUE_TYPE" =>"WORK" ] ] ,
                        'COMMENTS' => $arRequest['comment'],
                    ],
                ];
                Helper::CurlBitrix24('crm.lead.add', $fields);
                break;
            default:
                break;
        }
    }



}