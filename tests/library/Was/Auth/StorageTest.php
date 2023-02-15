<?php
/**
 * Was Auth Storage test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Auth/Adapter.php';
/**
 * @see Was_Auth_StorageTest
 * @see Was_Auth_Table_Access
 * @see Was_Auth_Table_History
 */
require_once 'Was/Auth/Storage.php';
require_once 'Was/Auth/Table/Access.php';
require_once 'Was/Auth/Table/History.php';

/**
 * Was_Auth_StorageTest 테스트용 클래스
 */
class Was_Auth_StorageTestClass extends Was_Auth_Storage {
    /**
     * @return Was_Auth_Table_Access
     */
    public function getAccessTable() {
        return $this->_accessTable;
    }

    /**
     * @return Was_Auth_Table_History
     */
    public function getHistoryTable() {
        return $this->_historyTable;
    }

    /**
     * @return Zend_Auth_Storage_Session
     */
    public function getSession() {
        return $this->_session;
    }
}

/**
 * Was_Auth_Storage test case.
 */
class Was_Auth_StorageTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Was_Auth_StorageTestClass
     */
    private $storage;
    /**
     * @var Was_Auth_Table_Access
     */
    private $accessTable;
    /**
     * @var Was_Auth_Table_History
     */
    private $historyTable;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        $this->accessTable = new Was_Auth_Table_Access();
        $this->historyTable = new Was_Auth_Table_History();

        $this->storage = new Was_Auth_StorageTestClass($this->accessTable, $this->historyTable);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->storage = null;

        $this->accessTable->delete(1);
        $this->accessTable = null;

        $this->historyTable->delete(1);
        $this->historyTable->getAdapter()->query("ALTER TABLE `auth_history` auto_increment = 1");
        $this->historyTable = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
    }

    /**
     * Tests Was_Auth_Storage->__construct()
     */
    public function test__construct() {
        $this->assertInstanceOf('Was_Auth_Table_Access', $this->storage->getAccessTable());
        $this->assertInstanceOf('Was_Auth_Table_History', $this->storage->getHistoryTable());
        $this->assertInstanceOf('Zend_Auth_Storage_Session', $this->storage->getSession());
    }

    /**
     * Tests Was_Auth_Storage->read()
     */
    public function testRead() {
        $this->assertNull($this->storage->read());

        $contents = $this->_writeContent($this->storage);

        $this->assertNotNull($this->storage->read());
        $this->assertEquals((object) $contents, $this->storage->read());
    }

    /**
     * Tests Was_Auth_Storage->isEmpty()
     */
    public function testIsEmpty() {
        $this->assertTrue($this->storage->isEmpty());

        $this->_writeContent($this->storage);
        $this->assertFalse($this->storage->isEmpty());
    }

    /**
     * Tests Was_Auth_Storage->clear()
     */
    public function testClear() {
        $contents = $this->_writeContent($this->storage);

        // 기록여부확인
        $this->assertNotNull($this->storage->read());
        $this->assertEquals((object) $contents, $this->storage->getSession()->read());

        $accessRow = $this->accessTable->find($contents['id'])->current();
        $this->assertNotNull($accessRow);

        // 삭제
        $this->storage->clear();

        // 삭제여부확인
        $this->assertNull($this->storage->read());
        $accessRow = $this->accessTable->find($contents['id'])->current();
        $this->assertNull($accessRow);
    }

    /**
     * Tests Was_Auth_Storage->write()
     */
    public function testWriteException() {
        // 예외건 체크
        $contents = array(
            'remoteIp' => '127.0.0.1',
            'sessionId' => session_id()
        );

        try {
            $this->storage->write($contents);
            $this->assertTrue(false);
        } catch (Was_Auth_Storage_Exception $e) {
            $this->assertTrue(true);
        }

        $contents['id'] = 'root';
        unset($contents['remoteIp']);
        try {
            $this->storage->write($contents);
            $this->assertTrue(false);
        } catch (Was_Auth_Storage_Exception $e) {
            $this->assertTrue(true);
        }

        $contents['remoteIp'] = '127.0.0.1';
        unset($contents['sessionId']);
        try {
            $this->storage->write($contents);
            $this->assertTrue(false);
        } catch (Was_Auth_Storage_Exception $e) {
            $this->assertTrue(true);
        }

        $contents = 'test';
        try {
            $this->storage->write($contents);
            $this->assertTrue(false);
        } catch (Was_Auth_Storage_Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests Was_Auth_Storage->write()
     */
    public function testWrite() {
        $contents = $this->_writeContent($this->storage);

        // 세션 기록확인
        $this->assertTrue(Zend_Session::namespaceIsset('Was_Auth'));
        $this->assertEquals((object) $contents, $this->storage->getSession()->read());

        // access 테이블 기록확인
        $accessRow = $this->accessTable->find($contents['id'])->current();
        $this->assertNotNull($accessRow);
        $this->assertEquals($contents['id'], $accessRow->identityId);
        $this->assertNotEmpty($accessRow->authTime);
        $this->assertNotEmpty($accessRow->accessTime);

        // history 테이블 기록확인
        $select = $this->historyTable->select();
        $select->from($this->historyTable);
        $select->where('identityId =?', $contents['id']);

        $historyRow = $this->historyTable->fetchRow($select);
        $this->assertNotNull($historyRow);
        $this->assertEquals(Was_Auth_Table_History::SUCCESS_MESSAGE, $historyRow->result);
        $this->assertNotEmpty($historyRow->authTime);
    }

    /**
     * 테스트 데이터 기록
     * @param Was_Auth_StorageTestClass $storage
     */
    protected function _writeContent(Was_Auth_StorageTestClass $storage) {
        // 테스트데이터 기록
        $contents = array(
            'id'        => 'root',
            'remoteIp'  => $_SERVER['REMOTE_ADDR'],
            'sessionId' => session_id()
        );
        $storage->write($contents);

        return $contents;
    }
}

