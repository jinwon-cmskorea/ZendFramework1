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
 * Was_Board_Form_Board
 *
 * @package    Was
 * @subpackage Was_Board_Form
 */
class Was_Board_Form_Board extends Zend_Form {
    /*
     * {@inheritDoc}
     * @see Zend_Form::init()
     */
    public function init() {
        $decorators = array(
            "Description", "FormElements", "Fieldset", "Form"
        );
        $this->setDecorators($decorators);
        
        //element 데코레이터 설정
        $this->setElementDecorators(array(
            "ViewHelper",
            "Errors",
            "Description",
            array("HtmlTag", array("tag" => "div"))
        ));
        
        $this->addElement('text', 'title', array(
            'required'  => true,
            'label'     => '제목'
        ));
        
        $this->addElement('textarea', 'content', array(
            'required'  => true,
            'label'     => '내용'
        ));
        
        $this->addElement('text', 'writer', array(
            'required'  => true,
            'label'     => '작성자'
        ));
        $writer = $this->getElement('writer');
        $writer->addValidator(new Zend_Validate_Regex('/[가-힣A-Za-z]+$/'));
        
        $this->addElement('file', 'uploadFile', array(
            'label'     => '파일업로드'
        ));
        
        $this->addElement('submit', 'submit', array('label' => '제출'));
        
        $this->addElement('button', 'cancle', array('label' => '취소'));
    }
}

