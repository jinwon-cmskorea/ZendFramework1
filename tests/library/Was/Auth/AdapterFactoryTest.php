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
require_once 'Was/Auth/AdapterFactory.php';
/**
 * @see Was_Auth_Table_Identity
 */
require_once 'Was/Auth/Table/Identity.php';

/**
 * Was_Auth_AdapterFactory test case.
 */
class Was_Auth_AdapterFactoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Was_Auth_AdapterFactory
     */
    private $factory;

    /**
     * @var Was_Auth_Table_Identity
     */
    private $identityTable;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->factory = new Was_Auth_AdapterFactory();

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
        $this->factory = null;

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
     * Tests Was_Auth_AdapterFactory::getAdapter()
     */
    public function testGetAdapter() {
        $identityTable = new Was_Auth_Table_Identity();

        $actual = Was_Auth_AdapterFactory::getAdapter($identityTable);
        $this->assertInstanceOf('Was_Auth_Adapter', $actual);

        // 로그인 테스트
        $actual->setIdentity('root');
        $actual->setCredential('test1234');
        $this->assertTrue($actual->authenticate()->isValid());

        $actual->setIdentity('root');
        $actual->setCredential('test12341234');
        $this->assertFalse($actual->authenticate()->isValid());

        // 인증불능 회원 추가
        $this->identityTable->insert(array(
            'id'        => 'member',
            'pw'        => md5('test1234'),
            'name'      => '회원',
            'authable'  => 0
        ));
        $actual->setIdentity('member');
        $actual->setCredential('test1234');
        $this->assertFalse($actual->authenticate()->isValid());
    }
}

