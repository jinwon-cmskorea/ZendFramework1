<?php
/**
 * CmskoreaERP AllTests file
 */
/**
 * require bootstrap
 */
require_once __DIR__ . '/bootstrap.php';
/**
 * Was_Board_AllTests
 */
class Was_Board_AllTests extends PHPUnit_Framework_TestSuite {

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Was Board All Tests');


        return $suite;
    }
}