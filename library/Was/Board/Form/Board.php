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
            "Description", "FormElements", "Form"
        );
        $this->setDecorators($decorators);
        
        //element 데코레이터 설정
        $this->setElementDecorators(array(
            "ViewHelper",
            "Description",
            array("Label", array("class" => "control-label-center")),
            array("HtmlTag", array("tag" => "div", "class" => "form-group"))
        ));
        
        $this->addElement('text', 'title', array(
            'required'  => true,
            'class'     => 'write-board-title space-form',
            'label'     => '제 목'
        ));
        
        $this->addElement('textarea', 'content', array(
            'required'  => true,
            'class'     => 'write-board-textarea space-form',
            'label'     => '내 용'
        ));
        
        $this->addElement('text', 'writer', array(
            'required'  => true,
            'class'     => 'write-board-input space-form input-writer',
            'label'     => '작성자'
        ));
        $writer = $this->getElement('writer');
        $writer->addValidator(new Zend_Validate_Regex('/[가-힣A-Za-z]+$/'));
        
        $this->addElement('file', 'uploadFile', array(
            'label'     => '파일업로드',
            'class'     => 'space-form'
        ));
        
        //제출 및 취소 버튼의 label 과 HtmlTag 데코레이터 제거
        $this->addElement('submit', 'submit', array(
            'class'     => 'submit-btn'
        ));
        $submit = $this->getElement('submit');
        $submit->removeDecorator('label');
        $submit->removeDecorator('HtmlTag');
        
        $this->addElement('button', 'cancle', array(
            'label'     => '취 소',
            'class'     => 'cancle-btn'
        ));
        $cancle = $this->getElement('cancle');
        $cancle->removeDecorator('label');
        $cancle->removeDecorator('HtmlTag');
        
        //작성, 취소 버튼을 묶어주기 위해 DisplayGroup 사용
        $this->addDisplayGroup(array('submit', 'cancle'), 'btns');
        $group = $this->getDisplayGroup('btns');
        $group->clearDecorators();
        $group->addDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'write-button'))
        ));
    }
}

