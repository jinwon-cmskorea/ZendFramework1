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
        require_once __DIR__ . '/MemberTest.php';//멤버테스트 추가?
        $suite->addTestSuite('Was_Member_Table_MemberTest');//테스트할 것 추가?

        return $suite;
    }
}