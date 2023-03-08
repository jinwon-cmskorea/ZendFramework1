<?php
/**
 * @see Bootstrap
 */
require_once __DIR__ . '/../Bootstrap.php';

class ManageController extends Zend_Controller_Action {
    /*
     * {@inheritDoc}
     * @see Zend_Controller_Action::init()
     */
    public function init() {
        //기본 레이아웃 설정
        $this->_helper->layout->setLayout('board');
        
        $info = Zend_Session::namespaceGet('Was_Auth');
        $this->view->name = $info['storage']->name;
        $this->view->id = $info['storage']->id;
        $this->view->position = $info['storage']->position;
    }
    
    /**
     * 회원 관리 Action
     */
    public function manageAction() {
        $request = $this->getRequest();
        //form name을 지정해주기 위해 main 및 subform 설정
        $searchForm = new Zend_Form(array('class' => 'member-search'));
        $manageForm = new Was_Member_Form_Manage();
        $manageForm->addElement('hidden', 'isSearch', array('value' => 0));
        $searchForm->setMethod(Zend_Form::METHOD_GET);
        $searchForm->addSubForm($manageForm, 'search');
        
        $params = $request->getParams();
        
        /*
         * 만약 검색 버튼을 눌렀을 경우, page 파라미터를 없애고, 검색 여부를 0으로 초기화
         * 페이지네이터의 기본 페이지가 1로 설정되있으므로 1페이지로 이동
         */
        if (isset($params['search']['isSearch']) && $params['search']['isSearch'] == 1) {
             $this->setParam('page', null);
             $params['search']['isSearch'] = 0;
        }
        if ($this->getRequest()) {
            $identityTable = new Was_Auth_Table_Identity();
            $memberTable = new Was_Member_Table_Member();
            //position 을 알기 위해 세션을 가져옴
            $session = Zend_Session::namespaceGet('Was_Auth');
            //검색 값이 존재하는 경우
            if (isset($params['search'])) {
                $param = $params['search'];
                
                if ($param['category'] && $param['search']) {
                    //카테고리 및 찾으려하는 정보를 조건으로 가지는 레코드 검색
                    $select = $memberTable->select();
                    $select->from(array('a' => $memberTable->getTableName()), array('pk', 'id', 'name', 'telNumber', 'email', 'position'))
                    ->join(array('b' => $identityTable->getTableName()), "a.id = b.id")
                    ->order("a.pk DESC")
                    ->where("a.{$param['category']} LIKE ?", "%".$param['search']."%");
                } else {
                    //table join 작업
                    $select = $memberTable->select();
                    $select->from(array('a' => $memberTable->getTableName()), array('pk', 'id', 'name', 'telNumber', 'email', 'position'))
                    ->join(array('b' => $identityTable->getTableName()), "a.id = b.id")
                    ->order("a.pk DESC");
                }
            } else {
                //table join 작업
                $select = $memberTable->select();
                $select->from(array('a' => $memberTable->getTableName()), array('pk', 'id', 'name', 'telNumber', 'email', 'position'))
                ->join(array('b' => $identityTable->getTableName()), "a.id = b.id")
                ->order("a.pk DESC");
            }
            
            //회원 등급에 따라, 다른 리스트가 보여질 수 있도록 where 절 추가
            $select->where("a.position > ?", $session['storage']->position);
            //Zend_Db_Table_Select 객체로 join을 사용할 때, 아래와 같이 설정해줘야함
            $select->setIntegrityCheck(false);
            //join 결과를 array로 가져옴
            $result = $memberTable->getAdapter()->fetchAll($select);
            
            //paginator 객체 생성
            $paginator = Zend_Paginator::factory($result);
            //현재 페이지를 _getParam 을 이용해 설정해줌(2번째 인수는 default로 설정할 값)
            $paginator->setCurrentPageNumber($this->getParam('page', 1));
            //paginator 객체 할당
            $this->view->paginator = $paginator;
            //전체 member 레코드 갯수 구하기
            //이전에 사용한 select 객체 초기화
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::WHERE);
            $select->reset(Zend_Db_Select::FROM);
            $select->reset(Zend_Db_Select::ORDER);
            $select->from($memberTable->getTableName(), array('count' => new Zend_Db_Expr('COUNT(*)')));
            $select->where("position > ?", $session['storage']->position);
            $total = $memberTable->getAdapter()->fetchRow($select);
            //전체 레코드 갯수와 검색된 레코드 갯수 할당
            $this->view->totalCount = $total['count'];
            $this->view->recordCount = count($result);
        }
        
