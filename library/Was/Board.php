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
require_once 'Zend/Validate/LessThan.php';
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
    /**
     * 유효하지 않은 파일 확장자
     * @var number
     */
    const INVALID_FILE_TYPE = 2;
    /**
     * 파일의 크기가 너무 크거나 존재하지않음
     * @var number
     */
    const INVALID_FILE_SIZE = 3;
    
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
    public function getFileDetailsTable() {
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
            throw new Was_Board_Exception($e->getMessage());
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
        $select = $boardTable->select();
        $select->where('pk = ?', $boardPk);
        return $boardTable->getAdapter()->fetchRow($select);
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
            throw new Was_Board_Exception($e->getMessage());
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
     * @exception Was_Board_Exception 게시글이 존재하지않을 시
     */
    public function addFile($boardPk, $fileInfos) {
        //존재하는 게시글 번호인지 확인
        try {
            $this->_checkExistBoard($boardPk);
        } catch (Was_Board_Exception $e) {
            throw new Was_Board_Exception($e->getMessage());
        }
        
        if (!isset($fileInfos['type']) || !$fileInfos['type']) return self::INVALID_FILE_TYPE;
        
        $this->getValidFileTypes();
        if (array_search($fileInfos['type'], $this->_validFileType) === false) return self::INVALID_FILE_TYPE;
        
        //파일 업로드 시 3MB 초과하면 false 반환
        if (!isset($fileInfos['size']) || !$fileInfos['size'] ||
            ($fileInfos['size'] && $fileInfos['size'] > 3145728)) return self::INVALID_FILE_SIZE;
        
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
            
            $detailsTable = $this->getFileDetailsTable();
            $detailsPk = $detailsTable->insert(array(
                'filePk'    => $filePk, 
                'content'   => $content
            ));
            
            // 파일내용기록 실패 시 파일 테이블에서 삭제
            if (!$detailsPk) {
                $fileTable->getAdapter()->delete($fileTable->getTableName(), "pk = {$filePk}");
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 게시글의 파일들을 불러온다
     * 
     * @param number 게시글기본키
     * @return array 파일 목록들 | 파일 목록 없으면 빈 배열 반환
     * @exception Was_Board_Exception 게시글이 존재하지않을 시
     */
    public function getFiles($boardPk) {
        //파일 목록들 불러오기
        $fileTable = $this->getFileTable();
        $select = $fileTable->select();
        $select->where('boardPk = ?', $boardPk);
        $datas = $fileTable->getAdapter()->fetchAll($select);
        //빈 배열일 시 추가 작업 필요없으므로 리턴
        if (!$datas) return array();
        
        $boardFiles = array();
        //file_details 테이블에서 각 파일의 content를 불러옴
        foreach ($datas as $index => $content) {
            $deTailsTable = $this->getFileDetailsTable();
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
     * @param number 파일기본키
     * @param number 게시글기본키 [optional]
     * @return number
     */
    public function deleteFile($filePk = null, $boardPk = null) {
        if ($boardPk) {
            //존재하는 게시글 번호인지 확인
            try {
                $this->_checkExistBoard($boardPk);
            } catch (Was_Board_Exception $e) {
                throw new Was_Board_Exception($e->getMessage());
            }
        }
        
        $fileTable = $this->getFileTable();
        //파일 기본키가 존재하고 게시글 기본키가 없는 경우
        if ((isset($filePk) && $filePk) && (!isset($boardPk) || !$boardPk)) {
            //파일 하나 삭제
            $fileTable->getAdapter()->delete($fileTable->getTableName(), "pk = {$filePk}");
            //해당하는 파일 내용도 삭제
            $detailsTable = $this->getFileDetailsTable();
            $detailsTable->getAdapter()->delete($detailsTable->getTableName(), "filePk = {$filePk}");
        } else if(isset($boardPk) && $boardPk) {
            //해당 게시글의 파일을 전부 지우기 위해 filePk를 전부 가져옴
            $select = $fileTable->select();
            $select->from($fileTable->getTableName(), array("pk"))->where('boardPk = ?', $boardPk);
            $indexs = $fileTable->getAdapter()->fetchAll($select);
            //filePk 들을 가져온 후 delete
            $fileTable->getAdapter()->delete($fileTable->getTableName(), "boardPk = {$boardPk}");
            
            $detailsTable = $this->getFileDetailsTable();
            foreach ($indexs as $index => $pk) {
                $detailsTable->getAdapter()->delete($detailsTable->getTableName(), "filePk = {$pk['pk']}");
            }
        }
        
        return 1;
    }
    
    /**
     * 게시글을 조회한다
     * @param number 게시글기본키
     * @return Zend_Db_Table_Row | null
     * @exception Was_Board_Exception 게시글이 존재하지않을 시
     */
    public function read($pk) {
        //존재하는 게시글 번호인지 확인
        try {
            $this->_checkExistBoard($pk);
        } catch (Was_Board_Exception $e) {
            throw new Was_Board_Exception($e->getMessage());
        }
        
        $boardTable = $this->getBoardTable();
        //게시글 번호에 해당하는 레코드 찾기
        $select2 = $boardTable->select();
        $select2->where('pk = ?', $pk);
        //찾은 Row 반환
        return $boardTable->getAdapter()->fetchRow($select2);
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
                    $select->where("{$where['category']} LIKE ?", "%".$where['search']."%");
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
        
//         //페이징 조건이 있을 경우
//         if (array_key_exists('start', $where)) {
//             $select->limit(10, $where['start']);
//         } else {
//             $select->limit(10, 0);
//         }
        $datas = $boardTable->getAdapter()->fetchAll($select);
        
        if (!$datas) return array();
        // array
        return $datas;
    }
    
    /**
     * 게시글을 삭제한다
     * 
     * @param number 게시글기본키
     * @return Number
     * @exception Was_Board_Exception 게시글이 존재하지않을 시
     */
    public function delete($pk) {
        //존재하는 게시글 번호인지 확인
        try {
            $this->_checkExistBoard($pk);
        } catch (Was_Board_Exception $e) {
            throw new Was_Board_Exception($e->getMessage());
        }
        
        //파일 일괄 삭제
        $this->deleteFile(null, $pk);
        
        //댓글 일괄 삭제
        $this->deleteReply(null, $pk);
        
        $boardTable = $this->getBoardTable();
        //파일, 댓글 삭제 후 게시글 삭제
        $num = $boardTable->getAdapter()->delete($boardTable->getTableName(), "pk = {$pk}");
        if ($num != 1) return false;
        
        return 1;
    }
    
    /**
     * 댓글들을 불러온다
     * @param number 게시글기본키
     * @return array 댓글목록
     * @exception Was_Board_Exception 게시글이 존재하지않을 시
     */
    public function getReply($boardPk) {
        //존재하는 게시글 번호인지 확인
        try {
            $this->_checkExistBoard($boardPk);
        } catch (Was_Board_Exception $e) {
            throw new Was_Board_Exception($e->getMessage());
        }
        
        //해당 게시글에 있는 댓글 전체 가져오기
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
     * @return boolean false 필수 항목 미입력, 100자 초과 댓글내용 | number
     * @exception Was_Board_Exception 게시글이 존재하지않을 시
     */
    public function writeReply($boardPk, $writerPk, $content) {
        //존재하는 게시글 번호인지 확인
        try {
            $this->_checkExistBoard($boardPk);
        } catch (Was_Board_Exception $e) {
            throw new Was_Board_Exception($e->getMessage());
        }
        
        if (!$writerPk || !$content) return false;
        
        //댓글 글자길이 100 자 초과시 false 반환
        $contentValidator = new Zend_Validate_LessThan(101);
        if (!$contentValidator->isValid(strlen($content))) return false;
        
        $boardReplyTable = $this->getBoardReplyTable();
        $replyPk = $boardReplyTable->insert(array(
            'boardPk'       => $boardPk,
            'memberPk'      => $writerPk,
            'content'       => $content,
            'insertTime'    => new Zend_Db_Expr('NOW()')
        ));
        
        if (!$replyPk) return false;
        
        $select = $boardReplyTable->select();
        $select->where('pk = ?', $replyPk);
        
        return $boardReplyTable->getAdapter()->fetchRow($select); // 작성된 데이터 row
    }
    
    /**
     * 댓글을 삭제한다
     * 
     * @param number 댓글기본키 [optional]
     * @param number 게시글 기본키 [optional]
     * @return number
     * @exception Was_Board_Exception 게시글이 존재하지않을 시
     */
    public function deleteReply($replyPk = null, $boardPk = null) {
        if ($boardPk) { 
            //존재하는 게시글 번호인지 확인
            try {
                $this->_checkExistBoard($boardPk);
            } catch (Was_Board_Exception $e) {
                throw new Was_Board_Exception($e->getMessage());
            }
        }
        
        $boardReplyTable = $this->getBoardReplyTable();
        //댓글 기본키는 존재하나, 게시글기본키가 존재하지 않는 경우
        if ((isset($replyPk) && $replyPk) && (!isset($boardPk) || !$boardPk)) {
            //댓글 하나 삭제
            $boardReplyTable->getAdapter()->delete($boardReplyTable->getTableName(), "pk = {$replyPk}");
        } else if (isset($boardPk) && $boardPk) {
            //해당 게시글의 댓글 일괄 삭제
            $boardReplyTable->getAdapter()->delete($boardReplyTable->getTableName(), "boardPk = {$boardPk}");
        }
        
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
    
    /**
     * 존재하는 게시글인지 확인하는 메소드
     * @param number 
     * @throws Was_Board_Exception 존재하지 않는 게시글인 경우
     */
    protected function _checkExistBoard($boardPk) {
        //존재하는 게시글인지 확인
        $boardTable = $this->getBoardTable();
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array(new Zend_Db_Expr('COUNT(pk) AS count')))
        ->where('pk = ?', $boardPk);
        $fetchRow = $boardTable->getAdapter()->fetchRow($select);
        
        if ($fetchRow['count'] != 1) throw new Was_Board_Exception('Not Exist Board.');
    }
}

