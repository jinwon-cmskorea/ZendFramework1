<?php
require_once 'Zend/Session.php';
Zend_Session::start();
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * @see Was_Auth
 */
require_once '/Was/Auth.php';
/**
 * @see Zend_Config_Ini
 */
require_once 'Zend/Config/Ini.php';
class Was_AuthTestClass extends Was_Auth {
    public function getDb() {
        return $this->_db;
    }
}
/**
 * Was_Auth test case.
 */
class Was_AuthTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Was_AuthTestClass
     */
    private $auth;

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
        $this->auth = new Was_AuthTestClass($dbConfig);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->auth = null;

        parent::tearDown();
    }

    /**
     * Tests Was_Auth->__construct()
     */
    public function test__construct()
    {
        $dbConfig = array(
            'host'      => $config->resources->db->params->host,
            'username'  => $config->resources->db->params->username,
            'password'  => $config->resources->db->params->password,
            'dbname'    => $config->resources->db->params->dbname
        );
        $this->auth->__construct($dbConfig);
    }

    /**
     * Tests Was_Auth->login()
     */
    public function testLogin()
    {
        $ip = '127.0.0.1';
        $this->assertEquals(array(), $this->auth->login('root', "1234", $ip));
        
        $expect = array(
            'identityId'        => 'root',
            'remoteIp'          => $ip,
        );
        
        $select = $this->auth->getDb()->select();
        $select->from('auth_access', array('identityId', 'remoteIp'))->where('identityId = "root"');
        $res = $select->query()->fetchAll();
        $this->assertEquals($expect, $res[0]);
        
        $this->assertEquals(array("아이디 또는 비밀번호가 일치하지 않습니다."), $this->auth->login('root', "1111@!", $ip));
        $this->assertEquals(array("아이디 또는 비밀번호가 일치하지 않습니다."), $this->auth->login('root', "1111@!", $ip));
        $this->assertEquals(array("아이디 또는 비밀번호가 일치하지 않습니다.남은 로그인 횟수는 2 입니다."), $this->auth->login('root', "1111@!", $ip));
        $this->assertEquals(array("아이디 또는 비밀번호가 일치하지 않습니다.남은 로그인 횟수는 1 입니다."), $this->auth->login('root', "1111@!", $ip));
        $this->assertEquals(array("로그인을 5번 이상 실패하셨습니다. 아이디가 잠금 처리됐습니다."), $this->auth->login('root', "1111@!", $ip));
        $this->assertEquals(array("아이디 또는 비밀번호가 일치하지 않습니다."), $this->auth->login('notuser', "1234", $ip));
    }

    /**
     * Tests Was_Auth->logout()
     */
    public function testLogout()
    {
        //세션 생성
        $testSession = new Zend_Session_Namespace('cmskorea');
        
        //auth_access 레코드 가져오기
        $select = $this->auth->getDb()->select();
        $select->from('member', array('id', 'name', 'telNumber', 'email', 'position'))
               ->where("id = 'root'");
        $res1 = $select->query()->fetchAll();
        
        $testSession->info = $res1[0];
        
        $logOutRes = $this->auth->logout($testSession);
        $arr = $testSession->info;//세션이 unset되어, 빈 배열인지 확인
        $this->assertEmpty($arr);
        $this->assertEquals(true, $logOutRes);
        
        //로그아웃 이후, auth_access에 해당 유저의 레코드가 사라졌는지 확인
        $select = $this->auth->getDb()->select();
        $select->from('auth_access')
               ->where("identityId = 'root'");
        $res2 = $select->query()->fetchAll();
        $this->assertEquals(array(), $res2);
    }
    
    /**
     * Tests Was_Auth->addAuthHistory()
     */
    public function testAddAuthHistory() {
        //로그인 실패했을 경우
        $pk = $this->auth->addAuthHistory('root', '12345', '127.0.0.1');
        $select1 = $this->auth->getDb()
                              ->select()
                              ->from('auth_history', array('identityId', 'remoteIp', 'result'))
                              ->where("pk = {$pk}");
        $expext1 = array(
            'identityId'    => 'root',
            'remoteIp'      => '127.0.0.1',
            'result'        => '로그인 실패'
        );
        $result1 = $select1->query()->fetchAll();
        $this->assertEquals($expext1, $result1[0]);
        //로그인 성공했을 경우
        $pk2 = $this->auth->addAuthHistory('root', "1234", '127.0.0.1');
        $select2 = $this->auth->getDb()
                              ->select()
                              ->from('auth_history', array('identityId', 'remoteIp', 'result'))
                              ->where("pk = {$pk2}");
        $expext2 = array(
            'identityId'    => 'root',
            'remoteIp'      => '127.0.0.1',
            'result'        => '로그인 성공'
        );
        $result2 = $select2->query()->fetchAll();
        $this->assertEquals($expext2, $result2[0]);
    }
    
    /**
     * Tests Was_Auth->checkAuthable()
     */
    public function testCheckAuthable() {
        $this->assertEquals('로그인을 5번 이상 실패하셨습니다. 아이디가 잠금 처리됐습니다.', $this->auth->checkAuthable('root'));
        $this->auth->getDb()->update('auth_identity', array('authable' => 1, 'errorCount' => '0', 'errorMessage' => ''));
        $this->assertNull($this->auth->checkAuthable('root'));
    }
}

