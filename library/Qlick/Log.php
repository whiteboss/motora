<?php

class Qlick_Log extends Zend_Log
{

    /**
     * Создание записи
     */
    public function log( $message, $priority, Zend_Controller_Request_Abstract $request, $extras = null)
    {
        $log = Zend_Registry::get('logger');
        $auth = Zend_Auth::getInstance();                
        if ($auth->hasIdentity()) {
            $log->setEventItem('userid', $auth->getIdentity()->id );
        }
        $userip = $request->getServer('REMOTE_ADDR');
        if ($userip == '66.249.72.122' || $userip == '66.249.72.244') {  // отсекаем гугл робота
            return NULL;
        }
        $log->setEventItem('module', $request->getModuleName());
        $log->setEventItem('controller', $request->getControllerName());
        $log->setEventItem('action', $request->getActionName());
        $log->setEventItem('userip', $userip);
        $log->setEventItem('userhost', $request->getHttpHost());
        
        parent::log( $message, $priority, $extras );
    }
    
}

?>
