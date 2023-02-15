<?php
/**
 * Was Auth Adapter test file
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
 * @see Was_Auth_Table_Identity
 */
require_once 'Was/Auth/Table/Identity.php';

/**
 * Was_Auth_AdapterTest
 */
class Was_Auth_AdapterTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Was_Auth_Adapter
     */
    private $adapter;

    /**
     * @var Was_Auth_Table_Identity
     */
    private $identityTable;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        $this->identityTable = new Was_Auth_Table_Identity();
        $this->identityTable->insert(array(
            'id'    => 'root',
            'pw'    => md5('test1234'),
            'name'  => '관리자',
        ));

        $this->adapter = new Was_Auth_Adapter($this->identityTable->getAdapter(), $this->identityTable->getTableName(), 'id', 'pw', '? AND authable = 1');
        $this->adapter->setIdentity('root');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->adapter = null;

        $this->identityTable->delete(1);
        $this->identityTable = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
    }

    /**
     * Tests Was_Auth_Adapter->setCredential()
     */
    public function testSetCredential() {
        // 정상적인 비밀번호
        $this->adapter->setCredential('test1234');

        $actual = $this->adapter->authenticate();
        $this->assertInstanceOf('Zend_Auth_Result', $actual);
        $this->assertTrue($actual->isValid());

        // 비밀번호 틀림
        $this->adapter->setCredential('1234');
        $actual = $this->adapter->authenticate();
        $this->assertInstanceOf('Zend_Auth_Result', $actual);
        $this->assertFalse($actual->isValid());

        $this->adapter->setCredential(md5('1234'));
        $actual = $this->adapter->authenticate();
        $this->assertInstanceOf('Zend_Auth_Result', $actual);
        $this->assertFalse($actual->isValid());
    }

    /**
     * Tests Was_Auth_Adapter->setIdentityTable()
     * Tests Was_Auth_Adapter->getIdentityTable()
     */
    public function testSetGetIdentityTable() {
        $this->adapter->setIdentityTable($this->identityTable);

        $this->assertEquals($this->identityTable, $this->adapter->getIdentityTable());
    }
}

