<?php
/**
 * @see Zend_Auth_Storage_Interface
 * @see Zend_Auth_Storage_Session
 * @see Was_Auth_Table_History
 */
require_once 'Zend/Auth/Storage/Interface.php';
require_once 'Zend/Auth/Storage/Session.php';
require_once 'Was/Auth/Table/History.php';

/**
 * 인증 스토리지 클래스
 *
 * @package    Was
 * @subpackage Was_Auth
 *
 */
class Was_Auth_Storage implements Zend_Auth_Storage_Interface {
    /**
     * 인증세션
     *
     * @var Zend_Auth_Storage_Session
     */
    protected $_session;

    /**
     * 현재 접속중인 인증내역 테이블
     *
     * @var Was_Auth_Table_Access
     */
    protected $_accessTable;

    /**
     * 접속이력 테이블
     *
     * @var Was_Auth_Table_History
     */
    protected $_historyTable;

    /**
     * 생성자
     *
     * @param Zend_Db_Table_Abstract 인증내역 테이블
     * @param Zend_Db_Table_Abstract 이력 테이블
     */
    public function __construct(Zend_Db_Table_Abstract $accessTable, $historyTable) {
        $this->_accessTable = $accessTable;
        $this->_historyTable = $historyTable;

        $this->_session = new Zend_Auth_Storage_Session('Was_Auth');
    }

    /*
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::read()
     */
    public function read() {
        if (!$this->isEmpty()) {
            return $this->_session->read();
        }
    }

    /*
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::isEmpty()
     */
    public function isEmpty() {
        // 세션이 비어있는 경우
        if ($this->_session->isEmpty()) {
            return true;
        }

        $sessionIdentity = $this->_session->read();
        $accessRowset = $this->_accessTable->find($sessionIdentity->id);
        if ($accessRowset->count() < 1) {
            return true;
        }

        return false;
    }

    /*
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::clear()
     */
    public function clear() {
        // 현재 접속중인 인증정보 제거
        $sessionIdentity = $this->_session->read();
        $accessRow = $this->_accessTable->find($sessionIdentity->id)->current();
        if ($accessRow) {
            $accessRow->delete();
        }
        // 세션 초기화
        $this->_session->clear();
    }

    /*
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::write()
     */
    public function write($contents) {
        /**
         * @see Was_Auth_Storage_Exception
         */
        require_once 'Was/Auth/Storage/Exception.php';

        // 이미 인증이 되있는 경우에, 인증을 시도할 경우
        if(!$this->isEmpty()) {
            $this->clear();
        }

        if(is_array($contents)) {
            $contents = (object) $contents;
        }

        if (!is_object($contents)) {
            throw new Was_Auth_Storage_Exception("Is not array or object.");
        }

        if (!isset($contents->id)) {
            throw new Was_Auth_Storage_Exception("not exist id property");
        }
        if (!isset($contents->sessionId)) {
            throw new Was_Auth_Storage_Exception("not exist sessionId property");
        }
        if (!isset($contents->remoteIp)) {
            throw new Was_Auth_Storage_Exception("not exist remoteIp property");
        }
        // 원격 아이피 없는경우
        if (is_null($contents->remoteIp) || !$contents->remoteIp) {
            $contents->remoteIp = $_SERVER['REMOTE_ADDR'];
        }
        
        // 세션아이디 없는경우
        if (is_null($contents->sessionId) || !$contents->sessionId) {
            $contents->sessionId = session_id();
        }
        // 세션에 기록
        $this->_session->write($contents);

        //로그아웃 안하고 창 닫기등, 세션은 없는데 로그인 시도할 경우 access 테이블에 레코드가 남아있으므로 이를 삭제 후 레코드 insert
        $accessRow = $this->_accessTable->find($contents->id)->current();
        if ($accessRow) {
            $accessRow->delete();
        }
        
        // 현재 접속자 테이블에 기록
        $this->_accessTable->newAccess($contents->id, $contents->remoteIp, $contents->sessionId);

        // 접속 이력 테이블에 기록
        $this->_historyTable->addHistory($contents->id, $contents->remoteIp, Was_Auth_Table_History::SUCCESS_MESSAGE);
    }
}

