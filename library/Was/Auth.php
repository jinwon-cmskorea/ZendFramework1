<?php
/**
 * @see Zend_Auth
 * @see Was_Auth_Table_Identity
 * @see Was_Auth_Table_Access
 * @see Was_Auth_Table_History
 * @see Was_Auth_Storage
 * @see Zend_Auth_Result
 * @see Zend_Auth_Exception
 */
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Result.php';
require_once 'Was/Auth/Table/Identity.php';
require_once 'Was/Auth/Table/Access.php';
require_once 'Was/Auth/Table/History.php';
require_once 'Was/Auth/Storage.php';
require_once 'Was/Auth/Exception.php';

/**
 * 인증관리
 *
 * @package Was
 */
class Was_Auth extends Zend_Auth {
    /**
     * 접속중 테이블
     * @var Was_Auth_Table_Access
     */
    protected $_accessTable;
    /**
     * 이력테이블
     * @var Was_Auth_Table_History
     */
    protected $_historyTable;

    /**
     * Storage 테이블 미설정 오류코드
     * @var integer
     */
    const NOT_SET_STORAGE_TABLE = 99;

    /**
     * 싱글턴
     * @return Was_Auth
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /*
     * {@inheritDoc}
     * @see Zend_Auth::authenticate()
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter) {
        $result = $adapter->authenticate();

        try {
            $storage = $this->getStorage();
        } catch (Was_Auth_Exception $e) {
            return new Zend_Auth_Result(self::NOT_SET_STORAGE_TABLE, $result->getIdentity(), array('Storage required table is not set.'));
        }

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        $identityTable = $adapter->getIdentityTable();

        // 로그인성공
        if ($result->isValid()) {
            $identityData = $identityTable->find($result->getIdentity())->current()->toArray();
            $identityData['sessionId'] = '';
            $identityData['remoteIp'] = '';
            $storage->write($identityData);
        }

        return $result;
    }

    /*
     * {@inheritDoc}
     * @see Zend_Auth::getStorage()
     */
    public function getStorage() {
        if (null === $this->_storage) {
            // storage 테이블 미설정 시 예외
            if (!is_a($this->_accessTable, 'Was_Auth_Table_Access')
            || !is_a($this->_historyTable, 'Was_Auth_Table_History')) {
                throw new Was_Auth_Exception('Storage required table is not set.');
            }
            $this->setStorage(new Was_Auth_Storage($this->_accessTable, $this->_historyTable));
        }

        return $this->_storage;
    }

    /**
     * 접속중관리 테이블 설정한다.
     *
     * @param Was_Auth_Table_Access 접속중관리 테이블
     * @return Was_Auth
     */
    public function setAccessTable(Was_Auth_Table_Access $accessTable) {
        $this->_accessTable = $accessTable;

        return $this;
    }

    /**
     * 이력테이블을 설정한다.
     *
     * @param Was_Auth_Table_History 이력테이블
     * @return Was_Auth
     */
    public function setHistoryTable(Was_Auth_Table_History $historyTable) {
        $this->_historyTable = $historyTable;

        return $this;
    }
}

