<?php
/**
 * Was_Board_Table_Board test file
 */
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * require test class
 */
require_once 'Was/Board/Table/Board.php';
/**
 * Was_Board_Table_BoardTest
 */
class Was_Board_Table_BoardTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Was_Board_Table_Board
     */
    private $board;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Board_Table_BoardTest::setUp()

        $this->board = new Was_Board_Table_Board(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Board_Table_BoardTest::tearDown()
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
     * Tests Was_Board_Table_Board->getTableName()
     */
    public function testGetTableName() {
        // 스키마없음
        $this->assertEquals('board', $this->board->getTableName());
        
        // 스키마있음.
        $this->assertEquals('cmskorea_board_test.board', $this->board->getTableName(true));
    }
}

