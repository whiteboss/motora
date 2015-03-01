<?php

class Post_Form_CreatePost extends Qlick_Form_NulledForm
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

//        $this->addElement("select", "feed_id", array(
//            'label'                     => "Blog",
//            'required'                  => true,
//            'filters'                   => array( 'Int' ),
//            'validators'                => array( 'digits', array('greaterThan', false, array(0)) ),
//            'RegisterInArrayValidator'  => false,
//            'decorators'    => array(array('ViewScript', array(
//                'viewScript' => 'standart_select.phtml'
//             )))
//        ));
        
        $this->addElement("text", "gallery_id", array(
            'label'                     => "Fotoreportaje (id)",
            'required'                  => false,
            'filters'                   => array( 'Int' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml'
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
            //'validators'    => array(array('Db_NoRecordExists', false, array('table' => 'post_posts', 'field' => 'url'))),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '250'
            )))            
        ));
        
        
        $this->addElement("text", "description", array(
            'label' => "Descripción breve",
            'required' => false,
            'rows'  => 4,
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
        
        $this->addElement('select', 'type_photo_index', array(
            'label'         => 'Tipo de tamaño de badge',
            'required'      => false,
            'filters'       => array('StringTrim', 'StripTags'),
            'validators'    => array( array('InArray', false, array(array_keys(Post_Model_Post::$post_index_size))) ),
            'multiOptions'  => Post_Model_Post::$post_index_size,
            'value'         => 'post_size_big',
            'registerInArrayValidator' => false,
            'decorators'        => array(array('ViewScript', array(
                'viewScript'    => 'standart_select.phtml',
            )))
        ));
        
        $this->addElement("hidden", "photo_index",array(
            'required'  => true,
            'label'     => 'Бэйдж на индекс',
            'filters'   => array('StringTrim', 'StripTags'),
        ));
        $this->addElement("hidden", "photo_list",array(
            'required'  => true,
            'label'     => 'Изображение для бейджей',
            'filters'   => array('StringTrim', 'StripTags'),
        ));
        $this->addElement('hidden', 'crop_x', array('filters' => array( 'Int' )) );
        $this->addElement('hidden', 'crop_y', array('filters' => array( 'Int' )));
        $this->addElement('hidden', 'w', array('filters' => array( 'Int' )));
        $this->addElement('hidden', 'h', array('filters' => array( 'Int' )));        
         
        $this->addElement("hidden", "photos",array(
            'label'     => 'Фотографии для галереи',
            'filters'   => array('StringTrim', 'StripTags'),
        ));
        
        $this->addElement("text", "post_type", array(
            'required'  => false,
            'label' => "Tipo de publicación",
            'filters' => array( 'StringTrim', 'StripTags' ),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml'
            )))            
        ));
        
        $this->addElement("text", "author", array(
            'label' => "Autor de la fotografia",
            'filters' => array( 'StringTrim', 'StripTags' ),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml'
            )))            
        ));        
         
        $this->addElement("textarea", "post", array(
            'label'     => 'Editar material',
            'required'  => true,
            'filters'   => array( 'StringTrim' ), //, new Zend_Filter_StripTags(array('allowTags' => array('li', 'b', 'i', 'strong', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'div' => array('style', 'class'), 'span' => array('style'), 'p' => array('style'), 'img' => array('src', 'title', 'width', 'height'), 'video' => array('src', 'class', 'id', 'width', 'height'), 'a' => array('href', 'title'), 'br', 'em', 'table', 'tbody', 'tr', 'td'))) ),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'imperavi_textarea.phtml',
             )))
        ));
        
        $this->addElement("file", "video", array(
            'label' => 'Video *.mp4 (max: ' . round(ini_get('upload_max_filesize'), 2) . ' MB)',
            'destionation'  => realpath(dirname('.')).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array('File', array('ViewScript', array(
                'viewScript'    => 'standart_file.phtml',
                'placement'     => false
            ))
            )
        ));
        $this->video->addValidator('Extension', false, 'mp4');
        //$this->video->addValidator('Size', false, ini_get('upload_max_filesize'));
        
        $this->addElement("file", "video_webm", array(
            'label' => 'Video *.webm (max: ' . round(ini_get('upload_max_filesize'), 2) . ' MB)',
            'destionation'  => realpath(dirname('.')).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array('File', array('ViewScript', array(
                'viewScript'    => 'standart_file.phtml',
                'placement'     => false
            ))
            )
        ));
        $this->video_webm->addValidator('Extension', false, 'webm');
        
        $this->addElement("text", "tags", array(
            'label' => 'Tags',
            'filters' => array( 'StringTrim', 'StripTags' ),
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_input.phtml',
                'maxlength'      => '100'
             )))
        ));
        
        $this->addElement("select", "status", array(
            'label'         => 'Estado de la publicación',
            'required'      => true,
            'filters'       => array('Int'),
            'multiOptions'  => Post_Model_Post::$post_status,
            'registerInArrayValidator' => false,
            'decorators'        => array(array('ViewScript', array(
                'viewScript'    => 'standart_select.phtml',
            )))
        ));
        
//        $this->addElement("button","toDraft",array(
//        //$this->addElement("submit","toDraft",array(
//            'label'     => 'Draft',
//            "ignore"    =>  true,
//            "label"     =>  "Llevar al borrador",
//            'decorators' => array(array('ViewScript', array(
//                'viewScript' => 'standart_button.phtml',
//                'class'      => 'standard_publish_button COLLECTION'
//             )))
//        ));
        
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

