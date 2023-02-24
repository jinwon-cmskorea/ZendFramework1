<?php
/**
 * 계정 컨트롤러
 */
require_once 'Zend/Controller/Action/Helper/AjaxContext.php';
require_once 'Zend/Controller/Request/Http.php';
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
 * @see Was_Member
 * @see Was_Member_Table_Member
 * @see Was_Member_Exception
 * @see Was_Member_Table_Exception
 */
require_once 'Was/Member.php';
require_once 'Was/Member/Table/Member.php';
require_once 'Was/Member/Exception.php';
require_once 'Was/Member/Table/Exception.php';
/**
 * @see Zend_Db
 * @see Zend_Db_Expr
 */
require_once 'Zend/Db.php';
require_once 'Zend/Db/Expr.php';
/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';
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
        //기본 레이아웃 설정
        $this->_helper->layout->setLayout('default1');
    }
    
    /**
     * 로그인 페이지 Action
     */
    public function signinAction() {
        $request = $this->getRequest();
        require_once 'Was/Auth/Form/Login.php';
        $loginForm = new Was_Auth_Form_Login();
        
        if ($this->getRequest()->isPost()) {
            if ($loginForm->isValid($request->getPost())) {
                //auth 인스턴스 생성
                $auth = Was_Auth::getInstance();
                //테이블 객체 생성
                $identityTable = new Was_Auth_Table_Identity();
                $accessTable = new Was_Auth_Table_Access();
                $historyTable = new Was_Auth_Table_History();
                //auth 객체 내부에 테이블 세팅
                $auth->setAccessTable($accessTable);
                $auth->setHistoryTable($historyTable);
                //adapter 생성
                $adapter = Was_Auth_AdapterFactory::getAdapter($identityTable);
                //getAllParams로 값들을 가져옴
                $param = $this->getAllParams();
                $adapter->setIdentity($param['id']);
                $adapter->setCredential($param['pw']);
                
                $authRes = $auth->authenticate($adapter);
                if ($authRes->isValid()) {
                    $this->redirect('/board/boardlist');
                } else if (!$authRes->isValid()) {
                    echo "<script>alert('아이디 또는 비밀번호가 일치하지 않습니다.')</script>";
                    echo "<script>history.back(-1);</script>";
                }
            } else {
                echo "<script>alert('아이디 또는 비밀번호를 입력해주세요')</script>";
                echo "<script>history.back(-1);</script>";
            }
        }
        
        $this->view->form = $loginForm;
    }
    
    /**
     * 로그아웃 페이지 Action
     */
    public function logoutAction() {
        //auth 인스턴스 생성
        $auth = Was_Auth::getInstance();
        //테이블 객체 생성
        $accessTable = new Was_Auth_Table_Access();
        $historyTable = new Was_Auth_Table_History();
        //auth 객체 내부에 테이블 세팅
        $auth->setAccessTable($accessTable);
        $auth->setHistoryTable($historyTable);
        //clear 메소드를 사용하기 위해 스토리지 가져오기, 세션 unset 및 access table 레코드 삭제
        $auth->getStorage()->clear();
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
    
    public function signupAction() {
        $request = $this->getRequest();
        
        require_once 'Was/Member/Form/Member.php';
        $form = new Was_Member_Form_Member();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                //사용자 입력값 및 모든 파라미터 가져옴
                $params = $this->getAllParams();
                //member 테이블에 먼저 등록
                $member = new Was_Member(new Was_Member_Table_Member());
                try {
                    $memberPk = $member->registMember(array(
                        'id'        => $params['id'],
                        'pw'        => $params['pw'],
                        'name'      => $params['name'],
                        'telNumber' => $params['telNumber'],
                        'email'     => $params['email']
                    ));
                    if (!$memberPk) {
                        echo "<script>alert('비어있는 항목이 존재합니다')</script>";
                        echo "<script>history.back(-1);</script>";
                    }
                } catch (Was_Member_Exception $e) {
                    echo "<script>alert('{$e->getMessage()}')</script>";
                    echo "<script>history.back(-1);</script>";
                } catch (Was_Member_Table_Exception $e) {
                    echo "<script>alert('{$e->getMessage()}')</script>";
                    echo "<script>history.back(-1);</script>";
                }
                if ($memberPk) {
                    $identityTable = new Was_Auth_Table_Identity();
                    $identityPk = $identityTable->insert(array(
                        'id'            => $params['id'],
                        'pw'            => md5($params['pw']),
                        'name'          => $params['name'],
                        'insertTime'    => new Zend_Db_Expr('NOW()')
                    ));
                }
                
                if ($memberPk && $identityPk) {
                    echo "<script>alert('회원가입이 완료됐습니다.')</script>";
                    echo "<script>location.href='/testform/signin';</script>";
                }
                
            } else {
                echo "맞지 않는 형식 존재";
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
    
    public function searchidAction() {
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                echo "test";
            }
        }
    }
    
}

