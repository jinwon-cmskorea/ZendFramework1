<?php
/**
 * CmskoreaERP AllTests file
 */
/**
 * require bootstrap
 */
require_once __DIR__ . '/bootstrap.php';
/**
 * Was_Member_Table_AllTests
 */
class Was_Member_Table_AllTests extends PHPUnit_Framework_TestSuite {

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Was Member Table All Tests');


        return $suite;
    }
}