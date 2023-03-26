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
            //검색조건, 정렬조건이 있는 경우, 메소드에 전달할 배열 설정
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
                
                $char = "";
                if ($params['order'] == "DESC") $char = "▼";
                else if ($params['order'] == "ASC") $char = "▲";
                $this->view->char = $char;
            }
            
            $fetchAll = $board->reads($boardWhere);
            $paginator = Zend_Paginator::factory($fetchAll);
            $paginator->setCurrentPageNumber($this->getParam('page', 1));
            $this->view->paginator = $paginator;
            //board 테이블의 전체 레코드 갯수를 구하기 위해 COUNT 사용
            $select = $boardTable->select();
            $select->from($boardTable->getTableName(), array('count' => new Zend_Db_Expr('COUNT(*)')));
            $total = $boardTable->getAdapter()->fetchRow($select);
            //board 테이블의 전체 레코드 갯수 전달
            $this->view->totalCount = $total['count'];
            //select된 레코드 갯수 전달
            $this->view->recordCount = count($fetchAll);
        }
        
        //검색 시, 선택한 category 및 search 유지해줌
        $manageForm->setDefaults($params);
        
        $this->view->form = $searchForm;
    }
    
    /**
     * 게시글 작성 Action
     */
    public function writeAction() {
        $request = $this->getRequest();
        //boardForm 에 필요한 요소 추가 및 수정
        $boardForm = new Was_Board_Form_Board();
        
        $boardForm->addElement('hidden', 'memberPk');
        
        $uploadFile = $boardForm->getElement('uploadFile');
        $uploadFile->setName('uploadFile1');
        $uploadFile->removeDecorator('HtmlTag');
        
        $submit = $boardForm->getElement('submit');
        $submit->setLabel('작 성');
        
        if ($this->getRequest()->isPost()) {
            $params = $request->getParams();
            //작성 완료시 view 에 리턴해줄 변수 설정
            $this->view->writeResult = false;
            $this->view->writeMessage = '';
            //파일 업로드를 위한 배열 선언
            $fileArrays = array();
            
            $contents = array(
                'title'     => $params['title'],
                'content'   => $params['content'],
                'writer'    => $params['writer']
            );
            //파일 업로드를 위해, 업로드한 파일 정보들을 담은 배열 생성
            $files = $_FILES;
            foreach ($files as $file) {
                if (!$file['name'] || $file['error']) {
                    continue;
                }
                $fileType = explode('/', $file['type']);
                $fileContent = file_get_contents($file['tmp_name']);
                
                $temp = array(
                    'name'      => $file['name'],
                    'type'      => $fileType[1],
                    'size'      => $file['size'],
                    'content'   => $fileContent
                );
                array_push($fileArrays, $temp);
            }
            
            if ($boardForm->isValid($contents)) {
                //board 클래스 세팅 및 허용된 파일 확장자를 불러옴
                $boardTable = new Was_Board_Table_Board();
                $board = new Was_Board($boardTable->getAdapter());
                
                $db = Zend_Db::factory('mysqli', $boardTable->getAdapter()->getConfig());
                //트랜잭션 시작
                $db->beginTransaction();
                //게시글 작성
                try {
                    $result = $board->write($contents, $params['memberPk']);
                    
                    if ($fileArrays) {
                        foreach ($fileArrays as $fileArray) {
                            $fileResult = $board->addFile($result['pk'], $fileArray);
                            if (!$fileResult) {
                                break;
                            }
                        }
                    }
                    if (!$result) {
                        $db->rollBack();
                        $this->view->writeMessage = "게시글 작성에 실패했습니다.";
                    } else if (isset($fileResult) && !$fileResult) {
                        $db->rollBack();
                        $this->view->writeMessage = "파일 업로드를 실패하여 게시글 작성에 실패했습니다.";
                    } else {
                        $this->view->writeResult = true;
                        $this->view->writeMessage = "게시글 작성했습니다.";
                    }
                } catch (Was_Board_Exception $e) {
                    $db->rollBack();
                    $this->view->writeMessage = $e->getMessage();
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->view->writeMessage = $e->getMessage();
                }
                $db->commit();
            } else {
                $this->view->writeMessage = "게시글 작성 형식을 지켜주세요.";
            }
        }
        
        $this->view->boardForm = $boardForm;
    }
    
    /**
     * 게시글 조회 Action
     */
    public function viewAction() {
        $request = $this->getRequest();
        
        if ($this->getRequest()) {
            $this->view->boardResult = false;
            $this->view->boardMessage = '';
            
            $params = $request->getParams();
            $pk = $params['pk'];
            
            $boardTable = new Was_Board_Table_Board();
            $board = new Was_Board($boardTable->getAdapter());
            //해당 게시글이 보유한 내용, 파일, 댓글을 가져옴
            try {
                $this->view->board = $board->read($pk);
                $this->view->replys = $board->getReply($pk);
                $this->view->boardResult = true;
            } catch (Was_Board_Exception $e) {
                $this->view->boardMessage = "존재하지 않는 게시글입니다.";
            }
            
        }
        //댓글 작성을 위해 hidden 요소 추가
        $replyForm = new Was_Board_Form_Reply();
        $replyForm->addElement('hidden', 'boardPk');
        $replyForm->addElement('hidden', 'memberPk');
        
        $this->view->replyForm = $replyForm;
    }
    /**
     * 게시글 삭제 Action
     */
    public function deleteBoardAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array(
            'result'   => false,
            'message'  => ''
        );
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            
            $boardTable = new Was_Board_Table_Board();
            $board = new Was_Board($boardTable->getAdapter());
            
            try {
                $deleteResult = $board->delete($params['pk']);
                
                if (!$deleteResult) {
                    $result['message'] = '게시글을 삭제하는데 문제가 발생했습니다.';
                } else {
                    $result['result'] = true;
                    $result['message'] = '게시글을 삭제했습니다.';
                }
            } catch (Was_Board_Exception $e) {
                $result['message'] = '존재하지 않는 게시글입니다.';
            }
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '잘못된 요청입니다.';
        }
        
        $this->_helper->json->sendJson($result);
    }
    
    /**
     * 댓글 작성 Action
     */
    public function writeReplyAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array(
            'result'   => false,
            'message'  => ''
        );
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            
            $replyTable = new Was_Board_Table_BoardReply();
            $board = new Was_Board($replyTable->getAdapter());
            
            try {
                $writeReplyResult = $board->writeReply($params['boardPk'], $params['memberPk'], $params['content']);
                
                if (!$writeReplyResult) {
                    $result['message'] = '댓글을 등록 중 문제가 발생했습니다.';
                } else {
                    $result['result'] = true;
                    $result['message'] = '댓글을 등록했습니다.';
                }
            } catch (Was_Board_Exception $e) {
                $result['message'] = '존재하지 않는 게시글입니다.';
            }
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '잘못된 요청입니다.';
        }
        
        $this->_helper->json->sendJson($result);
    }
}

