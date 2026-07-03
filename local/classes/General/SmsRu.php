<?php

namespace Legacy\General;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Sms;
class SmsRu extends Sms\Event
{
	protected $sms_host = 'https://sms.ru/sms/send';
    protected $sms_api_id;

    public function __construct($eventName, array $fields = [])
    {
        parent::__construct($eventName, $fields);
        parent::setSite(SITE_ID);
        parent::setLanguage('ru');
        $this->sms_api_id = Option::get('kubx.settings', 'sms_ru');
    }

	public function send($directly = false)
	{
		$result = new Main\Result();

		$phone = $this->fields['PHONE'];

		$messageListResult = $this->createMessageList();
		if (!$messageListResult->isSuccess()) {
			return $result->addErrors($messageListResult->getErrors());
		}
		$messageList = $messageListResult->getData();

		if ($message = current($messageList)) {
			$smsMessage = $message->getText();
			//Если сообщение длинное, то транслитерацируем его.
			if (mb_strlen($smsMessage) > 70) {
				$smsMessage = \CUtil::translit($smsMessage, 'ru', [
					'max_len' => 160,
					'change_case' => false,
					'safe_chars' => ' https:/.?=&+-',
				]);
				$smsMessage = mb_convert_encoding($smsMessage, 'windows-1251', 'utf-8');
			}

            $httpClient = new HttpClient();
            $response = $httpClient->post(
                $this->sms_host,
                [
                    'api_id' => $this->sms_api_id,
                    'to' => $phone,
                    'msg' => $smsMessage,
                    'json' => 1
                ]
            );

            $response = json_decode($response, true);
            if ($response['status'] == 'OK') {
                $sms = current($response['sms']);
                if ($sms['status'] == 'OK') {
                    $result->setData(['ID' => $sms['sms_id']]);
                } else {
                    $result->addError(new Main\Error($sms['status_text']));
                }
            } else {
				$result->addError(new Main\Error('Ошибка при отправке СМС'));
            }
        }
        return $result;
    }
}
