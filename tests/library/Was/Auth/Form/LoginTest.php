<?php
/**
 * Was Auth Form Login test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Auth/Form/Login.php';

/**
 * Was_Auth_Form_Login test case.
 */
class Was_Auth_Form_LoginTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Was_Auth_Form_Login
     */
    private $form;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();
        $this->form = new Was_Auth_Form_Login();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        $this->form = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct() {
    }

    /**
     * Tests Was_Auth_Form_Login->init()
     */
    public function testInit() {
        $element = $this->form->getElement('id');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('id', $element->getId());
        $this->assertEquals('id', $element->getName());
        $this->assertEquals('myForm-control', $element->getAttrib('class'));
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('아이디', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Alnum', $element->getValidator('alnum'));

        $element = $this->form->getElement('pw');
        $this->assertInstanceOf('Zend_Form_Element_Password', $element);
        $this->assertEquals('pw', $element->getId());
        $this->assertEquals('pw', $element->getName());
        $this->assertEquals('myForm-control', $element->getAttrib('class'));
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('비밀번호', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));

        $element = $this->form->getElement('login');
        $this->assertInstanceOf('Zend_Form_Element_Submit', $element);
        $this->assertEquals('login', $element->getId());
        $this->assertEquals('login', $element->getName());
        $this->assertEquals('btn-block login-btn', $element->getAttrib('class'));
    }
}
