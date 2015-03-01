<?php
/**
 * 
 * Блюдо
 * 
 */

class Companies_Model_MenuDish extends Companies_Model_Abstract {
	
	protected $_category;
	
	/**
	 * @return string
	 */
	public function __toString() {
		return (string) $this->name;
	}
	
	/**
	 * @return Companies_Model_MenuCategory
	*/
	public function getCategory() {
		if ( !empty($this->_category) ) return $this->_category;
		$this->_category = $this->findParentRow( 'Companies_Model_Table_MenuCatalog' );
		return $this->_category;
	}
        
        public function getName() {
            if (strlen($this->name) > 28)
                return mb_substr($this->name, 0, 28, 'UTF-8') . '...';
            else
                return $this->name;
        }

                /**
	 * @return string
	*/
	public function getPrice() {
		//return number_format( $this->price, 0, ',', ' ' );
            return $this->price;
	}
	
	/**
	 * @return string
	*/
	public function getVolume() {
		return $this->volume;
	}
	
	/**
	 * @return string
	*/
	public function getPhoto() {
            if (!is_null($this->photo)) {
                $photo = json_decode($this->photo);
                return $photo[0];
            } else
                return NULL;
	}
	
	/**
	 * @param int $user_id
	 * @return array
	*/
	public function isLiked( $user_id ) {
		$rows = $this->findDependentRowset('Companies_Model_Table_Likes', null, $this->select()->where('id_user = ?', $user_id));
		return (bool) count($rows);
	}

        public function dishLikeCount() {
            $rows = $this->findDependentRowset('Companies_Model_Table_Likes');
            if (count($rows) > 0) return count($rows); else return NULL;
        }

	/**
	 * Возвращает данные в массиве колонка=>значение
	 * @return array
	*/
	public function toArray() {
		$data = parent::toArray ();
		$data['id'] = (int) $data['id'];
		return $data;
	}
}