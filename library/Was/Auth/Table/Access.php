<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
require_once 'Zend/Db/Expr.php';
/**
 * 현재 접속중인 인증내역 테이블
 *
 * @package    Was
 * @subpackage Was_Auth
 */
class Was_Auth_Table_Access extends Zend_Db_Table_Abstract {
    /**
     * 테이블 명
     * @var string
     */
    protected $_name = 'auth_access';

    /**
     * 새로운 접속이력을 기록한다.
     *
     * @param string 인증아이디
     * @param string 접속아이피
     * @param string 세션아이디
     * @return string 인증아이디
     */
    public function newAccess($identityId, $remoteIp = null, $sessionId = null) {
        // 원격 아이피 없는경우
        if (is_null($remoteIp)) {
            $remoteIp = $_SERVER['REMOTE_ADDR'];
        }

        // 세션아이디 없는경우
        if (is_null($sessionId)) {
            $sessionId = session_id();
        }

        return $this->insert(array(
            'identityId' => $identityId,
            'remoteIp'      => $remoteIp,
            'sessionId'     => $sessionId,
            'authTime'      => new Zend_Db_Expr('NOW()'),
            'accessTime'    => new Zend_Db_Expr('NOW()')
        ));
    }
}