        //select 요소의 option 설정
        $category = $manageForm->getElement('category');
        $category->setMultiOptions(array(
            'id'    => '아이디',
            'name'  => '이름'
        ));
        //검색 시, 선택한 category 및 search 유지해줌
        $manageForm->setDefaults($params);
        
        $this->view->form = $searchForm;
    }
    
    /**
     * 회원정보 수정 Action
     */
    public function modifyAction() {
        $request = $this->getRequest();
        //상단 바가 필요없으므로, default 레이아웃 사용
        $this->_helper->layout->disableLayout();
        $this->_helper->layout->setLayout('default');
        
        $params = $request->getParams();
        
        $modifyForm = new Was_Member_Form_Member();
        $pwForm = new Was_Auth_Form_Password();
        
        //회원수정(member) form 에 비밀번호 칸 삭제 및 비밀번호 변경 버튼 추가
        $modifyForm->removeElement('pw');
        $modifyForm->addElement('button', 'change-pw', array(
            'class'     => 'change-pw-btn',
            'label'     => '비밀번호 변경',
            'order'     => 1
        ));
        $changePw = $modifyForm->getElement('change-pw');
        $changePw->removeDecorator('label');
        $changePw->removeDecorator('HtmlTag');
        //제출 버튼 문구 변경
        $submit = $modifyForm->getElement('submit');
        $submit->setLabel('수 정');
        //취소 누를 시, 창 닫기
        $cancle = $modifyForm->getElement('cancle');
        $cancle->setAttrib("onclick", "window.close()");
        //하단의 버튼 displaygroup 속성 변경
        $group = $modifyForm->getDisplayGroup('btns');
        $group->clearDecorators();
        $group->addDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'modify-button'))
        ));
        
        //get 파라미터로 받아온 userId를 통해 레코드를 가져옴
        if (isset($params['userId']) && $params['userId']) {
            $memberTable = new Was_Member_Table_Member();
            //클릭한 userId 의 회원 정보를 가져옴
            $select = $memberTable->select();
            $select->from($memberTable->getTableName(), array('id', 'name', 'telNumber', 'email', 'position'))
            ->where("id = ?", $params['userId']);
            $row = $memberTable->getAdapter()->fetchRow($select);
            //최종 관리자는 회원정보 수정이 불가능하므로 alert 창 출력
            if ($row['position'] == 1) {
                $this->view->message = "최종관리자는 회원 정보를 수정할 수 없습니다.";
            }
            //각 input 창에 회원 정보 출력
            $id = $modifyForm->getElement('id');
            $id->setValue($row['id']);
            $id->setAttrib('readonly', true);
            
            $name = $modifyForm->getElement('name');
            $name->setValue($row['name']);
            
            $tel = preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3" ,$row['telNumber']);
            $telNumber = $modifyForm->getElement('telNumber');
            $telNumber->setValue($tel);
            
            $email = $modifyForm->getElement('email');
            $email->setValue($row['email']);
            
            $this->view->userId = $params['userId'];
        }
        
        if ($this->getRequest()->isPost()) {
            $params = $request->getPost();
            Zend_Debug::dump($params);
            Zend_Debug::dump($modifyForm->isValid($params));
        }
        
        //form 을 view에 전달
        $this->view->modifyForm = $modifyForm;
        $this->view->pwForm = $pwForm;
    }
    
    /**
     * 로그인 제한 관리 Action
     */
    public function authableManageAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array(
            'result'    => false,
            'message'   => ''
        );
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            $set = array();
            $status = "로그인 불가";
            if ($params['checked'] == "true") {
                $set['authable'] = 0;
                $status = "로그인 차단";
            } else if ($params['checked'] == "false") {
                $set['authable'] = 1;
                $set['errorCount'] = 0;
                $status = "로그인 허용";
            }
            
            $identityTable = new Was_Auth_Table_Identity();
            $row = $identityTable->update($set, "id = '{$params['userId']}'");
            if ($row) {
                $result['result'] = true;
                $result['message'] = "로그인 제한 상태가 [" . $status . "] 상태로 변경됐습니다.";
            }
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '';
        }
        
        $this->_helper->json->sendJson($result);
    }
    /**
     * 관리자로 등급을 변경하는 Action
     */
    public function changeManagerAction() {
         $this->_helper->layout->disableLayout();
         
         $result = array(
             'result'   => false,
             'message'  => ''
         );
         
         if($this->getRequest()->isXmlHttpRequest()) {
             $params = $this->getAllParams();
             $memberTable = new Was_Member_Table_Member();
             //등급 업데이트, 정상적으로 수행됐다면 영향받은 row 갯수 리턴
             $num = $memberTable->update(array('position' => 2), "id = '{$params['userId']}'");
             if ($num) {
                 $result['result'] = true;
                 $result['message'] = $params['userId'] . "회원의 등급이 관리자로 변경됐습니다.";
             } else {
                 $result['message'] = "회원 등급 변경 중 문제가 발생했습니다.";
             }
         } else {
             //예외
             $result['result'] = false;
             $result['message'] = '잘못된 요청입니다.';
         }
         
         $this->_helper->json->sendJson($result);
    }
    
    /**
     * 일반 회원으로 등급을 변경하는 Action
     */
    public function changeNormalAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array(
            'result'   => false,
            'message'  => ''
        );
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            $memberTable = new Was_Member_Table_Member();
            //등급 업데이트, 정상적으로 수행됐다면 영향받은 row 갯수 리턴
            $num = $memberTable->update(array('position' => 3), "id = '{$params['userId']}'");
            if ($num) {
                $result['result'] = true;
                $result['message'] = $params['userId'] . "회원의 등급이 일반 회원으로 변경됐습니다.";
            } else {
                $result['message'] = "회원 등급 변경 중 문제가 발생했습니다.";
            }
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '잘못된 요청입니다.';
        }
        
        $this->_helper->json->sendJson($result);
    }
    
    /**
     * 회원을 삭제하는 Action
     */
    public function deleteMemberAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array(
            'result'   => false,
            'message'  => ''
        );
        
        if($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            $memberTable = new Was_Member_Table_Member();
            $identitiyTable = new Was_Auth_Table_Identity();
            $db = Zend_Db::factory('mysqli', $memberTable->getAdapter()->getConfig());
            //트랜잭션 시작
            $db->beginTransaction();
            try {
                $num = $memberTable->getAdapter()->delete($memberTable->getTableName(), "id = '{$params['userId']}'");
                if ($num != 1) {
                    //문제가 발생 시, rollback
                    $db->rollBack();
                    $result['message'] = 'member 테이블의 레코드 삭제 중 문제가 발생했습니다.';
                }
                
                $num2 = $identitiyTable->getAdapter()->delete($identitiyTable->getTableName(), "id = '{$params['userId']}'");
                if ($num2 != 1) {
                    //문제가 발생 시, rollback
                    $db->rollBack();
                    $result['message'] = 'identity 테이블의 레코드 삭제 중 문제가 발생했습니다.';
                }
            } catch (Zend_Db_Exception $e) {
                //예외 발생시 rollback
                $db->rollBack();
                $result['message'] = '회원정보 삭제 중 문제가 발생했습니다.';
            }
            $db->commit();
            $result['result'] = true;
            $result['message'] = $params['userId'] . "회원 정보 삭제가 완료됐습니다.";
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '잘못된 요청입니다.';
        }
        
        $this->_helper->json->sendJson($result);
    }
    
    /**
     * 현재 비밀번호를 체크하는 Action
     */
    public function checkPwAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array('result' => false);
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            //비밀번호가 들어있는 테이블인 identity 를 가져옴
            $identityTable = new Was_Auth_Table_Identity();
            //비밀번호 컬럼을 가져옴
            $select = $identityTable->select();
            $select->from($identityTable->getTableName(), array('pw'))->where("id = ?", $params['userId']);
            $row = $identityTable->getAdapter()->fetchRow($select);
            
            if (md5($params['nowPw']) == $row['pw']) {
                $result['result'] = true;
            }
        }
        
        $this->_helper->json->sendJson($result);
    }
}

