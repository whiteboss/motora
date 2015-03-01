<?php
/**
 *
 * Интерфейс доступа к таблице данных лайков
 *
 */

class Companies_Model_Table_Views extends Zend_Db_Table_Abstract {
    
    protected $_name = 'companies_view';

    protected $_referenceMap = array (
            'Company' => array (
                    'columns' => 'id_company', 
                    'refTableClass' => 'Companies_Model_Table_Companies', 
                    'refColumns' => 'id'
            )
    );
    
    public function createRow( $data = array(), $defaultSource = null ) {        
        $new = parent::createRow( $data, $defaultSource );
        $new->date = date_create()->format( 'Y-m-d' );
        return $new;
    }     
    
}