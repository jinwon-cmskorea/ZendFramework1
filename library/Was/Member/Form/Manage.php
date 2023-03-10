<?php
/**
 * @see Zend_Form
 */
require_once 'Zend/Form.php';
/**
 * @see Zend_Form_SubForm
 */
require_once 'Zend/Form/SubForm.php';
/**
 * Was_Member_Form_Manage
 *
 * @package    Was
 * @subpackage Was_Member_Form
 */
class Was_Member_Form_Manage extends Zend_Form_SubForm {
    /*
     * {@inheritDoc}
     * @see Zend_Form::init()
     */
    public function init() {
        $decorators = array(
            "Description",
            "FormElements",
            array("Form", array('class' => 'search-member-form')),
            array("HtmlTag", array('tag' => 'div', 'class' => 'board-upper'))
        );
        
        $this->setDecorators($decorators);
        
        $this->setElementDecorators(array(
            "ViewHelper",
            "Description"
        ));
        
        $this->addElement('select', 'category', array(
            'class'     => 'selectbox'
        ));
        
        $this->addElement('text', 'search', array(
            'class'     => 's-input'
        ));
        
        $this->addElement('submit', 'submit', array(
            'class'     => 'btn s-button',
            'label'     => '검색'
        ));
        $submit = $this->getElement('submit');
    }
}

