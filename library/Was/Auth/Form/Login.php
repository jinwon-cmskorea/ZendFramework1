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
            "Description",
            "FormElements",
            array('Form', array('class' => 'form-horizontal'))
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
        $this->addElement('password', 'pw', array(
            'class'         => 'myForm-control',
            'required'      => true,
            'label'         => '비밀번호',
        ));

        $this->addElement('submit', 'login', array(
            'class' => 'btn-block login-btn',
            'label' => '로그인'
        ));
        $login = $this->getElement('login');
        $login->removeDecorator('HtmlTag');
        $login->removeDecorator('Label');
    }
}

