<?php

namespace Sprint\Migration;


class NewEmployeeEmail20240904085746 extends Version
{
    protected $description = "Миграция для почтового события подтверждения сотрудника";

    protected $moduleVersion = "4.6.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Event()->saveEventType('NEW_EMPLOYEE_CONFIRM', array (
  'LID' => 'ru',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Подтверждение регистрации нового сотрудника',
  'DESCRIPTION' => '#EMAIL# - EMail
#EMAIL_CODE# - код подтверждения',
  'SORT' => '1',
));
            $helper->Event()->saveEventType('NEW_EMPLOYEE_CONFIRM', array (
  'LID' => 'en',
  'EVENT_TYPE' => 'email',
  'NAME' => 'New employee registration confirmation',
  'DESCRIPTION' => '#EMAIL# - EMail
#EMAIL_CODE# - confirmation code',
  'SORT' => '1',
));
            $helper->Event()->saveEventMessage('NEW_EMPLOYEE_CONFIRM', array (
  'LID' => 
  array (
    0 => 's1',
  ),
  'ACTIVE' => 'Y',
  'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
  'EMAIL_TO' => '#EMAIL#',
  'SUBJECT' => '#SITE_NAME#: Регистрация сотрудника',
  'MESSAGE' => 'Информационное сообщение сайта #SITE_NAME#
------------------------------------------

Здравствуйте,

Вы получили это сообщение, так как ваш адрес был использован при регистрации нового сотрудника на сервере #SERVER_NAME#.

Ваш код для подтверждения регистрации: #EMAIL_CODE#

Для подтверждения регистрации перейдите по следующей ссылке:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#&confirm_code=#CONFIRM_CODE#

Вы также можете ввести код для подтверждения регистрации на странице:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#

Внимание! Ваш профиль не будет активным, пока вы не подтвердите свою регистрацию.

---------------------------------------------------------------------

Сообщение сгенерировано автоматически.',
  'BODY_TYPE' => 'text',
  'BCC' => '',
  'REPLY_TO' => '',
  'CC' => '',
  'IN_REPLY_TO' => '',
  'PRIORITY' => '',
  'FIELD1_NAME' => '',
  'FIELD1_VALUE' => '',
  'FIELD2_NAME' => '',
  'FIELD2_VALUE' => '',
  'SITE_TEMPLATE_ID' => '',
  'ADDITIONAL_FIELD' => 
  array (
  ),
  'LANGUAGE_ID' => '',
  'EVENT_TYPE' => '[ NEW_EMPLOYEE_CONFIRM ] Подтверждение регистрации нового сотрудника',
));
        }

    public function down()
    {
        //your code ...
    }
}
