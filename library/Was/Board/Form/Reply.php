<?php
/**
 * @see Zend_Form
 */
require_once 'Zend/Form.php';
/**
 * Was_Board_Form_Reply
 *
 * @package    Was
 * @subpackage Was_Board_Form
 */
class Was_Board_Form_Reply extends Zend_Form {
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
            array("HtmlTag", array("tag" => "div"))
        ));
        
        $this->addElement('text', 'content', array(
           'class'          => 'reply-content',
           'placeholder'    => '댓글을 남겨요'
        ));
        
        $content = $this->getElement('content');
        $content->removeDecorator('label');
        $content->removeDecorator('HtmlTag');
        
        $this->addElement('submit', 'submit', array(
            'label'         => '등록',
            'class'         => 'reply-submit'
        ));
        $submit = $this->getElement('submit');
        $submit->removeDecorator('label');
        $submit->removeDecorator('HtmlTag');
    }
}

