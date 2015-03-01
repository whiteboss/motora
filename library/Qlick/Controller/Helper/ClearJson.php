<?php
/**
 * 
 * Нормализация Json для записи в базу
 *
 */

class Qlick_Controller_Helper_ClearJson extends Zend_Controller_Action_Helper_Abstract {
	
	/**
	 * Убирает из данных, сериализованных в строку несущесвующие NULL
	 * @param string $data
	 * @return null|string
	 */
	protected function clear( $data ) {
		$data_arr = json_decode( $data );
		if ( !is_array($data_arr) ) return $data;
		$data_arr_new = array();
		foreach( $data_arr as $k=>$val ) {
			if ( !empty($val) && !is_null($val)) $data_arr_new[] = $val;
		}
		return json_encode( $data_arr_new );
	}
	
	public function direct( $data ) {
		return $this->clear( $data );
	}
}