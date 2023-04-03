<?php
/**
 * @see Was_Board_Table_Abstract
 */
require_once 'Was/Board/Table/Abstract.php';
/**
 * 게시글 파일 관리 테이블
 *
 * @package    Was
 * @subpackage Was_Board_Table
 */
class Was_Board_Table_File extends Was_Board_Table_Abstract {
    /**
     * 테이블명
     * @var string
     */
    protected $_name = 'file';
    
    protected $_dependentTables = array(
        'Was_Board_Table_FileDetails'
    );
    
    protected $_referenceMap = array(
        'Board' => array(
            'columns'           => 'boardPk',
            'refTableClass'     => 'Was_Board_Table_Board',
            'refColumns'        => 'pk',
            'onDelete'          => self::CASCADE
        )
    );
}

