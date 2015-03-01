<?php
/**
 * 
 * Абстрактный информационный объект
 * расширяет стандартный класс строки Zend_Db_Table_Row
 * 
 */

abstract class Application_Model_Abstract extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * 
	 *
	 * @param  string $columnName колонка
	 * @return mixed         
	 * @throws Feed_Model_Exception если не существует
	 */
	public function __get($columnName) {
		if (! array_key_exists ( $columnName, $this->_data )) {
			throw new Feed_Model_Exception ( "Неизвестное значение '$columnName' " );
		}
		return $this->_data [$columnName];
	}

        /**
	 * Устанавливает новые данные из массива, если они изменились
	 * @param  array $data
	 * @return Companies_Model_Abstract $this
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
			throw new Feed_Model_Exception( $e );
		}
		return $result;
	}
}