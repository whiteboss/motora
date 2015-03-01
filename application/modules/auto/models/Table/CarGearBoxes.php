<?php
/**
 *
 * Интерфейс доступа к таблице данных кузовов
 *
 */

class Auto_Model_Table_CarGearBoxes extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_gear_box';
        protected $_rowClass = 'Auto_Model_CarGearBox';
		
	/**
	 * Возвращает весь список
	 * @return array
	 */
	public function getGearBoxes($id_car_category = NULL) {
		$items = array();
                $select = $this->select();
                
                if (!is_null($id_car_category))        
                    $select->where('id_car_category = ?', $id_car_category); //->order('order ASC');
                
		$rows = $this->fetchAll($select);
		foreach ( $rows as $row ) {
			$items[ $row->id_gear_box ] = $row;
		}
		return $items;
	}

}