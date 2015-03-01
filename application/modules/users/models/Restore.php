<?php

/**
 * 
 * Сотрудник
 * 
 */
class Users_Model_Restore extends Users_Model_Abstract {

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
    public function getUser() {
        if ( !empty($this->_user) ) return $this->_user;
        $this->_user = $this->findParentRow( 'Application_Model_Table_Users' );
        return $this->_user;
    }

}