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
        
        $identityTable = new Was_Auth_Table_Identity();
        $memberTable = new Was_Member_Table_Member();
        //table join 작업
        $select = $memberTable->select();
        $select->from(array('a' => $memberTable->getTableName()), array('pk', 'id', 'name', 'telNumber', 'email', 'position'))
               ->join(array('b' => $identityTable->getTableName()), "a.id = b.id")
               ->order("a.pk DESC");
        //Zend_Db_Table_Select 객체로 join을 사용할 때, 아래와 같이 설정해줘야함
        $select->setIntegrityCheck(false);
        //join 결과를 array로 가져옴
        $result = $memberTable->getAdapter()->fetchAll($select);
        
        //paginator 객체 생성
        $paginator = Zend_Paginator::factory($result);
        //현재 페이지를 _getParam 을 이용해 설정해줌(2번째 인수는 default로 설정할 값)
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        
        $this->view->paginator = $paginator;
        $this->view->totalCount = count($result);
        $this->view->recordCount = count($result);
        
        
        $form = new Was_Member_Form_Manage();
        //select 요소의 option 설정
        $category = $form->getElement('category');
        $category->setMultiOptions(array(
            'id'    => '아이디',
            'name'  => '이름'
        ));
        
        $this->view->form = $form;
    }
}

