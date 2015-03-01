<?php
/**
 * 
 * Форма редактирования раздела меню
 * 
 */

class Companies_Form_EditMenuCategory extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName( 'edit_menucategory_form' );
        $this->setMethod( 'post' );

        $this->addElement( 'text', 'name', array(
            'label'         => 'Название',
            'required'      => true,
            'description'   => 'Например, Холодние закуски или Коктейли',
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '22'
            )))
        ) );

        $this->addElement( 'radio', 'type', array(
            'label'         => 'Тип категории',
            'multiOptions'  => array(1=>'Блюда', 2=>'Напитки'),
            'required'      => true,
            'filters'       => array('Int'),
            'value'         => 1,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_radio.phtml'
            )))
        ) );

//        $this->addElement( 'textarea', 'description', array(
//            'label'         => 'Краткое описание',
//            'rows'          => 4,
//            'filters'       => array('StringTrim', 'StripTags'),
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'standart_textarea.phtml',
//                'maxlength'     => '200'
//            )))
//        ) );
//
//        $this->addElement( 'hidden', 'id_parent', array(
//            'label'         => 'Раздел',
//            'required'      => true,
//            'filters'       => array('Int'),
//            'decorators'    => array('ViewHelper')
//        ) );

        $this->addElement( 'submit', 'add_item', array(
            'label'         => 'Опубликовать',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));
    }
}