<?php
/**
 *
 * Интерфейс доступа к таблице данных кузовов
 *
 */

class Auto_Model_Table_CarGearTypes extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_drive_gear_type';
        protected $_rowClass = 'Auto_Model_CarGearType';
		
	/**
	 * Возвращает весь список
	 * @return array
	 */
	public function getAll() {
		$items = array();
                $select = $this->select();
                
		$rows = $this->fetchAll($select);
		foreach ( $rows as $row ) {
			$items[ $row->id_drive_gear_type ] = $row;
		}
		return $items;
	}

}