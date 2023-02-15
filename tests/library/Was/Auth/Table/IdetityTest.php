<?php
/**
 * Was Auth Table History test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Auth/Table/Identity.php';

/**
 * Was_Auth_Table_IdetityTest
 */
class Was_Auth_Table_IdetityTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Was_Auth_Table_Identity
     */
    private $table;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->table = new Was_Auth_Table_Identity();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // 테스트 모든 데이터 삭제
        $this->table->delete(1);

        // 객체 초기화
        $this->table = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
    }

    /**
     * Tests Was_Auth_Table_Identity->getTableName()
     */
    public function testGetTableName() {
        // 스키마없음
        $this->assertEquals('auth_identity', $this->table->getTableName());

        // 스키마있음.
        $this->assertEquals('cmskorea_board_test.auth_identity', $this->table->getTableName(true));
    }
}

