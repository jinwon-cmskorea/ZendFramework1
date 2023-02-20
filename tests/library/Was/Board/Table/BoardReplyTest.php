<?php
/**
 * Was Board Table BoardReply test file
 */
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * require test class
 */
require_once 'Was/Board/Table/BoardReply.php';

/**
 * Was_Board_Table_BoardReply test case.
 */
class Was_Board_Table_BoardReplyTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Board_Table_BoardReply
     */
    private $boardReply;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Board_Table_BoardReplyTest::setUp()

        $this->boardReply = new Was_Board_Table_BoardReply(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Board_Table_BoardReplyTest::tearDown()
        $this->boardReply = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
        // TODO Auto-generated constructor
    }
    
    /**
     * Tests Was_Board_Table_BoardReply->getTableName()
     */
    public function testGetTableName() {
        // 스키마없음
        $this->assertEquals('board_reply', $this->boardReply->getTableName());
        
        // 스키마있음.
        $this->assertEquals('cmskorea_board_test.board_reply', $this->boardReply->getTableName(true));
    }
}

