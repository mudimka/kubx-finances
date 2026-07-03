<?php

namespace Sprint\Migration;


class ELEMENTS_HB_FORM_FIELDS_14_07_25_20250714164904 extends Version
{
    protected $author = "admin";

    protected $description   = "";

    protected $moduleVersion = "5.3.3";

    /**
     * @throws Exceptions\MigrationException
     * @throws Exceptions\RestartException
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $this->getExchangeManager()
             ->HlblockElementsImport()
             ->setLimit(20)
             ->execute(function ($item) {
                 $this->getHelperManager()
                      ->Hlblock()
                      ->saveElementByXmlId(
                          $item['hlblock_id'],
                          $item['fields']
                      );
             });
    }

    /**
     * @throws Exceptions\MigrationException
     * @throws Exceptions\RestartException
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function down()
    {
        $this->getExchangeManager()
             ->HlblockElementsImport()
             ->setLimit(20)
             ->execute(function ($item) {
                 $this->getHelperManager()
                      ->Hlblock()
                      ->deleteElementByXmlId(
                          $item['hlblock_id'],
                          $item['fields']['UF_XML_ID']
                      );
             });
    }


}
