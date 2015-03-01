<?php
/**
 * 
 * Форма редактирования вакансии
 * 
 */

class Companies_Form_EditVacancy extends Qlick_Form_NulledForm {

	public function init() {

            $this->setName( 'edit_vacancy_form' );
            $this->setMethod( 'post' );

            $this->addElement( 'text', 'position', array(
                'label'         => 'Должность',
                'required'      => true,
                'filters'       => array( 'StringTrim', 'StripTags' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '70'
                )))
            ) );

            $this->addElement( 'select', 'industry', array(
                'label'         => 'Профессиональная область',
                'required'      => true,
                'multiOptions'  => array('...') + Companies_Model_Vacancy::getIndustries(),
                'filters'       => array('Int'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'textarea', 'description', array(
                'label'         => 'Обязанности',
                'rows'          => 4,
                'required'      => true,
                'filters'       => array('StringTrim', new Zend_Filter_StripTags(array('allowTags' => 'li'))),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                    'maxlength'     => '300'
                )))
            ) );

            $this->addElement( 'textarea', 'requirements', array(
                'label'         => 'Требования',
                'rows'          => 4,
                'required'      => true,
                'filters'       => array('StringTrim', new Zend_Filter_StripTags(array('allowTags' => 'li'))),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                    'maxlength'     => '300'
                )))
            ) );
            
            $table = new Application_Model_Table_Cities();
            $this->addElement( 'select', 'id_city', array(
                'label'             => 'Город',
                'filters'           => array( 'Int' ),
                'multiOptions'      => $table->getAll(),
                'value'             => '1',
                'registerInArrayValidator' => false,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'salary_from', array(
                'label'     => 'Заработная плата от',
                'required'  => false,
                'filters'   => array( 'Digits' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_input.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'salary_to', array(
                'label'    => 'до',
                'filters'  => array( 'Digits' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_input.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'select', 'schedule', array(
                'label'    => 'График работы',
                'multiOptions' => Companies_Model_Vacancy::getScheduleTypes(),
                'filters'       => array('Int'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'age_from', array(
                'label'    => 'Возраст от',
                'filters'  => array( 'Digits', array('Callback', array('callback'=>'substr', 'options'=>array(0,2)) ) ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_input.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'age_to', array(
                'label'    => 'до',
                'filters'  => array( 'Digits' ),
                'validators' => array( array('LessThan', false, 100) ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_input.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'radio', 'sex', array(
                'label'         => 'Пол',
                'multiOptions'  => array('Пол не важен', 'Мужчина', 'Женщина'),
                'value'         => 0,
                'filters'       => array('Int'),
                    'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_radio.phtml'
                )))                
            ) );

            $this->addElement( 'select', 'education', array(
                'label'         => 'Образование',
                'multiOptions'  => Companies_Model_Vacancy::getEducationTypes(),
                'value'         => 3, // высшее
                'filters'       => array('Int'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'phone', array(
                'label'    => 'Teléfono',
                'required' => true,
                'description' => 'Por ejemplo: +56 2 xxxxxxx Celulares: +56 9 xxxxxxxx',
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '18'
                )))
            ) );
            $this->phone->addValidator( new Qlick_Validate_Phone() );
            
            $this->addElement( 'text', 'email', array(
                'label'    => 'Электронная почта',
                'required' => true,
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'validators' => array( 'EmailAddress' ),
                'description' => 'Por ejemplo: mailbox@somesite.ru',
                'decorators' => array(array('ViewScript', array(
                    'viewScript' => 'standart_input.phtml',
                    'maxlength'      => '30'
                )))
            ) );

            $this->addElement( 'text', 'person', array(
                'label'    => 'Persona de contacto',
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '70'
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
	
	/**
	 * Добавляет условные валидаторы
	 * @var array $data данные POST
	 * @return void
	 */
	public function addConditionalValidators( $data ) {
            if ( !empty($data['phone']) ) $this->email->setRequired(false);
            //if ( !empty($data['email']) ) $this->phone->setRequired(false);
	}
}