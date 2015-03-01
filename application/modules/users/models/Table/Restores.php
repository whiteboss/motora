<?php

/**
 *
 * Интерфейс доступа к таблице данных сообщений
 *
 */
class Users_Model_Table_Restores extends Zend_Db_Table_Abstract {

    protected $_name = 'users_restores';
    protected $_rowClass = 'Users_Model_Restore';
    
    protected $_referenceMap = array(
        'Users' => array(
            'columns' => 'id_user',
            'refTableClass' => 'Application_Model_Table_Users',
            'refColumns' => 'id'
        )
    );    
    
    public function getRestore($userId)
    {
        $select = $this->select()->where('id_user = ?', (int) $userId);  
        //throw new Exception($select);
        $row = $this->fetchRow($select);
        if (!$row) return NULL;
        return $row;
    }

    public function createrow( $data = array(), $defaultSource = null ) {
        $new = parent::createRow( $data, $defaultSource );
        $new->date = date_create()->format( 'Y-m-d H:i:s' );
        return $new;
    }

}