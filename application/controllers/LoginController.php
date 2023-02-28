<?php
/**
 * 계정 컨트롤러
 */
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
        $this->_helper->layout->setLayout('default');
    }
    
    /**
     * 로그인 페이지 Action
     */
    public function signinAction() {
        $request = $this->getRequest();
        $loginForm = new Was_Auth_Form_Login();
        
        if ($this->getRequest()->isPost()) {
            $this->view->processResult = false;
            $this->view->processMessage = '';
            //form validate를 통과한 경우
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
                //errorCount 및 error 메세지를 초기화 후, 게시글 리스트로 리다이렉트
                if ($authRes->isValid()) {
                    $identityTable->update(array('errorCount' => 0, 'errorMessage' => ''), "id = '{$param['id']}'");
                    //세션에 member.position 정보를 넣기 위해서 조회
                    $memberTable = new Was_Member_Table_Member();
                    $select = $memberTable->select();
                    $select->where('id = ?', $param['id']);
                    $row = $memberTable->getAdapter()->fetchRow($select);
                    //session 정보를 불러온 뒤, position 정보를 session 에 삽입
                    $info = Zend_Session::namespaceGet('Was_Auth');
                    $info['storage']->position = $row['position'];
                    
                    $this->redirect('/board/list');
                } else if (!$authRes->isValid()) {
                    //로그인 에러문 설정
                    $errorString = '아이디 또는 비밀번호가 일치하지 않습니다.';
                    
                    //존재하는 회원인지 검색
                    $select = $identityTable->select();
                    $select->from($identityTable->getTableName(), array('errorCount'))
                           ->where('id = ?', $param['id']);
                    $row = $identityTable->getAdapter()->fetchRow($select);
                    //존재하는 회원인 경우 errorCount 및 errorMessage 갱신
                    if ($row) {
                        //업데이트 할 컬럼 설정
                        $set = array(
                            'errorCount'   => new Zend_Db_Expr('errorCount + 1'),
                            'errorMessage' => $errorString
                        );
                        //errorCount에 따른 처리
                        if ($row['errorCount'] >= 2 && $row['errorCount'] < 4) {
                            $errorString .= " 남은 로그인 횟수는 ". (5 - ($row['errorCount'] + 1)) . "번 입니다.";
                        } else if ($row['errorCount'] >= 4) {
                            $errorString .= " 계정이 잠금 처리 되었습니다.";
                            $set['authable'] = 0;
                        }
                        
                        $identityTable->update($set, "id = '{$param['id']}'");
                    }
                    //설정된 에러문으로 view 에 전달
                    $this->view->processResult = false;
                    $this->view->processMessage = $errorString;
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
    /**
     * 회원가입 페이지 Action
     */
    public function signupAction() {
        $request = $this->getRequest();
        
        $form = new Was_Member_Form_Member();
        //form element 요소 수정
        $submit = $form->getElement('submit');
        $submit->setLabel('가 입');
        $cancle = $form->getElement('cancle');
        $cancle->setAttrib('onclick', "window.close();");
        
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
                        'pw'            => new Zend_Db_Expr("MD5('{$params['pw']}')"),
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
    
    /**
     * 아이디 찾기 페이지 Action
     */
    public function searchidAction() {
        $request = $this->getRequest();
        
        $searchForm = new Was_Member_Form_SearchId();
        
        if ($this->getRequest()->isPost()) {
            $this->view->processResult = false;
            $this->view->processId = '';
            //form validate를 통과한 경우
            if ($searchForm->isValid($request->getPost())) {
                $memberTable = new Was_Member_Table_Member();
                $member = new Was_Member($memberTable);
                $params = $this->getAllParams();
                //아이디를 검색한 결과를 받고 체크
                $result = $member->searchId($params['name'], $params['telNumber']);
                //아이디가 존재하는 경우 아이디를 붙여줌
                if ($result) {
                    $this->view->processResult = true;
                    $this->view->processId .= $result;
                } else {
                    //존재하지 않는 경우, id 대신 경고문구 출력
                    $this->view->processId .= '존재하지 않는 회원입니다.';
                }
            } else {
                //form validate를 통과하지 못한 경우
                $this->view->processId = '이름 또는 휴대전화번호가 잘못된 형식입니다.';
            }
        }
        
        $this->view->form = $searchForm;
    }

    /**
     * 아이디 중복 체크를 처리하기 위한 별도의 action
     */
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
                $result['message'] = '중복된 아이디입니다.';
            }
        } else {
            //에외
            $result['result'] = false;
            $result['message'] = '';
        }
        
        $this->_helper->json->sendJson($result);
    }
    
}

