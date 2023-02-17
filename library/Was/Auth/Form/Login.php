<?php
/**
 * @see Zend_Form
 */
require_once 'Zend/Form.php';
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
            "Description", "FormElements", "Fieldset", "Form"
        );
        $this->setDecorators($decorators);

        // 엘리먼트 데코레이터 설정
        $this->setElementDecorators(array(
            "ViewHelper",
            "Errors",
            "Description",
            array("HtmlTag", array("tag" => "div", "class" => ""))
        ));

        $this->addElement('text', 'id', array(
            'class'       => '',
            'placeholder' => '아이디',
            'required'    => true,
            'label'  => '아이디',
        ));
        $this->addElement('password', 'pw', array(
            'class'         => '',
            'placeholder'   => '비밀번호',
        ));
        $pw = $this->getElement('pw');
        $pw->setRequired();

        $this->addElement('submit', 'login');
        $login = $this->getElement('login');
        $login->setLabel('로그인');
    }
}

