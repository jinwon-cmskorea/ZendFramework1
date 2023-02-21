<?php
/**
 * Was Member test file
 */
/**
 * @see Bootstrap
 */
require_once __DIR__.'/bootstrap.php';

/**
 * @see Was_Board_Exception
 */
require_once 'Was/Board/Exception.php';
/**
 * require test class
 */
require_once 'Was/Board.php';

class Was_BoardTestClass extends Was_Board {
    public function checkContents(array $contents) {
        try {
            $this->_checkContents($contents);
            return true;
        } catch (Was_Board_Exception $e) {
            return ($e->getMessage());
        }
    }
    
    public function checkExistBoard($boardPk) {
        try {
            $this->_checkExistBoard($boardPk);
            return true;
        } catch (Was_Board_Exception $e) {
            return ($e->getMessage());
        }
    }
}

/**
 *Was_BoardTest
 */
class Was_BoardTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Board
     */
    private $board;
    
    /**
     *
     * @var array 게시글 작성값
     */
    private $fileTestArray = array(
        'name'      => 'test.png',
        'type'      => 'png',
        'size'      => 132
    );
    
    /**
     *
     * @var array db 정보
     */
    private $dbInfo = array(
        'host'      => '127.0.0.1',
        'username'  => 'root',
        'password'  => 'cmskorea',
        'dbname'    => 'cmskorea_board_test'
    );

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        
        $this->board = new Was_BoardTestClass(Zend_Db::factory('Mysqli', $this->dbInfo));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->board->getBoardTable()->delete(1);
        $this->board->getBoardTable()->getAdapter()->query("ALTER TABLE `board` auto_increment = 1");
        
        $this->board->getBoardReplyTable()->delete(1);
        $this->board->getBoardReplyTable()->getAdapter()->query("ALTER TABLE `board_reply` auto_increment = 1");
        
        $this->board->getFileTable()->delete(1);
        $this->board->getFileTable()->getAdapter()->query("ALTER TABLE `file` auto_increment = 1");
        
        $this->board->getFileDetailsTable()->delete(1);
        $this->board->getFileDetailsTable()->getAdapter()->query("ALTER TABLE `file_details` auto_increment = 1");
        $this->board = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
        // TODO Auto-generated constructor
    }

    
    /**
     * Tests Was_Board->setValidFileTypes()
     * Tests Was_Board->getValidFileTypes()
     */
    public function testSetGetValidType() {
        $valid = array('jpg', 'png');
        
        $this->assertInstanceOf('Was_Board', $this->board->setValidFileTypes($valid));
        $this->assertEquals($valid, $this->board->getValidFileTypes());
    }
    
    /**
     * Tests Was_Board->setBoardTable()
     * Tests Was_Board->getBoardTable()
     */
    public function testSetGetBoardTable() {
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setBoardTable(Zend_Db::factory('Mysqli', $this->dbInfo)));
        $testBoardTable = new Was_Board_Table_Board(Zend_Db::factory('Mysqli', $this->dbInfo));
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setBoardTable($testBoardTable));
        $this->assertInstanceOf('Was_Board_Table_Board', $this->board->getBoardTable());
    }
    
    /**
     * Tests Was_Board->setBoardReplyTable()
     * Tests Was_Board->getBoardReplyTable()
     */
    public function testSetGetBoardReplyTable() {
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setBoardReplyTable(Zend_Db::factory('Mysqli', $this->dbInfo)));
        $testBoardReplyTable = new Was_Board_Table_BoardReply(Zend_Db::factory('Mysqli', $this->dbInfo));
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setBoardReplyTable($testBoardReplyTable));
        $this->assertInstanceOf('Was_Board_Table_BoardReply', $this->board->getBoardReplyTable());
    }
    
    /**
     * Tests Was_Board->setFileTable()
     * Tests Was_Board->getFileTable()
     */
    public function testSetGetFileTable() {
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setFileTable(Zend_Db::factory('Mysqli', $this->dbInfo)));
        $testFileTable = new Was_Board_Table_File(Zend_Db::factory('Mysqli', $this->dbInfo));
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setFileTable($testFileTable));
        $this->assertInstanceOf('Was_Board_Table_File', $this->board->getFileTable());
    }
    
    /**
     * Tests Was_Board->setFileDetailTable()
     * Tests Was_Board->getFileDetailTable()
     */
    public function testSetGetFileDetailTable() {
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setFileDetailsTable(Zend_Db::factory('Mysqli', $this->dbInfo)));
        $testFileDetailsTable = new Was_Board_Table_FileDetails(Zend_Db::factory('Mysqli', $this->dbInfo));
        $this->assertInstanceOf('Was_BoardTestClass', $this->board->setFileDetailsTable($testFileDetailsTable));
        $this->assertInstanceOf('Was_Board_Table_FileDetails', $this->board->getFileDetailsTable());
    }
    
    /**
     * Tests Was_Board->write()
     */
    public function testWrite() {
        $contents = array(
            'title'     => '제목',
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $actual = $this->board->write($contents, 1);
        $this->assertTrue(is_array($actual));
        $this->assertEquals(1, $actual['memberPk']);
        $this->assertEquals('제목', $actual['title']);
        $this->assertEquals('작성자', $actual['writer']);
        $this->assertEquals('내용', $actual['content']);
        $this->assertEquals(0, $actual['view']);
    }

    /**
     * Tests Was_Board->edit()
     */
    public function testEdit() {
        //게시글 수정을 위해 먼저 삽입
        $boardTable = $this->board->getBoardTable();
        $boardPk = $boardTable->insert(array(
            'memberPk'      => 3,
            'title'         => '테스트',
            'writer'        => '테스터',
            'content'       => '테스트입니다',
            'insertTime'    => new Zend_Db_Expr('NOW()'),
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ));
        
        $editContent = array(
            'title'         => '수정테스트',
            'writer'        => '수정테스터',
            'content'       => '수정테스트입니다',
        );
        
        $this->assertEquals(1, $this->board->edit($editContent, $boardPk));
        //수정값이 잘 들어갔는지 확인
        $row = $boardTable->find($boardPk)->current();
        $this->assertEquals('수정테스트', $row->title);
        $this->assertEquals('수정테스터', $row->writer);
        $this->assertEquals('수정테스트입니다', $row->content);
    }

    /**
     * Tests Was_Board->addFile()
     * Tests Was_Board->getFile()
     */
    public function testAddGetFiles() {
        $infos = $this->_createFileAndBoard();
        
        //정상적으로 파일 삽입
        $fileInfos = $this->fileTestArray;
        $fileInfos['content'] = file_get_contents($infos['testFilePath']);
        $this->assertTrue($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //정상적으로 파일 삽입2
        $fileInfos2 = $this->fileTestArray;
        $fileInfos2['name'] = 'test2.png';
        $fileInfos2['content'] = file_get_contents($infos['testFilePath']);
        $this->assertTrue($this->board->addFile($infos['boardPk'], $fileInfos2));
        
        //정상적으로 파일 가져오기 - 2개 가져옴
        $getFiles = $this->board->getFiles($infos['boardPk']);
        $this->assertEquals('test.png', $getFiles[0]['filename']);
        $this->assertEquals('test2.png', $getFiles[1]['filename']);
        
        //잘못된 boardPk 삽입
        try {
            $this->board->addFile(3, $fileInfos);
            $this->assertFalse(true);
        } catch (Was_Board_Exception $e) {
            $this->assertTrue(true);
        }
        
        
        //fileType이 빈 값일 경우 false
        $fileInfos = array(
            'type' => ''
        );
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //fileType 필드가 존재하지않는 경우
        $fileInfos = $this->fileTestArray;
        unset($fileInfos['type']);
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //허용되지않은 확장자
        $fileInfos = $this->fileTestArray;
        $fileInfos['type'] = 'hwp';
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //fileSize 필드가 존재하지 않는 경우
        $fileInfos = $this->fileTestArray;
        unset($fileInfos['size']);
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //fileSize이 빈값인 경우
        $fileInfos = $this->fileTestArray;
        $fileInfos['size'] = '';
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //허용된 fileSize를 초과하는 경우
        $fileInfos = $this->fileTestArray;
        $fileInfos['size'] = 3200000;
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //name 필드가 존재하지 않는 경우
        $fileInfos = $this->fileTestArray;
        unset($fileInfos['name']);
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //name이 빈값인 경우
        $fileInfos = $this->fileTestArray;
        $fileInfos['name'] = '';
        $this->assertFalse($this->board->addFile($infos['boardPk'], $fileInfos));
        
        //넣었던 테스트 게시글 삭제
        $boardTable = $this->board->getBoardTable();
        $boardTable->find($infos['boardPk'])->current()->delete();
        //테스트 파일 삭제
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Board' . DIRECTORY_SEPARATOR . 'tempFile' . DIRECTORY_SEPARATOR . 'test.png');
    }

    /**
     * Tests Was_Board->deleteFile()
     */
    public function testDeleteFile() {
        $infos = $this->_createFileAndBoard();
        //파일 삽입
        $fileInfos = $this->fileTestArray;
        $fileInfos['content'] = file_get_contents($infos['testFilePath']);
        $this->assertTrue($this->board->addFile($infos['boardPk'], $fileInfos));
        
        $res = $this->board->getFiles($infos['boardPk']);
        $this->assertEquals(1, $this->board->deleteFile($res[0]['pk']));
        
        //파일 2개 삽입
        $fileInfos = $this->fileTestArray;
        $fileInfos['content'] = file_get_contents($infos['testFilePath']);
        $this->assertTrue($this->board->addFile($infos['boardPk'], $fileInfos));
        
        $fileInfos2 = $this->fileTestArray;
        $fileInfos2['name'] = 'test2.png';
        $fileInfos2['content'] = file_get_contents($infos['testFilePath']);
        $this->assertTrue($this->board->addFile($infos['boardPk'], $fileInfos2));
        
        $this->assertEquals(2, count($this->board->getFiles($infos['boardPk'])));
        $this->assertEquals(1, $this->board->deleteFile(null, $infos['boardPk']));
        $this->assertEquals(0, count($this->board->getFiles($infos['boardPk'])));
        
        //테스트 파일 삭제
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Board' . DIRECTORY_SEPARATOR . 'tempFile' . DIRECTORY_SEPARATOR . 'test.png');
    }

    /**
     * Tests Was_Board->read()
     */
    public function testRead() {
        $contents = array(
            'title'     => '제목',
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $read = $this->board->write($contents, 1);
        $actual = $this->board->read($read['pk']);
        $this->assertTrue(is_array($actual));
        $this->assertEquals($read['pk'], $actual['pk']);
        $this->assertEquals(1, $actual['memberPk']);
        $this->assertEquals('제목', $actual['title']);
        $this->assertEquals('작성자', $actual['writer']);
        $this->assertEquals('내용', $actual['content']);
        $this->assertEquals(0, $actual['views']);
    }
    
    /**
     * Tests Was_Board->reads()
     */
    public function testReads() {
        for ($i = 1; $i <= 5; $i++) {
            $testArr = array(
                'title' => "테스트제목 $i",
                'writer' => "테스터 $i",
                'content' => "테스트내용 $i"
            );
            $this->board->write($testArr, $i);
        }
        $boardTable = $this->board->getBoardTable();
        //1. 아무 조건없이 게시글 검색한 결과
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
               ->order('pk DESC')
               ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        
        $actual = $this->board->reads(array());
        $this->assertEquals($expext, $actual);
        
        //2. 검색 카테고리 : writer, 검색어 : 테스터 3
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
                ->where('writer LIKE ?', '%테스터 3%')
                ->order('pk DESC')
                ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('category' => 'writer', 'search' => '테스터 3'));
        $this->assertEquals($expext, $actual);
        
        //3. 검색 카테고리 : title, 검색어 : 테스트제목 3
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->where('title LIKE ?', '%테스트제목 3%')
        ->order('pk DESC')
        ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('category' => 'title', 'search' => '테스트제목 3'));
        $this->assertEquals($expext, $actual);
        
        //4. 검색 카테고리 : insertTime, 검색어 : 2023-02-20
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->where('insertTime LIKE ?', '%2023-02-20%')
        ->order('pk DESC')
        ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('category' => 'insertTime', 'search' => '2023-02-20'));
        $this->assertEquals($expext, $actual);
        
        //5. 검색 카테고리 : writer, 검색어 : (아무것도 없을 때 전체 검색)
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->where('writer LIKE ?', '%%')
        ->order('pk DESC')
        ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('category' => 'writer', 'search' => ''));
        $this->assertEquals($expext, $actual);
        
        //6. 글 번호 기준 오름차순 정렬
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->order('pk ASC')
        ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('fieldName' => 'pk', 'order' => 'ASC'));
        $this->assertEquals($expext, $actual);
        
        //7. 작성일자 기준 내림차순 정렬
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->order('insertTime DESC')
        ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('fieldName' => 'insertTime', 'order' => 'DESC'));
        $this->assertEquals($expext, $actual);
        
        //8. 조회수 기준 오름차순 정렬
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->order('views ASC')
        ->limit(10, 0);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('fieldName' => 'views', 'order' => 'ASC'));
        $this->assertEquals($expext, $actual);
        
        //9. 3번째 글부터 10개 출력
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->order('pk DESC')
        ->limit(10, 3);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $actual = $this->board->reads(array('start' => 3));
        $this->assertEquals($expext, $actual);
        
        //10. 검색 카테고리 : writer, 검색 내용 : 테스터 1, 글 번호 기준 오름차순 정렬, 2번째 글 부터 10개 출력
        $select = $boardTable->select();
        $select->from($boardTable->getTableName(), array('pk', 'memberPk', 'title', 'writer', 'insertTime', 'views'))
        ->where('writer LIKE ?', '%테스터 1%')
        ->order('pk ASC')
        ->limit(10, 1);
        $expext = $boardTable->getAdapter()->fetchAll($select);
        $conditionArr= array(
            'category'      => 'writer',
            'search'        => '테스터 1',
            'fieldName'     => 'pk',
            'order'         => 'ASC',
            'start'         => '1'
        );
        $actual = $this->board->reads($conditionArr);
        $this->assertEquals($expext, $actual);
    }

    /**
     * Tests Was_Board->delete()
     */
    public function testDelete() {
        $contents = array(
            'title'     => '제목',
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $row = $this->board->write($contents, 1);

        $actual = $this->board->delete($row['pk']);
        $this->assertEquals(1, $actual);
        //삭제후 게시글이 남아있는지 확인
        try {
            $this->board->read($row['pk']);
            $this->assertFalse(true);
        } catch (Was_Board_Exception $e) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * Tests Was_Board->delete() WithFileAndReply
     */
    public function testDeleteWithFileAndReply() {
        //테스트 게시글 및 file 생성
        $infos = $this->_createFileAndBoard();
        
        //정상적으로 파일 삽입
        $fileInfos = $this->fileTestArray;
        $fileInfos['content'] = file_get_contents($infos['testFilePath']);
        $this->board->addFile($infos['boardPk'], $fileInfos);
        
        //정상적으로 파일 삽입2
        $fileInfos2 = $this->fileTestArray;
        $fileInfos2['name'] = 'test2.png';
        $fileInfos2['content'] = file_get_contents($infos['testFilePath']);
        $this->board->addFile($infos['boardPk'], $fileInfos2);
        
        //댓글작성1
        $this->board->writeReply($infos['boardPk'], 10, '댓글내용1');
        //댓글작성2
        $this->board->writeReply($infos['boardPk'], 20, '댓글내용2');
        //게시글 삭제
        $actual = $this->board->delete($infos['boardPk']);
        $this->assertEquals(1, $actual);
        //게시글이 남아있는지 확인
        try {
            $this->board->read(1);
            $this->assertFalse(true);
        } catch (Was_Board_Exception $e) {
            $this->assertTrue(true);
        }
        //파일이 완전히 삭제됐는지 확인
        $fileTable = $this->board->getFileTable();
        $select = $fileTable->select();
        $select->where('boardPk = ?', 1);
        $result = $fileTable->getAdapter()->fetchAll($select);
        $this->assertEquals(array(), $result);
        
        //파일 내용이 완전히 삭제됐는지 확인
        $detailTable = $this->board->getFileDetailsTable();
        $select = $detailTable->select();
        $result = $detailTable->getAdapter()->fetchAll($select);
        $this->assertEquals(array(), $result);
        
        //댓글들이 완전히 삭제됐는지 확인
        $replyTable = $this->board->getBoardReplyTable();
        $select = $replyTable->select();
        $select->where('boardPk = ?', 1);
        $result = $replyTable->getAdapter()->fetchAll($select);
        $this->assertEquals(array(), $result);
        
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Board' . DIRECTORY_SEPARATOR . 'tempFile' . DIRECTORY_SEPARATOR . 'test.png');
    }

    /**
     * Tests Was_Board->getReply()
     */
    public function testGetReply() {
        $contents = array(
            'title'     => '댓글용 게시글 제목',
            'writer'    => '댓글용 게시글 작성자',
            'content'   => '댓글용 게시글 내용'
        );
        $boardRow = $this->board->write($contents, 1);
        
        $this->board->writeReply($boardRow['pk'], 10, '댓글입니다1');
        $this->board->writeReply($boardRow['pk'], 20, '댓글입니다2');
        
        $actual = $this->board->getReply($boardRow['pk']);
        $this->assertEquals(2, count($actual));
        
        $this->assertEquals($boardRow['pk'], $actual[0]['boardPk']);
        $this->assertEquals(10, $actual[0]['memberPk']);
        $this->assertEquals('댓글입니다1', $actual[0]['content']);
        
        $this->assertEquals($boardRow['pk'], $actual[1]['boardPk']);
        $this->assertEquals(20, $actual[1]['memberPk']);
        $this->assertEquals('댓글입니다2', $actual[1]['content']);
    }
    
    /**
     * Tests Was_Board->writeReply()
     */
    public function testWriteReply() {
        $contents = array(
            'title'     => '댓글용 게시글 제목',
            'writer'    => '댓글용 게시글 작성자',
            'content'   => '댓글용 게시글 내용'
        );
        $row = $this->board->write($contents, 1);
        
        $actual = $this->board->writeReply($row['pk'], 10, '현재 작성자 기본키 10');
        $this->assertEquals($row['pk'], $actual['boardPk']);
        $this->assertEquals(10, $actual['memberPk']);
        $this->assertEquals('현재 작성자 기본키 10', $actual['content']);
    }
    
    /**
     * Tests Was_Board->deleteReply()
     */
    public function testDeleteReply() {
        $contents = array(
            'title'     => '댓글용 게시글 제목',
            'writer'    => '댓글용 게시글 작성자',
            'content'   => '댓글용 게시글 내용'
        );
        $boardRow = $this->board->write($contents, 1);
        //1개의 댓글 작성 후 삭제
        $replyRow = $this->board->writeReply($boardRow['pk'], 10, '현재 작성자 기본키 10');
        $actual = $this->board->deleteReply($replyRow['pk']);
        $this->assertEquals(1, $actual);
        $this->assertEquals(array(), $this->board->getReply($boardRow['pk']));
        
        //2개의 댓글 작성 후 전체 삭제
        $this->board->writeReply($boardRow['pk'], 20, '현재 작성자 기본키 20');
        $this->board->writeReply($boardRow['pk'], 30, '현재 작성자 기본키 30');
        
        $this->assertEquals(2, count($this->board->getReply($boardRow['pk'])));
        $this->board->deleteReply(null, $boardRow['pk']);
        $this->assertEquals(0, count($this->board->getReply($boardRow['pk'])));
    }
    
    /**
     * Tests Was_Board->_checkContents()
     */
    public function testCheckContents() {
        //정상적인 게시글 입력
        $wrongContents = array(
            'title'     => '제목',
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $this->assertTrue($this->board->checkContents($wrongContents));
        
        $requireMessage = 'Please enter a required item.';
        
        //제목값 없음
        $wrongContents = array(
            'title'     => '',
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $this->assertEquals($requireMessage, $this->board->checkContents($wrongContents));
        
        //제목 필드 없음
        $wrongContents = array(
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $this->assertEquals($requireMessage, $this->board->checkContents($wrongContents));
        
        //작성자 값 없음
        $wrongContents = array(
            'title'     => '제목',
            'writer'    => '',
            'content'   => '내용'
        );
        $this->assertEquals($requireMessage, $this->board->checkContents($wrongContents));
        
        //작성자 필드 없음
        $wrongContents = array(
            'title'     => '제목',
            'content'   => '내용'
        );
        $this->assertEquals($requireMessage, $this->board->checkContents($wrongContents));
        
        //내용 값 없음
        $wrongContents = array(
            'title'     => '제목',
            'writer'    => '작성자',
            'content'   => ''
        );
        $this->assertEquals($requireMessage, $this->board->checkContents($wrongContents));
        
        //내용 필드 없음
        $wrongContents = array(
            'title'     => '제목',
            'writer'    => '작성자'
        );
        $this->assertEquals($requireMessage, $this->board->checkContents($wrongContents));
        
        $wrongContents = array(
            'title'     => '제목',
            'writer'    => '작성자!@#$$',
            'content'   => '내용'
        );
        $wrongNameMessage = 'You can enter only Korean, English, and numbers for names.';
        $this->assertEquals($wrongNameMessage, $this->board->checkContents($wrongContents));
    }
    
    /**
     * Tests Was_Board->_checkExistBoard()
     */
    public function testCheckExistBoard() {
        $contents = array(
            'title'     => '제목',
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $this->board->write($contents, 1);
        
        $this->assertEquals('Not Exist Board.', $this->board->checkExistBoard(100));
    }
    
    /**
     * 파일 업로드 테스트를 위한 메소드
     * 
     * @return array
     *         array(
     *             'boardPk'       => 게시글기본키,
                   'testFilePath'  => 테스트파일경로
     *         )
     */
    protected function _createFileAndBoard() {
        //테스트용 이미지 파일 생성
        $imageFile = imagecreatetruecolor(50, 50);
        $bgColor = imagecolorallocate($imageFile, 255, 0, 0);
        imagefill($imageFile, 0, 0, $bgColor);
        $testFilePath = __DIR__.'/Board/tempFile/test.png';
        imagepng($imageFile, $testFilePath);
        imagedestroy($imageFile);
        //파일 추가를 위한 게시글 삽입
        $boardTable = $this->board->getBoardTable();
        $boardPk = $boardTable->insert(array(
            'memberPk'      => 1,
            'title'         => '테스트',
            'writer'        => '테스터',
            'content'       => '테스트입니다',
            'insertTime'    => new Zend_Db_Expr('NOW()'),
            'updateTime'    => new Zend_Db_Expr('NOW()')
        ));
        
        $info = array(
            'boardPk'       => $boardPk,
            'testFilePath'  => $testFilePath
        );
        return $info;
    }
}

