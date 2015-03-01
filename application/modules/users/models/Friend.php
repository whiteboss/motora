<?php

/**
 * 
 * Сотрудник
 * 
 */
class Users_Model_Friend extends Users_Model_Abstract {

    protected $_user;

    /**
     * @return string
     */
    public function __toString() {
        //return $this->getFullName();
    }

    /**
     * Возвращает данные в массиве колонка=>значение
     * @return array
     */
    public function toArray() {
        $data = parent::toArray();
        $data['id'] = (int) $data['id'];
        return $data;
    }

    /**
     * Возвращает запись дружбана
     * @return Zend_Db_Table_Row друг
     */
    public function getFriend() {
        $table = new Application_Model_Table_Users();
        $select = $table->select()->setIntegrityCheck(false)->from("users")->where("id = ?", $this->id_user_to);
        return $table->fetchRow($select);
    }
    
    public function getRequestFriend() {
        $table = new Application_Model_Table_Users();
        $select = $table->select()->setIntegrityCheck(false)->from("users")->where("id = ?", $this->id_user_from);
        return $table->fetchRow($select);
    }

}