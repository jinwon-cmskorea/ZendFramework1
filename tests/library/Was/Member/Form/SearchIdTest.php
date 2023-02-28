<?php
/**
 * Was Member Form SearchId test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Member/Form/SearchId.php';
/**
 * Was_Member_Form_SearchId test case.
 */
class Was_Member_Form_SearchIdTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Member_Form_SearchId
     */
    private $form;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Member_Form_SearchIdTest::setUp()

        $this->form = new Was_Member_Form_SearchId(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Member_Form_SearchIdTest::tearDown()
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
     * Tests Was_Member_Form_SearchId->init()
     */
    public function testInit() {
        $element = $this->form->getElement('name');
        $this->assertInstanceOf('Zend_Form_Element_text', $element);
        $this->assertEquals('name', $element->getId());
        $this->assertEquals('name', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('이름', $element->getLabel());
        $this->assertEquals('searchid-input', $element->getAttrib('class'));
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        
        $element = $this->form->getElement('telNumber');
        $this->assertInstanceOf('Zend_Form_Element_text', $element);
        $this->assertEquals('telNumber', $element->getId());
        $this->assertEquals('telNumber', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('휴대전화번호', $element->getLabel());
        $this->assertEquals('searchid-input', $element->getAttrib('class'));
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        
        $element = $this->form->getElement('search');
        $this->assertInstanceOf('Zend_Form_Element_Submit', $element);
        $this->assertEquals('search', $element->getId());
        $this->assertEquals('search', $element->getName());
        $this->assertEquals('btn-block searchid-btn', $element->getAttrib('class'));
    }
}

