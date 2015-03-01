<?php
/**
 *
 * Интерфейс доступа к таблице данных объявлений авто
 *
 */

class Auto_Model_Table_CarAds extends Zend_Db_Table_Abstract {
    
	protected $_name = 'auto_car_ads';
	protected $_rowClass = 'Auto_Model_CarAd';

	protected $_referenceMap = array (
            'Model' => array (
                'columns' => 'id_car_model',
                'refTableClass' => 'Auto_Model_Table_CarModels',
                'refColumns' => 'id_car_model'
            ),
            'Modification' => array (
                'columns' => 'id_car_modification',
                'refTableClass' => 'Auto_Model_Table_CarModifications',
                'refColumns' => 'id_car_modification'
            ),
            'Users' => array(
                'columns' => 'id_user',
                'refTableClass' => 'Application_Model_Table_Users',
                'refColumns' => 'id'
            ),
	);
        
        public function getCar($id)
        {
            $select = $this->select(array('id', 'id_user'))->where('id = ?', (int) $id)->limit(1);
            $row = $this->fetchRow($select);
            if (!$row) {
                //throw new Exception("Такой пользователь не существует");
                return NULL;
            }
            return $row;        
        }
        
        public function getCarByIdSync( $id_sync ) {
            
            $row = $this->fetchRow($this->select('id')->where('id_sync = ?', (int) $id_sync ));
            if ($row) return $row; else return NULL;            
            
        }
        
        public function getCarsByUser( $userId, $count = false )
        {
            $items = array();
            
            if ($count)
                $select = $this->select()->from($this, array('id'));
            else
                $select = $this->select();            
            
            $select->where('id_user = ?', $userId)->order('date DESC');
            
            //throw new Exception($select);
            
            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $items[ $row->id ] = $row;
            }
            return $items;
            
        }

        /**
	 * Возвращает список всех объявлений или только для заданных моделей
	 * @param array $model_ids модели
	 * @return array
	 */
	public function getListByModel( array $model_ids ) {
		$items = array();
		$select = $this->select();
		if ( !empty($model_ids) ) {
                    $select->where( 'id_car_model in(?)', $model_ids );
		}
//                else {
//                    $select->where( 'id_car_model IS NULL' );
//                }
                throw new Exception($select);
		$rows = $this->fetchAll( $select );
		foreach ( $rows as $row ) {
			$items[ $row->id ] = $row;
		}
		return $items;
	}
        
        public function getListBySeries( array $series_ids ) {
		$items = array();
		$select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('aca' => 'auto_car_ads'), array('aca.id', 'aca.year', 'aca.mileage', 'aca.price', 'aca.engine_volume'))                        
                        ->joinLeft(array('pcm' => 'pr_car_models'), 'pcm.id_car_model = aca.id_car_model', array('pcm.id_series'))
                        ;                
                
		if ( count($series_ids) > 0 && !is_null($series_ids) ) {
                    $select->where( 'pcm.id_series in(?)', $series_ids );
		}
