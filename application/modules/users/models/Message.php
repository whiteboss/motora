<?php
/**
 * 
 * Сотрудник
 * 
 */

class Users_Model_Message extends Users_Model_Abstract {
	
	protected $_user;
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getFullName();
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
	 * Возвращает запись сообщения
	 * @return Zend_Db_Table_Row сообщение
	 */
	public function getMessage() {
            return $this->message;
	}

        public function getFrom() {
//            $table = new Application_Model_Table_Users();
//            $rows = $table->find( $this->id_user_from );
//            if ( count($rows)>0 ) {
//                    $row = $rows->current();
//            } else {
//                    throw new Exception('Desconocido identificador');
//            }
//            return $row->getFullName();
            $table = new Application_Model_Table_Users();
            $qs = $table->select()->setIntegrityCheck(false)->where("id = ?", $this->id_user_from);
            return $table->fetchRow($qs);
        }

        public function getTo() {
            $table = new Application_Model_Table_Users();
            $qs = $table->select()->setIntegrityCheck(false)->where("id = ?", $this->id_user_to);
            return $table->fetchRow($qs);
        }

        /**
	 * @return string
	*/
	public function getSubject() {
            return $this->subject;
	}

}