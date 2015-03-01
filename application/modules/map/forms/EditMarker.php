<?php

class Map_Form_EditMarker extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName("markerEdit");
        $this->setMethod("post");

//        $this->addElement("text", "title", array(
//            'label' => 'Название',
//            'required' => true,
//            'filters' => array('StringTrim', 'StripTags'),
//            'decorators' => array(array('ViewScript', array(
//                'viewScript' => 'standart_input.phtml'
//            )))
//        ));
//        
//        $this->addElement("text", "user_id", array(
//            'filters' => array('Int'),
//            'decorators' => array(array('ViewScript', array(
//                'viewScript' => 'standart_input.phtml'
//            )))
//        ));
//        
//        $this->addElement("text", "category_id", array(
//            'label' => 'Категория',
//            'required' => true,
//            'filters' => array('Int'),
//            'decorators' => array(array('ViewScript', array(
//                'viewScript' => 'standart_input.phtml'
//            )))
//        ));    
        
        $this->addElement("text", "lat", array(
            'label' => 'Латитуда',
            'required' => true,
            'validators' => array(new Qlick_Validate_LatLng()),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml'
            )))
        ));
        
        $this->addElement("text", "lng", array(
            'label' => 'Лонгитуда',
            'required' => true,
            'validators' => array(new Qlick_Validate_LatLng()),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml'
            )))
        ));
        
        $this->addElement("text", "company_id", array(
            'label' => 'Компания',
            'required' => true,
            'filters' => array('Digits'),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml'
            )))
        ));        

//        $this->addElement("textarea", "description", array(
//            'label' => 'Описание',
//            'required' => false,
//            'filters' => array('StringTrim', 'StripTags'),
//            'decorators' => array(array('ViewScript', array(
//                'viewScript' => 'standart_textarea.phtml',
//            )))
//        ));
//        
//        $this->addElement("text", "address", array(
//            'label' => 'Адрес',
//            'required' => false,
//            'filters' => array('StringTrim', 'StripTags'),
//            'decorators' => array(array('ViewScript', array(
//                'viewScript' => 'standart_input.phtml',
//            )))
//        ));        
//
//        $this->addElement('text', 'icon', array(
//            'label'     => 'Иконка',
//            'required'  => true,
//            'filters'   => array('StringTrim', 'StripTags'),
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'standart_input.phtml'
//            )))            
//        ));      

        $this->addElement('submit', 'add_item', array(
            'label' => 'Listo',
            'ignore' => true,
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_submit.phtml',
                'formname' => $this->getName(),
                'button_class' => 'standard_save_button COLLECTION'
            )))
        ));
    }

}

