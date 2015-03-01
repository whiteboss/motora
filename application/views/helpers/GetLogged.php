<?php
class Application_Views_Helper_GetLogged extends Zend_View_Helper_Abstract
{

    public function GetLogged()
    {
        $auth = Zend_Auth::getInstance();        
        if ($auth->hasIdentity()) return true; else return false;
    }

}