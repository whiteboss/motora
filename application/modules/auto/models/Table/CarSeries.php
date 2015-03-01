<?php
/**
 *
 * Интерфейс доступа к таблице данных моделей
 *
 */

class Auto_Model_Table_CarSeries extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_car_series';
	protected $_rowClass = 'Auto_Model_CarSeries';

	protected $_referenceMap = array (		
		'Mark' => array (
			'columns' => 'id_car_mark', 
			'refTableClass' => 'Auto_Model_Table_CarMarks', 
			'refColumns' => 'id_car_mark'
		)
	);
        
        public function getSerieByUrl($url)
        {
                
                $q = $this->select()->setIntegrityCheck(true)->from(array('s' => $this->_name));

                $q->where('LOWER(REPLACE(s.name, "-", " ")) = ?', $url)->limit(1);

                //throw new Exception($q);
                $result = $this->fetchRow($q);

                if ($result)        
                    return $result;
                else
                    return NULL;
                
	}
		
	/**
	 * Возвращает список моделей для заданных кузовов или марок
	 * @param array $body_ids кузова
	 * @param array $brand_ids марки
	 * @param bool $with_items только с объявлениями
	 * @return array
	 */
	public function getListByBodyBrand( array $body_ids, array $car_mark_ids, $with_items=false, $limit = 0 ) {
		$items = array();
		if ( $with_items ) {
			$select = $this->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
				->setIntegrityCheck(false)
				->joinLeft( 'auto_car_item', 'auto_car_item.id_model = '.$this->_name.'.id', array(
                                    'COUNT(auto_car_item.id) as items_count',
                                    'MIN(auto_car_item.price) as min_price',
                                    'MAX(auto_car_item.price) as max_price'
                                ))
                                //->joinLeft( 'auto_car_brand', 'auto_car_brand.id = '.$this->_name.'.id_brand', array('name AS brandname', 'order AS brandorder') )
				->group( $this->_name.'.id' )
				->having( 'items_count > 0' );
                        
		} else {
			$select = $this->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                                ->setIntegrityCheck(false)
                                //->joinLeft( 'auto_car_brand', 'auto_car_brand.id = '.$this->_name.'.id_brand', 'order' )
                                ;
                        
                        
		}
		if ( !empty($body_ids) ) {
			$select->where( 'id_body in (?)', $body_ids );
		}
		if ( !empty($car_mark_ids) ) {
			$select->where( 'id_car_mark in (?)', $car_mark_ids );
		}
                
                if ($limit > 0)
                    $select->limit($limit);
                
                if ( $with_items ) {
                    $select->order(array('brandorder ASC', 'name ASC'));
                } else {
                    $select->order('name ASC');
                }
                
                throw new Exception($select);
		$rows = $this->fetchAll( $select );
		foreach ( $rows as $row ) {
			$items[ $row->id_series ] = $row;
		}
		return $items;
	}
        
        // json
        public function getSeriesByMarks( array $car_mark_ids, $json = false, $with_null = false ) {
		$items = array();
                
                $subselect = $this->select()
                    ->from(array('aca' => 'auto_car_ads'), 
                    array('pcs.id_series', 'COUNT(pcs.id_series) as count_car_ads', 'MIN(aca.price) as min_price', 'MAX(aca.price) as max_price')
                )
                ->setIntegrityCheck(false)
                ->joinInner(array('pcm' => 'pr_car_models'), 'aca.id_car_model = pcm.id_car_model', NULL)
                ->joinInner(array('pcs' => 'pr_car_series'), 'pcs.id_series = pcm.id_series', NULL)
                ->group('pcs.id_series')                
                ;
                
                $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('pcs' => 'pr_car_series'), array('pcs.id_series', 'pcs.id_car_mark', 'pcs.name as name', 'sdg.count_car_ads', 'sdg.min_price', 'sdg.max_price'))
                    ->joinLeft( array('sdg' => new Zend_Db_Expr('('.$subselect.')')), 'sdg.id_series = pcs.id_series', NULL );

		if ( !empty($car_mark_ids) ) {
                    $select->where( 'pcs.id_car_mark in (?)', $car_mark_ids );
		}
                
                $select->order('pcs.name ASC');
                
                //throw new Exception($select);
		$rows = $this->fetchAll( $select );
                
                if ($json && $with_null)
                    $items[] = array('id' => 0, 'name' => '- Elije de series -');    
                
		foreach ( $rows as $row ) {
                    if ($json)
			$items[] = array('id' => $row->id_series, 'name' => $row->name);
                    else
                        $items[$row->id_series] = $row;
		}
		return $items;
	}  
        
	/**
	 * Возвращает список моделей марки
	 * @param array $brand_id марка
	 * @return array
	 */
	public function getListByBrand( $brand_id, $sort = 'popular' ) {
            $items = array();
            $select = $this->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                    ->setIntegrityCheck(false)
                    ->joinLeft( array('car'=>'auto_car_item'), 'car.id_model = '.$this->_name.'.id', array(
                            'COUNT(car.id) as items_count',
                            'MIN(car.price) as min_price',
                            'MAX(car.price) as max_price',
                    ))
                    ->where( 'id_brand = ?', $brand_id )
                    ->order( array($sort, 'name ASC', 'IF(main_photo,1,0) ASC') )
                    ->group( $this->_name.'.id' );

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $items[ $row->id_series ] = $row;
            }
            return $items;
	}
        
//        public function getModelByName( $name ) {
//            
//            // исключения концепта
//            $norm_model = '';
//            switch ($name) {                
//                case 'EX25 HI-TECH' : $norm_model = 'EX25'; break;
//                case 'GX460 EXECUTIVE' : $norm_model = 'GX460'; break;                
//                case 'RX350EXES+SP' : $norm_model = 'RX350'; break;
//                case 'RX350EXEC+SP' : $norm_model = 'RX350'; break;
//                case 'HIGHLANDER прес' : $norm_model = 'Highlander'; break;
//                case 'PRADO STAND' : $norm_model = 'Prado'; break;
//                case 'RAV-4 ПРЕС.+' : $norm_model = 'Rav-4'; break;
//                case 'RAV-4 ЭЛЕГАНС' : $norm_model = 'Rav-4'; break;
//                case 'TUNDRA SR5' : $norm_model = 'Tundra'; break; 
//                case 'FJ CRUIZER' : $norm_model = 'FJ Cruiser'; break; 
//                default : $norm_model = $name;
//            }            
//            
//            $select = $this->select('id')->where('UPPER(name) = "' . $norm_model . '"');            
//            $row = $this->fetchRow($select);            
//            if ($row) return $row->id_series; else return NULL;
//            
//        }

}