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

