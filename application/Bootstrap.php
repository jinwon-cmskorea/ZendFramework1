<?php
/**
 * @see Zend_Config
 */
require_once 'Zend/Config/Ini.php';
/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';
/**
 * Bootstrap3
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    /**
     * 전역적으로 db adapter 설정
     */
    public function _initDb() {
        $dbConfig = $this->getOption('resources');
        $db = Zend_Db::factory('Mysqli', $dbConfig['db']['params']);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }
    
    public function _initSession() {
        Zend_Session::start();
    }
    
    public function _initPaginatrolController() {
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/controls.phtml');
    }
}
