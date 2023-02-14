<?php
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * @see configs/dbConfig.php
 */
require_once __DIR__ . '/../../../configs/dbConfig.php';
/**
 * @see Cmskorea_Baord_Member
 */
require_once '/Cmskorea/Board/Member.php';
/**
 * Cmskorea_Board_Member 테스트를 위한 클래스
 * Cmskorea_Board_MemberTestClass
 */
class Cmskorea_Board_MemberTestClass extends Cmskorea_Board_Member {
    /**
     * 테스트를 위한 생성자 변경
     */
    public function __construct($dbHost, $userName, $userPw, $dbName) {
        $this->_mysqli = mysqli_connect($dbHost, $userName, $userPw, $dbName);
        if (!$this->_mysqli) {
            die("DB 접속중 문제가 발생했습니다. : ".mysqli_connect_error());
        }
    }
    
    /**
     * 테스트를 위해 mysqli 객체 접근
     * @return mysqli_connect
     */
    public function getMysqli() {
        return $this->_mysqli;
    }
}
/**
 * Cmskorea_Baord_Member test case.
 */
class Cmskorea_Baord_MemberTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Cmskorea_Board_MemberTestClass
     */
    private $member;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->member = new Cmskorea_Board_MemberTestClass(DBHOST, USERNAME, USERPW, 'cmskorea_board_test');
        $sql = "INSERT INTO member(id, pw, name, telNumber, insertTime) VALUES ('test', MD5('1111@'), '테스터', '01012341234', NOW())";
        $res = mysqli_query($this->member->getMysqli(), $sql);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $sql = "DELETE FROM member";
        $res = mysqli_query($this->member->getMysqli(), $sql);
        mysqli_close($this->member->getMysqli());
        $this->member = null;

        parent::tearDown();
    }

    /**
     * Tests Duplicate Cmskorea_Baord_Member->registMember()
     */
    public function testRegistMemberDuplicate()
    {
        /* 아이디 중복 검사 */
        $test1 = array(
            'id' => 'test',
            'pw' => '1234@',
            'name' => '테스터',
            'telNumber' => '010-1234-1234'
        );
        
        try {
            $this->member->registMember($test1);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('이미 동일한 아이디가 존재합니다.',$e->getMessage());
        }
    }
    
    /**
     * Tests WrongInput Cmskorea_Baord_Member->registMember()
     */
    public function testRegistMemberWrongInput()
    {
        /* 입력값 검증 검사 
         * 아이디는 영문, 숫자만
         */
        $wrongId = array(
            'id' => '잘못됐어요',
            'pw' => '1234@',
            'name' => '테스터',
            'telNumber' => '010-1234-1234'
        );
        try {
            $this->member->registMember($wrongId);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('아이디 입력 형식을 지켜주세요.',$e->getMessage());
        }
        
        /* 비밀번호에 특수문자 없음 */
        $wrongPw = array(
            'id' => 'test123',
            'pw' => '1234',
            'name' => '테스터',
            'telNumber' => '010-1234-1234'
        );
        try {
            $this->member->registMember($wrongPw);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('비밀번호 입력 형식을 지켜주세요.',$e->getMessage());
        }
        /* 이름은 한글, 영어만 가능 */
        $wrongName = array(
            'id' => 'test1234',
            'pw' => '1234@',
            'name' => '123',
            'telNumber' => '010-1234-1234'
        );
        try {
            $this->member->registMember($wrongName);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('이름 입력 형식을 지켜주세요.',$e->getMessage());
        }
        
        /* 휴대번호 형식 지켜야함 */
        $wrongTel = array(
            'id' => 'test12345',
            'pw' => '1234@',
            'name' => '테스터',
            'telNumber' => '010-1234-12345'
        );
        try {
            $this->member->registMember($wrongTel);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('휴대전화 입력 형식을 지켜주세요.',$e->getMessage());
        }
    }
    
    /**
     * Tests NotInput Cmskorea_Baord_Member->registMember()
     */
    public function testRegistMemberNotInput()
    {
        /* 입력값 미입력 검사
         * 아이디 미 입력
         */
        $notId = array(
            'id' => '',
            'pw' => '1234@',
            'name' => '테스터',
            'telNumber' => '010-1234-1234'
        );
        try {
            $this->member->registMember($notId);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
        }
        
        /* 비밀번호 미 입력 */
        $notPw = array(
            'id' => 'testPw',
            'pw' => '',
            'name' => '테스터',
            'telNumber' => '010-1234-1234'
        );
        try {
            $this->member->registMember($notPw);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
        }
        
        /* 이름 미 입력 */
        $notName = array(
            'id' => 'testName',
            'pw' => '1234@',
            'name' => '',
            'telNumber' => '010-1234-1234'
        );
        try {
            $this->member->registMember($notName);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
        }
        
        /* 휴대번호 미 입력 */
        $notTel = array(
            'id' => 'testTel',
            'pw' => '1234@',
            'name' => '테스터',
            'telNumber' => ''
        );
        try {
            $this->member->registMember($notTel);
            $this->assertFalse(true);
        } catch(Exception $e) {
            $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
        }
    }
    
    /**
     * Tests Cmskorea_Baord_Member->registMember()
     */
    public function testRegistMember()
    {
        $id = "notOverlapId";
        $test1 = array(
            'id' => $id,
            'pw' => '1234@',
            'name' => '중복아님',
            'telNumber' => '010-4321-4321'
        );
        try {
            $result = $this->member->registMember($test1);
        } catch(Exception $e) {
            $this->assertFalse(true);//예외를 던지면 실패한 것
        }
        
        $sql = "SELECT pk FROM member WHERE id='{$id}'";
        $res = mysqli_query($this->member->getMysqli(), $sql);
        $row = mysqli_fetch_assoc($res);
        
        $expacted = $this->member->getMember($id);
        
        unset($test1['pw']);
        $test1['pk'] = $row['pk'];
        $test1['telNumber'] = str_replace('-', '', $test1['telNumber']);
        $this->assertEquals($expacted, $test1);
    }

    /**
     * Tests Cmskorea_Baord_Member->getMember()
     */
    public function testGetMember()
    {
        $testSql = "SELECT pk, id, name, telNumber FROM member where id='test'";
        $testRes = mysqli_query($this->member->getMysqli(), $testSql);
        $testArray = mysqli_fetch_assoc($testRes);
        
        $res = $this->member->getMember('test');
        $this->assertEquals($testArray, $res);
        $res = $this->member->getMember('notuser');
        $this->assertNotEquals($testArray, $res);
    }

    /**
     * Tests Cmskorea_Baord_Member->authenticate()
     */
    public function testAuthenticate()
    {
        $res = $this->member->authenticate('', '1111');
        $this->assertEquals("아이디를 입력해주세요.", $res);
        $res = $this->member->authenticate('test', '');
        $this->assertEquals("비밀번호를 입력해주세요.", $res);
        $res = $this->member->authenticate('test', '1111@');
        $this->assertEquals('', $res);
        $res = $this->member->authenticate('notuser', '1111');
        $this->assertEquals("아이디 또는 비밀번호가 일치하지 않습니다.", $res);
        $res = $this->member->authenticate('test', '1234');
        $this->assertEquals("아이디 또는 비밀번호가 일치하지 않습니다.", $res);
    }
}

