<?php

class Users_Form_RestoreForm extends Qlick_Form_NulledForm
{

    public function init()
    {

        $this->setName('restore_form');
	$this->setMethod('post');

        $this->addElement('text', 'email', array(
            'label'         => 'Ingresa Email de su cuenta',
            'required'      => TRUE,
            'value'         => '',
            'validators'    => array(
                array('EmailAddress'),
                'NotEmpty',
                array('StringLength', false, array(0, 50))
            ),
            'filters'       => array('StringTrim', 'StringToLower'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '50'
            )))
        ));

//        $this->addElement('text', 'username', array(
//            'label'         => 'Имя пользователя',
//            'required'      => TRUE,
//            'validators'    => array(
//                'NotEmpty',
//                array('StringLength', false, array(3, 15))
//            ),
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript'    => 'standart_input.phtml',
//                'maxlength'     => '15'
//            )))
//        ));

        $this->addElement('submit', 'signin', array(
            'label'         => 'Entrar',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_send_button COLLECTION'
            )))
        ));

    }


}

