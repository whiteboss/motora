<?php
/**
 * 
 * Резюме
 * 
 */

class Users_Model_Resume extends Users_Model_Abstract {

        protected $_city;
        protected $_author;
        
	/**
	 * Опыт работы (список)
	 * @var array
	 */
	protected static $_experience_levels = array(
		'отсутствует', 'менее одного года', '1-3 лет', '3-5 лет', '5-10 лет', 'более 10 лет'
	);
	
	/**
	 * Языки (список)
	 * @var array
	 */
	protected static $_languages = array(
		'английский', 'арабский', 'испанский', 'итальянский', 'китайский', 'корейский', 'немецкий', 'французкий', 'японский', '- otro -'
	);
	
	/**
	 * Владение языками (список)
	 * @var array
	 */
	protected static $_language_levels = array(
		'Базовые знания', 'Владею разговорным', 'Владею техническим', 'Свободно владею'
	);
	
	/**
	 * Водительские права (список)
	 * @var array
	 */
	protected static $_driving_permits = array(
		'Категория B', 'Категория C', 'Категория D', 'Категория E'
	);
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getFio();
	}	
  
	
	/**
	 * Возвращает список
	 * @return array
	 */
	public static function getLangs() {
		return array_combine( self::$_languages, self::$_languages );	
	}
	
	/**
	 * Возвращает список
	 * @return array
	 */
	public static function getExperienceLevels() {
		return self::$_experience_levels;	
	}
	
	/**
	 * Возвращает список
	 * @return array
	 */
	public static function getLanguageLevels() {
		return self::$_language_levels;	
	}
	
	/**
	 * Возвращает список
	 * @return array
	 */
	public static function getDrivingPermits() {
		return self::$_driving_permits;	
	}
	
	/**
	 * Возвращает полное имя
	 * @return string
	*/
	public function getFio() {
		$fio = $this->surname;
		if ( !empty($this->name) ) {
			$fio .= ' '.$this->name;
			if ( !empty($this->patronymic) ) $fio .= ' '.$this->patronymic;
		}
		return $fio;
	}
	
	/**
	 * @return string
	*/
	public function getBirthDate() {
		$date = new Zend_Date($this->birthdate, Zend_Date::ISO_8601);
		return $date->toString('dd.MM.yyyy');
	}
	
	/**
	 * @return array
	*/
	public function getIndustryList() {
		if ( !is_null($this->industry) ) {
			$list = explode(',', $this->industry);
			foreach( $list as $k=>$v ) {
				$industies = Companies_Model_Vacancy::getIndustries();
				$list[$k] = $industies[$v];
			}
			return $list;
		} else {
			return array();
		}
	}
        
        public function getAuthor()
        {
            if ( !empty($this->_author) ) return $this->_author;
            $this->_author = $this->findParentRow( 'Application_Model_Table_Users' );
            return $this->_author;
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
		$list = Companies_Model_Resume::getScheduleTypes();
		if ( !is_null($this->schedule) and isset( $list[ $this->schedule ] ) )
			return $list[ $this->schedule ];
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
		}
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
		if ( !is_null($this->industry) and isset( Companies_Model_Vacancy::$_industries[ $this->industry ] ) )
			return Companies_Model_Vacancy::$_industries[ $this->industry ];
		else
			return '';
	}        
	
	/**
	 * Возвращает опыт работы
	 * @return string
	*/
	public function getExperience() {
		if ( !is_null($this->experience) and isset( self::$_experience_levels[ $this->experience ] ) )
			return self::$_experience_levels[ $this->experience ];
		else
			return '';
	}
	
	/**
	 * Возвращает места работы
	 * @return array
	*/
	public function getWorks() {
		if ( !empty($this->work) )
			return json_decode( $this->work );
		else
			return array();
	}
	
	/**
	 * Возвращает образование
	 * @return string
	*/
	public function getEducation() {
		$list = Companies_Model_Resume::getEducationTypes();
		if ( !is_null($this->education) and isset( $list[ $this->education ] ) )
			return $list[ $this->education ];
		else
			return '';
	}
	
	/**
	 * Возвращает места обучения
	 * @return array
	*/
	public function getInstitutes() {
		if ( !empty($this->institute) )
			return json_decode( $this->institute );
		else
			return array();
	}
	
	/**
	 * @return array
	 */
	public function getLanguages() {
		if ( !empty($this->languages) ) {
			$ls = json_decode( $this->languages );
			foreach( $ls as $l ) {
				$l->level = self::$_language_levels[$l->level];
			}
			return $ls;
		} else {
			return array();
		}
	}
	
	/**
	 * @return array
	*/
	public function getDriving() {
		if ( !empty($this->driving) ) {
			$list = explode(',', $this->driving);
			foreach( $list as $k=>$v ) {
				$list[$k] = self::$_driving_permits[$v];
			}
			return $list;
		} else {
			return array();
		}
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
        
        public function getSalaryFrom() {
                return number_format( $this->salary_from, 0, ',', ' ' );
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

	/**
	 * Устанавливает новые данные из массива, если они изменились
	 * @param  array $data
	 * @return $this
	 */
	public function setFromArray(array $data) {
		$data = array_intersect_key($data, $this->_data);
		foreach( array('name','patronymic','city','salary_from','skills','work','institutes','certificates','languages','driving','hobbies','phone','email') as $col ) {
			if ( isset($data[$col]) and empty($data[$col]) ) {
				$data[$col] = null;
			}
		}
		foreach ($data as $columnName => $value) {
			if ( $this->_data[$columnName] != $value ) {
				$this->__set($columnName, $value);
			}
		}
		return $this;
	}
}