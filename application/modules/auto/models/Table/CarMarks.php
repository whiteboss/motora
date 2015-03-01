<?php
/**
 *
 * Интерфейс доступа к таблице данных марок
 *
 */

class Auto_Model_Table_CarMarks extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_car_marks';
	protected $_rowClass = 'Auto_Model_CarMark';
		
	/**
	 * Возвращает весь список
	 * @return array
	 */
	public function getAll()
        {
		$items = array();
                $select = $this->select();
                //$select->order('order asc');
		$rows = $this->fetchAll($select);
		foreach ( $rows as $row ) {
			$items[ $row->id_car_mark ] = $row;
		}
		return $items;
	}
        
        public function getMarkByUrl($url)
        {
                
                $q = $this->select()->setIntegrityCheck(true)->from(array('m' => $this->_name));

                $q->where('LOWER(REPLACE(m.name, "-", " ")) = ?', $url)->limit(1);

                $result = $this->fetchRow($q);

                if ($result)        
                    return $result;
                else
                    return NULL;
                
	}

        public function getFirst()
        {
 		$items = array();
                $select = $this->select();
                //$select->order('order asc');
                $select->limit(7);
		$rows = $this->fetchAll($select);
		foreach ( $rows as $row ) {
			$items[ $row->id_car_mark ] = $row;
		}
		return $items;
        }

        public function getElse()
        {
 		$items = array();
                $select = $this->select();
                //$select->order('order asc');
		$rows = $this->fetchAll($select);
                $i = 1;
		foreach ( $rows as $row ) {
                    if ($i>7) $items[ $row->id_car_mark ] = $row;
                    $i++;
		}
		return $items;
        }

        public function getBrandByName($name)
        {
		$items = array();
                $select = $this->select()->where('name = ?', $name)->limit(1);
		$rows = $this->fetchAll($select);
		if (count($rows) > 0) return $rows->current();
		return null;
        }
}