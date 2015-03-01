<?php
/**
 *
 * Интерфейс доступа к таблице данных кузовов
 *
 */

class Auto_Model_Table_CarModifications extends Zend_Db_Table_Abstract {
	protected $_name = 'pr_car_modification';
	protected $_rowClass = 'Auto_Model_CarModification';

        protected $_referenceMap    = array(
            'GearBox' => array(
                'columns'           => 'gear_box',
                'refTableClass'     => 'Auto_Model_Table_CarGearBoxes',
                'refColumns'        => 'id_gear_box'
            ),
            'GearType' => array(
                'columns'           => 'drive_gear_type',
                'refTableClass'     => 'Auto_Model_Table_CarGearTypes',
                'refColumns'        => 'id_drive_gear_type'
            ),
            'Model' => array(
                'columns'           => 'id_car_model',
                'refTableClass'     => 'Auto_Model_Table_CarModels',
                'refColumns'        => 'id_car_model'
            )
        );
        

}