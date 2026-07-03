<?php

namespace Sprint\Migration;


class SmsEvents20240515115437 extends Version
{
    protected $description = "Миграции для почтовых событий";

    protected $moduleVersion = "4.6.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Event()->saveEventType('SMS_NEW_USER_CONFIRM_NUMBER', array (
  'LID' => 'ru',
  'EVENT_TYPE' => 'sms',
  'NAME' => 'Подтверждение номера телефона по СМС для регистрации пользователя',
  'DESCRIPTION' => '#USER_PHONE# - номер телефона
#CODE# - код подтверждения',
  'SORT' => '150',
));
            $helper->Event()->saveEventType('SMS_NEW_USER_CONFIRM_NUMBER', array (
  'LID' => 'en',
  'EVENT_TYPE' => 'sms',
  'NAME' => 'Verify phone number using SMS for registration',
  'DESCRIPTION' => '#USER_PHONE# - phone number
#CODE# - confirmation code',
  'SORT' => '150',
));
        }

    public function down()
    {
        //your code ...
    }
}
