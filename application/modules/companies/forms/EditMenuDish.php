<?php
/**
 * 
 * Форма редактирования блюда меню
 * 
 */

class Companies_Form_EditMenuDish extends Qlick_Form_NulledForm {

	public function init() {

            $this->setName( 'edit_menudish_form' );
            $this->setMethod( 'post' );

            $this->addElement( 'text', 'name', array(
                'label'         => 'Название блюда',
                'required'      => true,
                'filters'       => array( 'StringTrim', 'StripTags' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '60'
                )))
            ) );

            $this->addElement( 'hidden', 'photo', array(
                'label'     => 'Фотография',
                'filters'   => array('StringTrim', 'StripTags')
            ) );

            $this->addElement( 'textarea', 'description', array(
                'label'         => 'Описание блюда или состав',
                'required'      => false,
                'rows'          => 4,
                'description'   => 'Por ejemplo: Итальянская закуска из сладкого перца',
                'filters'       => array('StringTrim', 'StripTags'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                    'maxlength'     => '120'
                )))
            ) );

            $this->addElement( 'text', 'price', array(
                'label'         => 'Precio, peso',
                'required'      => true,
                'description'   => 'Solo en cifras, por favor',
                'filters'       => array( 'Digits' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '5'
                )))
            ) );

            $this->addElement( 'text', 'volume', array(
                'label'         => 'Граммы', // меняется в контроллере
                'required'      => false,
                'description'   => 'Solo en cifras, por favor',
                'filters'       => array( 'Digits' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '4'
                )))
            ) );

            $this->addElement( 'checkbox', 'delivery', array(
                'label'         => 'Есть доставка',
                'filters'       => array('Int'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_checkbox.phtml',
                    'class'         => 'form element'
                )))
            ) );

//            $this->addElement( 'hidden', 'id_catalog', array(
//                'label'         => 'Подраздел',
//                'required'      => true,
//                'filters'       => array('Int'),
//                'decorators'    => array('ViewHelper')
//            ) );

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