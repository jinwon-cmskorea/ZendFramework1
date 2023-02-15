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
require_once 'Was/Auth/Table/History.php';

/**
 * Was_Auth_Table_HistoryTest
 */
class Was_Auth_Table_HistoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Was_Auth_Table_History
     */
    private $table;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->table = new Was_Auth_Table_History();

        // 테스트를 위한 기본값
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // 테스트 모든 데이터 삭제
        $this->table->delete(1);
        $this->table->getAdapter()->query("ALTER TABLE `auth_history` auto_increment = 1");

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
     * Tests Was_Auth_Table_History->addHistory()
     */
    public function testAddHistory() {
        // 아이디만 설정
        $identityId = 'test';

        $historyPk = $this->table->addHistory($identityId);
        $historyRow = $this->table->find($historyPk)->current();
        $this->assertNotNull($historyRow);
        $this->assertEquals('127.0.0.1', $historyRow->remoteIp);
        $this->assertEquals(Was_Auth_Table_History::FAIL_MESSAGE, $historyRow->result);

        // 인증아이피 설정
        $historyPk = $this->table->addHistory($identityId, '192.168.0.215');
        $historyRow = $this->table->find($historyPk)->current();
        $this->assertEquals('192.168.0.215', $historyRow->remoteIp);

        // result 설정
        $historyPk = $this->table->addHistory($identityId, null, Was_Auth_Table_History::SUCCESS_MESSAGE);
        $historyRow = $this->table->find($historyPk)->current();
        $this->assertEquals(Was_Auth_Table_History::SUCCESS_MESSAGE, $historyRow->result);
    }

    /**
     * Tests Was_Auth_Table_History->addHistory()
     */
    public function testAddHistoryException() {
        // 아이디만 설정
        $identityId = 'test';

        try {
            $this->table->addHistory($identityId, null, 'Exception');

            $this->assertTrue(false);
        } catch (Was_Auth_Table_Exception $e) {
            $this->assertTrue(true);
        }
    }
}

