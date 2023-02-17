<?php
/**
 * Was Board Table FileDetails test file
 */
/**
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/**
 * require test class
 */
require_once 'Was/Board/Table/FileDetails.php';

/**
 * Was_Board_Table_FileDetailsTest
 */
class Was_Board_Table_FileDetailsTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Was_Board_Table_FileDetails
     */
    private $fileDetails;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Board_Table_FileDetailsTest::setUp()

        $this->fileDetails = new Was_Board_Table_FileDetails(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Board_Table_FileDetailsTest::tearDown()
        $this->fileDetails = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
    }
    
    /**
     * Tests Was_Board_Table_FileDetails->getTableName()
     */
    public function testGetTableName() {
        // 스키마없음
        $this->assertEquals('file_details', $this->fileDetails->getTableName());
        
        // 스키마있음.
        $this->assertEquals('cmskorea_board_test.file_details', $this->fileDetails->getTableName(true));
    }
}

