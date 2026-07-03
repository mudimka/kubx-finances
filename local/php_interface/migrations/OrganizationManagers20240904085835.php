<?php

namespace Sprint\Migration;


class OrganizationManagers20240904085835 extends Version
{
    protected $description = "Миграции для групп пользователей - менеджеры организаций";

    protected $moduleVersion = "4.6.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();

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
    }

    public function down()
    {
        //your code ...
    }
}
