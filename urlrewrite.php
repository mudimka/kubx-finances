<?php
$arUrlRewrite=array (
  22 => 
  array (
    'CONDITION' => '#^/disk/file/(?<unique_code>[0-9a-zA-Z]{20})/edit(\\/?)#',
    'RULE' => 'action=disk.api.unifiedlinkcontroller.edit&uniqueCode=$1&',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  23 => 
  array (
    'CONDITION' => '#^/disk/file/(?<unique_code>[0-9a-zA-Z]{20})(\\/?)#',
    'RULE' => 'action=disk.api.unifiedlinkcontroller.view&uniqueCode=$1&',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  15 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/openAttachedDocument#',
    'RULE' => 'action=disk.integration.flipchart.openAttachedDocument&attachedObjectId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  8 => 
  array (
    'CONDITION' => '#^/pub/booking/confirmation/([0-9a-z\\.]+)/#',
    'RULE' => 'hash=$1',
    'ID' => 'bitrix:booking.pub.confirm',
    'PATH' => '/pub/booking/confirmation.php',
    'SORT' => 100,
  ),
  16 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/openDocument#',
    'RULE' => 'action=disk.integration.flipchart.openDocument&fileId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  17 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/openAttached#',
    'RULE' => 'action=disk.integration.flipchart.openAttachedDocument&attachedObjectId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  10 => 
  array (
    'CONDITION' => '#^/bi/dashboard/detail/([0-9]+)/#',
    'RULE' => 'dashboardId=$1',
    'ID' => 'bitrix:biconnector.apachesuperset.dashboard.detail',
    'PATH' => '/bi/dashboard/detail/index.php',
    'SORT' => 100,
  ),
  20 => 
  array (
    'CONDITION' => '#^/shop/settings/permissions/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.store.entity.controller',
    'PATH' => '/shop/settings/permissions/index.php',
    'SORT' => 100,
  ),
  18 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/open#',
    'RULE' => 'action=disk.integration.flipchart.openDocument&fileId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  19 => 
  array (
    'CONDITION' => '#^/vote-result/([0-9a-z\\.]+)#',
    'RULE' => 'signedAttachId=$1',
    'ID' => NULL,
    'PATH' => '/vote-result/index.php',
    'SORT' => 100,
  ),
  7 => 
  array (
    'CONDITION' => '#^/booking/detail/([0-9]+)#',
    'RULE' => 'id=$1',
    'ID' => 'bitrix:booking.booking.detail',
    'PATH' => '/booking/detail.php',
    'SORT' => 100,
  ),
  24 => 
  array (
    'CONDITION' => '#^/task/comments/([0-9]+)#',
    'RULE' => 'taskId=$1',
    'ID' => NULL,
    'PATH' => '/tasks/comments.php',
    'SORT' => 100,
  ),
  21 => 
  array (
    'CONDITION' => '#^/bi/unused_elements/#',
    'RULE' => '',
    'ID' => 'bitrix:biconnector.apachesuperset.workspace_analytic.controller',
    'PATH' => '/bi/unused_elements/index.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/api/(.*)/(.*)/(.*)#',
    'RULE' => 'CLASS=$1&METHOD=$2',
    'ID' => 'legacy:api',
    'PATH' => '/local/api/index.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/calendar/open/#',
    'RULE' => '',
    'ID' => 'bitrix:calendar.open-events',
    'PATH' => '/calendar/open_events.php',
    'SORT' => 100,
  ),
  13 => 
  array (
    'CONDITION' => '#^/bi/statistics/#',
    'RULE' => '',
    'ID' => 'bitrix:biconnector.apachesuperset.workspace_analytic.controller',
    'PATH' => '/bi/statistics/index.php',
    'SORT' => 100,
  ),
  3 => 
  array (
    'CONDITION' => '#^/desktop/menu#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/desktop_menu/index.php',
    'SORT' => 100,
  ),
  11 => 
  array (
    'CONDITION' => '#^/bi/dataset/#',
    'RULE' => '',
    'ID' => 'bitrix:biconnector.apachesuperset.workspace_analytic.controller',
    'PATH' => '/bi/dataset/index.php',
    'SORT' => 100,
  ),
  12 => 
  array (
    'CONDITION' => '#^/bi/source/#',
    'RULE' => '',
    'ID' => 'bitrix:biconnector.apachesuperset.workspace_analytic.controller',
    'PATH' => '/bi/source/index.php',
    'SORT' => 100,
  ),
  14 => 
  array (
    'CONDITION' => '#^/vibe/edit/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/vibe/edit/index.php',
    'SORT' => 100,
  ),
  25 => 
  array (
    'CONDITION' => '#^vibe/edit/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => 'vibe/edit/index.php',
    'SORT' => 100,
  ),
  6 => 
  array (
    'CONDITION' => '#^/booking/#',
    'RULE' => '',
    'ID' => 'bitrix:booking',
    'PATH' => '/booking/index.php',
    'SORT' => 100,
  ),
  0 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
  5 => 
  array (
    'CONDITION' => '#^/crm/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.router',
    'PATH' => '/crm/index.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/hr/#',
    'RULE' => '',
    'ID' => 'bitrix:humanresources.start',
    'PATH' => '/hr/index.php',
    'SORT' => 100,
  ),
);
