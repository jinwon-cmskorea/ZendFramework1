<?php
/**
 * Was Auth Table Access test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Auth/Table/Access.php';

/**
 * Was_Auth_Table_AccessTest
 */
class Was_Auth_Table_AccessTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Was_Auth_Table_Access
     */
    private $table;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->table = new Was_Auth_Table_Access();

        // 테스트를 위한 기본값
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
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
     * Tests Was_Auth_Table_Access->newAccess()
     */
    public function testNewAccess() {
        $identityId = 'test';

        // 인증아이디만 설정
        $actual = $this->table->newAccess($identityId);
        $this->assertEquals($identityId, $actual);

        // 인증아이피
        $identityId = 'test2';
        $this->table->newAccess($identityId, '192.168.0.215');
        $accessRow = $this->table->find($identityId)->current();
        $this->assertEquals('192.168.0.215', $accessRow->remoteIp);

        // 세션아이디
        $identityId = 'test3';
        $sessionId = session_id();
        $this->table->newAccess($identityId, null, $sessionId);
        $accessRow = $this->table->find($identityId)->current();
        $this->assertEquals($sessionId, $accessRow->sessionId);
    }
}

