<?php
/**
 * CMSKOREA BOARD
 *
 * @category Was
 */

/**
 * @see Zend_Auth
 */
require_once 'Zend/Auth.php';
/**
 * @see Zend_Auth_Adapter_DbTable
 */
require_once 'Zend/Auth/Adapter/DbTable.php';
/**
 * @see Zend_Db_Adapter_Mysqli
 */
require_once 'Zend/Db/Adapter/Mysqli.php';
/**
 * @see Zend_Db_Selects
 */
require_once 'Zend/Db/Select.php';
/**
 * 씨엠에스코리아 사용자 인증 클래스
 *
 * @category Cmskorea
 * @package  Board
 */
class Was_Auth extends Zend_Auth {
    
    /**
     * Zend_Auth_Adapter_DbTable 객체
     * @var Zend_Auth_Adapter_DbTable
     */
    protected $_adapter = null;
    
    /**
     * Zend_Db_Adapter_Mysqli 객체
     * @var Zend_Db_Adapter_Mysqli
     */
    protected $_db = null;
    /**
     * 생성자
     * Zend_Db, Auth_Adapter_DbTable 등을 초기화
     * @param array DB 연결정보
     *        array (
     *            'host'     => '호스트 정보',
     *            'username' => 'db 계정 id',
     *            'password' => 'db 계정 pw',
     *            'dbname'   => 'db 이름'
     *        )
     * @return void
     */
    public function __construct(array $dbConfig) {
        $this->_db = Zend_Db::factory('Mysqli', $dbConfig);
        $this->_adapter = new Zend_Auth_Adapter_DbTable($this->_db, 'auth_identity', 'id', 'pw');
    }
    
    /**
     * 로그인 인증
     *  - 로그인에 성공한 경우 세션에 로그인에 성공한 회원정보를 보관한다.
     *
     * @param string 아이디
     * @param string 비밀번호
     * @return array 로그인 성공 시 빈값|로그인 불능 시 불능메시지가 들어있는 배열 반환
     */
    public function login($id, $pw, $remoteIp) {
        //아이디, 비밀번호 확인
        $this->_adapter->setIdentity($id);
        $this->_adapter->setCredential(md5($pw));
        $result = $this->_adapter->authenticate();
        //로그인 실패시, 실패 카운트 증가 및 에러 메세지 반환
        if (!$result->isValid()) {
            //실패 카운트 증가
            $errorMsg = "아이디 또는 비밀번호가 일치하지 않습니다.";
            $this->_db->update('auth_identity', array('errorCount' => new Zend_Db_Expr('errorCount + 1')), "id = '{$id}'");
            
            //실패 카운트 확인
            $select = $this->_db->select()->from('auth_identity', array('errorCount'))->where("id = '{$id}'");
            $result = $select->query()->fetchAll();
            if (isset($result[0]['errorCount']) && $result[0]['errorCount'] >= 3) {
                $errorMsg .= "남은 로그인 횟수는 " . (5 - $result[0]['errorCount']) . " 입니다.";
            }
            if (isset($result[0]['errorCount']) && $result[0]['errorCount'] == 5) {
                $errorMsg = "로그인을 5번 이상 실패하셨습니다. 아이디가 잠금 처리됐습니다.";
                $this->_db->update('auth_identity', array('authable' => 0), "id = '{$id}'");
            }
            $this->_db->update('auth_identity', array('errorMessage' => $errorMsg), "id = '{$id}'");
            return array($errorMsg);
        }
        
        //auth_access 테이블에 레코드 추가
        $data = array(
            'identityId'    => $id,
            'remoteIp'      => $remoteIp,
            'sessionId'     => Zend_Session::getId(),
            'authTime'      => date("Y-m-d H:i:s"),
            'accessTime'    => date("Y-m-d H:i:s")
        );
        $this->_db->insert('auth_access', $data);
        $this->_db->update('auth_identity', array('errorCount' => '0', 'errorMessage' => ''), "id = '{$id}'");
        
        return array();
    }

    /**
     * 로그아웃
     *  - 로그인 시 생성된 세션을 파괴한다.
     * @param array 세션 정보
     *        array(
     *            'identityId' => 아이디,
     *            'remoteIp'   => 접속IP,
     *            'sessionId'  => 세션아이디,
     *            'autoTime'   => 인증시간,
     *            'accessTime' => 활동시간
     *        );
     * @return boolean
     */
    public function logout($session) {
        
        $temp = $session->info;
        $this->_db->delete('auth_access', "identityId = '{$temp['id']}'");
        unset($session->info);

        return true;
    }
    
    /**
     * 로그인 시도
     *  - 모든 로그인 시도를 auth_history에 쌓인다
     *
     * @param string 아이디
     * @param string 비밀번호
     * @param 접속 ip
     * @return int 마지막으로 추가된 레코드 pk
     */
    public function addAuthHistory($id, $pw, $remoteIp) {
        $data = array(
            'identityId'    => $id,
            'remoteIp'      => $remoteIp,
            'authTime'      => date("Y-m-d H:i:s")
        );
        
        //아이디, 비밀번호 확인
        $this->_adapter->setIdentity($id);
        $this->_adapter->setCredential(md5($pw));
        $result = $this->_adapter->authenticate();
        
        //로그인 성공 유무 메세지를 result 인덱스에 값 저장
        $result-> isValid() ? $data['result'] = "로그인 성공" : $data['result'] = "로그인 실패";
        //로그인 결과를 auth_history 테이블에 저장
        $this->_db->insert('auth_history', $data);
        //마지막으로 추가된 레코드의 pk를 리턴
        return $this->_db->lastInsertId();
    }
    
    /**
     * 로그인 가능 여부
     *  - auth_identity 의 authable 을 확인하여 로그인 가능 여부 확인
     *
     * @param string 아이디
     * @return 로그인 가능하면 void | 불가능하면 에러메세지 출력
     */
    public function checkAuthable($id) {
        $select = $this->_db->select()->from('auth_identity', array('authable', 'errorMessage'))->where("id = '{$id}'");
        $result = $select->query()->fetchAll();
        if ($result[0]['authable'] == 0) {
            return ($result[0]['errorMessage']);
        }
    }
}

