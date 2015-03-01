<?php

class Companies_Form_VacancySearchForm extends Qlick_Form_NulledForm
{

    public function init()
    {

        $this->setName('vacancy_search_form');
	$this->setMethod('post');

        $this->addElement( 'text', 'jobname', array(
            'label'         => 'Наименование',
            'required'      => true,
            'filters'       => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '60',
                'class'         => 'clsf'
            )))
        ));

        $this->addElement ('submit', 'search_item', array(
            'label'         => 'Поиск',
            'ignore'        => true,
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));

    }


}

