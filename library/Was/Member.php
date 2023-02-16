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
    const MASTER = 1;
    /**
     * 관리자 등급
     * @var int
     */
    const MANAGER = 2;
    /**
     * 일반회원 등급
     * @var int
     */
    const MEMBER = 3;
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
            $this->_inputValidate($contents);
        } catch (Was_Member_Exception $e) {
            throw new Was_Member_Exception($e->getMessage());
        }
        
        $contents['position'] = self::MEMBER;
        return $this->_memberTable->regist($contents);
    }
    
    /**
     * 등록된 회원의 id를 찾는다
     * @param array $contents
     *        array(
     *            'name'        => '이름',
     *            'telNumner'   => '휴대전화번호'
     *        )
     * @return 성공시, id 정보 반환 | 실패시 빈 값 반환
     */
    public function searchId($contents) {
        str_replace('-', '', $contents['telNumber']);
        $row = $this->_memberTable->fetchRow($this->_memberTable->select()->where("name = '{$contents['name']}'")->where("telNumber = '{$contents['telNumber']}'"));
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
     *            'pk'          => 회원번호
     *            'id'          => '아이디',
     *            'pw'          => '비밀번호',
     *            'name'        => '이름',
     *            'telNumber'   => '휴대전화번호',
     *            'email'       => '이메일'
     *        )
     * @return 예외 발생시, 해당 에러문 반환 | 성공시 수정된 rowCount() 반환
     */
    public function modifyMember(array $contents) {
        /*
         * 각 항목에 대해 validete 수행
         * 형식에 안맞으면 예외 에러문 리턴
         */
        try {
            $this->_inputValidate($contents);
        } catch (Was_Member_Exception $e) {
            throw new Was_Member_Exception($e->getMessage());
        }
        
        return $this->_memberTable->modify($contents);
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
     */
    protected function _inputValidate(array $contents) {
        $idValidator = new Zend_Validate_Alnum();
        if (!$idValidator->isValid($contents['id']) || !isset($contents['id'])) {
            throw new Was_Member_Exception('The ID format is not correct.');
        }
        
        $pwValidator = new Zend_Validate_Regex('/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/');
        if (!$pwValidator->isValid($contents['pw']) || !isset($contents['pw'])) {
            throw new Was_Member_Exception('The Password format is not correct.');
        }
        
        $nameValidator = new Zend_Validate_Regex('/[가-힣A-Za-z]+$/');
        if (!$nameValidator->isValid($contents['name']) || !isset($contents['name'])) {
            throw new Was_Member_Exception('The Name format is not correct.');
        }
        
        $phoneValidator = new Zend_Validate_Regex('/^(010|011|016|017|018|019)-[0-9]{4}-[0-9]{4}$|^[0-9]{11}&/');
        if (!$phoneValidator->isValid($contents['telNumber']) || !isset($contents['telNumber'])) {
            throw new Was_Member_Exception('The telNumber format is not correct.');
        }
        
        $emailValidator = new Zend_Validate_EmailAddress();
        if (!$emailValidator->isValid($contents['email'])) {
            throw new Was_Member_Exception('The Email format is not correct.');
        }
    }
}

