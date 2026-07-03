<?php

namespace Sprint\Migration;


class USER_FIELD_FAVOURITES_12_12_20241212124302 extends Version
{
    protected $description = "";

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
  'FIELD_NAME' => 'UF_FAVOURITE_PRODUCT_IDS',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => 'UF_FAVOURITE_PRODUCT_IDS',
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
    'en' => 'ID favourite',
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

    public function down()
    {
        //your code ...
    }
}
