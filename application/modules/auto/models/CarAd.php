<?php
/**
 * 
 * Объявление (авто)
 * 
 */

class Auto_Model_CarAd extends Auto_Model_Abstract {

	//public static $engine_types = array( 'Дизельный', 'Бензиновый', 'Гибридный' );
	//public static $gearboxes = array( 'автомат', 'ручная', 'вариатор' );
	public static $transmissions = array( 'полный', 'задний', 'передний' );

        public static $limited_value_filter = array('price'=>'2000000', 'year'=>'2010', 'mileage'=>'150000', 'engine_volume'=>'3500');
        public static $default_value_filter = array('price'=>'1500000', 'year'=>'2007', 'mileage'=>'110000', 'engine_volume'=>'2800');
        
        public static $max_photos = 10;
        
        public static $car_per_lazypage = 20;

        protected $_model;
        protected $_modification;
        protected $_author;
        
        public function getLink($model) {
            $qs = new Qlick_Str;
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            return $viewRenderer->view->url(array('car_mark' => $qs->convertToLink($model->getMark()), 'car_series' => $qs->convertToLink($model->getSeries()), 'carId' => $this->id), 'car_ad');
        }
        
        public function getMarkLink() {
            $qs = new Qlick_Str;
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            return $viewRenderer->view->url(array('car_mark' => $qs->convertToLink($this->getMark())), 'car_series');
        }
        
        public function getModelLink() {
            $qs = new Qlick_Str;
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            return $viewRenderer->view->url(array('car_mark' => $qs->convertToLink($this->getMark()), 'car_series' => $qs->convertToLink($this->getSeries())), 'car_models');
        }

        /**
	 * @return Auto_Model_CarModel
	*/
	public function getModel() {
		if ( !empty($this->_model) ) return $this->_model;
		$this->_model = $this->findParentRow( 'Auto_Model_Table_CarModels' );
		return $this->_model;
	}
	
	/**
	 * @return Auto_Model_CarMark
	*/
	public function getMark() {
		return $this->getModel()->getMark();
	}
        
        public function getSeries() {
		return $this->getModel()->getSeries();
	}
        
        public function getName() {
            return $this->getMark() . ' ' . $this->getSeries(); // . ' ' . $this->getModel();
        }
        
        public function getModification() {
            if ( !empty($this->_modification) ) return $this->_modification;
            $this->_modification = $this->findParentRow( 'Auto_Model_Table_CarModifications' );
            if ($this->_modification)
                return $this->_modification;
            else
                return NULL;
	}        

                /**
	 * @return int
	*/
	public function getYear() {
		return (int) $this->year;
	}
	
	/**
	 * @return string
	*/
	public function getPrice() {
		return '$ ' . number_format( $this->price, 0, ',', ' ' );
	}
	
	/**
	 * @return string
	*/
	public function getMileage() {
                $qn = new Qlick_Num();
		return $qn->normMileage($this->mileage);
	}
        
        public function getEngine() {
            return $this->getEngineVolume() . 'cc ' . $this->getEngineType() . ' ' . $this->getGearBox() . ' ' . $this->getGearType();
        }

                /**
	 * @return int
	*/
	public function getEngineVolume() {
		return (int) $this->engine_volume;
	}
	
	/**
	 * @return string
	*/
	public function getEngineType() {
//		if ( !is_null($this->engine_type) )
//			return (string) self::$engine_types[ $this->engine_type ];
//		else
//			return '';
            $mod = $this->getModification();
            if (!is_null($mod)) {
                $fuel_kind = $mod->fuel_kind;
                switch ($fuel_kind) {
                    case '1' :
                    case '2' :
                    case '3' :
                    case '6' : return 'gasolina'; break;
                    case '5' : return 'diesel'; break;
                    case '7' : return 'híbrido'; break;
                    case '8' : return 'gas'; break;
                    case '9' : return 'electro'; break;
                }
            }
            
	}
		
	/**
	 * @return string
	*/
	public function getGearBox() {
            $mod = $this->getModification();
            if (!is_null($mod))
                return $mod->getGearBox();
            else
                return NULL;
	}
		
	/**
	 * @return string
	*/
	public function getGearType() {
	$mod = $this->getModification();
            if (!is_null($mod))
                return $mod->getGearType();
            else
                return NULL;
	}
	
	/**
	 * @return string
	*/
	public function getPhone() {
		return $this->phone;
	}
	
	/**
	 * @return string
	*/
	public function getEmail() {
		if (empty($this->email)) return '';
		return '<a class="blue" href="mailto:' . $this->email . '">' . $this->email . '</a>';
	}
	
	/**
	 * @return string
	*/
	public function getSkype() {
		return $this->skype;
	}

        public function getPhoto() 
        {
            $photo = json_decode($this->photos, true);
            return $photo[0];
        }

        public function getPhotos($limit = 0)
        {        
            if ($limit > 0) {
                $items = array();
                $photos = json_decode($this->photos, true);

                if (count($photos) > $limit) {
                    for ($i=0; $i < $limit; $i++) {
                        $items[] = $photos[$i];
                    } 
                } else {
                    for ($i=0; $i < count($photos); $i++) {
                        $items[] = $photos[$i];
                    }                
                }
                return $items;
            } else {
                return json_decode($this->photos, true);
            }
        }

        public function getAuthor()
        {
            if ( !empty($this->_author) ) return $this->_author;
            $this->_author = $this->findParentRow( 'Application_Model_Table_Users' );
            return $this->_author;
        }
	
	/**
	 * @return int
	*/
	public function getCounter() {
		return (int) $this->counter;
	}

	/**
	 * @return Zend_Navigation_Page
	 */
	public function getPage() {
		$data = array(
			'id'        => 'car-' . $this->id, 
			'label'     => 'car-' . $this->id,
			'module' => 'auto',
			'controller' => 'car',
			'action' => 'card',
			'params'    => array( 'id'=>$this->id ),
			'lastmod' => $this->date
		);
		return Zend_Navigation_Page::factory( $data );
	}

	/**
	 * Устанавливает новые данные из массива, если они изменились
	 * @param  array $data
	 * @return $this
	 */
	public function setFromArray(array $data) {
		$data = array_intersect_key($data, $this->_data);
		if ( isset($data['photo']) and empty($data['photo']) ) {
			$data['photo'] = null;
		}
		foreach ($data as $columnName => $value) {
			if ( $this->_data[$columnName] != $value ) {
				$this->__set($columnName, $value);
			}
		}
		return $this;
	}
	
	/**
	 * @return Auto_Model_CarAd $this
	*/
	public function count() {
		$this->counter = $this->counter + 1;
		$this->save();
		return $this;
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