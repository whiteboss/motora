<?php
class Application_Views_Helper_GetUserByIdentity extends Zend_View_Helper_Abstract
{

    public function GetUserByIdentity()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {             
            $table = new Application_Model_Table_Users();
            $userId = (int) $auth->GetIdentity()->id;
            $users = $table->find($userId);
            if (count($users) > 0) {                
                return $users->current();
            } else {
                $auth->clearIdentity();
                return NULL;
            }

        } else {
            return NUlL;
        }
    }

}