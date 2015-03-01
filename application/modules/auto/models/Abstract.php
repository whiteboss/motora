<?php
/**
 * 
 * Абстрактный информационный объект
 * расширяет стандартный класс строки Zend_Db_Table_Row
 * 
 */

abstract class Auto_Model_Abstract extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * Возвращает значение int для идентификаторов, string для остальных
	 *
	 * @param  string $columnName колонка
	 * @return int|string         значение
	 * @throws Auto_Model_Exception если не существует
	 */
	public function __get($columnName) {
		if (! array_key_exists ( $columnName, $this->_data )) {
			throw new Auto_Model_Exception ( "Неизвестное значение '$columnName' " );
		}
		if (preg_match ( '/^i(d|s)(_|$)/', $columnName )) { // если название колонки начинается с id или is_ - привести к int
			return ( int ) $this->_data [$columnName];
		}
		return $this->_data [$columnName];
	}

	/**
	 * Устанавливает новые данные из массива, если они изменились
	 * @param  array $data
	 * @return Auto_Model_Abstract $this
	 */
	public function setFromArray(array $data) {
		$data = array_intersect_key($data, $this->_data);
		foreach ($data as $columnName => $value) {
			if ( $this->_data[$columnName] != $value ) {
				$this->__set($columnName, $value);
			}
		}
		return $this;
	}
	
	/**
	 * Если есть изменения - сохранить в базу
	 * @return bool true если данные сохранены, иначе false
	 */
	public function commit() {
		//$db = $this->getTable()->getAdapter()->beginTransaction();
		$result = false;
		try {
			if ( !empty( $this->_modifiedFields ) ) {
				$result = (bool) $this->save();
			}
			//$db->commit();
		} catch ( Exception $e ) {
			//$db->rollBack();
			throw new Auto_Model_Exception( $e );
		}
		return $result;
	}
}