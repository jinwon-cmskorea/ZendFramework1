<?php
/**
 * Was Board Table File test file
 */
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * require test class
 */
require_once 'Was/Board/Table/File.php';

/**
 * Was_Board_Table_File test case.
 */
class Was_Board_Table_FileTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Board_Table_File
     */
    private $file;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Board_Table_FileTest::setUp()

        $this->file = new Was_Board_Table_File(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Board_Table_FileTest::tearDown()
        $this->file = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
        // TODO Auto-generated constructor
    }
    
    /**
     * Tests Was_Board_Table_File->getTableName()
     */
    public function testGetTableName() {
        // 스키마없음
        $this->assertEquals('file', $this->file->getTableName());
        
        // 스키마있음.
        $this->assertEquals('cmskorea_board_test.file', $this->file->getTableName(true));
    }
}

