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
        $this->_helper->layout->setLayout('default1');
    }
    
    public function boardlistAction() {
        $request = $this->getRequest();
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                echo "test";
            }
        }
    }
}

