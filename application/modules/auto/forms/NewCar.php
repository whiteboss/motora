<?php
/**
 * 
 * Форма добавления нового объявления авто
 * 
 */

class Auto_Form_NewCar extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName( 'new_item_form' );
        $this->setMethod( 'post' );
        
        $this->addElement('checkbox', 'is_new', array(
            'label'         => 'Eso es un automóvil nuevo',
            'value'         => '0',
            'filters'       => array('Int'),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_checkbox.phtml',
            )))
	));

        $table = new Auto_Model_Table_CarMarks();
        $this->addElement( 'select', 'mark', array(
            'label'         => 'Marca',
            'required'      => true,
            'filters'       => array( 'Int' ),
            'multiOptions'  => $table->getAll(),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $this->addElement( 'select', 'serie', array(
            'label'         => 'Serie',
            'required'      => true,
            'filters'       => array( 'Int' ),
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );
        
        // через модель идут почти все связки, поэтому обязательное
        $this->addElement( 'select', 'id_car_model', array(
            'label'         => 'Modelo',
            'required'      => true,
            'filters'       => array( 'Int' ),
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );
        
        $this->addElement( 'select', 'id_car_modification', array(
            'label'         => 'Versión',
            //'required'      => true,
            'filters'       => array( 'Int' ),
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $this->addElement( 'text', 'price', array(
            'label'         => 'Precio, pesos',
            'required'      => true,
            'description'   => 'Только цифры. Например: 300000',
            'filters'       => array( 'Digits' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '8'
            )))
        ) );

        $year = date("Y")+1;
        $years = array();
        for ($i=1980;$i<$year;$i++) {
            $years[$i] = $i;
        }

        $this->addElement( 'select', 'year', array(
            'label'         => 'Año',
            'required'      => true,
            'multiOptions'  => $years,
            'filters'       => array( 'Int' ),
            'value'         => '2001',
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $this->addElement( 'text', 'mileage', array(
            'label'         => 'Kilometraje',
            'required'      => true,
            'description'   => 'Только цифры. Например: 50200 или 0 (если без пробега)',
            'filters'       => array( 'Digits' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '6'
            )))
        ) );

        $this->addElement( 'text', 'engine_volume', array(
            'label'         => 'Cilindrada (c.c.)',
            'required'      => true,
            'description'   => 'Только цифры. Например: 2500',
            'filters'       => array( 'Digits' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '5'
            )))
        ) );
        
        $table = new Auto_Model_Table_CarFuelKinds();
        $this->addElement( 'select', 'fuel_kind', array(
            'label'         => 'Combustible',
            'multiOptions'  => $table->getAll(),
            'filters'       => array( 'Int' ),
            'value'         => 1,
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $table = new Auto_Model_Table_CarGearBoxes();
        $this->addElement( 'select', 'gearbox', array(
            'label'         => 'Transmisión',
            'multiOptions'  => $table->getGearBoxes(1),
            'filters'       => array( 'Int' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $table = new Auto_Model_Table_CarGearTypes();
        $this->addElement( 'select', 'gear_type', array(
            'label'         => 'Tipo de caja de cambios',
            'multiOptions'  => $table->getAll(),
            'filters'       => array( 'Int' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $this->addElement( 'hidden', 'photos', array(
            'label'     => 'Fotos',
            'filters'   => array('StringTrim', 'StripTags')
        ) );

        $this->addElement( 'textarea', 'description', array(
            'label'         => 'Descripción',
            'rows'          => 4,
            'filters'       => array('StringTrim', new Zend_Filter_StripTags(array('allowTags' => 'br'))),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_textarea.phtml',
                'maxlength'     => '1000'
            )))
        ) );

        $this->addElement( 'text', 'phone', array(
            'label'         => 'Teléfono',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'value'         =>  '',
            'description'   => 'Por ejemplo: +56 2 xxxxxxx, сelulares: +56 9 xxxxxxxx',
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '18'
            )))
        ) );
        $this->phone->addValidator( new Qlick_Validate_Phone() );
        
        $this->addElement( 'text', 'email', array(
            'label'         => 'E-mail',
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'validators'    => array( 'EmailAddress' ),
            'description'   => 'Например: mailbox@somesite.ru',
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '30'
            )))
        ) );

        $this->addElement( 'text', 'skype', array(
            'label'         => 'Skype',
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '20'
            )))
        ) );

//        $this->addElement( 'captcha', 'figli', array(
//                'label'         => 'Введите',
//                //'description'   => 'Если не хотите каждый раз вводить это, <a href="/auth/facebook">войдите</a>',
//                'captcha'       => array(
//                    'captcha' => 'ReCaptcha',
//                    'privKey' => '6Leu6ewSAAAAAKWKBfPGzbx2TtYEQ2Aw_EZ81oZh',
//                    'pubKey' => '6Ldu5ewSAAAAAOogtYgszgtXGMVhkf7ibGOxYZy6'
//                ),
//                'captchaOptions' => array(                    
//                    'theme' => 'clean',
//                    'lang'  => 'ru'                    
//                )
//        ) );
//        // https://www.google.com/recaptcha
//        // motora.cl
//        // privKey  6LejINgSAAAAAAYSGN93a0hmUxIxIjtXENsq3oYX
//        // pubKey   6Leu6ewSAAAAADCaOSnXUGqnJlitB8lsYWxDLe4h
//        // for local test motora.la
//        // privKey  6Leu6ewSAAAAAKWKBfPGzbx2TtYEQ2Aw_EZ81oZh
//        // pubKey   6Ldu5ewSAAAAAOogtYgszgtXGMVhkf7ibGOxYZy6

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