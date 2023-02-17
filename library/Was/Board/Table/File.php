<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
/**
 * 게시글 파일 관리 테이블
 *
 * @package    Was
 * @subpackage Was_Board_Table
 */
class Was_Board_Table_File extends Zend_Db_Table_Abstract {
    /**
     * 테이블명
     * @var string
     */
    protected $_name = 'file';
}

