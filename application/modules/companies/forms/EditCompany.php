<?php

/**
 * 
 * Форма редактирования профиля компании (главное)
 * 
 */
class Companies_Form_EditCompany extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName('edit_company_form');
        $this->setMethod('post');


        $this->addElement('text', 'name', array(
            'label'         => 'Nombre',
            'required'      => true,
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '45'
            )))
        ));

        $this->addElement('text', 'name_eng', array(
            'label'         => 'Nombre alternativo',
            'required'      => false,
            'description'   => 'Por ejemplo, el nombre en variante ingles',
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '40'
            )))
        ));
        
        $this->addElement("text", "url",array(
            'label'         => 'URL',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '250'
            )))            
        ));
        
        $this->addElement( 'select', 'type', array(
            'label'         => 'Tipo de compañía',
            'multiOptions'  => Companies_Model_Company::$header_types,
            'decorators'        => array(array('ViewScript', array(
                'viewScript'    => 'standart_select.phtml'
            )))
        ) );        

        $this->addElement('textarea', 'description', array(
            'label'         => 'Descripción corta',
            'rows'          => 4,
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_textarea.phtml',
                'maxlength'     => '450'
            )))
        ));

        $this->addElement('hidden', 'photo', array(
            'filters'   => array('StringTrim', 'StripTags'),
            'label'     => 'Avatar'
        ));
        $this->addElement('hidden', 'crop_x', array('filters' => array( 'Int' )) );
        $this->addElement('hidden', 'crop_y', array('filters' => array( 'Int' )) );
        $this->addElement('hidden', 'w', array('filters' => array( 'Int' )) );
        $this->addElement('hidden', 'h', array('filters' => array( 'Int' )) );

        $this->addElement('hidden', 'photos', array(
            'filters'   => array('StringTrim', 'StripTags'),
            'label'     => 'Fotos'
        ));

        $this->addElement('text', 'address', array(
            'label'         => 'Dirección',
            'description'   => 'Por ejemplo: Irarrázaval 0001, Ñuñoa, Santiago de Chile',
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '150'
            )))
        ));
        
        $this->addElement('textarea', 'path', array(
            'label'         => 'Cómo llegar',
            'rows'          => 4,
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_textarea.phtml',
                'maxlength'     => '300'
            )))
        ));        

        $this->addElement('text', 'phone', array(
            'label'         => 'Teléfono general',
            'required'      => false,
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '18'
            )))
        ));
        $this->phone->addValidator(new Qlick_Validate_Phone());

        // Дополнительные телефоны
        $this->addElement('text', 'newphone_nubmer', array(
            'label'     => 'Teléfono adicionales',
            'value'     => '',
            'ignore'    => true,
            'filters'   => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                'viewScript'    => 'standart_short_input.phtml',
                'maxlength'     => '18'
            )))
        ));

        $this->addElement('text', 'newphone_name', array(
            'label'     => 'Persona de contacto',
            'ignore'    => true,
            'decorators' => array(array('ViewScript', array(
                'viewScript'    => 'standart_short_input.phtml',
                'maxlength'     => '30'
            )))
        ));

        $this->addElement('hidden', 'phone_add', array('decorators' => array('ViewHelper')));

        // E-mail адреса
        $this->addElement('text', 'newemail_email', array(
            'label'         => 'E-mails',
            'ignore'        => true,
            'filters'       => array('StringTrim', 'StripTags'),
            'validators'    => array('EmailAddress'),
            'description'   => 'Porejemplo: mailbox@somesite.cl',
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '50'
            )))
        ));

        $this->addElement('button', 'newemail_add', array(
            'label' => 'Добавить',
            'ignore' => true,
            'decorators' => array('ViewHelper', array('HtmlTag', array('closeOnly' => true)))
        ));

        $this->addElement('hidden', 'email', array('decorators' => array('ViewHelper')));
        
        $this->addElement('text', 'facebook', array(
            'label'         => 'Facebook',
            'filters'       => array('StringTrim', 'StripTags', 'StringToLower'),
            'description'   => 'Por ejemplo: https://www.facebook.com/pages/Qlickcl/265987913519758?ref=hl',
            'validators'    => array(new Qlick_Validate_Facebook()),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '250'
            )))
        ));   
        
        $this->addElement('text', 'tweeter', array(
            'label'         => 'Twitter',
            'filters'       => array('StringTrim', 'StripTags', 'StringToLower'),
            'description'   => 'Por ejemplo: https://www.facebook.com/pages/Qlickcl/265987913519758?ref=hl',
            'validators'    => array(new Qlick_Validate_Twitter()),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml',
                'maxlength'     => '250'
            )))
        ));        

        $this->addElement('text', 'site', array(
            'label'         => 'Sítio',
            'filters'       => array('StringTrim', 'StripTags', 'StringToLower'),
            'description'   => 'Por ejemplo: http://mysite.ru',
            'validators'    => array(new Qlick_Validate_Url()),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '250'
            )))
        ));
        // Поля, специфичные по типам компаний
        $type = $this->getAttrib('type');
        $this->removeAttrib('type');
        switch ($type) {
            case 'rest' :
            case 'bar' :
            case 'cafe' :
            case 'pizz' :
                $this->addElement('text', 'la_cuenta', array(
                    'label'         => 'Cheque promedio',
                    'description'   => 'Por ejemplo: 4500',
                    'filters'       => array('Digits'),
                    'decorators'    => array(array('ViewScript', array(
                        'viewScript'    => 'standart_input.phtml',
                        'maxlength'     => '5'
                    )))
                ));
            case 'shop' :                
                $this->addElement('textarea', 'regime', array(
                    'label'         => 'Horario de trabajo',
                    'description'   => 'Por ejemplo: dias laborables de 9:00 a 18:00, fin de semana de 10:00 a 15:00',
                    'filters'       => array('StringTrim', 'StripTags'),
                    'decorators'    => array(array('ViewScript', array(
                        'viewScript'    => 'standart_textarea.phtml',
                        'maxlength'     => '200'
                    )))
                ));
                $this->addElement('text', 'payment_types', array(
                    'label'         => 'Metodos de pago',
                    'description'   => 'Por ejemplo: efectivo, tarjeta de banco',
                    'filters'       => array('StringTrim', 'StripTags'),
                    'decorators'    => array(array('ViewScript', array(
                        'viewScript'    => 'standart_input.phtml',
                        'maxlength'     => '30'
                    )))
                ));
                $this->addElement('textarea', 'have', array(
                    'label'         => 'Que hay',
                    'rows'          => 3,
                    'filters'       => array('StringTrim', 'StripTags'),
                    'decorators'    => array(array('ViewScript', array(
                        'viewScript'    => 'standart_textarea.phtml',
                        'maxlength'     => '200'
                    )))
                ));
//                $this->addElement('checkbox', 'delivery_available', array(
//                    'label'         => 'Hay entrega',
//                    'value'         => 0,
//                    'filters'       => array('Int'),
//                    'decorators'    => array(array('ViewScript', array(
//                        'viewScript' => 'standart_checkbox.phtml',
//                    )))
//                ));
//
//                $this->addElement('text', 'delivery_phone', array(
//                    'label'         => 'Teléfono de entrega',
//                    'filters'       => array('StringTrim', 'StripTags'),
//                    'value'         => '+56 2 ',
//                    'description'   => 'Por ejemplo: +56 2 xxxxxxx, сelulares: +56 9 xxxxxxxx',
//                    'decorators'    => array(array('ViewScript', array(
//                        'viewScript'    => 'standart_input.phtml',
//                        'maxlength'     => '18'
//                    )))
//                ));
//                $this->delivery_phone->addValidator(new Qlick_Validate_Phone());

                break;
            
            case 'inn' :
                $this->addElement('textarea', 'have', array(
                    'label'         => 'Que hay',
                    'rows'          => 3,
                    'filters'       => array('StringTrim', 'StripTags'),
                    'decorators'    => array(array('ViewScript', array(
                        'viewScript'    => 'standart_textarea.phtml',
                        'maxlength'     => '300'
                    )))
                ));
                $this->addElement('text', 'checkout', array(
                    'label'         => 'Tiempo Check-out',
                    'filters'       => array('StringTrim', 'StripTags'),
                    'decorators'    => array(array('ViewScript', array(
                        'viewScript'    => 'standart_input.phtml',
                        'maxlength'     => '30'
                    )))
                ));
                
                break;
            
            default:
                $this->addElement('textarea', 'regime', array(
                    'label'         => 'Horario de trabajo',
                    'description'   => 'Por ejemplo: dias laborables de 9:00 a 18:00, fin de semana de 10:00 a 15:00',
                    'filters'       => array('StringTrim', 'StripTags'),
                    'decorators'    => array(array('ViewScript', array(
                        'viewScript'    => 'standart_textarea.phtml',
                        'maxlength'     => '200'
                    )))
                ));
                
                break;
            
        }
        
        if ($type == 'shop') {
            $this->addElement( 'select', 'shop_type', array(
                'label'         => 'Especialización',
                'required'      => true,
                'multiOptions'  => Companies_Model_Company::$shop_types,
                'filters'       => array( 'Int' ),
                'decorators'        => array(array('ViewScript', array(
                    'viewScript'    => 'standart_select.phtml',
                )))
            ) );
        }

        $this->addElement('submit', 'add_item', array(
            'label'         => 'Listo',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));
    }

    /**
     * Добавляет условные валидаторы
     * @var array $data данные POST
     * @return void
     */
    public function addConditionalValidators($data) {
//        if (!empty($data['delivery_available'])) {
////                    $this->delivery_from->setRequired(true);
////                    $this->delivery_to->setRequired(true);
//            $this->delivery_phone->setRequired(true);
//        }
    }

}