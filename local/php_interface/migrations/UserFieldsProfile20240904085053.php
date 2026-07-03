<?php

namespace Sprint\Migration;


class UserFieldsProfile20240904085053 extends Version
{
    protected $description = "Миграции для пользовательских полей профиля";

    protected $moduleVersion = "4.6.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_DEPARTMENT',
  'USER_TYPE_ID' => 'iblock_section',
  'XML_ID' => '',
  'SORT' => '1',
  'MULTIPLE' => 'Y',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 8,
    'IBLOCK_ID' => 3,
    'DEFAULT_VALUE' => '',
    'ACTIVE_FILTER' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Departments',
    'ru' => 'Подразделения',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Departments',
    'ru' => 'Подразделения',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Departments',
    'ru' => 'Подразделения',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_PHONE_INNER',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '2',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'S',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Extension number',
    'ru' => 'Внутренний телефон',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Extension number',
    'ru' => 'Внутренний телефон',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Extension number',
    'ru' => 'Внутренний телефон',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_USER_CRM_ENTITY',
  'USER_TYPE_ID' => 'crm',
  'XML_ID' => NULL,
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'LEAD' => 'Y',
    'CONTACT' => 'Y',
    'COMPANY' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'CRM Items',
    'ru' => 'Элементы CRM',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'CRM Items',
    'ru' => 'Элементы CRM',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'CRM Items',
    'ru' => 'Элементы CRM',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_IM_SEARCH',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => NULL,
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'IM: users can find',
    'ru' => 'IM: users can find',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'IM: users can find',
    'ru' => 'IM: users can find',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'IM: users can find',
    'ru' => 'IM: users can find',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_CONNECTOR_MD5',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => NULL,
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_1C',
  'USER_TYPE_ID' => 'boolean',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'DEFAULT_VALUE' => 0,
    'DISPLAY' => 'CHECKBOX',
    'LABEL' => 
    array (
      0 => '',
      1 => '',
    ),
    'LABEL_CHECKBOX' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'User from 1C',
    'ru' => 'Пользователь из 1С',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'User from 1C',
    'ru' => 'Пользователь из 1С',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'User from 1C',
    'ru' => 'Пользователь из 1С',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_INN',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'INN',
    'ru' => 'ИНН',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'INN',
    'ru' => 'ИНН',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'INN',
    'ru' => 'ИНН',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_DISTRICT',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'District',
    'ru' => 'Район',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'District',
    'ru' => 'Район',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'District',
    'ru' => 'Район',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_SKYPE',
  'USER_TYPE_ID' => 'string_formatted',
  'XML_ID' => 'UF_SKYPE',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => NULL,
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
    'PATTERN' => '<a href="skype://#VALUE#">#VALUE#</a>',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Skype name',
    'ru' => 'Логин Skype',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Skype name',
    'ru' => 'Логин Skype',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Skype name',
    'ru' => 'Логин Skype',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_SKYPE_LINK',
  'USER_TYPE_ID' => 'url',
  'XML_ID' => 'UF_SKYPE_LINK',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'POPUP' => 'Y',
    'SIZE' => 20,
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Skype chat link',
    'ru' => 'Ссылка на чат в Skype',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Skype chat link',
    'ru' => 'Ссылка на чат в Skype',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Skype chat link',
    'ru' => 'Ссылка на чат в Skype',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_ZOOM',
  'USER_TYPE_ID' => 'url',
  'XML_ID' => 'UF_ZOOM',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'POPUP' => 'Y',
    'SIZE' => 20,
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Zoom',
    'ru' => 'Zoom',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Zoom',
    'ru' => 'Zoom',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Zoom',
    'ru' => 'Zoom',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TWITTER',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Twitter',
    'ru' => 'Twitter',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Twitter',
    'ru' => 'Twitter',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Twitter',
    'ru' => 'Twitter',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_FACEBOOK',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Facebook',
    'ru' => 'Facebook',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Facebook',
    'ru' => 'Facebook',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Facebook',
    'ru' => 'Facebook',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_LINKEDIN',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'LinkedIn',
    'ru' => 'LinkedIn',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'LinkedIn',
    'ru' => 'LinkedIn',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'LinkedIn',
    'ru' => 'LinkedIn',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_XING',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Xing',
    'ru' => 'Xing',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Xing',
    'ru' => 'Xing',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Xing',
    'ru' => 'Xing',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_WEB_SITES',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Other websites',
    'ru' => 'Другие сайты',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Other websites',
    'ru' => 'Другие сайты',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Other websites',
    'ru' => 'Другие сайты',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_SKILLS',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Skills',
    'ru' => 'Навыки',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Skills',
    'ru' => 'Навыки',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Skills',
    'ru' => 'Навыки',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_INTERESTS',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Interests',
    'ru' => 'Интересы',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Interests',
    'ru' => 'Интересы',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Interests',
    'ru' => 'Интересы',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_EMPLOYMENT_DATE',
  'USER_TYPE_ID' => 'date',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'E',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'Y',
  'SETTINGS' => 
  array (
    'DEFAULT_VALUE' => 
    array (
      'TYPE' => 'NONE',
      'VALUE' => '',
    ),
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Hiring date',
    'ru' => 'Дата принятия на работу',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Hiring date',
    'ru' => 'Дата принятия на работу',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Hiring date',
    'ru' => 'Дата принятия на работу',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_WORK_BINDING',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => NULL,
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'ru' => 'Привязки для учета внутреннего совмещения',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'ru' => NULL,
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'ru' => NULL,
  ),
  'ERROR_MESSAGE' => 
  array (
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_BXDAVEX_CALSYNC',
  'USER_TYPE_ID' => 'datetime',
  'XML_ID' => NULL,
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DEFAULT_VALUE' => 
    array (
      'TYPE' => 'NONE',
      'VALUE' => '',
    ),
    'USE_SECOND' => 'Y',
    'USE_TIMEZONE' => 'N',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Calendar sync date',
    'ru' => 'Calendar sync date',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_DIRECTOR',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => 'UF_DIRECTOR',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Director',
    'ru' => 'Директор',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Director',
    'ru' => 'Директор',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Director',
    'ru' => 'Директор',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_OGRN',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => 'UF_OGRN',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'OGRN',
    'ru' => 'ОГРН',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'OGRN',
    'ru' => 'ОГРН',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'OGRN',
    'ru' => 'ОГРН',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_KPP',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => 'UF_KPP',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'KPP',
    'ru' => 'КПП',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'KPP',
    'ru' => 'КПП',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'KPP',
    'ru' => 'КПП',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_MANAGER_ID',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => 'UF_MANAGER_ID',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Manager ID',
    'ru' => 'ID менеджера',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_ORGANIZATION',
  'USER_TYPE_ID' => 'hlblock',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'Y',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'HLBLOCK_ID' => 'Organizations',
    'HLFIELD_ID' => 'UF_NAME',
    'DEFAULT_VALUE' => 
    array (
    ),
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Organization',
    'ru' => 'Организация',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Organization',
    'ru' => 'Организация',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Organization',
    'ru' => 'Организация',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_ACTIVE_ORGANIZATION',
  'USER_TYPE_ID' => 'hlblock',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'HLBLOCK_ID' => 'Organizations',
    'HLFIELD_ID' => 'UF_NAME',
    'DEFAULT_VALUE' => 0,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Active organization',
    'ru' => 'Активная организация',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Active organization',
    'ru' => 'Активная организация',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Active organization',
    'ru' => 'Активная организация',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TIMEMAN',
  'USER_TYPE_ID' => 'enumeration',
  'XML_ID' => '',
  'SORT' => '1011',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'CAPTION_NO_VALUE' => '(наследовать значение)',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Working Time Management',
    'ru' => 'Учет рабочего времени',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Working Time Management',
    'ru' => 'Учет рабочего времени',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Working Time Management',
    'ru' => 'Учет рабочего времени',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'вести учет',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'Y',
    ),
    1 => 
    array (
      'VALUE' => 'не вести учет',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'N',
    ),
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_MAX_START',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1015',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 5,
    'DEFAULT_VALUE' => '00:00',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Latest clock-in time',
    'ru' => 'Максимальное время начала рабочего дня',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Latest clock-in time',
    'ru' => 'Максимальное время начала рабочего дня',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Latest clock-in time',
    'ru' => 'Максимальное время начала рабочего дня',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_MIN_FINISH',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1019',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 5,
    'DEFAULT_VALUE' => '00:00',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Earliest clock-out time',
    'ru' => 'Минимальное время завершения рабочего дня',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Earliest clock-out time',
    'ru' => 'Минимальное время завершения рабочего дня',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Earliest clock-out time',
    'ru' => 'Минимальное время завершения рабочего дня',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_MIN_DURATION',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1023',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 5,
    'DEFAULT_VALUE' => '00:00',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Minimum Working Day Duration',
    'ru' => 'Минимальная продолжительность рабочего дня',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Minimum Working Day Duration',
    'ru' => 'Минимальная продолжительность рабочего дня',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Minimum Working Day Duration',
    'ru' => 'Минимальная продолжительность рабочего дня',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_REPORT_REQ',
  'USER_TYPE_ID' => 'enumeration',
  'XML_ID' => '',
  'SORT' => '1027',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'CAPTION_NO_VALUE' => '(наследовать значение)',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Daily Report',
    'ru' => 'Отчет за день',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Daily Report',
    'ru' => 'Отчет за день',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Daily Report',
    'ru' => 'Отчет за день',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'обязателен',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'Y',
    ),
    1 => 
    array (
      'VALUE' => 'не обязателен',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'N',
    ),
    2 => 
    array (
      'VALUE' => 'не показывать форму отчета',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'A',
    ),
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_REPORT_TPL',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1031',
  'MULTIPLE' => 'Y',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 50,
    'ROWS' => 6,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Report Templates',
    'ru' => 'Шаблоны отчета',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Report Templates',
    'ru' => 'Шаблоны отчета',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Report Templates',
    'ru' => 'Шаблоны отчета',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_FREE',
  'USER_TYPE_ID' => 'enumeration',
  'XML_ID' => '',
  'SORT' => '1034',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'CAPTION_NO_VALUE' => '(наследовать значение)',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Flexible work schedule',
    'ru' => 'Свободный график',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Flexible work schedule',
    'ru' => 'Свободный график',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Flexible work schedule',
    'ru' => 'Свободный график',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'включен',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'Y',
    ),
    1 => 
    array (
      'VALUE' => 'выключен',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'N',
    ),
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_TIME',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1035',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 5,
    'DEFAULT_VALUE' => '17:00',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Report due by',
    'ru' => 'Время сдачи отчета',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Report due by',
    'ru' => 'Время сдачи отчета',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Report due by',
    'ru' => 'Время сдачи отчета',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_DAY',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1036',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 1,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Day',
    'ru' => 'День',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Day',
    'ru' => 'День',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Day',
    'ru' => 'День',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_REPORT_DATE',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1037',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 2,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Day of month',
    'ru' => 'Число месяца',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Day of month',
    'ru' => 'Число месяца',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Day of month',
    'ru' => 'Число месяца',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_REPORT_PERIOD',
  'USER_TYPE_ID' => 'enumeration',
  'XML_ID' => '',
  'SORT' => '1038',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'CAPTION_NO_VALUE' => '(наследовать значение)',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Report period',
    'ru' => 'Частота сдачи отчета',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Report period',
    'ru' => 'Частота сдачи отчета',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Report period',
    'ru' => 'Частота сдачи отчета',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'День',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'DAY',
    ),
    1 => 
    array (
      'VALUE' => 'Неделя',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'WEEK',
    ),
    2 => 
    array (
      'VALUE' => 'Месяц',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'MONTH',
    ),
    3 => 
    array (
      'VALUE' => 'Отчет не требуется',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'NONE',
    ),
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_DELAY_TIME',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1039',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 20,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Deferred report time',
    'ru' => 'Отложенное время отчета',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Deferred report time',
    'ru' => 'Отложенное время отчета',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Deferred report time',
    'ru' => 'Отложенное время отчета',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_LAST_REPORT_DATE',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1040',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 20,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Last report date',
    'ru' => 'Дата последнего отчета',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Last report date',
    'ru' => 'Дата последнего отчета',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Last report date',
    'ru' => 'Дата последнего отчета',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_SETTING_DATE',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1040',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 8,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 30,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Settings saved on',
    'ru' => 'Дата установки настроек',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Settings saved on',
    'ru' => 'Дата установки настроек',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Settings saved on',
    'ru' => 'Дата установки настроек',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_TM_ALLOWED_DELTA',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1065',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 3,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 5,
    'DEFAULT_VALUE' => '900',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Allowed time adjustment interval',
    'ru' => 'Допустимый промежуток изменения времени',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Allowed time adjustment interval',
    'ru' => 'Допустимый промежуток изменения времени',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Allowed time adjustment interval',
    'ru' => 'Допустимый промежуток изменения времени',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => NULL,
    'ru' => NULL,
  ),
));
    }

    public function down()
    {
        //your code ...
    }
}
