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
        $this->_helper->layout->setLayout('board');
        
        $info = Zend_Session::namespaceGet('Was_Auth');
        $this->view->name = $info['storage']->name;
        $this->view->id = $info['storage']->id;
        $this->view->position = $info['storage']->position;
        //로그인을 하지않아 session 이 없는 경우, 로그인 페이지로 리다이렉트
        if (!Zend_Session::namespaceGet("Was_Auth")) {
            $this->redirect('/login/signin');
        }
    }
    /**
     * 게시글 리스트 Action
     */
    public function listAction() {
    }
}

