<?php

class Post_Form_UserCreatePost extends Qlick_Form_NulledForm
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        
        $this->setName("postCreate");
        $this->setMethod("post");        
        
        $rubricManager = new Feed_Model_Table_Rubric();
        $this->addElement("select", 'rubric_id', array(
            'label'                     => "Elección de rúbrica",
            'required'                  => true,   
            'filters'                   => array( 'Int' ),
            'validators'                => array( 'digits', array('greaterThan', false, array(0)) ),
            'multiOptions'              => array('0' => '- Elección de rúbrica -') + $rubricManager->getAll(),
            'RegisterInArrayValidator'  => false,
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
            )))
        ));        
        
        //$table = new Companies_Model_Table_Companies();
        $this->addElement("select", "company_id", array(
            'label'                     => "Empresa relacionadas",
            'required'                  => false,
            //'multiOptions'              => array('0' => '- Elección de empresa -') + $table->getCompanies(),
            'filters'                   => array( 'Int' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'chosen_select.phtml'
             )))
        ));        
        
        $this->addElement("text", "name",array(
            'label'         => 'Título',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '150'
            )))            
        ));
        
        $this->addElement("text", "url",array(
            'label'         => 'URL',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'readonly'      => true,
                'maxlength'     => '250'
            )))            
        ));        
        
        $this->addElement("text", "description", array(
            'label'     => "Descripción breve",
            'required'  => true,
            'rows'      => 4,
            'filters' => array( 'StringTrim', 'StripTags' ),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_textarea.phtml',
                'maxlength'      => '300'
            )))            
        ));
        
        $this->addElement("hidden", "photo",array(
            'required'  => true,
            'label'     => 'Основное изображение',
            'filters'   => array('StringTrim', 'StripTags'),
        )); 
         
        $this->addElement("textarea", "post", array(
            'label'     => 'Editar material',
            'required'  => true,
            'filters'   => array( 'StringTrim' ), //, new Zend_Filter_StripTags(array('allowTags' => array('li', 'b', 'i', 'strong', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'div' => array('style', 'class'), 'span' => array('style'), 'p' => array('style'), 'img' => array('src', 'title', 'width', 'height'), 'video' => array('src', 'class', 'id', 'width', 'height'), 'a' => array('href', 'title'), 'br', 'em', 'table', 'tbody', 'tr', 'td'))) ),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'imperavi_textarea.phtml',
             )))
        ));
        
        $this->addElement("text", "tags", array(
            'label'     => 'Tags',
            'required'  => false,
            'filters'   => array( 'StringTrim', 'StripTags' ),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml',
                'maxlength'      => '100'
             )))
        ));
        
        $this->addElement ('submit', 'add_item', array(
            'label'   => 'Listo',
            'ignore'  => true,
            'decorators' => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_publish_button COLLECTION'
            )))
        ));
    }


}

