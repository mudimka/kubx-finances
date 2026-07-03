<?php

namespace Sprint\Migration;


class USER_GROUPS_25_06_25_20250625130047 extends Version
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

        $helper->UserGroup()->saveGroup('administrators',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '1',
  'ANONYMOUS' => 'N',
  'NAME' => 'Администраторы',
  'DESCRIPTION' => 'Полный доступ к управлению сайтом.',
  'SECURITY_POLICY' => 
  array (
    'SESSION_TIMEOUT' => 15,
    'SESSION_IP_MASK' => '255.255.255.255',
    'MAX_STORE_NUM' => 1,
    'STORE_IP_MASK' => '255.255.255.255',
    'STORE_TIMEOUT' => 4320,
    'CHECKWORD_TIMEOUT' => 60,
    'PASSWORD_LENGTH' => 10,
    'PASSWORD_UPPERCASE' => 'Y',
    'PASSWORD_LOWERCASE' => 'Y',
    'PASSWORD_DIGITS' => 'Y',
    'PASSWORD_PUNCTUATION' => 'Y',
    'LOGIN_ATTEMPTS' => 3,
  ),
));
        $helper->UserGroup()->saveGroup('everyone',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '2',
  'ANONYMOUS' => 'Y',
  'NAME' => 'Все пользователи (в том числе неавторизованные)',
  'DESCRIPTION' => 'Все пользователи, включая неавторизованных.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('EMPLOYEES_s1',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '3',
  'ANONYMOUS' => 'N',
  'NAME' => 'Kubx: Сотрудники',
  'DESCRIPTION' => 'Все сотрудники компании, зарегистрированные на сайте.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('RATING_VOTE',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '3',
  'ANONYMOUS' => 'N',
  'NAME' => 'Пользователи, имеющие право голосовать за рейтинг',
  'DESCRIPTION' => 'В эту группу пользователи добавляются автоматически.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('RATING_VOTE_AUTHORITY',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '4',
  'ANONYMOUS' => 'N',
  'NAME' => 'Пользователи имеющие право голосовать за авторитет',
  'DESCRIPTION' => 'В эту группу пользователи добавляются автоматически.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('PERSONNEL_DEPARTMENT',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '4',
  'ANONYMOUS' => 'N',
  'NAME' => 'Kubx: Отдел кадров',
  'DESCRIPTION' => 'Сотрудники отдела кадров.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('DIRECTION',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '5',
  'ANONYMOUS' => 'N',
  'NAME' => 'Kubx: Руководство',
  'DESCRIPTION' => 'Руководители компании.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('PORTAL_ADMINISTRATION_s1',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '6',
  'ANONYMOUS' => 'N',
  'NAME' => 'Kubx: Администрация портала',
  'DESCRIPTION' => 'Администраторы портала - пользователи с полными возможностями по управлению всеми сервисами портала.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('ADMIN_SECTION',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '7',
  'ANONYMOUS' => 'N',
  'NAME' => 'Работает в панели управления',
  'DESCRIPTION' => 'Включает пользователей, которые могут работать в "Панели управления" портала.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('CREATE_GROUPS',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '8',
  'ANONYMOUS' => 'N',
  'NAME' => 'Могут создавать рабочие группы',
  'DESCRIPTION' => 'Включает пользователей, которые могут создавать рабочие группы.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('MARKETING_AND_SALES',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '9',
  'ANONYMOUS' => 'N',
  'NAME' => 'Kubx: Маркетинг и продажи',
  'DESCRIPTION' => 'Сотрудники отделов маркетинга и продаж.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('SUPPORT',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '10',
  'ANONYMOUS' => 'N',
  'NAME' => 'Техподдержка',
  'DESCRIPTION' => 'Сотрудники IT отдела, обеспечивающие техническую поддержку.',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('CRM_SHOP_BUYER',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '10',
  'ANONYMOUS' => 'N',
  'NAME' => 'Все покупатели',
  'DESCRIPTION' => 'Группа пользователей, содержащая всех покупателей магазина',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('opt1',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Опт1',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
        $helper->UserGroup()->saveGroup('ORGANIZATION_MAIN_MANAGER',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Главный менеджер организации',
  'DESCRIPTION' => 'Группа пользователей, которые могут работать с организациями (создание, изменение, удаление)',
  'SECURITY_POLICY' => 
  array (
  ),
));
        $helper->UserGroup()->saveGroup('ORGANIZATION_MANAGER',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Менеджер организации',
  'DESCRIPTION' => 'Группа пользователей, которые могут работать с организациями (создание дочерних организаций, изменение, удаление)',
  'SECURITY_POLICY' => 
  array (
  ),
));
        $helper->UserGroup()->saveGroup('KUBX_VISITOR',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Просмотр админ. части KUBX',
  'DESCRIPTION' => 'Группа с правами для просмотра админской части',
  'SECURITY_POLICY' => 
  array (
  ),
));
        $helper->UserGroup()->saveGroup('COMPANY',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Юридические лица',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
        $helper->UserGroup()->saveGroup('CONTACT',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Физические лица',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
        $helper->UserGroup()->saveGroup('CRM_SHOP_MANAGER',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Менеджеры магазина',
  'DESCRIPTION' => 'Группа пользователей, которые могут работать с магазином',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('CRM_SHOP_ADMIN',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Администраторы магазина',
  'DESCRIPTION' => 'Группа пользователей, которые могут настраивать магазин',
  'SECURITY_POLICY' => NULL,
));
        $helper->UserGroup()->saveGroup('MAIL_INVITED',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '201',
  'ANONYMOUS' => 'N',
  'NAME' => 'Почтовые пользователи',
  'DESCRIPTION' => 'Пользователи, авторизуемые на портале по прямой ссылке из почтовых уведомлений',
  'SECURITY_POLICY' => NULL,
));
    }

}
