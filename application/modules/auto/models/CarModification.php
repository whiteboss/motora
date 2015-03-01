<?php
/**
 * 
 * Кузов
 * 
 */

class Auto_Model_CarModification extends Auto_Model_Abstract {
	
    protected $_gearbox;
    protected $_geartype;
    
    public function __toString() {
        return (string) $this->name;
    }

    public function getGearBox() {
        if ( !empty($this->_gearbox) ) return $this->_gearbox;
        $this->_gearbox = $this->findParentRow( 'Auto_Model_Table_CarGearBoxes' );
        return $this->_gearbox;
    }
    
    public function getGearType() {
        if ( !empty($this->_geartype) ) return $this->_geartype;
        $this->_geartype = $this->findParentRow( 'Auto_Model_Table_CarGearTypes' );
        return $this->_geartype;
    }

}