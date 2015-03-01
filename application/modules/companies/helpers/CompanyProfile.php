<?php

/**
 * 
 * Perfil de empresa
 *
 */
class Companies_Helper_CompanyProfile extends Zend_Controller_Action_Helper_Abstract {

    protected $view;

    /**
     * Возвращает данные о компании и передает из в объект вида
     * @param int $id идентификатор компании
     * @return Companies_Model_Company
     */
    public function getCompany($id, $url) {
        $table = new Companies_Model_Table_Companies();
        
        if ($id > 0)
            $items = $table->find($id);
        else
            $items = $table->getCompanyByUrl($url);
        
        if (count($items) > 0) {
            
            if ($id > 0)
                $item = $items->current();
            else
                $item = $items;
            
            $item->count();
            return $item;
        } else {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
//            $controller = $this->getActionController();
//            $controller->FlashMessenger(array('system_message' => 'Desconocido identificador de empresa'));
            $redirector->gotoUrl($this->view->url(array(''), 'companies'));
        }
    }

    /**
     * Возвращает текущий объект вида
     * @return Zend_View
     */
    public function getView() {
        if (null !== $this->view) {
            return $this->view;
        }
        $controller = $this->getActionController();
        $view = $controller->view;
        if (!$view instanceof Zend_View_Abstract) {
            return;
        }
        $this->view = $view;
        return $view;
    }

    /**
     * @param int $id идентификатор компании
     * @return Companies_Model_Company
     */
    public function direct($id = 0, $url = NULL) {
        //$this->getView()->active = $this->getRequest()->getControllerName();
        $this->getView()->company = $this->getCompany($id, $url);
        return $this->getView()->company;
    }

}