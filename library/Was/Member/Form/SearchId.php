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
 * Was_Member_Form_SearchId
 *
 * @package    Was
 * @subpackage Was_Member_Form
 */
class Was_Member_Form_SearchId extends Zend_Form {
    /*
     * {@inheritDoc}
     * @see Zend_Form::init()
     */
    public function init() {
        $decorators = array(
            "Description",
            "FormElements",
            array("Form", array(
                'class'     => 'form-horizontal loginForm',
                'name'      => 'search-id-form'
            ))
        );
        //form 데코레이터 설정
        $this->setDecorators($decorators);
        //element 데코레이터 설정
        $this->setElementDecorators(array(
            "ViewHelper",
            "Description",
            array("Label", array('class' => 'col-sm-3 control-label-left')),
            array("HtmlTag", array("tag" => "div", 'class' => 'form-group'))
        ));
        
        $this->addElement('text', 'name', array(
            'class'     => 'searchid-input',
            'required'  => true,
            'label'     => '이름'
        ));
        $name = $this->getElement('name');
        $name->addValidator(new Zend_Validate_Regex('/[가-힣A-Za-z]+$/'));
        
        $this->addElement('text', 'telNumber', array(
            'class'     => 'searchid-input',
            'required'  => true,
            'label'     => '휴대전화번호'
        ));
        $phone = $this->getElement('telNumber');
        $phone->addValidator(new Zend_Validate_Regex('/^01(0|1|6|7|8|9)-?([0-9]{3,4})-?([0-9]{4})$/'));
        
        $this->addElement('submit', 'search', array(
            'class' => 'btn-block searchid-btn',
            'label' => '아이디 찾기'
        ));
        $login = $this->getElement('search');
        $login->removeDecorator('HtmlTag');
        $login->removeDecorator('Label');
        
        
    }
}

