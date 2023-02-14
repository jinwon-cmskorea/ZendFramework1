<?php
/**
 * CMSKOREA BOARD
 *
 * @category Was
 */
/**
 * 씨엠에스코리아 게시판 클래스
 *
 * @category Was
 */
class Was_Board {
    /**
     * DB연결 변수
     *
     * @var mysqli_connect 로 부터 리턴 받음
     */
    protected $_mysqli;
    /**
     * 씨엠에스코리아 인증 클래스
     *
     * @var Was_Auth
     */
    protected $_auth;
    
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
     */
    protected function _checkDatas(array $datas) {
        if (!$datas['title'] || !$datas['writer'] || !$datas['content']) {
            throw new Exception("필수 항목을 입력해주세요.");
        } else if (!preg_match("/[가-힣A-Za-z0-9]+$/", $datas['writer'])) {
            throw new Exception("이름은 한글, 영문, 숫자만 입력할 수 있습니다.");
        }
    }
    /**
     * 글을 작성한다.
     *
     * @throws Exception _checkDatas 메소드에 의해 발생
     *                   필수 항목을 입력하지 않았을 경우
     *                   이름 작성 조건을 지키지않았을 경우
     * @param array 작성할 내용
     *        array(
     *            'memberPk' => '작성자고유키'
     *            'title'   => '제목',
     *            'writer'  => '작성자',
     *            'content' => '내용'
     *        )
     * @return 글번호
     */
    public function addContent(array $datas) {
        try {
            $this->_checkDatas($datas);
            
            $fTitle = mysqli_real_escape_string($this->_mysqli, $datas['title']);
            $fWriter = mysqli_real_escape_string($this->_mysqli, $datas['writer']);
            $fContent = mysqli_real_escape_string($this->_mysqli, $datas['content']);
            
            $sql = "INSERT INTO board(memberPk, title, writer, content, insertTime, updateTime) VALUES ('{$datas['memberPk']}', '{$fTitle}', '{$fWriter}', '{$fContent}', now(), now())";
            mysqli_query($this->_mysqli, $sql);
            if (mysqli_errno($this->_mysqli)) {
                throw new Exception('게시글을 등록하지 못했습니다. 관리자에게 문의하십시오.');
            }
            $insertedNum = mysqli_insert_id($this->_mysqli);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $insertedNum;
    }

    /**
     * 내용을 수정한다.
     * @throws Exception _checkDatas 메소드에 의해 발생
     *                   필수 항목을 입력하지 않았을 경우
     *                   이름 작성 조건을 지키지않았을 경우
     * @param array 수정할 내용 (글번호 포함)
     *        array(
     *            'no'      => '글번호',
     *            'title'   => '제목',
     *            'writer'  => '작성자',
     *            'content' => '내용'
     *        )
     * @return boolean
     */
    public function editContent(array $datas) {
        // updateTime 수정
        try {
            $this->_checkDatas($datas);
            
            $fTitle = mysqli_real_escape_string($this->_mysqli, $datas['title']);
            $fWriter = mysqli_real_escape_string($this->_mysqli, $datas['writer']);
            $fContent = mysqli_real_escape_string($this->_mysqli, $datas['content']);
            
            $sql = "UPDATE board SET title='{$fTitle}', writer='{$fWriter}', content='{$fContent}', updateTime=now() WHERE pk='{$datas['no']}'";
            mysqli_query($this->_mysqli, $sql);
            if (mysqli_affected_rows($this->_mysqli) < 1) {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return true;
    }

    /**
     * 을 삭제한다.
     *
     * @param number 글번호
     * @return boolean
     */
    public function delContent($no) {
        $sql = "DELETE FROM board WHERE pk={$no}";
        mysqli_query($this->_mysqli, $sql);
        
        $selectSql = "SELECT COUNT(pk) AS count FROM board WHERE pk={$no}";
        $selectRes = mysqli_query($this->_mysqli, $selectSql);
        $row = mysqli_fetch_assoc($selectRes);
        if ($row['count'] != 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 글을 리턴한다.
     *
     * @param number 글번호
     * @return array 글번호에 해당하는 데이터 전체
     */
    public function getContent($no) {
        $sql = "SELECT * FROM board WHERE pk={$no}";
        $res = mysqli_query($this->_mysqli, $sql);
        $row = mysqli_fetch_assoc($res);
        //만약 count 가 0이면, 작성되지않은 게시글이거나, 잘못된 접근이므로 빈 배열 리턴
        if ($row == NULL) {
            return array();
        }
        return $row;
    }

    /**
     * 조건에 해당하는 글들을 리턴한다.
     *
     * @param array 조회조건(모든 글을 리턴하는 경우 빈배열)
     *        array(
     *            'categoty'        => '검색 카테고리',
     *            'search'          => '검색 내용',
     *            'fieldName'       => '정렬할 필드이름',
     *            'order'           => '정렬 방식',
     *            'start'           => '출력 시작 번호'
     *        )
     * @return array 글 내용을 제외한 모든 데이터
     */
    public function getContents(array $conditions) {
        $conditionArr = array(
            'writer',
            'title',
            'insertTime'
        );
        $fieldNameArr = array(
            'pk',
            'title',
            'writer',
            'insertTime'
        );
        /*
         * 조회조건이 없으면
         * SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board ORDER BY pk DESC LIMIT 0, 10
         * 이 조립됨
         * 조건에 따라 sql이 조립됨
         */
        $sql = "SELECT pk, memberPk, title, writer, insertTime, updateTime FROM board";
        
        //검색 조건이 있을 경우
        if (array_key_exists('category', $conditions) && array_key_exists('search', $conditions)) {
            $sql .= " WHERE";
            foreach ($conditionArr as $condition) {
                if ($conditions['category'] == $condition) {
                    $sql .= " {$conditions['category']} LIKE '%{$conditions['search']}%'";
                    break;
                }
            }
        }
        //정렬 조건이 있을 경우
        if (array_key_exists('fieldName', $conditions) && array_key_exists('order', $conditions)) {
            $sql .= " ORDER BY";
            foreach ($fieldNameArr as $fieldCon) {
                if ($conditions['fieldName'] == $fieldCon) {
                    $sql .= " {$conditions['fieldName']} {$conditions['order']}";
                    break;
                }
            }
        } else {
            $sql .= " ORDER BY pk DESC";
        }
        //페이징 조건이 있을 경우
        if (array_key_exists('start', $conditions)) {
            $sql .= " LIMIT {$conditions['start']}, 10";
        } else {
            $sql .= " LIMIT 0, 10";
        }
        $res = mysqli_query($this->_mysqli, $sql);
        if (!$res) {
            return array();
        }
        $rowArrays = array();
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($rowArrays, $row);
        }
        return $rowArrays;
    }

    /**
     * 업로드된 파일을 등록한다.
     *
     * @param number 게시판 고유키
     * @param array  $_FILES 함수의 내용
     *        array (
     *            'name'      => '파일 원래 이름',
     *            'type'      => '파일의 mime 형식',
     *            'tmp_name'  => '업로드된 파일 임시 이름',
     *            'error'     => '업로드와 관련된 에러 코드',
     *            'size'      => '업로드 파일 크기를 바이트로 표현',
     *            'content'   => '파일의 내용'
     *        )
     * @return boolean
     */
    public function addFile($boardPk, array $fileInfos) {
        $allowFiles = array(
            'jpeg', 'jpg', 'gif', 'png', 'pdf'
        );
        $fileType = explode('/', $fileInfos['type']);
        if (!in_array($fileType[1], $allowFiles)) { //파일 업로드 시 허용된 mime(jpg 등) 타입이 아니면 false 반환
            return false;
        } else if ($fileInfos['error'] > 0) { //파일 업로드 시 에러가 존재하면 false 반환
            return false;
        } else if ($fileInfos['size'] > 3145728) {//파일 업로드 시 3MB 초과하면 false 반환
            return false;
        } else {
            $sql1 = "INSERT INTO file(boardPk, filename, fileType, fileSize, insertTime) VALUES ({$boardPk}, '{$fileInfos['name']}', '{$fileType[1]}', {$fileInfos['size']}, now())";
            $res1 = mysqli_query($this->_mysqli, $sql1);
            if (!$res1) {
                return false;
            }
            $filePk = mysqli_insert_id($this->_mysqli);
            
            $content = base64_encode($fileInfos['content']);
            
            $sql2 = "INSERT INTO file_details(filePk, content) VALUES ({$filePk}, '{$content}')";
            $res2 = mysqli_query($this->_mysqli, $sql2);
            if (!$res2) {
                return false;
            }
        }
        return true;
    }

    /**
     * 게시물에 업로드된 파일들을 리턴한다.
     *
     * @param number 글번호
     * @return array 글번호에 해당하는 파일데이터
     *         array (
     *             'index(최대 2개)' => array (
     *                 'pk'         => '파일 고유키',
     *                 'boardPk'    => '게시판 고유키',
     *                 'filename'   => '파일명',
     *                 'fileType'   => '파일타입',
     *                 'fileSize'   => '파일크기',
     *                 'insertTime' => '등록시간',
     *                 'content'    => '파일 내용'
     *             )
     *         )
     */
    public function getFiles($boardPk) {
        $sql = "SELECT * FROM file WHERE boardPk={$boardPk}";
        $res = mysqli_query($this->_mysqli, $sql);
        
        $boardFiles = array();
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row == NULL) {
                return array();
            }
            $sql2 = "SELECT content FROM file_details WHERE filePk={$row['pk']}";
            $res2 = mysqli_query($this->_mysqli, $sql2);
            $row2 = mysqli_fetch_assoc($res2);
            if (!$row2) { 
                return array();
            }
            
            $row['content'] = base64_decode($row2['content']);
            array_push($boardFiles, $row);
        }
        return $boardFiles;
    }

    /**
     * 업로드 한 파일을 삭제한다.
     *
     * @param number 파일고유키
     * @return boolean
     */
    public function delFile($filePk) {
        $sql = "DELETE FROM file WHERE pk={$filePk}";
        mysqli_query($this->_mysqli, $sql);
        
        $selectSql1 = "SELECT COUNT(pk) AS count FROM file WHERE pk={$filePk}";
        $selectRes1 = mysqli_query($this->_mysqli, $selectSql1);
        $row1 = mysqli_fetch_assoc($selectRes1);
        if ($row1['count'] != 0) {
            return false;
        }
        
        $sql2 = "DELETE FROM file_details WHERE filePk={$filePk}";
        mysqli_query($this->_mysqli, $sql2);
        
        $selectSql2 = "SELECT COUNT(filePk) AS count FROM file_details WHERE filePk={$filePk}";
        $selectRes2 = mysqli_query($this->_mysqli, $selectSql2);
        $row2 = mysqli_fetch_assoc($selectRes2);
        if ($row2['count'] != 0) {
            return false;
        }

        return true;
    }
}

