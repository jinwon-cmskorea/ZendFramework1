<?php
/**
 * @see Was_Member_Table_Member
 */
require_once 'Was/Member/Table/Member.php';

/**
 * @see Was_Member_Exception
 */
require_once 'Was/Member/Exception.php';
/**
 * @see Was_Member_Table_Exception
 */
require_once 'Was/Member/Table/Exception.php';
/**
 * @see Zend_Validate
 * @see Zend_Validate_Alnum
 * @see Zend_Validate_Regex
 * @see Zend_Validate_EmailAddress
 */
require_once 'Zend/Validate.php';
require_once 'Zend/Validate/Alnum.php';
require_once 'Zend/Validate/Regex.php';
require_once 'Zend/Validate/EmailAddress.php';

/**
 * 회원 관리
 *
 * @package Was
 */
class Was_Member {
    /**
     * 최종관리자 등급
     * @var int
     */
    const POWER_MASTER = 1;
    /**
     * 관리자 등급
     * @var int
     */
    const POWER_MANAGER = 2;
    /**
     * 일반회원 등급
     * @var int
     */
    const POWER_MEMBER = 3;
    /**
     * 회원 테이블
     * @var Was_Member_Table_Member
     */
    protected $_memberTable;
    
    /**
     * 생성자
     */
    public function __construct(Was_Member_Table_Member $memberTable) {
        $this->_memberTable = $memberTable;
    }
    
    /**
     * 회원을 등록한다
     * @param array $contents 회원가입 정보
     *        array (
     *            'id'          => '아이디',
     *            'pw'          => '비밀번호',
     *            'name'        => '이름',
     *            'telNumber'   => '휴대전화번호',
     *            'email'       => '이메일'
     *        )
     * @return 예외 발생시, 해당 에러문 반환 | 성공시 insert된 pk 반환
     */
    public function registMember(array $contents) {
        /*
         * 각 항목에 대해 validete 수행
         * 형식에 안맞으면 예외 에러문 리턴
         */
        try {
            if (!$this->_isValid($contents)) {
                return false;
            }
            
            $contents['position'] = self::POWER_MEMBER;
            return $this->_memberTable->regist($contents);
        } catch (Was_Member_Exception $e) {
            throw new Was_Member_Exception($e->getMessage());
        } catch (Was_Member_Table_Exception $e) {
            throw new Was_Member_Table_Exception($e->getMessage());
        }
    }
    
    /**
     * 등록된 회원의 id를 찾는다
     * @param string 이름
     * @param string 휴대전호번호
     * @return 성공시, id 정보 반환 | 실패시 빈 값 반환
     */
    public function searchId($name, $telNumber) {
        $telNumber = str_replace('-', '', $telNumber);
        //id 찾기
        $select = $this->_memberTable->select();
        $select->where('name = ?', $name)->where('telNumber = ?', $telNumber);
        $row = $this->_memberTable->fetchRow($select);
        if ($row) {
            return $row->id;
        } else {
            return '';
        }
    }
    
    /**
     * 회원 정보를 수정한다
     * @param array $contents
     *        array (
     *            'id'          => '아이디',
     *            'pw'          => '비밀번호',
     *            'name'        => '이름',
     *            'telNumber'   => '휴대전화번호',
     *            'email'       => '이메일'
     *        )
     * @param number 수정할 회원정보
     * @return 예외 발생시, 해당 에러문 반환 | 성공시 수정된 rowCount() 반환
     */
    public function modifyMember(array $contents, $pk) {
        /*
         * 각 항목에 대해 validete 수행
         * 형식에 안맞으면 예외 에러문 리턴
         */
        try {
            if (!$this->_isValid($contents)) {
                return false;
            }
            
            return $this->_memberTable->modify($contents, $pk);
        } catch (Was_Member_Exception $e) {
            throw new Was_Member_Exception($e->getMessage());
        } catch (Was_Member_Table_Exception $e) {
            throw new Was_Member_Table_Exception($e->getMessage());
        }
    }
    /**
     * 회원 정보를 가져온다
     * @param string 아이디
     * @return array 회원 정보 | 존재하지 않는 회원의 경우 빈 배열 반환
     */
    public function getMember($id) {
        $select = $this->_memberTable->select();
        $select->where('id = ?', $id);
        $row = $this->_memberTable->fetchRow($select);
        //존재하지않는 회원인 경우 빈 배열 반환
        if (is_null($row)) {
            return array();
        }
        
        $memberInfo = array(
            'id'        => $row->id,
            'name'      => $row->name,
            'telNumber' => preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3" ,$row->telNumber),
            'email'     => $row->email
        );
        
        return $memberInfo;
    }
    
    /**
     * validate 를 수행한다
     * @param array $contents 회원가입 정보
     *        array (
     *            'id'          => '아이디',
     *            'pw'          => '비밀번호',
     *            'name'        => '이름',
     *            'telNumber'   => '휴대전화번호',
     *            'email'       => '이메일'
     *        )
     * @throws Was_Member_Exception
     * @return 비어있는 contents가 있으면 false, 모든 Validate 통과하면 true 반환
     */
    protected function _isValid(array $contents) {
        if (isset($contents['id']) && $contents['id']) {
            $idValidator = new Zend_Validate_Alnum();
            if (!$idValidator->isValid($contents['id'])) {
                throw new Was_Member_Exception('The ID format is not correct.');
            }
        } else {
            return false;
        }
        
        if (isset($contents['pw'])  && $contents['pw']) {
            $pwValidator = new Zend_Validate_Regex('/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/');
            if (!$pwValidator->isValid($contents['pw'])) {
                throw new Was_Member_Exception('The Password format is not correct.');
            }
        } else {
            return false;
        }
        
        if (isset($contents['name']) && $contents['name']) {
            $nameValidator = new Zend_Validate_Regex('/[가-힣A-Za-z]+$/');
            if (!$nameValidator->isValid($contents['name'])) {
                throw new Was_Member_Exception('The Name format is not correct.');
            }
        } else {
            return false;
        }
        
        if (isset($contents['telNumber']) && $contents['telNumber']) {
            $phoneValidator = new Zend_Validate_Regex('/^(010|011|016|017|018|019)-[0-9]{3,4}-[0-9]{4}$|^[0-9]{11}&/');
            if (!$phoneValidator->isValid($contents['telNumber'])) {
                throw new Was_Member_Exception('The telNumber format is not correct.');
            }
        } else {
            return false;
        }
        
        if (isset($contents['email']) && $contents['email']) {
            $emailValidator = new Zend_Validate_EmailAddress();
            if (!$emailValidator->isValid($contents['email'])) {
                throw new Was_Member_Exception('The Email format is not correct.');
            }
        }
        
        return true;
    }
}

