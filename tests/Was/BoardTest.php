<?php
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * @see configs/dbConfig.php
 */
require_once __DIR__ . '/../../../configs/dbConfig.php';
/**
 * @see Cmskorea_Board_Board
 */
require_once '/Cmskorea/Board/Board.php';
/**
 * Cmskorea_Board_Board 테스트를 위한 클래스
 * Cmskorea_Board_BoardTestClass
 */
class Cmskorea_Board_BoardTestClass extends Cmskorea_Board_Board {
    /**
     * 생성자
     * @brief mysqli 객체를 생성해서 멤버변수에 넣어줌
     *
     * @return void
     */
    public function __construct($dbHost, $userName, $userPw, $dbName) {
        $this->_mysqli = mysqli_connect($dbHost, $userName, $userPw, $dbName);
        if (!$this->_mysqli) {
            die("DB 접속중 문제가 발생했습니다. : ".mysqli_connect_error());
        }
    }
    /**
     * 테스트를 위해 mysqli 객체 접근
     * @return mysqli_connect
     */
    public function getMysqli() {
        return $this->_mysqli;
    }
}
/**
 * Cmskorea_Board_Board test case.
 */
class Cmskorea_Board_BoardTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Cmskorea_Board_Board
     */
    private $board;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->board = new Cmskorea_Board_BoardTestClass(DBHOST, USERNAME, USERPW, 'cmskorea_board_test');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $sql = "DELETE FROM board";
        $res = mysqli_query($this->board->getMysqli(), $sql);
        
        $sql2 = "DELETE FROM file";
        $res2 = mysqli_query($this->board->getMysqli(), $sql2);
        
        $sql3 = "DELETE FROM file_details";
        $res3 = mysqli_query($this->board->getMysqli(), $sql3);
        mysqli_close($this->board->getMysqli());
        $this->board = null;

        parent::tearDown();
    }

    /**
     * Tests Cmskorea_Board_Board->addContent()
     */
    public function testAddContent()
    {
        $testContent = array(
            'memberPk'  => 3,
            'title'     => 'test입니다.',
            'writer'    => '테스터',
            'content'   => '테스트 게시글입니다.'
        );
        $res = $this->board->addContent($testContent);
        $sql = "SELECT pk FROM board WHERE memberPK='3'";
        $sqlRes = mysqli_query($this->board->getMysqli(), $sql);
        $row = mysqli_fetch_assoc($sqlRes);
        $this->assertEquals($row['pk'], $res);
    }
    
    /**
     * Tests not input Cmskorea_Board_Board->addContent()
     */
    public function testAddContentNotInput()
    {
        //제목이 없을 경우
        $testContent1 = array(
            'memberPk'  => 3,
            'title'     => '',
            'writer'    => '테스터',
            'content'   => '테스트 게시글입니다.'
        );
        try {
            $res = $this->board->addContent($testContent1);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("필수 항목을 입력해주세요.", $e->getMessage());
        }
        
        //작성자가 없을 경우
        $testContent2 = array(
            'memberPk'  => 3,
            'title'     => '제목있어요',
            'writer'    => '',
            'content'   => '내용있어요'
        );
        try {
            $res = $this->board->addContent($testContent2);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("필수 항목을 입력해주세요.", $e->getMessage());
        }
        
        //내용이 없을 경우
        $testContent3 = array(
            'memberPk'  => 3,
            'title'     => '제목있어요',
            'writer'    => '작성자 있어요',
            'content'   => ''
        );
        try {
            $res = $this->board->addContent($testContent3);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("필수 항목을 입력해주세요.", $e->getMessage());
        }
    }
    
    /**
     * Tests wrong writer Cmskorea_Board_Board->addContent()
     */
    public function testAddContentWrongWriter()
    {
        $testContent = array(
            'memberPk'  => 3,
            'title'     => 'test입니다.',
            'writer'    => '이상한작성자@!$$',
            'content'   => '테스트 게시글입니다.'
        );
        try {
            $res = $this->board->addContent($testContent);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("이름은 한글, 영문, 숫자만 입력할 수 있습니다.", $e->getMessage());
        }
    }

    /**
     * Tests Cmskorea_Board_Board->editContent()
     */
    public function testEditContent()
    {
        $testContent = array(
            'memberPk'  => 10,
            'title'     => '수정 전',
            'writer'    => '테스터수정전',
            'content'   => '게시글수정전'
        );
        $contentNo = $this->board->addContent($testContent);
        sleep(1);//수정 시간을 다르게 하기 위해 sleep 사용
        $editDatas = array(
            'no'        => $contentNo,
            'title'     => '수정 후',
            'writer'    => '테스터수정후',
            'content'   => '게시글수정후'
        );
        $editRes = $this->board->editContent($editDatas);
        $this->assertEquals(true, $editRes);
        
        //테스트용 배열과 실제 수정된 내용이 동일한지 확인
        $sql = "SELECT pk AS no, title, writer, content FROM board WHERE pk='{$contentNo}'";
        $searchRes = mysqli_query($this->board->getMysqli(), $sql);
        $row = mysqli_fetch_assoc($searchRes);
        $this->assertEquals($editDatas, $row);
        
        //작성 시간과 수정 시간이 다른지 확인
        $sql2 = "SELECT insertTime, updateTime FROM board WHERE pk='{$contentNo}'";
        $searchRes2 = mysqli_query($this->board->getMysqli(), $sql2);
        $row2 = mysqli_fetch_assoc($searchRes2);
        $this->assertNotEquals($row2['insertTime'], $row2['updateTime']);
    }
    
    /**
     * Tests not input Cmskorea_Board_Board->editContent()
     */
    public function testEditContentNotInput()
    {
        //제목이 없을 경우
        $testContent1 = array(
            'memberPk'  => 20,
            'title'     => '',
            'writer'    => '테스터수정',
            'content'   => '게시글수정'
        );
        try {
            $this->board->editContent($testContent1);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("필수 항목을 입력해주세요.", $e->getMessage());
        }
        
        //작성자가 없을 경우
        $testContent2 = array(
            'memberPk'  => 20,
            'title'     => '제목수정',
            'writer'    => '',
            'content'   => '게시글수정'
        );
        try {
            $this->board->editContent($testContent2);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("필수 항목을 입력해주세요.", $e->getMessage());
        }
        
        //내용이 없을 경우
        $testContent3 = array(
            'memberPk'  => 20,
            'title'     => '제목수정',
            'writer'    => '테스터수정',
            'content'   => ''
        );
        try {
            $this->board->editContent($testContent3);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("필수 항목을 입력해주세요.", $e->getMessage());
        }
    }
    
    /**
     * Tests wrong writer Cmskorea_Board_Board->editContent()
     */
    public function testEditContentWrongWriter()
    {
        $testContent = array(
            'memberPk'  => 20,
            'title'     => 'test입니다.',
            'writer'    => '이상한수정자@!$$',
            'content'   => '테스트 수정 게시글입니다.'
        );
        try {
            $res = $this->board->editContent($testContent);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals("이름은 한글, 영문, 숫자만 입력할 수 있습니다.", $e->getMessage());
        }
    }

    /**
     * Tests Cmskorea_Board_Board->delContent()
     */
    public function testDelContent()
    {
        $testContent = array(
            'memberPk' => '30',
            'title'   => '삭제될 게시글입니다.',
            'writer'  => '테스터',
            'content' => '불쌍한 게시글'
        );
        //게시글이 삭제되어 true 반환하는지 확인
        $boardNo = $this->board->addContent($testContent);
        $res = $this->board->delContent($boardNo);
        $this->assertTrue($res);
        
        //게시글이 삭제되었는지 확인
        $sql = "SELECT title FROM board WHERE pk={$boardNo}";
        $res2 = mysqli_query($this->board->getMysqli(), $sql);
        $count = mysqli_num_rows($res2);
        $this->assertEquals(0, $count);
    }

    /**
     * Tests Cmskorea_Board_Board->getContent()
     */
    public function testGetContent()
    {
        $testContent = array(
            'memberPk'      => '11',
            'title'         => '조회용',
            'writer'        => '조회테스트',
            'content'       => '조회테스트'
        );
        $boardNo = $this->board->addContent($testContent);
        //추가된 게시글 데이터 불러오기
        $sql = "SELECT * FROM board WHERE pk={$boardNo}";
        $res = mysqli_query($this->board->getMysqli(), $sql);
        $testRow = mysqli_fetch_assoc($res);
        
        //메소드가 데이터를 잘 가져오는지 확인
        $getRow = $this->board->getContent($boardNo);
        $this->assertEquals($testRow, $getRow);
        
        //존재하지 않는 게시글 참조
        $wrongNo = 123123;
        $getRow2 = $this->board->getContent($wrongNo);
        $this->assertEquals(array(), $getRow2);
    }

    /**
     * Tests Cmskorea_Board_Board->getContents()
     */
    public function testGetContents()
    {
        for ($i = 1; $i <= 10; $i++) {
            $testArr = array(
                'memberPk' => $i,
                'title' => "테스트제목 $i",
                'writer' => "테스터 $i",
                'content' => "테스트내용 $i"
            );
            $this->board->addContent($testArr);
        }
        //1. 전체 내용 검색
        $sql1 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board ORDER BY pk DESC LIMIT 0, 10";
        $res1 = mysqli_query($this->board->getMysqli(), $sql1);
        $originArrays1 = array();
        while ($origin1 = mysqli_fetch_assoc($res1)) {
            array_push($originArrays1, $origin1);
        }
        $getRes1 = $this->board->getContents(array());
        $this->assertEquals($originArrays1, $getRes1);
        
        //2. 검색 카테고리 : writer, 검색어 : 테스터 3
        $sql2 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board WHERE writer LIKE '%테스터 3%' ORDER BY pk DESC LIMIT 0, 10";
        $res2 = mysqli_query($this->board->getMysqli(), $sql2);
        $originArrays2 = array();
        while ($origin2 = mysqli_fetch_assoc($res2)) {
            array_push($originArrays2, $origin2);
        }
        $conditionArr2 = array('category' => 'writer', 'search' => '테스터 3');
        $getRes2 = $this->board->getContents($conditionArr2);
        $this->assertEquals($originArrays2, $getRes2);
        
        //3. 검색 카테고리 : title, 검색어 : 테스트제목 3
        $sql3 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board WHERE title LIKE '%테스트제목 3%' ORDER BY pk DESC LIMIT 0, 10";
        $res3 = mysqli_query($this->board->getMysqli(), $sql3);
        $originArrays3 = array();
        while ($origin3 = mysqli_fetch_assoc($res3)) {
            array_push($originArrays3, $origin3);
        }
        $conditionArr3 = array('category' => 'title', 'search' => '테스트제목 3');
        $getRes3 = $this->board->getContents($conditionArr3);
        $this->assertEquals($originArrays3, $getRes3);
        
        //4. 검색 카테고리 : insertTime, 검색어 : 2023-01-06
        $sql4 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board WHERE insertTime LIKE '%2023-01-06%' ORDER BY pk DESC LIMIT 0, 10";
        $res4 = mysqli_query($this->board->getMysqli(), $sql4);
        $originArrays4 = array();
        while ($origin4 = mysqli_fetch_assoc($res4)) {
            array_push($originArrays4, $origin4);
        }
        $conditionArr4 = array('category' => 'insertTime', 'search' => '2023-01-06');
        $getRes4 = $this->board->getContents($conditionArr4);
        $this->assertEquals($originArrays4, $getRes4);
        
        //5. 검색 카테고리 : writer, 검색어 : (아무것도 없을 때 전체 검색)
        $sql5 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board WHERE writer LIKE '%%' ORDER BY pk DESC LIMIT 0, 10";
        $res5 = mysqli_query($this->board->getMysqli(), $sql5);
        $originArrays5 = array();
        while ($origin5 = mysqli_fetch_assoc($res5)) {
            array_push($originArrays5, $origin5);
        }
        $conditionArr5 = array('category' => 'writer', 'search' => '');
        $getRes5 = $this->board->getContents($conditionArr5);
        $this->assertEquals($originArrays5, $getRes5);
        
        //6. 글 번호 기준 오름차순 정렬
        $sql6 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board ORDER BY pk ASC LIMIT 0, 10";
        $res6 = mysqli_query($this->board->getMysqli(), $sql6);
        $originArrays6 = array();
        while ($origin6 = mysqli_fetch_assoc($res6)) {
            array_push($originArrays6, $origin6);
        }
        $conditionArr6 = array('fieldName' => 'pk', 'order' => 'ASC');
        $getRes6 = $this->board->getContents($conditionArr6);
        $this->assertEquals($originArrays6, $getRes6);
        
        //7. 제목 기준 내림차순 정렬
        $sql7 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board ORDER BY title DESC LIMIT 0, 10";
        $res7 = mysqli_query($this->board->getMysqli(), $sql7);
        $originArrays7 = array();
        while ($origin7 = mysqli_fetch_assoc($res7)) {
            array_push($originArrays7, $origin7);
        }
        $conditionArr7 = array('fieldName' => 'title', 'order' => 'DESC');
        $getRes7 = $this->board->getContents($conditionArr7);
        $this->assertEquals($originArrays7, $getRes7);
        
        //8. 작성일자 기준 오름차순 정렬
        $sql8 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board ORDER BY insertTime ASC LIMIT 0, 10";
        $res8 = mysqli_query($this->board->getMysqli(), $sql8);
        $originArrays8 = array();
        while ($origin8 = mysqli_fetch_assoc($res8)) {
            array_push($originArrays8, $origin8);
        }
        $conditionArr8 = array('fieldName' => 'insertTime', 'order' => 'ASC');
        $getRes8 = $this->board->getContents($conditionArr8);
        $this->assertEquals($originArrays8, $getRes8);
        
        //9. 5번째 글부터 10개 출력
        $sql9 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board ORDER BY pk DESC LIMIT 5, 10";
        $res9 = mysqli_query($this->board->getMysqli(), $sql9);
        $originArrays9 = array();
        while ($origin9 = mysqli_fetch_assoc($res9)) {
            array_push($originArrays9, $origin9);
        }
        $conditionArr9 = array('start' => '5');
        $getRes9 = $this->board->getContents($conditionArr9);
        $this->assertEquals($originArrays9, $getRes9);
        
        //10. 검색 카테고리 : writer, 검색 내용 : 테스터 1, 글 번호 기준 오름차순 정렬, 1번째 글 부터 10개 출력
        $sql10 = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board WHERE writer LIKE '%테스터 1%' ORDER BY pk ASC LIMIT 1, 10";
        $res10 = mysqli_query($this->board->getMysqli(), $sql10);
        $originArrays10 = array();
        while ($origin10 = mysqli_fetch_assoc($res10)) {
            array_push($originArrays10, $origin10);
        }
        $conditionArr10 = array(
            'category'      => 'writer',
            'search'        => '테스터 1',
            'fieldName'     => 'pk',
            'order'         => 'ASC',
            'start'         => '1'
        );
        $getRes10 = $this->board->getContents($conditionArr10);
        $this->assertEquals($originArrays10, $getRes10);
    }
    
    /**
     * Tests Cmskorea_Board_Board->addFile()
     */
    public function testAddFile() {
        //테스트용 이미지 파일 생성
        $imageFile = imagecreatetruecolor(50, 50);
        $bgColor = imagecolorallocate($imageFile, 255, 0, 0);
        imagefill($imageFile, 0, 0, $bgColor);
        $testFilePath = __DIR__.'/tempDestination/test.png';
        imagepng($imageFile, $testFilePath);
        imagedestroy($imageFile);
        
        $fileArr = array (
            'name'      => 'test.png',
            'type'      => 'image/png',
            'tmp_name'  => 'tempTest.png',
            'error'     => UPLOAD_ERR_OK,
            'size'      => 132
        );
        $blob = file_get_contents($testFilePath);
        
        //1. 테스트 파일 업로드 및 db 내용과 동일한지 확인
        $fileArr['content'] = $blob;
        $this->board->addFile(1, $fileArr);
        
        $sql = "SELECT pk, boardPk, filename, fileType, fileSize FROM file WHERE boardPk=1";
        $res = mysqli_query($this->board->getMysqli(), $sql);
        $row = mysqli_fetch_assoc($res);
        $testArr = array(
            'pk'            => $row['pk'],
            'boardPk'       => '1',
            'filename'      => 'test.png',
            'fileType'      => 'png',
            'fileSize'      => '132'
        );
        $this->assertEquals($row, $testArr);
        
        $sql2 = "SELECT filePk, content FROM file_details WHERE filePk={$row['pk']}";
        $res2 = mysqli_query($this->board->getMysqli(), $sql2);
        $row2 = mysqli_fetch_assoc($res2);
        $testFileDetail = array(
            'filePk'    => $row2['filePk'],
            'content'   => base64_encode($blob)
        );
        $this->assertEquals($row2, $testFileDetail);
        
        //2. 허용되지 않은 파일 입력시
        $fileArr2 = array (
            'name'      => 'test.blabla',
            'type'      => 'text/css',
            'tmp_name'  => 'tempTest.blabla',
            'error'     => UPLOAD_ERR_OK,
            'size'      => 132
        );
        $fileArr2['content'] = $blob;
        $testRes2 = $this->board->addFile(2, $fileArr2);
        $this->assertEquals(false, $testRes2);
        
        //3. 3MB 초과 파일 업로드시
        $fileArr3 = array (
            'name'      => 'test.blabla',
            'type'      => 'text/css',
            'tmp_name'  => 'tempTest.blabla',
            'error'     => UPLOAD_ERR_OK,
            'size'      => 3200000
        );
        $fileArr3['content'] = $blob;
        $testRes3 = $this->board->addFile(3, $fileArr3);
        $this->assertEquals(false, $testRes3);
        
        //4. 파일 업로드 시 error 발생 시
        $fileArr4 = array (
            'name'      => 'test.png',
            'type'      => 'image/png',
            'tmp_name'  => 'tempTest.png',
            'error'     => UPLOAD_ERR_INI_SIZE,
            'size'      => 132
        );
        $fileArr4['content'] = $blob;
        $testRes4 = $this->board->addFile(4, $fileArr4);
        $this->assertEquals(false, $testRes4);
        
        //생성한 테스트용 이미지 파일 삭제
        unlink($testFilePath);
    }
    
    /**
     * Tests Cmskorea_Board_Board->getFiles()
     */
    public function testGetFiles() {
        //테스트용 이미지 파일 생성
        $imageFile = imagecreatetruecolor(50, 50);
        $bgColor = imagecolorallocate($imageFile, 255, 0, 0);
        imagefill($imageFile, 0, 0, $bgColor);
        $testFilePath = __DIR__.'/tempDestination/test.png';
        imagepng($imageFile, $testFilePath);
        imagedestroy($imageFile);
        
        //첫번째 파일 업로드
        $fileArr1 = array (
            'name'      => 'test1.png',
            'type'      => 'image/png',
            'tmp_name'  => 'tempTest1.png',
            'error'     => UPLOAD_ERR_OK,
            'size'      => 132
        );
        $blob = file_get_contents($testFilePath);
        $fileArr1['content'] = $blob;
        $this->board->addFile(1, $fileArr1);
        
        //두번째 파일 업로드
        $fileArr2 = array (
            'name'      => 'test2.png',
            'type'      => 'image/png',
            'tmp_name'  => 'tempTest2.png',
            'error'     => UPLOAD_ERR_OK,
            'size'      => 132
        );
        $fileArr2['content'] = $blob;
        $this->board->addFile(1, $fileArr2);
        
        //기대값 배열과 실제 불러온 배열이 동일한 지 확인
        $getSql = "SELECT pk, insertTime FROM file WHERE boardPk=1";
        $res = mysqli_query($this->board->getMysqli(), $getSql);
        
        $testArray = array();
        $i = 1;
        while ($row = mysqli_fetch_assoc($res)) {
            $expect = array (
                'pk'            => $row['pk'],
                'boardPk'       => '1',
                'filename'      => 'test'.$i.'.png',
                'fileType'      => 'png',
                'fileSize'      => '132',
                'insertTime'    => $row['insertTime'],
                'content'       => $blob
            );
            array_push($testArray, $expect);
            $i++;
        }
        
        $resultArray = $this->board->getFiles(1);
        $this->assertEquals($testArray, $resultArray);
        
        //파일 업로드하지 않은 게시글에서 파일을 가져올 때
        $resultArray2 = $this->board->getFiles(10);//빈 배열 반환
        $this->assertEquals(array(), $resultArray2);
        
        //생성한 테스트용 이미지 파일 삭제
        unlink($testFilePath);
    }
    
    /**
     * Tests Cmskorea_Board_Board->delFile()
     */
    public function testDelFile() {
        //테스트용 이미지 파일 생성
        $imageFile = imagecreatetruecolor(50, 50);
        $bgColor = imagecolorallocate($imageFile, 255, 0, 0);
        imagefill($imageFile, 0, 0, $bgColor);
        $testFilePath = __DIR__.'/tempDestination/test.png';
        imagepng($imageFile, $testFilePath);
        imagedestroy($imageFile);
        
        //파일 업로드
        $fileArr1 = array (
            'name'      => 'test.png',
            'type'      => 'image/png',
            'tmp_name'  => 'tempTest1.png',
            'error'     => UPLOAD_ERR_OK,
            'size'      => 132
        );
        $blob = file_get_contents($testFilePath);
        $fileArr1['content'] = $blob;
        $this->board->addFile(20, $fileArr1);
        
        //업로드 파일 pk 가져오기
        $sql = "SELECT pk FROM file WHERE boardPk=20";
        $res = mysqli_query($this->board->getMysqli(), $sql);
        $row = mysqli_fetch_assoc($res);
        
        //가져온 pk를 이용해서 업로드 파일 삭제
        $delResult = $this->board->delFile($row['pk']);
        $this->assertTrue($delResult);
        
        //실제로 삭제되었는지 레코드 갯수로 확인
        $sql2 = "SELECT pk FROM file WHERE pk={$row['pk']}";
        $res2 = mysqli_query($this->board->getMysqli(), $sql2);
        $cnt = mysqli_num_rows($res2);
        $this->assertEquals(0, $cnt);
        
        $sql3 = "SELECT filePk FROM file_details WHERE filePk={$row['pk']}";
        $res3 = mysqli_query($this->board->getMysqli(), $sql3);
        $cnt2 = mysqli_num_rows($res3);
        $this->assertEquals(0, $cnt2);
        
        //생성한 테스트용 이미지 파일 삭제
        unlink($testFilePath);
    }
}

