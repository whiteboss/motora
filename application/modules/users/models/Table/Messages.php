<?php
/**
 *
 * Интерфейс доступа к таблице данных сообщений
 *
 */

class Users_Model_Table_Messages extends Zend_Db_Table_Abstract {
	protected $_name = 'users_messages';
	protected $_rowClass = 'Users_Model_Message';

	protected $_referenceMap = array (
		'Outbox' => array (
			'columns' => 'id_user_from',
			'refTableClass' => 'Application_Model_Table_Users',
			'refColumns' => 'id'
		),
		'Inbox' => array (
			'columns' => 'id_user_to',
			'refTableClass' => 'Application_Model_Table_Users', 
			'refColumns' => 'id'
		),
                'AuthorProfile' => array(
			'columns' => 'id_user_to',
			'refTableClass' => 'Application_Model_Table_Profiles', 
			'refColumns' => 'id'
		)
	);

    /**
     * Создание нового сообщения
     * @param  array $data OPTIONAL данные инициализации
     * @param  string $defaultSource OPTIONAL флаг указывающий установить значения по умолчанию
     * @return Application_Model_User
     */
    public function createrow( $data = array(), $defaultSource = null ) {
            $new = parent::createRow( $data, $defaultSource );
            $new->date = date_create()->format( 'Y-m-d H:i:s' );
            return $new;
    }
    
    public function getCountMessages($params)
    {
        $qs = $this->select()->setIntegrityCheck(false)->from(array("f"=>$this->_name), array("messageCount"=>"COUNT(id)"));
        
        if($params['messageType']==1) // входящие
            $qs->where("f.id_user_to = ?", $params['userId'])->where("f.delete_user_to = 0");
        if($params['messageType']==2) // исходящие
            $qs->where("f.id_user_from = ?", $params['userId'])->where("f.delete_user_from = 0");
        if($params['messageType']==3){ // не прочитанные
            $qs->where("f.id_user_to = ?", $params['userId'])->where("f.is_read = 0")->where("f.delete_user_to = 0");
        }

        $result = $this->fetchAll($qs);
        
        return $result;
       
    }
    
    public function getMessagesWithParams($params)
    {
        $qs = $this->select()->setIntegrityCheck(false)->from(array("m"=>$this->_name));
        
        if(isset($params['userToFrom'])){
            $qs->where("m.id_user_to = ".$params['userToFrom']." OR m.id_user_from = ".$params['userToFrom']);
        }
        if(isset($params['id']))
            $qs->where("m.id = ?", $params['id']);

        $result = $this->fetchAll($qs);
        
        return $result;
    }

}