<?php
/**
 * @see Zend_Auth_Storage_Interface
 */
require_once 'Zend/Auth/Storage/Interface.php';

class Was_Auth_Storage implements Zend_Auth_Storage_Interface {
    /**
     * 접속중인 사용자 테이블
     * 
     * @var Was_Auth_Table_Access
     */
    protected $_accessTable;
    
    /**
     * 접속 이력 테이블
     * 
     * @var Was_Auth_Table_History
     */
    protected $_historyTable;
    
    /**
     * @var string
     */
    protected $_accessPk;
    
    /**
     * 
     * @var string
     */
    protected $_session;
    
    /**
     * @var string
     */
    const LOGIN_SUCCESS = "로그인 성공";
    
    /**
     * @var string
     */
    const LOGIN_FAIL = "로그인 실패";
    
    /**
     * 생성자
     * 
     * @param Was_Auth_Table_Access $accessTable
     * @param Was_Auth_Table_History $historyTable
     * @param string $accessPk
     * @param string $session
     */
    public function __construct(Was_Auth_Table_Access $accessTable, Was_Auth_Table_History $historyTable, $accessPk = null, $session = null) {
        /**
         * @see Zend_Session
         */
        require_once 'Zend/Session.php';
        
        if (!Zend_Session::isStarted()) {
            Zend_Session::start();
        }
        
        $this->_accessTable = $accessTable;
        $this->_historyTable = $historyTable;
        $this->_accessPk = $accessPk;
        $this->_session = is_null($session) ? session_id() : $session;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::read()
     */
    public function read()
    {}

    public function isEmpty()
    {}

    public function clear()
    {}

    public function write($contents)
    {}
}

