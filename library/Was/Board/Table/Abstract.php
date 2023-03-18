<?php
/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
/**
 * Was_Board 의  Db Table Abstract
 * @package Was
 * @subpackage Was_Board_Table
 */
abstract class Was_Board_Table_Abstract extends Zend_Db_Table_Abstract {
    /**
     * 테이블명을 리턴한다.
     *
     * @param boolean 스키마설정여부
     * @return string
     */
    public function getTableName($schema = false) {
        $tableName = $this->_name;
        
        if ($schema) {
            $dbConfig = $this->getAdapter()->getConfig();
            
            if (isset($dbConfig['dbname'])) {
                $tablename = $dbConfig['dbname'] . "." . $tableName;
            }
        }
        
        return $tableName;
    }
}

