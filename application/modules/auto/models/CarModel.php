<?php
/**
 * 
 * Модель
 * 
 */

class Auto_Model_CarModel extends Auto_Model_Abstract {
	
	/**
	 * @var Auto_Model_CarMark
	 */
	protected $_mark;
        protected $_series;
	
	/**
	 * @return string
	 */
	public function __toString() {
		return (string) $this->name;
	}
	
	/**
	 * @return Auto_Model_CarMark
	*/
	public function getMark() {
		if ( !empty($this->_mark) ) return $this->_mark;
		$this->_mark = $this->findParentRow( 'Auto_Model_Table_CarMarks' );
		return $this->_mark;
	}
        
        public function getSeries() {
		if ( !empty($this->_series) ) return $this->_series;
		$this->_series = $this->findParentRow( 'Auto_Model_Table_CarSeries' );
		return $this->_series;
	}
        
        public function getModifications($with_null = false) {
//            $rows = $this->findDependentRowset('Auto_Model_Table_CarModifications', null, $this->select()->where('user_id = ?', $user_id)->where('event_id = ?', $this->id));  
//            return (bool) count($rows);
            $items = array();
            
            $rows = $this->findDependentRowset('Auto_Model_Table_CarModifications');
            if (count($rows) > 0) {
                if ($with_null) {
                    $items[] = array('id_car_modification' => 0, 'name' => '- Elección de modificación -'); // + $rows->toArray();
                    foreach ($rows as $row)
                        $items[] = $row->toArray();
                } else {
                    $items = $rows->toArray();
                }
                
                return $items; 
            } else {
                if ($with_null)
                    return array('id' => '0', 'name' => '- Elección de modificación -');
                else    
                    return NULL;
            }
            
        }

        /**
	 * Возвращает данные в массиве колонка=>значение
	 * @return array
	*/
	public function toArray() {
		$data = parent::toArray ();
		$data['id_car_model'] = (int) $data['id_car_model'];
		return $data;
	}
        
}