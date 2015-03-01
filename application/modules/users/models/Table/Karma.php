<?php
// карма пользоваталей хранится на каждый день
// при изменении кармы, если на тек. день уже есть значение, оно должно обновиться
class Users_Model_Table_Karma extends Zend_Db_Table_Abstract
{

    protected $_name = 'users_karma';
    protected $_primary = array('id');

    protected $_referenceMap = array (
		'Users' => array (
			'columns' => 'ud_user',
			'refTableClass' => 'Application_Model_Table_Users',
			'refColumns' => 'id'
		)
    );
    
    public function getKarmaWithParams($params)
    {
        
        $select = $this->select()->setIntegrityCheck(false)->from(array("r" => $this->_name));
        
        if(isset($params['id_user'])) $select->where("r.id_user = ?", $params['id_user']);        
        if(isset($params['date'])) $select->where("TO_DAYS(r.date) - TO_DAYS(?) = 0", $params['date']);
        
        $select->order('date DESC')->limit(1);
        
        //throw new Exception($select);
        
        $result = $this->fetchRow($select);
        if (!empty($result)) {            
            return ($result);
        } else {
            return NULL;
        }    
    }

//    public function createRow( $data = array(), $defaultSource = null ) {        
//        $new = parent::createRow( $data, $defaultSource );
//        if (count($data) > 0 && is_null($data['date'])) $new->date = date_create()->format( 'Y-m-d H:i:s' );
//        return $new;
//    }    
    
}