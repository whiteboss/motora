<?php
/**
 * 
 * Вакансия
 * 
 */

class Companies_Model_Vacancy extends Companies_Model_Abstract {
	
        protected $_city;
        protected $_company;
	/**
	 * Виды образования (список)
	 * @var array
	 */
	protected static $_education_types = array(
		'Среднее образование', 'Среднее профессиональное образование', 'Неполное высшее образование', 'Высшее образование', 'Ученая степень'
	);
	
	/**
	 * Графики работы (список)
	 * @var array
	 */
	protected static $_schedule_types = array(
		'Полный рабочий день',
		'Свободный график',
		'Сменный график',
		'Частичная занятость',
		'Удаленная работа',
		'Вахтовый метод'
	);
	
	/**
	 * Профессиональные области (список)
	 * @var array
	 */
	public static $_industries = array(
		'Административный персонал',
		'Безопасность и охрана',
		'Бухгалтерия и экономика',
		'Государственная служба',
		'Информационные технологии',
		'Логистика и снабжение',
		'Медицина  и фармацевтика',
		'Нефтегазовая отрасль',
		'Образование, наука и культура',
		'Производство',
		'Работа для студентов',
		'Рестораны, бары и кафе',
		'Руководство',
		'Рыбная отрасль и судоходство',
		'СМИ, реклама и дизайн',
		'Спорт,  красота и здоровье',
		'Строительство и недвижимость',
		'Телекоммуникации и связь',
		'Торговля',
		'Транспорт и Автосервис',
		'Развлечения, гостиницы и туризм',
		'Сфера услуг',
		'Юриспруденция'
	);
	
	/**
	 * @return string
	 */
	public function __toString() {
		return (string) $this->position;
	}
	
	/**
	 * Возвращает список
	 * @return array
	 */
	public static function getEducationTypes() {
		return self::$_education_types;	
	}
	
	/**
	 * Возвращает список
	 * @return array
	 */
	public static function getScheduleTypes() {
		return self::$_schedule_types;	
	}
	
	/**
	 * Возвращает список
	 * @return array
	 */
	public static function getIndustries() {
		return self::$_industries;	
	}
	
	/**
	 * @return string
	*/
	public function getPosition() {
		return $this->position;
	}
	
	/**
	 * Возвращает профессиональную область
	 * @return string
	*/
	public function getIndustry() {
		if ( !is_null($this->industry) and isset( self::$_industries[ $this->industry ] ) )
			return self::$_industries[ $this->industry ];
		else
			return '';
	}
        
	/**
	 * @return string
	*/
	public function getCompany() {
            if ( !empty($this->_company) ) return $this->_company;
            $this->_company = $this->findParentRow( 'Companies_Model_Table_Companies', null, null, array('id', 'name', 'photo') );
            return $this->_company;
	}        
	
	/**
	 * @return string
	*/
	public function getCity() {
            if ( !empty($this->_city) ) return $this->_city;
            $this->_city = $this->findParentRow( 'Application_Model_Table_Cities' );
            return $this->_city;
	}
	
	/**
	 * Возвращает график работ
	 * @return string
	*/
	public function getSchedule() {
		if ( !is_null($this->schedule) and isset( self::$_schedule_types[ $this->schedule ] ) )
			return self::$_schedule_types[ $this->schedule ];
		else
			return '';
	}
	
	/**
	 * Возвращает пол
	 * @return string
	*/
	public function getSex() {
		switch ($this->sex) {
			case 1:  return 'Мужчина';
			case 2:  return 'Женщина';
			default: return 'Пол не важен';
		}
	}
	
	/**
	 * Возвращает образование
	 * @return string
	*/
	public function getEducation() {
		if ( !is_null($this->education) and isset( self::$_education_types[ $this->education ] ) )
			return self::$_education_types[ $this->education ];
		else
			return '';
	}
	
	/**
	 * @return string
	*/
	public function getPhone() {
		return $this->phone;
	}
	
	/**
	 * @return string
	*/
	public function getEmail() {
		return $this->email;
	}
	
	/**
	 * @return string
	*/
	public function getPerson() {
		return $this->person;
	}

        public function getSalaryFrom() {
                return number_format($this->salary_from, 0, ',', ',');
        }

        public function getSalaryTo() {
                return number_format($this->salary_to, 0, ',', ',');
        }
        
        public function getSalary() {
            $salary = number_format($this->salary_from, 0, ',', ' ');
            if ($this->salary_to > 0) $salary.= ' до ' . number_format($this->salary_to, 0, ',', ' ');
            return $salary;
        }

        public function getAgeTo() {
                $qn = new Qlick_Num();
                return $this->age_to." ".$qn->num2word($this->age_to, array("года", "года", "лет"));
        }

        /**
	 * Устанавливает новые данные из массива, если они изменились
	 * @param  array $data
	 * @return $this
	 */
	public function setFromArray(array $data) {
		$data = array_intersect_key($data, $this->_data);
		if ( isset($data['phone']) and empty($data['phone']) ) {
			$data['phone'] = null;
		}
		if ( isset($data['email']) and empty($data['email']) ) {
			$data['email'] = null;
		}
		if ( isset($data['person']) and empty($data['person']) ) {
			$data['person'] = null;
		}
		foreach ($data as $columnName => $value) {
			if ( $this->_data[$columnName] != $value ) {
				$this->__set($columnName, $value);
			}
		}
		return $this;
	}

	/**
	 * Возвращает данные в массиве колонка=>значение
	 * @return array
	*/
	public function toArray() {
		$data = parent::toArray ();
		$data['id'] = (int) $data['id'];
		return $data;
	}
}