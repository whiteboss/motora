<?php
/**
 * 
 * Форма редактирования сотрудника
 * 
 */

class Companies_Form_EditEmployer extends Qlick_Form_NulledJQueryForm {

    public function init() {

        $this->setName( 'edit_employer_form' );
        $this->setMethod( 'post' );

        $this->addElement( 'text', 'userTo', array(
            'label'    => 'Empieza ingresar nombre de usuario*',
            'filters'  => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml'
            )))
        ) );
        
        $this->addElement('hidden', 'id_user', array(
            'value'         => '0',
            'filters'       => array('Int'),
        ));        
        
        $this->addElement( 'text', 'position', array(
            'label'    => 'Должность',
            'required' => true,
            'filters'  => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'      => '40'
            )))
        ) );

        $this->addElement ('submit', 'add_item', array(
            'label'   => 'Listo',
            'ignore'  => true,
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));
    }
}