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
require_once 'Was/Auth.php';
/**
 * @see Was_Auth_StorageTest
 * @see Was_Auth_Table_Access
 * @see Was_Auth_Table_History
 * @see Was_Auth_Exception
 * @see Zend_Auth_Result
 */
require_once 'Was/Auth/Storage.php';
require_once 'Was/Auth/Table/Access.php';
require_once 'Was/Auth/Table/History.php';
require_once 'Was/Auth/Exception.php';
require_once 'Was/Auth/AdapterFactory.php';
require_once 'Zend/Auth/Result.php';

class Was_Auth_StorageTableClass extends Was_Auth_Storage {
}

class Was_AuthTestClass extends Was_Auth {
    /**
     * @return Zend_Auth
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

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
     * 테스트용 초기화
     */
    public function resetStorage() {
        $this->_storage = null;
        $this->_accessTable = '';
        $this->_historyTable = '';
    }
}

/**
 * Was_Auth test case.
 */
class Was_AuthTest extends PHPUnit_Framework_TestCase {

    /**
     * 인증 테스트 클래스
     *
     * @var Was_AuthTestClass
     */
    private $auth;
    /**
     * 인증 테이블
     *
     * @var Was_Auth_Table_Identity
     */
    private $identityTable;
    /**
     * 접속중 관리테이블
     *
     * @var Was_Auth_Table_Access
     */
    private $accessTable;
    /**
     * 접속 이력테이블
     *
     * @var Was_Auth_Table_History
     */
    private $historyTable;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->auth = Was_AuthTestClass::getInstance();

        $this->accessTable = new Was_Auth_Table_Access();
        $this->historyTable = new Was_Auth_Table_History();
        $this->identityTable = new Was_Auth_Table_Identity();
        $this->identityTable->insert(array(
            'id'    => 'root',
            'pw'    => md5('test1234'),
            'name'  => '관리자',
        ));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->auth->resetStorage();
        $this->auth = null;

        $this->identityTable->delete(1);
        $this->identityTable = null;

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
     * Tests Was_Auth::getInstance()
     */
    public function testGetInstance() {
        $this->assertEquals($this->auth, Was_Auth::getInstance());
    }

    /**
     * Tests Was_Auth->authenticate()
     */
    public function testAuthenticate() {
        $adapter = Was_Auth_AdapterFactory::getAdapter($this->identityTable);

        // 정상적인 로그인
        $adapter->setIdentity('root');
        $adapter->setCredential('test1234');

        // 테이블
        $this->auth->setAccessTable($this->accessTable);
        $this->auth->setHistoryTable($this->historyTable);

        $actual = $this->auth->authenticate($adapter);
        $this->assertInstanceOf('Zend_Auth_Result', $actual);
        $this->assertTrue($actual->isValid());
        $this->assertEquals(Zend_Auth_Result::SUCCESS, $actual->getCode());


        // 비정상적인 로그인
        $adapter->setIdentity('root');
        $adapter->setCredential('test');
        $actual = $this->auth->authenticate($adapter);
        $this->assertInstanceOf('Zend_Auth_Result', $actual);
        $this->assertFalse($actual->isValid());
        $this->assertEquals(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $actual->getCode());

        $adapter->setIdentity('root12');
        $adapter->setCredential('test1234');
        $actual = $this->auth->authenticate($adapter);
        $this->assertInstanceOf('Zend_Auth_Result', $actual);
        $this->assertFalse($actual->isValid());
        $this->assertEquals(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $actual->getCode());

        // 인증여부 불가인경우
        $this->identityTable->update(array(
            'authable'  => 0
        ), "id = 'root'");
        $adapter->setIdentity('root');
        $adapter->setCredential('test1234');
        $actual = $this->auth->authenticate($adapter);
        $this->assertInstanceOf('Zend_Auth_Result', $actual);
        $this->assertFalse($actual->isValid());
        $this->assertEquals(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $actual->getCode());
    }

    /**
     * Tests Was_Auth->getStorage()
     */
    public function testGetStorageException() {
        try {
            $this->auth->getStorage();
            $this->assertTrue(false);
        } catch (Was_Auth_Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $this->auth->setAccessTable($this->accessTable);
            $this->auth->getStorage();
            $this->assertTrue(false);
        } catch (Was_Auth_Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $this->auth->resetStorage();
            $this->auth->setHistoryTable($this->historyTable);
            $this->auth->getStorage();
            $this->assertTrue(false);
        } catch (Was_Auth_Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Test Was_Auth->getStorage()
     */
    public function testGetStorage() {
        $this->auth->setAccessTable($this->accessTable);
        $this->auth->setHistoryTable($this->historyTable);

        $actual = $this->auth->getStorage();
        $this->assertInstanceOf('Was_Auth_Storage', $actual);

        // 초기화
        $this->auth->resetStorage();

        $storage = new Was_Auth_StorageTableClass($this->accessTable, $this->historyTable);
        $this->auth->setStorage($storage);
        $actual = $this->auth->getStorage();
        $this->assertInstanceOf('Was_Auth_StorageTableClass', $actual);
        $this->assertEquals($storage, $actual);
    }

    /**
     * Test Was_Auth->setAccessTable()
     * Test Was_Auth->getAccessTable()
     */
    public function testSetGetAccessTable() {
        $this->auth->setAccessTable($this->accessTable);
        $this->assertEquals($this->accessTable, $this->auth->getAccessTable());
    }

    /**
     * Test Was_Auth->setHistoryTable()
     * Test Was_Auth->getHistoryTable()
     */
    public function testSetGetHistoryTable() {
        $this->auth->setHistoryTable($this->historyTable);
        $this->assertEquals($this->historyTable, $this->auth->getHistoryTable());
    }
}

