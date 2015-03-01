<?php
/**
 * 
 * Форма редактирования резюме
 * 
 */

class Companies_Form_EditResume extends Qlick_Form_NulledForm {

	public function init() {

            $this->setName( 'edit_resume_form' );
            $this->setMethod( 'post' );
            
            $this->addElement( 'text', 'surname', array(
                'label'    => 'Фамилия',
                'required' => true,
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '40'
                )))
            ) );

            $this->addElement( 'text', 'name', array(
                'label'    => 'Имя',
                'required' => true,
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '40'
                )))
            ) );

            $this->addElement( 'text', 'patronymic', array(
                'label'    => 'Отчество',
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '40'
                )))
            ) );

            $this->addElement( 'text', 'birthdate', array(
                'label'    => 'Дата рождения',
                'required' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_date.phtml',
                    'maxlength'     => '40'
                )))
            ) );

            $this->addElement( 'radio', 'sex', array(
                'label'    => 'Пол*',
		'multiOptions' => array(1=>'Мужчина', 2=>'Женщина'),
                'filters'       => array('Int'),
                    'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_radio_w_errors.phtml'
                )))
            ) );

            $this->addElement( 'text', 'position', array(
                'label'    => 'Желаемая должность',
                'required' => true,
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '70'
                )))
            ) );

            $this->addElement( 'select', 'industry', array(
                'label'    => 'Профессиональная область',
                'required' => true,
                'multiOptions' => Companies_Model_Resume::getIndustries(),
                'filters'       => array('Int'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );
            
            $table = new Application_Model_Table_Cities();
            $this->addElement( 'select', 'id_city', array(
                'label'    => 'Город',
                'required' => true,
                'multiOptions' => $table->getAll(),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'salary_from', array(
                'label'    => 'Заработная плата от',
                'filters'  => array( 'Digits' ),
                'required' => true,
                'description'   => 'Solo en cifras, por favor. Por ejemplo: 30000',
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '6'
                )))
            ) );

            $this->addElement( 'select', 'schedule', array(
                'label'    => 'График работы',
                'multiOptions' => Companies_Model_Resume::getScheduleTypes(),
                'filters'       => array('Int'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'textarea', 'skills', array(
                'label'    => 'Навыки и знания',
                'rows'    => 4,
                'filters'       => array('StringTrim', 'StripTags'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                    'maxlength'     => '300'
                )))
            ) );

            $this->addElement( 'select', 'experience', array(
                'label'    => 'Опыт работы в данной области',
                'multiOptions' => Companies_Model_Resume::getExperienceLevels(),
                'filters'       => array('Int'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            // Профессиональный опыт
            $years = range( intval(date("Y")), 1980 );
            $years = array_combine( $years, $years );
            $this->addElement( 'select', 'newwork_start', array(
                'label'    => 'Период работы с',
                'filters'       => array('Int'),
                'multiOptions' => $years,
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'select', 'newwork_end', array(
                'label'    => 'до',
                'multiOptions' => array('настоящее время') + $years,
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'newwork_company', array(
                'label'    => 'Компания',
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '50'
                )))
            ) );

            $this->addElement( 'text', 'newwork_position', array(
                'label'    => 'Должность',
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '30'
                )))
            ) );

            $this->addElement( 'textarea', 'newwork_responsibility', array(
                'label'    => 'Обязанности',
                'rows'    => 4,
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                    'maxlength'     => '200'
                )))
            ) );

            $this->addElement( 'button', 'newwork_add', array(
                    'label'  => 'Добавить',
                    'ignore' => true
            ) );

            $this->addElement( 'hidden', 'work', array('decorators' => array('ViewHelper')) );
//            $this->addDisplayGroup(
//                    array('work', 'newwork_start', 'newwork_end', 'newwork_company', 'newwork_position', 'newwork_responsibility', 'newwork_add'),
//                    'newwork',
//                    array(
//                            'legend'  => 'Профессиональный опыт'
//                    )
//            );

            $this->addElement( 'select', 'education', array(
                'label'    => 'Образование',
                'multiOptions' => Companies_Model_Resume::getEducationTypes(),
                'value'    => 3, // высшее
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            // Образование
            $this->addElement( 'select', 'newinstitute_start', array(
                'label'    => 'Период обучения с',
                'filters'       => array('Int'),
                'multiOptions' => $years,
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'select', 'newinstitute_end', array(
                'label'    => 'до',
                'multiOptions' => array('настоящее время') + $years,
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_short_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'text', 'newinstitute_name', array(
                'label'    => 'Учебное заведение',
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '50'
                )))
            ) );

            $this->addElement( 'text', 'newinstitute_speciality', array(
                'label'    => 'Специальность',
                'filters'  => array( 'StringTrim', 'StripTags' ),
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_input.phtml',
                    'maxlength'     => '50'
                )))
            ) );

            $this->addElement( 'button', 'newinstitute_add', array(
                    'label'  => 'Добавить',
                    'ignore' => true
            ) );

            $this->addElement( 'hidden', 'institute', array('decorators' => array('ViewHelper')) );
//            $this->addDisplayGroup(
//                    array('institute', 'newinstitute_start', 'newinstitute_end', 'newinstitute_name', 'newinstitute_speciality', 'newinstitute_add'),
//                    'newinstitute',
//                    array(
//                            'legend'  => 'Образование'
//                    )
//            );

            $this->addElement( 'textarea', 'certificates', array(
                'label'    => 'Сертификаты и курсы',
                'rows'    => 4,
                'filters'       => array('StringTrim', 'StripTags'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                    'maxlength'     => '300'
                )))
            ) );

            $this->addElement( 'select', 'newlang_language', array(
                'label'    => 'Знание языков',
                'multiOptions' => Companies_Model_Resume::getLangs(),
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'select', 'newlang_level', array(
                'label'    => 'Уровень',
                'multiOptions' => Companies_Model_Resume::getLanguageLevels(),
                'ignore' => true,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_select.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'button', 'newlang_add', array(
                    'label'  => 'Добавить',
                    'ignore' => true
            ) );

            $this->addElement( 'hidden', 'languages', array('decorators' => array('ViewHelper')) );
//            $this->addDisplayGroup(
//                    array('languages', 'newlang_language', 'newlang_level', 'newlang_add'),
//                    'newlang',
//                    array(
//                            'legend'  => 'Владение языками'
//                    )
//            );

            $this->addElement( 'multiselect', 'driving', array(
                'label'    => 'Водительские права',
                'multiOptions' => Companies_Model_Resume::getDrivingPermits(),
                'value' => array('0'),
                //'ignore' => true,
                'description'   => 'Удерживайте CTRL для выбора нескольких вариантов',
                'decorators'    => array(array('ViewScript', array(
                    'viewScript' => 'standart_multiselect.phtml',
                    'class'      => 'form element'
                )))
            ) );

            $this->addElement( 'textarea', 'hobbies', array(
                'label'    => 'Особые навыки, хобби',
                'rows'    => 4,
                'filters'       => array('StringTrim', 'StripTags'),
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                    'maxlength'     => '300'
                )))
            ) );

            $this->addElement( 'text', 'phone', array(
                'label'    => 'Contacto de teléfono',
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