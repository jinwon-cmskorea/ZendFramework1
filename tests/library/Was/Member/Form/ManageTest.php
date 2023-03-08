<?php
/**
 * Was Member Form Manage test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Member/Form/Manage.php';
/**
 * Was_Member_Form_Manage test case.
 */
class Was_Member_Form_ManageTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Member_Form_Manage
     */
    private $form;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Member_Form_ManageTest::setUp()

        $this->form = new Was_Member_Form_Manage(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Member_Form_ManageTest::tearDown()
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
     * Tests Was_Member_Form_Manage->init()
     */
    public function testInit() {
        $element = $this->form->getElement('category');
        $this->assertInstanceOf('Zend_Form_Element_Select', $element);
        $this->assertEquals('category', $element->getName());
        $this->assertEquals('category', $element->getId());
        $this->assertEquals('selectbox', $element->getAttrib('class'));
        
        $element = $this->form->getElement('search');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('search', $element->getName());
        $this->assertEquals('search', $element->getId());
        $this->assertEquals('s-input', $element->getAttrib('class'));
        
        $element = $this->form->getElement('submit');
        $this->assertInstanceOf('Zend_Form_Element_Submit', $element);
        $this->assertEquals('submit', $element->getName());
        $this->assertEquals('submit', $element->getId());
        $this->assertEquals('btn s-button', $element->getAttrib('class'));
        $this->assertEquals('검색', $element->getLabel());
    }
}

