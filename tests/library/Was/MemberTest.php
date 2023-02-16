<?php
/**
 * Was Member test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Member.php';
/**
 * @see Was_Member_Table_Member
 */
require_once 'Was/Member/Table/Member.php';

class Was_MemberTestClass extends Was_Member {
    public function getMemberTable() {
        return $this->_memberTable;
    }
}

class Was_MemberTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Was_Member
     */
    private $member;
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

        // TODO Auto-generated Was_MemberTest::setUp()
        $this->memberTable = new Was_Member_Table_Member();
        $this->member = new Was_MemberTestClass($this->memberTable);
        
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
        // TODO Auto-generated Was_MemberTest::tearDown()
        $this->member = null;

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
     * Tests Was_Member->__construct()
     */
    public function test__construct()
    {

        $this->member->__construct($this->memberTable);
        $this->assertInstanceOf('Was_Member_Table_Member', $this->member->getMemberTable());
    }

    /**
     * Tests Was_Member->registMember()
     */
    public function testRegistMember()
    {
        $contents = array(
            'id'        => 'testRegist',
            'pw'        => '1234@',
            'name'      => '등록테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        //회원정보 등록
        $result = $this->member->registMember($contents);
        $memberRow = $this->memberTable->find(2)->current();
        $this->assertNotNull($result);
        $this->assertEquals('등록테스트', $memberRow->name);
    }
    
    /**
     * Tests Was_Member->registMember() Exception
     */
    public function testRegistMemberException()
    {
        //잘못된 아이디형식
        $contents = array(
            'id'        => 'test@Validate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        try {
            $this->member->registMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 비밀번호 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->member->registMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 이름 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트123',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->member->registMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 핸드폰번호 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-56789',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->member->registMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 이메일 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@127.0.0.1'
        );
        
        try {
            $this->member->registMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests Was_Member->searchId()
     */
    public function testSearchId()
    {
        $this->assertEquals('test', $this->member->searchId(array( 'name' => '테스터', 'telNumber' => '010-1234-1234')));
    }

    /**
     * Tests Was_Member->modifyMember()
     */
    public function testModifyMember()
    {
        $contents = array(
            'pk'        => 1,
            'id'        => 'testModify',
            'pw'        => '1234@',
            'name'      => '수정테스트',
            'telNumber' => '010-1234-4321',
            'email'     => 'regist@test.com'
        );
        $result = $this->member->modifyMember($contents);
        $memberRow = $this->memberTable->find(1)->current();
        $this->assertNotNull($result);
        $this->assertEquals('testModify', $memberRow->id);
        $this->assertEquals('수정테스트', $memberRow->name);
    }
    
    /**
     * Tests Was_Member->modifyMember() Exception
     */
    public function testModifyMemberException()
    {
        //잘못된 아이디형식
        $contents = array(
            'pk'        => 1,
            'id'        => 'test@Validate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        try {
            $this->member->modifyMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 비밀번호 형식
        $contents = array(
            'pk'        => 1,
            'id'        => 'testValidate',
            'pw'        => '1234',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->member->modifyMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 이름 형식
        $contents = array(
            'pk'        => 1,
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트123',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->member->modifyMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 핸드폰번호 형식
        $contents = array(
            'pk'        => 1,
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-56789',
            'email'     => 'regist@test.com'
        );
        
        try {
            $this->member->modifyMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
        
        //잘못된 이메일 형식
        $contents = array(
            'pk'        => 1,
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@127.0.0.1'
        );
        
        try {
            $this->member->modifyMember($contents);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
    }
}

