<?php
/**
 * 
 * Кузов
 * 
 */

class Auto_Model_CarBody extends Auto_Model_Abstract {
	
	public function __toString() {
		return (string) $this->name;
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