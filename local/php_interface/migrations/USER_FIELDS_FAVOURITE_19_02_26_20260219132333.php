<?php

namespace Sprint\Migration;


class USER_FIELDS_FAVOURITE_19_02_26_20260219132333 extends Version
{
    protected $author = "admin";

    protected $description = "";

    protected $moduleVersion = "5.6.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_FAVOURITE_PRODUCT_IDS',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => 'UF_FAVOURITE_PRODUCT_IDS',
  'SORT' => '100',
  'MULTIPLE' => 'Y',
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
    'en' => 'ID favourites',
    'ru' => 'ID избранных товаров',
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
    }

}
