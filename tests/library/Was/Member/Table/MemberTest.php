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
require_once 'Was/Member/Table/Member.php';
/**
 * @see Was_Member
 */
require_once 'Was/Member.php';

/**
 * Was_Member_Table_MemberTest
 */
class Was_Member_Table_MemberTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Was_Member_Table_Member
     */
    private $memberTable;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->memberTable = new Was_Member_Table_Member();
        $this->memberTable->insert(array(
            'id'          => 'test',
            'name'        => '테스터',
            'telNumber'   => '01012341234',
            'email'       => 'test@example.com',
            'position'    => Was_Member::POWER_MEMBER,
            'insertTime'    => new Zend_Db_Expr('NOW()'),
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ));
        $this->memberTable->insert(array(
            'id'          => 'test2',
            'name'        => '테스터two',
            'telNumber'   => '01010021002',
            'email'       => 'test2@example.com',
            'position'    => Was_Member::POWER_MEMBER,
            'insertTime'    => new Zend_Db_Expr('NOW()'),
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->memberTable->delete(1);
        $this->memberTable->getAdapter()->query("ALTER TABLE `member` auto_increment = 1");
        $this->memberTable = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests Was_Member_Table_Member->regist()
     */
    public function testRegist()
    {
        $contents = array(
            'id'        => 'testRegist',
            'pw'        => '1234@',
            'name'      => '등록테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com',
            'position'  => Was_Member::POWER_MEMBER
        );
        
        $memberPk = $this->memberTable->regist($contents);
        $memberRow = $this->memberTable->find($memberPk)->current();
        $this->assertNotNull($memberRow);
        $this->assertEquals('testRegist', $memberRow->id);
        $this->assertEquals('regist@test.com', $memberRow->email);
        $this->assertEquals('01012345678', $memberRow->telNumber);
        
        $contents = array(
            'id'        => 'testRegist2',
            'pw'        => '12@34',
            'name'      => '등록테스트',
            'telNumber' => '010-987-1234',
            'email'     => 'regist2@test.com',
            'position'  => Was_Member::POWER_MEMBER
        );
        
        $memberPk = $this->memberTable->regist($contents);
        $memberRow = $this->memberTable->find($memberPk)->current();
        $this->assertNotNull($memberRow);
        $this->assertEquals('testRegist2', $memberRow->id);
        $this->assertEquals('regist2@test.com', $memberRow->email);
        $this->assertEquals('0109871234', $memberRow->telNumber);
    }
    
    /**
     * Tests Was_Member_Table_Member->regist() telNumberDuplicateException
     */
    public function testRegistTelNumberDuplicateException() {
        //
        $contents = array(
            'id'        => 'testRegist2',
            'pw'        => '1234@',
            'name'      => '등록테스트',
            'telNumber' => '010-1234-1234',
            'email'     => 'regist@test.com',
            'position'  => Was_Member::POWER_MEMBER
        );
        
        try {
            $this->memberTable->regist($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Table_Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests Was_Member_Table_Member->modify()
     */
    public function testModify()
    {
        $contents = array(
            'id'        => 'testModify',
            'name'      => '수정테스트',
            'telNumber' => '010-1234-4321',
            'email'     => 'regist@test.com'
        );
        $update = $this->memberTable->modify($contents, 1);
        $memberRow = $this->memberTable->find(1)->current();
        $this->assertNotNull($update);
        $this->assertEquals('testModify', $memberRow->id);
        $this->assertEquals('수정테스트', $memberRow->name);
        $this->assertEquals('01012344321', $memberRow->telNumber);
        
        $contents = array(
            'id'        => 'testModify2',
            'name'      => '수정테스트',
            'telNumber' => '010-123-4321',
            'email'     => 'regist@test.com'
        );
        $update = $this->memberTable->modify($contents, 1);
        $memberRow = $this->memberTable->find(1)->current();
        $this->assertNotNull($update);
        $this->assertEquals('testModify2', $memberRow->id);
        $this->assertEquals('수정테스트', $memberRow->name);
        $this->assertEquals('0101234321', $memberRow->telNumber);
    }
    
    /**
     * Tests Was_Member_Table_Member->modify() telNumberDuplicateException
     */
    public function testModifytelNumberDuplicateException() {
        $contents = array(
            'id'        => 'testModify',
            'name'      => '수정테스트',
            'telNumber' => '010-1002-1002',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->memberTable->modify($contents, 1);
            $this->assertFalse(true);
        } catch (Was_Member_Table_Exception $e) {
            $this->assertTrue(true);
        }
    }
}

