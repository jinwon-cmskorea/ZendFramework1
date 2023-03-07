<?php
/**
 * @see Zend_Form
 */
require_once 'Zend/Form.php';
/**
 * @see Zend_Validate_Regex
 */
require_once 'Zend/Validate/Regex.php';
/**
 * Was_Auth_Form_Password
 *
 * @package    Was
 * @subpackage Was_Auth_Form
 */
class Was_Auth_Form_Password extends Zend_Form {
    /*
     * {@inheritDoc}
     * @see Zend_Form::init()
     */
    public function init() {
        $decorators = array(
            "Description",
            "FormElements",
            array("Fieldset", array('class' => 'pwPart display-none'))
        );
        
        $this->setDecorators($decorators);
        
        $this->setElementDecorators(array(
            "ViewHelper",
            "Description",
            array("Label", array('class' => 'pw-category-design')),
            array("HtmlTag", array('tag' => 'div'))
        ));
        
        $this->addElement('password', 'nowPw', array(
            'required'  => true,
            'label'     => '기존 비밀번호',
            'class'     => 'myform-control4'
        ));
        $nowPw = $this->getElement('nowPw');
        $nowPw->addValidator(new Zend_Validate_Regex('/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/'));
        
        $this->addElement('password', 'newPw', array(
            'required'  => true,
            'label'     => '비밀번호 변경',
            'class'     => 'myform-control4'
        ));
        $newPw = $this->getElement('newPw');
        $newPw->addValidator(new Zend_Validate_Regex('/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/'));
        
        $this->addElement('password', 'confirmPw', array(
            'required'  => true,
            'label'     => '비밀번호 확인',
            'class'     => 'myform-control4'
        ));
        $confirmPw = $this->getElement('confirmPw');
        $confirmPw->addValidator(new Zend_Validate_Regex('/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/'));
    }
}

