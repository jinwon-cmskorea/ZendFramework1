<?php
/**
 * @see Zend_Config
 */
require_once 'Zend/Config/Ini.php';
/**
 * Bootstrap3
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    /**
     * DB Adapter 를 만들어서 반환해준다.
     * 
     * @return Zend_Db_Adapter_Abstract
     */
    public static function setDbFactory() {
        
        $configIni = new Zend_Config_Ini(__DIR__ . '/configs/application.ini', 'testing');
        
        $zdb = Zend_Db::factory("Mysqli", array(
            'host'      => $configIni->resources->db->params->host,
            'username'  => $configIni->resources->db->params->username,
            'password'  => $configIni->resources->db->params->password,
            'dbname'    => $configIni->resources->db->params->dbname
        ));
        return $zdb;
    }
}
