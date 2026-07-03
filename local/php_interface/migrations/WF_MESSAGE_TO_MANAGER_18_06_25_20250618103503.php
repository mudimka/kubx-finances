<?php

namespace Sprint\Migration;


class WF_MESSAGE_TO_MANAGER_18_06_25_20250618103503 extends Version
{
    protected $author = "admin";

    protected $description = "";

    protected $moduleVersion = "5.0.2";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $formId = $helper->Form()->saveForm(array (
  'NAME' => 'Форма сообщение менеджеру',
  'SID' => 'MESSAGE_TO_MANAGER',
  'C_SORT' => '400',
  'MAIL_EVENT_TYPE' => 'FORM_FILLING_MESSAGE_TO_MANAGER',
  'FILTER_RESULT_TEMPLATE' => '',
  'TABLE_RESULT_TEMPLATE' => '',
  'RESTRICT_STATUS' => NULL,
  'STAT_EVENT2' => 'message_to_manager',
  'arSITE' => 
  array (
    0 => 's1',
  ),
  'arMENU' => 
  array (
    'ru' => 'Форма сообщение менеджеру',
    'en' => '',
  ),
  'arGROUP' => 
  array (
  ),
  'arMAIL_TEMPLATE' => 
  array (
  ),
));
        $helper->Form()->saveStatuses($formId, array (
  0 => 
  array (
    'CSS' => 'statusgreen',
    'TITLE' => 'DEFAULT',
    'HANDLER_OUT' => '',
    'HANDLER_IN' => '',
    'arPERMISSION_VIEW' => 
    array (
      0 => '0',
    ),
    'arPERMISSION_MOVE' => 
    array (
      0 => '0',
    ),
    'arPERMISSION_EDIT' => 
    array (
      0 => '0',
    ),
    'arPERMISSION_DELETE' => 
    array (
      0 => '0',
    ),
  ),
));
        $helper->Form()->saveFields($formId, array (
  0 => 
  array (
    'TITLE' => 'Ваш E-mail',
    'TITLE_TYPE' => 'text',
    'SID' => 'email',
    'IN_FILTER' => 'N',
    'FIELD_TYPE' => '',
    'COMMENTS' => 'Ваш E-mail',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => 'Ваш E-mail',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
        'C_SORT' => '100',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  1 => 
  array (
    'TITLE' => 'Ваш комментарий',
    'TITLE_TYPE' => 'text',
    'SID' => 'comment',
    'C_SORT' => '200',
    'IN_FILTER' => 'N',
    'FIELD_TYPE' => '',
    'COMMENTS' => 'Ваш комментарий',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => 'Ваш комментарий',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
        'C_SORT' => '100',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
));
    }
}

