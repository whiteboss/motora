<?php
class Qlick_Validate_Url extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL => "%value% &mdash; не существующий или некорректный адрес!"
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
 
        //if we have a valid URI then we check the hostname for valid TLDs, and not local urls
        $hostnameValidator = new Zend_Validate_Hostname(Zend_Validate_Hostname::ALLOW_DNS); //do not allow local hostnames, this is the default
        if (!$hostnameValidator->isValid($uriHttp->getHost())) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        return true;        
        
//        $valueString = (string) $value;        
//        $this->_setValue($valueString);
//        
//        // проверка ебанутых русских доменов        
//        if (mb_ereg_match('/[^а-я]/', $valueString)) {            
//            $valueString = idn_to_ascii(str_replace('http://', '', $valueString));
//        } else {
//            if (!Zend_Uri::check($valueString)) {
//                $this->_error(self::INVALID_URL);
//                return false;
//            }
//        }
////        throw new Exception($valueString);
//        
////        ini_set('default_socket_timeout', '10');
////        $fp = fopen($valueString, "r");
////        $res = fread($fp, 500);
////        fclose($fp);
////        if (strlen($res) > 0) 
////          return true;
////        
////        return false;
//        
//
//        $c = curl_init();
//        curl_setopt($c,CURLOPT_URL, $valueString);
//        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 10);
//        curl_setopt($c,CURLOPT_HEADER, 1);//get the header
//        curl_setopt($c,CURLOPT_NOBODY, 1);//and *only* get the header
//        curl_setopt($c,CURLOPT_RETURNTRANSFER, 1);//get the response as a string from curl_exec(), rather than echoing it
//        curl_setopt($c,CURLOPT_FRESH_CONNECT,1);//don't use a cached version of the url
//        $response = curl_exec($c); 
//        curl_close($c);  
//        if(!$response) {
//            $this->_error(self::INVALID_URL);
//            return false;
//        }
//
//        return true;
    }
}