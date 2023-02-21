<?php
/**
 * Was Member Form Member test file
 */
/*
 * require bootstrap
 */
require_once __DIR__.'/bootstrap.php';
/*
 * require test class
 */
require_once 'Was/Member/Form/Member.php';

/**
 * Was_Member_Form_Member test case.
 */
class Was_Member_Form_MemberTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Was_Member_Form_Member
     */
    private $form;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp();

        // TODO Auto-generated Was_Member_Form_MemberTest::setUp()

        $this->form = new Was_Member_Form_Member(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated Was_Member_Form_MemberTest::tearDown()
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
     * Tests Was_Member_Form_Member->init()
     */
    public function testInit() {
        $element = $this->form->getElement('id');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('id', $element->getId());
        $this->assertEquals('id', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('아이디', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Alnum', $element->getValidator('alnum'));

        $element = $this->form->getElement('pw');
        $this->assertInstanceOf('Zend_Form_Element_Password', $element);
        $this->assertEquals('pw', $element->getId());
        $this->assertEquals('pw', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('비밀번호', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        
        $element = $this->form->getElement('name');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('name', $element->getId());
        $this->assertEquals('name', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('이름', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        
        $element = $this->form->getElement('phone');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('phone', $element->getId());
        $this->assertEquals('phone', $element->getName());
        $this->assertEquals(true, $element->isRequired());
        $this->assertEquals('휴대전화', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_Regex', $element->getValidator('regex'));
        
        $element = $this->form->getElement('email');
        $this->assertInstanceOf('Zend_Form_Element_Text', $element);
        $this->assertEquals('email', $element->getId());
        $this->assertEquals('email', $element->getName());
        $this->assertEquals('이메일', $element->getLabel());
        $this->assertInstanceOf('Zend_Validate_EmailAddress', $element->getValidator('emailaddress'));
        
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

