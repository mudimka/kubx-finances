<?php

namespace Sprint\Migration;


class HB_FORM_FIELDS_14_07_25_20250714164829 extends Version
{
    protected $author = "admin";

    protected $description = "";

    protected $moduleVersion = "5.3.3";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
    $hlblockId = $helper->Hlblock()->saveHlblock(array (
  'NAME' => 'FormFields',
  'TABLE_NAME' => 'form_fields',
  'LANG' => 
  array (
    'ru' => 
    array (
      'NAME' => 'Поля форм',
    ),
    'en' => 
    array (
      'NAME' => 'Form fields',
    ),
  ),
));
        $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_PLACEHOLDER',
  'USER_TYPE_ID' => 'string',
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
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Подсказка в поле (placeholder)',
    'ru' => 'Подсказка в поле (placeholder)',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Подсказка в поле (placeholder)',
    'ru' => 'Подсказка в поле (placeholder)',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Подсказка в поле (placeholder)',
    'ru' => 'Подсказка в поле (placeholder)',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_DATA_TYPE',
  'USER_TYPE_ID' => 'enumeration',
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
    'CAPTION_NO_VALUE' => '',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Тип данных',
    'ru' => 'Тип данных',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Тип данных',
    'ru' => 'Тип данных',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Тип данных',
    'ru' => 'Тип данных',
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
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'Почта',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'email',
    ),
    1 => 
    array (
      'VALUE' => 'ФИО',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'FIO',
    ),
    2 => 
    array (
      'VALUE' => 'Телефон',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'phone',
    ),
  ),
));
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_ITEMS',
  'USER_TYPE_ID' => 'string',
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
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Список опций для выбора (формат code::name)',
    'ru' => 'Список опций для выбора (формат code::name)',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Список опций для выбора (формат code::name)',
    'ru' => 'Список опций для выбора (формат code::name)',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Список опций для выбора (формат code::name)',
    'ru' => 'Список опций для выбора (формат code::name)',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_ACCEPT',
  'USER_TYPE_ID' => 'string',
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
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Расширения файла',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Расширения файла',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_MULTIPLE',
  'USER_TYPE_ID' => 'boolean',
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
    'en' => 'Множественное',
    'ru' => 'Множественное',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Множественное',
    'ru' => 'Множественное',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Множественное',
    'ru' => 'Множественное',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_MAX_FILES',
  'USER_TYPE_ID' => 'integer',
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
    'SIZE' => 20,
    'MIN_VALUE' => 0,
    'MAX_VALUE' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Максимально файлов',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Максимально файлов',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_HAS_OTHER_OPTION',
  'USER_TYPE_ID' => 'boolean',
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
    'en' => '',
    'ru' => 'Другая опция',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Другая опция',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_IS_REQUIRED',
  'USER_TYPE_ID' => 'boolean',
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
    'en' => 'Обязательное',
    'ru' => 'Обязательное',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Обязательное',
    'ru' => 'Обязательное',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Обязательное',
    'ru' => 'Обязательное',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_DESCRIPTION',
  'USER_TYPE_ID' => 'string',
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
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'ru' => 'Описание',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'ru' => 'Описание',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'ru' => 'Описание',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_FULL_DESCRIPTION',
  'USER_TYPE_ID' => 'string',
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
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'ru' => 'Полное описание',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'ru' => 'Полное описание',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'ru' => 'Полное описание',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_NAME',
  'USER_TYPE_ID' => 'string',
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
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Название',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Название',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_TYPE',
  'USER_TYPE_ID' => 'enumeration',
  'XML_ID' => 'UF_TYPE',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'Y',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'CAPTION_NO_VALUE' => '',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Тип поля',
    'ru' => 'Тип поля',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Тип поля',
    'ru' => 'Тип поля',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Тип поля',
    'ru' => 'Тип поля',
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
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'Текстовое поле (input)',
      'DEF' => 'Y',
      'SORT' => '500',
      'XML_ID' => 'text',
    ),
    1 => 
    array (
      'VALUE' => 'Выпадающий список (select)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'select',
    ),
    2 => 
    array (
      'VALUE' => 'Переключатель (toggle)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'toggle',
    ),
    3 => 
    array (
      'VALUE' => 'Файл (file)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'file',
    ),
    4 => 
    array (
      'VALUE' => 'Радио-кнопка (radio)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'radio',
    ),
    5 => 
    array (
      'VALUE' => 'Флажок (checkbox)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'checkbox',
    ),
    6 => 
    array (
      'VALUE' => 'Многострочное поле (textarea)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'textarea',
    ),
  ),
));
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_XML_ID',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => 'UF_XML_ID',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'Y',
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
    'en' => 'Символьный код',
    'ru' => 'Символьный код',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Символьный код',
    'ru' => 'Символьный код',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Символьный код',
    'ru' => 'Символьный код',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_ROWS',
  'USER_TYPE_ID' => 'integer',
  'XML_ID' => 'UF_ROWS',
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
    'MIN_VALUE' => 0,
    'MAX_VALUE' => 0,
    'DEFAULT_VALUE' => NULL,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Количество строк',
    'ru' => 'Количество строк',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Количество строк',
    'ru' => 'Количество строк',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Количество строк',
    'ru' => 'Количество строк',
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
            $helper->Hlblock()->saveField($hlblockId, array (
  'FIELD_NAME' => 'UF_LABEL',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '200',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'Y',
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
    'en' => 'Подпись (label)',
    'ru' => 'Подпись (label)',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Подпись (label)',
    'ru' => 'Подпись (label)',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Подпись (label)',
    'ru' => 'Подпись (label)',
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
        $helper->UserOptions()->saveHlblockForm($hlblockId, array (
  'Поля форм|edit1' => 
  array (
    'USER_FIELDS_ADD' => 'Добавить пользовательское поле',
    'ID' => 'ID',
    'UF_NAME' => 'Название',
    'UF_XML_ID' => 'Символьный код',
    'UF_TYPE' => 'Тип поля',
    'UF_LABEL' => 'Подпись (label)',
    'edit1_csection2' => 'Долнительные свойства поля',
    'UF_IS_REQUIRED' => 'Обязательное',
    'UF_PLACEHOLDER' => 'Подсказка в поле (placeholder)',
    'UF_DESCRIPTION' => 'Описание',
    'UF_FULL_DESCRIPTION' => 'Полное описание',
    'UF_DATA_TYPE' => 'Тип данных',
    'edit1_csection3' => 'Поля Выпадающего списка (select) и Радио-кнопки (radio',
    'UF_ITEMS' => 'Список опций для выбора (формат code::name)',
    'UF_HAS_OTHER_OPTION' => 'Другая опция',
    'edit1_csection1' => 'Свойства файла',
    'UF_MULTIPLE' => 'Несколько файлов',
    'UF_ACCEPT' => 'Расширения файла',
    'UF_MAX_FILES' => 'Максимально файлов',
    'edit1_csection4' => 'Свойства множественного поля (textarea)',
    'UF_ROWS' => 'Количество строк',
  ),
));
    $helper->UserOptions()->saveHlblockList($hlblockId, array (
  'page_size' => '10',
  'order' => 'asc',
  'by' => 'ID',
  'columns' => 
  array (
    0 => 'ID',
    1 => 'UF_NAME',
    2 => 'UF_XML_ID',
    3 => 'UF_DESCRIPTION',
    4 => 'UF_LABEL',
    5 => 'UF_PLACEHOLDER',
    6 => 'UF_DATA_TYPE',
    7 => 'UF_TYPE',
    8 => 'UF_IS_REQUIRED',
    9 => 'UF_ITEMS',
    10 => 'UF_ACCEPT',
    11 => 'UF_MULTIPLE',
    12 => 'UF_MAX_FILES',
    13 => 'UF_HAS_OTHER_OPTION',
  ),
));
    }
}
