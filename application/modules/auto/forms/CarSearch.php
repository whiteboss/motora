<?php

/**
 * 
 * Форма поиска авто
 * 
 */
class Auto_Form_CarSearch extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName('car_search_form');
        $this->setMethod('get');
        $this->setAction('/cars/filter');

//        $this->addElement('multiCheckbox', 'body', array(
//            'label' => 'Кузов',
//            'registerInArrayValidator' => false
//        ));

//        $this->addElement('multiCheckbox', 'mark', array(
//            'label' => 'Mark',
//            'registerInArrayValidator' => false
//        ));
        $table = new Auto_Model_Table_CarMarks();
        $this->addElement( 'select', 'mark', array(
            //'label'         => 'Marca',
            'required'      => true,
            'filters'       => array( 'Int' ),
            'multiOptions'  => array('0' => '- Elije de mark -') + $table->getAll(),
            'value'         => 0,
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

//        $this->addElement('multiCheckbox', 'series', array(
//            'label' => 'Модель',
//            'registerInArrayValidator' => false
//        ));
        $this->addElement( 'multiselect', 'serie', array(
            'label'         => 'Escribe una series',
            'required'      => true,
            'filters'       => array( 'Int' ),
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'chosen_nulledmultiselect.phtml',
            )))
        ) );

        $this->addElement('text', 'price_from', array(
            'filters'   => array('Int'),
            'label'     => 'Цена',
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('id' => 'price', 'class' => 'range', 'openOnly' => true)))
        ));

        $this->addElement('text', 'price_to', array(
            'filters'   => array('Int'),
            'label'     => ' -',
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('closeOnly' => true)))
        ));

        $this->addElement('text', 'year_from', array(
            'label'     => 'Год выпуска',
            'filters'   => array('Int'),
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('id' => 'year', 'class' => 'range', 'openOnly' => true)))
        ));

        $this->addElement('text', 'year_to', array(
            'filters'   => array('Int'),
            'label'     => ' -',
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('closeOnly' => true)))
        ));

        $this->addElement('text', 'mileage_from', array(
            'filters'   => array('Int'),
            'label'     => 'Пробег',
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('id' => 'mileage', 'class' => 'range', 'openOnly' => true)))
        ));

        $this->addElement('text', 'mileage_to', array(
            'filters'   => array('Int'),
            'label'     => ' -',
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('closeOnly' => true)))
        ));

        $this->addElement('text', 'engine_volume_from', array(
            'filters'   => array('Int'),
            'label'     => 'Объем двигателя',
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('id' => 'engine_volume', 'class' => 'range', 'openOnly' => true)))
        ));

        $this->addElement('text', 'engine_volume_to', array(
            'filters'   => array('Int'),
            'label'     => ' -',
            //'decorators' => array('Label', 'ViewHelper', array('HtmlTag', array('closeOnly' => true)))
        ));
        
        $table = new Auto_Model_Table_CarFuelKinds();
        $this->addElement( 'select', 'fuel_kind', array(
            'multiOptions'  => array('0' => '- Elije de combustible -') + $table->getAll(),
            'filters'       => array( 'Int' ),
            'value'         => 1,
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );
        

//        $this->addElement('multiCheckbox', 'engine_type', array(
//            'label'         => 'Тип двигателя',
//            'multiOptions'  => Auto_Model_CarAd::$engine_types,
//            'value'         => array('0','1', '2'),
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'nulled_multicheckbox.phtml',
//            )))
//        ));
//
//        $this->addElement('multiCheckbox', 'gearbox', array(
//            'label'         => 'Коробка передач',
//            'multiOptions'  => Auto_Model_CarAd::$gearboxes,
//            'value'         => array('0', '1', '2'),
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'nulled_multicheckbox.phtml',
//            )))
//        ));
//
//        $this->addElement('multiCheckbox', 'transmission', array(
//            'label'         => 'Привод',
//            'multiOptions'  => Auto_Model_CarAd::$transmissions,
//            'value'         => array('0', '1', '2'),
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'nulled_multicheckbox.phtml',
//            )))
//        ));
//
        $this->addElement('checkbox', 'withphoto', array(
            'filters'   => array('Int'),
            'label'     => 'Solo con fotos',
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'standart_checkbox.phtml',
//            )))
        ));

        $this->addElement('submit', 'car_search', array(
            'label' => 'Искать',
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'COLLECTION'
            )))
        ));

    }

}