<?php
/**
 *
 * Интерфейс доступа к таблице данных лайков
 *
 */

class Companies_Model_Table_Likes extends Zend_Db_Table_Abstract {
	protected $_name = 'companies_likes';

	protected $_referenceMap = array (
		'Company' => array (
			'columns' => 'id_company', 
			'refTableClass' => 'Companies_Model_Table_Companies', 
			'refColumns' => 'id'
		),
		'Dish' => array (
			'columns' => 'id_dish', 
			'refTableClass' => 'Companies_Model_Table_MenuDishes', 
			'refColumns' => 'id'
		),
		'User' => array (
			'columns' => 'id_user', 
			'refTableClass' => 'Application_Model_Table_Users', 
			'refColumns' => 'id'
		)
	);
    
    public function getCompanyLike($companyId)
    {
        $qs = $this->select()->setIntegrityCheck(false)->from(array("cl"=>$this->_name),array("likeCount"=>"COUNT(cl.id)"));        
        $qs->where("cl.id_company = ?",$companyId);        
        $result = $this->fetchAll($qs);        
        return $result;
    }
    
    public function createRow( $data = array(), $defaultSource = null ) {        
        $new = parent::createRow( $data, $defaultSource );
        $new->date = date_create()->format( 'Y-m-d H:i:s' );
        return $new;
    }     
    
}