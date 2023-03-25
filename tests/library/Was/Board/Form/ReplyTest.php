<?php
/**
 * Was Board Form Reply test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Board/Form/Reply.php';
/**
 * Was_Board_Form_Reply test case.
 */
class Was_Board_Form_ReplyTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Board_Form_Reply
     */
    private $form;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Board_Form_ReplyTest::setUp()

        $this->form = new Was_Board_Form_Reply(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated Was_Board_Form_ReplyTest::tearDown()
        $this->form = null;

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
     * Tests Was_Board_Form_Reply->init()
     */
    public function testInit()
    {
        $element = $this->form->getElement('reply-contnet');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('reply-contnet', $element->getId());
        $this->assertEquals('reply-contnet', $element->getName());
        $this->assertEquals('댓글을 남겨요', $element->getAttrib('placeholder'));
        $this->assertEquals('reply-content', $element->getAttrib('class'));
        
        $element = $this->form->getElement('submit');
        $this->assertInstanceOf('Zend_Form_Element_Submit', $element);
        $this->assertEquals('submit', $element->getId());
        $this->assertEquals('submit', $element->getName());
        $this->assertEquals('등록', $element->getLabel());
        $this->assertEquals('reply-submit', $element->getAttrib('class'));
    }
}

