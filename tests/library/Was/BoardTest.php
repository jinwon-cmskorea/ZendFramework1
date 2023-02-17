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
}

/**
 *Was_BoardTest
 */
class Was_BoardTest extends PHPUnit_Framework_TestCase
{

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
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->board = new Was_BoardTestClass(Zend_Db::factory('Mysqli', array(
            'host'      => '127.0.0.1',
            'username'  => 'root',
            'password'  => 'cmskorea',
            'dbname'    => 'cmskorea_board_test'
        )));
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
        
        $this->board->getFilDetailTable()->delete(1);
        $this->board->getFilDetailTable()->getAdapter()->query("ALTER TABLE `file_details` auto_increment = 1");
        $this->board = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests Was_Board->write()
     */
    public function testWrite()
    {
        $contents = array(
            'title'     => '제목',
            'writer'    => '작성자',
            'content'   => '내용'
        );
        $actual = $this->board->write($contents, 1);
        $this->assertInstanceOf('Zend_Db_Table_Row', $actual);
        $this->assertEquals(1, $actual->memberPk);
        $this->assertEquals('제목', $actual->title);
        $this->assertEquals('작성자', $actual->writer);
        $this->assertEquals('내용', $actual->content);
    }

    /**
     * Tests Was_Board->edit()
     */
    public function testEdit()
    {
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
        
        //정상적으로 파일 삽입
        $fileInfos = $this->fileTestArray;
        $fileInfos['content'] = file_get_contents($testFilePath);
        $this->assertTrue($this->board->addFile($boardPk, $fileInfos));
        
        //정상적으로 파일 삽입2
        $fileInfos2 = $this->fileTestArray;
        $fileInfos2['name'] = 'test2.png';
        $fileInfos2['content'] = file_get_contents($testFilePath);
        $this->assertTrue($this->board->addFile($boardPk, $fileInfos2));
        
        //정상적으로 파일 가져오기 - 2개 가져옴
        $getFiles = $this->board->getFiles($boardPk);
        $this->assertEquals('test.png', $getFiles[0]['filename']);
        $this->assertEquals('test2.png', $getFiles[1]['filename']);
        
        //잘못된 boardPk 삽입
        $this->assertFalse($this->board->addFile(3, $fileInfos));
        
        //fileType이 빈 값일 경우 false
        $fileInfos = array(
            'type' => ''
        );
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //fileType 필드가 존재하지않는 경우
        $fileInfos = $this->fileTestArray;
        unset($fileInfos['type']);
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //허용되지않은 확장자
        $fileInfos = $this->fileTestArray;
        $fileInfos['type'] = 'hwp';
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //fileSize 필드가 존재하지 않는 경우
        $fileInfos = $this->fileTestArray;
        unset($fileInfos['size']);
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //fileSize이 빈값인 경우
        $fileInfos = $this->fileTestArray;
        $fileInfos['size'] = '';
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //허용된 fileSize를 초과하는 경우
        $fileInfos = $this->fileTestArray;
        $fileInfos['size'] = 3200000;
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //name 필드가 존재하지 않는 경우
        $fileInfos = $this->fileTestArray;
        unset($fileInfos['name']);
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //name이 빈값인 경우
        $fileInfos = $this->fileTestArray;
        $fileInfos['name'] = '';
        $this->assertFalse($this->board->addFile($boardPk, $fileInfos));
        
        //넣었던 테스트 게시글 삭제
        $boardTable->find($boardPk)->current()->delete();
        //테스트 파일 삭제
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'Board' . DIRECTORY_SEPARATOR . 'tempFile' . DIRECTORY_SEPARATOR . 'test.png');
    }

    /**
     * Tests Was_Board->deleteFile()
     */
    public function testDeleteFile()
    {
        // TODO Auto-generated Was_BoardTest->testDeleteFile()
        $this->markTestIncomplete("deleteFile test not implemented");

        $this->board->deleteFile(/* parameters */);
    }

    /**
     * Tests Was_Board->read()
     */
    public function testRead()
    {
        // TODO Auto-generated Was_BoardTest->testRead()
        $this->markTestIncomplete("read test not implemented");

        $this->board->read(/* parameters */);
    }

    /**
     * Tests Was_Board->reads()
     */
    public function testReads()
    {
        // TODO Auto-generated Was_BoardTest->testReads()
        $this->markTestIncomplete("reads test not implemented");

        $this->board->reads(/* parameters */);
    }

    /**
     * Tests Was_Board->delete()
     */
    public function testDelete()
    {
        // TODO Auto-generated Was_BoardTest->testDelete()
        $this->markTestIncomplete("delete test not implemented");

        $this->board->delete(/* parameters */);
    }

    /**
     * Tests Was_Board->getReply()
     */
    public function testGetReply()
    {
        // TODO Auto-generated Was_BoardTest->testGetReply()
        $this->markTestIncomplete("getReply test not implemented");

        $this->board->getReply(/* parameters */);
    }

    /**
     * Tests Was_Board->writeReply()
     */
    public function testWriteReply()
    {
        // TODO Auto-generated Was_BoardTest->testWriteReply()
        $this->markTestIncomplete("writeReply test not implemented");

        $this->board->writeReply(/* parameters */);
    }

    /**
     * Tests Was_Board->deleteReply()
     */
    public function testDeleteReply()
    {
        // TODO Auto-generated Was_BoardTest->testDeleteReply()
        $this->markTestIncomplete("deleteReply test not implemented");

        $this->board->deleteReply(/* parameters */);
    }
    
    /**
     * Tests Was_Board->_checkContents()
     */
    public function testCheckContents()
    {
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
}

