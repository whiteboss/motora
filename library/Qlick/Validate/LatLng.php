<?php
class Qlick_Validate_LatLng extends Zend_Validate_Abstract
{
    const FLOAT = 'float';
 
    protected $_messageTemplates = array(
        self::FLOAT => "'%value%' is not a floating point value"
    );
 
    public function isValid($value)
    {
        $this->_setValue($value);
 
        if (!is_real(abs($value))) {
            $this->_error(self::FLOAT);
            return false;
        }
 
        return true;
    }
}