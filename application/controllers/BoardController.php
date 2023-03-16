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
        $request = $this->getRequest();
        $params = $request->getParams();
        
        $searchForm = new Zend_Form(array('class' => 'board-search'));
        $manageForm = new Was_Member_Form_Manage();
        $manageForm->addElement('hidden', 'isSearch', array('value' => 0));
        $searchForm->setMethod(Zend_Form::METHOD_GET);
        $searchForm->addSubForm($manageForm, 'search');
        
        //select 요소의 option 설정
        $category = $manageForm->getElement('category');
        $category->setMultiOptions(array(
            'writer'        => '작성자',
            'title'         => '제목',
            'insertTime'    => '작성일자'
        ));
        
        if (isset($params['search']['isSearch']) && $params['search']['isSearch'] == 1) {
            $this->setParam('page', null);
            $params['search']['isSearch'] = 0;
        }
        
        if ($this->getRequest()) {
            $boardTable = new Was_Board_Table_Board();
            $board = new Was_Board($boardTable->getAdapter());
            
            $boardWhere = array();
            if (isset($params['search'])) {
                $param = $params['search'];
                
                if ($param['category'] && $param['search']) {
                    $boardWhere['category'] = $param['category'];
                    $boardWhere['search'] = $param['search'];
                }
            }
            
            if (isset($params['fieldName']) && $params['fieldName'] && isset($params['order']) && $params['order']) {
                $boardWhere['fieldName'] = $params['fieldName'];
                $boardWhere['order'] = $params['order'];
                $this->view->fieldName = $params['fieldName'];
                $this->view->order = $params['order'];
            }
            
            $fetchAll = $board->reads($boardWhere);
            $paginator = Zend_Paginator::factory($fetchAll);
            $paginator->setCurrentPageNumber($this->getParam('page', 1));
            $this->view->paginator = $paginator;
        }
        
        //검색 시, 선택한 category 및 search 유지해줌
        $manageForm->setDefaults($params);
        
        $this->view->form = $searchForm;
    }
}

