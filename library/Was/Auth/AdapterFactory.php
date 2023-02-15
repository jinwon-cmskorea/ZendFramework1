<?php
/**
 * @see Was_Auth_Adapter
 */
require_once 'Was/Auth/Adapter.php';

/**
 * 인증 아답타 생성기
 *
 * @package    Was
 * @subpackage Was_Auth
 *
 */
class Was_Auth_AdapterFactory {
    /**
     *
     * @return Was_Auth_Adapter
     */
    static public function getAdapter(Was_Auth_Table_Identity $table) {
        $adapter = new Was_Auth_Adapter($table->getAdapter());

        $adapter->setIdentityTable($table);
        $adapter->setTableName($table->getTableName());
        $adapter->setIdentityColumn('id');
        $adapter->setCredentialColumn('pw');
        $adapter->setCredentialTreatment('? AND authable = 1');


        return $adapter;
    }
}

