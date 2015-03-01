<?php
/**
 * 
 * Марка
 * 
 */

class Auto_Model_CarMark extends Auto_Model_Abstract {
	
	public function __toString() {
		return (string) $this->name;
	}
	
	/**
	 * @return string
	*/
	public function getLogo() {
		return $this->id_car_mark . '.png';
	}
		
	/**
	 * Возвращает список моделей марки
	 * @return array
	 */
	public function getModelList() {
		$items = array();
		$rows = $this->findDependentRowset( 'Auto_Model_Table_CarSeries' );
		foreach ( $rows as $row ) {
			$items[ $row->id_car_mark ] = $row;
		}
		return $items;
	}
	
	/**
	 * @return Zend_Navigation_Page
	 */
	public function getPage() {
		$data = array(
			'id'        => 'brand-' . $this->id, 
			'label'     => $this->name,
			'title'     => $this->name,
			'module' => 'auto',
			'controller' => 'brand',
			'action' => 'card',
			'params'    => array( 'id'=>$this->id )
		);
		return Zend_Navigation_Page::factory( $data );
	}

	/**
	 * Возвращает данные в массиве колонка=>значение
	 * @return array
	*/
	public function toArray() {
		$data = parent::toArray ();
		$data['id_car_mark'] = (int) $data['id_car_mark'];
		return $data;
	}
}