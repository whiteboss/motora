<?php
/**
 *
 * Интерфейс доступа к таблице данных лайков
 *
 */

class Post_Model_Table_Views extends Zend_Db_Table_Abstract {
    
    protected $_name = 'posts_view';

    protected $_referenceMap = array (
            'Post' => array (
                    'columns' => 'id_music', 
                    'refTableClass' => 'Post_Model_Table_Post', 
                    'refColumns' => 'id'
            )
    );
    
    public function createRow( $data = array(), $defaultSource = null ) {        
        $new = parent::createRow( $data, $defaultSource );
        $new->date = date_create()->format( 'Y-m-d' );
        return $new;
    }     
    
}