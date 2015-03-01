<?php
/**
 *
 * Интерфейс доступа к таблице данных каталога меню
 *
 */

class Companies_Model_Table_MenuCatalog extends Zend_Db_Table_Abstract {
    protected $_name = 'companies_menu_catalog';
    protected $_rowClass = 'Companies_Model_MenuCategory';

    protected $_referenceMap = array (
            'Parent' => array (
                    'columns' => 'id_parent', 
                    'refTableClass' => 'Companies_Model_Table_MenuCatalog', 
                    'refColumns' => 'id'
            ),
            'Company' => array (
                    'columns' => 'id_company', 
                    'refTableClass' => 'Companies_Model_Table_Companies', 
                    'refColumns' => 'id'
            )
    );        
        
}