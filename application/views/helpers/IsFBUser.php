<?php
class Application_Views_Helper_IsFBUser extends Zend_View_Helper_Abstract
{

    public function IsFBUser()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {             
            $user = $this->view->GetUserByIdentity();
            if (!is_null($user->uid)) {
                return TRUE;
            } else {
                return FALSE;
            }

        } else {
            return NUlL;
        }
    }

}