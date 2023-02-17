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
        require_once __DIR__ . '/../MemberTest.php';//멤버테스트 추가?
        $suite->addTestSuite('Was_MemberTest');//테스트하고 싶은 클래스명 추가
        
        $suite->addTestFile(__DIR__ . '/Table/AllTests.php');//테스트하고싶은 파일 추가

        return $suite;
    }
}