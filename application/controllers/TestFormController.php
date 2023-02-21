<?php
/**
 * 테스트용 form 컨트롤러
 *
 */
/**
 * @see Was_Auth
 * @see Was_Auth_Table_Access
 * @see Was_Auth_Table_History
 * @see Was_Auth_Table_Identity
 * @see Was_Auth_AdapterFactory
 */
require_once 'Was/Auth.php';
require_once 'Was/Auth/Table/Access.php';
require_once 'Was/Auth/Table/History.php';
require_once 'Was/Auth/Table/Identity.php';
require_once 'Was/Auth/AdapterFactory.php';
/**
 * @see Zend_Db
 */
require_once 'Zend/Db.php';
/**
 * @see Bootstrap
 */
require_once __DIR__ . '/../Bootstrap.php';

class TestFormController extends Zend_Controller_Action {
    
    /*
     * {@inheritDoc}
     * @see Zend_Controller_Action::init()
     */
    public function init() {
        
    }
    
    /**
     * login page Action
     */
    public function testsignAction() {
        //기본 레이아웃 설정
        $this->_helper->layout->setLayout('default1');
        $request = $this->getRequest();
        
        require_once 'Was/Auth/Form/Login.php';
        $loginForm = new Was_Auth_Form_Login();
        
        if ($this->getRequest()->isPost()) {
            if ($loginForm->isValid($request->getPost())) {
                //auth 인스턴스 생성
                $auth = Was_Auth::getInstance();
                //mysqli adapter 생성
                $db = Bootstrap::setDbFactory();
                //테이블 객체 생성
                $identityTable = new Was_Auth_Table_Identity($db);
                $accessTable = new Was_Auth_Table_Access($db);
                $historyTable = new Was_Auth_Table_History($db);
                //auth 객체 내부에 테이블 세팅
                $auth->setAccessTable($accessTable);
                $auth->setHistoryTable($historyTable);
                //adapter 생성
                $adapter = Was_Auth_AdapterFactory::getAdapter($identityTable);
                //post로 받아온 값을을 세팅
                $param = $request->getPost();
                $adapter->setIdentity($param['id']);
                $adapter->setCredential($param['pw']);
                
                $authRes = $auth->authenticate($adapter);
                var_dump($authRes->isValid());
            }
        }
        
        $this->view->form = $loginForm;
    }
    
    public function testpasswordAction() {
        $request = $this->getRequest();
        
        require_once 'Was/Auth/Form/Password.php';
        $form = new Was_Auth_Form_Password();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                echo "test";
            }
        }
        
        $this->view->form = $form;
    }
    
    public function testmemberAction() {
        $request = $this->getRequest();
        
        require_once 'Was/Member/Form/Member.php';
        $form = new Was_Member_Form_Member();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                echo "test";
            }
        }
        
        $this->view->form = $form;
    }
    
    public function testboardAction() {
        $request = $this->getRequest();
        
        require_once 'Was/Board/Form/Board.php';
        $form = new Was_Board_Form_Board();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                echo "test";
            }
        }
        
        $this->view->form = $form;
    }
}

