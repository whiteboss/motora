<?php
/**
 *
 * Интерфейс доступа к таблице данных моделей
 *
 */

class Auto_Model_Table_CarModels extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_car_models';
	protected $_rowClass = 'Auto_Model_CarModel';

	protected $_referenceMap = array (
		'Mark' => array (
			'columns' => 'id_car_mark', 
			'refTableClass' => 'Auto_Model_Table_CarMarks', 
			'refColumns' => 'id_car_mark'
		),
                'Series' => array (
			'columns' => 'id_series', 
			'refTableClass' => 'Auto_Model_Table_CarSeries', 
			'refColumns' => 'id_series'
		)
	);
        
        // json
        public function getModelsBySeries( array $serie_ids, $ext_model_data = false, $with_null = false )
        {
		$items = array();
                $select = $this->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                        ->setIntegrityCheck(false)
                        //->joinLeft( 'auto_car_brand', 'auto_car_brand.id = '.$this->_name.'.id_brand', 'order' )
                        ;
                
                if ($ext_model_data) {
                    $select->joinLeft( 'auto_car_brand', 'auto_car_brand.id = '.$this->_name.'.id_brand', 'order' );    
                }

		if ( !empty($serie_ids) ) {
                    $select->where( 'id_series in (?)', $serie_ids );
		}
                
                $select->order('name ASC');
                
                //throw new Exception($select);
		$rows = $this->fetchAll( $select );
                
                //if ($with_null) $items[] = array('id' => '0', 'name' => '- Elección de modelo -');
                
		foreach ( $rows as $row ) {
                    if ($ext_model_data)
			$items[] = array('id' => $row->id_car_model, 'name' => $row->name, 'year_start' => $row->year_release, 'year_end' => $row->year_finish);
                    else
                        $items[] = array('id' => $row->id_car_model, 'name' => $row->name);
		}
		return $items;
                
	}
        
        public function getModificationsBySeries( array $serie_ids )
        {
		$items = array();
                $select = $this->select()
                        ->from(array('cm' => 'pr_car_models'), 
                            array('name as model_name', 'year_finish')
                        )
                        ->setIntegrityCheck(false)
                        ;
                
                $select->joinLeft( array('pcm' => 'pr_car_modification'), 'pcm.id_car_model = cm.id_car_model', array('id_car_modification', 'name', 'engine_type', 'engine_volume', 'drive_gear_type', 'year_release', 'year_finish') );                 

		if ( !empty($serie_ids) ) {
                    $select->where( 'cm.id_series in (?)', array_keys($serie_ids) );
		}
                
                //$select->order('name ASC');
                $select->group('pcm.id_car_modification')->order(array('cm.year_finish DESC', 'model_name ASC'));
                
                //throw new Exception($select);
		$rows = $this->fetchAll( $select );
                
                //if ($with_null) $items[] = array('id' => '0', 'name' => '- Elección de modelo -');
                
		foreach ( $rows as $row ) {                    
                        $items[$row->id_car_modification] = $row;
		}
		return $items;
                
	}

}