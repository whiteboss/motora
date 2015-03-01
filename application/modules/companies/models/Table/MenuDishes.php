<?php
/**
 *
 * Интерфейс доступа к таблице данных блюд меню
 *
 */

class Companies_Model_Table_MenuDishes extends Zend_Db_Table_Abstract {
	protected $_name = 'companies_menu_dish';
	protected $_rowClass = 'Companies_Model_MenuDish';

	protected $_referenceMap = array (
		'Category' => array (
			'columns' => 'id_catalog', 
			'refTableClass' => 'Companies_Model_Table_MenuCatalog', 
			'refColumns' => 'id'
		)
	);
        
        public function getBestDishes($limit = 10)
        {
                $items = array();

                //$table = new Companies_Model_Table_LinkTypes();
                $select = $this->select()
                        ->from($this->_name, array('id', 'id_catalog', 'name', 'photo', 'description', 'price'))
                        ->setIntegrityCheck(false)
                        ->joinInner( 'companies_menu_catalog', 'companies_menu_catalog.id = companies_menu_dish.id_catalog', NULL )                        
                        ->joinInner( 'companies_company', 'companies_company.id = companies_menu_catalog.id_company', array('id as company_id', 'name as company_name', 'type as company_type') )                        
                        ->joinLeft( 'companies_likes', 'companies_likes.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_company = `companies_company`.id) AS company_like') )                        
                        //->joinLeft( 'companies_likes', 'companies_likes.id_dish = companies_menu_dish.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_dish = `companies_menu_dish`.id) AS dish_like') )                        
                        ->where( 'is_best = 1' )
                        ->where( 'visible = 1' )
                        ->where( 'photo is not NULL' )
                        ->where( 'companies_company.is_confirmed = 1' )
                        ->limit($limit)
                        //->order( array('company_like DESC', 'dish_like DESC') )			
                        ->order( 'company_like DESC' )
			->group('id')
                        ;
                
                //throw new Exception($select);
                
                $rows = $this->fetchAll( $select );
                //throw new Exception(count($rows));
                foreach ( $rows as $row ) {
                    $items[ $row->id ] = $row;    
                }                                
                //throw new Exception(count($items));
                return $items;        
        }
        
}