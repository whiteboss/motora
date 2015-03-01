<?php
/**
 * 
 * Форма добавления компании
 * 
 */

class Companies_Form_NewCompany extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName( 'edit_company_form' );
        $this->setMethod( 'post' );

        $this->addElement( 'text', 'name', array(
            'label'         => 'Nombre',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '45'
            )))
        ) );
        $this->name->addValidator( new Zend_Validate_Db_NoRecordExists(
            array(
                'table' => 'companies_company',
                'field' => 'name'
            ))
        );

        $this->addElement( 'text', 'name_eng', array(
            'label'         => 'Nombre alternativo',
            'required'      => false,
            'description'   => 'Por ejemplo, el nombre en versión inglés',
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '40'
            )))
        ) );
        
        $this->addElement("text", "url",array(
            'label'         => 'URL',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '250'
            )))            
        ));
        
        
//        $this->addElement( 'select', 'activity_sphere', array(
//            'label'         => 'O escoja la esfera de actividad',
//            'filters'       => array( 'Int' ),
//            'decorators'        => array(array('ViewScript', array(
//                'viewScript'    => 'standart_select.phtml',
//            )))
//        ) );
//
//        $this->addElement( 'multiselect', 'activity_type', array(
//            'label'                     => 'Escoja el tipos de compañía',
//            'registerInArrayValidator'  => false,
//            'decorators'        => array(array('ViewScript', array(
//                'viewScript'    => 'chosen_nulledmultiselect.phtml'
//            )))
//        ) );

        $this->addElement( 'select', 'type', array(
            'label'         => 'Tipo de compañía',
            'multiOptions'  => Companies_Model_Company::$header_types,
            'decorators'        => array(array('ViewScript', array(
                'viewScript'    => 'standart_select.phtml'
            )))
        ) );

        $this->addElement( 'textarea', 'description', array(
            'label'         => 'Descripción corta',
            'rows'          => 4,
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_textarea.phtml',
                'maxlength'     => '450'
            )))
        ) );

        $this->addElement( 'text', 'address', array(
            'label'         => 'Dirección',
            'description'   => 'Por ejemplo: Irarrázaval 0001, Ñuñoa, Santiago de Chile',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '150'
            )))
        ) );
        
        $this->addElement('textarea', 'path', array(
            'label'         => 'Cómo llegar',
            'rows'          => 4,
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_textarea.phtml',
                'maxlength'     => '300'
            )))
        ));        

        $this->addElement( 'text', 'phone', array(
            'label'         => 'Teléfono general',
            'value'         => '',
            'required'      => false,
            'filters'       => array('StringTrim', 'StripTags'),
            'description'   => 'Por ejemplo: +56 2 xxxxxxx, сelulares: +56 9 xxxxxxxx',
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '18'
            )))
        ) );
        $this->phone->addValidator( new Qlick_Validate_Phone() );

//        $this->addElement( 'checkbox', 'representative', array(
//            'label'         => 'Sí, no me contra que los usuarios del sitio sepan que y o presento esta empresa',
//            'value'         => '1',
//            'filters'       => array('Int'),
//            'decorators'        => array(array('ViewScript', array(
//                'viewScript'    => 'standart_checkbox.phtml',
//            )))
//        ) );
//
//        $this->addElement( 'text', 'position', array(
//            'label'         => 'Su puesto en el perfil de empresa',
//            'filters'       => array( 'StringTrim', 'StripTags' ),
//            'value'         => 'Representante de Qlick.cl',
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'standart_input.phtml',
//                'maxlength'     => '50'
//            )))
//        ) );
//
//        $this->addElement( 'text', 'contact', array(
//            'label'         => 'Su numero de teléfono',
//            'required'      => true,
//            'filters'       => array('StringTrim', 'StripTags'),
//            'description'   => 'Lo será usado una vez por la administración del sítio para la actualización y no será concedido a otras personas',
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'standart_input.phtml',
//                'maxlength'     => '18'
//            )))
//        ) );
//        $this->contact->addValidator( new Qlick_Validate_Phone() );
//
        $this->addElement( 'checkbox', 'agree', array(
            'label'         => 'Estoy de acuerdo con las <a href="#agreement" role="button" data-toggle="modal">condiciones de usuario</a> y las <a href="#rules" role="button" data-toggle="modal">reglas del sítio</a>',
            'required'      => true,
            'value'         => '1',
            'filters'       => array('Int'),
            'decorators'        => array(array('ViewScript', array(
                'viewScript'    => 'standart_checkbox.phtml',
            )))
        ) );

        $this->addElement ('submit', 'add_item', array(
            'label'         => 'Listo',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));
        
    }
}