<?php
/**
 *
 * Интерфейс доступа к таблице данных кузовов
 *
 */

class Auto_Model_Table_CarFuelKinds extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_fuel_kind';
        protected $_rowClass = 'Auto_Model_CarFuelKind';
		
	/**
	 * Возвращает весь список
	 * @return array
	 */
	public function getAll() {
		$items = array();
                $select = $this->select();
                
		$rows = $this->fetchAll($select);
		foreach ( $rows as $row ) {
			$items[ $row->id_fuel_kind ] = $row;
		}
		return $items;
	}

}