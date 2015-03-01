<?php

/**
 * 
 * Форма поиска авто
 * 
 */
class Map_Form_SearchForm extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName('search_form');
        $this->setMethod('get');
        //$this->setAction('/cars/filter');

        $this->addElement('text', 'name', array(
            'label'         => 'Наименование',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_nulledinput.phtml',
                'maxlength'     => '60',
                'class'         => 'map_query'
            )))
        ));

        $this->addElement ('submit', 'search_item', array(
            'label'         => 'Поиск',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));

    }

}