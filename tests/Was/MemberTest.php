<?php
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * @see Was_Member
 */
require_once '/Was/Member.php';
/**
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';
/**
 * Was_Member 테스트를 위한 클래스
 * Was_MemberTestClass
 */
class Was_MemberTestClass extends Was_Member {
    public function getDb() {
        return $this->_db;
    }
    
    public function getTable() {
        return $this->_table;
    }
}
/**
 * Was_Member test case.
 */
class Was_MemberTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Was_MemberTestClass
     */
    private $member;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new Zend_Config_Ini(__DIR__ . '/../../application/configs/application.ini', 'testing');
        $dbConfig = array(
            'host'      => $config->resources->db->params->host,
            'username'  => $config->resources->db->params->username,
            'password'  => $config->resources->db->params->password,
            'dbname'    => $config->resources->db->params->dbname
        );
        $this->member = new Was_MemberTestClass($dbConfig);
        //테스트용 레코드 삽입
        $data = array(
            'id'        => 'test',
            'name'      => '테스터',
            'telNumber' => '010-1234-1234',
            'email'     => 'test@example.com',
            'position'  => 3,
            'insertTime'    => date("Y-m-d H:i:s"),
            'updateTime'    => date("Y-m-d H:i:s")
        );
        $this->member->getTable()->insert($data);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->member->getTable()->delete("id = 'test'");
        $this->member->getTable()->delete("id = 'notOverlapId'");
        $this->member->getDb()->delete('auth_identity', "id = 'test'");
        $this->member->getDb()->delete('auth_identity', "id = 'notOverlapId'");
        
        $this->member = null;

        parent::tearDown();
    }

    /**
     * Tests Duplicate Was_Member->registMember()
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
    
//     /**
//      * Tests WrongInput Was_Member->registMember()
//      */
//     public function testRegistMemberWrongInput()
//     {
//         /* 입력값 검증 검사 
//          * 아이디는 영문, 숫자만
//          */
//         $wrongId = array(
//             'id' => '잘못됐어요',
//             'pw' => '1234@',
//             'name' => '테스터',
//             'telNumber' => '010-1234-1234'
//         );
//         try {
//             $this->member->registMember($wrongId);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('아이디 입력 형식을 지켜주세요.',$e->getMessage());
//         }
        
//         /* 비밀번호에 특수문자 없음 */
//         $wrongPw = array(
//             'id' => 'test123',
//             'pw' => '1234',
//             'name' => '테스터',
//             'telNumber' => '010-1234-1234'
//         );
//         try {
//             $this->member->registMember($wrongPw);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('비밀번호 입력 형식을 지켜주세요.',$e->getMessage());
//         }
//         /* 이름은 한글, 영어만 가능 */
//         $wrongName = array(
//             'id' => 'test1234',
//             'pw' => '1234@',
//             'name' => '123',
//             'telNumber' => '010-1234-1234'
//         );
//         try {
//             $this->member->registMember($wrongName);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('이름 입력 형식을 지켜주세요.',$e->getMessage());
//         }
        
//         /* 휴대번호 형식 지켜야함 */
//         $wrongTel = array(
//             'id' => 'test12345',
//             'pw' => '1234@',
//             'name' => '테스터',
//             'telNumber' => '010-1234-12345'
//         );
//         try {
//             $this->member->registMember($wrongTel);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('휴대전화 입력 형식을 지켜주세요.',$e->getMessage());
//         }
//     }
    
//     /**
//      * Tests NotInput Was_Member->registMember()
//      */
//     public function testRegistMemberNotInput()
//     {
//         /* 입력값 미입력 검사
//          * 아이디 미 입력
//          */
//         $notId = array(
//             'id' => '',
//             'pw' => '1234@',
//             'name' => '테스터',
//             'telNumber' => '010-1234-1234'
//         );
//         try {
//             $this->member->registMember($notId);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
//         }
        
//         /* 비밀번호 미 입력 */
//         $notPw = array(
//             'id' => 'testPw',
//             'pw' => '',
//             'name' => '테스터',
//             'telNumber' => '010-1234-1234'
//         );
//         try {
//             $this->member->registMember($notPw);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
//         }
        
//         /* 이름 미 입력 */
//         $notName = array(
//             'id' => 'testName',
//             'pw' => '1234@',
//             'name' => '',
//             'telNumber' => '010-1234-1234'
//         );
//         try {
//             $this->member->registMember($notName);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
//         }
        
//         /* 휴대번호 미 입력 */
//         $notTel = array(
//             'id' => 'testTel',
//             'pw' => '1234@',
//             'name' => '테스터',
//             'telNumber' => ''
//         );
//         try {
//             $this->member->registMember($notTel);
//             $this->assertFalse(true);
//         } catch(Exception $e) {
//             $this->assertEquals('필수 항목을 모두 입력해주세요.',$e->getMessage());
//         }
//     }
    
    /**
     * Tests Was_Member->registMember()
     */
    public function testRegistMember()
    {
        $id = "notOverlapId";
        $test1 = array(
            'id' => $id,
            'pw' => '1234@',
            'name' => '중복아님',
            'telNumber' => '010-4321-4321',
            'email'     => 'notOverlapId@test.com'
        );
        try {
            $result = $this->member->registMember($test1);
        } catch(Exception $e) {
            $this->assertFalse(true);//예외를 던지면 실패한 것
        }
        
        $expacted = $this->member->getMember($id);
        
        unset($expacted['pk']);
        unset($test1['pw']);
        $test1['telNumber'] = str_replace('-', '', $test1['telNumber']);
        $test1['position'] = 3;
        $this->assertEquals($expacted, $test1);
    }

    /**
     * Tests Was_Member->getMember()
     */
    public function testGetMember()
    {
        $select = $this->member->getTable()->select();
        $select->from($this->member->getTable(), array('pk', 'id', 'name', 'telNumber', 'email', 'position'))
               ->where("id = 'test'");
        $testArray = $select->query()->fetchAll();
        
        $res = $this->member->getMember('test');
        $this->assertEquals($testArray[0], $res);
        $res = $this->member->getMember('notuser');
        $this->assertNotEquals($testArray[0], $res);
    }
}

