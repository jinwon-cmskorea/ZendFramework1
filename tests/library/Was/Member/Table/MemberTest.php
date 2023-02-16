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

        // TODO Auto-generated Was_Member_Table_MemberTest::setUp()

        $this->memberTable = new Was_Member_Table_Member();
        $this->memberTable->insert(array(
            'id'          => 'test',
            'name'        => '테스터',
            'telNumber'   => '010-1234-1234',
            'email'       => 'test@example.com',
            'position'    => Was_Member::MEMBER,
            'insertTime'    => new Zend_Db_Expr('NOW()'),
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated Was_Member_Table_MemberTest::tearDown()
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
            'position'  => Was_Member::MEMBER
        );
        
        $memberPk = $this->memberTable->regist($contents);
        $memberRow = $this->memberTable->find($memberPk)->current();
        $this->assertNotNull($memberRow);
        $this->assertEquals('testRegist', $memberRow->id);
        $this->assertEquals('regist@test.com', $memberRow->email);
    }
    
    /**
     * Tests Was_Member_Table_Member->regist() Exception
     */
    public function testRegistException() {
        $contents = array(
            'id'        => 'testRegist2',
            'pw'        => '1234@',
            'name'      => '등록테스트',
            'telNumber' => '010-1234-1234',
            'email'     => 'regist@test.com',
            'position'  => Was_Member::MEMBER
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
            'pk'        => 1,
            'id'        => 'testModify',
            'name'      => '수정테스트',
            'telNumber' => '010-1234-4321',
            'email'     => 'regist@test.com'
        );
        $update = $this->memberTable->modify($contents);
        $memberRow = $this->memberTable->find(1)->current();
        $this->assertNotNull($update);
        $this->assertEquals('testModify', $memberRow->id);
        $this->assertEquals('수정테스트', $memberRow->name);
    }
    
    /**
     * Tests Was_Member_Table_Member->modify() Exception
     */
    public function testModifyException() {
        $contents = array(
            'pk'        => 1,
            'id'        => 'testModify',
            'name'      => '수정테스트',
            'telNumber' => '010-1234-1234',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->memberTable->modify($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Table_Exception $e) {
            $this->assertTrue(true);
        }
    }
}

