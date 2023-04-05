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
            //board 클래스 세팅 및 허용된 파일 확장자를 불러옴
            $boardTable = new Was_Board_Table_Board();
            $board = new Was_Board($boardTable->getAdapter());
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
            $allowType = $board->getValidFileTypes();
            $checkFile = true;
            
            foreach ($files as $file) {
                if (!$file['name'] || $file['error']) {
                    continue;
                }
                $fileType = explode('/', $file['type']);
                $fileContent = file_get_contents($file['tmp_name']);
                
                if (array_search($fileType[1], $allowType) === false) {
                    $checkFile = false;
                    break;
                }
                
                $temp = array(
                    'name'      => $file['name'],
                    'type'      => $fileType[1],
                    'size'      => $file['size'],
                    'content'   => $fileContent
                );
                array_push($fileArrays, $temp);
            }
            
            if ($boardForm->isValid($contents) && $checkFile) {
                $db = Zend_Db::factory('mysqli', $boardTable->getAdapter()->getConfig());
                //트랜잭션 시작
                $db->beginTransaction();
                //게시글 작성
                try {
                    $result = $board->write($contents, $params['memberPk']);
                    
                    if ($fileArrays) {
                        foreach ($fileArrays as $fileArray) {
                            $fileResult = $board->addFile($result['pk'], $fileArray);
                            if (!$fileResult || is_numeric($fileResult)) {
                                break;
                            }
                        }
                    }
                    if (!$result) {
                        $db->rollBack();
                        $this->view->writeMessage = "게시글 작성에 실패했습니다.";
                    } else if (isset($fileResult) && (!$fileResult || is_numeric($fileResult))) {
                        $db->rollBack();
                        if ($fileResult == false) {
                            $this->view->writeMessage = "파일 업로드를 실패하여 게시글 수정에 실패했습니다.";
                        } else if ($fileResult === Was_Board::INVALID_FILE_TYPE) {
                            $this->view->writeMessage = "허용되지 않는 파일 확장자입니다.\\n업로드 가능한 파일은 jpeg, jpg, gif, png, pdf 입니다.";
                        } else if ($fileResult === Was_Board::INVALID_FILE_SIZE) {
                            $this->view->writeMessage = "파일의 크기가 너무 큽니다.\\n최대 3MB의 파일까지만 업로드 가능합니다.";
                        }
                        $board->delete($result['pk']);
                    } else {
                        $this->view->writeResult = true;
                        $this->view->writeMessage = "게시글 작성했습니다.";
                        $this->view->pk = $result['pk'];
                        $db->commit();
                    }
                } catch (Was_Board_Exception $e) {
                    $db->rollBack();
                    $this->view->writeMessage = $e->getMessage();
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->view->writeMessage = $e->getMessage();
                }
            } else if (!$checkFile) {
                $this->view->writeMessage = "허용되지 않는 파일 확장자가 존재합니다.\\n업로드 가능한 파일은 jpeg, jpg, gif, png, pdf 입니다.";
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
                $this->view->files = $board->getFiles($pk);
                $this->view->boardResult = true;
            } catch (Was_Board_Exception $e) {
                $this->view->boardMessage = "존재하지 않는 게시글입니다.";
            } catch (Zend_Db_Exception $e) {
                $this->view->boardMessage = "db 작업 중 문제가 발생했습니다.";
            }
            
        }
    }
    
    /**
     * 게시글 수정 Action
     */
    public function editAction() {
        $request = $this->getRequest();
        //boardForm 에 필요한 요소 추가 및 수정
        $boardForm = new Was_Board_Form_Board();
        
        $boardForm->addElement('hidden', 'boardPk');
        
        $uploadFile = $boardForm->getElement('uploadFile');
        $uploadFile->setName('uploadFile1');
        $uploadFile->removeDecorator('HtmlTag');
        
        $submit = $boardForm->getElement('submit');
        $submit->setLabel('수 정');
        
        //pk를 가져와 해당 게시글의 정보를 가져옴
        if ($this->getRequest()) {
            $params = $request->getParams();
            $pk = $params['pk'];
            
            $boardTable = new Was_Board_Table_Board();
            $board = new Was_Board($boardTable->getAdapter());
            
            try {
                $row = $board->read($pk);
                $this->view->board = $row;
                $this->view->boardFiles = $board->getFiles($pk);
                $boardForm->setDefaults(array(
                    'boardPk'   => $row['pk'],
                    'title'     => $row['title'],
                    'content'   => $row['content'],
                    'writer'    => $row['writer']
                ));
            } catch (Was_Board_Exception $e) {
                $this->view->editResult = false;
                $this->view->editNotBoard = '존재하지 않는 게시글입니다.';
            }
            
            //수정 버튼을 누를 시, post 값을 가져와서 업데이트 진행
            if ($this->getRequest()->isPost()) {
                $this->view->editResult = false;
                $this->view->editMessage = '';
                //파일 추가 업로드를 위한 배열 선언
                $fileArrays = array();
                
                $contents = array(
                    'title'     => $params['title'],
                    'content'   => $params['content'],
                    'writer'    => $params['writer']
                );
                
                //파일 업로드를 위해, 업로드한 파일 정보들을 담은 배열 생성
                $files = $_FILES;
                $allowType = $board->getValidFileTypes();
                $checkFile = true;
                
                foreach ($files as $file) {
                    if (!$file['name'] || $file['error']) {
                        continue;
                    }
                    $fileType = explode('/', $file['type']);
                    $fileContent = file_get_contents($file['tmp_name']);
                    
                    if (array_search($fileType[1], $allowType) === false) {
                        $checkFile = false;
                        break;
                    }
                    
                    $temp = array(
                        'name'      => $file['name'],
                        'type'      => $fileType[1],
                        'size'      => $file['size'],
                        'content'   => $fileContent
                    );
                    array_push($fileArrays, $temp);
                }
                
                if ($boardForm->isValid($contents) && $checkFile) {
                    //board 클래스 세팅
                    $boardTable = new Was_Board_Table_Board();
                    $board = new Was_Board($boardTable->getAdapter());
                    
                    $db = Zend_Db::factory('mysqli', $boardTable->getAdapter()->getConfig());
                    //트랜잭션 시작
                    $db->beginTransaction();
                    //게시글 수정
                    try {
                        //게시글 수정 메소드 호출
                        $result = $board->edit($contents, $pk);
                        
                        if ($fileArrays) {
                            foreach ($fileArrays as $fileArray) {
                                $fileResult = $board->addFile($pk, $fileArray);
                                if ($fileResult == false || is_numeric($fileResult)) {
                                    break;
                                }
                            }
                        }
                        if (!$result) {
                            $db->rollBack();
                            $this->view->editMessage = "게시글 수정에 실패했습니다.";
                        } else if (isset($fileResult) && (!$fileResult || is_numeric($fileResult))) {
                            $db->rollBack();
                            if ($fileResult == false) {
                                $this->view->editMessage = "파일 업로드를 실패하여 게시글 수정에 실패했습니다.";
                            } else if ($fileResult === Was_Board::INVALID_FILE_TYPE) {
                                $this->view->editMessage = "허용되지 않는 파일 확장자입니다.\\n업로드 가능한 파일은 jpeg, jpg, gif, png, pdf 입니다.";
                            } else if ($fileResult === Was_Board::INVALID_FILE_SIZE) {
                                $this->view->editMessage = "파일의 크기가 너무 큽니다.\\n최대 3MB의 파일까지만 업로드 가능합니다.";
                            }
                        } else {
                            $this->view->editResult = true;
                            $this->view->editMessage = "게시글 수정했습니다.";
                        }
                    } catch (Was_Board_Exception $e) {
                        $db->rollBack();
                        $this->view->editMessage = $e->getMessage();
                    } catch (Zend_Db_Exception $e) {
                        $db->rollBack();
                        $this->view->editMessage = $e->getMessage();
                    }
                    $db->commit();
                } else if (!$checkFile) {
                    $this->view->editMessage = "허용되지 않는 파일 확장자가 존재합니다.\\n업로드 가능한 파일은 jpeg, jpg, gif, png, pdf 입니다.";
                } else {
                    $this->view->editMessage = "게시글 작성 형식을 지켜주세요.";
                }
            }
        }
        
        $this->view->boardForm = $boardForm;
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
    
    /**
     * 댓글 삭제 Action
     */
    public function deleteReplyAction() {
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
                $deleteReplyResult = $board->deleteReply($params['replyPk']);
                
                if (!$deleteReplyResult) {
                    $result['message'] = '댓글을 삭제 중 문제가 발생했습니다.';
                } else {
                    $result['result'] = true;
                    $result['message'] = '댓글을 삭제했습니다.';
                }
            } catch (Zend_Db_Exception $e) {
                $result['message'] = '댓글을 삭제 중 문제가 발생했습니다.';
            }
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '잘못된 요청입니다.';
        }
        
        $this->_helper->json->sendJson($result);
    }
    
    /**
     * 이미지 미리보기 Action
     */
    public function viewImageAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->layout->setLayout('default');
        $request = $this->getRequest();
        
        if ($this->getRequest()) {
            //filePk를 가져옴
            $params = $request->getParams();
            //파일 정보를 불러오기 위해 테이블 클래스 객체 생성
            $fileTable = new Was_Board_Table_File();
            $detailsTable = new Was_Board_Table_FileDetails();
            
            //pk, 파일 이름, 타입, 크기를 가져옴
            $fileArray = array();
            $select = $fileTable->select();
            $select->from($fileTable->getTableName(), array('pk', 'filename', 'fileType', 'fileSize'))
            ->where('pk = ?', $params['filePk']);
            $fileArray = $fileTable->getAdapter()->fetchRow($select);
            
            //filePk 에 해당하는 파일 내용을 가져옴
            $select2 = $detailsTable->select();
            $select2->from($detailsTable->getTableName(), array('content'))
            ->where('filePk = ?', $params['filePk']);
            $row = $detailsTable->getAdapter()->fetchRow($select2);
            $fileArray['content'] = $row['content'];
            
            $this->view->fileArray = $fileArray;
        }
    }
    
    /**
     * 파일 다운로드 Action
     */
    public function fileDownloadAction() {
        $this->_helper->layout->disableLayout();
        //파일 다운로드를 위해 임시로 저장할 디렉토리 지정
        $bootstrap = $this->getInvokeArg('bootstrap');
        $temp = $bootstrap->getOption('temp');
        $tempPath = $temp['path'];
        
        $request = $this->getRequest();
        
        if ($this->getRequest()) {
            //filePk를 가져옴
            $params = $request->getParams();
            //파일 정보를 불러오기 위해 테이블 클래스 객체 생성
            $fileTable = new Was_Board_Table_File();
            $detailsTable = new Was_Board_Table_FileDetails();
            
            //pk, 파일 이름, 타입, 크기를 가져옴
            $fileArray = array();
            $select = $fileTable->select();
            $select->from($fileTable->getTableName(), array('pk', 'filename', 'fileType', 'fileSize'))
            ->where('pk = ?', $params['filePk']);
            $fileArray = $fileTable->getAdapter()->fetchRow($select);
            
            //filePk 에 해당하는 파일 내용을 가져옴
            $select2 = $detailsTable->select();
            $select2->from($detailsTable->getTableName(), array('content'))
            ->where('filePk = ?', $params['filePk']);
            $row = $detailsTable->getAdapter()->fetchRow($select2);
            $fileArray['content'] = $row['content'];
            
            /*
             * data 폴더에 파일을 조립하고 저장
             * 한글 파일명의 경우, 그대로 사용하면 인식을 못해서 에러 발생
             * 그래서 iconv 함수를 이용해 인코딩을 변경해줌
             */
            $tmpFileName = iconv('utf-8', 'cp949', $fileArray['filename']);
            $saveDir = $tempPath . $tmpFileName;
            file_put_contents($saveDir, base64_decode($fileArray['content']));
            
            //파일 다운로드 기능을 위한 헤더 설정
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='. $tmpFileName);
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '. $fileArray['fileSize']);
            header('Expires: 0');
            header('Pragma: public');
            
            ob_clean();//출력 없이 버퍼만 비우고, 종료는 안함
            flush();//버퍼에 저장되어있는 내용을 브라우저로 출력후 버퍼를 비움
            readfile($saveDir);
            unlink($saveDir);
            
            exit;
        }
    }
    
    /**
     * 파일 삭제 Action
     */
    public function fileDeleteAction() {
        $this->_helper->layout->disableLayout();
        
        $result = array(
            'result'   => false,
            'message'  => ''
        );
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getAllParams();
            
            $boardTable = new Was_Board_Table_Board();
            $board = new Was_Board($boardTable->getAdapter());
            
            $data = array(
                'title'     => $params['title'],
                'content'   => $params['content'],
                'writer'    => $params['writer']
            );
            Zend_Session::namespaceUnset('formData');
            $formData = new Zend_Session_Namespace('formData');
            $formData->data = $data;
            
            try {
                $deleteFileResult = $board->deleteFile($params['filePk']);
                
                if (!$deleteFileResult) {
                    $result['message'] = '파일을 삭제 중 문제가 발생했습니다.';
                } else {
                    $result['result'] = true;
                    $result['message'] = '파일을 삭제했습니다.';
                }
            } catch (Zend_Db_Exception $e) {
                $result['message'] = '파일을 삭제 중 문제가 발생했습니다.';
            }
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '잘못된 요청입니다.';
        }
        
        $this->_helper->json->sendJson($result);
    }
    
    /**
     * 조회수 증가 Action
     */
    public function increaseViewAction() {
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
                $board->read($params['pk']);
                //게시글 조회수를 가져옴
                $select = $boardTable->select();
                $select->from($boardTable->getTableName(), array('views'))->where("pk = ?", $params['pk']);
                $row = $boardTable->getAdapter()->fetchRow($select);
                $views = $row['views'];
                
                $views++;
                $updateResult = $boardTable->update(array('views' => $views), "pk = {$params['pk']}");
                
                if ($updateResult == 1) {
                    $result['result'] = true;
                    $result['message'] = '조회수 증가';
                } else {
                    $result['message'] = '조회수를 증가시키는 중에 문제가 발생했습니다.';
                }
            } catch (Was_Board_Exception $e) {
                $result['message'] = '존재하지 않는 게시글입니다.';
            } catch (Zend_Db_Exception $e) {
                $result['message'] = 'DB 문제가 발생했습니다.';
            }
        } else {
            //예외
            $result['result'] = false;
            $result['message'] = '잘못된 요청입니다.';
        }
        
        $this->_helper->json->sendJson($result);
    }
}

