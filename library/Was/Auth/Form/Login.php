<?php
/**
 * @see Zend_Form
 */
require_once 'Zend/Form.php';
/**
 * @see Zend_Validate_Alnum
 * @see Zend_Validate_Regex
 */
require_once 'Zend/Validate/Alnum.php';
require_once 'Zend/Validate/Regex.php';
/**
 * Was_Auth_Form_Login
 *
 * @package    Was
 * @subpackage Was_Auth_Form
 */
class Was_Auth_Form_Login extends Zend_Form {
    /*
     * {@inheritDoc}
     * @see Zend_Form::init()
     */
    public function init() {
        $decorators = array(
            "Description",
            "FormElements",
            array('Form', array(
                    'class'     => 'form-horizontal loginForm',
                    'name'      => 'login-form'
                ))
        );
        $this->setDecorators($decorators);

        // 엘리먼트 데코레이터 설정
        $this->setElementDecorators(array(
            "ViewHelper",
            "Description",
            array("Label", array('class' => 'col-sm-2 control-label-left')),
            array("HtmlTag", array("tag" => "div", 'class' => 'form-group'))
        ));

        $this->addElement('text', 'id', array(
            'class'          => 'myForm-control',
            'required'      => true,
            'label'         => '아이디',
        ));
        $id = $this->getElement('id');
        $id->addValidator(new Zend_Validate_Alnum());
        
        $this->addElement('password', 'pw', array(
            'class'         => 'myForm-control',
            'required'      => true,
            'label'         => '비밀번호',
        ));
        $pw = $this->getElement('pw');
        $pw->addValidator(new Zend_Validate_Regex('/(?=.*[~`!@#$%\^&*()-+=])[A-Za-z0-9~`!@#$%\^&*()-+=]+$/'));

        $this->addElement('submit', 'login', array(
            'class' => 'btn-block login-btn',
            'label' => '로그인'
        ));
        $login = $this->getElement('login');
        $login->removeDecorator('HtmlTag');
        $login->removeDecorator('Label');
    }
}

