<?php
/**
 * Was Auth Form Password test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Auth/Form/Password.php';

/**
 * Was_Auth_Form_Password test case.
 */
class Was_Auth_Form_PasswordTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Auth_Form_Password
     */
    private $form;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Auth_Form_PasswordTest::setUp()

        $this->form = new Was_Auth_Form_Password(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Auth_Form_PasswordTest::tearDown()
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
     * Tests Was_Auth_Form_Password->init()
     */
    public function testInit() {
        $element = $this->form->getElement('nowPw');
        $this->assertInstanceOf('Zend_Form_Element_Password', $element);
        $this->assertEquals('nowPw', $element->getId());
        $this->assertEquals('nowPw', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('기존 비밀번호', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        $this->assertEquals('myform-control4', $element->getAttrib('class'));
        
        $element = $this->form->getElement('newPw');
        $this->assertInstanceOf('Zend_Form_Element_Password', $element);
        $this->assertEquals('newPw', $element->getId());
        $this->assertEquals('newPw', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('비밀번호 변경', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        $this->assertEquals('myform-control4', $element->getAttrib('class'));
        
        $element = $this->form->getElement('confirmPw');
        $this->assertInstanceOf('Zend_Form_Element_Password', $element);
        $this->assertEquals('confirmPw', $element->getId());
        $this->assertEquals('confirmPw', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('비밀번호 확인', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        $this->assertEquals('myform-control4', $element->getAttrib('class'));
    }
}

