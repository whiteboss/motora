<?php
class Application_Views_Helper_isGodOfProject extends Zend_View_Helper_Abstract
{

    public function isGodOfProject()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {            
            //$table = new Application_Model_Table_Users();
            $userId = (int) $auth->GetIdentity()->id;
            if ($userId == 1 || $userId == 2 || $userId == 94) return true; else return false;

//            $users = $table->find($userId);
//            if ($users) {
//                $user = $users->current();
//            } else {
//                return NILL;
//            }

        } else {
            return false;
        }
    }

}