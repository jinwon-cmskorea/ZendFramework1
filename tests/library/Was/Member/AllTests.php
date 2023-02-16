<?php
/**
 * CmskoreaERP AllTests file
 */
/**
 * require bootstrap
 */
require_once __DIR__ . '/bootstrap.php';
/**
 * Was_Member_AllTests
 */
class Was_Member_AllTests extends PHPUnit_Framework_TestSuite {

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Was Member All Tests');


        return $suite;
    }
}