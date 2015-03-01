<?php

class Users_Form_EditProfile extends Qlick_Form_NulledForm
{

    protected $rubricId;

//    public function __construct(array $params = array())
//    {
//        $this->rubricId = $params['rubricId'];
//        parent::__construct();
//    }

    public function init()
    {

        $this->setName("profileEdit");
        $this->setAttrib("id", "profileEdit");
        $this->setMethod("post");
        
        $this->setAction("/options/edit/");
        /* Form Elements & Other Definitions Here ... */
                
        $this->addElement("text", "username", array(
            'label' => 'Usuario',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'disabled' => true,
                        'input_class' => ' fgray'
            )))
        ));
        
        $this->addElement("text", "firstname", array(
            'label' => 'Nombre',
            'required' => true,
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '20',
            )))
        ));

        $this->addElement("text", "lastname", array(
            'label' => 'Apellidos',
            'required' => true,
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '30'
            )))
        ));
        
        $this->addElement('hidden', 'avatar',array(
            'label'     => 'Avatar',
            'filters'   => array('StringTrim', 'StripTags')
        ));
        $this->addElement('hidden', 'crop_x', array('filters' => array( 'Int' )) );
        $this->addElement('hidden', 'crop_y', array('filters' => array( 'Int' )) );
        $this->addElement('hidden', 'w', array('filters' => array( 'Int' )) );
        $this->addElement('hidden', 'h', array('filters' => array( 'Int' )) );

        $this->addElement("text", "date_of_birth", array(
            'label' => 'Cumpleaños',
            'class' => 'date-pick',
        ));

        $this->addElement("select", "sex", array(
            'label' => 'Género',
            'required' => true,
            'filters'       => array('Int'),
            'multiOptions' => array("1" => "Masculino", "2" => "Femenino"),
            'decorators' => array(array('ViewScript', array(
                    'viewScript' => 'prof_select.phtml',
            )))
        ));

        $table = new Application_Model_Table_Cities();
        $this->addElement("select", "city_id", array(
            'label' => 'Ciudad',
            'required' => true,
            'filters'       => array('Int'),
            'multiOptions' => $table->getAll() + array('0'=>'Otra ciudad'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_select.phtml',
            )))
        ));

        $this->addElement('text', 'city_other', array(
            'label'         => 'Otra ciudad',
            'required'      => FALSE,
            'validators'    => array(
                array('StringLength', false, array(0, 50))
            ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'prof_input.phtml',
                'maxlength'     => '50'
            )))
        ));

        $this->addElement("text", "email", array(
            'label' => 'E-mail',
            'required' => true,
            'validators'    => array(
                array('EmailAddress'),
                'NotEmpty',
                array('StringLength', false, array(0, 30))
            ),
            'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'prof_input.phtml',
                    'disabled'      => true,
                    'input_class'   => ' fgray' 
            )))
        ));

        $this->addElement("text", "www", array(
            'label' => 'Sítio',
            'required' => false,
            'validators' => array( new Qlick_Validate_Url() ),
            //'description'   => 'Por ejemplo: http://mysite.cl',
            'filters'  => array( 'StringTrim', 'StripTags' ),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '30',
                        'header_class'  => ' prof_st2'
            )))
        ));
        
        $this->addElement("text", "googleplus", array(
            'label' => 'Google Plus',
            'required' => false,
            'validators' => array( new Qlick_Validate_Url() ),
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '80',
                        'header_class'  => ' prof_gp2'
            )))
        ));        

        $this->addElement("text", "facebook", array(
            'label' => 'Facebook',
            'required' => false,
            'validators' => array( new Qlick_Validate_Facebook() ),
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '80',
                        'header_class'  => ' prof_vk2'
            )))
        ));


        $this->addElement("text", "livejournal", array(
            'label' => 'Live Journal',
            'required' => false,
            'validators' => array( new Qlick_Validate_Url() ),
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '80',
                        'header_class'  => ' prof_lj2'
            )))
        ));

        $this->addElement("text", "twitter", array(
            'label' => 'Twitter',
            'required' => false,
            'validators' => array( new Qlick_Validate_Twitter() ),
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '80',
                        'header_class'  => ' prof_tw2'
            )))
        ));

        $this->addElement("text", "skype", array(
            'label' => 'Skype',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '30',
                        'header_class'  => ' prof_sk2'
            )))
        ));

        $this->addElement("text", "icq", array(
            'label'         => 'ICQ',
            'required'      => false,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'validators'    => array( 'Digits' ),
            'decorators'    => array(array('ViewScript', array(
                        'viewScript' => 'prof_input.phtml',
                        'maxlength' => '10',
                        'header_class'  => ' prof_icq'
            )))
        ));

        $this->addElement("password", "password_old", array(
            'label' => 'Contraseña anterior',
            'required' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'prof_pass.phtml',
                'maxlength'     => '30'
            )))
        ));
        $this->addElement("password", "password_new", array(
            'label' => 'Nueva contraseña',
            'required' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'prof_pass.phtml',
                'maxlength'     => '30'
            )))
        ));
        $this->addElement("password", "password_new_repeat", array(
            'label' => 'Confirmar contraseña',
            'required' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'prof_pass.phtml',
                'maxlength'     => '30'
            )))
        ));
        $this->getElement('password_new_repeat')->addValidator('Identical', false, array('token' => 'password_new'));
        $this->getElement('password_new_repeat')->addErrorMessage('Contraseñas no coinciden');
              
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

