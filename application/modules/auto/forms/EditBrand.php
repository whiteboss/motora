<?php
/**
 * 
 * Форма редактирования и добавления бренда
 * 
 */

class Auto_Form_EditBrand extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName( 'edit_brand_form' );
        $this->setMethod( 'post' );

        $this->addElement( 'text', 'name', array(
            'label'    => 'Название',
            'required' => true,
            'filters'  => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '40'
            )))
        ) );

        $this->addElement( 'file', 'logo_file', array(
            'label'    => 'Логотип',
            'required' => true,
            'decorators'    => array("File",array('ViewScript', array(
                 'viewScript'   => 'standart_file.phtml',
                 'placement'    => false
            )))
        ) );
        $this->logo_file->addValidator('Extension', false, 'png');
        $this->addElement( 'hidden', 'logo', array('class'=>'imagify') );

        $this->addElement( 'textarea', 'links', array(
            'label'    => 'Ссылки на сайты',
            'rows'    => 4,
            'filters'       => array('StringTrim', 'StripTags'),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_textarea.phtml',
                'maxlength'     => '200'
            )))
        ) );
        
        $this->addElement ('submit', 'add_item', array(
            'label'   => 'Опубликовать',
            'ignore'  => true,
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));

    }

}