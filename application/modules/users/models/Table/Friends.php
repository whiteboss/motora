<?php

/**
 *
 * Интерфейс доступа к таблице данных сообщений
 *
 */
class Users_Model_Table_Friends extends Zend_Db_Table_Abstract {

    protected $_name = 'users_friends';
    protected $_rowClass = 'Users_Model_Friend';
    protected $_referenceMap = array(
        'MyFriends' => array(
            'columns' => 'id_user_from',
            'refTableClass' => 'Application_Model_Table_Users',
            'refColumns' => 'id'
        ),
        'FriendRequest' => array(
            'columns' => 'id_user_to',
            'refTableClass' => 'Application_Model_Table_Users',
            'refColumns' => 'id'
        )
    );

    /**
     * Запрос на добавление в друзья
     * @param  array $data OPTIONAL данные инициализации
     * @param  string $defaultSource OPTIONAL флаг указывающий установить значения по умолчанию
     * @return Application_Model_User
     */
    public function sendFriendship($data = array(), $defaultSource = null) {
        $new = parent::createRow($data, $defaultSource);
        $new->date_request = date_create()->format('Y-m-d H:i:s');
        return $new;
    }

    public function CountFriends($params) {
        $qs = $this->select()->setIntegrityCheck(false)->from(array("f" => $this->_name), array("friendCount" => "COUNT(id)"))
                ->where("f.id_user_from =?", $params['id_user_from'])->where("f.status =?", $params['status']);
        //throw new Exception($qs);
        $result = $this->fetchAll($qs);
        return $result;
    }

    public function CountRequestFriends($params) {
        $qs = $this->select()->setIntegrityCheck(false)->from(array("f" => $this->_name), array("friendCount" => "COUNT(id)"))
                ->where("f.id_user_to =?", $params['id_user_to'])->where("f.status =?", $params['status']);

        $result = $this->fetchAll($qs);
        return $result;
    }

    public function checkFriendship($id_user_from, $id_user_to, $status = 0) {
        $select = $this->select()->setIntegrityCheck(false)->where('id_user_from = ?', $id_user_from)->where('id_user_to = ?', $id_user_to)->where('status = ?', $status);
        //throw new Exception($select);
        $row = $this->fetchRow($select);
        //if ($row) return true; else return false;
        return $row;
    }
    
    public function deleteFriendship($id_user_from, $id_user_to) {
        $select = $this->select()->setIntegrityCheck(false)->where('id_user_from = ?', $id_user_from)->where('id_user_to = ?', $id_user_to)->orwhere('id_user_to = ?', $id_user_from)->where('id_user_from = ?', $id_user_to)->where('status = 1');        
        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        
        $db = $this->getAdapter()->beginTransaction();
        // безопасное удаление
        try {
            $i = 0;
            foreach ( $rows as $row ) {
                $row->delete();
                $i++;
            }
            if ($i > 0) {
                $this->getAdapter()->commit();
                return true;
            } else {
                throw new Exception('Ошибка удаления дружественной связи');
            }    
        } catch (Exception $e) {
            $this->getAdapter()->rollback();            
            return false;
        }    
    }

    public function getAllFriends($id_user, $filter = '') {
        $items = array();
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array("f" => $this->_name), array('id', 'id_user_from'))
                ->joinLeft(array('u' => 'users'), 'u.id = f.id_user_from', array('username') )
                ->where('f.id_user_to =?', $id_user)
                ->where('f.status = 1');
        
        if ($filter != '')
            $select->where('u.username LIKE ?', '%' . $filter . '%');
        
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;
    }

    public function getFriendshipRequest($id_request) {
        $items = array();
        $select = $this->select()->setIntegrityCheck(false)->from(array("f" => $this->_name), array("id"))
                //->where('f.id_user_from =?', $id_user)
                //->where('f.id_user_to = ?', (int) $id_user)
                ->where('f.id = ?', (int) $id_request)
                ->where('f.status = 0');
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;
    }



//    public function getMessagesWithParams($params) {
//        $qs = $this->select()->setIntegrityCheck(false)->from(array("m" => $this->_name));
//
//        if (isset($params['userToFrom'])) {
//            $qs->where("m.id_user_to = " . $params['userToFrom'] . " OR m.id_user_from = " . $params['userToFrom']);
//        }
//        if (isset($params['id']))
//            $qs->where("m.id = ?", $params['id']);
//
//        $result = $this->fetchAll($qs);
//
//        return $result;
//    }

}