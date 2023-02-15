<?php
/**
 * @see Zend_Auth_Adapter_DbTable
 */
require_once 'Zend/Auth/Adapter/DbTable.php';

/**
 * 인증 아답타
 *
 * @package    Was
 * @subpackage Was_Auth
 */
class Was_Auth_Adapter extends Zend_Auth_Adapter_DbTable {
    /**
     * 인증테이블
     * @var Zend_Db_Table_Abstract
     */
    protected $_identityTable;

    /*
     * {@inheritDoc}
     * @see Zend_Auth_Adapter_DbTable::setCredential()
     */
    public function setCredential($credential) {
        $this->_credential = md5($credential);
        return $this;
    }

    /**
     * 인증테이블을 설정한다.
     *
     * @param Zend_Db_Table_Abstract 인증테이블
     * @return Was_Auth_Adapter
     */
    public function setIdentityTable(Zend_Db_Table_Abstract $table) {
        $this->_identityTable = $table;
        return $this;
    }

    /**
     * 인증테이블을 리턴한다.
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getIdentityTable() {
        return $this->_identityTable;
    }
}
