<?php

class Companies_Model_LinkType extends Companies_Model_Abstract
{  
    
    public function __toString() {
		return (string) $this->name;
	}
    
    public function getParent() {
            if ( !empty($this->_parent) ) return $this->_parent;
            if ( empty($this->parent_id) ) return null;
            $this->_parent = $this->findParentRow( 'Companies_Model_Table_Spheres' );
            return $this->_parent;
    }
		
    /**
     * Возвращает список подкатегорий
     * @return array
     */
    public function getChildrenList() {
            $rows = $this->findDependentRowset( 'Companies_Model_Table_Spheres' );
            $items = array();
            foreach ( $rows as $row ) {
                    $items[ $row->id ] = $row;
            }
            return $items;
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

