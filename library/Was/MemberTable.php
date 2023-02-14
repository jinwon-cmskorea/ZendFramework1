<?php
/**
 * CMSKOREA BOARD
 *
 * @category My
 */
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
/**
 * 씨엠에스코리아 게시판 member table 클래스
 *
 * @category My
 */
class Was_MemberTable extends Zend_Db_Table_Abstract {
    /**
     * 접근할 테이블명
     * @var string
     */
    protected $_name = 'member';
    /**
     * member 테이블 기본키
     * @var string
     */
    protected $_primary = 'pk';
}