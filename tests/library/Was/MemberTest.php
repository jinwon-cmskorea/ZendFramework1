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
            'telNumber'   => '01012341234',
            'email'       => 'test@example.com',
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
        $this->assertEquals('01012345678', $memberRow->telNumber);
        
        $contents = array(
            'id'        => 'testThreeNum',
            'pw'        => '1234@',
            'name'      => '등록테스트',
            'telNumber' => '010-123-5678',
            'email'     => 'regist2@test.com'
        );
        //회원정보 등록
        $result = $this->member->registMember($contents);
        $memberRow = $this->memberTable->find(3)->current();
        $this->assertNotNull($result);
        $this->assertEquals('등록테스트', $memberRow->name);
        $this->assertEquals('0101235678', $memberRow->telNumber);
    }
    
    /**
     * Tests Was_Member->registMember() ValidateException
     */
    public function testRegistMemberValidateException()
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
     * Tests Was_Member->registMember() Require
     */
    public function testRegistMemberRequire()
    {
        //빈 아이디 형식
        $contents = array(
            'id'        => '',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        $this->assertFalse($this->member->registMember($contents));
        
        //빈 비밀번호 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        $this->assertFalse($this->member->registMember($contents));
        
        //빈 이름 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        $this->assertFalse($this->member->registMember($contents));
        
        //빈 핸드폰번호 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '',
            'email'     => 'regist@test.com'
        );
        
        $this->assertFalse($this->member->registMember($contents));
    }

    /**
     * Tests Was_Member->searchId()
     */
    public function testSearchId()
    {
        $this->assertEquals('test', $this->member->searchId('테스터', '010-1234-1234'));
    }
    
    /**
     * Tests Was_Member->searchId() NoExistUser
     */
    public function testSearchIdNoExistUser()
    {
        $this->assertEquals('', $this->member->searchId('존재하지않음', '010-123-1234'));
    }

    /**
     * Tests Was_Member->modifyMember()
     */
    public function testModifyMember()
    {
        $contents = array(
            'id'        => 'testModify',
            'pw'        => '1234@',
            'name'      => '수정테스트',
            'telNumber' => '010-1234-4321',
            'email'     => 'regist@test.com'
        );
        $result = $this->member->modifyMember($contents, 1);
        $memberRow = $this->memberTable->find(1)->current();
        $this->assertNotNull($result);
        $this->assertEquals('testModify', $memberRow->id);
        $this->assertEquals('수정테스트', $memberRow->name);
        $this->assertEquals('01012344321', $memberRow->telNumber);
        
        $contents = array(
            'id'        => 'testModifyTwo',
            'pw'        => '1234@',
            'name'      => '수정테스트',
            'telNumber' => '010-123-4321',
            'email'     => 'regist@test.com'
        );
        $result = $this->member->modifyMember($contents, 1);
        $memberRow = $this->memberTable->find(1)->current();
        $this->assertNotNull($result);
        $this->assertEquals('testModifyTwo', $memberRow->id);
        $this->assertEquals('수정테스트', $memberRow->name);
        $this->assertEquals('0101234321', $memberRow->telNumber);
    }
    
    /**
     * Tests Was_Member->modifyMember() ValidateException
     */
    public function testModifyMemberValidateException()
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
            $this->member->modifyMember($contents, 1);
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
            $this->member->modifyMember($contents, 1);
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
            $this->member->modifyMember($contents, 1);
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
            $this->member->modifyMember($contents, 1);
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
            $this->member->modifyMember($contents, 1);
            $this->assertFalse(true);
        } catch (Was_Member_Exception $e) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * Tests Was_Member->modifyMember() NotInput
     */
    public function testModifyMemberNotInput()
    {
        //빈 아이디형식
        $contents = array(
            'id'        => '',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        $this->assertFalse($this->member->modifyMember($contents, 1));
        
        //빈 비밀번호 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '',
            'name'      => '검증테스트',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        $this->assertFalse($this->member->modifyMember($contents, 1));
        
        //빈 이름 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '',
            'telNumber' => '010-1234-5678',
            'email'     => 'regist@test.com'
        );
        
        $this->assertFalse($this->member->modifyMember($contents, 1));
        
        //빈 핸드폰번호 형식
        $contents = array(
            'id'        => 'testValidate',
            'pw'        => '1234@',
            'name'      => '검증테스트',
            'telNumber' => '',
            'email'     => 'regist@test.com'
        );
        
        $this->assertFalse($this->member->modifyMember($contents, 1));
    }
    
    /**
     * Tests Was_Member->getMember()
     */
    public function testGetMember() {
        $expect = array(
            'id'          => 'test',
            'name'        => '테스터',
            'telNumber'   => '010-1234-1234',
            'email'       => 'test@example.com'
        );
        $memberInfo = $this->member->getMember('test');
        $this->assertTrue(is_array($memberInfo));
        $this->assertEquals($expect, $memberInfo);
    }
    
    /**
     * Tests Was_Member->getMember() NotExistUser
     */
    public function testGetMemberNotExistUser() {
        //존재하지 않는 회원 테스트
        $memberInfo = $this->member->getMember('notExist');
        $this->assertEquals(array(), $memberInfo);
    }
}