//                else {
//                    $select->where( 'id_car_model IS NULL' );
//                }
                throw new Exception($select);
		$rows = $this->fetchAll( $select );
		foreach ( $rows as $row ) {
			$items[ $row->id ] = $row;
		}
		return $items;
	}
        
	public function getSimilarCars( $car, $limit = 0 ) {
            $items = array();
            $select = $this->select();
            $select->where('id_car_model = ?', $car->id_model)->where('is_new = ?', $car->is_new)->where('engine_type = ?', $car->engine_type)
                    ->where('gearbox = ?', $car->gearbox)->where('id <> ?', $car->id);
            
            // отличие цены на 15%
            $similar_price_from = $car->price - ($car->price * 0.15);
            $similar_price_to = $car->price + ($car->price * 0.15);        
            $select->where('price >= ?', $similar_price_from)->where('price <= ?', $similar_price_to);

            if ($limit > 0)
                $select->limit($limit);

            $select->order(array('price asc'));
            //throw new Exception($select);
            $rows = $this->fetchAll($select);
            foreach ( $rows as $row ) $items[ $row->id ] = $row;
            return $items;
	}        

        public function getMinYear() {
            $select = $this->select('MIN(year)');
            $row = $this->fetchRow($select);
            return $row->year;
        }

         /**
	 * Возвращает список объявлений для заданных параметров
	 * @param array $data параметры поиска
	 * @param bool $group OPTIONAL группировать по моделям
	 * @return array массив объявлений id=>item, опционально с группировкой по моделям id_model=>array
	 */
	public function filter( array $data, $from = 0, $limit = 0 ) {
		$items = array();
		$select = $this->select()
                        ->setIntegrityCheck(false)                
                        ->from(array('aca' => 'auto_car_ads'), array('aca.id', 'aca.id_car_model', 'aca.id_car_modification', 'aca.year', 'aca.mileage', 'aca.price', 'aca.engine_volume', 'photos'))
                        ->joinLeft(array('pcm' => 'pr_car_models'), 'pcm.id_car_model = aca.id_car_model', array('pcm.id_series'))
                        ;
                      
                if ( !empty($data['mark']) ) {                    
                        $select->where( 'pcm.id_car_mark in(?)', $data['mark'] )
                            ;
                }
                
                
		if ( !empty($data['series']) ) {                    
                        $select->where( 'pcm.id_series in(?)', $data['series'] )
                            ;
                            
//                    $select->where( 'id_model in (?)', $data['model'] );
		//} else {
//                //    $select->where( 'id_model IS NULL' );
                }
//                $min_year = $this->getMinYear();
		foreach ( array('price', 'year', 'mileage', 'engine_volume') as $name ) {

                    if (!empty($data[$name.'_from']) && !empty($data[$name.'_to']) && $data[$name.'_from'] == $data[$name.'_to']) {
                        // Если одинаковые значения
                        $select->where( $name.' = ?', (int) $data[$name.'_from']);
                    } else {
			if ( !empty($data[$name.'_from']) ) {
//                            if ($name == "year" && (int) $data[$name.'_to'] == $min_year) {
//
//                            } else {
				$select->where( $name.' >= ?', (int) $data[$name.'_from']);
//                            }
			}
			if ( !empty($data[$name.'_to']) ) {
                            if ((int) $data[$name.'_to'] < (int)Auto_Model_CarAd::$limited_value_filter[$name]) {
				$select->where( $name.' <= ?', (int) $data[$name.'_to']);
                            }
			}
                    }
		}
//		foreach ( array('engine_type', 'gearbox', 'transmission') as $name ) {
//			if ( !empty($data[$name]) ) {
//				$select->where( $name.' in (?)', $data[$name] );
//			}
//		}
		if ( !empty($data['withphoto']) ) {
			$select->where( 'photos IS NOT NULL' );
		}
                
                if ($from > 0) {            
                    $select->limit(Auto_Model_CarAd::$car_per_lazypage, $from);
                } elseif ($limit > 0) {
                    $select->limit($limit);
                }
                
		$select->order(array('date DESC', 'price ASC'));
                //throw new Exception($select);
		
                $rows = $this->fetchAll( $select );
        	foreach ( $rows as $row ) $items[ $row->id ] = $row;

		return $items;
	}
        
        public function searchSpammersObjects($contacts = array())
        {
            $items = array();

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('c' => $this->_name), array('phone', 'email', 'skype'))                    
                    ;

            if (isset($contacts['phone'])) $select->where('REPLACE(phone, " ", "") = ?', str_replace (" ", "", $contacts['phone']));
            if (isset($contacts['email'])) $select->where('email = ?', $contacts['email']);
            if (isset($contacts['skype'])) $select->where('skype = ?', $contacts['skype']);

            //throw new Exception($select);
            $rows = $this->fetchAll($select);
            foreach ($rows as $row) {
                $items[] = $row;
            }
            return $items;        
        }
	
	/**
	 * Создание нового объявления
	 * @param  array $data OPTIONAL данные инициализации
     * @param  string $defaultSource OPTIONAL флаг указывающий установить значения по умолчанию
	 * @return Auto_Model_CarAd
	 */
	public function createRow( $data = array(), $defaultSource = null ) {
		$new = parent::createRow( $data, $defaultSource );
		$new->date = date_create()->format( 'Y-m-d H:i:s' );
                // округлим, если кто-то ввел ебанутое значение
                $engine_volume = round($new->engine_volume/100)*100;
                $new->engine_volume = $engine_volume;
                $mileage = round($new->mileage/1000)*1000;
                $new->mileage = $mileage;
		return $new;
	}
}