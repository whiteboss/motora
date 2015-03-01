<?php

class Qlick_Controller_Helper_Users extends Zend_Controller_Action_Helper_Abstract {
    
    public function getloggedInAs ()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return true;
        }
        return false;
    }

    /* Для махинаций с пользователем
     * если он конечно блять залогинен
     */
    public function getLoggedUser ()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return $auth->getIdentity();
        }
    }
}
