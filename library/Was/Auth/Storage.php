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
    public function read() {
        //매개변수로 넣은 기본키의 row 객체 반환
        return $this->_accessTable->find($this->_accessPk)->current();
    }

    /**
     * 
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::isEmpty()
     */
    public function isEmpty() {
        //매개변수로 기본키를 넣어, 갯수 반환
        return $this->_accessTable->find($this->_accessPk)->count() > 0 ? false : true;
    }

    /**
     * 검색한 row객체 delete, pk 초기화
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::clear()
     */
    public function clear() {
        $access = $this->read();
        
        if ($access) {
            $access->delete();
            $this->_accessPk = null;
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see Zend_Auth_Storage_Interface::write()
     * 
     * @param mix identity 정보
     */
    public function write($contents) {
        if (!array_key_exists('identityId', $contents) || is_null($contents['identityId'] || empty($contents['identityId']))) {
            /**
             * @see Was_Auth_Exception
             */
            require_once 'Was/Auth/Exception.php';
            throw new Was_Auth_Exception("identityId 가 비어있습니다.");
        }
        //만약 입력된 remoteIp가 있으면 그걸로 설정, 없으면 $_SERVER['REMOTE_ADDR'] 로 설정
        $remoteAddr = (isset($contents['remoteIp']) && !empty($contents['remoteIp'])) ? $contents['remoteIp'] : $_SERVER['REMOTE_ADDR'];
        
        //만약 입력된 session 정보가 있으면 그걸로 설정, 없으면 기본 sessionId로 설정
        $this->_session = (isset($contents['sessionId']) && !empty($contents['sessionId'])) ? $contents['sessionId'] : $this->_session;
        
        $contents['remoteIp'] = $remoteAddr;
        $contents['sessionId'] = $this->_session;
        $contents['authTime'] = new Zend_Db_Expr('NOW()');
        $contents['accessTime'] = new Zend_Db_Expr('NOW()');
        
        //db_table 의 insert 메소드 수행 후, 해당 레코드의 pk를 리턴받음
        $this->_accessPk = $this->_accessTable->insert($contents);
        
        if ($this->_accessPk) {
            $this->_historyTable->insert(array(
                'identityId'    => $contents['identityId'],
                'remoteIp'      => $contents['remoteIp'],
                'authTime'      => new Zend_Db_Expr('NOW()'),
                'result'        => self::LOGIN_SUCCESS
            ));
        } else {
            $this->_accessPk = 0;
            
            $this->_historyTable->insert(array(
                'identityId'    => $contents['identityId'],
                'remoteIp'      => $contents['remoteIp'],
                'authTime'      => new Zend_Db_Expr('NOW()'),
                'result'        => self::LOGIN_FAIL
            ));
        }
        
        return $this->_accessPk;
    }
}

