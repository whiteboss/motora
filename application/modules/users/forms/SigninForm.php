<?php

class Users_Form_SigninForm extends Qlick_Form_NulledForm
{

    public function init()
    {

        $this->setName('signin_form');
	$this->setMethod('post');
        $this->setAction('/signin');

        $this->addElement('text', 'email', array(
            'label'         => 'E-mail',
            'required'      => TRUE,
            'value'         => '',
            'validators'    => array(
                array('EmailAddress'),
                'NotEmpty',
                array('StringLength', false, array(0, 50))
            ),
            'filters'       => array('StringTrim', 'StringToLower', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '50'
            )))
        ));

        $this->addElement('password', 'password', array(
            'label'         => 'Contraseña',
            'required'      => true,
            'value'         => '',
            'validators'    => array(
                array('StringLength', false, array(0, 30)),
            ),
            'filters'       => array('StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_pass.phtml',
                'maxlength'   => '30'
            )))
        ));
        
//        $this->addElement( 'captcha', 'figli', array(
//                'label'         => 'Введите',
//                'description'   => 'Вы ввели несколько раз подряд неверные данные, <a href="/users/restore">забыли пароль</a>?',
//                'captcha'       => array(
//                    'captcha' => 'ReCaptcha',
//                    'theme' => 'clean',
//                    'lang'  => 'ru',
//                    'privKey' => '6LdB1cISAAAAALnzKZnYsVeEfUn5NOUOkMmZbFW8',
//                    'pubKey' => '6LdB1cISAAAAAJ5oAlQLx7XMpNM-YB7lKWHG2bRz'
//                )
//        ) );        

        $this->addElement('submit', 'signin', array(
            'label'         => 'Iniciar sesión',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_enter_button COLLECTION'
            )))
        ));

    }


}

