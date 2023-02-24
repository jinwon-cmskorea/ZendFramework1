<?php
/**
 * 계정 컨트롤러
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

class LoginController extends Zend_Controller_Action {
    /*
     * {@inheritDoc}
     * @see Zend_Controller_Action::init()
     */
    public function init() {
        //기본 레이아웃 설정
        $this->_helper->layout->setLayout('layout');
    }
    
    /**
     * 로그인 페이지 Action
     */
    public function signinAction() {
        $request = $this->getRequest();
        require_once 'Was/Auth/Form/Login.php';
        $loginForm = new Was_Auth_Form_Login();
        
        if ($this->getRequest()->isPost()) {
            $this->view->processResult = false;
            $this->view->processMessage = '';
            
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
                    $this->view->processResult = false;
                    $this->view->processMessage = '아이디 또는 비밀번호가 일치하지 않습니다.';
                }
            } else {
                $this->view->processResult = false;
                $this->view->processMessage = '아이디 또는 비밀번호를 입력해 주세요.';
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
    
    public function signupAction() {
        $request = $this->getRequest();
        
        require_once 'Was/Member/Form/Member.php';
        $form = new Was_Member_Form_Member();
        
        if ($this->getRequest()->isPost()) {
            //view 페이지에서 호출할 수 있는 변수 선언
            $this->view->processResult = false;
            $this->view->processMessage = '';
            
            if ($form->isValid($request->getPost())) {
                //사용자 입력값 및 모든 파라미터 가져옴
                $params = $this->getAllParams();
                //member 테이블에 먼저 등록
                $memberTable = new Was_Member_Table_Member();
                $db = Zend_Db::factory('mysqli', $memberTable->getAdapter()->getConfig());
                //트랜잭션 시작
                $db->beginTransaction();
                $member = new Was_Member($memberTable);
                try {
                    $memberPk = false;
                    $memberPk = $member->registMember(array(
                        'id'        => $params['id'],
                        'pw'        => $params['pw'],
                        'name'      => $params['name'],
                        'telNumber' => $params['telNumber'],
                        'email'     => $params['email']
                    ));
                    
                    $identityTable = new Was_Auth_Table_Identity();
                    $identityPk = $identityTable->insert(array(
                        'id'            => $params['id'],
                        'pw'            => md5($params['pw']),
                        'name'          => $params['name'],
                        'insertTime'    => new Zend_Db_Expr('NOW()')
                    ));
                } catch (Was_Member_Exception $e) {
                    $db->rollBack();
                    $this->view->processMessage = $e->getMessage();
                } catch (Was_Member_Table_Exception $e) {
                    $db->rollBack();
                    $this->view->processMessage = $e->getMessage();
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->view->processMessage = $e->getMessage();
                }
                
                //이상 없이 동작을 수행했으면 트랜잭션 commit
                $db->commit();
                
                if ($memberPk && $identityPk) {
                    $this->view->processResult = true;
                    $this->view->processMessage = '회원가입에 성공했습니다.';
                }
            } else {
                $this->view->processMessage = '회원가입이 실패하였습니다.';
            }
        }
        
        $this->view->form = $form;
    }

    //아이디 중복 체크를 처리하기 위한 별도의 action
    public function duplicateIdAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array(
            'result'    => false,
            'message'   => '',
        );
        //XmlHttpRequest 인지 확인(ajax)
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            //member 테이블에서 중복된 아이디가 존재하는 지 확인
            $member = new Was_Member_Table_Member();
            $select = $member->select();
            $select->from($member->getTableName(), array('count' => new Zend_Db_Expr('COUNT(id)')))
                   ->where('id = ?', $params['userId']);
            $row = $member->getAdapter()->fetchRow($select);
            //'count' 값이 1 이상이라면 중복된 아이디가 존재한다는 뜻이므로 처리
            if ($row['count'] > 0) {
                $result['result'] = true;
                $result['message'] = 'duplicate';
            }
        } else {
            //에외
            $result['result'] = false;
            $result['message'] = '';
        }
        
        $this->_helper->json->sendJson($result);
    }
    
}

