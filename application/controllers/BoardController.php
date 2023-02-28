<?php
/**
 * @see Bootstrap
 */
require_once __DIR__ . '/../Bootstrap.php';

class BoardController extends Zend_Controller_Action {
    /*
     * {@inheritDoc}
     * @see Zend_Controller_Action::init()
     */
    public function init() {
        //기본 레이아웃 설정
        $this->_helper->layout->setLayout('boardlayout');
        
        $info = Zend_Session::namespaceGet('Was_Auth');
        $this->view->name = $info['storage']->name;
        $this->view->position = $info['storage']->position;
    }
    /**
     * 게시글 리스트 Action
     */
    public function boardlistAction() {
    }
    
    /**
     * 회원 관리 Action
     */
    public function manageAction() {
        
    }
}

