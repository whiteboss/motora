<?php
/**
 * 
 * Модель
 * 
 */

class Auto_Model_CarSeries extends Auto_Model_Abstract {
	
	/**
	 * @var Auto_Model_CarMark
	 */
	protected $_mark;
	
	/**
	 * @return string
	 */
	public function __toString() {
		return (string) $this->name;
	}
        
        public function getModelLink() {
            $qs = new Qlick_Str;
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            return $viewRenderer->view->url(array('car_mark' => $qs->convertToLink($this->getMark()), 'car_series' => $qs->convertToLink($this->name)), 'car_models');
        }
	
	/**
	 * @return Auto_Model_CarMark
	*/
	public function getMark() {
		if ( !empty($this->_mark) ) return $this->_mark;
		$this->_mark = $this->findParentRow( 'Auto_Model_Table_CarMarks' );
		return $this->_mark;
	}

        public function getBrandModel() {
            return $this->getMark()->name. ' ' . $this->name;
        }

        /**
	 * Возвращает список авто
	 * @return array
	 */
	public function getCars() {
		$rows = $this->findDependentRowset( 'Auto_Model_Table_CarAds' );
		$items = array();
		foreach ( $rows as $row ) {
			$items[ $row->id ] = $row;
		}
		return $items;
	}
	
	/**
	 * @return string
	*/
	public function getPhoto() {
		if (empty($this->photo)) return null;
		list($first) = json_decode( $this->photo );
		return $first;
	}
        
        public function getCountOffers() {
            $rows = $this->findDependentRowset( 'Auto_Model_Table_CarAds' );
            if (count($rows) > 0) return count($rows); else return NULL;
        }
	
	/**
	 * @return Zend_Navigation_Page
	 */
	public function getPage() {
		$data = array(
			'id'        => 'model-' . $this->id, 
			'label'     => $this->name,
			'title'     => $this->name,
			'module' => 'auto',
			'controller' => 'model',
			'action' => 'card',
			'params'    => array( 'id'=>$this->id ),
		);
		return Zend_Navigation_Page::factory( $data );
	}

	/**
	 * Возвращает данные в массиве колонка=>значение
	 * @return array
	*/
	public function toArray() {
		$data = parent::toArray ();
		$data['id'] = (int) $data['id'];
		return $data;
	}
}