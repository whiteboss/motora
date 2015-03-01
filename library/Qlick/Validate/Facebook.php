<?php
class Qlick_Validate_Facebook extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL => "%value% &mdash; не facebook адрес!"
    );

    public function isValid($value)
    {
        
        if (!is_string($value)) {
            $this->_error(self::INVALID_URL);
            return false;
        }
 
        $this->_setValue($value);
        //get a Zend_Uri_Http object for our URL, this will only accept http(s) schemes
        try {
            $uriHttp = Zend_Uri_Http::fromString($value);
        } catch (Zend_Uri_Exception $e) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        
        if (!preg_match('#https?\://(?:www\.)?facebook\.com/(\d+|[A-Za-z0-9\.]+)/?#', $value)) {
        //if (!preg_match('#https?\://(?:www\.)?facebook\.com/(\pL+|[A-Za-z0-9\.]+)/iu?#', $value)) {
            $this->_error(self::INVALID_URL);
            return false; 
        }

        return true;        
 
    }
}