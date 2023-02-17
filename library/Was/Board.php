<?php
/**
 * @see Zend_Db_Expr
 */
require_once 'Zend/Db/Expr.php';
/**
 * @see Was_Board_Exception
 * @see Was_Board_Table_Board
 * @see Was_Board_Table_BoardReply
 * @see Was_Board_Table_File
 * @see Was_Board_Table_FileDetails
 */
require_once 'Was/Board/Exception.php';
require_once 'Was/Board/Table/Board.php';
require_once 'Was/Board/Table/BoardReply.php';
require_once 'Was/Board/Table/File.php';
require_once 'Was/Board/Table/FileDetails.php';
/**
 * @see Zend_Validate_Regex
 */
require_once 'Zend/Validate/Regex.php';

/**
 * @see Zend_Validate_GreaterThan
 */
require_once 'Zend/Validate/GreaterThan.php';
/**
 * 게시글 관리 클래스
 *
 * @package Was
 */
class Was_Board {
    /**
     * 게시판 관리 테이블
     * @var Was_Board_Table_Board
     */
    protected $_boardTable;
    /**
     * 게시글 댓글 관리 테이블
     * @var Was_Board_Table_BoardReply
     */
    protected $_boardReplyTable;
    /**
     * 파일 관리 테이블
     * @var Was_Board_Table_File
     */
    protected $_fileTable;
    /**
     * 파일 내용 관리 테이블
     * @var Was_Board_Table_FileDetails
     */
    protected $_fileDetailsTable;
    /**
     * 유효한 파일 타입
     * @var array
     */
    protected $_validFileType = array();
    
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter) {
        $this->setBoardTable($dbAdapter);
        $this->setBoardReplyTable($dbAdapter);
        $this->setFileTable($dbAdapter);
        $this->setFileDetailsTable($dbAdapter);
    }
    
    /**
     * 유효한 파일타입 설정한다.
     * 
     * @param array $types
     * @return Was_Board
     */
    public function setValidFileTypes(array $types) {
        $this->_validFileType = $types;
        return $this;
    }
    
    /**
     * 유효한 파일타입을 리턴한다.
     * 
     * @return array
     */
    public function getValidFileTypes() {
        // 설정된 유요한 파일타입이 없는 경우
        if (empty($this->_validFileType)) {
            $this->_validFileType = array(
                'jpeg', 'jpg', 'gif', 'png', 'pdf'
            );
        }
        
        return $this->_validFileType;
    }
    
    /**
     * 게시판 관리 테이블을 설정한다.
     * 
     * @param Zend_Db_Adapter_Abstract | Was_Board_Table_Board 
     * @return Was_Board
     */
    public function setBoardTable($object) {
        if (is_a($object, 'Was_Board_Table_Board')) {
            $this->_boardTable = $object;
        } else {
            $this->_boardTable = new Was_Board_Table_Board($object);
        }
        return $this;
    }
    
    /**
     * 게시판 관리 테이블을 리턴한다.
     * 
     * @throws Was_Board_Exception
     * @return Zend_Db_Table_Abstract
     */
    public function getBoardTable() {
        if (!$this->_boardTable) {
            throw new Was_Board_Exception('Board Table Is not set.');
        }
        
        return $this->_boardTable;
    }
    
    /**
     * 게시글 댓글 관리 테이블을 설정한다.
     *
     * @param Zend_Db_Adapter_Abstract | Was_Board_Table_BoardReply
     * @return Was_Board
     */
    public function setBoardReplyTable($object) {
        if (is_a($object, 'Was_Board_Table_BoardReply')) {
            $this->_boardReplyTable = $object;
        } else {
            $this->_boardReplyTable = new Was_Board_Table_BoardReply($object);
        }
        return $this;
    }
    
    /**
     * 게시글 댓글 관리 테이블을 리턴한다.
     *
     * @throws Was_Board_Exception
     * @return Zend_Db_Table_Abstract
     */
    public function getBoardReplyTable() {
        if (!$this->_boardReplyTable) {
            throw new Was_Board_Exception('Board Reply Table Is not set.');
        }
        
        return $this->_boardReplyTable;
    }
    
    /**
     * 파일 테이블을 설정한다.
     *
     * @param Zend_Db_Adapter_Abstract | Was_Board_Table_File 
     * @return Was_Board
     */
    public function setFileTable($object) {
        if (is_a($object, 'Was_Board_Table_File')) {
            $this->_fileTable = $object;
        } else {
            $this->_fileTable = new Was_Board_Table_File($object);
        }
        return $this;
    }
    
    /**
     * 파일 테이블을 리턴한다.
     *
     * @throws Was_Board_Exception
     * @return Zend_Db_Table_Abstract
     */
    public function getFileTable() {
        if (!$this->_fileTable) {
            throw new Was_Board_Exception('File Table Is not set.');
        }
        
        return $this->_fileTable;
    }
    
     /**
     * 파일 관리 테이블을 설정한다.
     *
     * @param Zend_Db_Adapter_Abstract | Was_Board_Table_FileDetails
     * @return Was_Board
     */
    public function setFileDetailsTable($object) {
        if (is_a($object, 'Was_Board_Table_FileDetails')) {
            $this->_fileDetailsTable = $object;
        } else {
            $this->_fileDetailsTable = new Was_Board_Table_FileDetails($object);
        }
        return $this;
    }
    
    /**
     * 파일 내용 테이블을 리턴한다.
     * 
     * @throws Was_Board_Exception
     * @return Was_Board_Table_Board
     */
    public function getFilDetailTable() {
        if (!$this->_fileDetailsTable) {
            throw new Was_Board_Exception('File Detail Table Is not set.');
        }
        
        return $this->_fileDetailsTable;
    }
    
    /**
     * 게시글을 작성한다
     * @param array 게시글 내용
     *        array(
     *            'title'    => '제목',
     *            'writer'   => '작성자',
     *            'content'  => '내용'
     *        )
     * @param number 작성자 고유키
     * @return Zend_Db_Table_Row | string 필수항목 미입력, 올바르지 않은 이름 형식시 에러문 반환
     */
    public function write(array $contents, $memberPk) {
        //필수항목 및 이름 형식 체크
        try {
            $this->_checkContents($contents);
        } catch (Was_Board_Exception $e) {
            return ($e->getMessage());
        }
        
        $boardTable = $this->getBoardTable();
        $boardPk = $boardTable->insert(array(
            'memberPk'      => $memberPk,
            'title'         => $contents['title'],
            'writer'        => $contents['writer'],
            'content'       => $contents['content'],
            'insertTime'    => new Zend_Db_Expr('NOW()'),
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ));
        
        if (!$boardPk) return false;
        
        // 작성된 데이터 row 리턴
        $boardTable = $this->getBoardTable();
        return $boardTable->find($boardPk)->current();
    }
    
    /**
     * 게시글을 수정한다
     * @param array array 게시글 내용
     *        array(
     *            'title'    => '제목',
     *            'writer'   => '작성자',
     *            'content'  => '내용'
     *        )
     * @param number 게시글기본키
     * @return number 수정 성공시 1 반환
     */
    public function edit(array $contents, $boardPk) {
        // 글 수정
        //필수항목 및 이름 형식 체크
        try {
            $this->_checkContents($contents);
        } catch (Was_Board_Exception $e) {
            return ($e->getMessage());
        }
        
        $boardTable = $this->getBoardTable();
        $result = $boardTable->update(array(
            'title'         => $contents['title'],
            'writer'        => $contents['writer'],
            'content'       => $contents['content'],
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ), "pk = {$boardPk}");
        
        if ($result != 1) return false;
        
        return 1;
    }
    
    /**
     * 파일을 추가한다
     * @param number 게시글 기본키
     * @param array 파일 정보
     *        array (
     *            'name'      => '파일 원래 이름'(필수),
     *            'type'      => '파일타입 형식'(필수, 기본 'jpeg', 'jpg', 'gif', 'png', 'pdf'),
     *            'size'      => '업로드 파일 크기를 바이트로 표현'(필수),
     *            'content'   => '파일의 내용'(필수)
     *        )
     * @return boolean
     */
    public function addFile($boardPk, $fileInfos) {
        $boardTable = $this->getBoardTable();
        $count = $boardTable->find($boardPk)->count();
        
        if ($count != 1) return false;
        
        if (!isset($fileInfos['type']) || !$fileInfos['type']) return false;
        
        $this->getValidFileTypes();
        if (array_search($fileInfos['type'], $this->_validFileType) === false) return false;
        
        //파일 업로드 시 3MB 초과하면 false 반환
        if (!isset($fileInfos['size']) || !$fileInfos['size'] ||
            ($fileInfos['size'] && $fileInfos['size'] > 3145728)) return false;
        
        if (!isset($fileInfos['name']) || !$fileInfos['name']) return false;
        
        
        $fileTable = $this->getFileTable();
        $filePk = $fileTable->insert(array(
            'boardPk'       => $boardPk, 
            'filename'      => $fileInfos['name'], 
            'fileType'      => $fileInfos['type'], 
            'fileSize'      => $fileInfos['size'], 
            'insertTime'    => new Zend_Db_Expr('NOW()')
        ));
        
        if (!$filePk) return false;
        
        // 파일기록 성공
        if ($filePk) {
            $content = base64_encode($fileInfos['content']);
            
            $detailsTable = $this->getFilDetailTable();
            $detailsPk = $detailsTable->insert(array(
                'filePk'    => $filePk, 
                'content'   => $content
            ));
            
            // 파일내용기록 실패 시 글 삭제
            if (!$detailsPk) {
                $fileTable->find($filePk)->current()->delete();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 게시글의 파일들을 불러온다
     * 
     * @param number 게시글기본키
     * @return array 파일 목록들 | 존재하지않는 게시글인 경우 false 반환 | 파일 목록 없으면 빈 배열 반환
     */
    public function getFiles($boardPk) {
        $boardTable = $this->getBoardTable();
        $count = $boardTable->find($boardPk)->count();
        
        if ($count != 1) return false;
        
        //파일 목록들 불러오기
        $fileTable = $this->getFileTable();
        $select = $fileTable->select();
        $select->where('boardPk = ?', $boardPk);
        $datas = $fileTable->getAdapter()->fetchAll($select);
        
        if (!$datas) return array();
        
        $boardFiles = array();
        //file_details 테이블에서 각 파일의 content를 불러옴
        foreach ($datas as $index => $content) {
            $deTailsTable = $this->getFilDetailTable();
            $select = $deTailsTable->select();
            $select->from($deTailsTable->getTableName(), 'content')
                   ->where('filePk = ?', $content['pk']);
            
            $fileContent = $deTailsTable->fetchRow($select);
            
            if (!$fileContent) return false;
            
            $datas[$index]['content'] = base64_decode($fileContent->content);
            array_push($boardFiles, $datas[$index]);
        }
        return $boardFiles;
    }
    
    /**
     * 해당 파일을 삭제한다
     * 
     * @param number 게시글기본키
     * @param number 파일기본키
     * @return number
     */
    public function deleteFile($boardPk, $filePk) {
        $fileTable = $this->getFileTable();
        $fileTable->delete(array("boardPk = {$boardPk}", "filePk = {$filePk}"));
        $select = $fileTable->select()->where('pk = ?', $filePk)->where('boardPk = ?', $boardPk);
        
        $datas = $fileTable->getAdapter()->fetchAll($select);
        if (!empty($datas)) return false;
        
        $detailsTable = $this->getFilDetailTable();
        $detailsTable->find($filePk)->current()->delete();
        $select2 = $detailsTable->select()->where('filePk = ?', $filePk);
        
        $datas2 = $detailsTable->getAdapter()->fetchAll($select2);
        if (!empty($datas2)) return false;
        
        return 1;
    }
    
    /**
     * 게시글을 조회한다
     * @param number 게시글기본키
     * @return Zend_Db_Table_Row | null
     */
    public function read($pk) {
        $boardTable = $this->getBoardTable();
        $count = $boardTable->find($pk)->count();
        
        if ($count != 1) return NULL;
        
        return $boardTable->find($pk)->current(); // 찾은 row /null
    }
    
    /**
     * 게시글들을 불러온다
     * @param array 조회조건(모든 글을 찾는 경우에는 빈 배열)
     *        array(
     *            'categoty'        => '검색 카테고리',
     *            'search'          => '검색 내용',
     *            'fieldName'       => '정렬할 필드이름',
     *            'order'           => '정렬 방식',
     *            'start'           => '출력 시작 번호'
     *        )
     * @return array
     */
    public function reads($where) {
        $conditionArr = array(
            'writer',
            'title',
            'insertTime'
        );
        $fieldNameArr = array(
            'pk',
            'insertTime',
            'views'
        );
        
        $boardTable = $this->getBoardTable();
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'));
        
        //검색 조건이 있을 경우
        if (array_key_exists('category', $where) && array_key_exists('search', $where)) {
            foreach ($conditionArr as $condition) {
                if ($where['category'] == $condition) {
                    $select->where("{$where['category']} LIKE ?", $where['search']);
                    break;
                }
            }
        }
        
        //정렬 조건이 있을 경우
        if (array_key_exists('fieldName', $where) && array_key_exists('order', $where)) {
            foreach ($fieldNameArr as $fieldCon) {
                if ($where['fieldName'] == $fieldCon) {
                    $select->order("{$where['fieldName']} {$where['order']}");
                    break;
                }
            }
        } else {
            $select->order("pk DESC");
        }
        
        //페이징 조건이 있을 경우
        if (array_key_exists('start', $where)) {
            $select->limit(10, $where['start']);
        } else {
            $select->limit(10, 0);
        }
        $datas = $boardTable->getAdapter()->fetchAll($select);
        
        if (!$datas) return array();
        
        return $datas; // array
    }
    
    /**
     * 게시글을 삭제한다
     * 
     * @param number 게시글기본키
     * @return number | 존재하지 않는 게시글 삭제 시도시 false 반환
     */
    public function delete($pk) {
        $boardTable = $this->getBoardTable();
        $count = $boardTable->find($pk)->count();
        
        if ($count != 1) return false;
        
        $num = $boardTable->find($pk)->current()->delete();
        if ($num != 1) return false;
        return 1;
    }
    
    /**
     * 댓글들을 불러온다
     * @param number 게시글기본키
     * @return array 댓글목록 | 존재하지 않는 게시글 확인시 false 반환
     */
    public function getReply($boardPk) {
        $boardTable = $this->getBoardTable();
        $count = $boardTable->find($boardPk)->count();
        
        if ($count != 1) return false;
        
        $boardReplyTable = $this->getBoardReplyTable();
        $select = $boardReplyTable->select();
        $select->where('boardPk = ?', $boardPk);
        
        $datas = $boardReplyTable->getAdapter()->fetchAll($select);
        
        return $datas;
    }
    
    /**
     * 댓글을 작성합니다
     * @param number 게시글기본키
     * @param number 작성자기본키
     * @param string 작성내용
     * @return boolean false 잘못된 게시글 조회, 필수 항목 미입력, 100자 초과 댓글내용 |number
     */
    public function writeReply($boardPk, $writer, $content) {
        $boardTable = $this->getBoardTable();
        $count = $boardTable->find($boardPk)->count();
        
        if ($count != 1) return false;
        
        if (!$writer || !$content) return false;
        
        $contentValidator =new Zend_Validate_GreaterThan(101);
        if (!$contentValidator->isValid(strlen($content))) return false;
        
        $boardReplyTable = $this->getBoardReplyTable();
        $replyPk = $boardReplyTable->insert(array(
            'boardPk'       => $boardPk,
            'memberPk'      => $writer,
            'content'       => $content,
            'insertTime'    => new Zend_Db_Expr('NOW()')
        ));
        
        if (!$replyPk) return false;
        
        return $boardReplyTable->find($replyPk)->current(); // 작성된 데이터 row
    }
    
    /**
     * 댓글을 삭제한다
     * 
     * @param number 게시글기본키
     * @param number 댓글기본키
     * @return number
     */
    public function deleteReply($boardPk, $replyPk) {
        $boardTable = $this->getBoardTable();
        $count = $boardTable->find($boardPk)->count();
        
        if ($count != 1) return false;
        
        $boardReplyTable = $this->getBoardReplyTable();
        
        $boardReplyTable->find($replyPk)->current()->delete();
        $select = $boardReplyTable->select()->where('pk = ?', $replyPk)->where('boardPk = ?', $boardPk);
        
        $datas = $boardReplyTable->getAdapter()->fetchAll($select);
        if (!empty($datas)) return false;
        
        return 1;
    }
    
    /**
     * 게시글을 체크한다
     *
     * @throws Exception 필수 항목을 입력하지 않았을 경우
     *                   이름 작성 조건을 지키지않았을 경우
     * @param array 게시글 내용(작성, 수정)
     *        array(
     *            'title'   => '제목',
     *            'writer'  => '작성자',
     *            'content' => '내용'
     *        )
     * @return boolean 모든 검증 통과하면 true 반환
     */
    protected function _checkContents(array $contents) {
        //필수항목 입력 체크
        if (!isset($contents['title']) || !$contents['title'] || 
            !isset($contents['writer']) || !$contents['writer'] ||
            !isset($contents['content']) || !$contents['content']) {
            throw new Was_Board_Exception("Please enter a required item.");
        }
        //이름 작성 조건 체크
        $nameValidator = new Zend_Validate_Regex("/[가-힣A-Za-z0-9]+$/");
        if (!$nameValidator->isValid($contents['writer'])) {
            throw new Was_Board_Exception("You can enter only Korean, English, and numbers for names.");
        }
        
        return true;
    }
}

