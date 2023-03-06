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
        $this->view->position = $info['storage']->position;
    }
    
    /**
     * 회원 관리 Action
     */
    public function manageAction() {
        $request = $this->getRequest();
        //form name을 지정해주기 위해 main 및 subform 설정
        $searchForm = new Zend_Form();
        $manageForm = new Was_Member_Form_Manage();
        $searchForm->setMethod(Zend_Form::METHOD_GET);
        $searchForm->addSubForm($manageForm, 'search');
        
        $params = $request->getParams();
        
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
            if ($session['storage']->position == 1) {
                $select->where("a.position != ?", $session['storage']->position);
            } else if ($session['storage']->position == 2) {
                $select->where("a.position != ?", $session['storage']->position)->where("a.position != ?", 1);
            }
            //Zend_Db_Table_Select 객체로 join을 사용할 때, 아래와 같이 설정해줘야함
            $select->setIntegrityCheck(false);
            //join 결과를 array로 가져옴
            $result = $memberTable->getAdapter()->fetchAll($select);
            
            //paginator 객체 생성
            $paginator = Zend_Paginator::factory($result);
            //현재 페이지를 _getParam 을 이용해 설정해줌(2번째 인수는 default로 설정할 값)
            $paginator->setCurrentPageNumber($this->_getParam('page', 1));
            //paginator 객체 할당
            $this->view->paginator = $paginator;
            //전체 member 레코드 갯수 구하기
            $memberTable2 = clone $memberTable;
            $select2 = $memberTable2->select();
            $select2->from($memberTable2->getTableName(), array('count' => new Zend_Db_Expr('COUNT(*)')));
            if ($session['storage']->position == 1) {
                $select2->where("position != ?", $session['storage']->position);
            } else if ($session['storage']->position == 2) {
                $select2->where("position != ?", $session['storage']->position)->where("position != ?", 1);
            }
            $total = $memberTable->getAdapter()->fetchRow($select2);
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
}

