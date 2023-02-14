<?php
/**
 * CMSKOREA BOARD
 *
 * @category Was
 */
/**
 * @see Zend_Db_Adapter_Mysqli
 */
require_once 'Zend/Db/Adapter/Mysqli.php';
/**
 * @see Zend_Db_Selects
 */
require_once 'Zend/Db/Select.php';
/**
 * @see My_Member
 */
require_once __DIR__ . '/../My/Member.php';
/**
 * 씨엠에스코리아 게시판 회원 클래스
 *
 * @category Was
 */
class Was_Member {
    /**
     * Zend_Db_Adapter 객체
     *
     * @var Zend_Db_Adapter
     */
    protected $_db;
    
    /**
     * member 테이블 객체
     *
     * @var My_Member
     */
    protected $_table;
    
    /**
     * 생성자
     * Zend_Db 등을 초기화
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
        $this->_table = new My_Member(array('db' => $this->_db));
    }
    

    /**
     * 회원을 등록한다.
     * 동일한 아이디의 회원을 등록 할 수 없다.
     *
     * @throws Exception 동일한 아이디의 회원이 존재하는 경우
     *                   필수 항목을 입력하지 않았을 경우
     *                   입력 형식을 지키지 않았을 경우
     * @param array 회원가입정보
     *        array(
     *            'id'        => '아이디',
     *            'pw'        => '비밀번호',
     *            'name'      => '회원명',
     *            'telNumber' => '연락처',
     *            'email'     => '이메일'(필수아님)
     *        )
     * @return Cmskorea_Baord_Member
     */
    public function registMember(array $datas) {
        $manageArrays = array(
            'id'        => array(
                                "kor" => "아이디",
                                "reg" => "/^[A-Za-z0-9]+$/"),
            'pw'        => array(
                                "kor" => "비밀번호",
                                "reg" => "/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/"),
            'name'      => array(
                                "kor" => "이름",
                                "reg" => "/[가-힣A-Za-z]+$/"),
            'telNumber' => array(
                                "kor" => "휴대전화",
                                "reg" => "/^(010|011|016|017|018|019)-[0-9]{3,4}-[0-9]{4}$|^[0-9]{11}&/")
        );
        
        foreach ($manageArrays as $field => $value) {
            if (!$datas[$field]) {
                throw new Exception('필수 항목을 모두 입력해주세요.');
            }
            
            if ($value['reg']) {
                if (!preg_match($value['reg'], $datas[$field])) {
                    throw new Exception($value['kor'].' 입력 형식을 지켜주세요.');
                }
            }
        }
        //member 테이블에 insert할 정보 만들기
        $insertMember = array(
            'id'            => $this->_db->quote($datas['id']),
            'name'          => $this->_db->quote($datas['name']),
            'telNumber'     => $this->_db->quote(str_replace('-', '', $datas['telNumber'])),
            'email'         => (isset($datas['email']) && $datas['email']) ? $this->_db->quote($datas['email']) : '',
            'position'      => 3,
            'insertTime'    => date("Y-m-d H:i:s"),
            'updateTime'    => date("Y-m-d H:i:s")
        );
        $this->_table->insert($insertMember);
        //auth_identity 테이블에 insert할 정보 만들기
        $insertIdentity = array(
            'id'            => $this->_db->quote($datas['id']),
            'pw'            => $this->_db->quote($datas['pw']),
            'name'          => $this->_db->quote($datas['name']),
            'errorMessage'  => '',
            'insertTime'    => date("Y-m-d H:i:s")
        );
        $this->_db->insert('auth_identity')
        return $this;
    }

    /**
     * 아이디에 해당하는 회원정보를 리턴한다.
     *
     * @param string 회원아이디
     * @return array
     *         array(
     *            'pk'        => '회원고유번호',
     *            'id'        => '아이디',
     *            'name'      => '회원명',
     *            'telNumber' => '연락처'
     *        )
     */
    public function getMember($id) {
        $fId = mysqli_real_escape_string($this->_mysqli, $id);
        $sql = "SELECT pk, id, name, telNumber FROM member where id='{$fId}'";
        $res = mysqli_query($this->_mysqli, $sql);
        if (!$res) {
            return array();
        }
        
        $row = mysqli_fetch_assoc($res);
        if ($row == NULL) {
            return array();
        }
        
        return $row;
    }

}

