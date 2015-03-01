<?php
/**
 * 
 * Кузов
 * 
 */

class Auto_Model_CarFuelKind extends Auto_Model_Abstract {
	
	public function __toString() {
		return (string) $this->name;
	}

}