<?php
/**
 *
 * Интерфейс доступа к таблице данных кузовов
 *
 */

class Auto_Model_Table_CarBody extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_body_type';
	protected $_rowClass = 'Auto_Model_CarBody';
		
	/**
	 * Возвращает весь список
	 * @return array
	 */
	public function getAll() {
		$items = array();
                $select = $this->select()->where('id_car_category = 1'); //->order('order ASC');
		$rows = $this->fetchAll($select);
		foreach ( $rows as $row ) {
			$items[ $row->id_body_type ] = $row;
		}
		return $items;
	}

}