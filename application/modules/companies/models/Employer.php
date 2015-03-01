<?php
/**
 * 
 * Сотрудник
 * 
 */

class Companies_Model_Employer extends Companies_Model_Abstract {
	
	protected $_user;
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getFullName();
	}

	/**
	 * Возвращает данные в массиве колонка=>значение
	 * @return array
	*/
	public function toArray() {
		$data = parent::toArray();
		$data['id'] = (int) $data['id'];
		return $data;
	}
	
	/**
	 * Возвращает запись пользователя
	 * @return Zend_Db_Table_Row пользователь
	 */
	public function getUser() {
		if ( !isset($this->_user) ) {
			$this->_user = $this->findParentRow('Application_Model_Table_Users');
			if ( !$this->_user )
				throw new Companies_Model_Exception('Пользователь не существует');
		}
		return $this->_user;
	}
	
	/**
	 * @return string
	 */
	public function getFullName() {
		if ( isset($this->lastname) and isset($this->firstname) ) {
			return $this->lastname . ' ' . $this->firstname;
		}
		$user = $this->getUser();
		return $user->firstname . ' ' . $user->lastname;
	}
	
	/**
	 * @return string
	*/
	public function getPosition() {
		return $this->position;
	}
}