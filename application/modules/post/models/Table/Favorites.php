<?php

class Post_Model_Table_Favorites extends Zend_Db_Table_Abstract
{

    protected $_name = 'post_favorites';
    protected $_rowClass = 'Post_Model_Favorites';

     protected $_referenceMap = array (
         'Post' => array(
            'columns' => 'post_id',
            'refTableClass' => 'Post_Model_Table_Post',
            'refColumns' => 'id'
            )
     );
    
    public function getPostFavoritesWithParams($params)
    {
        $qs = $this->select()->setIntegrityCheck(false)->from(array("pf"=>$this->_name));
        
        if(isset($params['userId']))
            $qs->where("pf.user_id = ?", $params['userId']);
        
        if(isset($params['post_id']))
            $qs->where ("pf.post_id = ?", $params['post_id']);
        
        $result = $this->fetchAll($qs);
        
        return $result;
        
    }
   
    
    
}

