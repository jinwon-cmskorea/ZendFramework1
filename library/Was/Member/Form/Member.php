<?php
/**
 * @see Zend_Form
 */
require_once 'Zend/Form.php';
/**
 * @see Zend_Validate_Regex
 * @see Zend_Validate_EmailAddress
 * @see Zend_Validate_Alnum
 */
require_once 'Zend/Validate/Regex.php';
require_once 'Zend/Validate/EmailAddress.php';
require_once 'Zend/Validate/Alnum.php';
/**
 * Was_Member_Form_Member
 *
 * @package    Was
 * @subpackage Was_Member_Form
 */
class Was_Member_Form_Member extends Zend_Form {
    /*
     * {@inheritDoc}
     * @see Zend_Form::init()
     */
    public function init() {
        $decorators = array(
            "Description", "FormElements", "Fieldset", "Form"
        );
        
        $this->setDecorators($decorators);
        
        $this->setElementDecorators(array(
            "ViewHelper",
            "Errors",
            "Description",
            array("HtmlTag", array("tag" => "div"))
        ));
        
        $this->addElement('text', 'id', array(
            'required'  => true,
            'label'     => '아이디'
        ));
        $id = $this->getElement('id');
        $id->addValidator(new Zend_Validate_Alnum());
        
        $this->addElement('password', 'pw', array(
            'required'  => true,
            'label'     => '비밀번호'
        ));
        $pw = $this->getElement('pw');
        $pw->addValidator(new Zend_Validate_Regex('/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/'));
        
        $this->addElement('text', 'name', array(
            'required'  => true,
            'label'     => '이름'
        ));
        $name = $this->getElement('name');
        $name->addValidator(new Zend_Validate_Regex('/[가-힣A-Za-z]+$/'));
        
        $this->addElement('text', 'phone', array(
            'required'  => true,
            'label'     => '휴대전화'
        ));
        $phone = $this->getElement('phone');
        $phone->addValidator(new Zend_Validate_Regex('/^01(0|1|6|7|8|9)-?([0-9]{3,4})-?([0-9]{4})$/'));
        
        $this->addElement('text', 'email', array(
            'label'     => '이메일'
        ));
        $email = $this->getElement('email');
        $email->addValidator(new Zend_Validate_EmailAddress());
        
        $this->addElement('submit', 'submit', array('label' => '제출'));
        
        $this->addElement('button', 'cancle', array('label' => '취소'));
    }
}

