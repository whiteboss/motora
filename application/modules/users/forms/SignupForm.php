<?php

class Users_Form_SignupForm extends Qlick_Form_NulledForm
{

    public function init()
    {

        $this->setName('signup_form');
	$this->setMethod('post');
        $this->setAction('/signup');

        $email = $this->createElement('text', 'email', array(
            'label'         => 'E-mail',
            'required'      => TRUE,
            'validators'    => array(
                array('EmailAddress'),
                'NotEmpty',
                array('stringLength', array('max'=>30))
            ),
            'filters'       => array('StringTrim', 'StringToLower', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_input.phtml',
                'maxlength'     => '30'
            )))
        ));
        $email->addValidator('Db_NoRecordExists', false, array(
            'table' => 'users',
            'field' => 'email'
        ));

        $username = $this->createElement('text', 'username', array(
            'label'         => 'Nombre de usuario',
            'required'      => TRUE,
            'validators'    => array(
                'NotEmpty',
                array('StringLength', false, array(3, 15))
            ),
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_input.phtml',
                'maxlength'     => '15'
            )))
        ));
        $username->addValidator('Db_NoRecordExists', false, array(
            'table' => 'users',
            'field' => 'username'
        ));
        $username->addValidator('regex', true, array('/^[a-zA-Z]++(?: [a-zA-Z]++)?$/i'));
        $username->getValidator('regex')->setMessage("Por vafor, ingresa su nombre correcto.");

        for ($i=1;$i<=31;$i++) {
            $days[$i] = $i;
        }

        $day_birth = $this->createElement('select', 'day_birth', array(
            'label'         => 'Día',
            'required'      => TRUE,
            'filters'       => array('Int'),            
            'multiOptions'  => $days,
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_select.phtml',
            )))
        ));

        $qd = new Qlick_Date();
        $month_birth = $this->createElement('select', 'month_birth', array(
            'label'         => 'Mes',
            'required'      => TRUE,
            'filters'       => array('Int'),            
            'multiOptions'  => $qd->MonthsForForms(),
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_select.phtml',
            )))
        ));
        
        // Малолеткам тут делать нехуй
        $year = date("Y");
        $years = array('0' => 'Secreto');
        for ($i=1940; $i<=$year-14; $i++) {
            $years[$i] = $i;
        }
        
        $year_birth = $this->createElement('select', 'year_birth', array(
            'label'         => 'Año',
            'required'      => false,
            'multiOptions'  => $years,
            'filters'       => array('Int'),
            'value'         => 1983,
            //'validators'    => array('NotEmpty'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_select.phtml',
            )))
        ));

        $sex = $this->createElement('select', 'sex', array(
            'label'         => 'Género',
            'required'      => TRUE,
            'filters'       => array('Int'),
            'multiOptions'  => array('1'=>'Masculino', '2'=>'Femenino'),
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_select.phtml',
            )))
        ));

        $table = new Application_Model_Table_Cities();
        $city_id = $this->createElement('select', 'city_id', array(
            'label'         => 'Ciudad',
            'required'      => TRUE,
            'value'         => 1,
            'filters'       => array('Int'),
            'multiOptions'  => $table->getAll() + array('0'=>'Otra ciudad'),
            'registerInArrayValidator' => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_select.phtml',
            )))
        ));

        $city = $this->createElement('text', 'city_other', array(
            'required'      => FALSE,
            'validators'    => array(
                array('StringLength', false, array(0, 30))
            ),
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_input.phtml',
                'maxlength'     => '30'
            )))
        ));

        $firstname = $this->createElement('text', 'firstname', array(
            'label'         => 'Nombre',
            'required'      => TRUE,
            'validators'    => array(
                'NotEmpty',
                array('StringLength', false, array(2, 50))
            ),
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_input.phtml',
                'maxlength'     => '20'
            )))
        ));
        $firstname->addValidator('regex', true, array('/^[a-z]++(?: [a-z]++)?$/iu'));
        $firstname->getValidator('regex')->setMessage("Por vafor, ingresa su nombre correcto.");

        $lastname = $this->createElement('text', 'lastname', array(
            'label'         => 'Apellido',
            'required'      => TRUE,
            'validators'    => array(
                'NotEmpty',
                array('StringLength', false, array(2, 50))
            ),
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_input.phtml',
                'maxlength'     => '30'
            )))
        ));
        $lastname->addValidator('regex', true, array('/^[a-z]++(?: [a-z]++)?$/iu'));
        $lastname->getValidator('regex')->setMessage("Por vafor, ingresa su apellido correcto.");

        $password = $this->createElement('password', 'password', array(
            'label'         => 'Contraseña',
            'required'      => TRUE,
            //'value'         => '111',
            'validators'    => array(
                'NotEmpty',
                array('StringLength', false, array(0, 30))
            ),
            'filters'       => array('StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_pass.phtml',
                'maxlength'   => '30'
            )))
        ));

        $confirmPassword = $this->createElement('password', 'confirmPassword', array(
            'label'         => 'Confirmar contraseña',
            'required'      => TRUE,
            //'value'         => '111',
            'validators'    => array(
                'NotEmpty',
                array('StringLength', false, array(0, 30))
            ),
            'filters'       => array('StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_pass.phtml',
                'maxlength'     => '30'
            )))
        ));

        $register = $this->createElement('submit', 'register', array(
            'label'         => 'Regístrate',
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'reg_submit.phtml',
                'formname'      => $this->getName()
            )))
        ));

        $this->addElements(array(
                    $email,
                    $username,
                    $sex,
                    $city_id,
                    $day_birth,
                    $month_birth,
                    $year_birth,
                    $city,
                    $firstname,
                    $lastname,
                    $password,
                    $confirmPassword,
                    $register
        ));
    }


}

