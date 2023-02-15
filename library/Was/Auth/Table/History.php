<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
/**
 * @see Was_Auth_Table_Exception
 */
require_once 'Was/Auth/Table/Exception.php';
/**
 * 접속이력 테이블
 *
 * @package    Was
 * @subpackage Was_Auth
 */
class Was_Auth_Table_History extends Zend_Db_Table_Abstract {
    /**
     * 인증성공 메시지
     *
     * @var string
     */
    const SUCCESS_MESSAGE = '성공';

    /**
     * 인증실패 메시지
     *
     * @var string
     */
    const FAIL_MESSAGE = '실패';

    /**
     * 테이블명
     * @var string
     */
    protected $_name = 'auth_history';

    /**
     * 인증이력하나를 기록한다.
     *
     * @param string  인증아이디
     * @param string  접속아이피
     * @param string  인증결과
     * @return number history 키
     */
    public function addHistory($identityId, $remoteIp = null, $result = null) {
        // 원격 아이피 없는경우
        if (is_null($remoteIp)) {
            $remoteIp = $_SERVER['REMOTE_ADDR'];
        }

        if (is_null($result)) {
            $result = self::FAIL_MESSAGE;
        }

        if (($result != self::SUCCESS_MESSAGE) && ($result != self::FAIL_MESSAGE)) {
            throw new Was_Auth_Table_Exception('Invalid value.');
        }

        return $this->insert(array(
            'identityId'    => $identityId,
            'remoteIp'      => $remoteIp,
            'authTime'      => new Zend_Db_Expr('NOW()'),
            'result'        => $result
        ));
    }
}

