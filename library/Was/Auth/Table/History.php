<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';

class Was_Auth_Table_History extends Zend_Db_Table_Abstract {
    /**
     * auth_history 테이블 이름
     *
     * @var string
     */
    protected $_name = 'auth_history';
}

