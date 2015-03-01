<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        $this->_helper->layout->setLayout('error');
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Страница не найдена';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Ошибка приложения';
                // Log exception, if logger available
                if ($log = $this->getLog()) {
                    $message = $errors->exception->getMessage() . ' === ' . $errors->exception->getTraceAsString() . ' === ' . var_export($errors->request->getParams(), true);
                    $log->log($message, Zend_Log::ERR, $errors->request);
                }
                break;
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    protected function getLog()
    {
        $log = $this->logger = Zend_Registry::get('logger');
        return $log;
    }


}