<?php
/**
 * Was Board Form Board test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Board/Form/Board.php';

/**
 * Was_Board_Form_Board test case.
 */
class Was_Board_Form_BoardTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Board_Form_Board
     */
    private $form;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Board_Form_BoardTest::setUp()

        $this->form = new Was_Board_Form_Board(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Board_Form_BoardTest::tearDown()
        $this->form = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
        // TODO Auto-generated constructor
    }

    /**
     * Tests Was_Board_Form_Board->init()
     */
    public function testInit() {
        $element = $this->form->getElement('title');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('title', $element->getId());
        $this->assertEquals('title', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('제목', $element->getLabel());
        
        $element = $this->form->getElement('content');
        $this->assertInstanceOf('Zend_Form_Element_Textarea', $element);
        $this->assertEquals('content', $element->getId());
        $this->assertEquals('content', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('내용', $element->getLabel());
        
        $element = $this->form->getElement('writer');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('writer', $element->getId());
        $this->assertEquals('writer', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('작성자', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('Regex'));
        
        $element = $this->form->getElement('uploadFile');
        $this->assertInstanceOf('Zend_Form_Element_File', $element);
        $this->assertEquals('uploadFile', $element->getId());
        $this->assertEquals('uploadFile', $element->getName());
        $this->assertEquals('파일업로드', $element->getLabel());
        
        $element = $this->form->getElement('submit');
        $this->assertInstanceOf('Zend_Form_Element_Submit', $element);
        $this->assertEquals('submit', $element->getId());
        $this->assertEquals('submit', $element->getName());
        $this->assertEquals('제출', $element->getLabel());
        
        $element = $this->form->getElement('cancle');
        $this->assertInstanceOf('Zend_Form_Element_Button', $element);
        $this->assertEquals('cancle', $element->getId());
        $this->assertEquals('cancle', $element->getName());
        $this->assertEquals('취소', $element->getLabel());
    }
}

