<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
/**
 * 인증 테이블
 *
 * @package    Was
 * @subpackage Was_Auth
 */
class Was_Auth_Table_Identity extends Zend_Db_Table_Abstract {
    /**
     * 테이블명
     * @var string
     */
    protected $_name = 'auth_identity';

    /**
     * 테이블명을 리턴한다.
     *
     * @param boolean 스키마설정여부
     * @return string
     */
    public function getTableName($schema = false) {
        $tablename = $this->_name;

        if ($schema) {
            $dbConfig = $this->getAdapter()->getConfig();

            if (isset($dbConfig['dbname'])) {
                $tablename = $dbConfig['dbname'] . "." . $tablename;
            }
        }

        return $tablename;
    }
}

