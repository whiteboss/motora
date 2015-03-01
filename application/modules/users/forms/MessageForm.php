<?php

class Users_Form_MessageForm extends Qlick_Form_NulledForm {

    public function init()
    {
        $auth = Zend_Auth::getInstance()->GetIdentity();
        
        $this->setName('new_message_form');
	$this->setMethod('post');
        $this->setAction('/messages/send');

        $this->addElement('text', 'userTo', array(
            'label'         => 'Destino',
            'filters'       => array( 'StringTrim', 'StripTags' ),            
            'description'   => 'Empieza ingresar nombre de su amigo, a quien quiere enviar un mensaje',
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_nulledinput.phtml'

            )))
        ));

        $this->addElement('hidden', 'id_user_to', array(
            'required'      => true,
            'value'         => '0',
            'filters'       => array('Int'),
        ));

        $this->addElement('textarea', 'message', array(
            'rows'          => 5,
            'required'      => true,
            'description'   => 'Escribe tu mensaje aquí...',
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_textarea.phtml',
                'maxlength'      => '1000'
            )))
	));

        $this->addElement('submit', 'sendMessage', array(
            'label'         => 'Enviar',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'disabled'      => true,
                'button_class'  => 'standard_send_button'
            )))
        ));

    }
}