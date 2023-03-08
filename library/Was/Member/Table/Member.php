<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
/**
 * @see Zend_Db_Expr
 */
require_once 'Zend/Db/Expr.php';
/**
 * @see Was_Member_Table_Exception
 */
require_once 'Was/Member/Table/Exception.php';

/**
 * 회원 테이블
 * @package    Was
 * @subpackage Was_Member
 */
class Was_Member_Table_Member extends Zend_Db_Table_Abstract {
    /**
     * 휴대전화 번호가 중복일 경우 에러 메세지
     * @var string
     */
    const DUPLICATE_PHONE_NUMBER = "중복된 휴대전화번호 입니다.";
    /**
     * 테이블명
     * @var string
     */
    protected $_name = 'member';
    
    /**
     * 새로운 회원을 등록함
     * 
     * @param array $contents 회원가입 정보
     *        array (
     *            'id'          => '아이디',
     *            'pw'          => '비밀번호',
     *            'name'        => '이름',
     *            'telNumber'   => '휴대전화번호',
     *            'email'       => '이메일',
     *            'position'    => '회원 등급'
     *        )
     * @return number 회원 기본키
     * @exception Was_Member_Table_Exception 이미 존재하는 핸드폰 번호인 경우
     */
    public function regist(array $contents) {
        //전화번호에서 '-' 문자 제거
        $contents['telNumber'] = str_replace('-', '', $contents['telNumber']);
        //이미 등록된 휴대전화 번호이면 throw 예외
        $select = $this->select();
        $select->from($this->getTableName(), array('count' => new Zend_Db_Expr("COUNT('telNumber')")))
               ->where('telNumber = ?', $contents['telNumber']);
        $resultRow = $this->getAdapter()->fetchRow($select);
        if ($resultRow['count'] > 0) {
            throw new Was_Member_Table_Exception(self::DUPLICATE_PHONE_NUMBER);
        }
        
        return $this->insert(array(
            'id'            => $contents['id'],
            'name'          => $contents['name'],
            'telNumber'     => $contents['telNumber'],
            'email'         => $contents['email'],
            'position'      => $contents['position'],
            'insertTime'    => new Zend_Db_Expr('NOW()'),
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ));
    }
    
    /**
     * 회원 정보를 업데이트함
     * @param array $contents
     *        array (
     *            'id'          => '아이디',
     *            'pw'          => '비밀번호',
     *            'name'        => '이름',
     *            'telNumber'   => '휴대전화번호',
     *            'email'       => '이메일'
     *        )
     * @param number 수정할 회원정보
     * @return 업데이트된 rowCount()
     * @exception Was_Member_Table_Exception 이미 존재하는 핸드폰 번호인 경우
     */
    public function modify(array $contents, $pk) {
        //전화번호에서 '-' 문자 제거
        $contents['telNumber'] = str_replace('-', '', $contents['telNumber']);
        
        //해당 회원의 현재 휴대전화 번호를 가져옴
        $telSelect = $this->select();
        $telSelect->from($this->getTableName(), array('telNumber'))->where("pk = ?", $pk);
        $row = $this->getAdapter()->fetchRow($telSelect);
        
        //현재 휴대전화 번호와 동일하지 않은 것으로 수정할 경우, 이미 사용중인 번호인지 체크. 사용중이면 throw 예외
        if ($contents['telNumber'] != $row['telNumber']) {
            //이미 등록된 휴대전화 번호이면 throw 예외
            $select = $this->select();
            
            $select->from($this->getTableName(), array('count' => new Zend_Db_Expr("COUNT('telNumber')")))
            ->where('telNumber = ?', $contents['telNumber']);
            $resultRow = $this->getAdapter()->fetchRow($select);
            if ($resultRow['count'] > 0) {
                throw new Was_Member_Table_Exception(self::DUPLICATE_PHONE_NUMBER);
            }
        }
        
        return $this->update(
            array(
                'id'            => $contents['id'],
                'name'          => $contents['name'],
                'telNumber'     => $contents['telNumber'],
                'email'         => $contents['email'],
                'updateTime'    => new Zend_Db_Expr('NOW()')
            ), "pk = {$pk}");
    }
    
    public function getTableName() {
        return $this->_name;
    }
}

