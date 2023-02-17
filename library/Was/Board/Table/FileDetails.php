<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
/**
 * 게시글 파일 내용 관리 테이블
 *
 * @package    Was
 * @subpackage Was_Board_Table
 */
class Was_Board_Table_FileDetails extends Zend_Db_Table_Abstract {
    /**
     * 테이블명
     * @var string
     */
    protected $_name = 'file_details';
    
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

